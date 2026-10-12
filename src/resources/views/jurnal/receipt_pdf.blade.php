<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Bukti Jurnal {{ $jurnal->no_jurnal }}</title>
<style>
    @page { margin: 14pt 16pt; }
    * { box-sizing: border-box; }
    body { margin: 0; color: #111; font-family: DejaVu Sans, sans-serif; font-size: {{ $paper === 'half' ? '6.8pt' : '8pt' }}; line-height: 1.15; }
    .heading { position: relative; text-align: center; border-bottom: 1px solid #222; padding-bottom: 5px; margin-bottom: 6px; }
    .heading h1 { margin: 0; font-size: {{ $paper === 'half' ? '10pt' : '12pt' }}; letter-spacing: .3px; }
    .heading .number { margin-top: 2px; font-weight: bold; }
    .status { position: absolute; top: 0; right: 0; border: 1px solid #333; padding: 2px 5px; font-size: 6pt; font-weight: bold; }
    .print-meta { width: 100%; margin-top: 3px; font-size: 6pt; }
    .print-meta td:last-child { text-align: right; }
    .info { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    .info td { padding: 1px 2px; vertical-align: top; }
    .info .label { width: 11%; color: #555; }
    .info .value { width: 39%; font-weight: bold; }
    .detail { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .detail th, .detail td { border: 1px solid #444; padding: {{ $paper === 'half' ? '2px 3px' : '3px 4px' }}; vertical-align: top; }
    .detail th { background: #eee; font-weight: bold; }
    .detail .code { width: 14%; white-space: nowrap; }
    .detail .account { width: 27%; }
    .detail .description { width: 31%; }
    .detail .num { width: 14%; text-align: right; white-space: nowrap; }
    .detail tfoot th { background: #eee; }
    .signatures { width: 100%; margin-top: {{ $paper === 'half' ? '12px' : '24px' }}; text-align: center; page-break-inside: avoid; }
    .signatures td { width: 33.33%; }
    .sign-line { margin: {{ $paper === 'half' ? '24px' : '38px' }} auto 0; padding-top: 2px; border-top: 1px solid #222; width: 80%; }
</style>
</head>
<body>
<div class="heading">
    <h1>BUKTI JURNAL UMUM</h1>
    <div class="number">{{ $jurnal->no_jurnal }}</div>
    <div class="status">{{ $jurnal->is_posted ? 'POSTED' : 'DRAFT' }}</div>
    <table class="print-meta"><tr><td>Dicetak: {{ now()->format('d/m/Y H:i') }}</td><td>Halaman 1</td></tr></table>
</div>

<table class="info">
    <tr><td class="label">Tanggal</td><td class="value">{{ $jurnal->tanggal->format('d/m/Y') }}</td><td class="label">Referensi</td><td class="value">{{ $jurnal->referensi ?: '-' }}</td></tr>
    <tr><td class="label">Dibuat oleh</td><td class="value">{{ $jurnal->createdBy->name ?? 'System' }}</td><td class="label">Keterangan</td><td class="value">{{ $jurnal->keterangan ?: '-' }}</td></tr>
</table>

<table class="detail">
    <thead><tr><th class="code">Kode Akun</th><th class="account">Nama Akun</th><th class="description">Deskripsi</th><th class="num">Debit</th><th class="num">Kredit</th></tr></thead>
    <tbody>
    @forelse($jurnal->items as $item)
        <tr>
            <td class="code">{{ $item->coa?->kode_akun ?? '-' }}</td>
            <td class="account">{{ $item->coa?->nama_akun ?? '-' }}</td>
            <td class="description">{{ $item->deskripsi ?: '-' }}</td>
            <td class="num">{{ number_format($item->debit, 2, ',', '.') }}</td>
            <td class="num">{{ number_format($item->kredit, 2, ',', '.') }}</td>
        </tr>
    @empty
        <tr><td colspan="5" style="text-align:center">Tidak ada item jurnal.</td></tr>
    @endforelse
    </tbody>
    <tfoot><tr><th colspan="3">TOTAL</th><th class="num">{{ number_format($jurnal->total_debit, 2, ',', '.') }}</th><th class="num">{{ number_format($jurnal->total_kredit, 2, ',', '.') }}</th></tr></tfoot>
</table>

<table class="signatures"><tr><td>Dibuat oleh<div class="sign-line">{{ $jurnal->createdBy->name ?? 'System' }}</div></td><td>Diperiksa oleh<div class="sign-line">&nbsp;</div></td><td>Disetujui oleh<div class="sign-line">&nbsp;</div></td></tr></table>
</body>
</html>
