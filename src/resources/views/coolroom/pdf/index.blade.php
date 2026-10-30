<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Jalan Coolroom {{ $coolroom->NOSJ }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #374151; padding: 6px; }
        .no-border { border: 0; }
        .center { text-align: center; }
        .right { text-align: right; }
        .header { text-align: center; margin-bottom: 18px; }
        .title { font-size: 15px; font-weight: bold; text-decoration: underline; margin-top: 12px; }
        .summary { width: 48%; margin-left: auto; margin-top: 14px; }
        .signatures { margin-top: 32px; }
        .signatures td { height: 78px; vertical-align: top; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <strong style="font-size: 15px">PT. LINTAS MITRA ANUGERAH SEJATI</strong>
        <div>COLD STORAGE &amp; COOLROOM</div>
        <div>Jl. Bajataki IV No.9 Denpasar</div>
        <div class="title">SURAT JALAN COOLROOM</div>
    </div>

    <table>
        <tr><td width="25%">Tanggal</td><td>{{ $coolroom->TGLSJ?->format('d-m-Y') ?? '-' }}</td></tr>
        <tr><td>No. SJ</td><td>{{ $coolroom->NOSJ }}</td></tr>
        <tr><td>Customer</td><td>{{ $coolroom->CUSTOMER }}</td></tr>
    </table>

    <table style="margin-top: 14px">
        <thead><tr><th width="6%">No</th><th>Keterangan</th><th width="13%">Qty (KG)</th><th width="15%">Harga</th><th width="17%">Subtotal</th></tr></thead>
        <tbody><tr>
            <td class="center">1</td>
            <td>{{ $coolroom->KETERANGAN ?: 'SEWA COOLROOM' }}{{ $coolroom->BOXING ? ' (BOXING)' : '' }}</td>
            <td class="right">{{ number_format((float) $coolroom->JUMLAH, 3, ',', '.') }}</td>
            <td class="right">{{ number_format((float) $coolroom->HARGA, 0, ',', '.') }}</td>
            <td class="right">{{ number_format((float) $coolroom->SUBTOTAL, 0, ',', '.') }}</td>
        </tr></tbody>
    </table>

    <table class="summary">
        <tr><td>Subtotal</td><td class="right">{{ number_format((float) $coolroom->SUBTOTAL, 0, ',', '.') }}</td></tr>
        <tr><td>Diskon</td><td class="right">{{ number_format((float) $coolroom->NDISC, 0, ',', '.') }}</td></tr>
        <tr><td>DPP</td><td class="right">{{ number_format((float) $coolroom->DPP, 0, ',', '.') }}</td></tr>
        <tr><td>PPN</td><td class="right">{{ number_format((float) $coolroom->NPPN, 0, ',', '.') }}</td></tr>
        <tr><th>Grand</th><th class="right">{{ number_format((float) $coolroom->GRAND, 0, ',', '.') }}</th></tr>
    </table>

    <table class="signatures">
        <tr>
            <td class="no-border">DIBUAT OLEH<br><br><br><strong>{{ $user->name ?? $coolroom->USERINPUT }}</strong></td>
            <td class="no-border">DIKETAHUI<br><br><br><strong>{{ $signature->nama ?? '-' }}</strong></td>
            <td class="no-border">DITERIMA<br><br><br><strong>{{ $coolroom->CUSTOMER }}</strong></td>
        </tr>
    </table>
</body>
</html>
