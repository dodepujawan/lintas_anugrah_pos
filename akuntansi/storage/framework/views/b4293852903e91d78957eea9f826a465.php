<?php $__env->startSection('title', 'Daftar Pelunasan Hutang'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Daftar Pelunasan Hutang</h1>
        <p class="text-muted mb-0">Pembayaran hutang supplier.</p>
    </div>
    <a href="<?php echo e(route('pelunasan-hutang.create')); ?>" class="btn btn-primary">Tambah Pelunasan</a>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Bukti</th>
                        <th>Tanggal</th>
                        <th>Jurnal</th>
                        <th>Supplier</th>
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
                            <td>
                                <?php if($payment->journal): ?>
                                    <a href="<?php echo e(route('jurnal.show', ['jurnal' => $payment->journal, 'from' => 'pelunasan-hutang', 'pelunasan_hutang_id' => $payment->id])); ?>" class="badge text-bg-primary text-decoration-none">
                                        <?php echo e($payment->NOJURNAL); ?>

                                    </a>
                                <?php elseif($payment->NOJURNAL): ?>
                                    <span class="badge text-bg-warning"><?php echo e($payment->NOJURNAL); ?></span>
                                <?php else: ?>
                                    <span class="badge text-bg-danger">Belum Jurnal</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($payment->supplier_display); ?></td>
                            <td><?php echo e($payment->NO_REFERENSI ?? '-'); ?></td>
                            <td><span class="badge bg-info"><?php echo e($payment->METODE_PEMBAYARAN); ?></span></td>
                            <td class="text-end"><?php echo e(number_format($payment->JUMLAH_BAYAR, 0, ',', '.')); ?></td>
                            <td><span class="badge bg-<?php echo e($payment->STATUS === 'VOID' ? 'danger' : 'success'); ?>"><?php echo e($payment->STATUS === 'VOID' ? 'Dibatalkan' : 'Aktif'); ?></span></td>
                            <td class="text-end text-nowrap">
                                <a href="<?php echo e(route('pelunasan-hutang.show', $payment->id)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                <?php if($payment->STATUS !== 'VOID'): ?>
                                    <a href="<?php echo e(route('pelunasan-hutang.edit', $payment->id)); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <form method="POST" action="<?php echo e(route('pelunasan-hutang.destroy', $payment->id)); ?>" class="d-inline" onsubmit="return confirm('Hapus pelunasan <?php echo e($payment->NOBAYAR); ?>? Saldo hutang akan dikembalikan dan jurnal pelunasan akan dihapus.')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger payment-delete-btn" title="Hapus pelunasan" aria-label="Hapus pelunasan <?php echo e($payment->NOBAYAR); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="9" class="text-center py-4">Belum ada data pelunasan hutang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    <?php echo e($data->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .payment-delete-btn {
        --bs-btn-padding-x: .42rem;
        --bs-btn-padding-y: .24rem;
        opacity: .72;
    }

    .payment-delete-btn:hover,
    .payment-delete-btn:focus {
        opacity: 1;
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\LARAVEL_APP\akuntansi\resources\views/pelunasan-hutang/index.blade.php ENDPATH**/ ?>