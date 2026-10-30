<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice Coolroom {{ $invoice->INVOICE }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #172033; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 7px; vertical-align: top; }
        .muted { color: #596579; }
        .bordered th, .bordered td { border: 1px solid #cbd5e1; }
        .heading { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .brand { text-align: right; font-size: 14px; font-weight: bold; }
        .title { background: #e8eff8; font-weight: bold; }
        .right { text-align: right; }
        .center { text-align: center; }
        .summary { width: 48%; margin-left: auto; margin-top: 14px; }
        .summary td { padding: 4px 7px; }
        .total { border-top: 2px solid #172033; font-size: 13px; font-weight: bold; }
        .sign { margin-top: 38px; }
        .sign td { width: 33%; text-align: center; height: 75px; }
    </style>
</head>
<body>
    <table><tr><td class="heading">INVOICE</td><td class="brand">PT. LINTAS ANUGERAH SEJATI<br><span class="muted" style="font-size: 10px">COLD CHAIN DISTRIBUTION &amp; STORAGE</span></td></tr></table>
    <hr>
    <table>
        <tr>
            <td width="50%"><span class="muted">No. Invoice</span><br><strong>{{ $invoice->INVOICE }}</strong></td>
            <td width="50%"><span class="muted">Tanggal Invoice</span><br><strong>{{ $invoice->TGLINVOICE?->translatedFormat('d F Y') ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td><span class="muted">Customer</span><br><strong>{{ $customer->NAMACUST ?? $invoice->CUSTOMER }}</strong><br>{{ $customer->KONTAK ?? '-' }}<br>{{ $customer->ALAMAT1 ?? '-' }}</td>
            <td><span class="muted">Jatuh Tempo</span><br><strong>{{ ($arh?->TGLJT ?? $invoice->TGLJT)?->translatedFormat('d F Y') ?? '-' }}</strong><br><span class="muted">Kode Customer</span><br>{{ $invoice->CUSTOMER_KODE ?: '-' }}</td>
        </tr>
    </table>

    <table class="bordered" style="margin-top: 18px">
        <thead><tr class="title"><th>No</th><th>No. SJ</th><th>Keterangan</th><th class="right">Jumlah</th><th>Unit</th><th class="right">Harga</th><th class="right">Diskon</th><th class="right">Total</th></tr></thead>
        <tbody>
            @foreach($details as $row)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $row->NOSJ }}</td>
                    <td>{{ $row->KETERANGAN ?: 'SEWA COOLROOM' }}{{ $row->BOXING ? ' (BOXING)' : '' }}</td>
                    <td class="right">{{ number_format((float) $row->JUMLAH, 3, ',', '.') }}</td>
                    <td>{{ $row->UNIT ?: 'KG' }}</td>
                    <td class="right">{{ number_format((float) $row->HARGA, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $row->NDISC, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $row->TOTAL, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><td>Subtotal</td><td class="right">{{ number_format((float) $details->sum('SUBTOTAL'), 0, ',', '.') }}</td></tr>
        <tr><td>Diskon</td><td class="right">{{ number_format((float) $details->sum('NDISC'), 0, ',', '.') }}</td></tr>
        <tr><td>DPP</td><td class="right">{{ number_format((float) $details->sum('DPP'), 0, ',', '.') }}</td></tr>
        <tr><td>PPN</td><td class="right">{{ number_format((float) $details->sum('NPPN'), 0, ',', '.') }}</td></tr>
        <tr class="total"><td>Grand Total</td><td class="right">{{ number_format((float) $details->sum('GRAND'), 0, ',', '.') }}</td></tr>
        <tr><td>Dibayar</td><td class="right">{{ number_format((float) $details->sum('BAYAR'), 0, ',', '.') }}</td></tr>
        <tr><td>Sisa Piutang</td><td class="right">{{ number_format((float) $details->sum('PIUTANG'), 0, ',', '.') }}</td></tr>
    </table>

    <table style="margin-top: 18px">
        <tr><td colspan="2" class="muted">Pembayaran dapat ditransfer ke rekening resmi:</td></tr>
        <tr><td width="15%">Bank</td><td>{{ $rekening->BANK ?? '-' }}</td></tr>
        <tr><td>No. Rekening</td><td>{{ $rekening->NOREK ?? '-' }}</td></tr>
        <tr><td>Atas Nama</td><td>{{ $rekening->NAMA ?? '-' }}</td></tr>
    </table>

    <table class="sign"><tr>
        <td>Penerima<br><br><br>(................................)</td>
        <td>Mengetahui<br><br><br><strong>{{ $signature->nama ?? '-' }}</strong></td>
        <td>Dicetak {{ now()->format('d/m/Y') }}<br><br><br>{{ auth()->user()->user_id ?? '-' }}</td>
    </tr></table>
</body>
</html>
