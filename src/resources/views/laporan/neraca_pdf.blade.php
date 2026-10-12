<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Neraca</title>
    <style>
        @page {
            size: {{ ($dotMatrix ?? false) ? '8.5in 13in' : '8.5in 11in' }} portrait;
            margin: {{ ($dotMatrix ?? false) ? '0.3in' : '0.4in' }};
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12pt;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        @if($dotMatrix ?? false)
        * { font-family: "DejaVu Sans Mono", monospace !important; color: #000 !important; background: transparent !important; }
        body { font-size: 9pt !important; }
        table th, table td { border-color: #000 !important; padding: 3px 4px !important; }
        @endif

        h1,
        h2,
        p {
            margin: 0;
            padding: 0;
        }

        h1 {
            font-size: 16px;
            text-align: center;
            font-weight: 700;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 14px;
            text-align: center;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .muted {
            color: #6b7280;
            text-align: center;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse !important;
            margin-top: 10px;
            border: 1px solid #000 !important;
        }

        th,
        td {
            border: 1px solid #000 !important;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        th.code-heading,
        td.code-col {
            text-align: left !important;
            white-space: nowrap;
        }

        .amount,
        th.amount {
            text-align: right;
        }

        .subtotal-row td,
        .total-row td {
            border-top: 1px solid #000;
            font-weight: 700;
        }

        .total-row td {
            border-top-width: 2px;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .section-title {
            background: #e5e7eb;
            font-weight: bold;
        }

        .subtotal {
            background: #f9fafb;
        }

        .subtotal {
            background: #f9fafb;
        }

        .total {
            background: #dbeafe;
        }

        .section-title td {
            font-weight: 700;
        }

        .amount {
            text-align: right;
        }

        .subtotal-row td {
            border-top: 1px solid #000;
            font-weight: 700;
        }

        .total-row td {
            border-top: 2px solid #000;
            font-weight: 700;
        }

        .subtotal-row td {
            border-top: 1px solid #000;
            font-weight: 700;
        }

        .total-row td {
            border-top: 2px solid #000;
            font-weight: 700;
        }

        .indent {
            display: inline-block;
        }

        /* ensure amounts align right in PDF */
        td.amount,
        th.amount {
            text-align: right;
        }

        .amount .currency,
        .amount .num,
        td.amount .currency,
        td.amount .num {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    @php
    $monthNames = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];
    $isMonthYearPeriod = ($period_type ?? 'date_range') === 'month_year';
    $periodeMonth = str_pad((string) ($month ?? date('m', strtotime($from_date))), 2, '0', STR_PAD_LEFT);
    $periodeYear = (string) ($year ?? date('Y', strtotime($from_date)));
    $periodeLabel = $isMonthYearPeriod
        ? strtoupper(($monthNames[$periodeMonth] ?? date('F', strtotime($from_date))) . ' ' . $periodeYear)
        : (date('j-m-Y', strtotime($from_date)) . ' s/d ' . date('j-m-Y', strtotime($to_date)));
    @endphp

    <h1>{{ config('app.company_name') }}</h1>
    <h2>NERACA</h2>
    <p class="muted">Periode: {{ $periodeLabel }}</p>
    @if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI')
    <p class="muted">Status: TERKUNCI</p>
    @endif

    @php
    $displayAmount = function ($value) {
    if ($value < 0) {
        $num=number_format(abs($value), 2, ',' , '.' );
        return '<span class="currency">(</span>&nbsp;<span class="num">' . $num . ')</span>' ;
        }

        $num=number_format($value, 2, ',' , '.' );
        return '<span class="num">' . $num . '</span>' ;
        };
        @endphp

        <table>
        <thead>
            <tr>
                @if($show_code ?? true)
                <th class="code-heading" style="width:8%">Kode</th>
                @endif
                <th>Nama Akun</th>
                <th style="width:46%" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-title">
                <td colspan="{{ ($show_code ?? true) ? 3 : 2 }}">AKTIVA</td>
            </tr>
            @foreach($asset_tree ?? [] as $node)
            @include('laporan._coa_tree_rows', ['nodes' => [$node], 'level' => 0, 'parentId' => 'section-aktiva', 'show_code' => $show_code ?? true])
            @endforeach
            <tr class="subtotal-row">
                <td colspan="{{ ($show_code ?? true) ? 2 : 1 }}" class="fw-bold">TOTAL AKTIVA</td>
                <td class="amount">{!! $displayAmount($total_asset ?? 0) !!}</td>
            </tr>

            <tr class="section-title subtotal-row">
                <td colspan="{{ ($show_code ?? true) ? 3 : 2 }}">PASIVA</td>
            </tr>
            <tr class="section-title">
                <td colspan="{{ ($show_code ?? true) ? 3 : 2 }}">KEWAJIBAN</td>
            </tr>
            @foreach($liability_tree ?? [] as $node)
            @include('laporan._coa_tree_rows', ['nodes' => [$node], 'level' => 0, 'parentId' => 'section-kewajiban', 'show_code' => $show_code ?? true])
            @endforeach
            <tr class="subtotal-row">
                <td colspan="{{ ($show_code ?? true) ? 2 : 1 }}" class="fw-bold">Total Kewajiban</td>
                <td class="amount">{!! $displayAmount($total_kewajiban ?? 0) !!}</td>
            </tr>

            <tr class="section-title">
                <td colspan="{{ ($show_code ?? true) ? 3 : 2 }}">EKUITAS</td>
            </tr>
            @foreach($equity_tree ?? [] as $node)
            @include('laporan._coa_tree_rows', ['nodes' => [$node], 'level' => 0, 'parentId' => 'section-ekuitas', 'show_code' => $show_code ?? true])
            @endforeach
            @if(($laba_berjalan ?? 0) != 0)
            <tr>
                @if($show_code ?? true)
                <td></td>
                @endif
                <td class="fw-bold">Laba Berjalan</td>
                <td class="text-right fw-bold">{!! $displayAmount($laba_berjalan ?? 0) !!}</td>
            </tr>
            @endif
            <tr class="subtotal-row">
                <td colspan="{{ ($show_code ?? true) ? 2 : 1 }}" class="fw-bold">Total Ekuitas</td>
                <td class="amount">{!! $displayAmount($total_ekuitas ?? 0) !!}</td>
            </tr>

            <tr class="total-row">
                <td colspan="{{ ($show_code ?? true) ? 2 : 1 }}" class="fw-bold">TOTAL PASIVA</td>
                <td class="amount">{!! $displayAmount($total_pasiva ?? 0) !!}</td>
            </tr>
        </tbody>
        </table>
</body>

</html>
