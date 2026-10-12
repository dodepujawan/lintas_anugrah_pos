@extends('layouts.app')

@php($isEdit = isset($piutang))
@section('title', $isEdit ? 'Edit Piutang Berjalan' : 'Input Piutang Berjalan')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="page-title mb-1"><i class="bi bi-receipt me-2"></i>{{ $isEdit ? 'Edit Invoice Piutang Berjalan' : 'Input Invoice Piutang Berjalan' }}</h2>
                <div class="small text-muted">Khusus invoice periode berjalan dari transaksi baru, bukan saldo awal.</div>
                <p class="text-muted mb-0">{{ $isEdit ? 'Perubahan akan memperbarui piutang dan jurnal terkait.' : 'Membuat invoice pelanggan dan jurnal penjualan kredit secara otomatis.' }}</p>
            </div>
            <div class="d-flex gap-2"><a href="{{ route('panduan.transaksi', 'piutang-berjalan') }}" class="btn btn-outline-info"><i class="bi bi-book me-1"></i>Panduan</a><a href="{{ route('piutang-berjalan.index') }}" class="btn btn-outline-secondary">Kembali</a></div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('piutang-berjalan.update', $piutang) : route('piutang-berjalan.store') }}" id="piutangBerjalanForm">
            @csrf
            @if($isEdit) @method('PUT') @endif
            <div class="card mb-4"><div class="card-header fw-semibold">Informasi Invoice</div><div class="card-body"><div class="row g-3">
                <div class="col-md-4"><label class="form-label">Nomor Bukti/Invoice <span class="text-danger">*</span></label><input name="no_invoice" class="form-control" maxlength="50" value="{{ old('no_invoice', $isEdit ? $piutang->NOFAKTUR : '') }}" required><div class="form-text">Harus unik dan tidak boleh pernah digunakan sebagai referensi jurnal.</div></div>
                <div class="col-md-4"><label class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label><input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $isEdit ? $piutang->TGLFAKTUR->toDateString() : now()->format('Y-m-d')) }}" required></div>
                <div class="col-md-4"><label class="form-label">Jatuh Tempo <span class="text-danger">*</span></label><input type="date" name="jatuh_tempo" class="form-control" value="{{ old('jatuh_tempo', $isEdit ? $piutang->TGLJT?->toDateString() : now()->addDays(30)->format('Y-m-d')) }}" required></div>
                <div class="col-md-6">
                    <label class="form-label">Kode Customer <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input name="customer" id="customer" list="customerList" class="form-control @error('customer') is-invalid @enderror" value="{{ old('customer', $isEdit ? $piutang->CUSTOMER : '') }}" autocomplete="off" required>
                        <button type="button" class="btn btn-outline-secondary" id="openCustomerModal" title="Cari customer" aria-label="Cari customer"><i class="bi bi-search me-1"></i>Cari</button>
                        <datalist id="customerList"></datalist>
                    </div>
                    @error('customer')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <div class="form-text">Ketik minimal dua karakter untuk mencari master customer.</div>
                    <label for="customerName" class="form-label mt-2 mb-1">Nama Customer</label>
                    <input type="text" id="customerName" class="form-control bg-light" placeholder="Terisi setelah customer dipilih" readonly>
                </div>
                <div class="col-md-6"><label class="form-label">Keterangan</label><input name="keterangan" class="form-control" maxlength="1000" value="{{ old('keterangan', $isEdit ? $piutang->KETERANGAN : '') }}"></div>
            </div></div></div>

            <div class="card mb-4"><div class="card-header fw-semibold">Jurnal Otomatis</div><div class="card-body"><div class="row g-3">
                <div class="col-md-6"><label class="form-label">Akun Piutang Usaha <span class="text-danger">*</span></label><select name="coa_piutang_id" class="form-select" required><option value="">Pilih akun</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected((string)old('coa_piutang_id', $receivableAccountId) === (string)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Akun Pendapatan <span class="text-danger">*</span></label><select name="coa_pendapatan_id" class="form-select" required><option value="">Pilih akun</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected((string)old('coa_pendapatan_id', $revenueAccountId) === (string)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Nilai Penjualan <span class="text-danger">*</span></label><input type="number" name="nilai_penjualan" id="nilaiPenjualan" class="form-control" min="0.01" step="0.01" value="{{ old('nilai_penjualan', $isEdit ? $salesValue : '') }}" required></div>
                <div class="col-md-6"><label class="form-label">Akun Pajak Keluaran</label><select name="coa_pajak_id" class="form-select"><option value="">Tanpa pajak</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected((string)old('coa_pajak_id', $outputTaxAccountId) === (string)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Nilai Pajak</label><input type="number" name="nilai_pajak" id="nilaiPajak" class="form-control" min="0" step="0.01" value="{{ old('nilai_pajak', $isEdit ? $taxValue : 0) }}"></div>
                <div class="col-md-6"><div class="p-3 bg-light rounded h-100"><div class="small text-muted">Total Invoice / Debit Piutang</div><div class="fs-4 fw-bold" id="totalInvoice">0,00</div></div></div>
            </div>
            <div class="alert alert-info mt-3 mb-0"><strong>Jurnal:</strong> Debit Piutang Usaha sebesar total invoice; Kredit Pendapatan dan Pajak Keluaran (jika ada).</div>
            </div></div>

            <div class="d-flex justify-content-end gap-2"><button class="btn btn-primary" id="piutangBerjalanSubmit" onclick="return confirm('{{ $isEdit ? 'Perbarui piutang dan jurnal terkait?' : 'Simpan invoice dan posting jurnal piutang ini?' }}')"><i class="bi bi-journal-check me-1"></i> {{ $isEdit ? 'Update Piutang & Jurnal' : 'Simpan dan Jurnal' }}</button></div>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sales = document.getElementById('nilaiPenjualan');
    const tax = document.getElementById('nilaiPajak');
    const total = document.getElementById('totalInvoice');
    const customer = document.getElementById('customer');
    const customerName = document.getElementById('customerName');
    const list = document.getElementById('customerList');
    const openModalButton = document.getElementById('openCustomerModal');
    const modalElement = document.getElementById('customerSearchModal');
    const modalSearch = document.getElementById('customerModalSearch');
    const modalResults = document.getElementById('customerModalResults');
    const modalEmpty = document.getElementById('customerModalEmpty');
    const modalLoading = document.getElementById('customerModalLoading');
    const modalError = document.getElementById('customerModalError');
    const modalTableWrapper = document.getElementById('customerModalTableWrapper');
    const customerModal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const form = document.getElementById('piutangBerjalanForm');
    let timer;
    let modalTimer;
    let requestNumber = 0;
    const customers = new Map();

    function normalizeCustomer(row) {
        return { customer: String(row.customer || row.value || '').trim(), nama: String(row.nama || row.name || '').trim() };
    }
    function escapeHtml(value) {
        const element = document.createElement('div'); element.textContent = value || ''; return element.innerHTML;
    }
    function rememberCustomer(row) {
        const item = normalizeCustomer(row);
        if (item.customer) customers.set(item.customer.toUpperCase(), item);
        return item;
    }
    function showSelectedCustomer() {
        const selected = customers.get(customer.value.trim().toUpperCase());
        customerName.value = selected ? selected.nama : '';
    }
    function selectCustomer(row) {
        const selected = rememberCustomer(row);
        customer.value = selected.customer;
        customerName.value = selected.nama;
        customer.classList.remove('is-invalid');
        customerModal.hide();
        customer.focus();
        customer.dispatchEvent(new Event('change'));
    }
    function renderModalRows(rows) {
        const items = rows.map(rememberCustomer).filter(row => row.customer);
        modalResults.innerHTML = items.map(row => `<tr><td class="fw-semibold">${escapeHtml(row.customer)}</td><td>${escapeHtml(row.nama || 'Tanpa Nama')}</td><td class="text-end"><button type="button" class="btn btn-primary btn-sm select-customer" data-customer="${escapeHtml(row.customer)}">Pilih</button></td></tr>`).join('');
        modalEmpty.classList.toggle('d-none', items.length > 0);
        modalTableWrapper.classList.toggle('d-none', items.length === 0);
    }
    function loadModalCustomers(query = '') {
        const currentRequest = ++requestNumber;
        modalLoading.classList.remove('d-none'); modalError.classList.add('d-none');
        modalEmpty.classList.add('d-none'); modalTableWrapper.classList.add('d-none');
        fetch("{{ route('customers.lookup') }}?q=" + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin'
        }).then(response => {
            if (!response.ok) throw new Error('Data customer gagal dimuat.');
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) throw new Error('Server tidak mengirim data JSON. Silakan refresh halaman dan login ulang jika sesi habis.');
            return response.json();
        }).then(rows => {
            if (currentRequest === requestNumber) renderModalRows(rows);
        }).catch(error => {
            if (currentRequest !== requestNumber) return;
            modalError.textContent = error.message; modalError.classList.remove('d-none');
        }).finally(() => {
            if (currentRequest === requestNumber) modalLoading.classList.add('d-none');
        });
    }
    function calculate() {
        const value = (parseFloat(sales.value) || 0) + (parseFloat(tax.value) || 0);
        total.textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
    }
    sales.addEventListener('input', calculate); tax.addEventListener('input', calculate); calculate();
    customer.addEventListener('input', function () {
        clearTimeout(timer); showSelectedCustomer(); const q = customer.value.trim(); if (q.length < 2) { list.innerHTML = ''; return; }
        timer = setTimeout(() => fetch("{{ route('customers.lookup') }}?q=" + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) throw new Error('Data customer gagal dimuat.');
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) throw new Error('Server tidak mengirim data JSON.');
                return response.json();
            }).then(rows => {
                list.innerHTML = ''; rows.forEach(row => { row = rememberCustomer(row); const option = document.createElement('option'); option.value = row.customer; option.label = row.customer + ' - ' + row.nama; list.appendChild(option); }); showSelectedCustomer();
            }).catch(() => { list.innerHTML = ''; }), 250);
    });
    customer.addEventListener('change', showSelectedCustomer);
    openModalButton.addEventListener('click', function () {
        modalSearch.value = customer.value.trim(); customerModal.show(); loadModalCustomers(modalSearch.value);
    });
    modalElement.addEventListener('shown.bs.modal', function () { modalSearch.focus(); modalSearch.select(); });
    modalSearch.addEventListener('input', function () {
        clearTimeout(modalTimer); modalTimer = setTimeout(() => loadModalCustomers(modalSearch.value.trim()), 250);
    });
    modalResults.addEventListener('click', function (event) {
        const button = event.target.closest('.select-customer'); if (!button) return;
        const selected = customers.get(button.dataset.customer.trim().toUpperCase()); if (selected) selectCustomer(selected);
    });
    if (customer.value.trim().length >= 2) customer.dispatchEvent(new Event('input'));

    form.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' || event.shiftKey || event.ctrlKey || event.altKey) return;

        // Nilai pajak adalah posisi terakhir: Enter di sini tetap melakukan submit.
        if (event.target === tax) return;

        const fields = Array.from(form.querySelectorAll('input:not([type="hidden"]), select, textarea'))
            .filter(field => !field.disabled && field.offsetParent !== null);
        const currentIndex = fields.indexOf(event.target);

        if (currentIndex >= 0 && currentIndex < fields.length - 1) {
            event.preventDefault();
            fields[currentIndex + 1].focus();
        }
    });

    form.addEventListener('submit', function () {
        const submit = document.getElementById('piutangBerjalanSubmit');
        if (!submit) return;

        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...';
    });
});
</script>
@endpush
