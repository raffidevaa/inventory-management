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
    letter-spacing: 0.3px;
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
.mono { font-family: DejaVu Sans Mono, monospace; font-size: 8.5px; color: #6B7280; }
.fw { font-weight: bold; }
.muted { color: #6B7280; }
.c { text-align: center; }

/* Condition badges */
.bdg { font-size: 7.5px; font-weight: bold; padding: 2px 7px; border-radius: 3px; }
.bdg-g { background-color: #DCFCE7; color: #15803D; }
.bdg-l { background-color: #FEF9C3; color: #854D0E; }
.bdg-h { background-color: #FEE2E2; color: #B91C1C; }

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
.sum-num { font-size: 26px; font-weight: bold; color: #CC0000; }
.sum-num-alt { color: #8B0000; }
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
        <div class="doc-title">Inventory Report</div>
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

@if ($search || $category)
<div class="filter-strip">
    <strong>Active filters &mdash;</strong>
    @if ($search) Search: &ldquo;{{ $search }}&rdquo;@endif
    @if ($search && $category) &nbsp;&bull;&nbsp; @endif
    @if ($category) Category: {{ $category->name }}@endif
</div>
@endif

{{-- ═══ TABLE ═══ --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:4%; text-align:center">#</th>
            <th style="width:11%">Code</th>
            <th>Product Name</th>
            <th style="width:13%">Category</th>
            <th style="width:9%; text-align:center">Total Stock</th>
            <th style="width:9%; text-align:center">Available</th>
            <th style="width:12%">Location</th>
            <th style="width:13%">Condition</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($products as $i => $product)
        <tr style="background-color:{{ $i % 2 !== 0 ? '#FFF9F9' : '#FFFFFF' }}">
            <td class="c muted">{{ $i + 1 }}</td>
            <td><span class="mono">{{ $product->code }}</span></td>
            <td class="fw">{{ $product->name }}</td>
            <td class="muted">{{ $product->category?->name ?? '—' }}</td>
            <td class="c">{{ $product->stock }}</td>
            <td class="c fw" style="color:{{ $product->stock_available > 0 ? '#CC0000' : '#9CA3AF' }}">
                {{ $product->stock_available }}
            </td>
            <td class="muted">{{ $product->location ?? '—' }}</td>
            <td>
                @if ($product->condition === 'good')
                    <span class="bdg bdg-g">Good</span>
                @elseif ($product->condition === 'lightly_damaged')
                    <span class="bdg bdg-l">Lightly Damaged</span>
                @else
                    <span class="bdg bdg-h">Heavily Damaged</span>
                @endif
            </td>
        </tr>
        @empty
        <tr style="background:#FFFFFF">
            <td colspan="8" style="text-align:center; padding:24px; color:#9CA3AF">
                No products found for the selected filters.
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
                <div class="sum-num">{{ $products->count() }}</div>
                <div class="sum-lbl">Total Products</div>
            </td>
            <td>
                <div class="sum-num">{{ $products->sum('stock') }}</div>
                <div class="sum-lbl">Total Stock</div>
            </td>
            <td>
                <div class="sum-num">{{ $products->sum('stock_available') }}</div>
                <div class="sum-lbl">Available Stock</div>
            </td>
            <td>
                <div class="sum-num sum-num-alt">{{ $products->sum('stock') - $products->sum('stock_available') }}</div>
                <div class="sum-lbl">Currently Borrowed</div>
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
