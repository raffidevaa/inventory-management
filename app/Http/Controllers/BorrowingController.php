<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Borrowing::class);

        $borrowings = Borrowing::with(['user', 'borrowingDetails.product'])
            ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('borrowings.index', compact('borrowings'));
    }

    public function create()
    {
        $this->authorize('create', Borrowing::class);

        $products = Product::where('condition', '!=', 'heavily_damaged')
            ->where('stock_available', '>', 0)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('borrowings.create', compact('products'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Borrowing::class);

        $validated = $request->validate([
            'borrower_name' => ['required', 'string', 'max:100'],
            'borrow_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:borrow_date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated['items'] as $i => $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->condition === 'heavily_damaged') {
                return back()->withErrors(["items.{$i}.product_id" => "'{$product->name}' cannot be borrowed (heavily damaged)."])->withInput();
            }
            if ($product->stock_available < $item['quantity']) {
                return back()->withErrors(["items.{$i}.quantity" => "Insufficient stock for '{$product->name}'. Available: {$product->stock_available}."])->withInput();
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

        return redirect()->route('borrowings.show', $borrowing)->with('success', 'Borrowing transaction created successfully.');
    }

    public function show(Borrowing $borrowing)
    {
        $this->authorize('view', $borrowing);

        $borrowing->load(['user', 'borrowingDetails.product']);

        return view('borrowings.show', compact('borrowing'));
    }

    public function edit(Borrowing $borrowing)
    {
        $this->authorize('update', $borrowing);

        return view('borrowings.edit', compact('borrowing'));
    }

    public function update(Request $request, Borrowing $borrowing)
    {
        $this->authorize('update', $borrowing);

        abort(501, 'Not yet implemented.');
    }

    public function destroy(Borrowing $borrowing)
    {
        $this->authorize('delete', $borrowing);

        abort(501, 'Not yet implemented.');
    }

    public function processReturn(Request $request, Borrowing $borrowing)
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

        return redirect()->route('borrowings.show', $borrowing)->with('success', 'Return processed successfully.');
    }
}
