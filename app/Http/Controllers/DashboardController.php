<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_borrowed' => Borrowing::where('status', 'borrowed')->count(),
            'total_overdue' => Borrowing::where('status', 'overdue')->count(),
            'stock_available' => Product::sum('stock_available'),
        ];

        $monthlyBorrowings = Borrowing::selectRaw('EXTRACT(MONTH FROM borrow_date) as month, COUNT(*) as total')
            ->whereYear('borrow_date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return view('dashboard', compact('stats', 'monthlyBorrowings'));
    }
}
