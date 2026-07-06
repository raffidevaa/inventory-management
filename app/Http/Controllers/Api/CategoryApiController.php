<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Categories', 'Manage product categories.')]
class CategoryApiController extends Controller
{
    use ApiResponse;

    /**
     * List all categories.
     */
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Data retrieved successfully',
        'data' => [['id' => 1, 'name' => 'Elektronik', 'description' => 'Perangkat elektronik kantor', 'products_count' => 12]],
    ])]
    #[Response(status: 401, content: ['message' => 'Unauthenticated.'])]
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')->get();

        return $this->success($categories);
    }

    /**
     * Create a new category.
     *
     * Requires the Admin or Staff role.
     */
    #[BodyParam('name', 'string', 'Category name.', example: 'Elektronik')]
    #[BodyParam('description', 'string', 'Category description.', required: false, example: 'Perangkat elektronik kantor')]
    #[Response(status: 201, content: ['success' => true, 'message' => 'Category created successfully', 'data' => ['id' => 1, 'name' => 'Elektronik', 'description' => 'Perangkat elektronik kantor']])]
    #[Response(status: 403, content: ['success' => false, 'message' => 'This action is unauthorized.'])]
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
