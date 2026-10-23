<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan {{ $expedisi->NOSJ }}</title>
    <style>
        body { font-size: 12px; font-family: Comic Sans, Helvetica, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { text-align: center; font-weight: bold; }
        td, th { border: 1px solid #000; padding: 5px; }
        .no-border { border: none !important; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <table class="no-border">
        <tr>
            <td class="no-border">
                <strong>PT. LINTAS MITRA ANUGERAH SEJATI</strong><br>
                COLD CHAIN DISTRIBUTION &amp; STORAGE<br>
                Denpasar - Bali
            </td>
            <td class="no-border text-right">
                <strong>SURAT JALAN EKSPEDISI</strong><br>
                NO SJ: {{ $expedisi->NOSJ }}
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td>Tanggal SJ</td>
            <td>{{ $expedisi->tglsj }}</td>
            <td>Nama Customer</td>
            <td>{{ $expedisi->CUSTOMER }}</td>
        </tr>
        <tr>
            <td>Nama Penerima</td>
            <td>{{ $expedisi->P_NAMA }}</td>
            <td>No Telp</td>
            <td>{{ $expedisi->P_PHONE }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td colspan="3">{{ $expedisi->P_ALAMAT }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Item Barang</th>
                <th width="15%">Penyimpanan<br>F / C / D</th>
                <th width="10%">Suhu</th>
                <th width="10%">Koli</th>
                <th width="15%">Kg / Ton</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{{ $expedisi->barang ?? '-' }}</td>
                <td class="text-center">{{ strtoupper($expedisi->penyimpanan ?? '-') }}</td>
                <td class="text-center">-</td>
                <td class="text-center">{{ $expedisi->koli ?? '0' }}</td>
                <td class="text-center">{{ number_format($expedisi->JUMLAH ?? 0, 0, ',', '.') }}</td>
            </tr>
            @for ($i = 2; $i <= 6; $i++)
                <tr>
                    <td class="text-center">{{ $i }}</td>
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table>
        <tr>
            <td width="70%" valign="top"><strong>Catatan Khusus:</strong><br></td>
            <td class="text-right">Subtotal</td>
            <td class="text-right">{{ number_format($expedisi->TOTAL, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td rowspan="2">{{ $expedisi->catatan ?? '-' }}</td>
            <td class="text-right">PPN</td>
            <td class="text-right">{{ number_format($expedisi->PPN, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-right"><strong>Grand Total</strong></td>
            <td class="text-right"><strong>{{ number_format($expedisi->GRAND, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <table class="no-border" style="margin-top:30px; width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:33.33%; vertical-align:top; border:none;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr><td colspan="2" style="text-align:center;"><strong>DISTRIBUSI</strong></td></tr>
                    <tr><td><strong>NAMA SOPIR</strong></td><td>{{ $expedisi->NAMA_DRIVER ?? '-' }}</td></tr>
                    <tr><td><strong>NO. PELAT</strong></td><td>{{ $expedisi->PLAT ?? '-' }}</td></tr>
                    <tr><td><strong>NO. TELP</strong></td><td>&nbsp;</td></tr>
                    <tr><td colspan="2" style="height:70px; vertical-align:bottom;"><strong>TANDA TANGAN</strong><div style="margin-top:35px; border-top:1px solid #000; text-align:center;">(.................................)</div></td></tr>
                </table>
            </td>
            <td style="width:33.33%; vertical-align:top; border:none;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr><td colspan="2" style="text-align:center;"><strong>CUSTOMER</strong></td></tr>
                    <tr><td><strong>NAMA</strong></td><td>{{ $expedisi->CUSTOMER ?? '-' }}</td></tr>
                    <tr><td colspan="2" style="height:60px;">&nbsp;</td></tr>
                    <tr><td colspan="2" style="height:70px; vertical-align:bottom;"><strong>TANDA TANGAN</strong><br><small>(Stempel Perusahaan)</small><div style="margin-top:25px; border-top:1px solid #000; text-align:center;">(.................................)</div></td></tr>
                </table>
            </td>
            <td style="width:33.33%; vertical-align:top; border:none;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr><td colspan="2" style="text-align:center;"><strong>PENERIMA</strong></td></tr>
                    <tr><td><strong>NAMA</strong></td><td>{{ $expedisi->P_NAMA ?? '-' }}</td></tr>
                    <tr><td colspan="2" style="height:60px;">&nbsp;</td></tr>
                    <tr><td colspan="2" style="height:70px; vertical-align:bottom;"><strong>TANDA TANGAN</strong><br><small>(Stempel Perusahaan)</small><div style="margin-top:25px; border-top:1px solid #000; text-align:center;">(.................................)</div></td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
