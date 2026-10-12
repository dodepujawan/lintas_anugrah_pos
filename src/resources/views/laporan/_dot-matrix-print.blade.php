@if(request('print_format') === 'dot_matrix')
<style>
    @page { size: 8.5in 13in portrait !important; margin: 6mm !important; }
    html, body { font-family: "DejaVu Sans Mono", monospace !important; color: #000 !important; background: #fff !important; }
    body { font-size: 9pt !important; line-height: 1.2 !important; }
    *, *::before, *::after { color: #000 !important; background-color: transparent !important; background-image: none !important; box-shadow: none !important; text-shadow: none !important; }
    .table, table { border-collapse: collapse !important; }
    .table th, .table td, table th, table td { border-color: #000 !important; padding: 3px 4px !important; }
    .table thead th, table thead th { background: transparent !important; font-weight: 700 !important; }
    .badge { border: 0 !important; padding: 0 !important; font-weight: 700 !important; }
    .print-only-header, .piutang-print-header { color: #000 !important; }
</style>
@endif
