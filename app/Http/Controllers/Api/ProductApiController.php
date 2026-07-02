<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductApiController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/products',
        summary: 'List all products (paginated)',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Search by name or code', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', description: 'Filter by category ID', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated product list', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with('category')
            ->when($request->search, fn($q, $s) => $q->where(fn($inner) => $inner
                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($s) . '%'])
                ->orWhereRaw('LOWER(code) LIKE ?', ['%' . strtolower($s) . '%'])))
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->latest()
            ->paginate(15);

        return $this->paginatedSuccess($products);
    }

    #[OA\Post(
        path: '/products',
        summary: 'Create a new product (Admin/Staff only)',
        security: [['sanctum' => []]],
        tags: ['Products'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code', 'name', 'category_id', 'stock', 'condition'],
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'ITM-ABC-1234'),
                    new OA\Property(property: 'name', type: 'string', example: 'Laptop Dell Latitude'),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'stock', type: 'integer', example: 10),
                    new OA\Property(property: 'location', type: 'string', example: 'Gedung A, Lantai 2'),
                    new OA\Property(property: 'condition', type: 'string', enum: ['good', 'lightly_damaged', 'heavily_damaged'], example: 'good'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Product created'),
            new OA\Response(response: 403, description: 'Forbidden — Admin/Staff only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

    #[OA\Get(
        path: '/products/{id}',
        summary: 'Get product detail',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product detail'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        return $this->success($product->load(['category', 'borrowingDetails.borrowing']));
    }

    #[OA\Put(
        path: '/products/{id}',
        summary: 'Update a product (Admin/Staff only)',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'category_id', type: 'integer'),
                    new OA\Property(property: 'stock', type: 'integer'),
                    new OA\Property(property: 'location', type: 'string'),
                    new OA\Property(property: 'condition', type: 'string', enum: ['good', 'lightly_damaged', 'heavily_damaged']),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Product updated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:50', 'unique:products,code,' . $product->id],
            'name' => ['sometimes', 'string', 'max:150'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:100'],
            'condition' => ['sometimes', 'in:good,lightly_damaged,heavily_damaged'],
        ]);

        $product->update($validated);

        return $this->success($product->fresh()->load('category'), 'Product updated successfully');
    }

    #[OA\Delete(
        path: '/products/{id}',
        summary: 'Soft-delete a product (Admin/Staff only)',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return $this->success(null, 'Product deleted successfully');
    }
}
