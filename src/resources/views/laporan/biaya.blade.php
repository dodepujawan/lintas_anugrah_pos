@extends('layouts.app')

@section('title', 'Laporan Biaya')

@section('content')
@push('styles')
<style>
    .biaya-mobile-card,
    .biaya-detail-mobile-card,
    .biaya-mobile-row,
    .biaya-detail-mobile-row {
        display: none;
    }

    @media (max-width: 768px) {
        .screen-header {
            margin-bottom: .75rem !important;
        }

        .screen-header .col-12 {
            flex-direction: column;
            align-items: stretch !important;
            gap: .75rem;
        }

        .screen-header h2 {
            font-size: 1.15rem;
            margin-bottom: .15rem;
        }

        .screen-header .btn {
            width: 100%;
            min-height: 40px;
        }

        .biaya-filter-card {
            margin-bottom: .75rem !important;
        }

        .biaya-filter-card .card-body {
            padding: .75rem;
        }

        .biaya-filter-card label {
            font-size: .72rem;
            margin-bottom: .2rem;
        }

        .biaya-filter-card .form-control {
            min-height: 40px;
            font-size: .82rem;
        }

        .biaya-filter-card .form-check {
            margin-bottom: 0 !important;
        }

        .biaya-filter-card .btn {
            min-height: 40px;
            width: 100%;
        }

        .biaya-filter-card .input-group .btn {
            width: auto;
        }

        .biaya-summary-row {
            --bs-gutter-x: .45rem;
            --bs-gutter-y: .45rem;
            margin-bottom: .75rem !important;
        }

        .biaya-summary-row .card-body {
            padding: .7rem .65rem;
        }

        .biaya-summary-row .text-muted {
            font-size: .66rem;
            line-height: 1.15;
        }

        .biaya-summary-row .h4 {
            font-size: clamp(.86rem, 4vw, 1.05rem);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-variant-numeric: tabular-nums;
        }

        .biaya-table thead,
        .biaya-table tfoot,
        .biaya-table .biaya-desktop-row,
        .biaya-detail-table thead,
        .biaya-detail-table .biaya-detail-desktop-row {
            display: none;
        }

        .biaya-mobile-row,
        .biaya-detail-mobile-row {
            display: block;
        }

        .biaya-table,
        .biaya-table tbody,
        .biaya-table tr,
        .biaya-table td,
        .biaya-detail-table,
        .biaya-detail-table tbody,
        .biaya-detail-table tr,
        .biaya-detail-table td {
            display: block;
            width: 100%;
        }

        .biaya-table td,
        .biaya-detail-table td {
            border: 0 !important;
            padding: 0 !important;
        }

        .biaya-mobile-card,
        .biaya-detail-mobile-card {
            display: block;
            margin: .6rem;
            padding: .75rem;
            border: 1px solid #dbe3ec;
            border-radius: .5rem;
            background: #fff;
            box-shadow: 0 2px 7px rgba(15, 23, 42, .06);
        }

        .biaya-mobile-card.active {
            border-color: #f0ad4e;
            background: #fffaf0;
        }

        .biaya-card-top,
        .biaya-detail-card-top {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            align-items: flex-start;
        }

        .biaya-code,
        .biaya-date {
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
        }

        .biaya-name,
        .biaya-description {
            margin-top: .15rem;
            color: #334155;
            font-size: .82rem;
            overflow-wrap: anywhere;
        }

        .biaya-amount-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .4rem;
            margin-top: .65rem;
            padding-top: .6rem;
            border-top: 1px solid #eef2f7;
        }

        .biaya-amount-grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .biaya-label {
            display: block;
            color: #64748b;
            font-size: .58rem;
            font-weight: 700;
            line-height: 1;
            text-align: right;
            text-transform: uppercase;
        }

        .biaya-value {
            display: block;
            margin-top: .18rem;
            text-align: right;
            font-size: clamp(.72rem, 3.3vw, .86rem);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .biaya-mobile-card .btn {
            min-width: 38px;
            min-height: 34px;
            padding: .35rem .5rem;
        }

        .biaya-detail-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: .65rem;
        }

        .biaya-detail-header .btn {
            width: 100%;
        }
    }
</style>
@endpush

<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-bold text-uppercase">{{ config('app.company_name') }}</div>
            <h2 class="page-title"><i class="bi bi-receipt-cutoff me-2"></i>Laporan Biaya</h2>
            <small class="text-muted">Periode {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
            @if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI')
            <small class="ms-2 badge bg-danger">TERKUNCI</small>
            @endif
        </div>
        <div>
            <a href="{{ route('laporan.biaya.excel', request()->all()) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>
</div>

<div class="card mb-4 biaya-filter-card">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.biaya') }}" class="row g-3 align-items-end" id="biayaFilterForm">
            <div class="col-md-3">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
            </div>
            <div class="col-md-3">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Cari Akun Biaya</label>
                <div class="input-group">
                    <input type="search" class="form-control" id="search" name="search" value="{{ $search }}" placeholder="Kode atau nama akun">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#biayaCoaModal" title="Cari akun biaya">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="show_zero" name="show_zero" value="1" @checked(request()->boolean('show_zero'))>
                    <label class="form-check-label" for="show_zero">Tampilkan nol</label>
                </div>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            </div>
            @foreach($selectedCoaIds as $openCoaId)
            <input type="hidden" name="coa_ids[]" value="{{ $openCoaId }}">
            @endforeach
            <input type="hidden" name="focus" id="biayaFocusInput" value="{{ request('focus') }}">
        </form>
    </div>
</div>

<div class="modal fade" id="biayaCoaModal" tabindex="-1" aria-labelledby="biayaCoaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="biayaCoaModalLabel"><i class="bi bi-search me-2"></i>Cari Akun Biaya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="search" class="form-control mb-3" id="biayaCoaModalSearch" placeholder="Ketik kode atau nama akun biaya" autocomplete="off">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="biayaCoaModalRows">
                            @foreach($coas as $coa)
                            <tr data-search="{{ strtolower($coa->kode_akun.' '.$coa->nama_akun) }}">
                                <td class="fw-semibold text-nowrap">{{ $coa->kode_akun }}</td>
                                <td>{{ $coa->nama_akun }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-primary choose-biaya-coa" data-id="{{ $coa->id }}" data-label="{{ $coa->kode_akun }} - {{ $coa->nama_akun }}">
                                        Pilih Detail
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="biayaCoaModalEmpty" class="text-center text-muted py-4 d-none">Akun biaya tidak ditemukan.</div>
            </div>
        </div>
    </div>
</div>

@php
    $openAllUrl = route('laporan.biaya', request()->except(['page', 'coa_id', 'coa_ids']) + ['coa_ids' => $rows->pluck('coa.id')->all(), 'focus' => 'details']);
    $closeAllUrl = route('laporan.biaya', request()->except(['page', 'coa_id', 'coa_ids', 'focus']));
@endphp

<div class="row g-3 mb-4 biaya-summary-row">
    <div class="col-md-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="text-muted">Total Biaya/Beban</div>
                <div class="h4 mb-0">{{ number_format($totalBiaya, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">Pajak Penghasilan</div>
                <div class="h4 mb-0">{{ number_format($totalPajakPenghasilan, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">Total Semua Biaya</div>
                <div class="h4 mb-0">{{ number_format($totalSemuaBiaya, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">Akun Ada Transaksi</div>
                <div class="h4 mb-0">{{ number_format($rows->where('has_transactions', true)->count(), 0) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <strong>Ringkasan Akun Biaya</strong>
        <div class="btn-group btn-group-sm">
            <a href="{{ $openAllUrl }}#biaya-details" class="btn btn-outline-primary"><i class="bi bi-arrows-angle-expand"></i> Buka Detail All</a>
            <a href="{{ $closeAllUrl }}" class="btn btn-outline-secondary"><i class="bi bi-arrows-angle-contract"></i> Tutup Detail All</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 biaya-table">
                <thead class="table-primary">
                    <tr>
                        <th class="px-3">Kode Akun</th>
                        <th>Nama Biaya</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th class="text-end">Saldo Biaya</th>
                        <th class="text-end px-3">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    @php
                        $isOpen = $selectedCoaIds->contains($row['coa']->id);
                        $nextOpenIds = $isOpen
                            ? $selectedCoaIds->reject(fn ($id) => (int) $id === (int) $row['coa']->id)->values()->all()
                            : $selectedCoaIds->concat([$row['coa']->id])->unique()->values()->all();
                        $detailUrl = route('laporan.biaya', request()->except(['page', 'coa_id', 'coa_ids']) + ['coa_ids' => $nextOpenIds, 'focus' => 'details']);
                    @endphp
                    <tr class="biaya-desktop-row {{ $isOpen ? 'table-warning' : '' }}">
                        <td class="px-3 fw-semibold text-nowrap">{{ $row['coa']->kode_akun }}</td>
                        <td>{{ $row['coa']->nama_akun }}</td>
                        <td class="text-end">{{ number_format($row['debit'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['kredit'], 2) }}</td>
                        <td class="text-end fw-semibold">
                            <a href="{{ $detailUrl }}" class="text-decoration-none">{{ number_format($row['saldo'], 2) }}</a>
                        </td>
                        <td class="text-end px-3">
                            <a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi {{ $isOpen ? 'bi-x-lg' : 'bi-list-ul' }}"></i> {{ $isOpen ? 'Tutup' : 'Detail' }}
                            </a>
                        </td>
                    </tr>
                    <tr class="biaya-mobile-row">
                        <td colspan="6">
                            <div class="biaya-mobile-card {{ $isOpen ? 'active' : '' }}">
                                <div class="biaya-card-top">
                                    <div>
                                        <div class="biaya-code">{{ $row['coa']->kode_akun }}</div>
                                        <div class="biaya-name">{{ $row['coa']->nama_akun }}</div>
                                    </div>
                                    <a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi {{ $isOpen ? 'bi-x-lg' : 'bi-list-ul' }}"></i>
                                    </a>
                                </div>
                                <div class="biaya-amount-grid">
                                    <div>
                                        <span class="biaya-label">Debit</span>
                                        <span class="biaya-value">{{ number_format($row['debit'], 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="biaya-label">Kredit</span>
                                        <span class="biaya-value">{{ number_format($row['kredit'], 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="biaya-label">Saldo</span>
                                        <a href="{{ $detailUrl }}" class="biaya-value text-decoration-none">{{ number_format($row['saldo'], 0, ',', '.') }}</a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada transaksi biaya pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2" class="px-3">TOTAL BIAYA/BEBAN</th>
                        <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                        <th class="text-end">{{ number_format($totalKredit, 2) }}</th>
                        <th class="text-end">{{ number_format($totalBiaya, 2) }}</th>
                        <th></th>
                    </tr>
                    @if(($totalPajakPenghasilan ?? 0) != 0)
                    <tr>
                        <th colspan="4" class="px-3">Pajak Penghasilan</th>
                        <th class="text-end">{{ number_format($totalPajakPenghasilan, 2) }}</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="px-3">TOTAL SEMUA BIAYA</th>
                        <th class="text-end">{{ number_format($totalSemuaBiaya, 2) }}</th>
                        <th></th>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if($selectedCoas->isNotEmpty())
<div id="biaya-details" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Detail Biaya Terbuka</h5>
    <a href="{{ $closeAllUrl }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrows-angle-contract"></i> Tutup Detail All</a>
</div>
@foreach($selectedCoas as $selectedCoa)
@php
    $panelOpenIds = $selectedCoaIds->reject(fn ($id) => (int) $id === (int) $selectedCoa->id)->values()->all();
    $panelCloseUrl = route('laporan.biaya', request()->except(['page', 'coa_id', 'coa_ids']) + ['coa_ids' => $panelOpenIds, 'focus' => 'details']);
    $detailItems = $detailItemsByCoa->get($selectedCoa->id, collect());
@endphp
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center biaya-detail-header">
        <div>
            <strong>Detail {{ $selectedCoa->kode_akun }} - {{ $selectedCoa->nama_akun }}</strong>
            <div class="small text-muted">Klik Export Excel untuk membawa detail ini juga.</div>
        </div>
        <a href="{{ $panelCloseUrl }}#biaya-details" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-lg"></i> Tutup Detail
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 biaya-detail-table">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Tanggal</th>
                        <th>No. Jurnal</th>
                        <th>Deskripsi</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th class="text-end px-3">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @php $running = 0; @endphp
                    @forelse($detailItems as $item)
                    @php $running += (float) $item->debit - (float) $item->kredit; @endphp
                    <tr class="biaya-detail-desktop-row">
                        <td class="px-3 text-nowrap">{{ $item->jurnal->tanggal->format('d/m/Y') }}</td>
                        <td class="text-nowrap">{{ $item->jurnal->no_jurnal }}</td>
                        <td>{{ $item->deskripsi ?? $item->jurnal->keterangan ?? '-' }}</td>
                        <td class="text-end">{{ number_format($item->debit, 2) }}</td>
                        <td class="text-end">{{ number_format($item->kredit, 2) }}</td>
                        <td class="text-end px-3 fw-semibold">{{ number_format($running, 2) }}</td>
                    </tr>
                    <tr class="biaya-detail-mobile-row">
                        <td colspan="6">
                            <div class="biaya-detail-mobile-card">
                                <div class="biaya-detail-card-top">
                                    <div>
                                        <div class="biaya-date">{{ $item->jurnal->tanggal->format('d M Y') }}</div>
                                        <div class="biaya-name">{{ $item->jurnal->no_jurnal }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="biaya-label">Saldo</span>
                                        <span class="biaya-value">{{ number_format($running, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="biaya-description">{{ $item->deskripsi ?? $item->jurnal->keterangan ?? '-' }}</div>
                                <div class="biaya-amount-grid two">
                                    <div>
                                        <span class="biaya-label">Debit</span>
                                        <span class="biaya-value">{{ number_format($item->debit, 0, ',', '.') }}</span>
                                    </div>
                                    <div>
                                        <span class="biaya-label">Kredit</span>
                                        <span class="biaya-value">{{ number_format($item->kredit, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada detail transaksi untuk akun ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach
@endif

@if(request('focus') === 'details' && $selectedCoas->isNotEmpty())
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('biaya-details')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
</script>
@endpush
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('biayaCoaModal');
    const modalSearch = document.getElementById('biayaCoaModalSearch');
    const modalRows = document.getElementById('biayaCoaModalRows');
    const modalEmpty = document.getElementById('biayaCoaModalEmpty');
    const filterForm = document.getElementById('biayaFilterForm');
    const focusInput = document.getElementById('biayaFocusInput');

    if (!modal || !modalSearch || !modalRows || !filterForm) return;

    const filterRows = function () {
        const keyword = modalSearch.value.toLowerCase().trim();
        let shown = 0;

        modalRows.querySelectorAll('tr').forEach(function (row) {
            const visible = row.dataset.search.includes(keyword);
            row.classList.toggle('d-none', !visible);
            if (visible) shown++;
        });

        modalEmpty?.classList.toggle('d-none', shown > 0);
    };

    modal.addEventListener('shown.bs.modal', function () {
        modalSearch.value = '';
        filterRows();
        modalSearch.focus();
    });

    modalSearch.addEventListener('input', filterRows);

    modalRows.addEventListener('click', function (event) {
        const button = event.target.closest('.choose-biaya-coa');
        if (!button) return;

        const coaId = button.dataset.id;
        const alreadyOpen = Array.from(filterForm.querySelectorAll('input[name="coa_ids[]"]'))
            .some(function (input) { return input.value === coaId; });

        if (!alreadyOpen) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'coa_ids[]';
            input.value = coaId;
            filterForm.appendChild(input);
        }

        if (focusInput) {
            focusInput.value = 'details';
        }

        filterForm.submit();
    });
});
</script>
@endpush
@endsection
