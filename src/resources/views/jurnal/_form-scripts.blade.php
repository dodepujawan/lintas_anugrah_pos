@push('scripts')
<style>
    .account-picker { position: relative; }
    .account-suggestions {
        display: none;
        position: fixed;
        z-index: 1060;
        top: auto;
        left: auto;
        right: auto;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
    }
    .account-suggestions.show { display: block; }
    .account-suggestion { text-align: left; font-size: .82rem; }
    .account-search.is-invalid + .btn-account-modal { border-color: var(--bs-form-invalid-border-color); }
    .journal-form-errors {
        flex: 0 0 auto;
        max-height: 68px;
        overflow-y: auto;
        margin-bottom: .35rem;
        padding: .35rem .55rem;
        font-size: .68rem;
        line-height: 1.2;
    }
    .journal-form-errors ul { margin: 0; padding-left: 1rem; }
</style>
<script>
    const draftSaveUrl = '{{ route("jurnal.draft.save") }}';
    const draftAutosaveEnabled = document.getElementById('formJurnal')?.dataset.draftAutosave !== 'false';


    const optionsEl = document.getElementById('accountOptionsJson');
    const accountOptions = (optionsEl ? JSON.parse(optionsEl.textContent || '[]') : []).map(function(coa) {
        const label = (typeof coa.label === 'string' && coa.label.trim()) ?
            coa.label :
            [coa.kode_akun, coa.nama_akun].filter(Boolean).join(' - ');

        return {
            id: coa.id,
            code: coa.kode_akun || '',
            name: coa.nama_akun || '',
            type: coa.tipe || '',
            label: label
        };
    });


    let draftTimer = null;
    let activeAccountRow = null;
    let coaModal = null;

    function renderModalAccounts(query) {
        const results = document.getElementById('coaModalResults');
        const empty = document.getElementById('coaModalEmpty');
        const matches = matchingAccounts(query, 100);
        results.innerHTML = matches.map(option => `<tr><td class="fw-semibold">${escapeHtml(option.code)}</td><td>${escapeHtml(option.name)}</td><td><span class="badge bg-light text-dark">${escapeHtml(option.type)}</span></td><td class="text-end"><button type="button" class="btn btn-primary btn-sm modal-account-select" data-account-id="${option.id}">Pilih</button></td></tr>`).join('');
        empty.classList.toggle('d-none', matches.length > 0);
    }

    function formatCurrency(value) {
        return Number(value).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function parseJournalMoney(value) {
        const text = String(value ?? '').trim().replace(/\s/g, '');
        if (!text) return 0;

        const normalized = text.includes(',')
            ? text.replace(/\./g, '').replace(',', '.')
            : text;
        const number = Number(normalized);

        return Number.isFinite(number) && number >= 0 ? number : 0;
    }

    function formatJournalMoneyInput(input) {
        input.value = formatCurrency(parseJournalMoney(input.value));
    }

    function normalizeJournalMoneyFormData(formData, form) {
        ['debit[]', 'kredit[]'].forEach(function(name) {
            formData.delete(name);
            form.querySelectorAll(`[name="${name}"]`).forEach(function(input) {
                formData.append(name, parseJournalMoney(input.value).toFixed(2));
            });
        });
    }

    function hitungTotal() {
        let totalDebit = 0;
        let totalKredit = 0;
        let validRows = 0;

        document.querySelectorAll('#jurnalBody tr').forEach(function(row) {
            const debitInput = row.querySelector('.debit');
            const kreditInput = row.querySelector('.kredit');
            const coaInput = row.querySelector('.coa-id');

            const debit = parseJournalMoney(debitInput?.value);
            const kredit = parseJournalMoney(kreditInput?.value);
            const hasCoa = coaInput && coaInput.value !== '';
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

        if (validRows < 2) {
            status.textContent = 'Tambahkan minimal 2 baris';
            status.className = 'badge bg-danger';
        } else if (Math.abs(difference) <= 0.01) {
            status.textContent = 'Seimbang';
            status.className = 'badge bg-success';
        } else {
            status.textContent = 'Tidak Seimbang: selisih ' + formatCurrency(Math.abs(difference));
            status.className = 'badge bg-danger';
        }
        // Tombol tetap aktif. Validasi final dijalankan di server agar pesan
        // kesalahan tampil pada form dan tombol tidak terasa tidak berfungsi.
        if (submit) submit.disabled = false;
    }

    function matchingAccounts(query, limit = 12) {
        const words = query.toLowerCase().trim().split(/\s+/).filter(Boolean);
        return accountOptions.filter(function(option) {
            const haystack = `${option.code} ${option.name} ${option.type}`.toLowerCase();
            return words.every(word => haystack.includes(word));
        }).slice(0, limit);
    }

    function selectAccount(row, option) {
        const search = row.querySelector('.account-search');
        row.querySelector('.coa-id').value = option.id;
        search.value = option.label;
        search.classList.remove('is-invalid');
        row.querySelector('.account-suggestions').classList.remove('show');
        hitungTotal();
        scheduleDebounceDraft();
    }

    function showAccountSuggestions(row) {
        const search = row.querySelector('.account-search');
        const box = row.querySelector('.account-suggestions');
        const matches = matchingAccounts(search.value);
        row.querySelector('.coa-id').value = '';
        box.innerHTML = matches.map((option, index) => `<button type="button" class="list-group-item list-group-item-action account-suggestion${index === 0 ? ' active' : ''}" data-account-id="${option.id}"><strong>${escapeHtml(option.code)}</strong> - ${escapeHtml(option.name)}<small class="d-block opacity-75">${escapeHtml(option.type)}</small></button>`).join('');
        const rect = search.getBoundingClientRect();
        const popupWidth = Math.min(Math.max(rect.width + 38, 320), window.innerWidth - 24);
        const popupLeft = Math.min(rect.left, window.innerWidth - popupWidth - 12);
        box.style.left = `${Math.max(12, popupLeft)}px`;
        box.style.top = `${rect.bottom + 2}px`;
        box.style.width = `${popupWidth}px`;
        box.classList.toggle('show', matches.length > 0);
        hitungTotal();
    }

    function updateDraftStatus(html, bootstrapColorClass) {
        const statusEl = document.getElementById('draftStatus');
        if (statusEl) {
            statusEl.className = 'autosave-status fw-bold ' + bootstrapColorClass;
            statusEl.innerHTML = html;
        }
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value || '';
        return element.innerHTML;
    }

    function saveDraftViaAjax(showFeedback = true, callback = null) {
        if (!draftAutosaveEnabled) {
            if (callback) callback(true);
            return;
        }
        const form = document.getElementById('formJurnal');
        const formData = new FormData(form);
        const token = form.querySelector('input[name="_token"]')?.value;

        // Do not submit method override on draft save, otherwise Laravel
        // may treat this as a PUT request and fail route matching.
        formData.delete('_method');
        normalizeJournalMoneyFormData(formData, form);

        if (showFeedback) {
            updateDraftStatus('<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan Draft...', 'text-warning');
        }

        fetch(draftSaveUrl, {
                method: 'POST',
                // Include cookies so the session/auth cookie and CSRF cookie are sent
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: formData
            })
            .then(function(response) {
                return response.json().catch(function() {
                    return { success: false, message: 'Respons server tidak valid.' };
                }).then(function(data) {
                    if (!response.ok) {
                        throw new Error(data.message || ('HTTP status ' + response.status));
                    }
                    return data;
                });
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
                updateDraftStatus('<i class="bi bi-x-circle"></i> Draft gagal: ' + escapeHtml(error.message), 'text-danger');
                if (callback) callback(false);
            });
    }

    function saveDraft(showFeedback = true, callback = null) {
        return saveDraftViaAjax(showFeedback, callback);
    }

    function scheduleDebounceDraft() {
        if (!draftAutosaveEnabled) return;
        if (draftTimer) {
            clearTimeout(draftTimer);
        }
        updateDraftStatus('<span class="spinner-border spinner-border-sm" role="status"></span> Menunggu...', 'text-secondary');
        draftTimer = setTimeout(function() {
            saveDraftViaAjax(false);
        }, 1500);
    }

    function tambahItem() {
        const tbody = document.getElementById('jurnalBody');
        document.querySelector('.item-table-wrapper')?.addEventListener('scroll', function() {
            document.querySelectorAll('.account-suggestions.show').forEach(box => box.classList.remove('show'));
        });
        if (!tbody) {
            return;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
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
                <input type="text" inputmode="decimal" name="debit[]" class="form-control form-control-sm text-end debit journal-money" value="0,00">
            </td>
            <td>
                <input type="text" inputmode="decimal" name="kredit[]" class="form-control form-control-sm text-end kredit journal-money" value="0,00">
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
        const tbody = document.getElementById('jurnalBody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const lastRow = rows[rows.length - 1];

        if (!lastRow) {
            tambahItem();
            return;
        }

        const lastAccountSearch = lastRow.querySelector('.account-search');
        const lastCoaInput = lastRow.querySelector('.coa-id');
        const lastDebit = parseJournalMoney(lastRow.querySelector('.debit')?.value);
        const lastKredit = parseJournalMoney(lastRow.querySelector('.kredit')?.value);
        const hasContent = lastAccountSearch?.value.trim() || lastCoaInput?.value || lastDebit > 0 || lastKredit > 0;

        if (hasContent) {
            tambahItem();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.journal-money').forEach(formatJournalMoneyInput);
        hitungTotal();

        coaModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('coaSearchModal'));
        document.getElementById('coaModalSearch').addEventListener('input', function() {
            renderModalAccounts(this.value);
        });
        document.getElementById('coaModalResults').addEventListener('click', function(e) {
            const button = e.target.closest('.modal-account-select');
            if (!button || !activeAccountRow) return;
            const option = accountOptions.find(item => item.id.toString() === button.dataset.accountId);
            if (option) {
                selectAccount(activeAccountRow, option);
                coaModal.hide();
                activeAccountRow.querySelector('input[name="deskripsi_item[]"]')?.focus();
            }
        });

        const tbody = document.getElementById('jurnalBody');

        tbody.addEventListener('input', function(e) {
            const target = e.target;
            const row = target.closest('tr');
            if (!row) return;

            if (target.classList.contains('account-search')) {
                showAccountSuggestions(row);
            } else if (target.classList.contains('debit')) {
                if (parseJournalMoney(target.value) > 0) {
                    row.querySelector('.kredit').value = '';
                }
                hitungTotal();
            } else if (target.classList.contains('kredit')) {
                if (parseJournalMoney(target.value) > 0) {
                    row.querySelector('.debit').value = '';
                }
                hitungTotal();
            }
            scheduleDebounceDraft();
        });

        tbody.addEventListener('click', function(e) {
            const suggestion = e.target.closest('.account-suggestion');
            if (suggestion) {
                const row = suggestion.closest('tr');
                const option = accountOptions.find(item => item.id.toString() === suggestion.dataset.accountId);
                if (option) selectAccount(row, option);
                return;
            }

            const modalButton = e.target.closest('.btn-account-modal');
            if (modalButton) {
                activeAccountRow = modalButton.closest('tr');
                coaModal.show();
                setTimeout(() => document.getElementById('coaModalSearch')?.focus(), 200);
                renderModalAccounts('');
                return;
            }

            const btnRemove = e.target.closest('.btn-remove-row');
            if (btnRemove) {
                const row = btnRemove.closest('tr');
                const account = row.querySelector('.account-search')?.value.trim() || 'baris kosong';
                if (!confirm(`Hapus item jurnal "${account}"? Item ini akan dihapus dari Draft pada penyimpanan berikutnya.`)) return;
                row.remove();
                hitungTotal();
                scheduleDebounceDraft();
            }
        });

        tbody.addEventListener('focusin', function(e) {
            if (!e.target.classList.contains('journal-money')) return;
            e.target.value = parseJournalMoney(e.target.value).toFixed(2);
            e.target.select();
        });

        tbody.addEventListener('focusout', function(e) {
            if (!e.target.classList.contains('journal-money')) return;
            formatJournalMoneyInput(e.target);
            hitungTotal();
        });

        const form = document.getElementById('formJurnal');

        form.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('account-search') && ['ArrowDown', 'ArrowUp', 'Escape'].includes(e.key)) {
                const suggestions = Array.from(e.target.closest('tr').querySelectorAll('.account-suggestion'));
                if (e.key === 'Escape') {
                    e.target.closest('tr').querySelector('.account-suggestions').classList.remove('show');
                    return;
                }
                if (!suggestions.length) return;
                e.preventDefault();
                const current = suggestions.findIndex(item => item.classList.contains('active'));
                const next = e.key === 'ArrowDown' ? Math.min(current + 1, suggestions.length - 1) : Math.max(current - 1, 0);
                suggestions.forEach(item => item.classList.remove('active'));
                suggestions[next].classList.add('active');
                suggestions[next].scrollIntoView({ block: 'nearest' });
                return;
            }

            if (e.key !== 'Enter') {
                return;
            }

            const target = e.target;
            const row = target.closest('#jurnalBody tr');

            if (!row) {
                if (target.tagName.toLowerCase() !== 'textarea') {
                    e.preventDefault();
                }
                return;
            }

            e.preventDefault();
            const accountSearch = row.querySelector('.account-search');
            const accountId = row.querySelector('.coa-id');
            const descInput = row.querySelector('input[name="deskripsi_item[]"]');
            const debitInput = row.querySelector('.debit');
            const kreditInput = row.querySelector('.kredit');

            if (target === accountSearch) {
                const activeSuggestion = row.querySelector('.account-suggestion.active');
                if (activeSuggestion) {
                    activeSuggestion.click();
                }
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
                const coaId = accountId ? accountId.value : null;
                const debitVal = parseJournalMoney(debitInput ? debitInput.value : 0);
                const kreditVal = parseJournalMoney(kreditInput ? kreditInput.value : 0);

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
                        const lastRow = document.querySelector('#jurnalBody tr:last-child');
                        lastRow?.querySelector('.account-search')?.focus();
                    } else {
                        alert('Gagal menyinkronkan draft dengan server. Periksa koneksi Anda!');
                    }
                });
            }
        });

        document.querySelectorAll('#tanggal, #referensi, #keterangan').forEach(function(field) {
            field.addEventListener('input', scheduleDebounceDraft);
        });

        document.querySelectorAll('#tanggal, #referensi').forEach(function(field) {
            field.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const firstRow = document.querySelector('#jurnalBody tr');
                    firstRow?.querySelector('.account-search')?.focus();
                }
            });
        });

        ensureBlankRowAtEnd();

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.account-picker')) {
                document.querySelectorAll('.account-suggestions.show').forEach(box => box.classList.remove('show'));
            }
        });

        form.addEventListener('submit', function(e) {
            const confirmationMessage = form.dataset.confirmMessage || 'Simpan jurnal ini?';
            if (!window.confirm(confirmationMessage)) {
                e.preventDefault();
                return;
            }

            form.querySelectorAll('.journal-money').forEach(function(input) {
                input.value = parseJournalMoney(input.value).toFixed(2);
            });
            const submit = document.getElementById('submitButton');
            if (submit) {
                submit.disabled = true;
                submit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Memperbarui...';
            }
        });
    });
</script>
@endpush
