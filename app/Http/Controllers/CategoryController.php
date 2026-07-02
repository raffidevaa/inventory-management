<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize('manage-inventory');

        return view('categories.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage-inventory');

        abort(501, 'Not yet implemented.');
    }

    public function show(Category $category)
    {
        $category->load('products');

        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $this->authorize('manage-inventory');

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('manage-inventory');

        abort(501, 'Not yet implemented.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('manage-inventory');

        abort(501, 'Not yet implemented.');
    }
}
