

<?php $__env->startSection('title', 'Daftar Pelunasan Piutang'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 receivable-payment-header">
    <div>
        <h1 class="page-title mb-1">Daftar Pelunasan Piutang</h1>
        <p class="text-muted mb-0">Kelola pembayaran piutang pelanggan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('panduan.pelunasan_piutang')); ?>" class="btn btn-outline-info">
            <i class="bi bi-book"></i> Panduan
        </a>
        <a href="<?php echo e(route('pelunasan-piutang.create')); ?>" class="btn btn-primary">Tambah Pelunasan</a>
    </div>
</div>

<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3 receivable-payment-filter">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('pelunasan-piutang.index')); ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Nomor AR / No. Bukti / Nomor Faktur</label>
                <input type="search" name="search" class="form-control form-control-sm" value="<?php echo e($search); ?>" placeholder="Contoh: AR2607..., nomor referensi, atau faktur">
            </div>
            <div class="col-md-5">
                <label class="form-label">Customer</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="customerFilter" name="customer" class="form-control" value="<?php echo e($customer); ?>" placeholder="Semua customer" readonly>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#customerFilterModal"><i class="bi bi-search"></i> Cari</button>
                    <?php if($customer !== ''): ?><button type="button" id="clearCustomerFilter" class="btn btn-outline-secondary" title="Hapus customer"><i class="bi bi-x-lg"></i></button><?php endif; ?>
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <?php if($search !== '' || $customer !== ''): ?><a href="<?php echo e(route('pelunasan-piutang.index')); ?>" class="btn btn-outline-secondary btn-sm">Reset</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 receivable-payment-table">
                <colgroup>
                    <col class="number-col"><col class="date-col"><col class="customer-col"><col class="reference-col">
                    <col class="method-col"><col class="amount-col"><col class="status-col"><col class="action-col">
                </colgroup>
                <thead class="table-light">
                    <tr>
                        <th>No. Bukti</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Referensi</th>
                        <th>Metode</th>
                        <th class="text-end">Jumlah Bayar</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($payment->NOBAYAR); ?></strong></td>
                        <td><?php echo e($payment->TGLBAYAR->format('d M Y')); ?></td>
                        <td><strong><?php echo e($payment->CUSTOMER); ?></strong><div class="small text-muted"><?php echo e($payment->CUSTOMER_NAME ?: 'Nama customer belum tersedia'); ?></div></td>
                        <td><?php echo e($payment->NO_REFERENSI ?? '-'); ?></td>
                        <td>
                            <span class="badge bg-info"><?php echo e($payment->METODE_PEMBAYARAN); ?></span>
                        </td>
                        <td class="text-end"><?php echo e(number_format($payment->JUMLAH_BAYAR, 0, ',', '.')); ?></td>
                        <td>
                            <span class="badge bg-<?php echo e($payment->STATUS === 'VOID' ? 'danger' : 'success'); ?>"><?php echo e($payment->STATUS === 'VOID' ? 'Dibatalkan' : 'Aktif'); ?></span>
                            <?php if($payment->STATUS === 'VOID'): ?>
                                <div class="mt-1">
                                    <?php if($payment->NOJURNAL_PEMBALIK): ?>
                                        <?php if($payment->REVERSAL_JOURNAL_ID): ?>
                                            <a class="badge bg-secondary text-decoration-none" href="<?php echo e(route('jurnal.show', $payment->REVERSAL_JOURNAL_ID)); ?>" title="Lihat jurnal pembalik void">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i><?php echo e($payment->NOJURNAL_PEMBALIK); ?>

                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark" title="Nomor jurnal pembalik tercatat, tetapi jurnal tidak ditemukan">
                                                <i class="bi bi-exclamation-triangle me-1"></i><?php echo e($payment->NOJURNAL_PEMBALIK); ?>

                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Jurnal balik belum ada</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><div class="payment-actions">
                            <a href="<?php echo e(route('pelunasan-piutang.show', $payment->id)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                            <?php if($payment->STATUS !== 'VOID' && !in_array((string)$payment->TGLBAYAR->year, $closedYears, true) && (auth()->user()?->hasMenuPermission('pelunasan-piutang.edit', 'edit') ?? false)): ?>
                            <a href="<?php echo e(route('pelunasan-piutang.edit', $payment->id)); ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                            <?php endif; ?>
                        </div></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <p class="text-muted mb-0">Belum ada data pelunasan</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    <?php echo e($data->links()); ?>

</div>

<div class="modal fade" id="customerFilterModal" tabindex="-1" aria-labelledby="customerFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="customerFilterModalLabel"><i class="bi bi-search me-2"></i>Cari Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="search" id="customerModalSearch" class="form-control form-control-sm mb-3" placeholder="Cari kode atau nama customer..." autocomplete="off">
            <div id="customerModalLoading" class="text-center text-muted py-3 d-none">Memuat customer...</div>
            <div id="customerModalEmpty" class="text-center text-muted py-3 d-none">Customer tidak ditemukan.</div>
            <div id="customerModalError" class="alert alert-danger py-2 d-none">Data customer gagal dimuat.</div>
            <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0"><thead class="table-light"><tr><th>Kode</th><th>Nama Customer</th><th></th></tr></thead><tbody id="customerModalResults"></tbody></table></div>
        </div>
    </div></div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .receivable-payment-header .page-title { font-size: 1.2rem; }
    .receivable-payment-header p { font-size: .875rem; }
    .receivable-payment-header .btn { padding: .38rem .65rem; font-size: .82rem; }
    .receivable-payment-filter .card-body { padding: .65rem; }
    .receivable-payment-filter .form-label { margin-bottom: .2rem; font-size: .8rem; }
    .receivable-payment-filter .form-control,
    .receivable-payment-filter .btn { font-size: .82rem; }
    .receivable-payment-table { table-layout: fixed; }
    .receivable-payment-table .number-col { width: 13%; }
    .receivable-payment-table .date-col { width: 10%; }
    .receivable-payment-table .customer-col { width: 22%; }
    .receivable-payment-table .reference-col { width: 14%; }
    .receivable-payment-table .method-col { width: 10%; }
    .receivable-payment-table .amount-col { width: 13%; }
    .receivable-payment-table .status-col { width: 8%; }
    .receivable-payment-table .action-col { width: 10%; }
    .receivable-payment-table th,
    .receivable-payment-table td {
        padding: .3rem .38rem;
        font-size: .88rem;
        line-height: 1.3;
        vertical-align: middle;
    }
    .receivable-payment-table th { font-size: .84rem; white-space: nowrap; }
    .receivable-payment-table td:nth-child(2),
    .receivable-payment-table td:nth-child(5),
    .receivable-payment-table td:nth-child(6),
    .receivable-payment-table td:nth-child(7) { white-space: nowrap; }
    .receivable-payment-table td:nth-child(3),
    .receivable-payment-table td:nth-child(4) { overflow-wrap: anywhere; }
    .receivable-payment-table .small { font-size: .8rem; line-height: 1.25; }
    .receivable-payment-table .badge { padding: .24rem .38rem; font-size: .77rem; }
    .receivable-payment-table .payment-actions { display: flex; flex-wrap: nowrap; gap: .2rem; }
    .receivable-payment-table .btn-sm { padding: .24rem .4rem; font-size: .8rem; }

    @media (max-width: 767.98px) {
        .receivable-payment-header > div:last-child { width: 100%; }
        .receivable-payment-header > div:last-child .btn { flex: 1 1 auto; }
        .receivable-payment-table { min-width: 760px; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('customerFilterModal');
    const searchInput = document.getElementById('customerModalSearch');
    const results = document.getElementById('customerModalResults');
    const loading = document.getElementById('customerModalLoading');
    const empty = document.getElementById('customerModalEmpty');
    const error = document.getElementById('customerModalError');
    const customerFilter = document.getElementById('customerFilter');
    let timer;

    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));

    function loadCustomers(query = '') {
        loading.classList.remove('d-none'); empty.classList.add('d-none'); error.classList.add('d-none'); results.innerHTML = '';
        fetch("<?php echo e(route('pelunasan-piutang.search-customer')); ?>?modal=1&q=" + encodeURIComponent(query), {headers: {'Accept': 'application/json'}})
            .then(response => { if (!response.ok) throw new Error(); return response.json(); })
            .then(rows => {
                if (!rows.length) { empty.classList.remove('d-none'); return; }
                results.innerHTML = rows.map(row => `<tr><td class="fw-semibold">${escapeHtml(row.customer)}</td><td>${escapeHtml(row.nama || '-')}</td><td class="text-end"><button type="button" class="btn btn-primary btn-sm select-filter-customer" data-customer="${encodeURIComponent(row.customer)}">Pilih</button></td></tr>`).join('');
            })
            .catch(() => error.classList.remove('d-none'))
            .finally(() => loading.classList.add('d-none'));
    }

    modalElement.addEventListener('shown.bs.modal', function () { searchInput.value = customerFilter.value; searchInput.focus(); loadCustomers(searchInput.value); });
    searchInput.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(() => loadCustomers(searchInput.value.trim()), 250); });
    results.addEventListener('click', function (event) {
        const button = event.target.closest('.select-filter-customer');
        if (!button) return;
        customerFilter.value = decodeURIComponent(button.dataset.customer);
        bootstrap.Modal.getInstance(modalElement).hide();
    });
    document.getElementById('clearCustomerFilter')?.addEventListener('click', () => { customerFilter.value = ''; });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\LARAVEL_APP\akuntansi\resources\views/pelunasan-piutang/index.blade.php ENDPATH**/ ?>