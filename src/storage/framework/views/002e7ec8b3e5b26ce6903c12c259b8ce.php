

<?php $__env->startSection('title', 'Saldo Awal Akun'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .filter-card {
        background: #f8f9fa;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div>
            <h2><i class="bi bi-wallet2"></i> Saldo Awal Akun</h2>
            <p class="text-muted mb-0">Kelola saldo awal akun dengan draft, posting, export Excel, dan cetak tampilan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo e(route('saldo-awal.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Saldo Awal
            </a>
            <a href="<?php echo e(route('saldo-awal.export', request()->all())); ?>" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <?php echo $__env->make('laporan._print-format-dropdown',['pdfUrl'=>route('saldo-awal.pdf',request()->except(['page','print_format'])),'dotMatrixUrl'=>route('saldo-awal.pdf',request()->except(['page','print_format'])+['print_format'=>'dot_matrix']),'buttonClass'=>'btn-outline-secondary'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('saldo-awal.index')); ?>" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" name="tanggal_awal" value="<?php echo e(request('tanggal_awal', now()->startOfMonth()->format('Y-m-d'))); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" name="tanggal_akhir" value="<?php echo e(request('tanggal_akhir', now()->format('Y-m-d'))); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="posted" <?php echo e(request('status') === 'posted' ? 'selected' : ''); ?>>Posted</option>
                    <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Draft</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>No. Saldo Awal</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $saldoAwals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $saldoAwal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($saldoAwals->firstItem() + $index); ?></td>
                        <td><strong><?php echo e($saldoAwal->no_jurnal); ?></strong></td>
                        <td><?php echo e($saldoAwal->tanggal->format('d/m/Y')); ?></td>
                        <td><?php echo e(Str::limit($saldoAwal->keterangan, 60)); ?></td>
                        <td class="text-end"><?php echo e(number_format($saldoAwal->total_debit, 2)); ?></td>
                        <td class="text-end"><?php echo e(number_format($saldoAwal->total_kredit, 2)); ?></td>
                        <td>
                            <?php if($saldoAwal->status === App\Models\Jurnal::STATUS_POSTED): ?>
                            <span class="badge bg-success">Posted</span>
                            <?php else: ?>
                            <span class="badge bg-warning text-dark">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap">
                            <a href="<?php echo e(route('saldo-awal.show', $saldoAwal->id)); ?>" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if($saldoAwal->status === App\Models\Jurnal::STATUS_POSTED && auth()->user()?->role?->name === 'Administrator'): ?>
                            <form action="<?php echo e(route('saldo-awal.reopen', $saldoAwal->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Buka kembali <?php echo e($saldoAwal->no_jurnal); ?> menjadi Draft? Setelah dibuka, saldo awal harus diperiksa dan diposting ulang.');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Buka Kembali menjadi Draft">
                                    <i class="bi bi-unlock"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if($saldoAwal->status === App\Models\Jurnal::STATUS_DRAFT): ?>
                            <a href="<?php echo e(route('saldo-awal.edit', $saldoAwal->id)); ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?php echo e(route('saldo-awal.destroy', $saldoAwal->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus Draft <?php echo e($saldoAwal->no_jurnal); ?>? Semua item saldo awal di dalam Draft ini akan ikut dihapus dan tindakan ini tidak dapat dibatalkan.')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus Draft"><i class="bi bi-trash"></i></button>
                            </form>
                            <form action="<?php echo e(route('saldo-awal.post', $saldoAwal->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Posting saldo awal ini?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-success" title="Posting">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">Belum ada data Saldo Awal</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    <?php echo e($saldoAwals->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/src/resources/views/saldo-awal/index.blade.php ENDPATH**/ ?>