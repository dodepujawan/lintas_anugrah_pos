@extends('layouts.app')

@php($isEdit = isset($piutang))
@section('title', $isEdit ? 'Edit Saldo Awal Piutang' : 'Tambah Saldo Awal Piutang')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-4"><h2 class="h5 mb-0">{{ $isEdit ? 'Edit Saldo Awal Piutang Pelanggan' : 'Tambah Saldo Awal Piutang Pelanggan' }}</h2><a href="{{ route('panduan.transaksi', 'saldo-awal-piutang') }}" class="btn btn-sm btn-outline-info"><i class="bi bi-book me-1"></i>Panduan</a></div>
        @if($isEdit)<div class="alert alert-warning py-2"><i class="bi bi-shield-check"></i> Edit hanya dapat disimpan jika belum ada pembayaran dan periode belum terkunci.</div>@endif
        <form method="POST" action="{{ $isEdit ? route('piutang.update', $piutang) : route('piutang.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">No. Faktur</label>
                    <input type="text" name="nofaktur" class="form-control @error('nofaktur') is-invalid @enderror" value="{{ old('nofaktur', $isEdit ? $piutang->NOFAKTUR : '') }}" required>
                    @error('nofaktur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Faktur</label>
                    <input type="date" name="tglfaktur" class="form-control @error('tglfaktur') is-invalid @enderror" value="{{ old('tglfaktur', $isEdit ? $piutang->TGLFAKTUR->toDateString() : now()->toDateString()) }}" required>
                    @error('tglfaktur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Jatuh Tempo</label>
                    <input type="date" name="tgljt" class="form-control @error('tgljt') is-invalid @enderror" value="{{ old('tgljt', $isEdit && $piutang->TGLJT ? $piutang->TGLJT->toDateString() : '') }}" required>
                    @error('tgljt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kode Customer</label>
                    <div class="input-group">
                        <input type="text" id="pelanggan" name="pelanggan" class="form-control @error('pelanggan') is-invalid @enderror" value="{{ old('pelanggan', $isEdit ? $piutang->CUSTOMER : '') }}" list="pelanggan-list" autocomplete="off" required>
                        <button type="button" class="btn btn-outline-secondary" id="openCustomerModal" title="Cari customer" aria-label="Cari customer">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                        <datalist id="pelanggan-list"></datalist>
                    </div>
                    @error('pelanggan')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <div class="form-text">Ketik minimal 2 huruf untuk mencari pelanggan.</div>
                    <label for="customer_name" class="form-label mt-2 mb-1">Nama Customer</label>
                    <input type="text" id="customer_name" class="form-control bg-light" placeholder="Terisi setelah customer dipilih" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nominal Piutang</label>
                    <input type="number" name="nominal" class="form-control @error('nominal') is-invalid @enderror" value="{{ old('nominal', $isEdit ? $piutang->PIUTANG : '') }}" min="0.01" step="0.01" required>
                    @error('nominal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi/Keterangan</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Contoh: Saldo awal piutang per 1 Januari 2026">{{ old('deskripsi', $isEdit ? $piutang->KETERANGAN : '') }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Saldo Awal' : 'Simpan Saldo Awal' }}</button>
                <a href="{{ route('piutang.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="customerSearchModal" tabindex="-1" aria-labelledby="customerSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerSearchModalLabel"><i class="bi bi-search me-2"></i>Cari Master Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <label for="customerModalSearch" class="form-label">Kode atau Nama Customer</label>
                <input type="search" id="customerModalSearch" class="form-control mb-3" placeholder="Ketik kode atau nama customer..." autocomplete="off">
                <div id="customerModalLoading" class="text-center text-muted py-4 d-none"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memuat data customer...</div>
                <div id="customerModalError" class="alert alert-danger d-none mb-3"></div>
                <div class="table-responsive" id="customerModalTableWrapper">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top"><tr><th>Kode Customer</th><th>Nama Customer</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody id="customerModalResults"></tbody>
                    </table>
                </div>
                <div id="customerModalEmpty" class="text-center text-muted py-4 d-none">Customer tidak ditemukan.</div>
                <div class="form-text mt-2">Maksimal 100 customer ditampilkan. Gunakan pencarian untuk mempersempit hasil.</div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('pelanggan');
        const list = document.getElementById('pelanggan-list');
        const customerName = document.getElementById('customer_name');
        const openModalButton = document.getElementById('openCustomerModal');
        const modalElement = document.getElementById('customerSearchModal');
        const modalSearch = document.getElementById('customerModalSearch');
        const modalResults = document.getElementById('customerModalResults');
        const modalEmpty = document.getElementById('customerModalEmpty');
        const modalLoading = document.getElementById('customerModalLoading');
        const modalError = document.getElementById('customerModalError');
        const modalTableWrapper = document.getElementById('customerModalTableWrapper');
        const customerModal = bootstrap.Modal.getOrCreateInstance(modalElement);

        if (!input || !list) {
            return;
        }

        let timeoutId = null;
        let modalTimer = null;
        let requestNumber = 0;
        const customers = new Map();

        function normalizeCustomer(item) {
            return {
                customer: String(item.customer || item.value || '').trim(),
                nama: String(item.nama || '').trim()
            };
        }

        function escapeHtml(value) {
            const element = document.createElement('div');
            element.textContent = value || '';
            return element.innerHTML;
        }

        function rememberCustomer(item) {
            const customer = normalizeCustomer(item);
            if (customer.customer) customers.set(customer.customer.toUpperCase(), customer);
            return customer;
        }

        function showSelectedCustomer() {
            const selected = customers.get(input.value.trim().toUpperCase());
            customerName.value = selected ? selected.nama : '';
        }

        function selectCustomer(item) {
            const customer = rememberCustomer(item);
            input.value = customer.customer;
            customerName.value = customer.nama;
            input.classList.remove('is-invalid');
            customerModal.hide();
            input.focus();
            input.dispatchEvent(new Event('change'));
        }

        function renderModalRows(items) {
            const rows = items.map(rememberCustomer).filter(item => item.customer);
            modalResults.innerHTML = rows.map(item => `<tr><td class="fw-semibold">${escapeHtml(item.customer)}</td><td>${escapeHtml(item.nama || 'Tanpa Nama')}</td><td class="text-end"><button type="button" class="btn btn-primary btn-sm select-customer" data-customer="${escapeHtml(item.customer)}">Pilih</button></td></tr>`).join('');
            modalEmpty.classList.toggle('d-none', rows.length > 0);
            modalTableWrapper.classList.toggle('d-none', rows.length === 0);
        }

        function loadModalCustomers(query = '') {
            const currentRequest = ++requestNumber;
            modalLoading.classList.remove('d-none');
            modalError.classList.add('d-none');
            modalEmpty.classList.add('d-none');
            modalTableWrapper.classList.add('d-none');

            fetch("{{ route('piutang.search-pelanggan') }}?modal=1&q=" + encodeURIComponent(query), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin'
            }).then(response => {
                if (!response.ok) throw new Error('Data customer gagal dimuat.');
                return response.json();
            }).then(items => {
                if (currentRequest === requestNumber) renderModalRows(items);
            }).catch(error => {
                if (currentRequest !== requestNumber) return;
                modalError.textContent = error.message;
                modalError.classList.remove('d-none');
            }).finally(() => {
                if (currentRequest === requestNumber) modalLoading.classList.add('d-none');
            });
        }

        input.addEventListener('input', function() {
            const value = input.value.trim();
            showSelectedCustomer();

            if (timeoutId) {
                clearTimeout(timeoutId);
            }

            if (value.length < 2) {
                list.innerHTML = '';
                return;
            }

            timeoutId = setTimeout(function() {

                const searchUrl = "{{ route('piutang.search-pelanggan') }}";

                fetch(searchUrl + '?q=' + encodeURIComponent(value))
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(items) {

                        list.innerHTML = '';

                        items.forEach(function(item) {
                            item = rememberCustomer(item);
                            const option = document.createElement('option');
                            option.value = item.customer;
                            option.label = item.customer + ' - ' + item.nama;
                            list.appendChild(option);
                        });

                        showSelectedCustomer();

                    });

            }, 250);
        });

        input.addEventListener('change', showSelectedCustomer);
        openModalButton.addEventListener('click', function() {
            modalSearch.value = input.value.trim();
            customerModal.show();
            loadModalCustomers(modalSearch.value);
        });
        modalElement.addEventListener('shown.bs.modal', function() {
            modalSearch.focus();
            modalSearch.select();
        });
        modalSearch.addEventListener('input', function() {
            clearTimeout(modalTimer);
            modalTimer = setTimeout(() => loadModalCustomers(modalSearch.value.trim()), 250);
        });
        modalResults.addEventListener('click', function(event) {
            const button = event.target.closest('.select-customer');
            if (!button) return;
            const selected = customers.get(button.dataset.customer.trim().toUpperCase());
            if (selected) selectCustomer(selected);
        });
        if (input.value.trim().length >= 2) input.dispatchEvent(new Event('input'));
    });
</script>
@endsection
