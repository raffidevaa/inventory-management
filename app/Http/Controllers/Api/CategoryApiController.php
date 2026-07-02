<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CategoryApiController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/categories',
        summary: 'List all categories',
        security: [['sanctum' => []]],
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 200, description: 'Category list', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')->get();

        return $this->success($categories);
    }

    #[OA\Post(
        path: '/categories',
        summary: 'Create a new category (Admin/Staff only)',
        security: [['sanctum' => []]],
        tags: ['Categories'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Elektronik'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Perangkat elektronik kantor'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Category created'),
            new OA\Response(response: 403, description: 'Forbidden — Admin/Staff only'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage-inventory');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create($validated);

        return $this->success($category, 'Category created successfully', 201);
    }
}
