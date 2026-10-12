@extends('layouts.app')

@section('title', 'Laporan Neraca')

@section('content')
@php
$monthNames = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember',
];
$isMonthYearPeriod = ($period_type ?? 'date_range') === 'month_year';
$periodeMonth = str_pad((string) ($month ?? date('m', strtotime($from_date))), 2, '0', STR_PAD_LEFT);
$periodeYear = (string) ($year ?? date('Y', strtotime($from_date)));
$periodeLabel = $isMonthYearPeriod
    ? strtoupper(($monthNames[$periodeMonth] ?? date('F', strtotime($from_date))) . ' ' . $periodeYear)
    : (date('j-m-Y', strtotime($from_date)) . ' s/d ' . date('j-m-Y', strtotime($to_date)));
@endphp

<div class="print-header d-none d-print-block">
    <h1>{{ config('app.company_name') }}</h1>
    <h2>NERACA</h2>
    <p>Periode: {{ $periodeLabel }}</p>
    @if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI')
    <p>Status: TERKUNCI</p>
    @endif
</div>

<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-bold text-uppercase">{{ config('app.company_name') }}</div>
            <h2 class="page-title"><i class="bi bi-file-earmark-text me-2"></i>Laporan Neraca</h2>
            <small class="text-muted">Periode: {{ $periodeLabel }}</small>
            @if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI')
            <small class="ms-2 badge bg-danger">TERKUNCI</small>
            @endif
        </div>
        <div>
            <a href="{{ route('laporan.neraca.excel', request()->except('page')) }}" class="btn btn-success me-2" id="neracaExportExcelButton" data-base-url="{{ route('laporan.neraca.excel') }}">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <a href="{{ route('laporan.neraca.pdf', request()->except('page')) }}" class="btn btn-danger me-2" id="neracaExportPdfButton" data-base-url="{{ route('laporan.neraca.pdf') }}">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            @include('laporan._print-format-dropdown', ['pdfUrl'=>route('laporan.neraca.pdf',request()->except('print_format')),'dotMatrixUrl'=>route('laporan.neraca.pdf',request()->except('print_format')+['print_format'=>'dot_matrix'])])
        </div>
    </div>
</div>

<style>
    .mobile-code {
        display: none;
    }

    .table td.amount,
    .table th.amount {
        text-align: right !important;
        white-space: nowrap !important;
        font-variant-numeric: tabular-nums;
    }

    .table .code-heading,
    .table .code-col {
        text-align: left !important;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .mobile-report-table {
            width: 100%;
            table-layout: fixed;
        }

        .mobile-report-table tbody {
            display: block;
            width: 100%;
        }

        .mobile-report-table .account-row,
        .mobile-report-table .subtotal-row,
        .mobile-report-table .total-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(108px, 36%);
            width: 100%;
        }

        .mobile-report-table .section-row {
            display: block;
            width: 100%;
        }

        .mobile-report-table .section-row>td {
            display: block;
            width: 100%;
        }

        .mobile-report-table td,
        .mobile-report-table th {
            box-sizing: border-box;
        }

        .report-controls {
            padding: 0.75rem 0.9rem !important;
            align-items: stretch !important;
            flex-direction: column;
            gap: 0.75rem;
        }

        .report-controls>div:first-child p {
            display: none !important;
        }

        .report-controls .btn-group {
            display: flex !important;
            width: 100%;
        }

        .report-controls .btn-group .btn {
            flex: 1 1 50%;
            white-space: nowrap;
        }

        .table.table-3col thead {
            display: block !important;
            width: 100%;
        }

        .table.table-3col thead tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(108px, 36%);
            width: 100%;
        }

        .table.table-3col thead .code-heading {
            display: none;
        }

        .table.table-3col thead .name-heading {
            min-width: 0;
            text-align: left !important;
        }

        .table.table-3col thead .amount-heading {
            min-width: 0;
            padding-left: 0.1rem !important;
            padding-right: 0.15rem !important;
            text-align: right !important;
            white-space: nowrap;
        }

        .table.table-3col> :not(caption)>*>* {
            padding: 0.4rem 0.35rem !important;
            font-size: 0.8rem !important;
        }

        .tree-row td {
            white-space: normal !important;
            word-break: break-word !important;
        }

        @if($show_code)
        .mobile-report-table .tree-row td:not([colspan]):first-child {
            display: none;
        }
        @endif

        .mobile-report-table .tree-row td:not([colspan]):nth-last-child(2) {
            width: auto;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .mobile-report-table .tree-row td.amount,
        .mobile-report-table .tree-row td:last-child:not([colspan]) {
            width: auto;
            max-width: none;
            min-width: 0;
            white-space: nowrap !important;
            word-break: normal !important;
            overflow: hidden;
            text-align: right !important;
            font-size: clamp(0.58rem, 2.45vw, 0.7rem) !important;
            letter-spacing: -0.02em;
            padding-left: 0.1rem !important;
            padding-right: 0.15rem !important;
            font-variant-numeric: tabular-nums;
        }

        .mobile-report-table .currency {
            display: none;
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

        .mobile-report-table .indent,
        .mobile-report-table [class*="ps-"] {
            padding-left: 0.5rem !important;
        }

        .tree-row td[colspan="3"] {
            font-size: 0.9rem !important;
        }

        .print-summary .card-body {
            padding: 0.9rem !important;
        }

        .print-summary h5 {
            font-size: 0.9rem !important;
        }

        .print-summary h3 {
            font-size: 1.05rem !important;
        }
    }

    @media print {
        .screen-header {
            display: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .print-header {
            display: block !important;
            text-align: center !important;
            margin: 0 0 4px 0 !important;
            padding: 0 !important;
            line-height: 1.1 !important;
        }

        .print-header h1,
        .print-header h2,
        .print-header p {
            margin: 0 0 2px 0 !important;
            padding: 0 !important;
            line-height: 1.1 !important;
        }

        .print-header h1 {
            font-size: 9pt !important;
        }

        .print-header h2 {
            font-size: 11pt !important;
            font-weight: 700 !important;
        }

        .print-header p {
            font-size: 9pt !important;
        }

        .filter-card,
        .report-controls,
        .page-title,
        .text-muted,
        .btn,
        .btn-group {
            display: none !important;
        }

        .main-wrapper,
        .page-content,
        .card,
        .card-body,
        .table-responsive,
        .print-card {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        .print-card,
        .print-card .card-body,
        .print-full {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .table,
        table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            margin-top: 0 !important;
        }

        .table th,
        .table td,
        table th,
        table td {
            border: 1px solid #000 !important;
            padding: 6px 8px !important;
        }

        .table thead th,
        table thead th {
            background: #f3f4f6 !important;
            font-weight: 700 !important;
            text-align: center !important;
        }

        .table thead th.code-heading,
        .table td.code-col {
            text-align: left !important;
        }

        .table th:nth-child(1),
        .table td:nth-child(1) {
            width: 15% !important;
        }

        .table th:nth-child(2),
        .table td:nth-child(2) {
            width: 55% !important;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 30% !important;
        }

        td.amount,
        th.amount,
        .text-end {
            text-align: right !important;
            white-space: nowrap !important;
        }

        .amount .currency,
        .amount .num,
        td.amount .currency,
        td.amount .num {
            white-space: nowrap !important;
        }

        tr.subtotal-row td {
            border-top: 1px solid #000 !important;
            font-weight: 700 !important;
        }

        tr.total-row td {
            border-top: 2px solid #000 !important;
            font-weight: 700 !important;
        }

        .indent {
            display: inline-block !important;
        }
    }
</style>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.neraca') }}" class="row g-3" id="neracaFilterForm">
            <div class="col-md-3">
                <label for="period_type" class="form-label">Periode</label>
                <select class="form-select" id="period_type" name="period_type">
                    <option value="date_range" {{ ($period_type ?? 'date_range') === 'date_range' ? 'selected' : '' }}>Rentang Tanggal</option>
                    <option value="month_year" {{ ($period_type ?? 'date_range') === 'month_year' ? 'selected' : '' }}>Bulan & Tahun</option>
                </select>
            </div>
            <div class="col-md-3 period-date-range {{ ($period_type ?? 'date_range') !== 'date_range' ? 'd-none' : '' }}">
                <label for="from_date" class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $from_date }}">
            </div>
            <div class="col-md-3 period-date-range {{ ($period_type ?? 'date_range') !== 'date_range' ? 'd-none' : '' }}">
                <label for="to_date" class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $to_date }}">
            </div>
            <div class="col-md-3 period-month-year {{ ($period_type ?? 'date_range') === 'month_year' ? '' : 'd-none' }}">
                <label for="month" class="form-label">Bulan</label>
                <select class="form-select" id="month" name="month">
                    @foreach(['01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember'] as $value => $label)
                    <option value="{{ $value }}" {{ str_pad((string) ($month ?? now()->format('m')), 2, '0', STR_PAD_LEFT) === str_pad((string) $value, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 period-month-year {{ ($period_type ?? 'date_range') === 'month_year' ? '' : 'd-none' }}">
                <label for="year" class="form-label">Tahun</label>
                <select class="form-select" id="year" name="year">
                    @php $currentYear = now()->format('Y'); @endphp
                    @foreach(range($currentYear - 5, $currentYear + 2) as $yearOption)
                    <option value="{{ $yearOption }}" {{ ($year ?? $currentYear) == $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="view_mode" class="form-label">Tampilkan</label>
                <select class="form-select" id="view_mode" name="view_mode">
                    <option value="all" {{ ($view_mode ?? $scope) === 'all' ? 'selected' : '' }}>Semua Akun</option>
                    <option value="header" {{ ($view_mode ?? $scope) === 'header' ? 'selected' : '' }}>Header Saja</option>
                    <option value="header_subheader_kelompok" {{ ($view_mode ?? $scope) === 'header_subheader_kelompok' ? 'selected' : '' }}>Header + Sub Header + Kelompok Akun</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label d-block">Filter Tambahan</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="only_balance" name="only_balance" value="1" {{ !empty($only_balance) ? 'checked' : '' }}>
                    <label class="form-check-label" for="only_balance">Hanya yang Bersaldo</label>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="only_transactions" name="only_transactions" value="1" {{ !empty($only_transactions) ? 'checked' : '' }}>
                    <label class="form-check-label" for="only_transactions">Hanya yang Ada Transaksi</label>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-none mb-0 print-card">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light report-controls">
            <div>
                <strong>Struktur Tree Neraca</strong>
                <p class="mb-0 text-muted">AKTIVA dan PASIVA dengan subgroup, subtotal otomatis. Akun kontra (akumulasi penyusutan, prive) mengurangi total sisi masing-masing.</p>
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
        <div class="table-responsive print-full">
            <table class="table table-bordered table-sm table-3col mobile-report-table mb-0">
                <thead class="table-light">
                    <tr>
                        @if($show_code)
                        <th class="px-3 code-heading text-start" style="width:8%">Kode</th>
                        @endif
                        <th class="px-3 name-heading">
                            <span class="d-none d-md-inline">Nama Akun</span>
                            <span class="d-md-none">Kode/Nama Akun</span>
                        </th>
                        <th class="text-end px-3 amount amount-heading">
                            <span class="d-none d-md-inline">Saldo</span>
                            <span class="d-md-none">Saldo</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $displayAmount = function ($value) {
                    if ($value < 0) {
                        $num=number_format(abs($value), 2, ',' , '.' );
                        return '<span class="currency">(</span>&nbsp;<span class="num">' . $num . '</span>' ;
                        }

                        $num=number_format($value, 2, ',' , '.' );
                        return '<span class="num">' . $num . '</span>' ;
                        };
                        @endphp

                        <tr class="tree-row table-primary section-row" data-id="section-aktiva" data-parent-id="">
                        <td colspan="3" class="px-3">
                            <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="section-aktiva" aria-label="Toggle Aktiva">
                                <i class="bi bi-chevron-down tree-toggle-icon"></i>
                            </button>
                            <strong>AKTIVA</strong>
                        </td>
                        </tr>
                        @foreach($asset_tree as $node)
                        @include('laporan._coa_tree_rows', ['nodes' => [$node], 'level' => 0, 'parentId' => 'section-aktiva', 'show_code' => $show_code, 'mobile_code' => true])
                        @endforeach
                        <tr class="tree-row subtotal-row table-secondary" data-parent-id="section-aktiva">
                            <td colspan="{{ $show_code ? 2 : 1 }}" class="px-3 fw-bold">TOTAL AKTIVA</td>
                            <td class="text-end amount px-3 fw-bold">{!! $displayAmount($total_asset) !!}</td>
                        </tr>

                        <tr class="tree-row table-primary section-row" data-id="section-pasiva" data-parent-id="">
                            <td colspan="3" class="px-3">
                                <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="section-pasiva" aria-label="Toggle Pasiva">
                                    <i class="bi bi-chevron-down tree-toggle-icon"></i>
                                </button>
                                <strong>PASIVA</strong>
                            </td>
                        </tr>
                        <tr class="tree-row table-secondary section-row" data-id="section-kewajiban" data-parent-id="section-pasiva">
                            <td colspan="3" class="px-3">
                                <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="section-kewajiban" aria-label="Toggle Kewajiban">
                                    <i class="bi bi-chevron-down tree-toggle-icon"></i>
                                </button>
                                <strong>KEWAJIBAN</strong>
                            </td>
                        </tr>
                        @foreach($liability_tree as $node)
                        @include('laporan._coa_tree_rows', ['nodes' => [$node], 'level' => 0, 'parentId' => 'section-kewajiban', 'show_code' => $show_code, 'mobile_code' => true])
                        @endforeach
                        <tr class="tree-row subtotal-row table-secondary" data-parent-id="section-kewajiban">
                            <td colspan="{{ $show_code ? 2 : 1 }}" class="px-3 fw-bold">Total Kewajiban</td>
                            <td class="text-end amount px-3 fw-bold">{!! $displayAmount($total_kewajiban) !!}</td>
                        </tr>
                        <tr class="tree-row table-secondary section-row" data-id="section-ekuitas" data-parent-id="section-pasiva">
                            <td colspan="3" class="px-3">
                                <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="section-ekuitas" aria-label="Toggle Ekuitas">
                                    <i class="bi bi-chevron-down tree-toggle-icon"></i>
                                </button>
                                <strong>EKUITAS</strong>
                            </td>
                        </tr>
                        @foreach($equity_tree as $node)
                        @include('laporan._coa_tree_rows', ['nodes' => [$node], 'level' => 0, 'parentId' => 'section-ekuitas', 'show_code' => $show_code, 'mobile_code' => true])
                        @endforeach
                        @if($laba_berjalan != 0)
                        <tr class="tree-row account-row" data-parent-id="section-ekuitas">
                            @if($show_code)
                            <td class="px-3"></td>
                            @endif
                            <td class="px-3 fw-bold text-warning">Laba Berjalan</td>
                            <td class="text-end px-3 fw-bold text-warning">{!! $displayAmount($laba_berjalan) !!}</td>
                        </tr>
                        @endif
                        <tr class="tree-row subtotal-row table-secondary" data-parent-id="section-ekuitas">
                            <td colspan="{{ $show_code ? 2 : 1 }}" class="px-3 fw-bold">Total Ekuitas</td>
                            <td class="text-end amount px-3 fw-bold">{!! $displayAmount($total_ekuitas) !!}</td>
                        </tr>
                        <tr class="tree-row total-row table-info" data-parent-id="section-pasiva">
                            <td colspan="{{ $show_code ? 2 : 1 }}" class="px-3 fw-bold">TOTAL PASIVA</td>
                            <td class="text-end amount px-3 fw-bold">{!! $displayAmount($total_pasiva) !!}</td>
                        </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12 print-summary">
        <div class="card border-{{ $is_balance ? 'success' : 'danger' }} shadow-sm">
            <div class="card-body text-center py-3">
                <div class="row">
                    <div class="col-md-4">
                        <h5 class="mb-0 text-primary">TOTAL AKTIVA</h5>
                        <h3 class="fw-bold text-primary">{!! $displayAmount($total_asset) !!}</h3>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-center">
                        @if($is_balance)
                        <div>
                            <i class="bi bi-check-circle-fill text-success display-6"></i>
                            <h5 class="text-success mt-2 mb-0">✅ BALANCE</h5>
                            <small class="text-muted">Aktiva=Pasiva</small>
                        </div>
                        @else
                        <div>
                            <i class="bi bi-x-circle-fill text-danger display-6"></i>
                            <h5 class="text-danger mt-2 mb-0">⚠️ TIDAK BALANCE</h5>
                            <small class="text-muted">Selisih: {!! $displayAmount($selisih) !!}</small>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h5 class="mb-0 text-danger">TOTAL PASIVA</h5>
                        <h3 class="fw-bold text-danger">{!! $displayAmount($total_pasiva) !!}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@include('laporan._report-print')
@include('laporan._tree-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const periodType = document.getElementById('period_type');
        const dateRangeGroups = document.querySelectorAll('.period-date-range');
        const monthYearGroups = document.querySelectorAll('.period-month-year');
        const filterForm = document.getElementById('neracaFilterForm');
        const exportButtons = [
            document.getElementById('neracaExportExcelButton'),
            document.getElementById('neracaExportPdfButton'),
        ].filter(Boolean);

        function togglePeriodFields() {
            const showMonthYear = periodType && periodType.value === 'month_year';
            dateRangeGroups.forEach(function(group) {
                group.classList.toggle('d-none', showMonthYear);
                group.querySelectorAll('input, select').forEach(function(field) {
                    field.disabled = showMonthYear;
                });
            });
            monthYearGroups.forEach(function(group) {
                group.classList.toggle('d-none', !showMonthYear);
                group.querySelectorAll('input, select').forEach(function(field) {
                    field.disabled = !showMonthYear;
                });
            });
        }

        function updateExportLinks() {
            if (!filterForm) {
                return;
            }

            const params = new URLSearchParams(new FormData(filterForm));
            exportButtons.forEach(function(button) {
                const baseUrl = button.dataset.baseUrl;
                button.href = params.toString() ? baseUrl + '?' + params.toString() : baseUrl;
            });
        }

        if (periodType) {
            periodType.addEventListener('change', function() {
                togglePeriodFields();
                updateExportLinks();
            });
            togglePeriodFields();
        }

        if (filterForm) {
            filterForm.addEventListener('change', updateExportLinks);
            filterForm.addEventListener('input', updateExportLinks);
            updateExportLinks();
        }
    });
</script>
