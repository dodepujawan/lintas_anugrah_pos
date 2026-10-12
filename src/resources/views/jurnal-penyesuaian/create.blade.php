<x-app-layout>
    @slot('title', isset($isEdit) && $isEdit ? 'Edit Jurnal Penyesuaian' : 'Tambah Jurnal Penyesuaian')

    @push('styles')
    <style>
        /* reusing styles from saldo-awal */
        .item-table-wrapper {
            max-height: none !important;
            min-height: 0;
            height: 100%;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .account-cell {
            min-width: 320px;
        }

        .account-cell .input-group {
            margin-bottom: 0.2rem !important;
        }

        .sticky-action-bar {
            flex: 0 0 auto;
            z-index: 1020;
            background: #ffffff;
            border-top: 2px solid #dee2e6;
            padding-top: .3rem;
            padding-bottom: .3rem;
            margin-top: .25rem;
        }

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

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            appearance: textfield;
            -moz-appearance: textfield;
        }

        .locked-editor-shell { position: fixed; top: 58px; right: auto; bottom: 8px; left: 264px; width: calc(100vw - 288px) !important; max-width: calc(100vw - 288px) !important; box-sizing: border-box; z-index: 90; display: flex; flex-direction: column; overflow: hidden; }
        .locked-editor-shell > .row { flex: 0 0 auto; margin-bottom: .2rem !important; }
        .locked-editor-card { flex: 1 1 auto; min-height: 0; overflow: hidden; }
        .locked-editor-card > .card-body, .locked-editor-form { height: 100%; min-height: 0; overflow: hidden; }
        .locked-editor-form { display: flex; flex-direction: column; }
        .locked-editor-header { flex: 0 0 auto; }
        .locked-items-panel { flex: 1 1 auto; min-height: 0; display: flex; flex-direction: column; overflow: hidden; margin-bottom: .1rem !important; }
        .locked-items-panel .item-table-wrapper { flex: 1 1 auto; }
        .locked-items-panel .table-responsive { width: 100%; max-width: 100%; overflow-x: hidden; }
        .locked-items-panel table { display: block; width: 100% !important; max-width: 100%; }
        .locked-items-panel thead, .locked-items-panel tbody { display: block; width: 100%; }
        .locked-items-panel tr {
            display: grid;
            grid-template-columns: minmax(0, 32fr) minmax(0, 32fr) minmax(90px, 16fr) minmax(90px, 16fr) 44px;
            width: 100%;
            align-items: center;
        }
        .locked-items-panel th, .locked-items-panel td { display: block; width: auto !important; min-width: 0 !important; padding: .25rem .4rem; }
        .locked-items-panel .account-cell { min-width: 0 !important; }
        .locked-items-panel .input-group { width: 100%; min-width: 0; flex-wrap: nowrap; }
        .locked-items-panel .account-search,
        .locked-items-panel input.form-control { width: 100%; max-width: 100%; min-width: 0; box-sizing: border-box; }
        .locked-items-panel th:last-child,
        .locked-items-panel td:last-child { padding-left: .15rem; padding-right: .15rem; }
        .locked-items-panel thead { position: sticky; top: 0; z-index: 6; background: #f8f9fa; }
        .locked-editor-shell > .row h3 { font-size: .95rem; margin-bottom: 0 !important; }
        .locked-editor-shell > .row p { display: none; }
        .locked-editor-card > .card-body { padding: .45rem .65rem !important; }
        .locked-editor-header { margin-bottom: .25rem !important; }
        .locked-editor-header .form-label { margin-bottom: .15rem; font-size: .72rem; }
        .locked-editor-header .form-control { min-height: 30px; padding-top: .2rem; padding-bottom: .2rem; }
        .locked-items-panel > .card-header { margin-bottom: .15rem !important; min-height: 30px; }
        .locked-items-panel > .card-header h5 { font-size: .75rem !important; }
        .locked-items-panel > .card-header .btn { padding: .2rem .65rem; }
        .locked-items-panel .form-control-sm,
        .locked-items-panel .form-select-sm,
        .locked-items-panel .input-group-sm > .input-group-text { min-height: 28px; padding-top: .15rem; padding-bottom: .15rem; font-size: .75rem; }
        .sticky-action-bar .small { font-size: .7rem; }
        .sticky-action-bar .mt-1 { margin-top: 0 !important; }
        .sticky-action-bar .row > [class*="col-"] { min-width: 0; }
        .sticky-action-bar #statusBalance {
            display: inline-block;
            max-width: 100%;
            white-space: normal;
            overflow-wrap: anywhere;
            line-height: 1.15;
            text-align: center;
        }
        .sticky-action-bar #totalDebit,
        .sticky-action-bar #totalKredit { white-space: nowrap; }
        @media (min-width: 768px) and (max-width: 992px) { .locked-editor-shell { left: 16px; right: auto; width: calc(100vw - 32px) !important; max-width: calc(100vw - 32px) !important; } }
        @media (max-width: 767.98px) {
            .locked-editor-shell {
                position: static;
                display: block;
                width: 100% !important;
                max-width: 100% !important;
                padding: .65rem !important;
                margin: 0 !important;
                overflow: visible;
            }
            .locked-editor-shell > .row { margin: 0 0 .5rem !important; }
            .locked-editor-shell > .row > div { padding: 0; }
            .locked-editor-shell > .row h3 { font-size: 1rem; line-height: 1.3; }
            .locked-editor-shell > .row .text-md-end { margin-top: .5rem !important; }
            .locked-editor-shell > .row .btn { width: 100%; }
            .locked-editor-card { overflow: visible; border-radius: .65rem; }
            .locked-editor-card > .card-body { padding: .75rem !important; }
            .locked-editor-form { display: block; overflow: visible; }
            .locked-editor-header > div { width: 100%; }
            .locked-editor-header .form-control { min-height: 38px; }
            .locked-items-panel { min-height: 0; margin-top: .75rem; }
            .locked-items-panel .card-header { align-items: center !important; margin-bottom: .35rem !important; }
            .locked-items-panel .card-header h5 { font-size: .8rem !important; }
            .locked-items-panel .card-header .btn { border-radius: .4rem !important; padding: .4rem .65rem !important; }
            .item-table-wrapper { max-height: none !important; height: auto; overflow: visible; border: 0; background: transparent; }
            .locked-items-panel .table-responsive { overflow: visible; }
            .locked-items-panel table,
            .locked-items-panel tbody { display: block; width: 100% !important; }
            .locked-items-panel thead { display: none; }
            .locked-items-panel tbody tr {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 34px;
                gap: .55rem;
                width: 100%;
                margin-bottom: .7rem;
                padding: .7rem;
                border: 1px solid #dbe3ec;
                border-radius: .6rem;
                background: #fff;
                box-shadow: 0 1px 3px rgba(15,23,42,.06);
            }
            .locked-items-panel tbody td { padding: 0 !important; width: auto !important; min-width: 0 !important; border: 0; background: transparent !important; }
            .locked-items-panel tbody td:nth-child(1),
            .locked-items-panel tbody td:nth-child(2) { grid-column: 1 / -1; }
            .locked-items-panel tbody td:nth-child(3) { grid-column: 1; }
            .locked-items-panel tbody td:nth-child(4) { grid-column: 2; }
            .locked-items-panel tbody td:nth-child(5) { grid-column: 3; display: flex; align-items: end; justify-content: center; padding-bottom: .35rem !important; }
            .locked-items-panel tbody td:nth-child(2)::before,
            .locked-items-panel tbody td:nth-child(3)::before,
            .locked-items-panel tbody td:nth-child(4)::before {
                display: block;
                margin-bottom: .2rem;
                color: #64748b;
                font-size: .68rem;
                font-weight: 600;
            }
            .locked-items-panel tbody td:nth-child(2)::before { content: 'Keterangan Baris'; }
            .locked-items-panel tbody td:nth-child(3)::before { content: 'Debit'; }
            .locked-items-panel tbody td:nth-child(4)::before { content: 'Kredit'; }
            .locked-items-panel .account-cell { min-width: 0 !important; }
            .locked-items-panel .account-cell .input-group { margin-bottom: .35rem !important; }
            .locked-items-panel input,
            .locked-items-panel select { min-height: 38px; font-size: .82rem; }
            .locked-items-panel .btn-remove-row { display: flex; align-items: center; justify-content: center; width: 32px; height: 38px; border: 1px solid #fecaca; border-radius: .4rem; background: #fff5f5; }
            .sticky-action-bar { position: static; margin: .35rem 0 0 !important; padding: .65rem !important; border: 1px solid #dbe3ec; border-top: 2px solid #dbe3ec; }
            .sticky-action-bar > div { width: 100%; }
            .sticky-action-bar .row > div { width: 50%; border: 0 !important; padding: .35rem !important; text-align: left !important; }
            .sticky-action-bar .row > div:last-child { width: 100%; justify-content: flex-start !important; padding-left: .35rem !important; }
            .sticky-action-bar .col-xl-4 { display: grid; grid-template-columns: 1fr; gap: .5rem; margin-top: .55rem !important; }
            .sticky-action-bar .col-xl-4 .btn { width: 100%; margin: 0 !important; min-height: 42px; white-space: normal; }
            .autosave-status { font-size: .72rem; }
        }
    </style>
    @endpush

    <div class="container-fluid px-4 py-3 locked-editor-shell">
        <div class="row mb-1 align-items-center">
            <div class="col-md-9">
                <h3 class="fw-bold text-dark mb-1"><i class="bi bi-journal-text text-primary me-2"></i> {{ isset($isEdit) && $isEdit ? 'Edit' : 'Tambah' }} Jurnal Penyesuaian</h3>
                <p class="text-muted mb-0">Buat Jurnal Penyesuaian dengan navigasi keyboard, draft autosave, dan UX identik dengan Saldo Awal.</p>
            </div>
            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                <a href="{{ route('panduan.jurnal_penyesuaian') }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-question-circle"></i> Panduan
                </a>
                <a href="{{ route('jurnal-penyesuaian.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0 locked-editor-card">
            <div class="card-body p-4">
                <form action="{{ isset($isEdit) && $isEdit ? route('jurnal-penyesuaian.update', $latestDraft->id) : route('jurnal-penyesuaian.store') }}" method="POST" id="formJurnalPenyesuaian" class="locked-editor-form" novalidate>
                    @csrf
                    @if(isset($isEdit) && $isEdit)
                    @method('PUT')
                    @endif

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="row g-2 mb-2 locked-editor-header align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">No. Jurnal</label>
                            <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $noJurnal }}" readonly>
                            <input type="hidden" name="no_jurnal" value="{{ $noJurnal }}">
                            @if(isset($latestDraft))
                            <input type="hidden" name="draft_id" id="draft_id" value="{{ $latestDraft->id }}">
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label for="tanggal" class="form-label fw-semibold small text-secondary">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="tanggal" name="tanggal" value="{{ old('tanggal', optional($latestDraft)->tanggal?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="keterangan" class="form-label fw-semibold small text-secondary">Keterangan Umum</label>
                            <textarea class="form-control form-control-sm" id="keterangan" name="keterangan" rows="1" placeholder="Deskripsi ringkas mengenai jurnal ini">{{ old('keterangan', $latestDraft->keterangan ?? '') }}</textarea>
                        </div>
                    </div>

                    @if(!($isEdit ?? false) && isset($latestDraft) && !old('draft_id'))
                    <div class="alert alert-subtle alert-info d-flex align-items-center py-1 px-3 mb-2 border-0 row mx-0 rounded">
                        <div class="col-12 text-dark small">
                            <i class="bi bi-info-circle-fill me-2 text-info"></i> Melanjutkan draft: <strong>{{ $latestDraft->no_jurnal }}</strong> (Terakhir disimpan {{ $latestDraft->updated_at->format('d/m/Y H:i') }}).
                        </div>
                    </div>
                    @endif

                    {{-- Reuse Saldo Awal item table markup --}}
                    @include('saldo-awal._items_partial', ['coas' => $coas, 'latestDraft' => $latestDraft, 'initialRows' => 5])

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
                                    @if($isPostedEdit ?? false)
                                    <span class="text-muted small"><i class="bi bi-pencil-square"></i> Mode koreksi Posted</span>
                                    @else
                                    <span id="draftStatus" class="autosave-status text-muted small">
                                        <i class="bi bi-cloud-arrow-up text-secondary"></i> Mendeteksi perubahan...
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-5 text-end mt-3 mt-md-0">
                            @if(!($isPostedEdit ?? false))
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2 fw-medium" id="saveDraftButton" onclick="saveDraftViaAjax(true)">
                                <i class="bi bi-cloud-check"></i> Simpan Draft
                            </button>
                            @endif
                            <button type="submit" class="btn btn-success btn-sm px-4 fw-bold" id="submitButton" disabled>
                                <i class="bi bi-box-arrow-in-right"></i> {{ ($isPostedEdit ?? false) ? 'Simpan Perubahan' : 'Simpan & Posting Jurnal Penyesuaian' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="application/json" id="accountOptionsJson">
        @json($accountOptions)
    </script>

    @include('jurnal-penyesuaian._form-scripts')
    <x-coa-autocomplete-enhancement />

</x-app-layout>
