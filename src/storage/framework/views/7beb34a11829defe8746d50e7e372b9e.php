<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Invoice Coolroom</h1>
            <p class="text-muted small mb-0">Daftar invoice coolroom yang sudah digenerate</p>
        </div>
        <div class="col-auto">
            <a href="<?php echo e(route('coolroom-invoice.create')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Generate Invoice
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari No. Invoice / Customer..." value="<?php echo e(request('search')); ?>">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="<?php echo e(route('coolroom-invoice.index')); ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>No. Invoice</th>
                        <th>Tgl Invoice</th>
                        <th>Customer</th>
                        <th class="text-end">PIUTANG</th>
                        <th class="text-end">BAYAR</th>
                        <th class="text-end">SALDO</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($invoices->firstItem() + $loop->index); ?></td>
                        <td class="font-monospace"><?php echo e($inv->NOFAKTUR); ?></td>
                        <td><?php echo e($inv->TGLFAKTUR?->format('d/m/Y')); ?></td>
                        <td><?php echo e($inv->CUSTOMER); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) $inv->PIUTANG, 0, ',', '.')); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) $inv->BAYAR, 0, ',', '.')); ?></td>
                        <td class="text-end"><?php echo e(number_format((float) $inv->SALDO, 0, ',', '.')); ?></td>
                        <td class="text-end">
                            <a href="<?php echo e(route('coolroom-invoice.pdf', $inv->NOFAKTUR)); ?>" class="btn btn-sm btn-outline-primary" title="Print Invoice" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data invoice coolroom.</p>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3"><?php echo e($invoices->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/src/resources/views/coolroom-invoice/index.blade.php ENDPATH**/ ?>