{{ str_repeat('=', 80) }}
                         PT. LINTAS ANUGERAH SEJATI
                            INVOICE RENT PENDINGIN
{{ str_repeat('=', 80) }}
NO. INVOICE : {{ $invoice->INVOICE }}
TANGGAL     : {{ $invoice->TGLINVOICE?->format('d-m-Y') ?? '-' }}
JATUH TEMPO : {{ ($arh?->TGLJT ?? $invoice->TGLJT)?->format('d-m-Y') ?? '-' }}
CUSTOMER    : {{ $invoice->CUSTOMER ?: '-' }}
NO. MUAT    : {{ $invoice->NOMUAT ?: '-' }}
NO. SJ      : {{ $details->pluck('NOSJ')->filter()->implode(', ') ?: '-' }}
KENDARAAN   : {{ $invoice->NAMA_KENDARAAN ?: '-' }}
{{ str_repeat('-', 80) }}
NO  NO. SURAT JALAN       PESANAN / RENTAL                          GRAND
{{ str_repeat('-', 80) }}
@foreach($details as $row)
{{ str_pad((string) $loop->iteration, 3) }} {{ str_pad((string) $row->NOSJ, 22) }} {{ str_pad((string) ($row->PESANAN ?: 'RENT PENDINGIN'), 40) }} {{ number_format((float) $row->GRAND, 0, ',', '.') }}
@endforeach
{{ str_repeat('-', 80) }}
SUBTOTAL       : Rp {{ number_format((float) $invoice->JUMLAH * (float) $invoice->HARGA, 0, ',', '.') }}
DISKON         : - Rp {{ number_format((float) $invoice->NDISC, 0, ',', '.') }}
TOTAL NET      : Rp {{ number_format((float) $invoice->TOTAL, 0, ',', '.') }}
D. CHARGE      : Rp {{ number_format((float) $invoice->DC, 0, ',', '.') }}
PPN ({{ number_format((float) $invoice->PPN, 2, ',', '.') }}%)     : Rp {{ number_format(round((float) $invoice->TOTAL * (float) $invoice->PPN / 100), 0, ',', '.') }}
GRAND TOTAL    : Rp {{ number_format((float) $invoice->GRAND, 0, ',', '.') }}
BAYAR          : Rp {{ number_format((float) $invoice->BAYAR, 0, ',', '.') }}
SISA PIUTANG   : Rp {{ number_format((float) $invoice->PIUTANG, 0, ',', '.') }}
{{ str_repeat('=', 80) }}
Dicetak {{ now()->format('d-m-Y H:i') }}
