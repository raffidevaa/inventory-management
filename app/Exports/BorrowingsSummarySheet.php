<?php

namespace App\Exports;

use App\Models\Borrowing;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowingsSummarySheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Summary';
    }

    public function collection(): Collection
    {
        return Borrowing::selectRaw("TO_CHAR(borrow_date, 'YYYY-MM') as month, COUNT(*) as total,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_count,
                SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function headings(): array
    {
        return ['Month', 'Total Transactions', 'Returned', 'Overdue'];
    }

    public function map($row): array
    {
        return [
            $row->month,
            $row->total,
            $row->returned_count,
            $row->overdue_count,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']]],
        ];
    }
}
