<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with('category')
            ->when(request('search'), fn($q, $s) => $q->where('name', 'ilike', "%{$s}%")->orWhere('code', 'ilike', "%{$s}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        return view('products.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        // Full implementation in Day 3 (StoreProductRequest + CategoryController)
        abort(501, 'Not yet implemented.');
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load(['category', 'borrowingDetails.borrowing']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        abort(501, 'Not yet implemented.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        abort(501, 'Not yet implemented.');
    }
}
