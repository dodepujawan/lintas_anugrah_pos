<?php $__env->startSection('title', 'Piutang Berjalan'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h2 class="h4 mb-1">Daftar Piutang Berjalan</h2><p class="text-muted mb-0">Berisi invoice berjalan dari input manual, import penjualan, dan sinkronisasi. Total keseluruhan ada di Laporan Piutang.</p></div>
    <a href="<?php echo e(route('piutang-berjalan.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Input Piutang Berjalan</a>
</div>

<div class="card shadow-sm mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-6"><label class="form-label small mb-1">Cari invoice/customer/keterangan</label><input type="search" name="search" value="<?php echo e(request('search')); ?>" class="form-control form-control-sm" placeholder="Masukkan kata pencarian"></div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Sumber</label>
            <select name="source" class="form-select form-select-sm">
                <option value="all" <?php if($source === 'all'): echo 'selected'; endif; ?>>Semua</option>
                <option value="manual" <?php if($source === 'manual'): echo 'selected'; endif; ?>>Manual</option>
                <option value="import" <?php if($source === 'import'): echo 'selected'; endif; ?>>Import Penjualan</option>
                <option value="sync" <?php if($source === 'sync'): echo 'selected'; endif; ?>>Sinkron Penjualan</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-search"></i> Cari</button><a href="<?php echo e(route('piutang-berjalan.index')); ?>" class="btn btn-outline-secondary btn-sm">Reset</a></div>
    </form>
</div></div>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-2"><div class="text-muted small">Jumlah Faktur</div><div class="fw-bold"><?php echo e(number_format((int) $totals->count, 0, ',', '.')); ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-2"><div class="text-muted small">Total Piutang</div><div class="fw-bold"><?php echo e(number_format((float) $totals->piutang, 2, ',', '.')); ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-2"><div class="text-muted small">Total Bayar</div><div class="fw-bold"><?php echo e(number_format((float) $totals->bayar, 2, ',', '.')); ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-2"><div class="text-muted small">Total Saldo</div><div class="fw-bold"><?php echo e(number_format((float) $totals->saldo, 2, ',', '.')); ?></div></div></div></div>
</div>

<div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Tanggal</th><th>Invoice</th><th>Customer</th><th>Sumber</th><th>Jatuh Tempo</th><th class="text-end">Piutang</th><th class="text-end">Bayar</th><th class="text-end">Saldo</th><th>Status</th><th>Jurnal</th><th>Aksi</th></tr></thead>
    <tbody><?php $__empty_1 = true; $__currentLoopData = $piutangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $piutang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php ($paymentDetails = $piutang->paymentDetails ?? collect()); ?>
        <tr><td><?php echo e($piutang->TGLFAKTUR?->format('d/m/Y')); ?></td><td class="fw-semibold"><?php echo e($piutang->NOFAKTUR); ?></td><td><?php echo e($piutang->CUSTOMER); ?></td><td>
            <?php ($sourceLabel = match (true) {
                $piutang->SOURCE === 'PIUTANG_BERJALAN_MANUAL' => 'Manual',
                $piutang->SOURCE === 'IMPORT_PENJUALAN' => 'Import',
                str_starts_with((string) $piutang->SOURCE, 'SYNC_') => 'Sinkron',
                default => $piutang->SOURCE ?: '-',
            }); ?>
            <span class="badge <?php echo e($piutang->SOURCE === 'PIUTANG_BERJALAN_MANUAL' ? 'bg-primary' : (str_starts_with((string) $piutang->SOURCE, 'SYNC_') ? 'bg-info text-dark' : 'bg-secondary')); ?>"><?php echo e($sourceLabel); ?></span>
        </td><td><?php echo e($piutang->TGLJT?->format('d/m/Y') ?: '-'); ?></td><td class="text-end"><?php echo e(number_format($piutang->PIUTANG,2,',','.')); ?></td><td class="text-end"><?php echo e(number_format($piutang->BAYAR,2,',','.')); ?></td><td class="text-end fw-semibold"><?php echo e(number_format($piutang->SALDO,2,',','.')); ?></td><td>
            <?php if(($piutang->STATUS ?? '') === 'VOID'): ?>
                <button type="button" class="badge border-0 bg-secondary receivable-running-payment-badge" data-bs-toggle="modal" data-bs-target="#runningPaymentDetailModal<?php echo e($piutang->id); ?>" title="Klik untuk lihat detail void"><i class="bi bi-x-circle me-1"></i>Void</button>
                <div class="small text-muted mt-1">Di-void</div>
            <?php elseif((float) $piutang->SALDO <= 0): ?>
                <button type="button" class="badge border-0 bg-success receivable-running-payment-badge" data-bs-toggle="modal" data-bs-target="#runningPaymentDetailModal<?php echo e($piutang->id); ?>" title="Klik untuk lihat detail pembayaran"><i class="bi bi-check-circle me-1"></i>Lunas</button>
                <div class="small text-success mt-1">Terbayar</div>
            <?php else: ?>
                <button type="button" class="badge border-0 bg-warning text-dark receivable-running-payment-badge" data-bs-toggle="modal" data-bs-target="#runningPaymentDetailModal<?php echo e($piutang->id); ?>" title="Klik untuk lihat detail pembayaran"><i class="bi bi-clock-history me-1"></i>Belum Lunas</button>
                <?php if($paymentDetails->isNotEmpty()): ?><div class="small text-muted mt-1"><?php echo e($paymentDetails->count()); ?> pembayaran</div><?php endif; ?>
            <?php endif; ?>
        </td><td><?php echo e($piutang->NOJURNAL ?: '-'); ?></td><td class="text-nowrap">
            <a href="<?php echo e(route('piutang.show', ['piutang' => $piutang, 'from' => 'piutang-berjalan'])); ?>" class="btn btn-outline-primary btn-sm">Detail</a>
            <?php if($piutang->SOURCE === 'PIUTANG_BERJALAN_MANUAL' && (float)$piutang->BAYAR <= 0 && (float)$piutang->RETUR <= 0 && (float)$piutang->DISCOUNT <= 0): ?>
            <a href="<?php echo e(route('piutang-berjalan.edit', $piutang)); ?>" class="btn btn-warning btn-sm">Edit</a>
            <form method="POST" action="<?php echo e(route('piutang-berjalan.destroy', $piutang)); ?>" class="d-inline" onsubmit="return confirm('Hapus piutang <?php echo e(addslashes($piutang->NOFAKTUR)); ?> beserta jurnal terkait? Data tidak dapat dikembalikan.');"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-danger btn-sm">Hapus</button></form>
            <?php elseif($piutang->SOURCE === 'PIUTANG_BERJALAN_MANUAL'): ?>
            <span class="text-muted small">Sudah ada transaksi</span>
            <?php else: ?>
            <span class="text-muted small">Dari <?php echo e(strtolower($sourceLabel)); ?></span>
            <?php endif; ?>
        </td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="11" class="text-center text-muted py-4">Belum ada piutang berjalan.</td></tr><?php endif; ?></tbody>
</table></div></div>

<?php $__currentLoopData = $piutangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $piutang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php ($paymentDetails = $piutang->paymentDetails ?? collect()); ?>
<div class="modal fade" id="runningPaymentDetailModal<?php echo e($piutang->id); ?>" tabindex="-1" aria-labelledby="runningPaymentDetailModalLabel<?php echo e($piutang->id); ?>" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><h5 class="modal-title" id="runningPaymentDetailModalLabel<?php echo e($piutang->id); ?>">Detail Pembayaran <?php echo e($piutang->NOFAKTUR); ?></h5><div class="small text-muted">Customer <?php echo e($piutang->CUSTOMER); ?></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3"><div class="text-muted small">Piutang</div><div class="fw-semibold"><?php echo e(number_format($piutang->PIUTANG, 0, ',', '.')); ?></div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Sudah Dibayar</div><div class="fw-semibold"><?php echo e(number_format($piutang->BAYAR, 0, ',', '.')); ?></div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Total Penyelesaian</div><div class="fw-semibold"><?php echo e(number_format($piutang->PAYMENT_SETTLED_TOTAL ?? 0, 0, ',', '.')); ?></div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Sisa</div><div class="fw-semibold <?php echo e($piutang->SALDO <= 0 ? 'text-success' : 'text-primary'); ?>"><?php echo e(number_format($piutang->SALDO, 0, ',', '.')); ?></div></div>
            </div>
            <?php if($paymentDetails->isNotEmpty()): ?>
            <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0"><thead class="table-light"><tr><th>No. Bayar</th><th>Tanggal</th><th>Metode</th><th>Akun Bayar</th><th>Referensi</th><th class="text-end">Bayar</th><th class="text-end">PPh 23</th><th class="text-end">Potongan</th><th class="text-end">Adm.</th><th class="text-end">Sisa Faktur</th><th></th></tr></thead><tbody>
                <?php $__currentLoopData = $paymentDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($header = $detail->header); ?>
                <tr><td class="fw-semibold"><?php echo e($detail->NOBAYAR); ?></td><td><?php echo e($header?->TGLBAYAR?->format('d/m/Y') ?: '-'); ?></td><td><?php echo e($header?->METODE_PEMBAYARAN ?: '-'); ?></td><td><?php echo e($header?->paymentCoa ? $header->paymentCoa->kode_akun.' - '.$header->paymentCoa->nama_akun : '-'); ?></td><td><?php echo e($header?->NO_REFERENSI ?: '-'); ?></td><td class="text-end"><?php echo e(number_format($detail->NILAI_BAYAR, 0, ',', '.')); ?></td><td class="text-end"><?php echo e(number_format($detail->PPH23, 0, ',', '.')); ?></td><td class="text-end"><?php echo e(number_format($detail->DISCOUNT, 0, ',', '.')); ?></td><td class="text-end"><?php echo e(number_format($detail->BANK_ADMIN, 0, ',', '.')); ?></td><td class="text-end"><?php echo e(number_format($detail->SISA_FAKTUR, 0, ',', '.')); ?></td><td class="text-end"><?php if($header): ?><a href="<?php echo e(route('pelunasan-piutang.show', $header->id)); ?>" class="btn btn-sm btn-outline-primary">Bukti</a><?php endif; ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody></table></div>
            <?php elseif((string) $piutang->STATUS === 'VOID'): ?>
            <div class="alert alert-secondary mb-0"><i class="bi bi-info-circle me-1"></i>Invoice ini telah di-void. Keterangan: <?php echo e($piutang->KETERANGAN ?: 'Tidak ada keterangan.'); ?></div>
            <?php else: ?>
            <div class="alert alert-info mb-0">Belum ada detail pembayaran untuk invoice ini.</div>
            <?php endif; ?>
        </div>
    </div></div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<div class="mt-3"><?php echo e($piutangs->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .receivable-running-payment-badge { cursor: pointer; font-size: .75rem; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\LARAVEL_APP\akuntansi\resources\views/piutang-berjalan/index.blade.php ENDPATH**/ ?>