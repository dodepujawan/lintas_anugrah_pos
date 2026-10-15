<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Courier New, sans-serif; font-size: 11px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .company { font-size: 14px; font-weight: bold; }
        .invoice-title { text-align: center; font-size: 16px; font-weight: bold; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        .no-border td { border: none; padding: 2px; vertical-align: top; }
        .table-detail th,
        .table-detail td { border: 1px solid #000; padding: 4px; }
        .table-detail th { text-align: center; background: #f5f5f5; font-size: 9px; }
        .table-detail td { font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 15px; }
        .sign { margin-top: 40px; text-align: center; }
        .font-monospace { font-family: monospace; }
    </style>
</head>
<body>

<div class="header">
    <table class="no-border">
        <tr>
            <td width="70%" class="company">
                PT. LINTAS MITRA ANUGERAH SEJATI
            </td>
            <td width="30%" class="text-right">
                <strong>INVOICE</strong>
            </td>
        </tr>
    </table>
</div>

<table class="no-border">
    <tr>
        <td width="55%">
            <strong>Kepada :</strong><br>
            {{ $arh->CUSTOMER ?? '-' }}<br>
            {{ $arh->KETERANGAN ?? '-' }}
        </td>
        <td width="45%">
            <span class="font-monospace">Nomor :</span> {{ $arh->NOFAKTUR ?? '-' }}<br>
            Tanggal : {{ $arh->TGLFAKTUR?->format('d-m-Y') ?? '-' }}<br>
            Keterangan : {{ $arh->KETERANGAN ?? '-' }}
        </td>
    </tr>
</table>

<br>

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
        @foreach($details as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $row->NOSJ ?? '' }}</td>
            <td>
                {{ trim($row->PESANANGB ?? '') !== '' ? ($row->PESANANGB ?? '') : ($row->PESANAN ?? '-') }}
            </td>
            <td class="text-center">{{ $row->UNIT ?? 'KG' }}</td>
            <td class="text-right">
                {{ number_format((float) ($row->HARGA ?? 0), 0, ',', '.') }}
            </td>
            <td class="text-right">
                {{ number_format((float) ($row->TOTAL ?? 0), 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
        @if($details->isEmpty())
        <tr><td colspan="6" class="text-center">Tidak ada detail SJ.</td></tr>
        @endif
    </tbody>
</table>

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
                        <td class="text-right">
                            {{ number_format((float) ($details->sum('TOTAL') ?? 0), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>Discount</td>
                        <td class="text-right">
                            {{ number_format((float) ($arh->DISCOUNT ?? 0), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>PPN</td>
                        <td class="text-right">
                            {{ number_format((float) (($details->sum('TOTAL') - ($arh->DISCOUNT ?? 0)) * ($details->first()->PPN ?? 0) / 100), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="text-right">
                            <strong>{{ number_format((float) ($arh->PIUTANG ?? 0), 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Dibayar</td>
                        <td class="text-right">
                            {{ number_format((float) ($arh->BAYAR ?? 0), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Saldo</strong></td>
                        <td class="text-right">
                            <strong>{{ number_format((float) ($arh->SALDO ?? 0), 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<table class="no-border sign">
    <tr>
        <td width="33%">Penerima</td>
        <td width="33%">Mengetahui</td>
        <td width="33%">
            Dicetak : {{ now()->format('d-m-Y') }}<br>
            User : {{ auth()->user()->name ?? '-' }}
        </td>
    </tr>
    <tr>
        <td height="60"></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td><strong>{{ $signature->NAMA ?? '__________________' }}</strong><br>
        <small>{{ $signature->JABATAN ?? '' }}</small></td>
        <td></td>
    </tr>
</table>

</body>
</html>
