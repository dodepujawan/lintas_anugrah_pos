@extends('layouts.app')

@section('title', 'Piutang')

@section('content')
<div class="opening-receivable-page">
<div class="d-flex justify-content-between align-items-center mb-3 opening-receivable-header">
    <div>
        <h1 class="page-title mb-1">Saldo Awal Piutang Pelanggan</h1>
        <p class="text-muted mb-0">Khusus sisa piutang sebelum aplikasi mulai dipakai. Bukan daftar total piutang berjalan.</p>
    </div>
    <div class="d-flex gap-2 opening-receivable-toolbar">
        <a href="{{ route('piutang.export', request()->except(['page', 'print'])) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        @include('laporan._print-format-dropdown',['pdfUrl'=>route('piutang.pdf',request()->except(['page','print','print_format'])),'dotMatrixUrl'=>route('piutang.pdf',request()->except(['page','print','print_format'])+['print_format'=>'dot_matrix']),'buttonClass'=>'btn-outline-secondary'])
        <a href="{{ route('piutang.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
    </div>
</div>

<div class="opening-receivable-print-header">
    <div class="fw-bold">SALDO AWAL PIUTANG</div>
    <div>Dicetak {{ now()->format('d/m/Y H:i') }}</div>
    @if(request('customer'))<div>Customer: {{ request('customer') }}</div>@endif
    @if(request('search'))<div>Filter: {{ request('search') }}</div>@endif
</div>

<div class="card shadow-sm border-0 mb-3 opening-receivable-filter">
    <div class="card-body">
        <form method="GET" action="{{ route('piutang.index') }}" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label for="search" class="form-label">Cari saldo awal piutang</label>
                <input type="search" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nomor faktur, kode customer, nama customer, atau keterangan">
            </div>
            <div class="col-md-3">
                <label for="customerFilter" class="form-label">Customer</label>
                <div class="input-group">
                    <input type="text" id="customerFilter" name="customer" value="{{ request('customer') }}" class="form-control bg-white" placeholder="Semua customer" readonly>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#openingCustomerModal" title="Cari customer"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search"></i> Cari</button>
                <a href="{{ route('piutang.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="openingCustomerModal" tabindex="-1" aria-labelledby="openingCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="openingCustomerModalLabel"><i class="bi bi-search me-2"></i>Cari Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <input type="search" id="openingCustomerSearch" class="form-control mb-3" placeholder="Ketik kode atau nama customer..." autocomplete="off">
            <div id="openingCustomerLoading" class="text-center text-muted py-4 d-none"><span class="spinner-border spinner-border-sm me-2"></span>Memuat customer...</div>
            <div id="openingCustomerError" class="alert alert-danger d-none"></div>
            <div class="table-responsive" id="openingCustomerTable"><table class="table table-sm table-hover align-middle mb-0"><thead class="table-light sticky-top"><tr><th>Kode</th><th>Nama Customer</th><th></th></tr></thead><tbody id="openingCustomerResults"></tbody></table></div>
            <div id="openingCustomerEmpty" class="text-center text-muted py-4 d-none">Customer tidak ditemukan.</div>
        </div>
    </div></div>
</div>

<div class="row g-2 mb-3 opening-receivable-summary">
    <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Jumlah Faktur</div><div class="fw-bold summary-value">{{ number_format($totals->count, 0, ',', '.') }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Piutang Awal</div><div class="fw-bold summary-value">{{ number_format($totals->receivable, 2, ',', '.') }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Pembayaran</div><div class="fw-bold summary-value">{{ number_format($totals->paid, 2, ',', '.') }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Sisa</div><div class="fw-bold summary-value text-primary">{{ number_format($totals->balance, 2, ',', '.') }}</div></div></div></div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive opening-receivable-scroll">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="opening-date-col">Tanggal</th>
                        <th class="opening-invoice-col">No. Faktur</th>
                        <th>Pelanggan</th>
                        <th class="text-end">Piutang Awal</th>
                        <th class="text-end">Pembayaran</th>
                        <th class="text-end">Sisa</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($piutangs as $piutang)
                    @php($paymentDetails = $piutang->paymentDetails ?? collect())
                    <tr>
                        <td class="opening-date-cell">
                            <div>{{ $piutang->TGLFAKTUR->format('d/m/Y') }}</div>
                            <div class="opening-due-date"><span>JTP</span> {{ $piutang->TGLJT ? $piutang->TGLJT->format('d/m/Y') : '-' }}</div>
                        </td>
                        <td class="opening-invoice-cell">{{ $piutang->NOFAKTUR }}</td>
                        <td><strong>{{ $piutang->CUSTOMER }}</strong><div class="small text-muted">{{ $piutang->CUSTOMER_NAME ?: 'Nama customer belum tersedia' }}</div></td>
                        <td class="text-end opening-amount-cell">{{ number_format($piutang->PIUTANG, 0, ',', '.') }}</td>
                        <td class="text-end opening-amount-cell">{{ number_format($piutang->BAYAR, 0, ',', '.') }}</td>
                        <td class="text-end opening-amount-cell fw-semibold">{{ number_format($piutang->SALDO, 0, ',', '.') }}</td>
                        <td>
                            @if ($piutang->SALDO <= 0)
                                <button type="button" class="badge border-0 bg-success opening-payment-badge" data-bs-toggle="modal" data-bs-target="#paymentDetailModal{{ $piutang->id }}" title="Klik untuk lihat detail pembayaran">
                                    <i class="bi bi-check-circle me-1"></i>Lunas
                                </button>
                                <div class="small text-success mt-1">Terbayar</div>
                            @else
                                <button type="button" class="badge border-0 bg-warning text-dark opening-payment-badge" data-bs-toggle="modal" data-bs-target="#paymentDetailModal{{ $piutang->id }}" title="Klik untuk lihat detail pembayaran">
                                    <i class="bi bi-clock-history me-1"></i>Belum Lunas
                                </button>
                                @if($paymentDetails->isNotEmpty())
                                    <div class="small text-muted mt-1">{{ $paymentDetails->count() }} pembayaran</div>
                                @endif
                            @endif
                        </td>
                        <td class="opening-receivable-actions">
                            <div class="d-inline-flex flex-nowrap gap-1 align-items-center">
                            <a href="{{ route('piutang.show', $piutang) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            @if(str_starts_with((string)$piutang->SOURCE, 'SALDO_AWAL_') && (float)$piutang->BAYAR <= 0 && (float)$piutang->RETUR <= 0 && (float)$piutang->DISCOUNT <= 0)
                            <a href="{{ route('piutang.edit', $piutang) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form method="POST" action="{{ route('piutang.destroy', $piutang) }}" class="d-inline-flex" onsubmit="return confirm('Hapus saldo awal piutang {{ addslashes($piutang->NOFAKTUR) }}? Gunakan hanya jika invoice dari sumber sebenarnya sudah lunas atau tidak seharusnya menjadi saldo awal. Data yang dihapus tidak dapat dikembalikan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">Belum ada data piutang.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold"><td colspan="3">Total Seluruh Data ({{ number_format($totals->count, 0, ',', '.') }} faktur)</td><td class="text-end">{{ number_format($totals->receivable, 2, ',', '.') }}</td><td class="text-end">{{ number_format($totals->paid, 2, ',', '.') }}</td><td class="text-end">{{ number_format($totals->balance, 2, ',', '.') }}</td><td colspan="2"></td></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@foreach ($piutangs as $piutang)
@php($paymentDetails = $piutang->paymentDetails ?? collect())
<div class="modal fade" id="paymentDetailModal{{ $piutang->id }}" tabindex="-1" aria-labelledby="paymentDetailModalLabel{{ $piutang->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><h5 class="modal-title" id="paymentDetailModalLabel{{ $piutang->id }}">Detail Pembayaran {{ $piutang->NOFAKTUR }}</h5><div class="small text-muted">{{ $piutang->CUSTOMER }} - {{ $piutang->CUSTOMER_NAME ?: 'Nama customer belum tersedia' }}</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3"><div class="text-muted small">Piutang Awal</div><div class="fw-semibold">{{ number_format($piutang->PIUTANG, 0, ',', '.') }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Sudah Dibayar</div><div class="fw-semibold">{{ number_format($piutang->BAYAR, 0, ',', '.') }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Total Penyelesaian</div><div class="fw-semibold">{{ number_format($piutang->PAYMENT_SETTLED_TOTAL ?? 0, 0, ',', '.') }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Sisa</div><div class="fw-semibold {{ $piutang->SALDO <= 0 ? 'text-success' : 'text-primary' }}">{{ number_format($piutang->SALDO, 0, ',', '.') }}</div></div>
            </div>
            @if($paymentDetails->isNotEmpty())
            <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0"><thead class="table-light"><tr><th>No. Bayar</th><th>Tanggal</th><th>Metode</th><th>Akun Bayar</th><th>Referensi</th><th class="text-end">Bayar</th><th class="text-end">PPh 23</th><th class="text-end">Potongan</th><th class="text-end">Adm.</th><th class="text-end">Sisa Faktur</th><th></th></tr></thead><tbody>
                @foreach($paymentDetails as $detail)
                @php($header = $detail->header)
                <tr><td class="fw-semibold">{{ $detail->NOBAYAR }}</td><td>{{ $header?->TGLBAYAR?->format('d/m/Y') ?: '-' }}</td><td>{{ $header?->METODE_PEMBAYARAN ?: '-' }}</td><td>{{ $header?->paymentCoa ? $header->paymentCoa->kode_akun.' - '.$header->paymentCoa->nama_akun : '-' }}</td><td>{{ $header?->NO_REFERENSI ?: '-' }}</td><td class="text-end">{{ number_format($detail->NILAI_BAYAR, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->PPH23, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->DISCOUNT, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->BANK_ADMIN, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->SISA_FAKTUR, 0, ',', '.') }}</td><td class="text-end">@if($header)<a href="{{ route('pelunasan-piutang.show', $header->id) }}" class="btn btn-sm btn-outline-primary">Bukti</a>@endif</td></tr>
                @endforeach
            </tbody></table></div>
            @else
            <div class="alert alert-info mb-0">Belum ada detail pembayaran untuk faktur ini.</div>
            @endif
        </div>
    </div></div>
</div>
@endforeach

@if(!$printMode && $piutangs->hasPages())
<div class="opening-receivable-pagination mt-2">{{ $piutangs->links() }}</div>
@endif
</div>
@endsection

@push('styles')
<style>
    .opening-receivable-page { font-size: .95rem; }
    .opening-receivable-page .page-title { font-size: 1.2rem; }
    .opening-receivable-page > .d-flex p { font-size: .875rem; }
    .opening-receivable-page .card-body { padding: .75rem; }
    .opening-receivable-page .form-label { font-size: .84rem; margin-bottom: .25rem; }
    .opening-receivable-page .form-control,
    .opening-receivable-page .btn { font-size: .84rem; }
    .opening-receivable-page .table th,
    .opening-receivable-page .table td {
        padding: .32rem .4rem;
        font-size: .84rem;
        line-height: 1.25;
        vertical-align: middle;
    }
    .opening-receivable-page .table th { font-size: .8rem; white-space: nowrap; }
    .opening-receivable-page .table .small { font-size: .76rem; }
    .opening-receivable-page .badge { font-size: .74rem; }
    .opening-date-col,
    .opening-date-cell { width: 92px; min-width: 92px; white-space: nowrap; }
    .opening-invoice-col,
    .opening-invoice-cell { width: 112px; min-width: 112px; padding-left: .65rem !important; padding-right: .65rem !important; white-space: nowrap; }
    .opening-due-date { color: #dc3545; font-size: .75rem; margin-top: .18rem; line-height: 1.2; }
    .opening-due-date span { font-weight: 700; margin-right: .2rem; }
    .opening-amount-cell { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .opening-receivable-print-header { display: none; }
    .opening-receivable-summary .card-body { padding: .55rem .65rem; }
    .opening-receivable-summary .summary-value { font-size: .95rem; white-space: nowrap; }
    .opening-receivable-actions { white-space: nowrap; width: 1%; }
    .opening-receivable-actions .btn {
        padding: .18rem .35rem;
        font-size: .76rem;
        line-height: 1.2;
    }
    .opening-receivable-scroll {
        max-height: 58vh;
        min-height: 240px;
        overflow: auto;
        scrollbar-gutter: stable;
    }
    .opening-receivable-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: var(--bs-light);
        box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .12);
    }

    @media (max-width: 767.98px) {
        .opening-receivable-page > .d-flex:first-child { align-items: flex-start !important; gap: .5rem; }
        .opening-receivable-page > .d-flex:first-child .btn { white-space: nowrap; }
        .opening-receivable-page table { min-width: 900px; }
        .opening-receivable-scroll { max-height: 55vh; min-height: 220px; }
        .opening-receivable-toolbar { flex-wrap: wrap; justify-content: flex-end; }
        .opening-receivable-toolbar .btn { padding-left: .45rem; padding-right: .45rem; }
    }

    @media print {
        @page { size: A4 landscape; margin: 9mm; }
        .opening-receivable-header,
        .opening-receivable-filter,
        .opening-receivable-summary,
        .opening-receivable-pagination,
        .opening-receivable-actions { display: none !important; }
        .opening-receivable-print-header { display: block; text-align: center; margin-bottom: 7px; font-size: 8pt; line-height: 1.25; }
        .opening-receivable-print-header .fw-bold { font-size: 13pt; }
        .opening-receivable-page .card { box-shadow: none !important; border: 0 !important; }
        .opening-receivable-page .card-body,
        .opening-receivable-scroll { max-height: none !important; min-height: 0 !important; overflow: visible !important; padding: 0 !important; }
        .opening-receivable-page table { min-width: 0 !important; width: 100% !important; }
        .opening-receivable-scroll thead th { position: static; }
        .opening-receivable-page .table th,
        .opening-receivable-page .table td { padding: 2px 3px !important; font-size: 7pt !important; color: #000 !important; }
        .opening-receivable-page .table th:last-child,
        .opening-receivable-page .table td:last-child { display: none !important; }
        .opening-receivable-page .badge { padding: 0; color: #000 !important; background: transparent !important; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('openingCustomerModal');
    const input = document.getElementById('openingCustomerSearch');
    const target = document.getElementById('customerFilter');
    const results = document.getElementById('openingCustomerResults');
    const loading = document.getElementById('openingCustomerLoading');
    const error = document.getElementById('openingCustomerError');
    const empty = document.getElementById('openingCustomerEmpty');
    const table = document.getElementById('openingCustomerTable');
    if (!modal || !input || !target) return;
    let timer;
    const escapeHtml = value => { const el = document.createElement('div'); el.textContent = value || ''; return el.innerHTML; };
    function loadCustomers(query) {
        loading.classList.remove('d-none'); error.classList.add('d-none'); empty.classList.add('d-none'); table.classList.add('d-none');
        fetch("{{ route('piutang.search-pelanggan') }}?modal=1&q=" + encodeURIComponent(query || ''), { headers: { 'Accept': 'application/json' } })
            .then(response => { if (!response.ok) throw new Error('Data customer gagal dimuat.'); return response.json(); })
            .then(rows => {
                results.innerHTML = rows.map(row => `<tr><td class="fw-semibold">${escapeHtml(row.customer)}</td><td>${escapeHtml(row.nama || '-')}</td><td class="text-end"><button type="button" class="btn btn-sm btn-primary choose-opening-customer" data-code="${escapeHtml(row.customer)}">Pilih</button></td></tr>`).join('');
                empty.classList.toggle('d-none', rows.length > 0); table.classList.toggle('d-none', rows.length === 0);
            }).catch(err => { error.textContent = err.message; error.classList.remove('d-none'); })
            .finally(() => loading.classList.add('d-none'));
    }
    modal.addEventListener('shown.bs.modal', function () { input.value = ''; input.focus(); loadCustomers(''); });
    input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(() => loadCustomers(input.value.trim()), 250); });
    results.addEventListener('click', function (event) {
        const button = event.target.closest('.choose-opening-customer'); if (!button) return;
        target.value = button.dataset.code || ''; bootstrap.Modal.getInstance(modal)?.hide();
    });
});
</script>
@endpush

@if($printMode)
@push('scripts')
<script>
    window.addEventListener('load', function () { window.setTimeout(function () { window.print(); }, 100); });
</script>
@endpush
@endif
