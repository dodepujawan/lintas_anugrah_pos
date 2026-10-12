<?php $__env->startSection('title', 'Laporan Laba Rugi'); ?>

<?php $__env->startSection('content'); ?>
<?php $__env->startPush('styles'); ?>
<style>
    .mobile-code {
        display: none;
    }

    .section-summary-content { display:flex; align-items:center; justify-content:space-between; gap:1rem; width:100%; }
    .section-summary-content>span:first-child { flex:1; text-align:left; }
    .persistent-summary-value { margin-left:auto; padding-left:2rem; white-space:nowrap; font-weight:700; text-align:right; }
    .laba-rugi-report .tree-row[data-tree-persistent] { display:table-row !important; }


    @media (max-width: 768px) {
        .persistent-summary-value { padding-left:1rem; }
        .laba-rugi-report .card-body>.d-flex {
            align-items: stretch !important;
            flex-direction: column;
            gap: 0.75rem;
            padding: 0.75rem 0.9rem !important;
        }

        .laba-rugi-report .card-body>.d-flex p {
            display: none;
        }

        .laba-rugi-report .btn-group {
            display: flex;
            width: 100%;
        }

        .laba-rugi-report .btn-group .btn {
            flex: 1 1 50%;
        }

        .laba-rugi-report .mobile-report-table {
            width: 100%;
            table-layout: fixed;
            font-size: 0.8rem;
        }

        .laba-rugi-report .mobile-report-table tbody {
            display: block;
            width: 100%;
        }

        .laba-rugi-report .mobile-report-table .account-row,
        .laba-rugi-report .mobile-report-table .summary-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(108px, 36%);
            width: 100%;
        }

        .laba-rugi-report .mobile-report-table .section-row {
            display: block !important;
            width: 100%;
        }

        .laba-rugi-report .mobile-report-table .section-row>td {
            display: block;
            width: 100%;
        }

        .laba-rugi-report .mobile-report-table .summary-row[data-tree-persistent] { display:grid !important; }

        .laba-rugi-report .mobile-report-table .profit-before-tax-row {
            display:grid !important;
            grid-template-columns:minmax(0,1fr) minmax(108px,36%);
        }

        .laba-rugi-report .mobile-report-table .profit-before-tax-row>td {
            display:flex;
            align-items:center;
            width:auto;
        }

        .laba-rugi-report .mobile-report-table .profit-before-tax-row>td:last-child {
            justify-content:flex-end;
        }

        .laba-rugi-report .mobile-report-table td,
        .laba-rugi-report .mobile-report-table th {
            box-sizing: border-box;
        }

        .laba-rugi-report .mobile-report-table thead {
            display: block;
            width: 100%;
        }

        .laba-rugi-report .mobile-report-table thead tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(108px, 36%);
            width: 100%;
        }

        .laba-rugi-report .mobile-report-table thead .code-heading {
            display: none;
        }

        .laba-rugi-report .mobile-report-table thead .name-heading {
            width: 100% !important;
            min-width: 0;
            text-align: left;
        }

        .laba-rugi-report .mobile-report-table thead .amount-heading {
            width: 100% !important;
            max-width: none !important;
            min-width: 0;
            justify-self: stretch;
            padding-left: 0.15rem !important;
            padding-right: 0.4rem !important;
            text-align: right !important;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            white-space: nowrap;
        }

        .laba-rugi-report .mobile-report-table td {
            padding: 0.45rem 0.5rem !important;
        }

        .laba-rugi-report .mobile-report-table .tree-row td:not([colspan]):first-child {
            display: none;
        }

        .laba-rugi-report .mobile-report-table .tree-row td:not([colspan]):nth-child(2) {
            width: auto;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .laba-rugi-report .mobile-report-table .tree-row td:last-child:not([colspan]) {
            width: auto;
            max-width: none;
            min-width: 0;
            white-space: nowrap !important;
            word-break: normal !important;
            overflow: hidden;
            text-align: right !important;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            font-size: clamp(0.62rem, 2.7vw, 0.74rem);
            letter-spacing: -0.02em;
            padding-left: 0.15rem !important;
            padding-right: 0.4rem !important;
        }

        .laba-rugi-report .mobile-report-table td.text-end {
            text-align: right !important;
        }

        .laba-rugi-report .mobile-report-table .currency {
            display: none;
        }

        .laba-rugi-report .mobile-report-table .mobile-code {
            display: block;
            margin-bottom: 0.1rem;
            color: #64748b;
            font-size: 0.68rem;
            font-weight: 600;
            line-height: 1.1;
            white-space: nowrap;
        }
    }
</style>
<?php $__env->stopPush(); ?>
<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-bold text-uppercase"><?php echo e(config('app.company_name')); ?></div>
            <h2 class="page-title"><i class="bi bi-graph-up-arrow me-2"></i>Laporan Laba Rugi</h2>
            <small class="text-muted">Periode: <?php echo e($periodeLabel); ?></small>
            <?php if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI'): ?>
            <small class="ms-2 badge bg-danger">TERKUNCI</small>
            <?php endif; ?>
        </div>
        <div>
            <a href="<?php echo e(route('laporan.laba_rugi.excel', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir])); ?>" class="btn btn-success me-2" id="labaRugiExportExcelButton" data-base-url="<?php echo e(route('laporan.laba_rugi.excel')); ?>">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <?php echo $__env->make('laporan._print-format-dropdown', ['pdfUrl'=>route('laporan.stream_pdf',['report'=>'laba-rugi']+request()->except('print_format')),'dotMatrixUrl'=>route('laporan.stream_pdf',['report'=>'laba-rugi','print_format'=>'dot_matrix']+request()->except('print_format'))], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>

<div class="print-only-header">
    <div class="title-line fw-bold"><?php echo e(config('app.company_name')); ?></div>
    <h2 class="page-title">Laporan Laba Rugi</h2>
    <small class="text-muted">Periode: <?php echo e($periodeLabel); ?></small>
    <?php if(($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI'): ?>
    <small class="text-muted d-block">Status: TERKUNCI</small>
    <?php endif; ?>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('laporan.laba_rugi')); ?>" class="row g-3" id="labaRugiFilterForm">
            <div class="col-md-3">
                <label for="mode_periode" class="form-label">Mode Periode</label>
                <select class="form-select" id="mode_periode" name="mode_periode">
                    <option value="bulanan" <?php echo e(($mode_periode ?? 'bulanan') === 'bulanan' ? 'selected' : ''); ?>>Bulanan</option>
                    <option value="tahunan" <?php echo e(($mode_periode ?? 'bulanan') === 'tahunan' ? 'selected' : ''); ?>>Tahunan</option>
                    <option value="range" <?php echo e(($mode_periode ?? 'bulanan') === 'range' ? 'selected' : ''); ?>>Rentang Tanggal</option>
                </select>
            </div>

            <div class="col-md-3 period-month <?php echo e(($mode_periode ?? 'bulanan') === 'bulanan' ? '' : 'd-none'); ?>">
                <label for="month" class="form-label">Bulan</label>
                <select class="form-select" id="month" name="month">
                    <?php $__currentLoopData = ['01' => 'Januari','02' => 'Februari','03' => 'Maret','04' => 'April','05' => 'Mei','06' => 'Juni','07' => 'Juli','08' => 'Agustus','09' => 'September','10' => 'Oktober','11' => 'November','12' => 'Desember']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php echo e(str_pad((string) ($month ?? now()->format('m')), 2, '0', STR_PAD_LEFT) === str_pad((string) $value, 2, '0', STR_PAD_LEFT) ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="col-md-3 period-year <?php echo e(in_array(($mode_periode ?? 'bulanan'), ['bulanan', 'tahunan'], true) ? '' : 'd-none'); ?>">
                <label for="year" class="form-label">Tahun</label>
                <select class="form-select" id="year" name="year">
                    <?php $currentYear = now()->format('Y'); ?>
                    <?php $__currentLoopData = range($currentYear - 5, $currentYear + 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($yearOption); ?>" <?php echo e(($year ?? $currentYear) == $yearOption ? 'selected' : ''); ?>><?php echo e($yearOption); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="col-md-3 period-range <?php echo e(($mode_periode ?? 'bulanan') === 'range' ? '' : 'd-none'); ?>">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="<?php echo e($tanggalAwal); ?>">
            </div>

            <div class="col-md-3 period-range <?php echo e(($mode_periode ?? 'bulanan') === 'range' ? '' : 'd-none'); ?>">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="<?php echo e($tanggalAkhir); ?>">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4 laba-rugi-report">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light">
            <div>
                <strong>Struktur Tree Laba Rugi</strong>
                <p class="mb-0 text-muted">Pendapatan dan Beban dengan subtotal.</p>
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
                        <th class="px-3 code-heading" width="20%">Kode</th>
                        <th class="name-heading">
                            <span class="d-none d-md-inline">Nama Akun</span>
                            <span class="d-md-none">Kode/Nama Akun</span>
                        </th>
                        <th class="text-end px-3 amount-heading" width="25%">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="tree-row table-success section-row" data-id="section-pendapatan" data-parent-id="">
                        <td colspan="3" class="px-3">
                            <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="section-pendapatan" aria-label="Toggle Pendapatan"><i class="bi bi-chevron-down tree-toggle-icon"></i></button><strong>PENDAPATAN</strong>
                        </td>
                    </tr>
                    <?php $__currentLoopData = $pendapatans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="tree-row account-row" data-parent-id="section-pendapatan">
                        <td class="px-3"><?php echo e($p->kode_akun); ?></td>
                        <td><span class="mobile-code"><?php echo e($p->kode_akun); ?></span><?php echo e($p->nama_akun); ?></td>
                        <td class="text-end px-3 fw-bold text-success"><?php echo e(number_format($pendapatanSaldo[$p->id] ?? 0, 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <tr class="tree-row table-secondary summary-row" data-parent-id="section-pendapatan">
                        <td colspan="2" class="px-3 fw-bold">Total Pendapatan</td>
                        <td class="text-end px-3 fw-bold"><?php echo e(number_format($totalPendapatan, 2)); ?></td>
                    </tr>

                    <tr class="tree-row table-danger section-row" data-id="section-beban" data-parent-id="">
                        <td colspan="3" class="px-3">
                            <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="section-beban" aria-label="Toggle Beban"><i class="bi bi-chevron-down tree-toggle-icon"></i></button><strong>BEBAN</strong>
                        </td>
                    </tr>
                    <?php $__currentLoopData = $bebans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="tree-row account-row" data-parent-id="section-beban">
                        <td class="px-3"><?php echo e($b->kode_akun); ?></td>
                        <td><span class="mobile-code"><?php echo e($b->kode_akun); ?></span><?php echo e($b->nama_akun); ?></td>
                        <td class="text-end px-3 fw-bold text-danger"><?php echo e(number_format($bebanSaldo[$b->id] ?? 0, 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <tr class="tree-row table-secondary summary-row" data-parent-id="section-beban">
                        <td colspan="2" class="px-3 fw-bold">Total Beban</td>
                        <td class="text-end px-3 fw-bold"><?php echo e(number_format($totalBeban, 2)); ?></td>
                    </tr>

                    <tr class="tree-row table-info section-row profit-before-tax-row" data-tree-persistent>
                        <td colspan="2" class="px-3 fw-bold text-start">LABA SEBELUM PAJAK</td>
                        <td class="text-end px-3 fw-bold"><?php echo e(number_format($labaSebelumPajak, 2)); ?></td>
                    </tr>
                    <tr class="tree-row summary-row" data-tree-persistent>
                        <td colspan="2" class="px-3">Taksiran Pajak Penghasilan</td>
                        <td class="text-end px-3"><?php echo e(number_format($taksiranPajakPenghasilan, 2)); ?></td>
                    </tr>
                    <tr class="tree-row table-secondary summary-row" data-tree-persistent>
                        <td colspan="2" class="px-3 fw-bold">LABA BERSIH</td>
                        <td class="text-end px-3 fw-bold"><?php echo e(number_format(abs($labaBersih), 2)); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('laporan._report-print', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->startPush('styles'); ?>
<style>
    @media print {
        /* Cetak mengikuti baris tree yang sedang terlihat di layar. */
        .laba-rugi-report .tree-row.print-tree-hidden {
            display: none !important;
        }

    }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('laporan._tree-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modePeriode = document.getElementById('mode_periode');
        const monthGroups = document.querySelectorAll('.period-month');
        const yearGroups = document.querySelectorAll('.period-year');
        const rangeGroups = document.querySelectorAll('.period-range');
        const filterForm = document.getElementById('labaRugiFilterForm');
        const exportButton = document.getElementById('labaRugiExportExcelButton');

        window.addEventListener('beforeprint', function() {
            document.querySelectorAll('.laba-rugi-report .tree-row').forEach(function(row) {
                row.classList.toggle('print-tree-hidden', row.style.display === 'none');
            });
        });

        window.addEventListener('afterprint', function() {
            document.querySelectorAll('.laba-rugi-report .print-tree-hidden').forEach(function(row) {
                row.classList.remove('print-tree-hidden');
            });
        });

        function togglePeriodeFields() {
            const isMonthly = modePeriode && modePeriode.value === 'bulanan';
            const isYearly = modePeriode && modePeriode.value === 'tahunan';
            monthGroups.forEach(function(group) {
                group.classList.toggle('d-none', !isMonthly);
                group.querySelectorAll('input, select').forEach(function(field) {
                    field.disabled = !isMonthly;
                });
            });
            yearGroups.forEach(function(group) {
                group.classList.toggle('d-none', !isMonthly && !isYearly);
                group.querySelectorAll('input, select').forEach(function(field) {
                    field.disabled = !isMonthly && !isYearly;
                });
            });
            rangeGroups.forEach(function(group) {
                group.classList.toggle('d-none', isMonthly || isYearly);
                group.querySelectorAll('input, select').forEach(function(field) {
                    field.disabled = isMonthly || isYearly;
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\LARAVEL_APP\akuntansi\resources\views/laporan/laba_rugi.blade.php ENDPATH**/ ?>