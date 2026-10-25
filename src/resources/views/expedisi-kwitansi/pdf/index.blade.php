<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header-title { text-align: center; font-weight: bold; font-size: 16px; }
        .sub-title { text-align: center; font-size: 11px; margin-bottom: 10px; }
        hr { border: 1px solid #000; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 0; vertical-align: top; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header-title">PT. LINTAS MITRA ANUGERAH SEJATI</div>
    <div class="sub-title">COLD CHAIN DISTRIBUTION &amp; STORAGE</div>

    <table>
        <tr>
            <td style="width:50%">
                Jl. Raya Sempidi No.9 Badung - Bali<br>
                Telp/Fax : (0361) 8947610<br>
                Jl. Bija Taki IV, No.9 Denpasar - Bali
            </td>
            <td style="width:50%" class="text-right">
                BizPark Commercial Estate<br>
                Jl. Sultan Agung KM 28,5 Bekasi<br>
                www.lintasmitralogistik.com
            </td>
        </tr>
    </table>

    <hr>

    <p class="text-bold">KWITANSI NO : {{ $master->kwt }}</p>

    <table>
        <tr>
            <td style="width:25%">SUDAH TERIMA DARI</td>
            <td style="width:5%">:</td>
            <td style="width:70%">{{ $master->CUSTOMER }}</td>
        </tr>
        <tr>
            <td>BANYAKNYA UANG</td>
            <td>:</td>
            <td>{{ ucwords(terbilang((float) ($master->GRAND ?? 0))) }} Rupiah</td>
        </tr>
        <tr>
            <td>UNTUK PEMBAYARAN</td>
            <td>:</td>
            <td>
                {{ $details->pluck('NOSJ')->filter()->unique()->implode(', ') }}
                <br>invoice : {{ $master->INVOICE }}
            </td>
        </tr>
    </table>

    <hr>

    <p>
        Untuk pembayaran mohon di transfer ke rek resmi<br>
        <b>A/n. PT. Lintas Mitra Anugerah Sejati</b><br>
        No.rek BCA 6115352010
    </p>

    <table>
        <tr>
            <td style="width:50%">CEK/GIRO NO. ______________________</td>
            <td style="width:50%" class="text-right">
                Denpasar {{ $master->TGLKW ? \Carbon\Carbon::parse($master->TGLKW)->format('d-m-Y') : '-' }}
            </td>
        </tr>
    </table>

    <br><br>

    <table>
        <tr>
            <td style="width:50%">
                <b>JUMLAH</b><br>
                RP. {{ number_format((float) ($master->GRAND ?? 0), 0, ',', '.') }}
            </td>
            <td style="width:50%" class="text-right">
                <br><br>
                {{ $signature->NAMA ?? '' }}
            </td>
        </tr>
    </table>
</body>
</html>
