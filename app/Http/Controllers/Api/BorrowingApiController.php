<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class BorrowingApiController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/borrowings',
        summary: 'List all borrowing transactions',
        security: [['sanctum' => []]],
        tags: ['Borrowings'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', description: 'Filter by status', schema: new OA\Schema(type: 'string', enum: ['borrowed', 'returned', 'overdue'])),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated borrowing list'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Borrowing::class);

        $borrowings = Borrowing::with(['user', 'borrowingDetails.product'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        return $this->paginatedSuccess($borrowings);
    }

    #[OA\Post(
        path: '/borrowings',
        summary: 'Create a new borrowing transaction (multi-item)',
        security: [['sanctum' => []]],
        tags: ['Borrowings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['borrower_name', 'borrow_date', 'due_date', 'items'],
                properties: [
                    new OA\Property(property: 'borrower_name', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'borrow_date', type: 'string', format: 'date', example: '2026-07-02'),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-07-09'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'product_id', type: 'integer', example: 3),
                                new OA\Property(property: 'quantity', type: 'integer', example: 2),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Borrowing transaction created'),
            new OA\Response(response: 403, description: 'Forbidden — Admin/Staff only'),
            new OA\Response(response: 422, description: 'Validation error or insufficient stock'),
        ]
    )]
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

    #[OA\Get(
        path: '/borrowings/{id}',
        summary: 'Get borrowing transaction detail',
        security: [['sanctum' => []]],
        tags: ['Borrowings'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Borrowing detail with all items'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Borrowing $borrowing): JsonResponse
    {
        $this->authorize('view', $borrowing);

        return $this->success($borrowing->load(['user', 'borrowingDetails.product']));
    }

    #[OA\Patch(
        path: '/borrowings/{id}/return',
        summary: 'Process item return for a borrowing transaction',
        security: [['sanctum' => []]],
        tags: ['Borrowings'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['items'],
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'borrowing_detail_id', type: 'integer', example: 1),
                                new OA\Property(property: 'condition_after', type: 'string', enum: ['good', 'lightly_damaged', 'heavily_damaged']),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Return processed successfully'),
            new OA\Response(response: 403, description: 'Forbidden — Admin/Staff only'),
        ]
    )]
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
