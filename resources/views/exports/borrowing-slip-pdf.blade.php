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
    padding: 22px 28px 20px;
    vertical-align: middle;
}
.hdr-panel {
    background-color: #590000;
    width: 100px;
    text-align: center;
    vertical-align: middle;
    padding: 22px 12px;
}
.brand-eyebrow {
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: rgba(255,255,255,0.55);
    margin-bottom: 5px;
}
.doc-title {
    font-size: 20px;
    font-weight: bold;
    color: #FFFFFF;
    margin-bottom: 5px;
}
.doc-id {
    font-size: 12px;
    color: rgba(255,255,255,0.70);
    margin-bottom: 10px;
}
.hdr-rule { height: 1px; background-color: rgba(255,255,255,0.18); margin-bottom: 9px; }
.status-pill {
    display: inline;
    font-size: 8px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 3px 10px;
    border-radius: 3px;
}
.pill-borrowed { background-color: rgba(219,234,254,0.25); color: #BFDBFE; border: 1px solid rgba(191,219,254,0.4); }
.pill-returned { background-color: rgba(220,252,231,0.25); color: #BBF7D0; border: 1px solid rgba(187,247,208,0.4); }
.pill-overdue  { background-color: rgba(254,226,226,0.25); color: #FECACA; border: 1px solid rgba(254,202,202,0.4); }
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

/* ── INFO GRID ───────────────────────────────────────── */
.info-section { padding: 0 28px; background-color: #FAFAFA; border-bottom: 1px solid #FFE8E8; }
.info-tbl { width: 100%; border-collapse: collapse; }
.info-tbl td { padding: 11px 12px; border-right: 1px solid #FFE8E8; vertical-align: top; width: 50%; }
.info-tbl td:last-child { border-right: none; }
.info-tbl tr:first-child td { border-bottom: 1px solid #FFE8E8; }
.info-label {
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #9CA3AF;
    margin-bottom: 3px;
}
.info-value { font-size: 11px; font-weight: bold; color: #1F2937; }
.info-value-sub { font-size: 9px; color: #6B7280; margin-top: 1px; }
.overdue-val { color: #B91C1C; }

/* ── NOTES ───────────────────────────────────────────── */
.notes-bar {
    background-color: #FFFBEB;
    border-left: 3px solid #F59E0B;
    padding: 8px 28px;
    font-size: 9px;
    color: #78350F;
    border-bottom: 1px solid #FDE68A;
}

/* ── SECTION TITLE ───────────────────────────────────── */
.section-title {
    padding: 12px 28px 8px;
    font-size: 8px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #8B0000;
    border-bottom: 2px solid #CC0000;
    background-color: #FFFFFF;
}

/* ── ITEMS TABLE ─────────────────────────────────────── */
.items { width: 100%; border-collapse: collapse; }
.items thead th {
    background-color: #8B0000;
    color: #FFFFFF;
    padding: 8px 10px 7px;
    text-align: left;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border-right: 1px solid rgba(255,255,255,0.08);
}
.items thead th:last-child { border-right: none; }
.items tbody td {
    padding: 8px 10px;
    font-size: 9.5px;
    border-bottom: 1px solid #FFE8E8;
    vertical-align: middle;
}
.mono { font-family: DejaVu Sans Mono, monospace; font-size: 8.5px; color: #6B7280; }
.fw { font-weight: bold; }
.muted { color: #6B7280; }
.c { text-align: center; }

.ibdg { font-size: 7.5px; font-weight: bold; padding: 2px 8px; border-radius: 3px; }
.ibdg-borrowed { background-color: #DBEAFE; color: #1D4ED8; }
.ibdg-returned { background-color: #DCFCE7; color: #15803D; }
.ibdg-lost     { background-color: #FEE2E2; color: #B91C1C; }
.ibdg-damaged  { background-color: #FEF9C3; color: #854D0E; }

/* ── SIGNATURES ──────────────────────────────────────── */
.sig-section { padding: 28px 28px 20px; }
.sig-tbl { width: 100%; border-collapse: collapse; }
.sig-tbl td { text-align: center; padding: 0 20px; vertical-align: bottom; }
.sig-line { border-top: 1px solid #374151; padding-top: 7px; margin-top: 44px; }
.sig-name { font-size: 9.5px; font-weight: bold; color: #1F2937; }
.sig-role { font-size: 8px; color: #9CA3AF; margin-top: 2px; }

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
        <div class="doc-title">Borrowing Receipt</div>
        <div class="doc-id">Transaction #{{ $borrowing->id }}</div>
        <div class="hdr-rule"></div>
        <span class="status-pill pill-{{ $borrowing->status }}">{{ ucfirst($borrowing->status) }}</span>
    </td>
    <td class="hdr-panel">
        <span class="logo-glyph">T</span>
        <span class="logo-wordmark">Telkomsel</span>
    </td>
</tr>
</table>
<div class="accent-bar"></div>

{{-- ═══ INFO GRID ═══ --}}
<div class="info-section">
    <table class="info-tbl">
        <tr>
            <td>
                <div class="info-label">Borrower</div>
                <div class="info-value">{{ $borrowing->borrower_name }}</div>
            </td>
            <td>
                <div class="info-label">Recorded By</div>
                <div class="info-value">{{ $borrowing->user?->name ?? '—' }}</div>
                @if ($borrowing->user?->role)
                <div class="info-value-sub">{{ ucfirst($borrowing->user->role->name) }}</div>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <div class="info-label">Borrow Date</div>
                <div class="info-value">{{ $borrowing->borrow_date->format('d F Y') }}</div>
            </td>
            <td>
                <div class="info-label">Due Date</div>
                <div class="info-value {{ $borrowing->isOverdue() ? 'overdue-val' : '' }}">
                    {{ $borrowing->due_date->format('d F Y') }}
                    @if ($borrowing->isOverdue())
                    &nbsp;<span style="font-size:9px; font-weight:normal">(Overdue)</span>
                    @endif
                </div>
                @if ($borrowing->return_date)
                <div class="info-label" style="margin-top:8px">Return Date</div>
                <div class="info-value">{{ $borrowing->return_date->format('d F Y') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

@if ($borrowing->notes)
<div class="notes-bar">
    <strong>Catatan:</strong> {{ $borrowing->notes }}
</div>
@endif

{{-- ═══ ITEMS ═══ --}}
<div class="section-title">Daftar Barang</div>
<table class="items">
    <thead>
        <tr>
            <th style="width:5%; text-align:center">#</th>
            <th style="width:13%">Code</th>
            <th>Product Name</th>
            <th style="width:7%; text-align:center">Qty</th>
            <th style="width:16%">Condition Before</th>
            <th style="width:16%">Condition After</th>
            <th style="width:11%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($borrowing->borrowingDetails as $i => $detail)
        <tr style="background-color:{{ $i % 2 !== 0 ? '#FFF9F9' : '#FFFFFF' }}">
            <td class="c muted">{{ $i + 1 }}</td>
            <td><span class="mono">{{ $detail->product->code }}</span></td>
            <td class="fw">{{ $detail->product->name }}</td>
            <td class="c">{{ $detail->quantity }}</td>
            <td>{{ ucwords(str_replace('_', ' ', $detail->condition_before)) }}</td>
            <td class="muted">{{ $detail->condition_after ? ucwords(str_replace('_', ' ', $detail->condition_after)) : '—' }}</td>
            <td>
                <span class="ibdg ibdg-{{ $detail->item_status }}">{{ ucfirst($detail->item_status) }}</span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ═══ SIGNATURES ═══ --}}
<div class="sig-section">
    <table class="sig-tbl">
        <tr>
            <td>
                <div class="sig-line">
                    <div class="sig-name">{{ $borrowing->borrower_name }}</div>
                    <div class="sig-role">Penerima Barang</div>
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <div class="sig-name">{{ $borrowing->user?->name ?? '—' }}</div>
                    <div class="sig-role">Petugas Inventaris</div>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══ FOOTER ═══ --}}
<div class="ftr">
    <table class="ftr-tbl">
        <tr>
            <td>&copy; {{ $generatedAt->year }} Telkomsel &bull; Inventory Management System</td>
            <td style="text-align:right">Dicetak pada {{ $generatedAt->format('d F Y, H:i') }}</td>
        </tr>
    </table>
</div>

</body>
</html>
