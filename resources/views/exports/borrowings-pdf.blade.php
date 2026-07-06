<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 0; }

* { margin: 0; padding: 0; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 10px;
    color: #1F2937;
    background: #FFFFFF;
}

/* ── HEADER ─────────────────────────────────────────── */
.hdr { width: 100%; border-collapse: collapse; }
.hdr-main {
    background-color: #CC0000;
    padding: 20px 28px 18px;
    vertical-align: middle;
}
.hdr-panel {
    background-color: #590000;
    width: 92px;
    text-align: center;
    vertical-align: middle;
    padding: 20px 10px;
}
.brand-eyebrow {
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: rgba(255,255,255,0.55);
    margin-bottom: 8px;
}
.doc-title {
    font-size: 22px;
    font-weight: bold;
    color: #FFFFFF;
    margin-bottom: 10px;
}
.hdr-rule { height: 1px; background-color: rgba(255,255,255,0.18); margin-bottom: 9px; }
.meta-row { font-size: 8.5px; color: rgba(255,255,255,0.78); }
.meta-bold { font-weight: bold; color: rgba(255,255,255,0.95); }
.logo-glyph {
    font-size: 38px;
    font-weight: bold;
    color: rgba(255,255,255,0.88);
    line-height: 1;
    display: block;
    margin-bottom: 5px;
}
.logo-wordmark {
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(255,255,255,0.45);
}
.accent-bar { height: 3px; background-color: #7A0000; }

/* ── FILTER STRIP ────────────────────────────────────── */
.filter-strip {
    background-color: #FFF5F5;
    border-left: 3px solid #CC0000;
    padding: 7px 28px;
    font-size: 8.5px;
    color: #7A0000;
}

/* ── DATA TABLE ──────────────────────────────────────── */
.data { width: 100%; border-collapse: collapse; }
.data thead th {
    background-color: #8B0000;
    color: #FFFFFF;
    padding: 9px 10px 8px;
    text-align: left;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-weight: bold;
    border-right: 1px solid rgba(255,255,255,0.08);
}
.data thead th:last-child { border-right: none; }
.data tbody td {
    padding: 7px 10px;
    font-size: 9.5px;
    border-bottom: 1px solid #FFE8E8;
    vertical-align: middle;
}
.fw { font-weight: bold; }
.muted { color: #6B7280; }
.c { text-align: center; }

/* Status badges */
.bdg { font-size: 7.5px; font-weight: bold; padding: 2px 8px; border-radius: 3px; }
.bdg-borrowed { background-color: #DBEAFE; color: #1D4ED8; }
.bdg-returned { background-color: #DCFCE7; color: #15803D; }
.bdg-overdue  { background-color: #FEE2E2; color: #B91C1C; }

/* ── SUMMARY ─────────────────────────────────────────── */
.summary { border-top: 3px solid #CC0000; background-color: #FAFAFA; }
.sum-tbl { width: 100%; border-collapse: collapse; }
.sum-tbl td {
    text-align: center;
    padding: 14px 10px 12px;
    border-right: 1px solid #FFE8E8;
    vertical-align: top;
}
.sum-tbl td:last-child { border-right: none; }
.sum-num { font-size: 26px; font-weight: bold; }
.sum-lbl { font-size: 7px; text-transform: uppercase; letter-spacing: 1px; color: #9CA3AF; margin-top: 3px; }

/* ── FOOTER ──────────────────────────────────────────── */
.ftr { border-top: 1px solid #FFE8E8; padding: 8px 28px; }
.ftr-tbl { width: 100%; border-collapse: collapse; }
.ftr-tbl td { font-size: 7.5px; color: #9CA3AF; }
</style>
</head>
<body>

{{-- ═══ HEADER ═══ --}}
<table class="hdr" width="100%">
<tr>
    <td class="hdr-main">
        <div class="brand-eyebrow">Telkomsel &nbsp;&bull;&nbsp; Inventory Management System</div>
        <div class="doc-title">Borrowing Report</div>
        <div class="hdr-rule"></div>
        <div class="meta-row">
            Generated on {{ $generatedAt->format('d F Y, H:i') }}
            &nbsp;&bull;&nbsp;
            Prepared by <span class="meta-bold">{{ $generatedBy }}</span>
        </div>
    </td>
    <td class="hdr-panel">
        <span class="logo-glyph">T</span>
        <span class="logo-wordmark">Telkomsel</span>
    </td>
</tr>
</table>
<div class="accent-bar"></div>

@if ($status)
<div class="filter-strip">
    <strong>Active filter &mdash;</strong> Status: {{ ucfirst($status) }}
</div>
@endif

{{-- ═══ TABLE ═══ --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:4%; text-align:center">#</th>
            <th style="width:6%">ID</th>
            <th style="width:17%">Borrower Name</th>
            <th style="width:14%">Recorded By</th>
            <th style="width:10%">Borrow Date</th>
            <th style="width:10%">Due Date</th>
            <th style="width:10%">Return Date</th>
            <th style="width:9%; text-align:center">Status</th>
            <th style="width:7%; text-align:center">Items</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($borrowings as $i => $borrowing)
        <tr style="background-color:{{ $i % 2 !== 0 ? '#FFF9F9' : '#FFFFFF' }}">
            <td class="c muted">{{ $i + 1 }}</td>
            <td class="muted">#{{ $borrowing->id }}</td>
            <td class="fw">{{ $borrowing->borrower_name }}</td>
            <td class="muted">{{ $borrowing->user?->name ?? '—' }}</td>
            <td>{{ $borrowing->borrow_date->format('d M Y') }}</td>
            <td style="color:{{ $borrowing->status === 'overdue' ? '#B91C1C' : 'inherit' }}; font-weight:{{ $borrowing->status === 'overdue' ? 'bold' : 'normal' }}">
                {{ $borrowing->due_date->format('d M Y') }}
            </td>
            <td class="muted">{{ $borrowing->return_date?->format('d M Y') ?? '—' }}</td>
            <td class="c">
                <span class="bdg bdg-{{ $borrowing->status }}">{{ ucfirst($borrowing->status) }}</span>
            </td>
            <td class="c">{{ $borrowing->borrowingDetails->count() }}</td>
        </tr>
        @empty
        <tr style="background:#FFFFFF">
            <td colspan="9" style="text-align:center; padding:24px; color:#9CA3AF">
                No borrowing records found.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- ═══ SUMMARY ═══ --}}
<div class="summary">
    <table class="sum-tbl">
        <tr>
            <td>
                <div class="sum-num" style="color:#CC0000">{{ $borrowings->count() }}</div>
                <div class="sum-lbl">Total Transactions</div>
            </td>
            <td>
                <div class="sum-num" style="color:#1D4ED8">{{ $borrowings->where('status','borrowed')->count() }}</div>
                <div class="sum-lbl">Borrowed</div>
            </td>
            <td>
                <div class="sum-num" style="color:#15803D">{{ $borrowings->where('status','returned')->count() }}</div>
                <div class="sum-lbl">Returned</div>
            </td>
            <td>
                <div class="sum-num" style="color:#B91C1C">{{ $borrowings->where('status','overdue')->count() }}</div>
                <div class="sum-lbl">Overdue</div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══ FOOTER ═══ --}}
<div class="ftr">
    <table class="ftr-tbl">
        <tr>
            <td>&copy; {{ $generatedAt->year }} Telkomsel &bull; Inventory Management System</td>
            <td style="text-align:right">Dokumen ini bersifat rahasia dan hanya untuk penggunaan internal.</td>
        </tr>
    </table>
</div>

</body>
</html>
