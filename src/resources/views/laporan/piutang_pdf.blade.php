<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Piutang</title>
    <style>
        @page { size: 8.5in 11in; margin: 0.33in; }
        body { margin: 0; color: #111; font-family: Helvetica, Arial, sans-serif; font-size: 8pt; }
        @if($dotMatrix)
        body { font-family: Courier, monospace; font-size: 8pt; }
        @endif
        .header { margin-bottom: 10pt; text-align: center; line-height: 1.35; }
        .company { font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .title { font-size: 11pt; font-weight: bold; }
        .meta { font-size: 8pt; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: .6pt solid #222; padding: 3pt; vertical-align: top; overflow-wrap: break-word; }
        th { background: #eee; font-weight: bold; }
        .right { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .total td { background: #eee; font-weight: bold; }
        .section { margin: 8pt 0 3pt; font-weight: bold; }
        .summary { width: 62%; margin-left: auto; }
        .no-border td { border: 0; }
        .invoice-lines { margin: 0; width: 100%; font-family: Courier, monospace; font-size: 7.8pt; line-height: 1.2; white-space: pre-wrap; }
        .invoice-page { page-break-after: always; }
        .invoice-page:last-child { page-break-after: auto; }
        .invoice-table { font-size: 6.4pt; }
        .invoice-table th, .invoice-table td { padding: 2.2pt 2.5pt; line-height: 1.12; }
        .invoice-table th { text-align: center; }
        .invoice-table .invoice-col { width: 14%; }
        .invoice-table .date-col { width: 9%; }
        .invoice-table .customer-col { width: 15%; }
        .invoice-table .status-col { width: 12%; }
        .invoice-table .amount-col { width: 14%; }
        .invoice-grid-lines { margin: 0; width: 100%; font-family: Courier, monospace; font-size: 7.2pt; line-height: 1.12; white-space: pre; }
    </style>
</head>
<body>
@php
    $money = static fn ($value) => number_format((float) $value, 2, ',', '.');
    $titles = [
        'invoice' => 'LAPORAN PIUTANG PER FAKTUR',
        'pelanggan' => 'DAFTAR PELANGGAN PIUTANG',
        'aging' => 'LAPORAN UMUR PIUTANG',
        'mutasi' => 'MUTASI PIUTANG',
        'penambahan' => 'DAFTAR PENAMBAHAN PIUTANG',
        'pembayaran' => 'LAPORAN PEMBAYARAN PIUTANG',
    ];
    $agingLabels = [
        'belum_jatuh_tempo' => 'Belum Jatuh Tempo',
        '1_30' => '1–30 Hari',
        '31_60' => '31–60 Hari',
        '61_90' => '61–90 Hari',
        'lebih_90' => '> 90 Hari',
    ];
@endphp

<div class="header">
    <div class="company">{{ config('app.company_name', config('app.name')) }}</div>
    <div class="title">{{ $titles[$section] ?? 'LAPORAN PIUTANG' }}</div>
    <div class="meta">
        @if(in_array($section, ['mutasi', 'penambahan', 'pembayaran'], true))
            Periode {{ date('d/m/Y', strtotime($tanggalAwal)) }} s/d {{ date('d/m/Y', strtotime($tanggalAkhir)) }}
        @else
            Posisi {{ date('d/m/Y', strtotime($tanggalPosisi)) }}
        @endif
        @if($customer !== '') — Pelanggan: {{ $customer }} @endif
    </div>
</div>

@switch($section)
@case('pelanggan')
    <table>
        <thead><tr><th>Pelanggan</th><th class="right">Invoice</th><th class="right">Nilai</th><th class="right">Terbayar</th><th class="right">Saldo</th></tr></thead>
        <tbody>
        @forelse($customers as $row)
            <tr><td>{{ $row['customer'] }}</td><td class="right">{{ $row['invoice_count'] }}</td><td class="right">{{ $money($row['invoice_amount']) }}</td><td class="right">{{ $money($row['paid']) }}</td><td class="right">{{ $money($row['balance']) }}</td></tr>
        @empty
            <tr><td colspan="5" class="center">Tidak ada data.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr class="total"><td>TOTAL</td><td class="right">{{ $customers->sum('invoice_count') }}</td><td class="right">{{ $money($customers->sum('invoice_amount')) }}</td><td class="right">{{ $money($customers->sum('paid')) }}</td><td class="right">{{ $money($customers->sum('balance')) }}</td></tr></tfoot>
    </table>
    @break

@case('aging')
    @if($agingDetail === 'all')
        <div class="section">SUBTOTAL PER KATEGORI UMUR</div>
        <table>
            <thead><tr><th>Kategori Umur</th><th class="right">Invoice</th><th class="right">Nilai</th><th class="right">Terbayar</th><th class="right">Sisa</th></tr></thead>
            <tbody>
            @foreach($agingLabels as $key => $label)
                @php($subtotal = $agingGroupTotals->get($key, ['count' => 0, 'invoice_amount' => 0, 'paid' => 0, 'balance' => 0]))
                <tr><td>{{ $label }}</td><td class="right">{{ number_format($subtotal['count'] ?? 0, 0, ',', '.') }}</td><td class="right">{{ $money($subtotal['invoice_amount'] ?? 0) }}</td><td class="right">{{ $money($subtotal['paid'] ?? 0) }}</td><td class="right">{{ $money($subtotal['balance'] ?? 0) }}</td></tr>
            @endforeach
            </tbody>
            <tfoot><tr class="total"><td>TOTAL SEMUA UMUR</td><td class="right">{{ number_format($agingGroupTotals->sum('count'), 0, ',', '.') }}</td><td class="right">{{ $money($agingGroupTotals->sum('invoice_amount')) }}</td><td class="right">{{ $money($agingGroupTotals->sum('paid')) }}</td><td class="right">{{ $money($agingGroupTotals->sum('balance')) }}</td></tr></tfoot>
        </table>
        @foreach($agingLabels as $key => $label)
            @php
                $groupRows = $agingGroups->get($key, collect());
                $subtotal = $agingGroupTotals->get($key, ['count' => 0, 'invoice_amount' => 0, 'paid' => 0, 'balance' => 0]);
            @endphp
            <div class="section">{{ $label }}</div>
            <table>
                <thead><tr><th>Invoice</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Pelanggan</th><th class="right">Nilai</th><th class="right">Terbayar</th><th class="right">Sisa</th></tr></thead>
                <tbody>
                @forelse($groupRows as $row)
                    <tr><td>{{ $row['invoice']->NOFAKTUR }}</td><td>{{ $row['invoice']->TGLFAKTUR?->format('d/m/Y') }}</td><td>{{ $row['invoice']->TGLJT?->format('d/m/Y') ?? '-' }}</td><td>{{ $row['invoice']->CUSTOMER }}</td><td class="right">{{ $money($row['invoice_amount']) }}</td><td class="right">{{ $money($row['paid']) }}</td><td class="right">{{ $money($row['balance']) }}</td></tr>
                @empty
                    <tr><td colspan="7" class="center">Tidak ada data.</td></tr>
                @endforelse
                </tbody>
                <tfoot><tr class="total"><td colspan="4">SUBTOTAL {{ strtoupper($label) }} ({{ $subtotal['count'] ?? 0 }} invoice)</td><td class="right">{{ $money($subtotal['invoice_amount'] ?? 0) }}</td><td class="right">{{ $money($subtotal['paid'] ?? 0) }}</td><td class="right">{{ $money($subtotal['balance'] ?? 0) }}</td></tr></tfoot>
            </table>
        @endforeach
    @else
        <table>
            <thead><tr><th>Kelompok Umur</th><th class="right">Saldo</th></tr></thead>
            <tbody>@foreach($agingLabels as $key => $label)<tr><td>{{ $label }}</td><td class="right">{{ $money($aging[$key] ?? 0) }}</td></tr>@endforeach</tbody>
            <tfoot><tr class="total"><td>TOTAL</td><td class="right">{{ $money($aging->sum()) }}</td></tr></tfoot>
        </table>
    @endif
    @break

@case('mutasi')
    <table class="summary">
        <tr><td>Saldo awal</td><td class="right">{{ $money($movement['opening']) }}</td></tr>
        <tr><td>Penambahan invoice</td><td class="right">{{ $money($movement['invoices']) }}</td></tr>
        <tr><td>Pembayaran</td><td class="right">({{ $money($movement['payments']) }})</td></tr>
        <tr><td>Diskon dan retur</td><td class="right">({{ $money($movement['adjustments']) }})</td></tr>
        <tr class="total"><td>Saldo akhir</td><td class="right">{{ $money($movement['ending']) }}</td></tr>
    </table>
    @break

@case('penambahan')
    <table>
        <thead><tr><th>Invoice</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Pelanggan</th><th>Status</th><th class="right">Nilai</th></tr></thead>
        <tbody>
        @forelse($periodInvoices as $row)
            <tr><td>{{ $row['invoice']->NOFAKTUR }}</td><td>{{ $row['invoice']->TGLFAKTUR?->format('d/m/Y') }}</td><td>{{ $row['invoice']->TGLJT?->format('d/m/Y') ?? '-' }}</td><td>{{ $row['invoice']->CUSTOMER }}</td><td>{{ $row['status'] }}</td><td class="right">{{ $money($row['invoice_amount']) }}</td></tr>
        @empty
            <tr><td colspan="6" class="center">Tidak ada data.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr class="total"><td colspan="5">TOTAL</td><td class="right">{{ $money($periodInvoices->sum('invoice_amount')) }}</td></tr></tfoot>
    </table>
    @break

@case('pembayaran')
    <table>
        <thead><tr><th>No. Bayar</th><th>Tanggal</th><th>Pelanggan</th><th>Metode</th><th>Invoice</th><th class="right">Jumlah</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
            <tr><td>{{ $payment->NOBAYAR }}</td><td>{{ $payment->TGLBAYAR?->format('d/m/Y') }}</td><td>{{ $payment->CUSTOMER }}</td><td>{{ $payment->METODE_PEMBAYARAN ?? '-' }}</td><td>{{ $payment->details->pluck('NOFAKTUR')->join(', ') }}</td><td class="right">{{ $money($payment->details->sum('NILAI_BAYAR')) }}</td></tr>
        @empty
            <tr><td colspan="6" class="center">Tidak ada data.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr class="total"><td colspan="5">TOTAL</td><td class="right">{{ $money($payments->sum(fn ($payment) => $payment->details->sum('NILAI_BAYAR'))) }}</td></tr></tfoot>
    </table>
    @break

@default
    @if(!$dotMatrix)
        @php
            $gridBorder = '+'.str_repeat('-', 15).'+'.str_repeat('-', 10).'+'.str_repeat('-', 10).'+'.str_repeat('-', 13).'+'.str_repeat('-', 12).'+'.str_repeat('-', 14).'+'.str_repeat('-', 14).'+'.str_repeat('-', 14).'+';
            $gridHeading = sprintf('|%-15s|%-10s|%-10s|%-13s|%-12s|%14s|%14s|%14s|', 'INVOICE', 'TANGGAL', 'J.TEMPO', 'PELANGGAN', 'STATUS', 'NILAI', 'TERBAYAR', 'SISA');
            $gridRows = [];
            foreach ($invoices as $row) {
                $gridRows[] = sprintf(
                    '|%-15s|%-10s|%-10s|%-13s|%-12s|%14s|%14s|%14s|',
                    mb_strimwidth((string) $row['invoice']->NOFAKTUR, 0, 15, ''),
                    $row['invoice']->TGLFAKTUR?->format('d/m/Y') ?? '-',
                    $row['invoice']->TGLJT?->format('d/m/Y') ?? '-',
                    mb_strimwidth((string) $row['invoice']->CUSTOMER, 0, 13, ''),
                    mb_strimwidth((string) $row['status'], 0, 12, ''),
                    $money($row['invoice_amount']),
                    $money($row['paid']),
                    $money($row['balance']),
                );
            }
            $standardPages = array_chunk($gridRows, 68);
            if ($standardPages === []) $standardPages = [[]];
        @endphp
        @foreach($standardPages as $pageRows)
            @php
                $gridPageLines = [$gridBorder, $gridHeading, $gridBorder, ...$pageRows];
                if ($loop->last) {
                    $gridPageLines[] = $gridBorder;
                    $gridPageLines[] = sprintf('|%-64s|%14s|%14s|%14s|', 'TOTAL ('.$invoiceTotals['count'].' invoice)', $money($invoiceTotals['invoice_amount']), $money($invoiceTotals['paid']), $money($invoiceTotals['balance']));
                }
                $gridPageLines[] = $gridBorder;
            @endphp
            <div class="invoice-page"><pre class="invoice-grid-lines">{{ implode("\n", $gridPageLines) }}</pre></div>
        @endforeach
    @else
    @php
        $invoiceHeading = sprintf('%-16s %-10s %-10s %-12s %-12s %15s %15s %15s', 'INVOICE', 'TANGGAL', 'J.TEMPO', 'PELANGGAN', 'STATUS', 'NILAI', 'TERBAYAR', 'SISA');
        $invoiceSeparator = str_repeat('-', 112);
        $invoiceRows = [];
        foreach ($invoices as $row) {
            $invoiceRows[] = sprintf(
                '%-16s %-10s %-10s %-12s %-12s %15s %15s %15s',
                mb_strimwidth((string) $row['invoice']->NOFAKTUR, 0, 16, ''),
                $row['invoice']->TGLFAKTUR?->format('d/m/Y') ?? '-',
                $row['invoice']->TGLJT?->format('d/m/Y') ?? '-',
                mb_strimwidth((string) $row['invoice']->CUSTOMER, 0, 12, ''),
                mb_strimwidth((string) $row['status'], 0, 12, ''),
                $money($row['invoice_amount']),
                $money($row['paid']),
                $money($row['balance']),
            );
        }
        if ($invoices->isEmpty()) {
            $invoiceRows[] = 'Tidak ada data.';
        }
        $invoiceTotalLine = sprintf(
            '%-76s %15s %15s %15s',
            'TOTAL ('.$invoiceTotals['count'].' invoice)',
            $money($invoiceTotals['invoice_amount']),
            $money($invoiceTotals['paid']),
            $money($invoiceTotals['balance']),
        );
        // Pemenggalan eksplisit mencegah DomPDF menghitung ulang ribuan baris
        // saat menentukan pergantian halaman.
        $invoicePages = array_chunk($invoiceRows, 70);
    @endphp
    @foreach($invoicePages as $pageIndex => $pageRows)
        @php
            $pageLines = [$invoiceHeading, $invoiceSeparator, ...$pageRows];
            if ($loop->last) {
                $pageLines[] = $invoiceSeparator;
                $pageLines[] = $invoiceTotalLine;
            }
        @endphp
        <div class="invoice-page"><pre class="invoice-lines">{{ implode("\n", $pageLines) }}</pre></div>
    @endforeach
    @endif
@endswitch
</body>
</html>
