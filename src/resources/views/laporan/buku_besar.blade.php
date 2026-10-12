@extends('layouts.app')

@section('title', 'Buku Besar')

@section('content')
@push('styles')
<style>
    .mobile-label {
        display: none;
    }

    .mobile-value {
        display: none;
    }

    .ledger-source-number {
        margin-top: .15rem;
        line-height: 1.2;
        font-size: .72rem;
    }

    .ledger-heading-action,
    .transaction-action {
        white-space: nowrap;
        width: 1%;
    }

    .transaction-action .btn {
        font-size: .72rem;
        padding: .2rem .45rem;
    }

    @media print {
        .ledger-heading-action,
        .transaction-action {
            display: none !important;
        }
    }

    @media (max-width: 768px) {
        .screen-header { margin-bottom: .75rem !important; }
        .screen-header .col-12 { flex-direction: column; align-items: stretch !important; gap: .65rem; }
        .screen-header h2 { font-size: 1.15rem; margin-bottom: 0; }
        .screen-header .col-12 > div:last-child { display: grid; grid-template-columns: 1fr 1fr; gap: .4rem; }
        .screen-header .col-12 > div:last-child .btn { width: 100%; margin: 0 !important; padding: .45rem .35rem; font-size: .72rem; white-space: normal; }
        .filter-card { margin-bottom: .75rem !important; }
        .filter-card .card-body { padding: .75rem; }
        .filter-card form { --bs-gutter-y: .55rem; }
        .filter-card form > div { width: 100%; }
        .filter-card label { font-size: .72rem; margin-bottom: .2rem; }
        .filter-card .form-control,.filter-card .form-select { min-height: 40px; font-size: .8rem; }
        .filter-card button { width: 100%; min-height: 40px; }
        .buku-besar-report .card-body>.d-flex {
            padding: 0.75rem 0.9rem !important;
        }

        .buku-besar-report .card-body>.d-flex p,
        .buku-besar-report .btn-group {
            display: none !important;
        }

        .buku-besar-report .table-responsive {
            overflow: visible;
        }

        .buku-besar-report .mobile-ledger-table,
        .buku-besar-report .mobile-ledger-table tbody {
            display: block;
            width: 100%;
        }

        .buku-besar-report .mobile-ledger-table thead {
            display: none;
        }

        .buku-besar-report .account-heading,
        .buku-besar-report .empty-row {
            display: block;
            width: 100%;
        }

        .buku-besar-report .account-heading>td,
        .buku-besar-report .empty-row>td {
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        .buku-besar-report .transaction-row {
            display: grid;
            grid-template-columns: 1fr;
            width: calc(100% - 1rem);
            margin: 0.65rem 0.5rem;
            overflow: hidden;
            border: 1px solid #dbe3ec;
            border-radius: 0.65rem;
            background: #fff;
            box-shadow: 0 2px 7px rgba(15, 23, 42, 0.06);
        }

        .buku-besar-report .transaction-row>td {
            display: block;
            min-width: 0;
            box-sizing: border-box;
            padding: 0.18rem 0.75rem !important;
            border: 0;
            font-size: 0.78rem;
        }

        .buku-besar-report .transaction-date {
            padding-top: 0.7rem !important;
            font-weight: 700;
            color: #0f172a;
        }

        .buku-besar-report .transaction-number {
            text-align: left;
            color: #64748b;
            overflow-wrap: anywhere;
        }

        .buku-besar-report .transaction-description {
            margin: 0.45rem 0 0.35rem;
            padding-top: 0.6rem !important;
            padding-bottom: 0.6rem !important;
            border-top: 1px solid #eef2f7;
            border-bottom: 1px solid #eef2f7;
            color: #334155;
            overflow-wrap: anywhere;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .buku-besar-report .transaction-amount {
            display: grid !important;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
            overflow: hidden;
            text-align: right !important;
            font-size: clamp(0.68rem, 3vw, 0.8rem) !important;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0;
        }

        .buku-besar-report .transaction-amount:last-child {
            padding-bottom: 0.7rem !important;
            font-weight: 700;
        }

        .buku-besar-report .mobile-label {
            display: block;
            margin-bottom: 0.12rem;
            color: #64748b;
            font-size: 0.58rem;
            font-weight: 700;
            line-height: 1;
            text-transform: uppercase;
        }

        .buku-besar-report .transaction-date .mobile-label,
        .buku-besar-report .transaction-number .mobile-label,
        .buku-besar-report .transaction-description .mobile-label,
        .buku-besar-report .desktop-value {
            display: none;
        }

        .buku-besar-report .mobile-value {
            display: inline;
        }

        .buku-besar-report .transaction-amount .mobile-value {
            min-width: 0;
            overflow: hidden;
            text-align: right;
        }

        .buku-besar-report .currency,
        .buku-besar-summary .currency {
            display: none;
        }

        .buku-besar-summary .card-body {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.35rem !important;
            padding: 0.75rem 0.5rem !important;
        }

        .buku-besar-summary .card-body>div {
            min-width: 0;
            text-align: right;
        }

        .buku-besar-summary .text-muted {
            font-size: 0.62rem;
        }

        .buku-besar-summary .h5 {
            overflow: hidden;
            white-space: nowrap;
            font-size: clamp(0.58rem, 2.5vw, 0.75rem);
            font-variant-numeric: tabular-nums;
        }
    }
</style>
@endpush
<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-bold text-uppercase">{{ config('app.company_name') }}</div>
            <h2 class="page-title"><i class="bi bi-journal-richtext me-2"></i>Buku Besar</h2>
            <small class="text-muted">Periode {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
            @if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI')
            <small class="ms-2 badge bg-danger">TERKUNCI</small>
            @endif
        </div>
        <div>
            @if(request('coa_id'))
            <a href="{{ route('laporan.buku_besar.excel', request()->all()) }}" class="btn btn-success me-2">
                <i class="bi bi-file-earmark-excel"></i> Export Excel (Akun Ini)
            </a>
            @endif
            <a href="{{ route('laporan.buku_besar.excel', ['coa_id' => '', 'tanggal_awal' => request('tanggal_awal', date('Y-m-01')), 'tanggal_akhir' => request('tanggal_akhir', date('Y-m-d'))]) }}" class="btn btn-info text-white me-2">
                <i class="bi bi-file-earmark-excel"></i> Export Semua Akun
            </a>
            @if($selectedCoa)
            @include('laporan._print-format-dropdown', [
                'pdfUrl' => route('laporan.buku_besar.pdf', request()->only(['coa_id', 'tanggal_awal', 'tanggal_akhir'])),
                'dotMatrixUrl' => route('laporan.buku_besar.pdf', request()->only(['coa_id', 'tanggal_awal', 'tanggal_akhir']) + ['print_format' => 'dot_matrix']),
            ])
            @endif
        </div>
    </div>
</div>

<div class="print-only-header">
    <div class="title-line fw-bold">{{ config('app.company_name') }}</div>
    <div class="title-line">
        <span class="page-title">LAPORAN BUKU BESAR</span>
    </div>
    <div class="period-line">
        <small class="text-muted">Periode: {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
    </div>
    @if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI')
    <div class="period-line"><small class="text-muted">Status: TERKUNCI</small></div>
    @endif
    @if($selectedCoa)
    <div class="period-line">
        <small class="text-muted">Akun: {{ $selectedCoa->kode_akun }} - {{ $selectedCoa->nama_akun }}</small>
    </div>
    @endif
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.buku_besar') }}" class="row g-3">
            <div class="col-md-3">
                <label for="ledgerCoaLabel" class="form-label">Akun <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input id="ledgerCoaLabel" class="form-control bg-white" value="{{ $selectedCoa ? $selectedCoa->kode_akun.' - '.$selectedCoa->nama_akun : '' }}" placeholder="Pilih akun COA" readonly required>
                    <input type="hidden" id="ledgerCoaId" name="coa_id" value="{{ request('coa_id') }}" required>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#ledgerCoaModal" title="Cari COA"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="col-md-3">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ request('tanggal_awal', date('Y-m-01')) }}">
            </div>
            <div class="col-md-3">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ request('tanggal_akhir', date('Y-m-d')) }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ledgerCoaModal" tabindex="-1" aria-labelledby="ledgerCoaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ledgerCoaModalLabel"><i class="bi bi-search me-2"></i>Cari COA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input id="ledgerCoaSearch" type="search" class="form-control mb-3" placeholder="Ketik kode atau nama akun" autocomplete="off">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top"><tr><th>Kode Akun</th><th>Nama Akun</th><th>Tipe</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody id="ledgerCoaRows">
                            @foreach($coas as $coa)
                            <tr data-search="{{ strtolower($coa->kode_akun.' '.$coa->nama_akun.' '.$coa->tipe) }}">
                                <td class="fw-semibold text-nowrap">{{ $coa->kode_akun }}</td>
                                <td>{{ $coa->nama_akun }}</td>
                                <td><span class="badge bg-secondary">{{ $coa->tipe }}</span></td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-primary choose-ledger-coa" data-id="{{ $coa->id }}" data-label="{{ $coa->kode_akun }} - {{ $coa->nama_akun }}">Pilih</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="ledgerCoaEmpty" class="text-center text-muted py-4 d-none">Akun tidak ditemukan.</div>
            </div>
        </div>
    </div>
</div>

@if($selectedCoa)
<div class="card buku-besar-report tree-mobile-expanded">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light">
            <div>
                <strong>Detail Buku Besar Periode</strong>
                <p class="mb-0 text-muted">Saldo awal + transaksi dari tanggal terkecil ke terbesar dalam rentang filter.</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary btn-sm" id="treeExpandAllButton">
                    <i class="bi bi-arrows-angle-expand"></i> Expand All
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="treeCollapseAllButton">
                    <i class="bi bi-arrows-angle-contract"></i> Collapse All
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mobile-ledger-table mb-0">
                <thead class="table-primary">
                    <tr>
                        <th class="px-3 ledger-heading-date">Tanggal</th>
                        <th class="ledger-heading-number">No. Jurnal</th>
                        <th class="ledger-heading-description">Deskripsi</th>
                        <th class="text-end px-3 ledger-heading-amount">Debit</th>
                        <th class="text-end px-3 ledger-heading-amount">Kredit</th>
                        <th class="text-end px-3 ledger-heading-amount">Saldo</th>
                        <th class="text-center ledger-heading-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="tree-row table-secondary account-heading" data-id="coa-{{ $selectedCoa->id }}" data-parent-id="">
                        <td colspan="7" class="px-3 fw-bold">{{ $selectedCoa->kode_akun }} - {{ $selectedCoa->nama_akun }}</td>
                    </tr>
                    @php $runningBalance = $openingBalance ?? 0; @endphp
                    <tr class="tree-row transaction-row table-light" data-parent-id="coa-{{ $selectedCoa->id }}">
                        <td class="px-3 transaction-date"><span class="mobile-label">Tanggal</span><span class="desktop-value">{{ date('d/m/Y', strtotime($tanggalAwal)) }}</span><span class="mobile-value">{{ date('d M Y', strtotime($tanggalAwal)) }}</span></td>
                        <td class="transaction-number"><span class="mobile-label">No. Jurnal</span>-</td>
                        <td class="transaction-description fw-semibold"><span class="mobile-label">Deskripsi</span>Saldo Awal per {{ date('d/m/Y', strtotime($tanggalAwal)) }}</td>
                        <td class="text-end px-3 transaction-amount"><span class="mobile-label">Debit</span><span class="desktop-value">-</span><span class="mobile-value">-</span></td>
                        <td class="text-end px-3 transaction-amount"><span class="mobile-label">Kredit</span><span class="desktop-value">-</span><span class="mobile-value">-</span></td>
                        <td class="text-end px-3 transaction-amount fw-semibold"><span class="mobile-label">Saldo</span><span class="desktop-value">{{ number_format($runningBalance, 2) }}</span><span class="mobile-value">{{ number_format($runningBalance, 0, ',', '.') }}</span></td>
                        <td class="text-center transaction-action"><span class="mobile-label">Aksi</span>-</td>
                    </tr>
                    @forelse($items as $item)
                    @php
                    if($selectedCoa->tipe == 'Aset' || $selectedCoa->tipe == 'Beban') {
                    $runningBalance += $item->debit - $item->kredit;
                    } else {
                    $runningBalance += $item->kredit - $item->debit;
                    }
                    $source = $sourceLinks[$item->jurnal->id] ?? null;
                    @endphp
                    <tr class="tree-row transaction-row" data-parent-id="coa-{{ $selectedCoa->id }}">
                        <td class="px-3 transaction-date"><span class="mobile-label">Tanggal</span><span class="desktop-value">{{ $item->jurnal->tanggal->format('d/m/Y') }}</span><span class="mobile-value">{{ $item->jurnal->tanggal->format('d M Y') }}</span></td>
                        <td class="transaction-number">
                            <span class="mobile-label">No. Jurnal</span>
                            <a href="{{ route('jurnal.show', $item->jurnal->id) }}" class="text-decoration-none" title="Lihat jurnal">{{ $item->jurnal->no_jurnal }}</a>
                            @if(!empty($source['source_number']) && $source['source_number'] !== $item->jurnal->no_jurnal)
                                <div class="small text-muted ledger-source-number">{{ $source['source_type'] }}: {{ $source['source_number'] }}</div>
                            @endif
                        </td>
                        <td class="transaction-description"><span class="mobile-label">Deskripsi</span>{{ $item->deskripsi ?? $item->jurnal->keterangan ?? '-' }}</td>
                        <td class="text-end px-3 transaction-amount"><span class="mobile-label">Debit</span><span class="desktop-value">{{ number_format($item->debit, 2) }}</span><span class="mobile-value">{{ $item->debit == 0 ? '-' : number_format($item->debit, 0, ',', '.') }}</span></td>
                        <td class="text-end px-3 transaction-amount"><span class="mobile-label">Kredit</span><span class="desktop-value">{{ number_format($item->kredit, 2) }}</span><span class="mobile-value">{{ $item->kredit == 0 ? '-' : number_format($item->kredit, 0, ',', '.') }}</span></td>
                        <td class="text-end px-3 transaction-amount"><span class="mobile-label">Saldo</span><span class="desktop-value">{{ number_format($runningBalance, 2) }}</span><span class="mobile-value">{{ number_format($runningBalance, 0, ',', '.') }}</span></td>
                        <td class="text-center transaction-action">
                            <span class="mobile-label">Aksi</span>
                            @if($source)
                                <a href="{{ $source['url'] }}" class="btn btn-sm btn-outline-primary text-nowrap" title="{{ $source['source_type'] }}{{ !empty($source['source_number']) ? ': '.$source['source_number'] : '' }}">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>{{ $source['label'] }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr class="tree-row empty-row" data-parent-id="coa-{{ $selectedCoa->id }}">
                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada transaksi untuk akun ini pada periode yang dipilih</td>
                    </tr>
                    @endforelse
                    <tr class="tree-row transaction-row table-light" data-parent-id="coa-{{ $selectedCoa->id }}">
                        <td class="px-3 transaction-date"><span class="mobile-label">Tanggal</span><span class="desktop-value">{{ date('d/m/Y', strtotime($tanggalAkhir)) }}</span><span class="mobile-value">{{ date('d M Y', strtotime($tanggalAkhir)) }}</span></td>
                        <td class="transaction-number"><span class="mobile-label">No. Jurnal</span>-</td>
                        <td class="transaction-description fw-semibold"><span class="mobile-label">Deskripsi</span>Saldo Akhir per {{ date('d/m/Y', strtotime($tanggalAkhir)) }}</td>
                        <td class="text-end px-3 transaction-amount"><span class="mobile-label">Debit</span>-</td>
                        <td class="text-end px-3 transaction-amount"><span class="mobile-label">Kredit</span>-</td>
                        <td class="text-end px-3 transaction-amount fw-semibold"><span class="mobile-label">Saldo</span><span class="desktop-value">{{ number_format($runningBalance, 2) }}</span><span class="mobile-value">{{ number_format($runningBalance, 0, ',', '.') }}</span></td>
                        <td class="text-center transaction-action"><span class="mobile-label">Aksi</span>-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($selectedCoa)
<div class="row mt-3 buku-besar-summary">
    <div class="col-md-12">
        <div class="card border-secondary shadow-sm">
            <div class="card-body py-3 d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <div class="text-muted">Saldo Awal ({{ date('d/m/Y', strtotime($tanggalAwal)) }})</div>
                    <div class="h5 mb-0">{{ number_format($openingBalance ?? 0, 2) }}</div>
                </div>
                <div>
                    <div class="text-muted">Total Debit Periode</div>
                    <div class="h5 mb-0">{{ number_format($totalDebit, 2) }}</div>
                </div>
                <div>
                    <div class="text-muted">Total Kredit Periode</div>
                    <div class="h5 mb-0">{{ number_format($totalKredit, 2) }}</div>
                </div>
                <div>
                    <div class="text-muted">Saldo Akhir ({{ date('d/m/Y', strtotime($tanggalAkhir)) }})</div>
                    <div class="h5 mb-0">{{ number_format($endingBalance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@include('laporan._report-print')
@include('laporan._tree-scripts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('ledgerCoaModal');
    const search = document.getElementById('ledgerCoaSearch');
    const rows = document.getElementById('ledgerCoaRows');
    const empty = document.getElementById('ledgerCoaEmpty');
    const target = document.getElementById('ledgerCoaId');
    const label = document.getElementById('ledgerCoaLabel');

    if (!modal || !search || !rows || !target || !label) return;

    const filterRows = function () {
        const query = search.value.toLowerCase().trim();
        let shown = 0;
        rows.querySelectorAll('tr').forEach(function (row) {
            const visible = row.dataset.search.includes(query);
            row.classList.toggle('d-none', !visible);
            if (visible) shown++;
        });
        empty?.classList.toggle('d-none', shown > 0);
    };

    modal.addEventListener('shown.bs.modal', function () {
        search.value = '';
        filterRows();
        search.focus();
    });

    search.addEventListener('input', filterRows);

    rows.addEventListener('click', function (event) {
        const button = event.target.closest('.choose-ledger-coa');
        if (!button) return;

        target.value = button.dataset.id || '';
        label.value = button.dataset.label || '';
        bootstrap.Modal.getInstance(modal)?.hide();
    });
});
</script>
@endpush

@push('styles')
<style media="print">
    @page { size: 8.5in 13in portrait; margin: 7mm; }
    .buku-besar-report .mobile-ledger-table { display: table !important; width: 100% !important; table-layout: fixed !important; }
    .buku-besar-report .mobile-ledger-table thead { display: table-header-group !important; }
    .buku-besar-report .mobile-ledger-table tbody { display: table-row-group !important; }
    .buku-besar-report .mobile-ledger-table tr { display: table-row !important; margin: 0 !important; padding: 0 !important; border: 0 !important; box-shadow: none !important; }
    .buku-besar-report .mobile-ledger-table th,
    .buku-besar-report .mobile-ledger-table td { display: table-cell !important; border: 1px solid #222 !important; padding: 3px 4px !important; font-size: 8pt !important; line-height: 1.2 !important; white-space: normal !important; overflow: visible !important; text-overflow: clip !important; }
    .buku-besar-report .mobile-ledger-table th:nth-child(1),.buku-besar-report .mobile-ledger-table td:nth-child(1){width:10%!important}
    .buku-besar-report .mobile-ledger-table th:nth-child(2),.buku-besar-report .mobile-ledger-table td:nth-child(2){width:15%!important}
    .buku-besar-report .mobile-ledger-table th:nth-child(3),.buku-besar-report .mobile-ledger-table td:nth-child(3){width:33%!important}
    .buku-besar-report .mobile-ledger-table th:nth-child(4),.buku-besar-report .mobile-ledger-table td:nth-child(4),
    .buku-besar-report .mobile-ledger-table th:nth-child(5),.buku-besar-report .mobile-ledger-table td:nth-child(5),
    .buku-besar-report .mobile-ledger-table th:nth-child(6),.buku-besar-report .mobile-ledger-table td:nth-child(6){width:14%!important;text-align:right!important;white-space:nowrap!important}
    .buku-besar-report .mobile-ledger-table th:nth-child(7),.buku-besar-report .mobile-ledger-table td:nth-child(7){display:none!important}
    .buku-besar-report .account-heading td { display: table-cell !important; font-weight: 700 !important; background: #e9ecef !important; }
    .buku-besar-report .mobile-label,.buku-besar-report .mobile-value { display: none !important; }
    .buku-besar-report .desktop-value { display: inline !important; }
    .buku-besar-report .transaction-description { -webkit-line-clamp: unset !important; display: table-cell !important; }
    .buku-besar-summary .card-body { display: flex !important; padding-top: 5px !important; border: 1px solid #222 !important; }
    .buku-besar-summary .h5 { font-size: 9pt !important; overflow: visible !important; }
    .print-only-header { margin-bottom: 5px !important; text-align: center !important; }
    .print-only-header .title-line { font-size: 11pt !important; }
</style>
@endpush
