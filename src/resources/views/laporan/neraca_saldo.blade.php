@extends('layouts.app')

@section('title', 'Neraca Saldo')

@section('content')
@push('styles')
<style>
    .mobile-code {
        display: none;
    }

    @media (max-width: 768px) {
        .screen-header .page-title {
            font-size: 1.1rem;
        }

        .screen-header p.text-muted {
            display: none;
        }

        .filter-card {
            margin-bottom: 0.75rem;
        }

        .filter-card .card-body {
            padding: 0.75rem !important;
        }

        .filter-card .form-label {
            font-size: 0.85rem;
            margin-bottom: 0.35rem;
        }

        .filter-card .form-control,
        .filter-card .form-select {
            font-size: 0.85rem;
            padding: 0.5rem 0.65rem;
        }

        .filter-card .btn {
            width: 100%;
        }

        .card-body.p-0 {
            padding: 0 !important;
        }

        .card-body .d-flex.justify-content-between {
            flex-wrap: wrap;
            gap: 0.65rem;
            padding: 0.75rem 0.9rem !important;
        }

        .card-body .btn-group {
            display: none !important;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            font-size: 0.82rem;
            margin-bottom: 0;
        }

        .mobile-report-table {
            width: 100%;
            table-layout: fixed;
        }

        .mobile-report-table tbody,
        .mobile-report-table tfoot {
            display: block;
            width: 100%;
        }

        .mobile-report-table .account-row,
        .mobile-report-table .summary-row,
        .mobile-report-table tfoot tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(68px, 22%) minmax(68px, 22%);
            width: 100%;
        }

        .mobile-report-table .tree-row.table-primary {
            display: block;
            width: 100%;
        }

        .mobile-report-table .tree-row.table-primary>td {
            display: block;
            width: 100%;
        }

        .mobile-report-table td,
        .mobile-report-table th {
            box-sizing: border-box;
        }

        .mobile-report-table thead {
            display: block;
            width: 100%;
        }

        .mobile-report-table thead tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(68px, 22%) minmax(68px, 22%);
            width: 100%;
        }

        .mobile-report-table thead .code-heading,
        .mobile-report-table thead .type-heading {
            display: none;
        }

        .mobile-report-table thead .name-heading {
            min-width: 0;
            text-align: left;
        }

        .mobile-report-table thead .amount-heading {
            min-width: 0;
            padding-left: 0.05rem !important;
            padding-right: 0.18rem !important;
            text-align: right !important;
            font-size: 0.68rem;
            white-space: nowrap;
        }

        .table td,
        .table th {
            padding: 0.45rem 0.5rem !important;
        }

        .tree-row td {
            white-space: normal !important;
            word-break: break-word !important;
        }

        .tree-row.table-primary td,
        .tree-row.table-secondary td {
            padding-left: 0.5rem !important;
        }

        .tree-row.table-primary td strong {
            font-size: 0.95rem;
        }

        .text-end.amount {
            white-space: nowrap !important;
        }

        .mobile-report-table .account-row .code-col,
        .mobile-report-table .type-col {
            display: none;
        }

        .mobile-report-table .account-row .name-col {
            width: auto;
            min-width: 0;
            padding-left: 0.35rem !important;
            padding-right: 0.2rem !important;
            overflow-wrap: break-word;
        }

        .mobile-report-table .mobile-code {
            display: block;
            margin-bottom: 0.1rem;
            color: #64748b;
            font-size: 0.68rem;
            font-weight: 600;
            line-height: 1.1;
            white-space: nowrap;
        }

        .mobile-report-table .tree-row td.amount,
        .mobile-report-table tfoot th.amount {
            width: auto;
            max-width: none;
            min-width: 0;
            padding-left: 0.05rem !important;
            padding-right: 0.18rem !important;
            font-size: clamp(0.52rem, 2.15vw, 0.66rem);
            word-break: normal !important;
            white-space: nowrap !important;
            overflow: hidden;
            text-align: right !important;
            letter-spacing: -0.03em;
        }

        .mobile-report-table td.text-end,
        .mobile-report-table th.text-end {
            text-align: right !important;
        }

        .mobile-report-table .currency {
            display: none;
        }

        .alert {
            font-size: 0.9rem;
            padding: 0.65rem 0.75rem;
        }
    }

    @media print {

        html,
        body {
            font-size: 12pt !important;
        }

        .table th,
        .table td {
            font-size: 12pt !important;
        }

        .print-only-header .page-title {
            font-size: 14pt !important;
        }
    }
</style>
@endpush
<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-bold text-uppercase">{{ config('app.company_name') }}</div>
            <h2 class="page-title"><i class="bi bi-columns-gap me-2"></i>Neraca Saldo</h2>
            <small class="text-muted d-block">{{ $periodeLabel }}</small>
            @if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI')
            <small class="badge bg-danger">TERKUNCI</small>
            @endif
        </div>
        <div>
            <a href="{{ route('laporan.neraca_saldo.excel', request()->all()) }}" class="btn btn-success me-2" id="neracaSaldoExportExcelButton" data-base-url="{{ route('laporan.neraca_saldo.excel') }}">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            @include('laporan._print-format-dropdown', ['pdfUrl'=>route('laporan.stream_pdf',['report'=>'neraca-saldo']+request()->except('print_format')),'dotMatrixUrl'=>route('laporan.stream_pdf',['report'=>'neraca-saldo','print_format'=>'dot_matrix']+request()->except('print_format'))])
        </div>
    </div>
</div>

<div class="print-only-header">
    <div class="title-line fw-bold">{{ config('app.company_name') }}</div>
    <div class="title-line">
        <span class="page-title">LAPORAN NERACA SALDO</span>
    </div>
    <div class="period-line">
        <small class="text-muted">{{ $periodeLabel }}</small>
    </div>
    @if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI')
    <div class="period-line"><small class="text-muted">Status: TERKUNCI</small></div>
    @endif
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.neraca_saldo') }}" class="row g-3" id="neracaSaldoFilterForm">
            <div class="col-md-3">
                <label for="mode_periode" class="form-label">Posisi Saldo</label>
                <select class="form-select" id="mode_periode" name="mode_periode">
                    <option value="bulanan" {{ ($modePeriode ?? 'bulanan') === 'bulanan' ? 'selected' : '' }}>Akhir Bulan</option>
                    <option value="range" {{ ($modePeriode ?? 'bulanan') === 'range' ? 'selected' : '' }}>Tanggal Tertentu</option>
                </select>
            </div>

            <div class="col-md-3 period-month-year {{ ($modePeriode ?? 'bulanan') === 'bulanan' ? '' : 'd-none' }}">
                <label for="month" class="form-label">Bulan</label>
                <select class="form-select" id="month" name="month">
                    @foreach(['01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember'] as $value => $label)
                    <option value="{{ $value }}" {{ str_pad((string) ($month ?? now()->format('m')), 2, '0', STR_PAD_LEFT) === str_pad((string) $value, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 period-month-year {{ ($modePeriode ?? 'bulanan') === 'bulanan' ? '' : 'd-none' }}">
                <label for="year" class="form-label">Tahun</label>
                <select class="form-select" id="year" name="year">
                    @php $currentYear = now()->format('Y'); @endphp
                    @foreach(range($currentYear - 5, $currentYear + 2) as $yearOption)
                    <option value="{{ $yearOption }}" {{ ($year ?? $currentYear) == $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 period-range {{ ($modePeriode ?? 'bulanan') === 'range' ? '' : 'd-none' }}">
                <label for="tanggal_akhir" class="form-label">Tanggal Posisi</label>
                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light">
            <div>
                <strong>Struktur Tree Neraca Saldo</strong>
                <p class="mb-0 text-muted">Saldo akhir kumulatif setiap akun sampai tanggal posisi.</p>
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
            <table class="table table-bordered mobile-report-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="code-heading">Kode Akun</th>
                        <th class="name-heading">
                            <span class="d-none d-md-inline">Nama Akun</span>
                            <span class="d-md-none">Kode/Nama Akun</span>
                        </th>
                        <th class="type-heading">Tipe</th>
                        <th class="text-end amount-heading">Saldo Debit</th>
                        <th class="text-end amount-heading">Saldo Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @php $groupedRows = $rows->groupBy(fn($row) => $row['coa']->tipe); @endphp
                    @foreach($groupedRows as $tipe => $group)
                    <tr class="tree-row table-primary" data-id="group-{{ strtolower(str_replace(' ', '-', $tipe)) }}" data-parent-id="">
                        <td colspan="5" class="px-3">
                            <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="group-{{ strtolower(str_replace(' ', '-', $tipe)) }}" aria-label="Toggle {{ $tipe }}">
                                <i class="bi bi-chevron-down tree-toggle-icon"></i>
                            </button>
                            <strong>{{ $tipe }}</strong>
                        </td>
                    </tr>
                    @foreach($group as $row)
                    @php
                    $indentClass = match ($row['coa']->level) {
                    'Header' => 'ps-0',
                    'Sub Header' => 'ps-4',
                    'Kelompok Akun' => 'ps-5',
                    'Akun Posting' => 'ps-6',
                    default => 'ps-0',
                    };
                    @endphp
                    <tr class="tree-row account-row" data-parent-id="group-{{ strtolower(str_replace(' ', '-', $tipe)) }}">
                        <td class="code-col">{{ $row['coa']->kode_akun }}</td>
                        <td class="name-col {{ $indentClass }}">
                            <span class="mobile-code">{{ $row['coa']->kode_akun }}</span>
                            {{ $row['coa']->nama_akun }}
                        </td>
                        <td class="type-col">{{ $row['coa']->tipe }}</td>
                        <td class="text-end amount">{{ number_format($row['debit'], 2) }}</td>
                        <td class="text-end amount">{{ number_format($row['kredit'], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="tree-row table-secondary summary-row" data-parent-id="group-{{ strtolower(str_replace(' ', '-', $tipe)) }}">
                        <td colspan="2" class="px-3 fw-bold">Total</td>
                        <td class="type-col"></td>
                        <td class="text-end fw-bold amount">{{ number_format($group->sum('debit'), 2) }}</td>
                        <td class="text-end fw-bold amount">{{ number_format($group->sum('kredit'), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <th colspan="2" class="text-end">Total</th>
                        <th class="type-col"></th>
                        <th class="text-end amount">{{ number_format($totalDebit, 2) }}</th>
                        <th class="text-end amount">{{ number_format($totalKredit, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <div class="alert {{ $isBalanced ? 'alert-success' : 'alert-warning' }}">
        {{ $isBalanced ? 'Neraca Saldo seimbang.' : 'Neraca Saldo tidak seimbang.' }}
    </div>
</div>
@endsection

@include('laporan._report-print')
@include('laporan._tree-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modePeriode = document.getElementById('mode_periode');
        const monthYearGroups = document.querySelectorAll('.period-month-year');
        const rangeGroups = document.querySelectorAll('.period-range');
        const filterForm = document.getElementById('neracaSaldoFilterForm');
        const exportButton = document.getElementById('neracaSaldoExportExcelButton');

        function togglePeriodeFields() {
            const isMonthly = modePeriode && modePeriode.value === 'bulanan';
            monthYearGroups.forEach(function(group) {
                group.classList.toggle('d-none', !isMonthly);
                group.querySelectorAll('input, select').forEach(function(field) {
                    field.disabled = !isMonthly;
                });
            });
            rangeGroups.forEach(function(group) {
                group.classList.toggle('d-none', isMonthly);
                group.querySelectorAll('input, select').forEach(function(field) {
                    field.disabled = isMonthly;
                });
            });
        }

        function updateExportLink() {
            if (!filterForm || !exportButton) {
                return;
            }

            const params = new URLSearchParams(new FormData(filterForm));
            const baseUrl = exportButton.dataset.baseUrl;
            exportButton.href = params.toString() ? baseUrl + '?' + params.toString() : baseUrl;
        }

        if (modePeriode) {
            modePeriode.addEventListener('change', function() {
                togglePeriodeFields();
                updateExportLink();
            });
            togglePeriodeFields();
        }

        if (filterForm) {
            filterForm.addEventListener('change', updateExportLink);
            filterForm.addEventListener('input', updateExportLink);
            updateExportLink();
        }
    });
</script>
