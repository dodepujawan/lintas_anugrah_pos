<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { margin-bottom: 10px; }
        .company { font-weight: bold; font-size: 13px; }
        .sub-title { font-size: 10px; color: #555; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td, th { border: 1px solid #000; padding: 4px 6px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .no-border { border: none !important; }
        .sign-row td { border: none !important; }
        .sign-col { width: 25%; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <table class="no-border">
            <tr>
                <td class="no-border">
                    <div class="company">PT. LINTAS MITRA ANUGERAH SEJATI</div>
                    <div class="sub-title">COLD CHAIN DISTRIBUTION &amp; STORAGE</div>
                    <div class="sub-title">Denpasar - Bali</div>
                </td>
                <td class="no-border text-right">
                    <strong>SURAT JALAN EKSPEDISI</strong><br>
                    NO SJ: <span class="font-monospace">{{ $expedisi->NOSJ ?? '-' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <tr>
            <td style="width:15%">Tanggal SJ</td>
            <td style="width:35%">{{ $expedisi->tglsj?->format('d/m/Y') ?? $expedisi->TGLMUAT?->format('d/m/Y') ?? '-' }}</td>
            <td style="width:15%">Nama Customer</td>
            <td style="width:35%">{{ $expedisi->CUSTOMER ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nama Penerima</td>
            <td>{{ $expedisi->P_NAMA ?? '-' }}</td>
            <td>No Telp</td>
            <td>{{ $expedisi->P_PHONE ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td colspan="3">{{ $expedisi->P_ALAMAT ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:25%">Item Barang</th>
                <th style="width:15%">Penyimpanan</th>
                <th style="width:10%">Suhu</th>
                <th style="width:10%">Koli</th>
                <th style="width:15%">Kg / Ton</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{{ $expedisi->barang ?? '-' }}</td>
                <td class="text-center">{{ strtoupper($expedisi->penyimpanan ?? '-') }}</td>
                <td class="text-center">{{ $expedisi->SUHU ?? '-' }}</td>
                <td class="text-center">{{ $expedisi->KOLI ?? '0' }}</td>
                <td class="text-center">{{ number_format((float) ($expedisi->JUMLAH ?? 0), 0, ',', '.') }}</td>
            </tr>
            @for ($i = 2; $i <= 6; $i++)
            <tr>
                <td class="text-center">{{ $i }}</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @endfor
        </tbody>
    </table>

    <table>
        <tr>
            <td style="width:70%; vertical-align: top;">
                <strong>Catatan Khusus:</strong><br>
                {{ $expedisi->catatan ?? '-' }}
            </td>
            <td class="text-right">Subtotal</td>
            <td class="text-right" style="width:15%">Rp. {{ number_format((float) ($expedisi->TOTAL ?? 0), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-right">PPN</td>
            <td class="text-right">Rp. {{ number_format((float) ($expedisi->PPN ?? 0), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-right"><strong>Grand Total</strong></td>
            <td class="text-right"><strong>Rp. {{ number_format((float) ($expedisi->GRAND ?? 0), 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <table>
        <tr class="sign-row">
            <td class="sign-col">
                <table class="table">
                    <tr><td class="text-center"><strong>DISTRIBUSI</strong></td></tr>
                    <tr><td><strong>NAMA SOPIR</strong></td><td>{{ $expedisi->NAMA_DRIVER ?? '-' }}</td></tr>
                    <tr><td><strong>NO. PELAT</strong></td><td>{{ $expedisi->PLAT ?? '-' }}</td></tr>
                    <tr><td colspan="2" style="height:70px; vertical-align:bottom;">
                        <div style="border-top:1px solid #000; text-align:center; margin-top:40px;">
                            (.................................)
                        </div>
                    </td></tr>
                </table>
            </td>
            <td class="sign-col">
                <table class="table">
                    <tr><td class="text-center"><strong>CUSTOMER</strong></td></tr>
                    <tr><td><strong>NAMA</strong></td><td>{{ $expedisi->CUSTOMER ?? '-' }}</td></tr>
                    <tr><td colspan="2" style="height:60px;">&nbsp;</td></tr>
                    <tr><td colspan="2" style="height:70px; vertical-align:bottom;">
                        <div style="border-top:1px solid #000; text-align:center; margin-top:25px;">
                            (.................................)
                        </div>
                    </td></tr>
                </table>
            </td>
            <td class="sign-col">
                <table class="table">
                    <tr><td class="text-center"><strong>PENERIMA</strong></td></tr>
                    <tr><td><strong>NAMA</strong></td><td>{{ $expedisi->P_NAMA ?? '-' }}</td></tr>
                    <tr><td colspan="2" style="height:60px;">&nbsp;</td></tr>
                    <tr><td colspan="2" style="height:70px; vertical-align:bottom;">
                        <div style="border-top:1px solid #000; text-align:center; margin-top:25px;">
                            (.................................)
                        </div>
                    </td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
