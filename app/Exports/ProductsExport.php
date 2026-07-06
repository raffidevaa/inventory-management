<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductsExport implements WithMultipleSheets
{
    public function __construct(
        private readonly ?string $search,
        private readonly ?string $categoryId,
    ) {}

    public function sheets(): array
    {
        return [
            new ProductsSheet($this->search, $this->categoryId),
            new ProductsSummarySheet,
        ];
    }
}
