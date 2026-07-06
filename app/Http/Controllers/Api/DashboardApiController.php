<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Dashboard', 'Aggregated statistics for the dashboard.')]
class DashboardApiController extends Controller
{
    use ApiResponse;

    /**
     * Dashboard statistics summary.
     */
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Data retrieved successfully',
        'data' => ['total_products' => 30, 'total_borrowed' => 5, 'total_overdue' => 1, 'stock_available' => 245],
    ])]
    #[Response(status: 401, content: ['message' => 'Unauthenticated.'])]
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

    /**
     * Monthly borrowing chart data for the current year.
     *
     * Returns borrowing counts keyed by month number (1–12).
     */
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Data retrieved successfully',
        'data' => ['year' => 2026, 'monthly' => ['1' => 4, '2' => 7, '7' => 12]],
    ])]
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
