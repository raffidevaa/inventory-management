<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BorrowingsExport implements WithMultipleSheets
{
    public function __construct(private readonly ?string $status) {}

    public function sheets(): array
    {
        return [
            new BorrowingsSheet($this->status),
            new BorrowingsDetailsSheet($this->status),
            new BorrowingsSummarySheet,
        ];
    }
}
