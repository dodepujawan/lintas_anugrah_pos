<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Kwitansi Expedisi</h1>
            <p class="text-muted small mb-0">Daftar kwitansi pembayaran</p>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#prosesKwitansiModal">
                <i class="bi bi-plus-lg"></i> Proses Kwitansi
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>No. KWT</th>
                        <th>Tgl KWT</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th class="text-end">Saldo Faktur</th>
                        <th class="text-end">Bayar</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $kwitantis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($kwitantis->firstItem() + $loop->index); ?></td>
                        <td class="font-monospace"><?php echo e($k->NO_KWT); ?></td>
                        <td><?php echo e($k->TGL_KWT?->format('d/m/Y')); ?></td>
                        <td class="font-monospace"><?php echo e($k->NOFAKTUR); ?></td>
                        <td><?php echo e($k->CUSTOMER); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) $k->SALDO_FAKTUR, 0, ',', '.')); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) $k->NILAI_BAYAR, 0, ',', '.')); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) ($k->SALDO_FAKTUR - $k->BAYAR), 0, ',', '.')); ?></td>
                        <td class="text-end">
                            <a href="<?php echo e(route('expedisi-kwitansi.printKwitansi', $k)); ?>" class="btn btn-sm btn-outline-primary" title="Print">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data kwitansi.</p>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="prosesKwitansiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="kwitansiForm" method="POST" action="<?php echo e(route('expedisi-kwitansi.proses')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Proses Kwitansi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">No Invoice <span class="text-danger">*</span></label>
                            <input type="text" name="NOFAKTUR" class="form-control form-control-sm" required list="invoiceList">
                            <datalist id="invoiceList">
                                <?php $__currentLoopData = \App\Models\Arh::where('STATUS','OPEN')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($arh->NOFAKTUR); ?>"><?php echo e($arh->NOFAKTUR); ?> — <?php echo e($arh->CUSTOMER); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </datalist>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tgl KWT <span class="text-danger">*</span></label>
                            <input type="date" name="TGL_KWT" class="form-control form-control-sm" value="<?php echo e(now()->format('Y-m-d')); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nilai Bayar <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="NILAI_BAYAR" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <input type="text" name="METODE_PEMBAYARAN" class="form-control form-control-sm" value="TUNAI">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="KETERANGAN" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/src/resources/views/expedisi-kwitansi/index.blade.php ENDPATH**/ ?>