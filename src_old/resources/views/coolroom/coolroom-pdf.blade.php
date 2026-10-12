<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Surat Jalan Coolroom</title>

<style>

body{
    font-family:sans-serif;
    font-size:11px;
}

table{
    width:100%;
    border-collapse:collapse;
}

td,th{
    border:1px solid #000;
    padding:5px;
}

.text-center{
    text-align:center;
}

.text-end{
    text-align:right;
}

.no-border{
    border:none;
}

.header{
    text-align:center;
    margin-bottom:15px;
}

.title{
    font-size:16px;
    font-weight:bold;
    margin-top:10px;
    text-decoration:underline;
}

.mt{
    margin-top:15px;
}

</style>

</head>

<body>

<div class="header">

    <h3 style="margin:0;">
        PT. LINTAS MITRA ANUGERAH SEJATI
    </h3>

    <div>
        COLD STORAGE & COOLROOM
    </div>

    <div>
        Jl. Bajataki IV No.9 Denpasar
    </div>

    <div class="title">
        SURAT JALAN COOLROOM
    </div>

</div>

<table>

    <tr>
        <td width="25%">
            TANGGAL
        </td>

        <td width="75%">
            {{ date('d-m-Y',strtotime($coolroom->TGLSJ)) }}
        </td>
    </tr>

    <tr>
        <td>
            NO SJ
        </td>

        <td>
            {{ $coolroom->NOSJ }}
        </td>
    </tr>

    <tr>
        <td>
            CUSTOMER
        </td>

        <td>
            {{ $coolroom->CUSTOMER }}
        </td>
    </tr>

</table>

<div class="mt"></div>

<table>

    <thead>

        <tr>

            <th width="5%">
                NO
            </th>

            <th>
                KETERANGAN
            </th>

            <th width="10%">
                QTY
            </th>

            <th width="10%">
                UNIT
            </th>

            <th width="15%">
                HARGA
            </th>

            <th width="15%">
                TOTAL
            </th>

        </tr>

    </thead>

    <tbody>

        <tr>

            <td class="text-center">
                1
            </td>

            <td>
                {{ $coolroom->KETERANGAN ?? 'SEWA COOLROOM' }}
            </td>

            <td class="text-end">
                {{ number_format($coolroom->JUMLAH,3,',','.') }}
            </td>

            <td class="text-center">
                {{ $coolroom->UNIT }}
            </td>

            <td class="text-end">
                {{ number_format($coolroom->HARGA,0,',','.') }}
            </td>

            <td class="text-end">
                {{ number_format($coolroom->SUBTOTAL,0,',','.') }}
            </td>

        </tr>

    </tbody>

</table>

<div class="mt"></div>

<table>

    <tr>
        <td width="70%" class="no-border"></td>

        <td width="15%">
            SUBTOTAL
        </td>

        <td width="15%" class="text-end">
            {{ number_format($coolroom->SUBTOTAL,0,',','.') }}
        </td>
    </tr>

    <tr>
        <td class="no-border"></td>

        <td>
            DISC
        </td>

        <td class="text-end">
            {{ number_format($coolroom->NDISC,0,',','.') }}
        </td>
    </tr>

    <tr>
        <td class="no-border"></td>

        <td>
            PPN
        </td>

        <td class="text-end">
            {{ number_format($coolroom->NPPN,0,',','.') }}
        </td>
    </tr>

    <tr>

        <td class="no-border"></td>

        <td>
            GRAND
        </td>

        <td class="text-end">
            <strong>
                {{ number_format($coolroom->GRAND,0,',','.') }}
            </strong>
        </td>

    </tr>

</table>

<div class="mt"></div>

<table>
    <tr>
        <td height="80" class="text-center">
            DIBUAT OLEH
            <br><br><br><br>
            <strong>
                {{ $user->name ?? $coolroom->USERINPUT }}
            </strong>
        </td>
        <td class="text-center">
            DIKETAHUI
            <br><br><br><br>
            <strong>
                {{ $signature->nama ?? '-' }}
            </strong>
        </td>
        <td class="text-center">
            DITERIMA
            <br><br><br><br>
            <strong>
                {{ $coolroom->CUSTOMER }}
            </strong>
        </td>
    </tr>
</table>

</body>
</html>
