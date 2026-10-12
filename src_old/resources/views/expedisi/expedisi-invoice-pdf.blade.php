<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
        } */

        body{
            font-family: Courier New;
            font-size: 11px;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
        }

        .company {
            font-size: 14px;
            font-weight: bold;
        }

        .invoice-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-border td {
            border: none;
            padding: 2px;
            vertical-align: top;
        }

        .table-detail th,
        .table-detail td {
            border: 1px solid #000;
            padding: 4px;
        }

        .table-detail th {
            text-align: center;
            background: #f5f5f5;
        }

        .right { text-align: right; }
        .center { text-align: center; }

        .footer {
            margin-top: 15px;
        }

        .sign {
            margin-top: 40px;
            text-align: center;
        }
    </style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <table class="no-border">
        <tr>
            <td width="70%" class="company">
                PT. LINTAS ANUGERAH SEJATI
            </td>
            <td width="30%" class="right">
                <strong>INVOICE</strong>
            </td>
        </tr>
    </table>
</div>

{{-- INFO ATAS --}}
<table class="no-border">
    <tr>
        <td width="55%">
            <strong>Kepada :</strong><br>
            {{ $master->CUSTOMER }}<br>
            {{ $master->ALAMAT ?? '-' }}
        </td>
        <td width="45%">
            Nomor : {{ $master->INVOICE }}<br>
            Tanggal : {{ \Carbon\Carbon::parse($master->TGLINVOICE)->format('d-m-Y') }}<br>
            Keterangan :
        </td>
    </tr>
</table>

<br>

{{-- TABLE DETAIL --}}
<table class="table-detail">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="15%">No SJ</th>
            <th width="40%">Nama Barang / Pesanan</th>
            <th width="10%">Unit</th>
            <th width="15%">Harga @</th>
            <th width="15%">Sub Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $i => $row)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $row->NOSJ }}</td>
            {{-- Sengaja Diisi IF soalnya di pendingin gak pakai pesanangb --}}
            <td>
                {{ trim($row->PESANANGB) !== '' ? $row->PESANANGB : $row->PESANAN }}
            </td>
            <td class="center">KG</td>
            <td class="right">
                {{ number_format($row->HARGA, 0, ',', '.') }}
            </td>
            <td class="right">
                {{ number_format($row->TOTAL, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- FOOTER --}}
<div class="footer">
    <table class="no-border">
        <tr>
            <td width="60%">
                <strong>Untuk pembayaran mohon di transfer ke rek resmi :</strong><br>
                No. Rek : {{ $rekening->NOREK ?? '' }}<br>
                A/n : {{ $rekening->NAMA ?? '' }}<br>
                Bank : {{ $rekening->BANK ?? '' }}
            </td>
            <td width="40%">
                <table>
                    <tr>
                        <td>Sub Total</td>
                        <td class="right">
                            {{ number_format($master->TOTAL, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>Pajak ({{ $master->PPN }}%)</td>
                        <td class="right">
                            {{ number_format($master->TOTAL * $master->PPN / 100, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="right">
                            <strong>{{ number_format($master->GRAND, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Dibayar</td>
                        <td class="right">0</td>
                    </tr>
                    <tr>
                        <td>Saldo</td>
                        <td class="right">
                            {{ number_format($master->GRAND, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

{{-- TANDA TANGAN --}}
<table class="no-border sign">
    <tr>
        <td width="33%">Penerima</td>
        <td width="33%">Mengetahui</td>
        <td width="33%">
            Dicetak : {{ now()->format('d-m-Y') }}<br>
            User : {{ auth()->user()->user_id }}
        </td>
    </tr>
    <tr>
        <td height="60"></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td><strong>{{ $signature->nama ?? '' }}</strong></td>
        <td></td>
    </tr>
</table>

</body>
</html>
