<x-app-layout>
    @slot('title', isset($isEdit) && $isEdit ? 'Edit Saldo Awal Akun' : 'Tambah Saldo Awal Akun')

    @push('styles')
    <style>
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
            margin-bottom: 0.35rem !important;
        }

        .sticky-action-bar {
            flex: 0 0 auto;
            z-index: 1020;
            background: #ffffff;
            border-top: 2px solid #dee2e6;
            padding-top: 1.25rem;
            padding-bottom: 1.25rem;
            margin-top: .5rem;
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

        .locked-editor-shell { position: fixed; top: 72px; right: auto; bottom: 16px; left: 264px; width: calc(100vw - 288px) !important; max-width: calc(100vw - 288px) !important; box-sizing: border-box; z-index: 90; display: flex; flex-direction: column; overflow: hidden; }
        .locked-editor-shell > .row { flex: 0 0 auto; margin-bottom: .5rem !important; }
        .locked-editor-card { flex: 1 1 auto; min-height: 0; overflow: hidden; }
        .locked-editor-card > .card-body, .locked-editor-form { height: 100%; min-height: 0; overflow: hidden; }
        .locked-editor-form { display: flex; flex-direction: column; }
        .locked-editor-header { flex: 0 0 auto; }
        .locked-items-panel { flex: 1 1 auto; min-height: 0; display: flex; flex-direction: column; overflow: hidden; margin-bottom: .25rem !important; }
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
        .locked-items-panel th, .locked-items-panel td { display: block; width: auto !important; min-width: 0 !important; padding-left: .4rem; padding-right: .4rem; }
        .locked-items-panel .account-cell { min-width: 0 !important; }
        .locked-items-panel .input-group { width: 100%; min-width: 0; flex-wrap: nowrap; }
        .locked-items-panel .account-search,
        .locked-items-panel input.form-control { width: 100%; max-width: 100%; min-width: 0; box-sizing: border-box; }
        .locked-items-panel th:last-child,
        .locked-items-panel td:last-child { padding-left: .15rem; padding-right: .15rem; }
        .locked-items-panel thead { position: sticky; top: 0; z-index: 6; background: #f8f9fa; }
        @media (min-width: 768px) and (max-width: 992px) { .locked-editor-shell { left: 16px; right: auto; width: calc(100vw - 32px) !important; max-width: calc(100vw - 32px) !important; } }
        @media (max-width: 767.98px) { .locked-editor-shell { position: static; display: block; } .locked-editor-card { overflow: visible; } .locked-editor-form { display: block; overflow: visible; } .locked-items-panel { min-height: 420px; } .item-table-wrapper { max-height: 520px !important; } }
    </style>
    @endpush

    <div class="container-fluid px-4 py-3 locked-editor-shell">
        <div class="row mb-4 align-items-center">
            <div class="col-md-9">
                <h3 class="fw-bold text-dark mb-1"><i class="bi bi-wallet2 text-primary me-2"></i> {{ isset($isEdit) && $isEdit ? 'Edit' : 'Tambah' }} Saldo Awal Akun</h3>
                <p class="text-muted mb-0">Buat saldo awal akun dengan mekanisme draft, benar seimbang, dan tampilan tabel yang responsif.</p>
            </div>
            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                <a href="{{ route('panduan.transaksi', 'saldo-awal-akun') }}" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-book"></i> Panduan
                </a>
                <a href="{{ route('saldo-awal.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0 locked-editor-card">
            <div class="card-body p-4">
                <form action="{{ isset($isEdit) && $isEdit ? route('saldo-awal.update', $latestDraft->id) : route('saldo-awal.store') }}" method="POST" id="formSaldoAwal" class="locked-editor-form" novalidate>
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

                    <div class="row g-3 mb-2 locked-editor-header">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-secondary">No. Saldo Awal</label>
                            <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $noSaldoAwal }}" readonly>
                            <input type="hidden" name="no_jurnal" value="{{ $noSaldoAwal }}">
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
                            <textarea class="form-control form-control-sm" id="keterangan" name="keterangan" rows="2" placeholder="Deskripsi ringkas mengenai saldo awal ini">{{ old('keterangan', $latestDraft->keterangan ?? '') }}</textarea>
                        </div>
                    </div>

                    @if(isset($latestDraft) && !old('draft_id'))
                    <div class="alert alert-subtle alert-info d-flex align-items-center py-2 px-3 mb-4 border-0 row mx-0 rounded">
                        <div class="col-12 text-dark small">
                            <i class="bi bi-info-circle-fill me-2 text-info"></i> Melanjutkan draft: <strong>{{ $latestDraft->no_jurnal }}</strong> (Terakhir disimpan {{ $latestDraft->updated_at->format('d/m/Y H:i') }}).
                        </div>
                    </div>
                    @endif

                    <div class="card border-0 shadow-none mb-3 locked-items-panel">
                        <div class="card-header bg-transparent px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                            <div>
                                <h5 class="mb-0 fw-bold text-secondary text-uppercase fs-6 tracking-wider">Item Saldo Awal</h5>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="tambahItem()">
                                <i class="bi bi-plus-lg"></i> Tambah Baris
                            </button>
                        </div>

                        <div class="item-table-wrapper" style="max-height:60vh;">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th class="ps-3" style="width: 35%;">Akun Posting</th>
                                            <th style="width: 35%;">Keterangan Baris</th>
                                            <th class="text-end" style="width: 13%;">Debit</th>
                                            <th class="text-end" style="width: 13%;">Kredit</th>
                                            <th class="text-center" style="width: 4%;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="saldoAwalBody">
                                        @php
                                        $oldRows = old('coa_id');
                                        $items = $oldRows !== null ? $oldRows : ($latestDraft?->items?->pluck('coa_id')->toArray() ?? []);
                                        $descriptions = old('deskripsi_item', $latestDraft?->items?->pluck('deskripsi')->toArray() ?? []);
                                        $debits = old('debit', $latestDraft?->items?->pluck('debit')->toArray() ?? []);
                                        $kredits = old('kredit', $latestDraft?->items?->pluck('kredit')->toArray() ?? []);

                                        $accountOptions = [];
                                        foreach ($coas as $coa) {
                                        $accountOptions[] = ['id' => $coa->id, 'label' => $coa->kode_akun . ' - ' . $coa->nama_akun];
                                        }
                                        @endphp

                                        @if(count($items) > 0)
                                        @foreach($items as $index => $coaId)
                                        @php $selectedAccount = $coas->firstWhere('id', $coaId); @endphp
                                        <tr>
                                            <td class="account-cell ps-3">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                                    <input type="search" class="form-control form-control-sm account-search border-start-0" placeholder="Ketik kode/nama akun..." value="{{ optional($selectedAccount)->kode_akun ? optional($selectedAccount)->kode_akun . ' - ' . optional($selectedAccount)->nama_akun : '' }}">
                                                </div>
                                                <select name="coa_id[]" class="form-select form-select-sm" required>
                                                    <option value="">-- Pilih Akun --</option>
                                                    @foreach($coas as $coa)
                                                    <option value="{{ $coa->id }}" {{ $coaId == $coa->id ? 'selected' : '' }}>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="deskripsi_item[]" class="form-control form-control-sm" value="{{ old('deskripsi_item.' . $index, $descriptions[$index] ?? '') }}" placeholder="Keterangan opsional">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="debit[]" class="form-control form-control-sm text-end debit" value="{{ old('debit.' . $index, $debits[$index] ?? 0) }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="kredit[]" class="form-control form-control-sm text-end kredit" value="{{ old('kredit.' . $index, $kredits[$index] ?? 0) }}">
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
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                                    <input type="search" class="form-control form-control-sm account-search border-start-0" placeholder="Ketik kode/nama akun...">
                                                </div>
                                                <select name="coa_id[]" class="form-select form-select-sm" required>
                                                    <option value="">-- Pilih Akun --</option>
                                                    @foreach($coas as $coa)
                                                    <option value="{{ $coa->id }}">{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="deskripsi_item[]" class="form-control form-control-sm" placeholder="Keterangan opsional">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="debit[]" class="form-control form-control-sm text-end debit" value="0">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="kredit[]" class="form-control form-control-sm text-end kredit" value="0">
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
                                <i class="bi bi-box-arrow-in-right"></i> Simpan & Posting Saldo Awal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="application/json" id="accountOptionsJson">
        @json($accountOptions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
    </script>

    @push('scripts')
    <script>
        const draftSaveUrl = '{{ route("saldo-awal.draft.save") }}';
        const formElementId = 'formSaldoAwal';

        const accountOptions = JSON.parse(
            document.getElementById('accountOptionsJson').textContent
        );

        let draftTimer = null;

        function formatCurrency(value) {
            return Number(value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function buildAccountOptions(selectedId = '') {
            return accountOptions.map(function(option) {
                return `<option value="${option.id}"${option.id.toString() === selectedId.toString() ? ' selected' : ''}>${option.label}</option>`;
            }).join('');
        }

        function hitungTotal() {
            let totalDebit = 0;
            let totalKredit = 0;
            let validRows = 0;

            document.querySelectorAll('#saldoAwalBody tr').forEach(function(row) {
                const debitInput = row.querySelector('.debit');
                const kreditInput = row.querySelector('.kredit');
                const coaSelect = row.querySelector('select[name="coa_id[]"]');

                const debit = parseFloat(debitInput?.value) || 0;
                const kredit = parseFloat(kreditInput?.value) || 0;
                const hasCoa = coaSelect && coaSelect.value !== '';
                const hasValue = debit > 0 || kredit > 0;

                totalDebit += debit;
                totalKredit += kredit;

                if (hasCoa && hasValue) {
                    validRows++;
                }
            });

            const difference = totalDebit - totalKredit;
            const status = document.getElementById('statusBalance');
            const submit = document.getElementById('submitButton');
            const totalDebitEl = document.getElementById('totalDebit');
            const totalKreditEl = document.getElementById('totalKredit');

            if (totalDebitEl) {
                totalDebitEl.textContent = formatCurrency(totalDebit);
            }
            if (totalKreditEl) {
                totalKreditEl.textContent = formatCurrency(totalKredit);
            }

            if (Math.abs(difference) <= 0.01 && validRows >= 2) {
                status.textContent = 'Seimbang';
                status.className = 'badge bg-success';
                submit.disabled = false;
            } else {
                status.textContent = validRows < 2 ? 'Tambahkan minimal 2 baris' : 'Selisih ' + formatCurrency(Math.abs(difference));
                status.className = 'badge bg-danger';
                submit.disabled = true;
            }
        }

        function filterAccountOptions(row) {
            const search = row.querySelector('.account-search');
            const select = row.querySelector('select[name="coa_id[]"]');
            const query = (search?.value || '').toLowerCase().trim();
            const currentValue = select?.value || '';

            let filtered = accountOptions.filter(function(option) {
                return option.label.toLowerCase().includes(query);
            });

            if ((filtered.length === 0 || (currentValue && !filtered.some(option => option.id.toString() === currentValue.toString()))) && currentValue) {
                const selectedOption = accountOptions.find(option => option.id.toString() === currentValue.toString());
                if (selectedOption) {
                    filtered = [selectedOption];
                } else {
                    filtered = accountOptions.slice();
                }
            }

            select.innerHTML = `<option value="">-- Pilih Akun --</option>` + filtered.map(function(option) {
                return `<option value="${option.id}"${option.id.toString() === currentValue.toString() ? ' selected' : ''}>${option.label}</option>`;
            }).join('');
        }

        function updateDraftStatus(html, bootstrapColorClass) {
            const statusEl = document.getElementById('draftStatus');
            if (statusEl) {
                statusEl.className = 'autosave-status fw-bold ' + bootstrapColorClass;
                statusEl.innerHTML = html;
            }
        }

        function saveDraftViaAjax(showFeedback = true, callback = null) {
            const form = document.getElementById(formElementId);
            const formData = new window.FormData(form);
            const token = form.querySelector('input[name="_token"]')?.value;

            formData.delete('_method');

            if (showFeedback) {
                updateDraftStatus('<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan Draft...', 'text-warning');
            }

            fetch(draftSaveUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    },
                    body: formData
                })
                .then(function(response) {
                    if (!response.ok) {
                        return response.text().then(function(text) {
                            const msg = 'HTTP status ' + response.status + (text ? (': ' + text) : '');
                            throw new Error(msg);
                        });
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        let draftIdInput = document.getElementById('draft_id');
                        if (!draftIdInput) {
                            draftIdInput = document.createElement('input');
                            draftIdInput.type = 'hidden';
                            draftIdInput.name = 'draft_id';
                            draftIdInput.id = 'draft_id';
                            form.appendChild(draftIdInput);
                        }
                        draftIdInput.value = data.draft_id;
                        updateDraftStatus('<i class="bi bi-check2-circle"></i> Draft Tersimpan', 'text-success');
                        if (callback) callback(true);
                    } else {
                        updateDraftStatus('<i class="bi bi-x-circle"></i> Draft Gagal Disimpan', 'text-danger');
                        if (callback) callback(false);
                    }
                })
                .catch(function(error) {
                    console.error('AutoSave Error:', error);
                    updateDraftStatus('<i class="bi bi-wifi-off"></i> Draft Gagal Disimpan', 'text-danger');
                    if (callback) callback(false);
                });
        }

        function saveDraft(showFeedback = true, callback = null) {
            return saveDraftViaAjax(showFeedback, callback);
        }

        function scheduleDebounceDraft() {
            if (draftTimer) {
                clearTimeout(draftTimer);
            }
            updateDraftStatus('<span class="spinner-border spinner-border-sm" role="status"></span> Menunggu...', 'text-secondary');
            draftTimer = setTimeout(function() {
                saveDraftViaAjax(false);
            }, 1500);
        }

        function tambahItem() {
            const tbody = document.getElementById('saldoAwalBody');
            if (!tbody) {
                return;
            }

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="account-cell ps-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="search" class="form-control form-control-sm account-search border-start-0" placeholder="Ketik kode/nama akun...">
                    </div>
                    <select name="coa_id[]" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Akun --</option>
                        ${buildAccountOptions()}
                    </select>
                </td>
                <td>
                    <input type="text" name="deskripsi_item[]" class="form-control form-control-sm" placeholder="Keterangan opsional">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="debit[]" class="form-control form-control-sm text-end debit" value="0">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="kredit[]" class="form-control form-control-sm text-end kredit" value="0">
                </td>
                <td class="text-center pe-3">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-row" aria-label="Hapus">
                        <i class="bi bi-trash3 fs-6"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
            row.querySelector('.account-search')?.focus();
            hitungTotal();
        }

        function ensureBlankRowAtEnd() {
            const tbody = document.getElementById('saldoAwalBody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const lastRow = rows[rows.length - 1];

            if (!lastRow) {
                tambahItem();
                return;
            }

            const lastAccountSearch = lastRow.querySelector('.account-search');
            const lastCoaSelect = lastRow.querySelector('select[name="coa_id[]"]');
            const lastDebit = parseFloat(lastRow.querySelector('.debit')?.value) || 0;
            const lastKredit = parseFloat(lastRow.querySelector('.kredit')?.value) || 0;
            const hasContent = lastAccountSearch?.value.trim() || lastCoaSelect?.value || lastDebit > 0 || lastKredit > 0;

            if (hasContent) {
                tambahItem();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            hitungTotal();

            const tbody = document.getElementById('saldoAwalBody');

            tbody.addEventListener('input', function(e) {
                const target = e.target;
                const row = target.closest('tr');
                if (!row) return;

                if (target.classList.contains('account-search')) {
                    filterAccountOptions(row);
                } else if (target.classList.contains('debit')) {
                    if (parseFloat(target.value) > 0) {
                        row.querySelector('.kredit').value = '';
                    }
                    hitungTotal();
                } else if (target.classList.contains('kredit')) {
                    if (parseFloat(target.value) > 0) {
                        row.querySelector('.debit').value = '';
                    }
                    hitungTotal();
                }
                scheduleDebounceDraft();
            });

            tbody.addEventListener('change', function(e) {
                if (e.target.name === 'coa_id[]') {
                    const row = e.target.closest('tr');
                    const select = e.target;
                    const selectedId = select.value;
                    const selOpt = accountOptions.find(o => o.id.toString() === (selectedId || '').toString());
                    const searchInput = row.querySelector('.account-search');
                    if (selOpt && searchInput) {
                        searchInput.value = selOpt.label;
                    }
                    filterAccountOptions(row);
                    scheduleDebounceDraft();
                }
            });

            tbody.addEventListener('click', function(e) {
                const btnRemove = e.target.closest('.btn-remove-row');
                if (btnRemove) {
                    const row = btnRemove.closest('tr');
                    const account = row.querySelector('.account-search')?.value.trim() || 'baris kosong';
                    if (!confirm(`Hapus item saldo awal "${account}"? Item ini akan dihapus dari Draft pada penyimpanan berikutnya.`)) return;
                    row.remove();
                    hitungTotal();
                    scheduleDebounceDraft();
                }
            });

            tbody.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter') {
                    return;
                }

                const target = e.target;
                const row = target.closest('tr');
                if (!row) {
                    return;
                }

                e.preventDefault();
                const accountSearch = row.querySelector('.account-search');
                const accountSelect = row.querySelector('select[name="coa_id[]"]');
                const descInput = row.querySelector('input[name="deskripsi_item[]"]');
                const debitInput = row.querySelector('.debit');
                const kreditInput = row.querySelector('.kredit');

                if (target === accountSearch || target === accountSelect) {
                    if (descInput) {
                        descInput.focus();
                    }
                } else if (target === descInput) {
                    if (debitInput) {
                        debitInput.focus();
                        debitInput.select();
                    }
                } else if (target === debitInput) {
                    if (kreditInput) {
                        kreditInput.focus();
                        kreditInput.select();
                    }
                } else if (target === kreditInput) {
                    const coaId = accountSelect ? accountSelect.value : null;
                    const debitVal = parseFloat(debitInput ? debitInput.value : 0) || 0;
                    const kreditVal = parseFloat(kreditInput ? kreditInput.value : 0) || 0;

                    if (!coaId) {
                        alert('Validasi Gagal: Akun posting belum dipilih pada baris ini!');
                        if (accountSearch) {
                            accountSearch.focus();
                        }
                        return;
                    }

                    if (debitVal === 0 && kreditVal === 0) {
                        alert('Validasi Gagal: Nilai Debit atau Kredit harus diisi!');
                        if (debitInput) {
                            debitInput.focus();
                        }
                        return;
                    }

                    saveDraftViaAjax(true, function(success) {
                        if (success) {
                            ensureBlankRowAtEnd();
                            const lastRow = document.querySelector('#saldoAwalBody tr:last-child');
                            lastRow?.querySelector('.account-search')?.focus();
                        } else {
                            alert('Gagal menyinkronkan draft dengan server. Periksa koneksi Anda!');
                        }
                    });
                }
            });

            document.querySelectorAll('#tanggal, #keterangan').forEach(function(field) {
                field.addEventListener('input', scheduleDebounceDraft);
            });

            const form = document.getElementById(formElementId);
            form.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName.toLowerCase() !== 'textarea') {
                    e.preventDefault();
                }
            });

            document.querySelectorAll('#tanggal').forEach(function(field) {
                field.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const firstRow = document.querySelector('#saldoAwalBody tr');
                        firstRow?.querySelector('.account-search')?.focus();
                    }
                });
            });

            ensureBlankRowAtEnd();

            document.getElementById(formElementId).addEventListener('submit', function(event) {
                const submit = document.getElementById('submitButton');
                if (submit.disabled) {
                    event.preventDefault();
                    alert('Total Debit dan Kredit harus sama, serta minimal dua baris jurnal harus terisi.');
                }
            });
        });
    </script>
    @endpush
    <x-coa-autocomplete-enhancement />
</x-app-layout>
