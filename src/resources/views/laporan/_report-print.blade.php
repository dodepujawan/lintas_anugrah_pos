@push('styles')
<style>
    /* Ensure print-only header is hidden on screen */
    .print-only-header {
        display: none !important;
    }

    .screen-header {
        display: block !important;
    }
</style>
<style media="print">
    /* ===========================================================
   PRINT LAPORAN - FULL LEBAR KERTAS F4 (8.5 x 13 inch)
   =========================================================== */

    @page {
        size: 8.5in 13in portrait;
        margin: 4mm;
    }

    html,
    body {
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 12pt !important;
        line-height: 1.2 !important;
        color: #000 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hilangkan seluruh margin bootstrap pada kontainer print saja */
    .container,
    .container-fluid,
    .card,
    .card-body,
    .card-header,
    .table-responsive {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    /* Sembunyikan komponen yang tidak dicetak */
    .navbar,
    .sidebar,
    .footer,
    .btn,
    .no-print,
        .report-controls,
        .filter-card,
        .card-body>.border-bottom,
        .dropdown,
        .dropdown-menu,
        .pagination,
    .modal,
    .alert,
    .toast {
        display: none !important;
    }

    /* Judul */
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        color: #000 !important;
        margin-bottom: 8px !important;
        page-break-after: avoid;
    }

    /* ===========================
   TABEL
   =========================== */

    .table {
        width: 100% !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        table-layout: fixed !important;
        margin-top: 8px !important;
        margin-bottom: 0 !important;
    }

    .print-full,
    .table-responsive {
        overflow: visible !important;
        width: 100% !important;
    }

    .table th,
    .table td {
        border: 1px solid #000 !important;
        padding: 4px 6px !important;
        vertical-align: middle !important;
        color: #000 !important;
        overflow-wrap: break-word !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
        white-space: normal !important;
    }

    /* Header */
    .table thead th {
        background: #d9d9d9 !important;
        color: #000 !important;
        font-weight: bold !important;
        text-align: center !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Baris kategori */
    .table-primary,
    .table-secondary,
    .section-title,
    .tree-row.table-primary {
        background: #e6e6e6 !important;
        color: #000 !important;
        font-weight: bold !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ===========================
   LEBAR KOLOM 4 KOLOM
   =========================== */

    .table th:nth-child(1),
    .table td:nth-child(1) {
        width: 14% !important;
    }

    .table th:nth-child(2),
    .table td:nth-child(2) {
        width: 40% !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .table th:nth-child(3),
    .table td:nth-child(3) {
        width: 14% !important;
        text-align: center !important;
        padding-right: 6px !important;
    }

    .table th:nth-child(4),
    .table td:nth-child(4) {
        width: 16% !important;
        text-align: right !important;
        padding-right: 10px !important;
    }

    .table th:nth-child(5),
    .table td:nth-child(5) {
        width: 16% !important;
        text-align: right !important;
        padding-right: 10px !important;
    }

    .table th,
    .table td {
        line-height: 1.1 !important;
    }

    /* Jika hanya 3 kolom */
    .table.table-3col th:nth-child(1),
    .table.table-3col td:nth-child(1) {
        width: 15% !important;
        white-space: nowrap !important;
    }

    .table.table-3col th:nth-child(2),
    .table.table-3col td:nth-child(2) {
        width: 55% !important;
    }

    .table.table-3col th:nth-child(3),
    .table.table-3col td:nth-child(3) {
        width: 30% !important;
        text-align: right !important;
    }

    /* Nominal */
    .amount,
    .text-end,
    .text-right,
    .table td:last-child {
        text-align: right !important;
        white-space: nowrap !important;
        font-variant-numeric: tabular-nums !important;
        padding-right: 6px !important;
    }

    /* Make table body and header standard size */
    .table th,
    .table td {
        font-size: 12pt !important;
    }

    /* Compact padding for cells to fit more rows */
    .table .table-secondary td,
    tfoot th,
    .table .section-title {
        font-size: 9pt !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
    }

    /* Print header: title on first line, period on second line */
    .print-only-header .page-title {
        display: block;
        font-size: 14pt !important;
        font-weight: 700 !important;
        margin: 0 0 2px 0 !important;
    }

    .print-only-header .period-line small {
        display: block;
        font-size: 11pt !important;
        color: #333 !important;
        margin: 0 0 4px 0 !important;
    }

    /* Table header: normal 12pt and single-line height */
    .table thead th {
        font-size: 12pt !important;
        font-weight: 400 !important;
        padding-top: 4px !important;
        padding-bottom: 4px !important;
        white-space: nowrap !important;
    }

    /* Make footer/total row occupy a single compact row */
    .table tfoot tr th,
    .table tfoot tr td,
    .table .table-secondary td {
        white-space: nowrap !important;
        font-size: 12pt !important;
        padding-top: 3px !important;
        padding-bottom: 3px !important;
    }

    /* Tree */
    .tree-indent-1 {
        padding-left: 20px !important;
    }

    .tree-indent-2 {
        padding-left: 40px !important;
    }

    .tree-indent-3 {
        padding-left: 60px !important;
    }

    .tree-indent-4 {
        padding-left: 80px !important;
    }

    .tree-indent-5 {
        padding-left: 100px !important;
    }

    /* Jangan pecah baris */
    tr,
    td,
    th {
        page-break-inside: avoid !important;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    /* Footer browser */
    @media print {

        html,
        body {
            zoom: 100%;
        }

        a {
            text-decoration: none !important;
            color: #000 !important;
        }

        .page-break {
            page-break-before: always;
        }
    }

    /* Print-only header: hide screen header and show minimal header */
    .screen-header {
        display: block !important;
    }

    .print-only-header {
        display: none !important;
        text-align: center;
        margin-bottom: 8px !important;
    }

    @media print {
        .screen-header {
            display: none !important;
        }

        .print-only-header {
            display: block !important;
        }

        /* Hide currency prefix '' in print */
        .currency {
            display: none !important;
        }

        /* Ensure all collapsed tree rows are visible in print */
        .tree-row {
            display: table-row !important;
        }

        .tree-row>td,
        .tree-row>th {
            display: table-cell !important;
        }

        /* Hide tree toggle controls in print */
        .tree-toggle,
        .tree-toggle-icon {
            display: none !important;
        }
    }
</style>
@endpush
