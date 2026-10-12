<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Bukti Pelunasan {{ $barh->NOBAYAR }}</title>
<style>
    @page { size: letter portrait; margin: 24pt 28pt; }
    body { margin: 0; color: #111; font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.32; }
    .title { text-align: center; border-bottom: 1px solid #222; padding-bottom: 7px; margin-bottom: 12px; }
    .title h1 { margin: 0; font-size: 15pt; }
    .title .number { margin-top: 4px; font-weight: bold; }
    .meta-print { width: 100%; margin-top: 5px; font-size: 8.5pt; }
    .meta-print td:last-child { text-align: right; }
    .info { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .info td { padding: 3px 4px; vertical-align: top; }
    .info .label { width: 12%; color: #555; }
    .info .value { width: 38%; font-weight: bold; }
    .detail { width: 100%; border-collapse: collapse; table-layout: auto; }
    .detail th, .detail td { border: 1px solid #555; padding: 5px 6px; }
    .detail th { background: #eee; }
    .detail th:first-child, .detail td:first-child { white-space: nowrap; }
    .num { text-align: right; white-space: nowrap; }
    .totals { width: 52%; margin: 10px 0 0 auto; border-collapse: collapse; }
    .totals td { padding: 3px 4px; }
    .totals td:last-child { text-align: right; font-weight: bold; }
    .signatures { width: 100%; margin-top: 32px; text-align: center; page-break-inside: avoid; }
    .signatures td { width: 33.33%; }
    .sign-line { margin: 44px auto 0; padding-top: 3px; border-top: 1px solid #222; width: 80%; }
</style>
</head>
<body>
@php($totalPph=$bard->sum('PPH23'))
@php($totalDiscount=$bard->sum('DISCOUNT'))
@php($totalAdmin=$bard->sum('BANK_ADMIN'))
@php($totalSettled=$barh->JUMLAH_BAYAR+$totalPph+$totalDiscount+$totalAdmin+$bard->sum('RETUR'))
<div class="title">
    <h1>BUKTI PELUNASAN PIUTANG</h1>
    <div class="number">No. {{ $barh->NOBAYAR }}</div>
    <table class="meta-print"><tr><td>Dicetak: {{ now()->format('d/m/Y H:i') }}</td><td>Halaman 1</td></tr></table>
</div>
<table class="info">
    <tr><td class="label">Tanggal</td><td class="value">{{ $barh->TGLBAYAR->format('d/m/Y') }}</td><td class="label">Metode</td><td class="value">{{ $barh->METODE_PEMBAYARAN }}</td></tr>
    <tr><td class="label">Pelanggan</td><td class="value">{{ $barh->CUSTOMER }} - {{ $barh->CUSTOMER_NAME ?: '-' }}</td><td class="label">Referensi</td><td class="value">{{ $barh->NO_REFERENSI ?: '-' }}</td></tr>
    <tr><td class="label">Jurnal</td><td class="value">{{ $barh->NOJURNAL ?: '-' }}</td><td class="label">Keterangan</td><td class="value">{{ $barh->KETERANGAN ?: '-' }}</td></tr>
</table>
<table class="detail">
    <thead><tr><th>Faktur</th><th>Tanggal</th><th class="num">Saldo Awal</th><th class="num">Bayar</th><th class="num">PPh 23</th><th class="num">Kerugian</th><th class="num">Adm.</th><th class="num">Sisa</th></tr></thead>
    <tbody>
    @forelse($bard as $d)
        <tr><td>{{ $d->NOFAKTUR }}</td><td>{{ $d->TGLFAKTUR?->format('d/m/Y') }}</td><td class="num">{{ number_format($d->SALDO_FAKTUR,0,',','.') }}</td><td class="num">{{ number_format($d->NILAI_BAYAR,0,',','.') }}</td><td class="num">{{ number_format($d->PPH23,0,',','.') }}</td><td class="num">{{ number_format($d->DISCOUNT,0,',','.') }}</td><td class="num">{{ number_format($d->BANK_ADMIN,0,',','.') }}</td><td class="num">{{ number_format($d->SISA_FAKTUR,0,',','.') }}</td></tr>
    @empty
        <tr><td colspan="8" style="text-align:center">Tidak ada detail.</td></tr>
    @endforelse
    </tbody>
</table>
<table class="totals"><tr><td>Uang diterima</td><td>{{ number_format($barh->JUMLAH_BAYAR,0,',','.') }}</td></tr><tr><td>Total penyelesaian</td><td>{{ number_format($totalSettled,0,',','.') }}</td></tr></table>
<table class="signatures"><tr><td>Dibuat oleh<div class="sign-line">{{ $barh->USER }}</div></td><td>Diterima oleh<div class="sign-line">&nbsp;</div></td><td>Mengetahui<div class="sign-line">&nbsp;</div></td></tr></table>
</body>
</html>
