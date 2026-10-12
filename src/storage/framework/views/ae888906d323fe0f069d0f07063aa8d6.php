<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Coolroom</h1>
            <p class="text-muted small mb-0">Daftar transaksi coolroom</p>
        </div>
        <div class="col-auto">
            <a href="<?php echo e(route('coolroom.create')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari NOSJ / Customer..." value="<?php echo e(request('search')); ?>">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="<?php echo e(route('coolroom.index')); ?>" class="btn btn-outline-secondary">Reset</a>
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
                        <th class="text-end">Grand</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $coolrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($coolrooms->firstItem() + $loop->index); ?></td>
                        <td class="font-monospace"><?php echo e($c->NOSJ); ?></td>
                        <td><?php echo e($c->TGLMUAT?->format('d/m/Y')); ?></td>
                        <td><?php echo e($c->CUSTOMER); ?></td>
                        <td><?php echo e($c->NAMA_KENDARAAN ?? $c->KENDARAAN); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) $c->GRAND, 0, ',', '.')); ?></td>
                        <td><?php echo e($c->STATUS ?? '-'); ?></td>
                        <td class="text-end">
                            <a href="<?php echo e(route('coolroom.edit', $c)); ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data coolroom.</p>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3"><?php echo e($coolrooms->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/src/resources/views/coolroom/index.blade.php ENDPATH**/ ?>