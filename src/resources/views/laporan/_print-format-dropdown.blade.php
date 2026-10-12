@php
    $printButtonClass = $buttonClass ?? 'btn-secondary';
    $printButtonSize = $buttonSize ?? '';
    $printLinkClass = $linkClass ?? '';
    $dotMatrixPrintUrl = $dotMatrixUrl.(str_contains($dotMatrixUrl, '?') ? '&' : '?').'auto_print=1';
@endphp
<div class="btn-group">
    <button type="button" class="btn {{ $printButtonClass }} {{ $printButtonSize }} dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-printer me-1"></i> Cetak
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item {{ $printLinkClass }}" data-print-format="standard" target="_blank" href="{{ $pdfUrl }}">PDF Standar</a></li>
        <li><a class="dropdown-item {{ $printLinkClass }}" data-print-format="dot_matrix" target="_blank" href="{{ $dotMatrixPrintUrl }}">Cetak TXT Dot Matrix</a></li>
        <li><a class="dropdown-item {{ $printLinkClass }}" data-print-format="dot_matrix_download" href="{{ $dotMatrixUrl }}">Download TXT Dot Matrix</a></li>
    </ul>
</div>
