<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Rent Pendingin</h1>
            <p class="text-muted small mb-0">Daftar sewa mobil pendingin</p>
        </div>
        <div class="col-auto">
            <a href="<?php echo e(route('rent-pendingin.create')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah SJ
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari NOSJ / Customer..." value="<?php echo e(request('search')); ?>">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="<?php echo e(route('rent-pendingin.index')); ?>" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Invoice</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($rents->firstItem() + $loop->index); ?></td>
                        <td class="font-monospace"><?php echo e($r->NOSJ); ?></td>
                        <td><?php echo e($r->TGLMUAT?->format('d/m/Y')); ?></td>
                        <td><?php echo e($r->CUSTOMER); ?></td>
                        <td><?php echo e($r->NAMA_KENDARAAN ?? $r->KENDARAAN); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) $r->GRAND, 0, ',', '.')); ?></td>
                        <td class="font-monospace"><?php echo e($r->INVOICE ?? '-'); ?></td>
                        <td class="text-end">
                            <a href="<?php echo e(route('rent-pendingin.print.surjal', [], $r->NOSJ)); ?>" class="btn btn-sm btn-outline-primary" title="Print SJ">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data rent pendingin.</p>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3"><?php echo e($rents->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/src/resources/views/rent-pendingin/index.blade.php ENDPATH**/ ?>