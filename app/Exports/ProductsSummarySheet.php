<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsSummarySheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Summary';
    }

    public function collection()
    {
        return Category::withCount('products')
            ->withSum('products', 'stock')
            ->withSum('products', 'stock_available')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return ['Category', 'Product Count', 'Total Stock', 'Available Stock'];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->products_count,
            $row->products_sum_stock ?? 0,
            $row->products_sum_stock_available ?? 0,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']]],
        ];
    }
}
