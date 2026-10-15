<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { margin-bottom: 10px; }
        .company { font-weight: bold; font-size: 13px; }
        .sub-title { font-size: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td, th { border: 1px solid #000; padding: 4px 6px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .no-border { border: none !important; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin: 10px 0; }
        .detail-table td { height: 22px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="no-border">
            <tr>
                <td class="no-border" width="70%">
                    <span class="company">PT. LINTAS MITRA ANUGERAH SEJATI</span><br>
                    Jl. Raya Semeru No. 5<br>
                    Denpasar - Bali
                </td>
                <td class="no-border text-right">
                    @if($expedisis->isNotEmpty())
                    <b>No : {{ $expedisis->first()->NOMUAT ?? '-' }}</b>
                    @else
                    <b>No : -</b>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="title">
        LAPORAN PERJALANAN
    </div>

    @if($expedisis->isNotEmpty())
    @php $header = $expedisis->first(); @endphp
    <table class="detail-table">
        <tr>
            <td width="20%">TANGGAL</td>
            <td width="30%">{{ \Carbon\Carbon::parse($header->TGLMUAT)->format('d-m-Y') }}</td>
            <td width="20%">RUTE</td>
            <td width="30%">{{ $header->rute ?? $header->RUTE ?? '-' }}</td>
        </tr>
        <tr>
            <td>PLAT NOMOR</td>
            <td>{{ $header->PLAT ?? '-' }}</td>
            <td>KM AWAL</td>
            <td>{{ $header->KM_AWAL ?? $header->JUMLAH ?? 0 }}</td>
        </tr>
        <tr>
            <td>DRIVER 1</td>
            <td>{{ $header->NAMA_DRIVER ?? $header->DRIVER ?? '-' }}</td>
            <td>KM AKHIR</td>
            <td>{{ $header->KM_AKHIR ?? $header->JUMLAH ?? 0 }}</td>
        </tr>
        <tr>
            <td>DRIVER 2</td>
            <td>{{ $header->NAMA_DRIVER2 ?? $header->DRIVER2 ?? '-' }}</td>
            <td>KENDARAAN</td>
            <td>{{ $header->NAMA_KENDARAAN ?? $header->KENDARAAN ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:35%">SURAT JALAN (SJ)</th>
                <th style="width:45%">Pengirim</th>
                <th style="width:15%">Berat (Kg)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalKg = 0; @endphp
            @foreach($expedisis as $i => $row)
                @php $totalKg += (float) ($row->JUMLAH ?? 0); @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row->NOSJ ?? '' }}</td>
                    <td>{{ $row->CUSTOMER ?? '' }}</td>
                    <td class="text-end">{{ number_format((float) ($row->JUMLAH ?? 0), 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @for($i = $expedisis->count(); $i < 20; $i++)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-center">TOTAL</th>
                <th class="text-end">{{ number_format($totalKg, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <table>
        <tr class="no-border">
            <td class="no-border text-center" style="width:25%">
                Driver
            </td>
            <td class="no-border text-center" style="width:25%">
                Operational
            </td>
            <td class="no-border text-center" style="width:25%">
                Manager Operational
            </td>
            <td class="no-border text-center" style="width:25%">
                Accounting
            </td>
        </tr>
        <tr class="no-border">
            <td class="no-border" style="height:80px;">&nbsp;</td>
            <td class="no-border">&nbsp;</td>
            <td class="no-border">&nbsp;</td>
            <td class="no-border">&nbsp;</td>
        </tr>
    </table>
    @endif
</body>
</html>
