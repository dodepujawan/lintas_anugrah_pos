<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>

        body{
            font-family: sans-serif;
            font-size: 11px;
        }

        .title{
            text-align:center;
            font-size:18px;
            font-weight:bold;
            margin-bottom:10px;
        }

        table{
            width:100%;
            border-collapse: collapse;
        }

        td, th{
            border:1px solid #000;
            padding:3px;
        }

        .no-border{
            border:none;
        }

        .text-center{
            text-align:center;
        }

        .text-end{
            text-align:right;
        }

        .header-table td{
            height:20px;
        }

        .detail-table td{
            height:22px;
        }

    </style>

</head>

<body>

    {{-- HEADER PERUSAHAAN --}}
    <table class="no-border" style="margin-bottom:10px;">

        <tr>

            <td class="no-border" width="70%">
                <b>PT. LINTAS MITRA ANUGERAH SEJATI</b><br>
                Jl. Raya Semeru No. 5<br>
                Denpasar - Bali
            </td>

            <td class="no-border text-end">
                <b>No : {{ $header->NOMUAT }}</b>
            </td>

        </tr>

    </table>


    {{-- TITLE --}}
    <div class="title">
        LAPORAN PERJALANAN
    </div>


    {{-- HEADER --}}
    <table class="header-table" style="margin-bottom:10px;">

        <tr>
            <td width="20%">TANGGAL</td>
            <td width="30%">{{ \Carbon\Carbon::parse($header->TGLMUAT)->format('d-m-Y') }}</td>

            <td width="20%">RUTE</td>
            <td width="30%">{{ $header->rute }}</td>
        </tr>

        <tr>
            <td>PLAT NOMOR</td>
            <td>{{ $header->PLAT_NOMOR }}</td>

            <td>KM AWAL</td>
            <td>{{ $header->KM_AWAL }}</td>
        </tr>

        <tr>
            <td>DRIVER 1</td>
            <td>{{ $header->NAMA_DRIVER }}</td>

            <td>KM AKHIR</td>
            <td>{{ $header->KM_AKHIR }}</td>
        </tr>

        <tr>
            <td>DRIVER 2</td>
            <td>{{ $header->NAMA_DRIVER2 }}</td>

            <td>KENDARAAN</td>
            <td>{{ $header->NAMA_KENDARAAN }}</td>
        </tr>

        <tr>
            <td>UANG JALAN</td>
            <td>Rp. {{ number_format($header->UANG_JALAN,0) }}</td>

            <td></td>
            <td></td>
        </tr>

        <tr>
            <td>UANG DRIVER+MAKAN</td>
            <td>Rp. {{ number_format($header->UANG_DRIVER_MAKAN,0) }}</td>

            <td></td>
            <td></td>
        </tr>

        <tr>
            <td>UANG LAIN-LAIN</td>
            <td>Rp. {{ number_format($header->UANG_LAIN_LAIN,0) }}</td>

            <td></td>
            <td></td>
        </tr>

    </table>


    {{-- DETAIL --}}
    <table class="detail-table">

        <thead>

            <tr>

                <th width="5%">No</th>
                <th width="35%">SURAT JALAN (SJ)</th>
                <th width="45%">Pengirim</th>
                <th width="15%">Berat (Kg)</th>
                {{-- <th width="15%">Nilai Tagihan</th> --}}

            </tr>

        </thead>

        <tbody>

            @php
                $totalKg = 0;
            @endphp

            @for($i = 0; $i < 20; $i++)

                @php
                    $row = $data[$i] ?? null;

                    if($row){
                        $totalKg += $row->JUMLAH;
                    }
                @endphp

                <tr>

                    <td class="text-center">
                        {{ $i + 1 }}
                    </td>

                    <td>
                        {{ $row->NOSJ ?? '' }}
                    </td>

                    <td>
                        {{ $row->PENGIRIM ?? '' }}
                    </td>

                    <td class="text-end">
                        {{ isset($row->JUMLAH) ? number_format($row->JUMLAH,0) : '' }}
                    </td>

                    {{-- KOSONGKAN NILAI TAGIHAN --}}
                    {{-- <td>
                        Rp.
                    </td> --}}

                </tr>

            @endfor

        </tbody>

        <tfoot>

            <tr>

                <th colspan="3" class="text-center">
                    TOTAL
                </th>

                <th class="text-end">
                    {{ number_format($totalKg,0) }}
                </th>

                {{-- <th>
                    Rp.
                </th> --}}

            </tr>

        </tfoot>

    </table>


    {{-- TTD --}}
    <table style="margin-top:30px;">

        <tr>

            <td class="text-center no-border">
                Driver
            </td>

            <td class="text-center no-border">
                Operational
            </td>

            <td class="text-center no-border">
                Manager Operational
            </td>

            <td class="text-center no-border">
                Accounting
            </td>

        </tr>

        <tr>

            <td height="80" class="no-border"></td>
            <td class="no-border"></td>
            <td class="no-border"></td>
            <td class="no-border"></td>

        </tr>

    </table>

</body>
</html>
