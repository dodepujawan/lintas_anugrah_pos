================================================================
           PT. LINTAS MITRA ANUGERAH SEJATI
        COLD CHAIN DISTRIBUTION & STORAGE
        Denpasar - Bali | Telp: (0361) 8947610
================================================================

INVOICE
--------------------------------------------------------------------------------
No. Invoice : {{ $arh->NOFAKTUR ?? '-' }}
Tgl Invoice : {{ $arh->TGLFAKTUR?->format('d-m-Y') ?? '-' }}
Tgl JT      : {{ $arh->TGLJT?->format('d-m-Y') ?? '-' }}
Customer    : {{ $arh->CUSTOMER ?? '-' }}
Sales       : {{ $arh->SALES ?? '-' }}
Keterangan  : {{ $arh->KETERANGAN ?? '-' }}
--------------------------------------------------------------------------------

DAFTAR SURAT JALAN:
@if($details && $details->isNotEmpty())
@foreach($details as $i => $d)
{{ $i + 1 }}. {{ $d->NOSJ ?? '-' }} | {{ $d->PESANANGB ?? $d->PESANAN ?? '-' }} | {{ number_format((float)($d->TOTAL ?? 0), 0, ',', '.') }}
@endforeach
@else
(Tidak ada detail SJ)
@endif

--------------------------------------------------------------------------------
SUB TOTAL       : Rp. {{ number_format((float) ($details->sum('TOTAL') ?? 0), 0, ',', '.') }}
DISCOUNT        : Rp. {{ number_format((float) ($arh->DISCOUNT ?? 0), 0, ',', '.') }}
PPN             : Rp. {{ number_format((float) ($arh->PIUTANG * 0.11), 0, ',', '.') }}
TOTAL           : Rp. {{ number_format((float) ($arh->PIUTANG ?? 0), 0, ',', '.') }}
BAYAR           : Rp. {{ number_format((float) ($arh->BAYAR ?? 0), 0, ',', '.') }}
SALDO           : Rp. {{ number_format((float) ($arh->SALDO ?? 0), 0, ',', '.') }}
--------------------------------------------------------------------------------

TERBILANG: {{ ucwords(\App\Helpers\Helper::terbilang((float) ($arh->PIUTANG ?? 0))) }} Rupiah

Untuk pembayaran mohon di transfer ke rek resmi:
No. Rek : {{ $rekening->NOREK ?? '-' }}
A/n     : {{ $rekening->NAMA ?? '-' }}
Bank    : {{ $rekening->BANK ?? '-' }}

DICETAK : {{ now()->format('d-m-Y H:i') }}
USER    : {{ auth()->user()->name ?? '-' }}
TTD     : {{ $signature->NAMA ?? '-' }}

================================================================
