<?php

namespace App\Exports;

use App\Models\BorrowingDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowingsDetailsSheet implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly ?string $status) {}

    public function title(): string
    {
        return 'Details';
    }

    public function query()
    {
        return BorrowingDetail::with(['borrowing.user', 'product'])
            ->when($this->status, fn ($q) => $q->whereHas('borrowing', fn ($b) => $b->where('status', $this->status)));
    }

    public function headings(): array
    {
        return [
            'Borrowing ID', 'Borrower Name', 'Borrow Date',
            'Product Code', 'Product Name', 'Qty',
            'Condition Before', 'Condition After', 'Item Status',
        ];
    }

    public function map($detail): array
    {
        return [
            '#'.$detail->borrowing_id,
            $detail->borrowing->borrower_name,
            $detail->borrowing->borrow_date->format('d M Y'),
            $detail->product->code,
            $detail->product->name,
            $detail->quantity,
            ucwords(str_replace('_', ' ', $detail->condition_before)),
            $detail->condition_after ? ucwords(str_replace('_', ' ', $detail->condition_after)) : '—',
            ucfirst($detail->item_status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']]],
        ];
    }
}
