<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Borrowings', 'Borrowing transactions and returns.')]
class BorrowingApiController extends Controller
{
    use ApiResponse;

    /**
     * List all borrowing transactions.
     */
    #[QueryParam('status', 'string', 'Filter by status.', required: false, enum: ['borrowed', 'returned', 'overdue'], example: 'borrowed')]
    #[QueryParam('page', 'integer', 'Page number.', required: false, example: 1)]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Data retrieved successfully',
        'data' => [['id' => 1, 'borrower_name' => 'Budi Santoso', 'status' => 'borrowed', 'borrow_date' => '2026-07-02', 'due_date' => '2026-07-09']],
        'meta' => ['current_page' => 1, 'last_page' => 2, 'per_page' => 15, 'total' => 18],
    ])]
    #[Response(status: 401, content: ['message' => 'Unauthenticated.'])]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Borrowing::class);

        $borrowings = Borrowing::with(['user', 'borrowingDetails.product'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        return $this->paginatedSuccess($borrowings);
    }

    /**
     * Create a new borrowing transaction (multi-item).
     *
     * Requires the Admin or Staff role.
     */
    #[BodyParam('borrower_name', 'string', 'Name of the borrower.', example: 'Budi Santoso')]
    #[BodyParam('borrow_date', 'date', 'Date the items are borrowed.', example: '2026-07-02')]
    #[BodyParam('due_date', 'date', 'Date the items are due back.', example: '2026-07-09')]
    #[BodyParam('notes', 'string', 'Optional notes.', required: false)]
    #[BodyParam('items', 'object[]', 'Items to borrow.')]
    #[BodyParam('items[].product_id', 'integer', 'Existing product ID.', example: 3)]
    #[BodyParam('items[].quantity', 'integer', 'Quantity to borrow.', example: 2)]
    #[Response(status: 201, content: ['success' => true, 'message' => 'Borrowing transaction created successfully', 'data' => ['id' => 1, 'borrower_name' => 'Budi Santoso', 'status' => 'borrowed']])]
    #[Response(status: 403, content: ['success' => false, 'message' => 'This action is unauthorized.'])]
    #[Response(status: 422, content: ['success' => false, 'message' => "Insufficient stock for 'Laptop Dell Latitude'. Available: 1."])]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Borrowing::class);

        $validated = $request->validate([
            'borrower_name' => ['required', 'string', 'max:100'],
            'borrow_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:borrow_date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Validate stock and condition per BR-02 and BR-08
        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->condition === 'heavily_damaged') {
                return $this->error("Product '{$product->name}' cannot be borrowed (heavily damaged).", 422);
            }
            if ($product->stock_available < $item['quantity']) {
                return $this->error("Insufficient stock for '{$product->name}'. Available: {$product->stock_available}.", 422);
            }
        }

        $borrowing = DB::transaction(function () use ($validated, $request) {
            $borrowing = Borrowing::create([
                'borrower_name' => $validated['borrower_name'],
                'user_id' => $request->user()->id,
                'borrow_date' => $validated['borrow_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'borrowed',
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $borrowing->borrowingDetails()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'condition_before' => $product->condition,
                    'item_status' => 'borrowed',
                ]);
                $product->decrement('stock_available', $item['quantity']);
            }

            return $borrowing;
        });

        return $this->success(
            $borrowing->load('borrowingDetails.product'),
            'Borrowing transaction created successfully',
            201
        );
    }

    /**
     * Get borrowing transaction detail.
     */
    #[UrlParam('id', 'integer', 'The borrowing ID.', example: 1)]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Data retrieved successfully',
        'data' => ['id' => 1, 'borrower_name' => 'Budi Santoso', 'status' => 'borrowed', 'borrowing_details' => [['id' => 1, 'product_id' => 3, 'quantity' => 2, 'item_status' => 'borrowed']]],
    ])]
    #[Response(status: 404, content: ['message' => 'No query results for model [App\\Models\\Borrowing].'])]
    public function show(Borrowing $borrowing): JsonResponse
    {
        $this->authorize('view', $borrowing);

        return $this->success($borrowing->load(['user', 'borrowingDetails.product']));
    }

    /**
     * Process item return for a borrowing transaction.
     *
     * Requires the Admin or Staff role.
     */
    #[UrlParam('id', 'integer', 'The borrowing ID.', example: 1)]
    #[BodyParam('items', 'object[]', 'Items being returned.')]
    #[BodyParam('items[].borrowing_detail_id', 'integer', 'Existing borrowing detail ID.', example: 1)]
    #[BodyParam('items[].condition_after', 'string', 'Item condition on return.', enum: ['good', 'lightly_damaged', 'heavily_damaged'], example: 'good')]
    #[Response(status: 200, content: ['success' => true, 'message' => 'Return processed successfully', 'data' => ['id' => 1, 'status' => 'returned']])]
    #[Response(status: 403, content: ['success' => false, 'message' => 'This action is unauthorized.'])]
    public function processReturn(Request $request, Borrowing $borrowing): JsonResponse
    {
        $this->authorize('processReturn', $borrowing);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.borrowing_detail_id' => ['required', 'exists:borrowing_details,id'],
            'items.*.condition_after' => ['required', 'in:good,lightly_damaged,heavily_damaged'],
        ]);

        DB::transaction(function () use ($validated, $borrowing) {
            foreach ($validated['items'] as $item) {
                $detail = $borrowing->borrowingDetails()->findOrFail($item['borrowing_detail_id']);
                if ($detail->item_status === 'borrowed') {
                    $detail->update([
                        'condition_after' => $item['condition_after'],
                        'item_status' => 'returned',
                    ]);
                    $detail->product->increment('stock_available', $detail->quantity);
                }
            }

            $allReturned = $borrowing->borrowingDetails()->where('item_status', 'borrowed')->doesntExist();
            if ($allReturned) {
                $borrowing->update(['status' => 'returned', 'return_date' => now()->toDateString()]);
            }
        });

        return $this->success(
            $borrowing->fresh()->load('borrowingDetails.product'),
            'Return processed successfully'
        );
    }
}
