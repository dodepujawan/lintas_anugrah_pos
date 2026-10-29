<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice Rent Pendingin {{ $invoice->INVOICE }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #172033; }
        .top { width: 100%; border-bottom: 2px solid #243b64; padding-bottom: 10px; margin-bottom: 18px; }
        .brand { font-size: 17px; font-weight: bold; color: #243b64; }
        .subtitle { color: #65728a; font-size: 9px; margin-top: 3px; }
        .title { text-align: right; font-size: 20px; font-weight: bold; letter-spacing: 2px; }
        .info { width: 100%; margin-bottom: 18px; }
        .info td { width: 50%; padding: 4px 2px; vertical-align: top; }
        .label { color: #65728a; width: 105px; display: inline-block; }
        .details { width: 100%; border-collapse: collapse; }
        .details th, .details td { border: 1px solid #cad2df; padding: 7px 6px; }
        .details th { background: #edf2f8; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .totals { width: 42%; margin: 14px 0 0 auto; border-collapse: collapse; }
        .totals td { padding: 5px 3px; }
        .grand { border-top: 1px solid #243b64; font-size: 12px; font-weight: bold; }
        .payment { margin-top: 24px; width: 100%; }
        .payment td { width: 50%; vertical-align: top; }
        .bank { line-height: 1.7; }
        .sign { height: 65px; }
        .muted { color: #65728a; }
    </style>
</head>
<body>
    <table class="top"><tr>
        <td><div class="brand">PT. LINTAS ANUGERAH SEJATI</div><div class="subtitle">INVOICE RENT PENDINGIN</div></td>
        <td class="title">INVOICE</td>
    </tr></table>

    <table class="info">
        <tr>
            <td><span class="label">No. Invoice</span><strong>{{ $invoice->INVOICE }}</strong></td>
            <td><span class="label">Tanggal</span>{{ $invoice->TGLINVOICE?->format('d-m-Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td><span class="label">Customer</span>{{ $invoice->CUSTOMER ?: '-' }}</td>
            <td><span class="label">Jatuh Tempo</span>{{ ($arh?->TGLJT ?? $invoice->TGLJT)?->format('d-m-Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td><span class="label">No. Surat Jalan</span>{{ $details->pluck('NOSJ')->filter()->implode(', ') ?: '-' }}</td>
            <td><span class="label">No. Muat</span>{{ $invoice->NOMUAT ?: '-' }}</td>
        </tr>
        <tr>
            <td><span class="label">Kendaraan</span>{{ $invoice->NAMA_KENDARAAN ?: '-' }}</td>
            <td><span class="label">Keterangan</span>{{ $invoice->KETERANGAN ?: '-' }}</td>
        </tr>
    </table>

    <table class="details">
        <thead><tr><th class="center" width="5%">No</th><th width="17%">No. SJ</th><th width="30%">Pesanan</th><th class="right" width="13%">Jumlah</th><th class="right" width="15%">Harga</th><th class="right" width="20%">Grand</th></tr></thead>
        <tbody>
        @foreach($details as $row)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $row->NOSJ ?: '-' }}</td>
                <td>{{ $row->PESANAN ?: 'RENT PENDINGIN' }}</td>
                <td class="right">{{ number_format((float) $row->JUMLAH, 3, ',', '.') }} {{ $row->UNIT }}</td>
                <td class="right">{{ number_format((float) $row->HARGA, 0, ',', '.') }}</td>
                <td class="right">{{ number_format((float) $row->GRAND, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">{{ number_format((float) $invoice->JUMLAH * (float) $invoice->HARGA, 0, ',', '.') }}</td></tr>
        <tr><td>Diskon</td><td class="right">- {{ number_format((float) $invoice->NDISC, 0, ',', '.') }}</td></tr>
        <tr><td>Total setelah diskon</td><td class="right">{{ number_format((float) $invoice->TOTAL, 0, ',', '.') }}</td></tr>
        <tr><td>D. Charge</td><td class="right">{{ number_format((float) $invoice->DC, 0, ',', '.') }}</td></tr>
        <tr><td>PPN ({{ number_format((float) $invoice->PPN, 2, ',', '.') }}%)</td><td class="right">{{ number_format(round((float) $invoice->TOTAL * (float) $invoice->PPN / 100), 0, ',', '.') }}</td></tr>
        <tr class="grand"><td>Grand Total</td><td class="right">Rp {{ number_format((float) $invoice->GRAND, 0, ',', '.') }}</td></tr>
        <tr><td>Bayar</td><td class="right">{{ number_format((float) $invoice->BAYAR, 0, ',', '.') }}</td></tr>
        <tr><td><strong>Sisa Piutang</strong></td><td class="right"><strong>Rp {{ number_format((float) $invoice->PIUTANG, 0, ',', '.') }}</strong></td></tr>
    </table>

    <table class="payment">
        <tr>
            <td class="bank">
                <strong>Informasi Pembayaran</strong><br>
                No. Rekening: {{ $rekening->NOREK ?? '-' }}<br>
                Atas Nama: {{ $rekening->NAMA ?? '-' }}<br>
                Bank: {{ $rekening->BANK ?? '-' }}
            </td>
            <td class="center">
                <div class="muted">Diterima,</div><div class="sign"></div><div>________________________</div>
                <div class="muted">{{ $signature->NAMA ?? $signature->nama ?? 'PT. Lintas Anugerah Sejati' }}</div>
            </td>
        </tr>
    </table>
    <p class="muted" style="margin-top: 18px; font-size: 8px;">Dicetak {{ now()->format('d-m-Y H:i') }} oleh {{ auth()->user()->name ?? '-' }}</p>
</body>
</html>
