<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { font-size: 14px; margin: 0; }
        .header h3 { font-size: 11px; margin: 2px 0; }
        .kwt { text-align: center; margin-top: 20px; margin-bottom: 10px; }
        .kwt h3 { font-size: 14px; font-weight: bold; }
        .content { page-break-after: always; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td, th { border: 1px solid #000; padding: 3px 5px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
<div class="content">
    <div class="header">
        <h2>{{ config('app.name', 'FinLedger') }}</h2>
    </div>

    <div class="kwt">
        <h3>BUKTI KAS &mdash; {{ $expedisi->JENIS_KWT ?? 'EKS' }}</h3>
    </div>

    <table>
        <tr>
            <th style="width:15%">No. KWT</th>
            <td>{{ $kwitansi->NO_KWT }}</td>
            <th style="width:15%">Tgl KWT</th>
            <td>{{ $kwitansi->TGL_KWT?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Invoice</th>
            <td>{{ $kwitansi->NOFAKTUR }}</td>
            <th>Sales</th>
            <td>{{ $arh->SALES ?? $arh->CUSTOMER ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <tr class="table-light">
            <th style="width:15%">Saldo Faktur</th>
            <th style="width:15%">Bayar</th>
            <th style="width:15%">Discount</th>
            <th style="width:15%">Retur</th>
            <th style="width:15%">Sisa</th>
            <th style="width:25%">Keterangan</th>
        </tr>
        <tr>
            <td class="text-right">{{ number_format($kwitansi->SALDO_FAKTUR, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($kwitansi->NILAI_BAYAR, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($kwitansi->DISCOUNT, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($kwitansi->RETUR, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($kwitansi->SALDO_FAKTUR - $kwitansi->BAYAR, 0, ',', '.') }}</td>
            <td>{{ $kwitansi->KETERANGAN }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td style="width:50%">
                <strong>Customer:</strong> {{ $kwitansi->CUSTOMER }}<br>
                <strong>Nama:</strong> {{ $kwitansi->NAMACUST ?? '-' }}
            </td>
            <td style="width:50%; text-align:right;">
                <strong>USER:</strong> {{ $kwitansi->USEREDIT ?? $kwitansi->user ?? '-' }}<br>
                <strong>TTD:</strong> {{ $signature->NAMA ?? '_________________' }}
            </td>
        </tr>
    </table>
</div>
</body>
</html>
