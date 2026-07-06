<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsSheet implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private readonly ?string $search,
        private readonly ?string $categoryId,
    ) {}

    public function title(): string
    {
        return 'Products';
    }

    public function query()
    {
        return Product::with('category')
            ->when($this->search, fn ($q, $s) => $q->where(fn ($inner) => $inner
                ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($s).'%'])
                ->orWhereRaw('LOWER(code) LIKE ?', ['%'.strtolower($s).'%'])))
            ->when($this->categoryId, fn ($q, $id) => $q->where('category_id', $id))
            ->latest();
    }

    public function headings(): array
    {
        return ['#', 'Product Code', 'Product Name', 'Category', 'Total Stock', 'Available Stock', 'Location', 'Condition'];
    }

    public function map($product): array
    {
        static $i = 0;
        $i++;

        return [
            $i,
            $product->code,
            $product->name,
            $product->category?->name ?? '—',
            $product->stock,
            $product->stock_available,
            $product->location ?? '—',
            ucwords(str_replace('_', ' ', $product->condition)),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']]],
        ];
    }
}
