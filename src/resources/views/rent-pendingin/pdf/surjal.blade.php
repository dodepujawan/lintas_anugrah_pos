<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Rent Pendingin {{ $expedisi->NOSJ }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        table { border-collapse: collapse; width: 100%; }
        .header td { vertical-align: top; }
        .box { border: 1px solid #000; padding: 8px; }
        .small { font-size: 11px; }
        th, td { padding: 6px 8px; border: 1px solid #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .muted { color: #444; }
        .no-border td { border: none; padding: 2px 0; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:60%;">
                <strong>PT. LINTAS MITRA ANUGERAH SEJATI</strong><br>
                <span class="small">Cold Chain Distribution & Storage</span>
            </td>
            <td style="width:40%; text-align:right;">
                <strong>SURAT JALAN RENT PENDINGIN</strong><br>
                NO. {{ $expedisi->NOSJ ?? '-' }}
            </td>
        </tr>
    </table>

    <div style="height:12px;"></div>

    <table class="box">
        <tr>
            <td style="width:20%;">Tanggal</td>
            <td style="width:30%;">{{ $expedisi->TGLMUAT?->format('d/m/Y') ?? '-' }}</td>
            <td style="width:20%;">Customer</td>
            <td style="width:30%;">{{ $expedisi->CUSTOMER ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kendaraan</td>
            <td>{{ $expedisi->NAMA_KENDARAAN ?? $expedisi->KENDARAAN ?? '-' }}</td>
            <td>Driver</td>
            <td>{{ $expedisi->NAMA_DRIVER ?? $expedisi->DRIVER ?? '-' }}</td>
        </tr>
        <tr>
            <td>Rute</td>
            <td>{{ $expedisi->rute ?? '-' }}</td>
            <td>Unit</td>
            <td>{{ $expedisi->UNIT ?? 'HARI' }}</td>
        </tr>
    </table>

    <div style="height:12px;"></div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:8%;">No</th>
                <th style="width:42%;">Keterangan</th>
                <th class="text-center" style="width:15%;">Jumlah</th>
                <th class="text-right" style="width:20%;">Harga</th>
                <th class="text-right" style="width:15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{{ $expedisi->PESANAN ?? 'RENT PENDINGIN' }}</td>
                <td class="text-center">{{ number_format((float) ($expedisi->JUMLAH ?? 0), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format((float) ($expedisi->HARGA ?? 0), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format((float) ($expedisi->GRAND ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>Disc</strong></td>
                <td class="text-right">{{ number_format((float) ($expedisi->NDISC ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>PPN</strong></td>
                <td class="text-right">{{ number_format((float) ($expedisi->PPN ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>Grand Total</strong></td>
                <td class="text-right"><strong>{{ number_format((float) ($expedisi->GRAND ?? 0), 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div style="height:18px;"></div>

    <table class="no-border">
        <tr>
            <td style="width:33%; text-align:center;">Driver</td>
            <td style="width:33%; text-align:center;">Customer</td>
            <td style="width:33%; text-align:center;">Accounting</td>
        </tr>
        <tr>
            <td style="height:60px; text-align:center; vertical-align:bottom;">(.....................)</td>
            <td style="height:60px; text-align:center; vertical-align:bottom;">(.....................)</td>
            <td style="height:60px; text-align:center; vertical-align:bottom;">(.....................)</td>
        </tr>
    </table>
</body>
</html>
