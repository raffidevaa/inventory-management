<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class DashboardApiController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/dashboard/summary',
        summary: 'Dashboard statistics summary',
        security: [['sanctum' => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Stats summary',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'total_products', type: 'integer', example: 30),
                            new OA\Property(property: 'total_borrowed', type: 'integer', example: 5),
                            new OA\Property(property: 'total_overdue', type: 'integer', example: 1),
                            new OA\Property(property: 'stock_available', type: 'integer', example: 245),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function summary(): JsonResponse
    {
        $stats = [
            'total_products' => Product::count(),
            'total_borrowed' => Borrowing::where('status', 'borrowed')->count(),
            'total_overdue' => Borrowing::where('status', 'overdue')->count(),
            'stock_available' => Product::sum('stock_available'),
        ];

        return $this->success($stats);
    }

    #[OA\Get(
        path: '/dashboard/chart',
        summary: 'Monthly borrowing chart data for the current year',
        security: [['sanctum' => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Monthly borrowing counts keyed by month number (1–12)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'year', type: 'integer', example: 2026),
                            new OA\Property(property: 'monthly', type: 'object'),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function chart(): JsonResponse
    {
        $monthly = Borrowing::selectRaw('EXTRACT(MONTH FROM borrow_date)::int AS month, COUNT(*) AS total')
            ->whereYear('borrow_date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return $this->success([
            'year' => now()->year,
            'monthly' => $monthly,
        ]);
    }
}
