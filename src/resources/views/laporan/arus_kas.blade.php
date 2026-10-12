@extends('layouts.app')

@section('title', 'Laporan Arus Kas')

@section('content')
<style>
    @media (max-width: 768px) {
        .arus-kas-header {
            margin-bottom: 0.75rem !important;
        }

        .arus-kas-header>.col-12 {
            align-items: stretch !important;
            flex-direction: column;
            gap: 0.6rem;
        }

        .arus-kas-header .page-title {
            margin-bottom: 0.2rem;
            font-size: 1.15rem;
        }

        .arus-kas-header p.text-muted {
            display: none;
        }

        .arus-kas-header .header-actions {
            display: flex;
            width: 100%;
            gap: 0.4rem;
        }

        .arus-kas-header .header-actions .btn {
            flex: 1 1 50%;
            margin-right: 0 !important;
            padding: 0.42rem 0.5rem;
            font-size: 0.7rem;
            white-space: nowrap;
        }

        .arus-kas-report .report-controls {
            padding: 0.65rem 0.8rem !important;
        }

        .arus-kas-report .report-controls strong {
            font-size: 0.82rem;
        }

        .arus-kas-report .report-controls p,
        .arus-kas-report .report-controls .btn-group {
            display: none !important;
        }

        .arus-kas-report .arus-kas-table {
            margin-bottom: 0;
            font-size: 0.68rem;
        }

        .arus-kas-report .arus-kas-table th,
        .arus-kas-report .arus-kas-table td {
            padding: 0.4rem 0.3rem !important;
        }

        .arus-kas-report .arus-kas-table .amount {
            white-space: nowrap;
            text-align: right !important;
            font-size: clamp(0.54rem, 2.2vw, 0.66rem);
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.03em;
        }

        .arus-kas-report .arus-kas-table .currency {
            display: none;
        }
    }

    @media print {

        .arus-kas-table th:nth-child(1),
        .arus-kas-table td:nth-child(1) {
            width: 16% !important;
        }

        .arus-kas-table th:nth-child(2),
        .arus-kas-table td:nth-child(2) {
            width: 36% !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }

        .arus-kas-table th:nth-child(3),
        .arus-kas-table td:nth-child(3),
        .arus-kas-table th:nth-child(4),
        .arus-kas-table td:nth-child(4) {
            width: 18% !important;
        }

        .arus-kas-table th:nth-child(5),
        .arus-kas-table td:nth-child(5) {
            width: 12% !important;
        }

        .arus-kas-table .currency {
            display: none !important;
        }

        .arus-kas-table td.amount,
        .arus-kas-table td.text-end,
        .arus-kas-table th.text-end {
            text-align: right !important;
            white-space: nowrap !important;
        }

        .arus-kas-table .table-secondary td {
            font-weight: 700 !important;
        }
    }
</style>
<div class="row mb-4 screen-header arus-kas-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="page-title"><i class="bi bi-cash-stack me-2"></i>Laporan Arus Kas</h2>
            <p class="text-muted mb-1">Ringkas penerimaan dan pengeluaran kas bank dari akun posting.</p>
            <small class="text-muted">Periode {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
            <small class="ms-2 badge bg-{{ ($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI' ? 'danger' : 'success' }}">{{ $periodLockStatus ?? 'TERBUKA' }}</small>
        </div>
        <div class="header-actions">
            <a href="{{ route('laporan.arus_kas.excel', request()->all()) }}" class="btn btn-success me-2">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            @include('laporan._print-format-dropdown', ['pdfUrl'=>route('laporan.stream_pdf',['report'=>'arus-kas']+request()->except('print_format')),'dotMatrixUrl'=>route('laporan.stream_pdf',['report'=>'arus-kas','print_format'=>'dot_matrix']+request()->except('print_format'))])
        </div>
    </div>
</div>

<div class="print-only-header">
    <div class="title-line">
        <span class="page-title">LAPORAN ARUS KAS</span>
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
        <form method="GET" action="{{ route('laporan.arus_kas') }}" class="row g-3">
            <div class="col-md-4">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
            </div>
            <div class="col-md-4">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card arus-kas-report tree-mobile-expanded">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light report-controls">
            <div>
                <strong>Struktur Tree Arus Kas</strong>
                <p class="mb-0 text-muted">Akun kas dan bank dengan subtotal penerimaan/pengeluaran.</p>
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
            <table class="table table-bordered mb-0 arus-kas-table">
                <thead class="table-light">
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th class="text-end">Penerimaan</th>
                        <th class="text-end">Pengeluaran</th>
                        <th class="text-end">Nett</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="tree-row table-primary" data-id="section-arus-kas" data-parent-id="">
                        <td colspan="5" class="px-3">
                            <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="section-arus-kas" aria-label="Toggle Arus Kas">
                                <i class="bi bi-chevron-down tree-toggle-icon"></i>
                            </button>
                            <strong>AKUN KAS / BANK</strong>
                        </td>
                    </tr>
                    @forelse($rows as $row)
                    <tr class="tree-row" data-parent-id="section-arus-kas">
                        <td>{{ $row['coa']->kode_akun }}</td>
                        <td>{{ $row['coa']->nama_akun }}</td>
                        <td class="text-end amount">{{ number_format($row['inflow'], 2) }}</td>
                        <td class="text-end amount">{{ number_format($row['outflow'], 2) }}</td>
                        <td class="text-end amount">{{ number_format($row['net'], 2) }}</td>
                    </tr>
                    @empty
                    <tr class="tree-row" data-parent-id="section-arus-kas">
                        <td colspan="5" class="text-center text-muted">Tidak ada data arus kas untuk periode ini.</td>
                    </tr>
                    @endforelse
                    <tr class="tree-row table-secondary" data-parent-id="section-arus-kas">
                        <td colspan="2" class="text-end fw-bold px-3">Total</td>
                        <td class="text-end fw-bold px-3 amount">{{ number_format($totalInflow, 2) }}</td>
                        <td class="text-end fw-bold px-3 amount">{{ number_format($totalOutflow, 2) }}</td>
                        <td class="text-end fw-bold px-3 amount">{{ number_format($totalInflow - $totalOutflow, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@include('laporan._report-print')
@include('laporan._tree-scripts')
