@extends('layouts.app')

@section('title', 'Jurnal Umum')

@section('content')
@push('styles')
<style>
    .journal-mobile-code {
        display: none;
    }

    .journal-report .mobile-journal-table {
        table-layout: fixed;
    }

    .journal-report .date-col,
    .journal-report .date-heading,
    .journal-report .journal-item .empty-date {
        width: 7%;
    }

    .journal-report .number-col,
    .journal-report .number-heading,
    .journal-report .journal-item .code-column {
        width: 10%;
    }

    .journal-report .description-col,
    .journal-report .description-heading,
    .journal-report .journal-item td:nth-child(3) {
        width: 61%;
    }

    .journal-report .amount-col,
    .journal-report .amount-heading,
    .journal-report .journal-item .amount-column {
        width: 11%;
    }

    .journal-report .description-heading,
    .journal-report .journal-item td:nth-child(3) {
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .journal-report .journal-heading-main {
        min-width: 0;
        flex: 1 1 auto;
    }

    .journal-report .journal-heading-text {
        min-width: 0;
        width: 100%;
    }

    .journal-report .journal-heading-text small {
        display: block;
        max-width: 100%;
        font-size: 11px !important;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .journal-report .journal-heading-badges {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .journal-page-header {
            margin-bottom: 0.75rem !important;
        }

        .journal-page-header .page-title {
            margin-bottom: 0.2rem;
            font-size: 1.15rem;
        }

        .journal-page-header .col-12 {
            align-items: stretch !important;
            flex-direction: column;
            gap: 0.6rem;
        }

        .journal-page-header .header-actions {
            display: flex;
            width: 100%;
            gap: 0.4rem;
        }

        .journal-page-header .header-actions .btn {
            flex: 1 1 50%;
            margin-right: 0 !important;
            padding: 0.45rem 0.55rem;
            font-size: 0.72rem;
            white-space: nowrap;
        }

        .journal-report .report-controls {
            padding: 0.7rem 0.85rem !important;
        }

        .journal-report .report-controls strong {
            font-size: 0.85rem;
        }

        .journal-report .report-controls p,
        .journal-report .report-controls .btn-group {
            display: none !important;
        }

        .journal-report .table-responsive {
            overflow: visible;
        }

        .journal-report .mobile-journal-table,
        .journal-report .mobile-journal-table tbody {
            display: block;
            width: 100%;
            table-layout: auto;
        }

        .journal-report .mobile-journal-table thead {
            display: block;
            width: 100%;
        }

        .journal-report .mobile-journal-table thead tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(74px, 24%) minmax(74px, 24%);
            width: 100%;
        }

        .journal-report .mobile-journal-table .date-heading,
        .journal-report .mobile-journal-table .number-heading {
            display: none;
        }

        .journal-report .mobile-journal-table .description-heading,
        .journal-report .mobile-journal-table .amount-heading {
            min-width: 0;
            box-sizing: border-box;
            padding: 0.4rem 0.3rem !important;
            font-size: clamp(0.58rem, 2.35vw, 0.7rem);
            white-space: nowrap;
        }

        .journal-report .mobile-journal-table .description-heading {
            text-align: left;
        }

        .journal-report .mobile-journal-table .amount-heading {
            text-align: right !important;
        }

        .journal-report .journal-heading {
            display: block;
            width: 100%;
        }

        .journal-report .journal-heading>td {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 0.55rem 0.6rem !important;
        }

        .journal-report .journal-heading .d-flex.gap-3 {
            gap: 0.45rem !important;
        }

        .journal-report .journal-heading .fw-bold {
            font-size: 0.75rem;
        }

        .journal-report .journal-heading small,
        .journal-report .journal-heading .badge {
            font-size: 0.58rem;
        }

        .journal-report .journal-item,
        .journal-report .journal-total {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(74px, 24%) minmax(74px, 24%);
            width: 100%;
        }

        .journal-report .journal-item>td,
        .journal-report .journal-total>td {
            min-width: 0;
            box-sizing: border-box;
            padding: 0.4rem 0.3rem !important;
            font-size: clamp(0.56rem, 2.35vw, 0.7rem);
        }

        .journal-report .journal-item .empty-date,
        .journal-report .journal-item .code-column,
        .journal-report .journal-total .total-spacer {
            display: none;
        }

        .journal-report .journal-mobile-code {
            display: block;
            margin-bottom: 0.1rem;
            color: #64748b;
            font-size: 0.6rem;
            font-weight: 700;
        }

        .journal-report .amount-column {
            overflow: hidden;
            white-space: nowrap;
            text-align: right !important;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.03em;
        }

        .journal-report .currency,
        .journal-summary .currency {
            display: none;
        }

        .journal-summary .col-md-6 {
            width: 100%;
        }

        .journal-summary .card-body {
            gap: 0.5rem;
            padding: 0.7rem !important;
        }

        .journal-summary .text-muted {
            font-size: 0.65rem;
        }

        .journal-summary .h5 {
            margin: 0;
            font-size: clamp(0.62rem, 2.8vw, 0.78rem);
            font-variant-numeric: tabular-nums;
        }
    }
</style>
@endpush
<div class="row mb-4 screen-header journal-page-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="page-title"><i class="bi bi-journal-text me-2"></i>Jurnal Umum</h2>
            <small class="text-muted">Periode {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
            <small class="ms-2 badge bg-{{ ($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI' ? 'danger' : 'success' }}">{{ $periodLockStatus ?? 'TERBUKA' }}</small>
        </div>
        <div class="header-actions">
            <a href="{{ route('laporan.jurnal_umum.excel', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'jenis' => request('jenis'), 'search' => request('search')]) }}" class="btn btn-success me-2">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            @include('laporan._print-format-dropdown', ['pdfUrl'=>route('laporan.stream_pdf',['report'=>'jurnal-umum']+request()->except('print_format')),'dotMatrixUrl'=>route('laporan.stream_pdf',['report'=>'jurnal-umum','print_format'=>'dot_matrix']+request()->except('print_format'))])
        </div>
    </div>
</div>

<div class="print-only-header">
    <div class="title-line">
        <span class="page-title">LAPORAN JURNAL UMUM</span>
    </div>
    <div class="period-line">
        <small class="text-muted">Periode: {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
    </div>
    <div class="period-line">
        <small class="text-muted">Status: {{ $periodLockStatus ?? 'TERBUKA' }}</small>
    </div>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.jurnal_umum') }}" class="row g-3">
            <div class="col-md-2">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
            </div>
            <div class="col-md-2">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
            </div>
            <div class="col-md-3">
                <label for="jenis" class="form-label">Jenis Jurnal</label>
                <select name="jenis" id="jenis" class="form-select">
                    <option value="">Semua Jenis</option>
                    @foreach(($jenisList ?? []) as $j)
                    <option value="{{ $j }}" {{ (isset($jenis) && $jenis === $j) ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Pencarian</label>
                <input type="search" class="form-control" id="search" name="search" value="{{ $search ?? request('search') }}" placeholder="No jurnal, referensi, akun, atau keterangan">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i> Tampilkan</button>
                <a href="{{ route('laporan.jurnal_umum') }}" class="btn btn-outline-secondary" title="Reset filter"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card journal-report tree-mobile-expanded">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light report-controls">
            <div>
                <strong>
                    <span class="d-none d-md-inline">Struktur Tree Jurnal Umum</span>
                    <span class="d-md-none">Rincian Jurnal</span>
                </strong>
                <p class="mb-0 text-muted">Tanggal > Nomor Jurnal > Akun Debit/Kredit.</p>
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
            <table class="table table-bordered mobile-journal-table mb-0">
                <colgroup>
                    <col class="date-col">
                    <col class="number-col">
                    <col class="description-col">
                    <col class="amount-col">
                    <col class="amount-col">
                </colgroup>
                <thead class="table-light">
                    <tr>
                        <th class="px-3 date-heading">Tanggal</th>
                        <th class="number-heading">No. Jurnal</th>
                        <th class="description-heading">
                            <span class="d-none d-md-inline">Deskripsi</span>
                            <span class="d-md-none">Akun/Deskripsi</span>
                        </th>
                        <th class="text-end px-3 amount-heading">Debit</th>
                        <th class="text-end px-3 amount-heading">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurnals as $jurnal)
                    <tr class="tree-row table-primary journal-heading" data-id="jurnal-{{ $jurnal->id }}" data-parent-id="">
                        <td colspan="5" class="px-3">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div class="d-flex align-items-center gap-3 journal-heading-main">
                                    <button type="button" class="btn btn-sm btn-link tree-toggle p-0" data-id="jurnal-{{ $jurnal->id }}" aria-label="Toggle jurnal">
                                        <i class="bi bi-chevron-down tree-toggle-icon"></i>
                                    </button>
                                    <div class="journal-heading-text">
                                        <div class="fw-bold text-primary">{{ $jurnal->tanggal->format('d/m/Y') }} - {{ $jurnal->no_jurnal }}</div>
                                        <small class="text-muted">{{ $jurnal->keterangan ?? '-' }}</small>
                                    </div>
                                </div>
                                <div class="journal-heading-badges">
                                    <span class="badge bg-{{ $jurnal->is_posted ? 'success' : 'warning text-dark' }}">{{ $jurnal->is_posted ? 'Posted' : 'Draft' }}</span>
                                    <span class="badge bg-secondary ms-2">{{ $jurnal->tipe }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @foreach($jurnal->items as $item)
                    <tr class="tree-row journal-item" data-parent-id="jurnal-{{ $jurnal->id }}">
                        <td class="px-3 empty-date">&nbsp;</td>
                        <td class="code-column">{{ $item->coa->kode_akun }}</td>
                        <td><span class="journal-mobile-code">{{ $item->coa->kode_akun }}</span>{{ $item->coa?->nama_akun ?? '' }} - {{ $item->deskripsi ?? '-' }}</td>
                        <td class="text-end px-3 amount-column">{{ number_format($item->debit, 2) }}</td>
                        <td class="text-end px-3 amount-column">{{ number_format($item->kredit, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="tree-row table-secondary journal-total" data-parent-id="jurnal-{{ $jurnal->id }}">
                        <td colspan="2" class="px-3 fw-bold">Total</td>
                        <td class="text-end px-3 fw-bold total-spacer">&nbsp;</td>
                        <td class="text-end px-3 fw-bold amount-column">{{ number_format($jurnal->total_debit, 2) }}</td>
                        <td class="text-end px-3 fw-bold amount-column">{{ number_format($jurnal->total_kredit, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-6"></i>
                            <div class="mt-3">Tidak ada jurnal pada periode yang dipilih.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(method_exists($jurnals, 'hasPages') && $jurnals->hasPages())
<div class="mt-3">
    {{ $jurnals->links() }}
</div>
@endif

<div class="row mt-3 journal-summary">
    <div class="col-md-6">
        <div class="card border-secondary shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Total Debit</div>
                    <div class="h5 mb-0">{{ number_format($totalDebit, 2) }}</div>
                </div>
                <div>
                    <div class="text-muted">Total Kredit</div>
                    <div class="h5 mb-0">{{ number_format($totalKredit, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@include('laporan._report-print')
@include('laporan._tree-scripts')
