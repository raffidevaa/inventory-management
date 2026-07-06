<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Products', 'Manage inventory products.')]
class ProductApiController extends Controller
{
    use ApiResponse;

    /**
     * List all products (paginated).
     */
    #[QueryParam('search', 'string', 'Search by name or code.', required: false, example: 'laptop')]
    #[QueryParam('category_id', 'integer', 'Filter by category ID.', required: false, example: 1)]
    #[QueryParam('page', 'integer', 'Page number.', required: false, example: 1)]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Data retrieved successfully',
        'data' => [['id' => 1, 'code' => 'ITM-ABC-1234', 'name' => 'Laptop Dell Latitude', 'category_id' => 1, 'stock' => 10, 'stock_available' => 8, 'condition' => 'good']],
        'meta' => ['current_page' => 1, 'last_page' => 3, 'per_page' => 15, 'total' => 42],
    ])]
    #[Response(status: 401, content: ['message' => 'Unauthenticated.'])]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with('category')
            ->when($request->search, fn ($q, $s) => $q->where(fn ($inner) => $inner
                ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($s).'%'])
                ->orWhereRaw('LOWER(code) LIKE ?', ['%'.strtolower($s).'%'])))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->latest()
            ->paginate(15);

        return $this->paginatedSuccess($products);
    }

    /**
     * Create a new product.
     *
     * Requires the Admin or Staff role.
     */
    #[BodyParam('code', 'string', 'Unique product code.', example: 'ITM-ABC-1234')]
    #[BodyParam('name', 'string', 'Product name.', example: 'Laptop Dell Latitude')]
    #[BodyParam('category_id', 'integer', 'Existing category ID.', example: 1)]
    #[BodyParam('stock', 'integer', 'Initial stock count.', example: 10)]
    #[BodyParam('location', 'string', 'Storage location.', required: false, example: 'Gedung A, Lantai 2')]
    #[BodyParam('condition', 'string', 'Product condition.', enum: ['good', 'lightly_damaged', 'heavily_damaged'], example: 'good')]
    #[Response(status: 201, content: ['success' => true, 'message' => 'Product created successfully', 'data' => ['id' => 1, 'code' => 'ITM-ABC-1234', 'name' => 'Laptop Dell Latitude']])]
    #[Response(status: 403, content: ['success' => false, 'message' => 'This action is unauthorized.'])]
    #[Response(status: 422, content: ['success' => false, 'message' => 'The code has already been taken.'])]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:products,code'],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'stock' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:100'],
            'condition' => ['required', 'in:good,lightly_damaged,heavily_damaged'],
        ]);

        $validated['stock_available'] = $validated['stock'];

        $product = Product::create($validated);

        return $this->success($product->load('category'), 'Product created successfully', 201);
    }

    /**
     * Get product detail.
     */
    #[UrlParam('id', 'integer', 'The product ID.', example: 1)]
    #[Response(status: 200, content: ['success' => true, 'message' => 'Data retrieved successfully', 'data' => ['id' => 1, 'code' => 'ITM-ABC-1234', 'name' => 'Laptop Dell Latitude', 'category' => ['id' => 1, 'name' => 'Elektronik']]])]
    #[Response(status: 404, content: ['message' => 'No query results for model [App\\Models\\Product].'])]
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        return $this->success($product->load(['category', 'borrowingDetails.borrowing']));
    }

    /**
     * Update a product.
     *
     * Requires the Admin or Staff role. All fields are optional (partial update).
     */
    #[UrlParam('id', 'integer', 'The product ID.', example: 1)]
    #[BodyParam('code', 'string', 'Unique product code.', required: false, example: 'ITM-ABC-1234')]
    #[BodyParam('name', 'string', 'Product name.', required: false, example: 'Laptop Dell Latitude')]
    #[BodyParam('category_id', 'integer', 'Existing category ID.', required: false, example: 1)]
    #[BodyParam('stock', 'integer', 'Stock count.', required: false, example: 12)]
    #[BodyParam('location', 'string', 'Storage location.', required: false, example: 'Gedung A, Lantai 2')]
    #[BodyParam('condition', 'string', 'Product condition.', required: false, enum: ['good', 'lightly_damaged', 'heavily_damaged'], example: 'good')]
    #[Response(status: 200, content: ['success' => true, 'message' => 'Product updated successfully', 'data' => ['id' => 1, 'name' => 'Laptop Dell Latitude']])]
    #[Response(status: 403, content: ['success' => false, 'message' => 'This action is unauthorized.'])]
    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:50', 'unique:products,code,'.$product->id],
            'name' => ['sometimes', 'string', 'max:150'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:100'],
            'condition' => ['sometimes', 'in:good,lightly_damaged,heavily_damaged'],
        ]);

        $product->update($validated);

        return $this->success($product->fresh()->load('category'), 'Product updated successfully');
    }

    /**
     * Soft-delete a product.
     *
     * Requires the Admin or Staff role.
     */
    #[UrlParam('id', 'integer', 'The product ID.', example: 1)]
    #[Response(status: 200, content: ['success' => true, 'message' => 'Product deleted successfully', 'data' => null])]
    #[Response(status: 403, content: ['success' => false, 'message' => 'This action is unauthorized.'])]
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return $this->success(null, 'Product deleted successfully');
    }
}
