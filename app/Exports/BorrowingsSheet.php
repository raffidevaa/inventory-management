<?php

namespace App\Exports;

use App\Models\Borrowing;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowingsSheet implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly ?string $status) {}

    public function title(): string
    {
        return 'Borrowings';
    }

    public function query()
    {
        return Borrowing::with(['user', 'borrowingDetails'])
            ->when($this->status, fn ($q, $s) => $q->where('status', $s))
            ->latest();
    }

    public function headings(): array
    {
        return ['ID', 'Borrower Name', 'Recorded By', 'Borrow Date', 'Due Date', 'Return Date', 'Status', 'Items'];
    }

    public function map($borrowing): array
    {
        return [
            '#'.$borrowing->id,
            $borrowing->borrower_name,
            $borrowing->user?->name ?? '—',
            $borrowing->borrow_date->format('d M Y'),
            $borrowing->due_date->format('d M Y'),
            $borrowing->return_date?->format('d M Y') ?? '—',
            ucfirst($borrowing->status),
            $borrowing->borrowingDetails->count().' item(s)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']]],
        ];
    }
}
