<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { font-size: 14px; margin: 0; font-weight: bold; }
        .header h3 { font-size: 10px; margin: 2px 0; color: #555; }
        .kwt { text-align: center; margin: 15px 0; }
        .kwt h3 { font-size: 14px; font-weight: bold; }
        .content { page-break-after: always; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td, th { border: 1px solid #000; padding: 4px 6px; }
        .text-right { text-align: right; }
        .font-monospace { font-family: monospace; }
        .terbilang-box { border: 1px dashed #000; padding: 8px; margin: 10px 0; }
    </style>
</head>
<body>
<div class="content">
    <div class="header">
        <h2>PT. LINTAS MITRA ANUGERAH SEJATI</h2>
        <h3>COLD CHAIN DISTRIBUTION &amp; STORAGE | Denpasar - Bali | Telp: (0361) 8947610</h3>
    </div>

    <div class="kwt">
        <h3>KWITANSI NO. {{ $kwitansi->NO_KWT ?? '-' }}</h3>
    </div>

    <table>
        <tr>
            <th style="width:15%">Tgl KW</th>
            <td>{{ $kwitansi->TGL_KWT?->format('d/m/Y') }}</td>
            <th style="width:15%">Invoice</th>
            <td>{{ $kwitansi->NOFAKTUR ?? '-' }}</td>
            <th style="width:15%">Tgl Invoice</th>
            <td>{{ $arh->TGLFAKTUR?->format('d/m/Y') ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td style="width:25%"><strong>Sudah menerima dari</strong></td>
            <td style="width:1%" >:</td>
            <td style="width:74%">{{ $kwitansi->CUSTOMER ?? $arh->CUSTOMER ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Nama Customer</strong></td>
            <td>:</td>
            <td>{{ $kwitansi->NAMACUST ?? $arh->CUSTOMER ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Metode Pembayaran</strong></td>
            <td>:</td>
            <td>{{ $kwitansi->USEREDIT ?? 'TUNAI' }}</td>
        </tr>
    </table>

    <div class="terbilang-box">
        <strong>Terbilang:</strong>
        {{ ucwords(\App\Helpers\Helper::terbilang((float) ($kwitansi->NILAI_BAYAR ?? 0)) ?? 'Nol') }} Rupiah
    </div>

    <table>
        <thead class="table-light">
            <tr>
                <th style="width:25%">Keterangan</th>
                <th style="width:15%" class="text-right">Saldo Faktur</th>
                <th style="width:15%" class="text-right">Discount</th>
                <th style="width:15%" class="text-right">Retur</th>
                <th style="width:15%" class="text-right">Bayar</th>
                <th style="width:15%" class="text-right">Sisa</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $kwitansi->KETERANGAN ?? '-' }}</td>
                <td class="text-right">{{ number_format((float) ($kwitansi->SALDO_FAKTUR ?? 0), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format((float) ($kwitansi->DISCOUNT ?? 0), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format((float) ($kwitansi->RETUR ?? 0), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format((float) ($kwitansi->NILAI_BAYAR ?? 0), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format((float) ($kwitansi->SISA_FAKTUR ?? 0), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <tr>
            <td style="width:50%">
                <strong>Rekening :</strong> {{ $rekening->BANK ?? '' }} — {{ $rekening->NOREK ?? '' }} a.n. {{ $rekening->NAMA ?? '' }}
            </td>
            <td style="width:50%; text-align:right;">
                <strong>Total :</strong> Rp. {{ number_format((float) ($kwitansi->NILAI_BAYAR ?? 0), 0, ',', '.') }}<br>
                <strong>USER :</strong> {{ auth()->user()->name ?? '-' }}<br>
                <strong>TTD :</strong> {{ $signature->NAMA ?? '__________________' }}
            </td>
        </tr>
    </table>
</div>
</body>
</html>
