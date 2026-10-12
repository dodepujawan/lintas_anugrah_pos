<x-app-layout>
    @slot('title', 'Tambah Jurnal Umum')

    @push('styles')
    <style>
        .item-table-wrapper {
            min-height: 260px;
            flex: 1 1 auto;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .journal-main-card {
            flex: 1 1 auto;
            height: auto;
            min-height: 0;
            overflow: hidden;
        }

        .journal-create-page {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 56px);
            min-height: 0;
            overflow: hidden;
            padding: .75rem 1rem !important;
        }

        .journal-create-header {
            flex: 0 0 auto;
            margin-bottom: .35rem !important;
        }

        .journal-create-header h3 { font-size: 1.2rem; }
        .journal-create-header .btn { padding: .28rem .5rem; font-size: .82rem; }

        .journal-main-card > .card-body {
            padding: .75rem !important;
        }

        .journal-main-card > .card-body,
        .journal-entry-form {
            min-height: 0;
            height: 100%;
        }

        .journal-entry-form {
            display: flex;
            flex-direction: column;
        }

        .journal-header-fields {
            flex: 0 0 auto;
            position: relative;
            z-index: 10;
            background: #fff;
            margin-bottom: .35rem !important;
            --bs-gutter-y: .3rem;
        }

        .journal-header-fields .form-label {
            margin-bottom: .15rem;
            font-size: .8rem !important;
        }

        .journal-header-fields .form-control {
            min-height: 33px;
            padding: .25rem .45rem;
            font-size: .84rem;
        }

        .journal-header-fields textarea.form-control {
            height: 33px;
            min-height: 33px;
            resize: none;
        }

        .journal-header-fields #keterangan {
            font-size: 11px;
        }

        .journal-items-card {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin-bottom: .35rem !important;
        }

        .journal-items-card .item-table-wrapper {
            min-height: 0;
        }

        /* Merapikan cell akun agar input search dan select menyatu dengan rapi */
        .account-cell {
            min-width: 260px;
        }

        .account-cell .input-group {
            margin-bottom: 0.35rem !important;
        }

        .sticky-action-bar {
            flex: 0 0 auto;
            z-index: 1020;
            background: #ffffff;
            border-top: 2px solid #dee2e6;
            padding-top: .25rem;
            padding-bottom: .25rem;
            margin-top: .15rem;
            font-size: .82rem;
        }

        .sticky-action-bar .mt-1 { margin-top: .1rem !important; }
        .sticky-action-bar .btn { padding-top: .3rem; padding-bottom: .3rem; font-size: .82rem; }

        .autosave-status {
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table thead th {
            white-space: nowrap;
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 5;
            border-bottom: 2px solid #dee2e6 !important;
        }

        .journal-items-table {
            table-layout: fixed;
            min-width: 820px;
        }

        .journal-items-table .item-account-col { width: 33%; }
        .journal-items-table .item-description-col { width: 33%; }
        .journal-items-table .item-amount-col { width: 14%; }
        .journal-items-table .item-action-col { width: 6%; }

        .journal-items-table th,
        .journal-items-table td {
            padding: .3rem .35rem;
            font-size: .82rem;
        }

        .journal-items-table input.form-control,
        .journal-items-table .input-group .btn {
            min-height: 33px;
            padding-top: .22rem;
            padding-bottom: .22rem;
            font-size: .82rem;
        }

        .journal-items-table .btn-remove-row {
            width: 32px;
            height: 32px;
            font-size: .82rem;
        }

        /* Menghilangkan panah naik turun di input number browser */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            appearance: textfield;
            -moz-appearance: textfield;
        }

        @media (max-width: 767.98px) {
            .journal-main-card { height: auto; min-height: 0; }
            .journal-entry-form { display: block; }
        }

        @media (max-width: 767.98px) {
            .journal-create-page { padding: .65rem !important; }
            .journal-create-page > .row:first-child { margin: 0 0 .65rem !important; }
            .journal-create-page > .row:first-child > div { padding: 0; }
            .journal-create-page > .row:first-child h3 { font-size: 1.05rem; margin-bottom: 0 !important; }
            .journal-create-page > .row:first-child p { display: none; }
            .journal-create-page > .row:first-child .col-md-3 { display: grid; grid-template-columns: 1fr 1fr; gap: .45rem; margin-top: .55rem !important; }
            .journal-create-page > .row:first-child .btn { width: 100%; margin: 0 !important; }
            .journal-main-card > .card-body { padding: .75rem !important; }
            .journal-header-fields { margin-bottom: .65rem !important; }
            .journal-header-fields > div { width: 100%; }
            .journal-header-fields .form-control { min-height: 38px; }
            .journal-header-fields textarea { min-height: 58px; }
            .journal-items-card { min-height: 0; overflow: visible; margin-top: .35rem; }
            .journal-items-card .card-header { margin-bottom: .35rem !important; align-items: center !important; }
            .journal-items-card .card-header h5 { font-size: .8rem !important; }
            .journal-items-card .card-header .btn { border-radius: .4rem !important; padding: .4rem .65rem !important; }
            .item-table-wrapper { min-height: 0; max-height: none; overflow: visible; border: 0; }
            .item-table-wrapper .table-responsive { overflow: visible; }
            .item-table-wrapper table,
            .item-table-wrapper tbody { display: block; width: 100%; }
            .item-table-wrapper thead { display: none; }
            .item-table-wrapper tbody tr {
                display: grid;
                grid-template-columns: minmax(0,1fr) minmax(0,1fr) 34px;
                gap: .55rem;
                width: 100%;
                padding: .7rem;
                margin-bottom: .7rem;
                border: 1px solid #dbe3ec;
                border-radius: .6rem;
                background: #fff;
                box-shadow: 0 1px 3px rgba(15,23,42,.06);
            }
            .item-table-wrapper tbody td { width: auto !important; min-width: 0 !important; padding: 0 !important; border: 0; background: transparent !important; }
            .item-table-wrapper tbody td:nth-child(1),
            .item-table-wrapper tbody td:nth-child(2) { grid-column: 1 / -1; }
            .item-table-wrapper tbody td:nth-child(3) { grid-column: 1; }
            .item-table-wrapper tbody td:nth-child(4) { grid-column: 2; }
            .item-table-wrapper tbody td:nth-child(5) { grid-column: 3; display: flex; align-items: end; justify-content: center; padding-bottom: .35rem !important; }
            .item-table-wrapper tbody td:nth-child(1)::before,
            .item-table-wrapper tbody td:nth-child(2)::before,
            .item-table-wrapper tbody td:nth-child(3)::before,
            .item-table-wrapper tbody td:nth-child(4)::before { display:block; margin-bottom:.2rem; color:#64748b; font-size:.68rem; font-weight:600; }
            .item-table-wrapper tbody td:nth-child(1)::before { content:'Akun Posting'; }
            .item-table-wrapper tbody td:nth-child(2)::before { content:'Keterangan Baris'; }
            .item-table-wrapper tbody td:nth-child(3)::before { content:'Debit'; }
            .item-table-wrapper tbody td:nth-child(4)::before { content:'Kredit'; }
            .account-cell { min-width: 0 !important; }
            .account-picker { width: 100%; flex-wrap: nowrap; }
            .account-picker .account-search { min-width: 0; }
            .account-suggestions { position: fixed !important; left: .75rem !important; right: .75rem !important; width: auto !important; max-height: 45vh !important; z-index: 1080 !important; }
            .item-table-wrapper input { min-height: 38px; font-size: .82rem; }
            .btn-remove-row { display:flex!important;align-items:center;justify-content:center;width:32px;height:38px;border:1px solid #fecaca!important;border-radius:.4rem!important;background:#fff5f5!important; }
            .sticky-action-bar { position:static; margin:.35rem 0 0!important; padding:.65rem!important; border:1px solid #dbe3ec; border-top:2px solid #dbe3ec; }
            .sticky-action-bar > div { width:100%; }
            .sticky-action-bar .row > div { width:50%;border:0!important;padding:.35rem!important;text-align:left!important; }
            .sticky-action-bar .row > div:last-child { width:100%;justify-content:flex-start!important;padding-left:.35rem!important; }
            .sticky-action-bar .col-xl-4 { display:grid;grid-template-columns:1fr;gap:.5rem;margin-top:.55rem!important; }
            .sticky-action-bar .col-xl-4 .btn { width:100%;margin:0!important;min-height:42px;white-space:normal; }
            .autosave-status { font-size:.72rem; }
        }
    </style>
    @endpush

    <div class="container-fluid px-4 py-3 journal-create-page">
        <div class="row align-items-center journal-create-header">
            <div class="col-md-9">
                <h3 class="fw-bold text-dark mb-1"><i class="bi bi-journal-plus text-primary me-2"></i> Jurnal Umum Baru</h3>
            </div>
            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                <a href="{{ route('panduan.jurnal_umum') }}" class="btn btn-outline-info btn-sm me-1">
                    <i class="bi bi-book"></i> Panduan
                </a>
                <a href="{{ route('jurnal.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0 journal-main-card">
            <div class="card-body p-4">
                <form action="{{ route('jurnal.store') }}" method="POST" id="formJurnal" class="journal-entry-form" data-confirm-message="Simpan dan posting jurnal ini? Pastikan total Debit dan Kredit sudah seimbang." novalidate>
                    @csrf

                    @if ($errors->any())
                    <div class="alert alert-danger journal-form-errors">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="row g-2 journal-header-fields">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small text-secondary">No. Jurnal</label>
                            <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $noJurnal }}" readonly>
                            <input type="hidden" name="no_jurnal" value="{{ $noJurnal }}">
                        </div>
                        <div class="col-md-2">
                            <label for="tanggal" class="form-label fw-semibold small text-secondary">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label for="referensi" class="form-label fw-semibold small text-secondary">Referensi / Bukti Transaksi</label>
                            <input type="text" class="form-control form-control-sm" id="referensi" name="referensi" value="{{ old('referensi', '') }}" maxlength="50" placeholder="Contoh: INV-001, BKK-02, dll">
                        </div>
                        <div class="col-md-6">
                            <label for="keterangan" class="form-label fw-semibold small text-secondary">Keterangan Umum</label>
                            <textarea
                                class="form-control form-control-sm"
                                id="keterangan"
                                name="keterangan"
                                rows="1"
                                placeholder="Deskripsi ringkas mengenai transaksi jurnal ini umum">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>


                    <div class="card border-0 shadow-none mb-2 journal-items-card">
                        <div class="card-header bg-transparent px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                            <div>
                                <h5 class="mb-0 fw-bold text-secondary text-uppercase fs-6 tracking-wider">Item Baris Jurnal</h5>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="tambahItem()">
                                <i class="bi bi-plus-lg"></i> Tambah Baris
                            </button>
                        </div>

                        <div class="item-table-wrapper">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle journal-items-table">
                                    <colgroup>
                                        <col class="item-account-col">
                                        <col class="item-description-col">
                                        <col class="item-amount-col">
                                        <col class="item-amount-col">
                                        <col class="item-action-col">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Akun Posting</th>
                                            <th>Keterangan Baris</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Kredit</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="jurnalBody">
                                        @php
                                        $oldRows = old('coa_id');
                                        $items = $oldRows !== null ? $oldRows : [];
                                        $descriptions = old('deskripsi_item', []);
                                        $debits = old('debit', []);
                                        $kredits = old('kredit', []);
                                        @endphp

                                        @if(count($items) > 0)
                                        @foreach($items as $index => $coaId)
                                        @php $selectedAccount = $coas->firstWhere('id', $coaId); @endphp
                                        <tr>
                                            <td class="account-cell ps-3">
                                                <div class="input-group input-group-sm account-picker">
                                                    <input type="search" class="form-control account-search" placeholder="Ketik kode/nama akun..." autocomplete="off" value="{{ optional($selectedAccount)->kode_akun ? optional($selectedAccount)->kode_akun . ' - ' . optional($selectedAccount)->nama_akun : '' }}">
                                                    <button type="button" class="btn btn-outline-secondary btn-account-modal" title="Bantuan cari COA" aria-label="Buka pencarian COA"><i class="bi bi-search"></i></button>
                                                    <input type="hidden" name="coa_id[]" class="coa-id" value="{{ $coaId }}">
                                                    <div class="account-suggestions list-group shadow"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="deskripsi_item[]" class="form-control form-control-sm" value="{{ old('deskripsi_item.' . $index, $descriptions[$index] ?? '') }}" placeholder="Keterangan opsional">
                                            </td>
                                            <td>
                                                <input type="text" inputmode="decimal" name="debit[]" class="form-control form-control-sm text-end debit journal-money" value="{{ old('debit.' . $index, $debits[$index] ?? 0) }}">
                                            </td>
                                            <td>
                                                <input type="text" inputmode="decimal" name="kredit[]" class="form-control form-control-sm text-end kredit journal-money" value="{{ old('kredit.' . $index, $kredits[$index] ?? 0) }}">
                                            </td>
                                            <td class="text-center pe-3">
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-row" aria-label="Hapus">
                                                    <i class="bi bi-trash3 fs-6"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        @for($i = 0; $i < 2; $i++)
                                            <tr>
                                            <td class="account-cell ps-3">
                                                <div class="input-group input-group-sm account-picker">
                                                    <input type="search" class="form-control account-search" placeholder="Ketik kode/nama akun..." autocomplete="off">
                                                    <button type="button" class="btn btn-outline-secondary btn-account-modal" title="Bantuan cari COA" aria-label="Buka pencarian COA"><i class="bi bi-search"></i></button>
                                                    <input type="hidden" name="coa_id[]" class="coa-id" value="">
                                                    <div class="account-suggestions list-group shadow"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="deskripsi_item[]" class="form-control form-control-sm" placeholder="Keterangan opsional">
                                            </td>
                                            <td>
                                                <input type="text" inputmode="decimal" name="debit[]" class="form-control form-control-sm text-end debit journal-money" value="0">
                                            </td>
                                            <td>
                                                <input type="text" inputmode="decimal" name="kredit[]" class="form-control form-control-sm text-end kredit journal-money" value="0">
                                            </td>
                                            <td class="text-center pe-3">
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-row" aria-label="Hapus">
                                                    <i class="bi bi-trash3 fs-6"></i>
                                                </button>
                                            </td>
                                            </tr>
                                            @endfor
                                            @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center sticky-action-bar rounded px-2 shadow-sm">
                        <div class="col-xl-8 col-md-7">
                            <div class="row g-2 text-center text-sm-start">
                                <div class="col-sm-3 border-end border-light-subtle">
                                    <div class="text-muted small fs-7">Status Saldo</div>
                                    <div class="mt-1"><span id="statusBalance" class="badge bg-secondary">Menunggu</span></div>
                                </div>
                                <div class="col-sm-3 border-end border-light-subtle">
                                    <div class="text-muted small fs-7">Total Debit</div>
                                    <div class="mt-1"><span id="totalDebit" class="fw-bold text-success">0,00</span></div>
                                </div>
                                <div class="col-sm-3 border-end border-light-subtle">
                                    <div class="text-muted small fs-7">Total Kredit</div>
                                    <div class="mt-1"><span id="totalKredit" class="fw-bold text-danger">0,00</span></div>
                                </div>
                                <div class="col-sm-3 d-flex align-items-center justify-content-center justify-content-sm-start pt-2 pt-sm-0 ps-3">
                                    <span id="draftStatus" class="autosave-status text-muted small">
                                        <i class="bi bi-cloud-arrow-up text-secondary"></i> Mendeteksi perubahan...
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-5 text-end mt-3 mt-md-0">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2 fw-medium" id="saveDraftButton" onclick="saveDraftViaAjax(true)">
                                <i class="bi bi-cloud-check"></i> Simpan Draft
                            </button>
                            <button type="submit" class="btn btn-success btn-sm px-4 fw-bold" id="submitButton" disabled>
                                <i class="bi bi-box-arrow-in-right"></i> Simpan & Posting Jurnal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="application/json" id="accountOptionsJson">
        @json($coas)
    </script>
    @include('jurnal._account-modal')
    @include('jurnal._form-scripts')
</x-app-layout>
