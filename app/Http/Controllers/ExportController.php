<?php

namespace App\Http\Controllers;

use App\Exports\BorrowingsExport;
use App\Exports\ProductsExport;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function productsPdf()
    {
        $this->authorize('view-reports');

        $products = Product::with('category')
            ->when(request('search'), fn ($q, $s) => $q->where(fn ($inner) => $inner
                ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($s).'%'])
                ->orWhereRaw('LOWER(code) LIKE ?', ['%'.strtolower($s).'%'])))
            ->when(request('category'), fn ($q, $id) => $q->where('category_id', $id))
            ->latest()
            ->get();

        $category = request('category') ? Category::find(request('category')) : null;

        $pdf = Pdf::loadView('exports.products-pdf', [
            'products' => $products,
            'category' => $category,
            'search' => request('search'),
            'generatedBy' => auth()->user()->name,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('inventory-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function productsExcel()
    {
        $this->authorize('view-reports');

        return Excel::download(
            new ProductsExport(request('search'), request('category')),
            'inventory-report-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function borrowingsPdf()
    {
        $this->authorize('view-reports');

        $borrowings = Borrowing::with(['user', 'borrowingDetails.product'])
            ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->get();

        $pdf = Pdf::loadView('exports.borrowings-pdf', [
            'borrowings' => $borrowings,
            'status' => request('status'),
            'generatedBy' => auth()->user()->name,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('borrowings-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function borrowingsExcel()
    {
        $this->authorize('view-reports');

        return Excel::download(
            new BorrowingsExport(request('status')),
            'borrowings-report-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function borrowingSlip(Borrowing $borrowing)
    {
        $this->authorize('view', $borrowing);

        $borrowing->load(['user', 'borrowingDetails.product']);

        $pdf = Pdf::loadView('exports.borrowing-slip-pdf', [
            'borrowing' => $borrowing,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('borrowing-slip-'.$borrowing->id.'-'.now()->format('Y-m-d').'.pdf');
    }
}
