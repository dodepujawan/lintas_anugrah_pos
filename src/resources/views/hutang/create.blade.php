@extends('layouts.app')

@section('title', 'Input Hutang Berjalan')

@push('styles')
<style>
.hutang-berjalan-form .form-control,
.hutang-berjalan-form .form-select,
.hutang-berjalan-form .form-label,
.hutang-berjalan-form .input-group .btn { font-size:1.05rem; }
.hutang-berjalan-form .form-label { margin-bottom:.4rem; }
.hutang-berjalan-form .form-text { font-size:.9rem; }
.hutang-berjalan-form .btn-primary#saveHutangButton { font-size:1.05rem; padding:.45rem 1.1rem; }
.hutang-berjalan-form .fs-4 { font-size:1.6rem; }
.hutang-berjalan-form .alert { font-size:1rem; }
</style>
@endpush

@section('content')
<div class="card shadow-sm border-0 hutang-berjalan-form">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <h2 class="h5 mb-1">Input Hutang Berjalan</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('panduan.transaksi', 'hutang-berjalan') }}" class="btn btn-sm btn-outline-info"><i class="bi bi-book me-1"></i>Panduan</a>
                <a href="{{ route('hutang.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
            </div>
        </div>
        <p class="text-muted mb-4">Faktur dan jurnal pembelian akan dibuat sekaligus.</p>
        <div class="alert alert-info">
            <strong>Jurnal otomatis:</strong> Saat disimpan, sistem akan membuat jurnal Debit akun Beban/Biaya yang dipilih dan Pajak Masukan (jika ada); Kredit Hutang Usaha sebesar total faktur.
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('hutang.store') }}" id="hutangBerjalanForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">No. Faktur</label>
                    <input type="text" name="nofaktur" class="form-control @error('nofaktur') is-invalid @enderror" value="{{ old('nofaktur') }}" required>
                    @error('nofaktur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Faktur</label>
                    <input type="date" name="tglfaktur" class="form-control @error('tglfaktur') is-invalid @enderror" value="{{ old('tglfaktur', date('Y-m-d')) }}" required>
                    @error('tglfaktur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Jatuh Tempo</label>
                    <input type="date" name="tgljt" class="form-control @error('tgljt') is-invalid @enderror" value="{{ old('tgljt') }}">
                    @error('tgljt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kode Supplier</label>
                    <div class="input-group">
                        <input type="text" name="supplier" id="supplier" class="form-control @error('supplier') is-invalid @enderror" value="{{ old('supplier') }}" list="supplier-list" autocomplete="off" required>
                        <button type="button" class="btn btn-outline-secondary" id="openSupplierModal" title="Cari supplier" aria-label="Cari supplier">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                        <datalist id="supplier-list"></datalist>
                    </div>
                    @error('supplier')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nama Supplier</label>
                    <input type="text" id="supplier_name" class="form-control bg-light" placeholder="Terisi setelah supplier dipilih" readonly>
                </div>
            </div>
            <hr class="my-4">
            <h3 class="h6 mb-3">Jurnal Otomatis</h3>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Akun Hutang Usaha *</label><select name="coa_hutang_id" class="form-select" required><option value="">Pilih akun</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected((string)old('coa_hutang_id',$payableAccountId)===(string)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Akun Beban/Biaya *</label><select name="coa_beban_id" class="form-select" required><option value="">Pilih sesuai jenis pembelian</option>@foreach($expenseAccounts as $account)<option value="{{ $account->id }}" @selected((string)old('coa_beban_id')===(string)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select><div class="form-text">Wajib dipilih sesuai penggunaan, misalnya Beban Perawatan Kendaraan untuk pembelian oli atau spare part kendaraan.</div></div>
                <div class="col-md-6"><label class="form-label">Nilai DPP/Pembelian *</label><input type="number" name="nilai_dpp" id="nilaiDpp" class="form-control" value="{{ old('nilai_dpp') }}" min="0.01" step="0.01" required></div>
                <div class="col-md-6"><label class="form-label">Akun Pajak Masukan</label><select name="coa_pajak_id" class="form-select"><option value="">Tanpa pajak</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected((string)old('coa_pajak_id',$inputTaxAccountId)===(string)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Nilai Pajak</label><input type="number" name="nilai_pajak" id="nilaiPajak" class="form-control" value="{{ old('nilai_pajak',0) }}" min="0" step="0.01"></div>
                <div class="col-md-6"><div class="p-3 bg-light rounded"><small class="text-muted">Total Hutang / Kredit</small><div class="fs-4 fw-bold" id="totalHutang">0,00</div></div></div>
            </div>
            <div class="mt-3">
                <label class="form-label">Keterangan</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Contoh: Pembelian oli kendaraan, sparepart, atau biaya operasional supplier ini">{{ old('deskripsi') }}</textarea>
                <div class="form-text">Catatan ini untuk transaksi hutang berjalan dan akan tersimpan bersama faktur. Saat disimpan, sistem membuat jurnal otomatis sesuai akun yang dipilih.</div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary" id="saveHutangButton">Simpan dan Jurnal</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="supplierSearchModal" tabindex="-1" aria-labelledby="supplierSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supplierSearchModalLabel"><i class="bi bi-search me-2"></i>Cari Master Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <label for="supplierModalSearch" class="form-label">Kode atau Nama Supplier</label>
                <input type="search" id="supplierModalSearch" class="form-control mb-3" placeholder="Ketik kode atau nama supplier..." autocomplete="off">

                <div id="supplierModalLoading" class="text-center text-muted py-4 d-none">
                    <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memuat data supplier...
                </div>
                <div id="supplierModalError" class="alert alert-danger d-none mb-3"></div>
                <div class="table-responsive" id="supplierModalTableWrapper">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th>Kode Supplier</th><th>Nama Supplier</th><th class="text-end">Aksi</th></tr>
                        </thead>
                        <tbody id="supplierModalResults"></tbody>
                    </table>
                </div>
                <div id="supplierModalEmpty" class="text-center text-muted py-4 d-none">Supplier tidak ditemukan.</div>
                <div class="form-text mt-2">Maksimal 100 supplier ditampilkan. Gunakan pencarian untuk mempersempit hasil.</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supplierInput = document.getElementById('supplier');
    const supplierList = document.getElementById('supplier-list');
    const supplierName = document.getElementById('supplier_name');
    const form = document.getElementById('hutangBerjalanForm');
    const saveButton = document.getElementById('saveHutangButton');
    const dpp = document.getElementById('nilaiDpp');
    const pajak = document.getElementById('nilaiPajak');
    const total = document.getElementById('totalHutang');
    const openModalButton = document.getElementById('openSupplierModal');
    const modalElement = document.getElementById('supplierSearchModal');
    const modalSearch = document.getElementById('supplierModalSearch');
    const modalResults = document.getElementById('supplierModalResults');
    const modalEmpty = document.getElementById('supplierModalEmpty');
    const modalLoading = document.getElementById('supplierModalLoading');
    const modalError = document.getElementById('supplierModalError');
    const modalTableWrapper = document.getElementById('supplierModalTableWrapper');
    const supplierModal = bootstrap.Modal.getOrCreateInstance(modalElement);
    let supplierTimer = null;
    let modalTimer = null;
    let requestNumber = 0;
    const suppliers = new Map();

    function normalizeSupplier(row) {
        return {
            supplier: String(row.supplier || row.value || '').trim(),
            nama: String(row.nama || '').trim()
        };
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value || '';
        return element.innerHTML;
    }

    function rememberSupplier(row) {
        const supplier = normalizeSupplier(row);
        if (supplier.supplier) suppliers.set(supplier.supplier.toUpperCase(), supplier);
        return supplier;
    }

    function showSupplierName() {
        const selected = suppliers.get(supplierInput.value.trim().toUpperCase());
        supplierName.value = selected ? selected.nama : '';
    }

    function selectSupplier(row) {
        const supplier = rememberSupplier(row);
        supplierInput.value = supplier.supplier;
        supplierName.value = supplier.nama;
        supplierInput.classList.remove('is-invalid');
        supplierModal.hide();
        supplierInput.focus();
        supplierInput.dispatchEvent(new Event('change'));
    }

    function renderModalRows(rows) {
        const normalizedRows = rows.map(rememberSupplier).filter(row => row.supplier);
        modalResults.innerHTML = normalizedRows.map(row =>
            `<tr>
                <td class="fw-semibold">${escapeHtml(row.supplier)}</td>
                <td>${escapeHtml(row.nama || 'Tanpa Nama')}</td>
                <td class="text-end"><button type="button" class="btn btn-primary btn-sm select-supplier" data-supplier="${escapeHtml(row.supplier)}">Pilih</button></td>
            </tr>`
        ).join('');
        modalEmpty.classList.toggle('d-none', normalizedRows.length > 0);
        modalTableWrapper.classList.toggle('d-none', normalizedRows.length === 0);
    }

    function loadModalSuppliers(query = '') {
        const currentRequest = ++requestNumber;
        modalLoading.classList.remove('d-none');
        modalError.classList.add('d-none');
        modalEmpty.classList.add('d-none');
        modalTableWrapper.classList.add('d-none');

        fetch("{{ route('hutang.search-supplier') }}?modal=1&q=" + encodeURIComponent(query), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) throw new Error('Data supplier gagal dimuat.');
                return response.json();
            })
            .then(rows => {
                if (currentRequest !== requestNumber) return;
                renderModalRows(rows);
            })
            .catch(error => {
                if (currentRequest !== requestNumber) return;
                modalError.textContent = error.message;
                modalError.classList.remove('d-none');
            })
            .finally(() => {
                if (currentRequest === requestNumber) modalLoading.classList.add('d-none');
            });
    }

    supplierInput.addEventListener('input', function() {
        const q = this.value.trim();
        showSupplierName();
        if (q.length < 2) {
            supplierList.innerHTML = '';
            return;
        }

        clearTimeout(supplierTimer);
        supplierTimer = setTimeout(function() {
            fetch("{{ route('hutang.search-supplier') }}?q=" + encodeURIComponent(q))
                .then(response => response.json())
                .then(function(data) {
                    supplierList.innerHTML = '';
                    data.forEach(function(item) {
                        item = rememberSupplier(item);
                        const option = document.createElement('option');
                        option.value = item.supplier;
                        option.label = item.supplier + ' - ' + item.nama;
                        supplierList.appendChild(option);
                    });
                    showSupplierName();
                });
        }, 250);
    });
    supplierInput.addEventListener('change', showSupplierName);

    openModalButton.addEventListener('click', function() {
        modalSearch.value = supplierInput.value.trim();
        supplierModal.show();
        loadModalSuppliers(modalSearch.value);
    });

    modalElement.addEventListener('shown.bs.modal', function() {
        modalSearch.focus();
        modalSearch.select();
    });

    modalSearch.addEventListener('input', function() {
        clearTimeout(modalTimer);
        modalTimer = setTimeout(() => loadModalSuppliers(modalSearch.value.trim()), 250);
    });

    modalResults.addEventListener('click', function(event) {
        const button = event.target.closest('.select-supplier');
        if (!button) return;
        const selected = suppliers.get(button.dataset.supplier.trim().toUpperCase());
        if (selected) selectSupplier(selected);
    });

    if (supplierInput.value.trim().length >= 2) supplierInput.dispatchEvent(new Event('input'));
    function calculateTotal() {
        const value = (parseFloat(dpp.value) || 0) + (parseFloat(pajak.value) || 0);
        total.textContent = new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR'}).format(value);
    }
    dpp.addEventListener('input', calculateTotal); pajak.addEventListener('input', calculateTotal); calculateTotal();
    const keyboardOrder = [
        'nofaktur', 'tglfaktur', 'tgljt', 'supplier',
        'coa_hutang_id', 'coa_beban_id', 'nilai_dpp',
        'coa_pajak_id', 'nilai_pajak', 'deskripsi'
    ];

    form.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter' || event.isComposing || event.ctrlKey || event.altKey || event.metaKey) return;

        const field = event.target;
        if (!field.matches('input, select, textarea')) return;

        if (field.name === 'deskripsi') {
            if (event.shiftKey) return;
            event.preventDefault();
            form.requestSubmit(saveButton);
            return;
        }

        event.preventDefault();
        const currentIndex = keyboardOrder.indexOf(field.name);
        const nextName = keyboardOrder[currentIndex + 1];
        const nextField = nextName ? form.elements.namedItem(nextName) : null;

        if (nextField && !nextField.disabled) {
            nextField.focus();
            if (typeof nextField.select === 'function' && nextField.type !== 'date') nextField.select();
        }
    });

    form.addEventListener('submit', function(event) {
        if (!confirm('Simpan hutang dan posting jurnal otomatis?')) {
            event.preventDefault();
        }
    });
});
</script>
@endsection
