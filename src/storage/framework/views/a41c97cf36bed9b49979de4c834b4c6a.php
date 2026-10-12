<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Ekspedisi (EKS)</h1>
            <p class="text-muted small mb-0">Daftar surat jalan ekspedisi</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="<?php echo e(route('expedisi.print.muat')); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-pdf"></i> Print Muat
            </a>
            <a href="<?php echo e(route('expedisi.create')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah SJ
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari NOSJ / Invoice / Customer..." value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="date" class="form-control form-control-sm"
                       value="<?php echo e(request('date')); ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="OPEN" <?php echo e(request('status') == 'OPEN' ? 'selected' : ''); ?>>OPEN</option>
                    <option value="CLOSE" <?php echo e(request('status') == 'CLOSE' ? 'selected' : ''); ?>>CLOSE</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2 text-end">
                <a href="<?php echo e(route('expedisi.index')); ?>" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NOSJ</th>
                        <th>Tgl Muat</th>
                        <th>Customer</th>
                        <th>Kendaraan</th>
                        <th class="text-end">Grand Total</th>
                        <th>Invoice</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $expedisis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($expedisis->firstItem() + $loop->index); ?></td>
                        <td class="font-monospace"><?php echo e($e->NOSJ); ?></td>
                        <td><?php echo e($e->TGLMUAT?->format('d/m/Y') ?? '-'); ?></td>
                        <td><?php echo e($e->CUSTOMER); ?></td>
                        <td><?php echo e($e->NAMA_KENDARAAN ?? $e->KENDARAAN); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) $e->GRAND, 0, ',', '.')); ?></td>
                        <td class="font-monospace"><?php echo e($e->INVOICE ?? '-'); ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo e(route('expedisi.print.surjal', $e)); ?>" class="btn btn-outline-primary" title="Print SJ">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="<?php echo e(route('expedisi.show', $e)); ?>" class="btn btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo e(route('expedisi.edit', $e)); ?>" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="<?php echo e(route('expedisi.destroy', $e)); ?>" method="POST"
                                      onsubmit="return confirm('Hapus ekspedisi ini? Data yang sudah diinvoice tidak bisa dihapus.')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-2">Tidak ada data ekspedisi.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <?php echo e($expedisis->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('alpine:init', () => {
        // Search filter otomatis
        document.querySelector('input[name="search"]').addEventListener('input', function(e) {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => {
                this.closest('form').requestSubmit();
            }, 500);
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/src/resources/views/expedisi/index.blade.php ENDPATH**/ ?>