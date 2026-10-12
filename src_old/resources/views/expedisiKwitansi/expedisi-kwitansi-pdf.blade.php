<style>
body {
    font-family: sans-serif;
    font-size: 12px;
}

.header-title {
    text-align: center;
    font-weight: bold;
    font-size: 16px;
}

.sub-title {
    text-align: center;
    font-size: 11px;
    margin-bottom: 10px;

}

hr {
    border: 1px solid black;
}

.box {
    border: 1px solid black;
    border-radius: 8px;
    padding: 6px;
}

.text-right {
    text-align: right;
}

.text-bold {
    font-weight: bold;
}
</style>


<!-- HEADER -->
<div class="header-title">
    PT. LINTAS MITRA ANUGERAH SEJATI
</div>
<div class="sub-title">
    COLD CHAIN DISTRIBUTION & STORAGE
</div>

<table width="100%">
<tr>
<td width="50%">
Jl. Raya Sempidi No.9 Badung - Bali<br>
Telp/Fax : (0361) 8947610<br>
Jl. Bija Taki IV, No.9 Denpasar - Bali
</td>

<td width="50%" class="text-right">
BizPark Commercial Estate<br>
Jl. Sultan Agung KM 28,5 Bekasi<br>
www.lintasmitralogistik.com
</td>
</tr>
</table>

<hr>

<!-- KWITANSI -->
<p class="text-bold">
KWITANSI NO : {{ $master->kwt }}
</p>

<br>

<table width="100%">
<tr>
<td width="25%">SUDAH TERIMA DARI</td>
<td width="5%">:</td>
<td width="70%">
    <div>
        {{ $master->CUSTOMER }}
    </div>
</td>
</tr>

<tr>
<td>BANYAKNYA UANG</td>
<td>:</td>
<td>
    <div>
        {{ ucwords(terbilang(optional($master)->GRAND ?? 0)) }} Rupiah
    </div>
</td>
</tr>

<tr>
<td>UNTUK PEMBAYARAN</td>
<td>:</td>
<td>
    <div>
        {{ $details->pluck('NOSJ')->implode(', ') }}
        invoice : {{ $master->INVOICE }}
    </div>
</td>
</tr>
</table>

<hr>

<p>
Untuk pembayaran mohon di transfer ke rek resmi<br>
<b>A/n. PT. Lintas Mitra Anugerah Sejati</b><br>
No.rek BCA 6115352010
</p>

<br>

<table width="100%">
<tr>
<td width="50%">
CEK/GIRO NO. ______________________
</td>

<td width="50%" class="text-right">
Denpasar {{ \Carbon\Carbon::parse($master->TGLKW)->format('d-m-Y') }}
</td>
</tr>
</table>

<br><br>

<table width="100%">
<tr>
<td width="50%">
<b>JUMLAH</b>
<div style="width:200px;">
    RP. {{ number_format(optional($master)->GRAND ?? 0, 0, ',', '.') }}
</div>
</td>

<td width="50%" class="text-right">
<br><br>
{{ $signature->nama ?? '' }}
</td>
</tr>
</table>
