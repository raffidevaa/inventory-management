<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with('category')
            ->when(request('search'), fn ($q, $s) => $q->where(fn ($inner) => $inner
                ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($s).'%'])
                ->orWhereRaw('LOWER(code) LIKE ?', ['%'.strtolower($s).'%'])))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();
        $validated['stock_available'] = $validated['stock'];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $validated['code'].'.'.$file->extension();
            $validated['image'] = $file->storeAs('products', $filename, config('filesystems.default', 'public'));
        }

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
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

        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk(config('filesystems.default', 'public'))->delete($product->image);
            }
            $file = $request->file('image');
            $code = $validated['code'] ?? $product->code;
            $filename = $code.'.'.$file->extension();
            $validated['image'] = $file->storeAs('products', $filename, config('filesystems.default', 'public'));
        } else {
            unset($validated['image']);
        }

        $product->update($validated);

        return redirect()->route('products.show', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        if ($product->image) {
            Storage::disk(config('filesystems.default', 'public'))->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
