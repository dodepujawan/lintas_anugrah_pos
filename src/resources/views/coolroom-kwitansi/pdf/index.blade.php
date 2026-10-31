<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi Coolroom {{ $noKwt }}</title>
    <style>
        @page { margin: 32px 40px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #172033; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #243b64; padding-bottom: 10px; }
        .company { font-size: 17px; font-weight: bold; color: #243b64; letter-spacing: .3px; }
        .subtitle { color: #65728a; margin-top: 4px; font-size: 9px; }
        .addresses { width: 100%; margin: 12px 0; color: #3d485b; font-size: 9px; }
        .addresses td { width: 50%; vertical-align: top; line-height: 1.6; }
        .right { text-align: right; }
        .title { margin-top: 18px; padding: 10px 12px; background: #edf2f8; border-left: 4px solid #243b64; font-size: 14px; font-weight: bold; }
        .receipt { width: 100%; margin-top: 18px; border-collapse: collapse; }
        .receipt td { padding: 8px 5px; vertical-align: top; }
        .label { width: 155px; color: #65728a; }
        .amount-words { padding: 10px 12px; border: 1px solid #cad2df; border-radius: 4px; min-height: 24px; }
        .sj-list { margin-top: 7px; line-height: 1.7; }
        .payment { margin-top: 22px; border-top: 1px solid #cad2df; padding-top: 12px; line-height: 1.7; }
        .amount-box { display: inline-block; margin-top: 8px; padding: 8px 14px; border: 1px solid #243b64; font-weight: bold; font-size: 14px; }
        .signature { width: 100%; margin-top: 34px; }
        .signature td { width: 50%; vertical-align: bottom; text-align: center; }
        .sign-space { height: 70px; }
        .muted { color: #65728a; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">PT. LINTAS MITRA ANUGERAH SEJATI</div>
        <div class="subtitle">COLD CHAIN DISTRIBUTION &amp; STORAGE</div>
    </div>
    <table class="addresses">
        <tr>
            <td>Jl. Raya Sempidi No.9 Badung - Bali<br>Telp/Fax: (0361) 8947610<br>Jl. Bija Taki IV, No.9 Denpasar - Bali</td>
            <td class="right">BizPark Commercial Estate<br>Jl. Sultan Agung KM 28,5 Bekasi<br>www.lintasmitralogistik.com</td>
        </tr>
    </table>

    <div class="title">KWITANSI NO: {{ $noKwt }}</div>
    <table class="receipt">
        <tr><td class="label">Sudah terima dari</td><td><strong>{{ $master->CUSTOMER ?: '-' }}</strong></td></tr>
        <tr><td class="label">Banyaknya uang</td><td><div class="amount-words">{{ ucwords(terbilang((float) ($master->GRAND ?? 0))) }} Rupiah</div></td></tr>
        <tr>
            <td class="label">Untuk pembayaran</td>
            <td>
                <strong>Sewa ruang dingin</strong>
                <div class="sj-list">No. SJ: {{ $details->pluck('NOSJ')->filter()->unique()->implode(', ') ?: '-' }}</div>
                <div>Invoice: {{ $invoice }}</div>
            </td>
        </tr>
    </table>

    <div class="payment">
        Untuk pembayaran mohon ditransfer ke rekening resmi:<br>
        <strong>A/n. PT. Lintas Mitra Anugerah Sejati</strong><br>
        No. Rekening BCA 6115352010
    </div>
    <table class="addresses">
        <tr>
            <td>CEK/GIRO NO. __________________________</td>
            <td class="right">Denpasar {{ $master->TGLKW?->format('d-m-Y') ?? '-' }}</td>
        </tr>
    </table>
    <div class="amount-box">JUMLAH &nbsp; RP. {{ number_format((float) ($master->GRAND ?? 0), 0, ',', '.') }}</div>

    <table class="signature">
        <tr>
            <td>PENERIMA<div class="sign-space"></div><span class="muted">(.........................................)</span></td>
            <td>MENGETAHUI<div class="sign-space"></div><strong>{{ $signature->NAMA ?? $signature->nama ?? '' }}</strong></td>
        </tr>
    </table>
</body>
</html>
