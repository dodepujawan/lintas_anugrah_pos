@extends('layouts.app')
@php($isEdit = isset($hutang))
@section('title', $isEdit ? 'Edit Saldo Awal Hutang' : 'Saldo Awal Hutang')
@section('content')
<div class="row justify-content-center"><div class="col-lg-9">
    <div class="d-flex justify-content-between mb-4"><div><h2 class="page-title mb-1">{{ $isEdit ? 'Edit Saldo Awal Hutang' : 'Input Saldo Awal Hutang' }}</h2><p class="text-muted mb-0">Mencatat rincian faktur lama tanpa membuat jurnal baru.</p></div><div class="d-flex gap-2"><a href="{{ route('panduan.transaksi', 'saldo-awal-hutang') }}" class="btn btn-outline-info"><i class="bi bi-book me-1"></i>Panduan</a><a href="{{ route('saldo-awal-hutang.index') }}" class="btn btn-outline-secondary">Kembali</a></div></div>
    <div class="alert alert-warning"><strong>Perhatian:</strong> gunakan hanya jika saldo akun Hutang Usaha sudah dicatat melalui Saldo Awal Akun. Total rincian harus direkonsiliasi agar tidak terjadi pencatatan ganda.</div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="card"><div class="card-body"><form method="POST" action="{{ $isEdit ? route('saldo-awal-hutang.update', $hutang) : route('saldo-awal-hutang.store') }}" id="saldoAwalHutangForm">@csrf @if($isEdit) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Nomor Faktur *</label><input name="nofaktur" class="form-control" value="{{ old('nofaktur', $isEdit ? $hutang->NOFAKTUR : '') }}" required></div>
            <div class="col-md-4"><label class="form-label">Tanggal Faktur *</label><input type="date" name="tglfaktur" class="form-control" value="{{ old('tglfaktur', $isEdit ? $hutang->TGLFAKTUR->toDateString() : now()->format('Y-m-d')) }}" required></div>
            <div class="col-md-4"><label class="form-label">Jatuh Tempo</label><input type="date" name="tgljt" class="form-control" value="{{ old('tgljt', $isEdit ? $hutang->TGLJT?->toDateString() : '') }}"></div>
            <div class="col-md-3">
                <label class="form-label">Kode Supplier *</label>
                <div class="input-group">
                    <input name="supplier" id="supplier" list="supplier-list" class="form-control @error('supplier') is-invalid @enderror" value="{{ old('supplier', $isEdit ? $hutang->SUPPLIER : '') }}" autocomplete="off" required>
                    <button type="button" class="btn btn-outline-secondary" id="openSupplierModal" title="Cari supplier" aria-label="Cari supplier">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <datalist id="supplier-list"></datalist>
                </div>
                @error('supplier')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3"><label class="form-label">Nama Supplier</label><input id="supplier_name" class="form-control bg-light" placeholder="Terisi setelah supplier dipilih" readonly></div>
            <div class="col-md-6"><label class="form-label">Saldo Hutang *</label><input type="number" name="nominal" class="form-control" min="0.01" step="0.01" value="{{ old('nominal', $isEdit ? $hutang->HUTANG : '') }}" required></div>
            <div class="col-12">
                <label class="form-label">Keterangan</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="2" placeholder="Contoh: Sisa hutang dari sistem lama per tanggal cutover">{{ old('deskripsi', $isEdit ? $hutang->KETERANGAN : '') }}</textarea>
                <div class="form-text">Catatan ini hanya untuk asal saldo awal. Simpan saldo awal hutang tidak membuat jurnal baru.</div>
            </div>
        </div><div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('saldo-awal-hutang.index') }}" class="btn btn-outline-secondary">Batal</a><button type="submit" class="btn btn-primary" id="saveSaldoAwalButton">{{ $isEdit ? 'Update Tanpa Jurnal' : 'Simpan Tanpa Jurnal' }}</button></div>
    </form></div></div>
</div></div>

<div class="modal fade" id="supplierSearchModal" tabindex="-1" aria-labelledby="supplierSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supplierSearchModalLabel"><i class="bi bi-search me-2"></i>Cari Supplier</h5>
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
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('saldoAwalHutangForm');
    const saveButton = document.getElementById('saveSaldoAwalButton');
    const input = document.getElementById('supplier');
    const list = document.getElementById('supplier-list');
    const nameInput = document.getElementById('supplier_name');
    const openModalButton = document.getElementById('openSupplierModal');
    const modalElement = document.getElementById('supplierSearchModal');
    const modalSearch = document.getElementById('supplierModalSearch');
    const modalResults = document.getElementById('supplierModalResults');
    const modalEmpty = document.getElementById('supplierModalEmpty');
    const modalLoading = document.getElementById('supplierModalLoading');
    const modalError = document.getElementById('supplierModalError');
    const modalTableWrapper = document.getElementById('supplierModalTableWrapper');
    const supplierModal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const suppliers = new Map();
    let timer;
    let modalTimer;
    let requestNumber = 0;

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
        const selected = suppliers.get(input.value.trim().toUpperCase());
        nameInput.value = selected ? selected.nama : '';
    }

    function selectSupplier(row) {
        const supplier = rememberSupplier(row);
        input.value = supplier.supplier;
        nameInput.value = supplier.nama;
        input.classList.remove('is-invalid');
        supplierModal.hide();
        input.focus();
        input.dispatchEvent(new Event('change'));
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

    input.addEventListener('input', function() {
        clearTimeout(timer); showSupplierName();
        const q = input.value.trim();
        if (q.length < 2) { list.innerHTML = ''; return; }
        timer = setTimeout(() => fetch("{{ route('hutang.search-supplier') }}?q=" + encodeURIComponent(q))
            .then(r => r.json()).then(rows => {
                list.innerHTML = '';
                rows.forEach(row => {
                    row = rememberSupplier(row);
                    const option = document.createElement('option');
                    option.value = row.supplier; option.label = row.supplier + ' - ' + row.nama;
                    list.appendChild(option);
                });
                showSupplierName();
            }), 250);
    });
    input.addEventListener('change', showSupplierName);

    openModalButton.addEventListener('click', function() {
        modalSearch.value = input.value.trim();
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

    form.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter' || event.isComposing) return;

        const field = event.target;
        if (!field.matches('input, select, textarea')) return;

        if (field.name === 'deskripsi') {
            if (event.shiftKey) return;
            event.preventDefault();
            form.requestSubmit(saveButton);
            return;
        }

        event.preventDefault();
        const fields = Array.from(form.querySelectorAll('input:not([type="hidden"]):not([readonly]), select:not([disabled]), textarea:not([readonly])'))
            .filter(item => !item.disabled && item.offsetParent !== null);
        const currentIndex = fields.indexOf(field);
        const nextField = fields[currentIndex + 1];

        if (nextField) {
            nextField.focus();
            if (typeof nextField.select === 'function' && nextField.type !== 'date') nextField.select();
        }
    });

    form.addEventListener('submit', function(event) {
        if (!confirm('{{ $isEdit ? 'Perbarui saldo awal hutang tanpa jurnal?' : 'Simpan saldo awal hutang tanpa jurnal?' }}')) {
            event.preventDefault();
        }
    });

    if (input.value.trim().length >= 2) input.dispatchEvent(new Event('input'));
});
</script>
@endpush
