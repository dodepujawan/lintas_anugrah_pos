<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{
            font-family:sans-serif;
            font-size:12px;
            color:#000;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        .border-top{
            border-top:1px solid #000;
        }

        .border-bottom{
            border-bottom:1px solid #000;
        }

        .text-right{
            text-align:right;
        }

        .text-center{
            text-align:center;
        }

        .mt-10{
            margin-top:10px;
        }

        .mt-20{
            margin-top:20px;
        }

        .mt-30{
            margin-top:30px;
        }

        .signature-line{
            border-top:1px solid #000;
            width:120px;
            margin-top:50px;
        }

    </style>
</head>
<body>

    {{-- ========================= --}}
    {{-- HEADER --}}
    {{-- ========================= --}}
    <table>
        <tr>
            <td style="width:50%;">
                <h2 style="letter-spacing:5px;">
                    I N V O I C E
                </h2>
            </td>

            <td style="width:50%;text-align:right;">
                <h2>
                    PT. LINTAS ANUGERAH SEJATI
                </h2>
            </td>
        </tr>
    </table>
    <hr>

    {{-- ========================= --}}
    {{-- HEADER INFO --}}
    {{-- ========================= --}}
    <table class="mt-10">
        <tr>
            {{-- KIRI --}}
            <td style="width:50%;vertical-align:top;">
                <table>
                    <tr>
                        <td style="width:120px;">
                            NOMOR
                        </td>
                        <td>
                            : {{ $master->INVOICE }}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            NOMOR SJ
                        </td>
                        <td>
                            : {{ $master->NOSJ }}
                        </td>
                    </tr>
                </table>
            </td>

            {{-- KANAN --}}
            <td style="width:50%;vertical-align:top;">
                <table>
                    <tr>
                        <td style="width:150px;">
                            TANGGAL
                        </td>
                        <td>
                            :
                            {{ \Carbon\Carbon::parse($master->TGLINVOICE)->translatedFormat('l, d F Y') }}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            TGL JATUH TEMPO
                        </td>
                        <td>
                            :
                            {{ $arh && $arh->TGLJT ? \Carbon\Carbon::parse($arh->TGLJT)->translatedFormat('l, d F Y') : '-' }}

                        </td>
                    </tr>

                    <tr>
                        <td>
                            KETERANGAN
                        </td>
                        <td>
                            : SEWA COOLROOM
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ========================= --}}
    {{-- CUSTOMER --}}
    {{-- ========================= --}}
    <table class="mt-20">
        <tr>
            <td style="width:80px;">
                KEPADA
            </td>
            <td>
                : {{ $master->CUSTOMER }}
            </td>
        </tr>

        <tr>
            <td>
                UP
            </td>
            <td>
                : {{ $customer->UP ?? '-' }}
            </td>
        </tr>

        <tr>
            <td>
                ALAMAT
            </td>
            <td>
                : {{ $customer->ALAMAT ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- ========================= --}}
    {{-- DETAIL --}}
    {{-- ========================= --}}
    <table class="mt-20">
        <thead>
            <tr class="border-top border-bottom">
                <th style="padding:8px;text-align:left;">
                    KETERANGAN
                </th>
                <th class="text-center">
                    JUMLAH
                </th>
                <th class="text-center">
                    UNIT
                </th>
                <th class="text-right">
                    HARGA
                </th>
                <th class="text-right">
                    DISC
                </th>
                <th class="text-right">
                    TOTAL
                </th>
                <th class="text-right">
                    PPN %
                </th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td style="padding:10px 5px;">
                    {{ $master->KETERANGAN ?? 'SEWA COOLROOM' }}
                </td>
                <td class="text-center">
                    {{ number_format($master->JUMLAH ?? 0,0,',','.') }}
                </td>
                <td class="text-center">
                    {{ $master->UNIT ?? 'KG' }}
                </td>
                <td class="text-right">
                    {{ number_format($master->HARGA ?? 0,0,',','.') }}
                </td>
                <td class="text-right">
                    {{ number_format($master->DISC ?? 0,0,',','.') }}
                </td>
                <td class="text-right">
                    {{ number_format($master->TOTAL ?? 0,0,',','.') }}
                </td>
                <td class="text-right">
                    {{ number_format($master->PPN ?? 0,2) }}
                </td>
            </tr>
        </tbody>
    </table>
    <hr>

    {{-- ========================= --}}
    {{-- PAYMENT --}}
    {{-- ========================= --}}
    <table class="mt-20">
        <tr>
            {{-- LEFT --}}
            <td style="width:60%;vertical-align:top;">
                Untuk pembayaran mohon di transfer ke rekening resmi :
                <table class="mt-10">
                    <tr>
                        <td style="width:80px;">
                            No.rek
                        </td>
                        <td>
                            : 6115352010
                        </td>
                    </tr>

                    <tr>
                        <td>
                            A/n
                        </td>
                        <td>
                            : PT. Lintas Mitra Anugerah Sejati
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Bank
                        </td>
                        <td>
                            : BCA
                        </td>
                    </tr>
                </table>

                <div class="mt-20">
                    Terbilang :
                </div>

                <div class="mt-10">
                    {{ ucwords(terbilang($master->GRAND ?? 0)) }} Rupiah
                </div>
            </td>

            {{-- RIGHT --}}
            <td style="width:40%;vertical-align:top;">
                <table>
                    <tr>
                        <td class="text-right">
                            DPP :
                        </td>

                        <td class="text-right" style="width:120px;">
                            {{ number_format($master->DPP ?? 0,0,',','.') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="text-right">
                            PAJAK :
                        </td>
                        <td class="text-right">
                            {{ number_format($master->NPPN ?? 0,0,',','.') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="text-right">
                            TOTAL :
                        </td>
                        <td class="text-right">
                            {{ number_format($master->GRAND ?? 0,0,',','.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            DIBAYAR :
                        </td>
                        <td class="text-right">
                            {{ number_format($master->BAYAR ?? 0,0,',','.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right" style="font-weight:bold;font-size:14px;">
                            SALDO :
                        </td>
                        <td class="text-right" style="font-weight:bold;font-size:14px;">
                            {{ number_format($master->PIUTANG ?? 0,0,',','.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    {{-- ========================= --}}
    {{-- SIGN --}}
    {{-- ========================= --}}
    <table class="mt-30">
        <tr>
            <td class="text-center" style="width:33%;">
                Penerima
                <div class="signature-line"></div>
            </td>
            <td class="text-center" style="width:33%;">
                Mengetahui
                <div class="signature-line">
                    {{ $signature->nama ?? '' }}
                </div>
            </td>
            <td class="text-center" style="width:33%;">
                dicetak :
                {{ now()->format('d-m-Y') }}
                <br><br>
                User :
                {{ auth()->user()->user_id ?? '-' }}

            </td>
        </tr>
    </table>
</body>
</html>
