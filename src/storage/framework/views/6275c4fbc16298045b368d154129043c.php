<?php
    $printButtonClass = $buttonClass ?? 'btn-secondary';
    $printButtonSize = $buttonSize ?? '';
    $printLinkClass = $linkClass ?? '';
    $dotMatrixPrintUrl = $dotMatrixUrl.(str_contains($dotMatrixUrl, '?') ? '&' : '?').'auto_print=1';
?>
<div class="btn-group">
    <button type="button" class="btn <?php echo e($printButtonClass); ?> <?php echo e($printButtonSize); ?> dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-printer me-1"></i> Cetak
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item <?php echo e($printLinkClass); ?>" data-print-format="standard" target="_blank" href="<?php echo e($pdfUrl); ?>">PDF Standar</a></li>
        <li><a class="dropdown-item <?php echo e($printLinkClass); ?>" data-print-format="dot_matrix" target="_blank" href="<?php echo e($dotMatrixPrintUrl); ?>">Cetak TXT Dot Matrix</a></li>
        <li><a class="dropdown-item <?php echo e($printLinkClass); ?>" data-print-format="dot_matrix_download" href="<?php echo e($dotMatrixUrl); ?>">Download TXT Dot Matrix</a></li>
    </ul>
</div>
<?php /**PATH /var/www/html/src/resources/views/laporan/_print-format-dropdown.blade.php ENDPATH**/ ?>