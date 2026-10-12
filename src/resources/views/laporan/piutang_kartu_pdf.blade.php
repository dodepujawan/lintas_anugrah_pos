<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Piutang Per Faktur</title>
    <style>
        @page { size: 8.5in 11in; margin: 0.35in; }
        body { margin: 0; color: #111; font-family: Helvetica, Arial, sans-serif; font-size: 8.2pt; }
        @if($dotMatrix)
        body { font-family: Courier, monospace; font-size: 8pt; }
        @endif
        .header { margin-bottom: 10pt; text-align: center; line-height: 1.35; }
        .company { font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .title { font-size: 11pt; font-weight: bold; }
        .meta { font-size: 8pt; }
        .card { page-break-inside: avoid; margin-bottom: 12pt; border: .7pt solid #222; }
        .card-head { padding: 5pt 6pt; background: #eee; border-bottom: .7pt solid #222; }
        .card-title { font-size: 9.5pt; font-weight: bold; }
        .card-sub { font-size: 7.8pt; margin-top: 2pt; }
        .summary { width: 100%; border-collapse: collapse; margin-top: 3pt; }
        .summary td { border: 0; padding: 1pt 0; }
        .summary .right { text-align: right; white-space: nowrap; }
        table.lines { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.lines th, table.lines td { border: .5pt solid #333; padding: 2.5pt 3pt; vertical-align: top; }
        table.lines th { background: #f3f3f3; font-size: 7.5pt; text-align: center; }
        .right { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .muted { color: #444; }
    </style>
</head>
<body>
@php
    $money = static fn ($value) => number_format((float) $value, 2, ',', '.');
@endphp

<div class="header">
    <div class="company">{{ config('app.company_name', config('app.name')) }}</div>
    <div class="title">KARTU PIUTANG PER FAKTUR</div>
    <div class="meta">
        Posisi {{ date('d/m/Y', strtotime($tanggalPosisi)) }}
        @if($customer !== '') — Pelanggan: {{ $customer }}@endif
        @if(($nofaktur ?? '') !== '') — No. Faktur: {{ $nofaktur }}@endif
    </div>
</div>

@foreach($invoiceCards as $card)
    <div class="card">
        <div class="card-head">
            <div class="card-title">{{ $card['invoice']->NOFAKTUR }}</div>
            <div class="card-sub">
                {{ $card['invoice']->CUSTOMER }}
                @if($card['customer_name']) — {{ $card['customer_name'] }}@endif
                · Tgl {{ $card['invoice']->TGLFAKTUR?->format('d/m/Y') }}
                · JTP {{ $card['invoice']->TGLJT?->format('d/m/Y') ?? '-' }}
                · {{ $card['status'] }}
                @if($card['journal_number'])
                    · Jurnal {{ $card['journal_number'] }}
                @endif
            </div>
            <table class="summary">
                <tr>
                    <td>Nilai: <span class="bold">{{ $money($card['invoice_amount']) }}</span></td>
                    <td class="right">Terbayar: <span class="bold">{{ $money($card['paid']) }}</span></td>
                    <td class="right">Sisa: <span class="bold">{{ $money($card['balance']) }}</span></td>
                </tr>
            </table>
        </div>
        <table class="lines">
            <thead>
                <tr>
                    <th style="width:11%">Tanggal</th>
                    <th style="width:12%">Jenis</th>
                    <th style="width:16%">Referensi</th>
                    <th style="width:29%">Keterangan</th>
                    <th style="width:11%">Debit</th>
                    <th style="width:11%">Kredit</th>
                    <th style="width:10%">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($card['lines'] as $line)
                    <tr>
                        <td class="center">{{ $line['date']?->format('d/m/Y') ?? '-' }}</td>
                        <td class="center">{{ $line['type'] === 'invoice' ? 'Invoice' : 'Pelunasan' }}</td>
                        <td>{{ $line['reference'] }}</td>
                        <td>{{ $line['description'] }}</td>
                        <td class="right">{{ $line['debit'] > 0 ? $money($line['debit']) : '-' }}</td>
                        <td class="right">{{ $line['credit'] > 0 ? $money($line['credit']) : '-' }}</td>
                        <td class="right bold">{{ $money($line['balance']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach
</body>
</html>
