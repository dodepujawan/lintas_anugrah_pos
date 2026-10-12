@extends('layouts.app')
@section('title', 'Laporan Penjualan')

@push('styles')
<style>
.report-screen-header{margin-bottom:.6rem!important}
.report-filter{margin-bottom:.65rem!important}
.report-filter .card-body{padding:.55rem .7rem!important}
.report-summary-compact{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:.4rem;margin-bottom:.65rem}
.report-summary-compact .item{background:#fff;border:1px solid #e5e7eb;border-radius:.45rem;padding:.4rem .55rem;box-shadow:0 1px 2px rgba(15,23,42,.04);min-width:0}
.report-summary-compact .label{display:block;color:#64748b;font-size:.68rem;font-weight:650;line-height:1.1}
.report-summary-compact .value{display:block;margin-top:.12rem;font-size:.86rem;font-weight:700;line-height:1.15;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
.sales-report-table{max-height:calc(100vh - 330px);overflow:auto;-webkit-overflow-scrolling:touch}
.sales-report-table table{table-layout:fixed;width:100%;min-width:980px;border-collapse:separate;border-spacing:0}
.sales-report-table thead th{position:sticky;top:0;z-index:2;background:#fff;white-space:nowrap}
.sales-report-table tfoot th{position:sticky;bottom:0;z-index:2;background:#f8fafc;box-shadow:0 -1px 0 #dee2e6}
.sales-report-table th,.sales-report-table td{font-size:.875rem;line-height:1.25;padding:.4rem .45rem;vertical-align:middle}
.sales-report-table .col-date{width:86px;white-space:nowrap}
.sales-report-table .col-faktur{width:140px;white-space:normal;overflow-wrap:anywhere;word-break:break-word}
.sales-report-table .col-customer{width:150px;white-space:normal;overflow-wrap:anywhere}
.sales-report-table .col-jenis{width:78px;white-space:nowrap}
.sales-report-table .col-amount{width:130px}
.sales-report-table .col-status{width:72px;white-space:nowrap}
.sales-report-table .col-journal{width:56px;white-space:nowrap;text-align:center}
.sales-report-table .amount-col{text-align:right!important;white-space:nowrap;font-variant-numeric:tabular-nums}
.sales-report-table .total-label{text-align:right;white-space:nowrap}
.sales-journal-btn{min-width:30px;padding:.15rem .35rem}
.sales-mobile-totals{display:none}
.sales-scroll-hint{display:none}
.print-heading{display:none}
@media(max-width:1199.98px){
    .report-summary-compact{grid-template-columns:repeat(4,minmax(0,1fr))}
}
@media(max-width:767.98px){
    .report-actions .btn{flex:1}
    .report-summary-compact{
        display:flex;
        flex-wrap:nowrap;
        gap:.45rem;
        margin:0 -.15rem .65rem;
        padding:.1rem .15rem .35rem;
        overflow-x:auto;
        overflow-y:hidden;
        -webkit-overflow-scrolling:touch;
        scroll-snap-type:x proximity;
        overscroll-behavior-x:contain;
    }
    .report-summary-compact .item{
        flex:0 0 auto;
        min-width:148px;
        max-width:none;
        scroll-snap-align:start;
    }
    .report-summary-compact .value{
        font-size:.82rem;
        white-space:nowrap;
        overflow:visible;
        overflow-wrap:normal;
    }
    .sales-report-table{max-height:none;overflow-x:auto;overflow-y:visible;-webkit-overflow-scrolling:touch}
    .sales-report-table table{min-width:1000px}
    .sales-report-table .col-faktur{width:120px}
    .sales-report-table .col-customer{width:120px}
    .sales-report-table .col-amount{width:120px}
    .sales-report-table .amount-col,.sales-report-table tfoot .amount-col{white-space:nowrap}
    .sales-mobile-totals{
        display:flex;
        flex-wrap:nowrap;
        gap:.45rem;
        margin-top:.65rem;
        padding-bottom:.25rem;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        scroll-snap-type:x proximity;
    }
    .sales-mobile-totals .item{
        flex:0 0 auto;
        min-width:160px;
        background:#f8fafc;
        border:1px solid #e5e7eb;
        border-radius:.45rem;
        padding:.45rem .55rem;
        scroll-snap-align:start;
    }
    .sales-mobile-totals .label{display:block;color:#64748b;font-size:.68rem;font-weight:650}
    .sales-mobile-totals .value{
        display:block;
        margin-top:.1rem;
        font-size:.84rem;
        font-weight:700;
        white-space:nowrap;
        font-variant-numeric:tabular-nums;
    }
    .sales-scroll-hint{
        display:block;
        margin:-.15rem 0 .55rem;
        color:#64748b;
        font-size:.72rem;
        font-weight:600;
    }
}
@page{size:A4 portrait;margin:10mm 8mm}
@media print{
    .sidebar,.topbar,.report-screen-header,.report-filter,.report-actions,.pagination,.sidebar-overlay,.report-summary-compact,.sales-mobile-totals,.sales-scroll-hint{display:none!important}
    .main-wrapper{margin:0!important;width:100%!important}
    .page-content{padding:0!important}
    .print-heading{display:block!important;text-align:center;margin:0 0 5mm;border-bottom:1.5px solid #222;padding-bottom:3mm}
    .print-heading h2{font-size:15px!important;margin:0!important}
    .print-heading .print-meta{font-size:10px;line-height:1.4}
    .sales-report-table{max-height:none;overflow:visible}
    .sales-report-table thead th,.sales-report-table tfoot th{position:static!important}
    .card{border:0!important;box-shadow:none!important}
    .table{font-size:9px!important;width:100%!important;min-width:0!important;border-collapse:collapse!important}
    .table th,.table td{padding:3px 4px!important;border:1px solid #333!important}
    body{background:#fff!important;color:#000!important}
}
</style>
@endpush

@section('content')
@php
    $jenisLabel = $jenis !== '' ? ($jenisOptions[$jenis] ?? $jenis) : 'Semua';
    $periodLabel = \Carbon\Carbon::parse($date_from)->format('d/m/Y').' s.d. '.\Carbon\Carbon::parse($date_to)->format('d/m/Y');
@endphp

<div class="print-heading">
    <h2>LAPORAN PENJUALAN</h2>
    <div class="print-meta">Periode {{ $periodLabel }}</div>
    <div class="print-meta">Customer: {{ $customer !== '' ? $customer : 'Semua' }} | Jenis: {{ $jenisLabel }}</div>
    <div class="print-meta">
        Data: {{ number_format($summary['count'], 0, ',', '.') }} |
        Nilai: {{ number_format($summary['piutang'], 0, ',', '.') }} |
        Terbayar: {{ number_format($summary['bayar'], 0, ',', '.') }} |
        Saldo: {{ number_format($summary['saldo'], 0, ',', '.') }}
    </div>
</div>

<div class="report-screen-header d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center mb-3">
    <div class="small text-muted">Diambil dari penambahan data piutang (hasil penjualan)</div>
    <div class="report-actions d-flex gap-2 flex-wrap">
        <a href="{{ route('laporan.penjualan.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Excel
        </a>
        @include('laporan._print-format-dropdown', [
            'pdfUrl' => route('laporan.penjualan.pdf', request()->except(['page', 'print_format'])),
            'dotMatrixUrl' => route('laporan.penjualan.pdf', request()->except(['page', 'print_format']) + ['print_format' => 'dot_matrix']),
            'buttonClass' => 'btn-outline-secondary',
        ])
    </div>
</div>

<div class="card border-0 shadow-sm report-filter mb-3">
    <div class="card-body p-2 p-md-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $date_from }}" class="form-control form-control-sm" required>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Sampai</label>
                <input type="date" name="date_to" value="{{ $date_to }}" class="form-control form-control-sm" required>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Customer</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="reportCustomerFilter" name="customer" class="form-control" value="{{ $customer }}" placeholder="Semua pelanggan" readonly>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportCustomerModal" title="Cari customer">
                        <i class="bi bi-search"></i>
                    </button>
                    @if($customer !== '')
                        <button type="button" id="clearReportCustomer" class="btn btn-outline-secondary" title="Hapus pelanggan"><i class="bi bi-x-lg"></i></button>
                    @endif
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Jenis</label>
                <select name="jenis" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="EKS" @selected($jenis === 'EKS')>EKS - Ekspedisi</option>
                    <option value="REN" @selected($jenis === 'REN')>REN - Rental</option>
                    <option value="CO" @selected($jenis === 'CO')>CO - Cold Room</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Cari</label>
                <input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="No faktur / customer / keterangan">
            </div>
            <div class="col-12 col-md-12 d-flex gap-2">
                <button class="btn btn-primary btn-sm">Tampilkan</button>
                <a href="{{ route('laporan.penjualan') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="reportCustomerModal" tabindex="-1" aria-labelledby="reportCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportCustomerModalLabel"><i class="bi bi-search me-2"></i>Cari Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="reportCustomerSearch" class="form-control mb-3" placeholder="Ketik kode atau nama customer..." autocomplete="off">
                <div id="reportCustomerLoading" class="text-center text-muted py-3 d-none">Memuat customer...</div>
                <div id="reportCustomerEmpty" class="text-center text-muted py-3 d-none">Customer tidak ditemukan.</div>
                <div id="reportCustomerError" class="alert alert-danger py-2 d-none">Data customer gagal dimuat.</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Kode</th><th>Nama Customer</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody id="reportCustomerResults"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sales-scroll-hint"><i class="bi bi-arrow-left-right me-1"></i>Geser kanan/kiri untuk melihat semua total & kolom angka</div>

<div class="report-summary-compact">
    <div class="item">
        <span class="label">Data</span>
        <span class="value text-primary">{{ number_format($summary['count'], 0, ',', '.') }}</span>
    </div>
    <div class="item">
        <span class="label">Penjualan</span>
        <span class="value text-primary">{{ number_format($summary['piutang'], 0, ',', '.') }}</span>
    </div>
    <div class="item">
        <span class="label">Terbayar</span>
        <span class="value text-success">{{ number_format($summary['bayar'], 0, ',', '.') }}</span>
    </div>
    <div class="item">
        <span class="label">Saldo</span>
        <span class="value text-warning">{{ number_format($summary['saldo'], 0, ',', '.') }}</span>
    </div>
    <div class="item">
        <span class="label">Ekspedisi</span>
        <span class="value text-primary">{{ number_format($jenisTotals['EKS'], 0, ',', '.') }}</span>
    </div>
    <div class="item">
        <span class="label">Rental</span>
        <span class="value text-success">{{ number_format($jenisTotals['REN'], 0, ',', '.') }}</span>
    </div>
    <div class="item">
        <span class="label">Cold Room</span>
        <span class="value text-info">{{ number_format($jenisTotals['CO'], 0, ',', '.') }}</span>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive sales-report-table">
        <table class="table table-sm table-hover align-middle mb-0">
            <colgroup>
                <col class="col-date">
                <col class="col-faktur">
                <col class="col-customer">
                <col class="col-jenis">
                <col class="col-amount">
                <col class="col-amount">
                <col class="col-amount">
                <col class="col-status">
                <col class="col-journal">
            </colgroup>
            <thead>
                <tr>
                    <th class="col-date">Tanggal</th>
                    <th class="col-faktur">No. Faktur</th>
                    <th class="col-customer">Customer</th>
                    <th class="col-jenis">Jenis</th>
                    <th class="col-amount amount-col">Nilai</th>
                    <th class="col-amount amount-col">Terbayar</th>
                    <th class="col-amount amount-col">Saldo</th>
                    <th class="col-status">Status</th>
                    <th class="col-journal">Jurnal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="col-date">{{ $row->TGLFAKTUR?->format('d/m/Y') }}</td>
                        <td class="col-faktur">{{ $row->NOFAKTUR }}</td>
                        <td class="col-customer">
                            <div class="fw-semibold">{{ $row->CUSTOMER }}</div>
                            <div class="small text-muted">{{ $row->customer_name ?: '-' }}</div>
                        </td>
                        <td class="col-jenis">
                            @php($badge = match($row->jenis_code) {
                                'EKS' => 'primary',
                                'REN' => 'success',
                                'CO' => 'info',
                                default => 'secondary',
                            })
                            <span class="badge text-bg-{{ $badge }}">{{ $row->jenis_label }}</span>
                        </td>
                        <td class="col-amount amount-col">{{ number_format((float) $row->PIUTANG, 2, ',', '.') }}</td>
                        <td class="col-amount amount-col">{{ number_format((float) $row->BAYAR, 2, ',', '.') }}</td>
                        <td class="col-amount amount-col fw-semibold">{{ number_format((float) $row->SALDO, 2, ',', '.') }}</td>
                        <td class="col-status"><span class="badge text-bg-{{ strtoupper((string)$row->STATUS) === 'LUNAS' ? 'success' : 'warning' }}">{{ $row->STATUS ?: '-' }}</span></td>
                        <td class="col-journal">
                            @if($row->journal_id)
                                <a href="{{ route('jurnal.show', ['jurnal' => $row->journal_id, 'from' => 'laporan-penjualan']) }}"
                                   class="btn btn-sm btn-outline-primary sales-journal-btn"
                                   title="Lihat jurnal"
                                   target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @elseif($row->NOJURNAL)
                                <button type="button" class="btn btn-sm btn-outline-warning sales-journal-btn" title="Jurnal tidak ditemukan" disabled>
                                    <i class="bi bi-exclamation-triangle"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary sales-journal-btn" title="Belum ada jurnal" disabled>
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data penjualan sesuai filter.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="4" class="total-label">TOTAL ({{ number_format($summary['count'], 0, ',', '.') }})</th>
                    <th class="col-amount amount-col">{{ number_format($summary['piutang'], 2, ',', '.') }}</th>
                    <th class="col-amount amount-col">{{ number_format($summary['bayar'], 2, ',', '.') }}</th>
                    <th class="col-amount amount-col">{{ number_format($summary['saldo'], 2, ',', '.') }}</th>
                    <th class="col-status"></th>
                    <th class="col-journal"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="sales-mobile-totals" aria-label="Total penjualan">
    <div class="item">
        <span class="label">Nilai</span>
        <span class="value">{{ number_format($summary['piutang'], 2, ',', '.') }}</span>
    </div>
    <div class="item">
        <span class="label">Terbayar</span>
        <span class="value">{{ number_format($summary['bayar'], 2, ',', '.') }}</span>
    </div>
    <div class="item">
        <span class="label">Saldo</span>
        <span class="value">{{ number_format($summary['saldo'], 2, ',', '.') }}</span>
    </div>
</div>

@if($rows->hasPages())
    <div class="mt-3">{{ $rows->links() }}</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const customerModal = document.getElementById('reportCustomerModal');
    const customerSearch = document.getElementById('reportCustomerSearch');
    const customerResults = document.getElementById('reportCustomerResults');
    const customerLoading = document.getElementById('reportCustomerLoading');
    const customerEmpty = document.getElementById('reportCustomerEmpty');
    const customerError = document.getElementById('reportCustomerError');
    const customerFilter = document.getElementById('reportCustomerFilter');
    let customerSearchTimer;

    const escapeCustomerHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[char]));

    function loadReportCustomers(query = '') {
        customerLoading.classList.remove('d-none');
        customerEmpty.classList.add('d-none');
        customerError.classList.add('d-none');
        customerResults.innerHTML = '';

        fetch("{{ route('pelunasan-piutang.search-customer') }}?modal=1&q=" + encodeURIComponent(query), {
            headers: {'Accept': 'application/json'}
        })
            .then(response => { if (!response.ok) throw new Error(); return response.json(); })
            .then(rows => {
                if (!rows.length) { customerEmpty.classList.remove('d-none'); return; }
                customerResults.innerHTML = rows.map(row => `
                    <tr>
                        <td class="fw-semibold">${escapeCustomerHtml(row.customer)}</td>
                        <td>${escapeCustomerHtml(row.nama || '-')}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-primary btn-sm select-report-customer" data-customer="${encodeURIComponent(row.customer)}">Pilih</button>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(() => customerError.classList.remove('d-none'))
            .finally(() => customerLoading.classList.add('d-none'));
    }

    customerModal?.addEventListener('shown.bs.modal', function () {
        customerSearch.value = customerFilter.value;
        customerSearch.focus();
        loadReportCustomers(customerSearch.value);
    });
    customerSearch?.addEventListener('input', function () {
        clearTimeout(customerSearchTimer);
        customerSearchTimer = setTimeout(() => loadReportCustomers(customerSearch.value.trim()), 250);
    });
    customerResults?.addEventListener('click', function (event) {
        const button = event.target.closest('.select-report-customer');
        if (!button) return;
        customerFilter.value = decodeURIComponent(button.dataset.customer);
        bootstrap.Modal.getInstance(customerModal)?.hide();
    });
    document.getElementById('clearReportCustomer')?.addEventListener('click', () => {
        customerFilter.value = '';
    });
});
</script>
@endpush
@endsection
