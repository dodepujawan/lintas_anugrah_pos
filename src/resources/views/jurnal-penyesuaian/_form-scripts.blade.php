@push('scripts')
<script>
    const draftSaveUrl = '{{ route("jurnal-penyesuaian.draft.save") }}';
    const formId = 'formJurnalPenyesuaian';
    const draftEnabled = @json(!($isPostedEdit ?? false));

    const optionsEl = document.getElementById('accountOptionsJson');
    const accountOptions = (optionsEl ? JSON.parse(optionsEl.textContent || '[]') : []).map(function(coa) {
        const label = (typeof coa.label === 'string' && coa.label.trim()) ?
            coa.label : [coa.kode_akun, coa.nama_akun].filter(Boolean).join(' - ');

        return {
            id: coa.id,
            label: label
        };
    });

    let draftTimer = null;
    let draftSaving = false;

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
            status.textContent = validRows < 2 ? 'Min. 2 baris' : 'Selisih ' + formatCurrency(Math.abs(difference));
            status.className = 'badge bg-danger';
            submit.disabled = true;
        }
    }

    function filterAccountOptions(row) {
        const search = row.querySelector('.account-search');
        const select = row.querySelector('select[name="coa_id[]"]');
        const query = search.value.toLowerCase().trim();
        const currentValue = select.value;

        let filtered = accountOptions.filter(function(option) {
            return option.label.toLowerCase().includes(query);
        });

        if (currentValue && !filtered.some(option => option.id.toString() === currentValue.toString())) {
            const selectedOption = accountOptions.find(option => option.id.toString() === currentValue.toString());
            if (selectedOption) filtered.unshift(selectedOption);
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
        if (!draftEnabled) {
            if (callback) callback(true);
            return;
        }

        if (draftSaving) {
            if (showFeedback) {
                updateDraftStatus('<span class="spinner-border spinner-border-sm" role="status"></span> Draft sedang disimpan...', 'text-warning');
            }
            return;
        }

        if (draftTimer) {
            clearTimeout(draftTimer);
            draftTimer = null;
        }

        const form = document.getElementById(formId);
        const formData = new FormData(form);
        const token = form.querySelector('input[name="_token"]')?.value;
        const saveButton = document.getElementById('saveDraftButton');

        formData.delete('_method');
        draftSaving = true;
        if (saveButton) saveButton.disabled = true;

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
            })
            .finally(function() {
                draftSaving = false;
                if (saveButton) saveButton.disabled = false;
            });
    }

    function saveDraft(showFeedback = true, callback = null) {
        return saveDraftViaAjax(showFeedback, callback);
    }

    function scheduleDebounceDraft() {
        if (!draftEnabled) return;
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
                const accountSearch = row.querySelector('.account-search');
                const selectedOption = e.target.selectedOptions[0];
                if (selectedOption) {
                    accountSearch.value = selectedOption.textContent;
                }
                hitungTotal();
                scheduleDebounceDraft();
            }
        });

        tbody.addEventListener('click', function(e) {
            const btnRemove = e.target.closest('.btn-remove-row');
            if (btnRemove) {
                const row = btnRemove.closest('tr');
                const account = row.querySelector('.account-search')?.value.trim() || 'baris kosong';
                if (!confirm(`Hapus item jurnal penyesuaian "${account}"? Item ini akan dihapus dari Draft pada penyimpanan berikutnya.`)) return;
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

                let nextRow = row.nextElementSibling;
                if (!nextRow) {
                    tambahItem();
                    nextRow = row.nextElementSibling;
                }

                nextRow?.querySelector('.account-search')?.focus();
                saveDraftViaAjax(true);
            }
        });

        document.querySelectorAll('#tanggal, #keterangan').forEach(function(field) {
            field.addEventListener('input', scheduleDebounceDraft);
        });

        document.getElementById(formId).addEventListener('keydown', function(e) {
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
        hitungTotal();

        document.getElementById(formId).addEventListener('submit', function(event) {
            const submit = document.getElementById('submitButton');
            if (submit.disabled) {
                event.preventDefault();
                alert('Total Debit dan Kredit harus sama, serta minimal dua baris jurnal harus terisi.');
            }
        });
    });
</script>
@endpush
