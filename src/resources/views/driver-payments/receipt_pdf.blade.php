<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Pelunasan Piutang Sopir {{ $payment->number }}</title>
    <style>
        @page { size: letter portrait; margin: 28pt 32pt; }
        body { margin: 0; color: #111; font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.35; }
        .title { text-align: center; border-bottom: 1.2pt solid #111; padding-bottom: 8pt; margin-bottom: 14pt; }
        .title h1 { margin: 0; font-size: 15pt; letter-spacing: .2pt; }
        .title .number { margin-top: 4pt; font-size: 10pt; font-weight: bold; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 12pt; }
        .meta td { padding: 3pt 4pt; vertical-align: top; }
        .meta .label { width: 18%; color: #444; }
        .meta .value { width: 32%; font-weight: bold; }
        .section-title { font-weight: bold; margin: 12pt 0 5pt; }
        table.detail, table.journal { width: 100%; border-collapse: collapse; }
        .detail th, .detail td, .journal th, .journal td { border: .7pt solid #444; padding: 5pt 6pt; vertical-align: top; }
        .detail th, .journal th { background: #eee; }
        .detail .receivable-number { width: 30%; overflow-wrap: anywhere; word-break: break-word; }
        .detail .receivable-date { width: 12%; white-space: nowrap; }
        .detail .receivable-type { width: 18%; overflow-wrap: anywhere; }
        .detail .amount { width: 13.33%; }
        .right { text-align: right; white-space: nowrap; }
        .note { min-height: 34pt; border: .7pt solid #444; padding: 6pt; }
        .signatures { width: 100%; margin-top: 32pt; text-align: center; page-break-inside: avoid; }
        .signatures td { width: 33.33%; }
        .sign-line { margin: 44pt auto 0; padding-top: 3pt; border-top: 1pt solid #222; width: 82%; }
        .small { font-size: 9pt; color: #444; }
    </style>
</head>
<body>
<div class="title">
    <h1>BUKTI PELUNASAN PIUTANG SOPIR</h1>
    <div class="number">No. {{ $payment->number }}</div>
    <div class="small">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
</div>

<table class="meta">
    <tr>
        <td class="label">Tanggal</td><td class="value">{{ $payment->date?->format('d/m/Y') }}</td>
        <td class="label">Metode</td><td class="value">{{ str_replace('_', ' ', $payment->method) }}</td>
    </tr>
    <tr>
        <td class="label">Sopir</td><td class="value">{{ $payment->receivable?->driver?->code }} - {{ $payment->receivable?->driver?->name }}</td>
        <td class="label">No. Jurnal</td><td class="value">{{ $payment->journal?->no_jurnal ?: '-' }}</td>
    </tr>
    <tr>
        <td class="label">Referensi</td><td class="value">{{ $payment->reference ?: '-' }}</td>
        <td class="label">Status</td><td class="value">{{ $payment->status }}</td>
    </tr>
</table>

<div class="section-title">Rincian Pelunasan</div>
<table class="detail">
    <thead>
        <tr>
            <th class="receivable-number">No. Piutang</th>
            <th class="receivable-date">Tanggal</th>
            <th class="receivable-type">Jenis</th>
            <th class="right amount">Piutang</th>
            <th class="right amount">Bayar</th>
            <th class="right amount">Sisa</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="receivable-number">{{ $payment->receivable?->number }}</td>
            <td class="receivable-date">{{ $payment->receivable?->date?->format('d/m/Y') }}</td>
            <td class="receivable-type">{{ str_replace('_', ' ', (string) $payment->receivable?->type) }}</td>
            <td class="right amount">{{ number_format((float) $payment->receivable?->amount, 2, ',', '.') }}</td>
            <td class="right amount">{{ number_format((float) $payment->amount, 2, ',', '.') }}</td>
            <td class="right amount">{{ number_format((float) $payment->receivable?->balance, 2, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

<div class="section-title">Keterangan</div>
<div class="note">{{ $payment->description ?: '-' }}</div>

@if($payment->journal)
<div class="section-title">Jurnal</div>
<table class="journal">
    <thead><tr><th>Akun</th><th>Keterangan</th><th class="right">Debit</th><th class="right">Kredit</th></tr></thead>
    <tbody>
        @foreach($payment->journal->items as $item)
            <tr>
                <td>{{ $item->coa?->kode_akun }} - {{ $item->coa?->nama_akun }}</td>
                <td>{{ $item->deskripsi }}</td>
                <td class="right">{{ number_format((float) $item->debit, 2, ',', '.') }}</td>
                <td class="right">{{ number_format((float) $item->kredit, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

<table class="signatures">
    <tr>
        <td>Dibuat oleh<div class="sign-line">{{ auth()->user()->name ?? 'SYSTEM' }}</div></td>
        <td>Diterima oleh<div class="sign-line">&nbsp;</div></td>
        <td>Mengetahui<div class="sign-line">&nbsp;</div></td>
    </tr>
</table>
</body>
</html>
