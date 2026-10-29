@extends('layouts.app')

@section('title', 'Generate Invoice Rent Pendingin')

@section('content')
<div class="container-fluid" id="rentInvoiceGeneratePage" data-debug="transaksiRentPendinginInvoiceGenerate">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-semibold text-primary mb-1">Transaksi / Rent Pendingin</div>
            <h1 class="page-title mb-1">Generate Invoice</h1>
            <p class="text-muted mb-0">Pilih satu Surat Jalan untuk membuat atau memperbarui invoice Rent Pendingin.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($canExport)
                <button type="button" class="btn btn-outline-success" id="rentInvoiceExportToggle" aria-expanded="false" aria-controls="rentInvoiceExportPanel">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Excel
                </button>
            @endif
            <a href="{{ route('rent-pendingin-invoice-gen.index') }}" class="btn btn-outline-warning" data-debug="transaksiRentPendinginInvoiceEditList">
                <i class="bi bi-pencil-square me-1"></i>Edit Rincian Invoice
            </a>
            <a href="{{ route('rent-pendingin.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Surat Jalan
            </a>
        </div>
    </div>

    @if($canExport)
    <div class="d-none mb-4" id="rentInvoiceExportPanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h2 class="h6 mb-0">Export Laporan Invoice Rent Pendingin</h2></div>
            <form method="GET" action="{{ route('rent-pendingin-invoice.export') }}" class="card-body row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold" for="rentInvoiceExportFrom">Tanggal dari</label>
                    <input type="date" id="rentInvoiceExportFrom" name="tanggal_dari" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold" for="rentInvoiceExportTo">Tanggal sampai</label>
                    <input type="date" id="rentInvoiceExportTo" name="tanggal_sampai" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold" for="rentInvoiceExportStatus">Status invoice</label>
                    <select id="rentInvoiceExportStatus" name="filter_kwt_dgn" class="form-select" required>
                        <option value="belum">Belum Invoice</option><option value="sudah">Sudah Invoice</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold" for="rentInvoiceExportCustomer">Customer (opsional)</label>
                    <select id="rentInvoiceExportCustomer" name="customer_invoice_ren" class="form-select">
                        <option value="">Semua Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->kode_cus }}">{{ $customer->NAMACUST ?: $customer->CUSTOMER }} ({{ $customer->kode_cus }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-success" type="submit"><i class="bi bi-download me-1"></i>Unduh Excel</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div id="rentInvoiceAlerts" aria-live="polite">
        @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-3 px-3 px-lg-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="h5 mb-1">Surat Jalan siap invoice</h2>
                    <p class="small text-muted mb-0">Invoice diproses per satu SJ. Data dan nilai tetap mengacu pada SJ Rent Pendingin.</p>
                </div>
                <div class="btn-group" role="group" aria-label="Filter status invoice">
                    <button type="button" class="btn btn-sm {{ $status === 'belum' ? 'btn-primary' : 'btn-outline-primary' }}" data-status="belum">Belum Invoice</button>
                    <button type="button" class="btn btn-sm {{ $status === 'sudah' ? 'btn-primary' : 'btn-outline-primary' }}" data-status="sudah">Sudah Invoice</button>
                </div>
            </div>
        </div>
        <div class="card-body px-3 px-lg-4">
            <div class="row g-2 align-items-end">
                <div class="col-lg-6 col-md-8">
                    <label for="rentInvoiceSearch" class="form-label small fw-semibold">Cari SJ, No. Muat, Invoice, atau Customer</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="search" id="rentInvoiceSearch" class="form-control" placeholder="Masukkan kata pencarian..." autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label for="rentInvoicePageSize" class="form-label small fw-semibold">Baris per halaman</label>
                    <select id="rentInvoicePageSize" class="form-select">
                        <option value="10">10</option><option value="25">25</option><option value="50">50</option>
                    </select>
                </div>
                <div class="col-lg-4 d-flex justify-content-lg-end gap-2">
                    <button type="button" id="rentInvoiceClear" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Reset</button>
                    <button type="button" id="rentInvoiceRefresh" class="btn btn-primary"><i class="bi bi-arrow-clockwise me-1"></i>Muat Ulang</button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="rentInvoiceTable" aria-busy="true">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Muat / Tanggal</th>
                        <th>No. Surat Jalan</th>
                        <th>Customer / Kendaraan</th>
                        <th>Pesanan</th>
                        <th class="text-end">Grand</th>
                        <th class="text-end">Bayar</th>
                        <th class="text-end">Piutang</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="8" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Memuat Surat Jalan...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 px-3 px-lg-4">
            <span class="small text-muted" id="rentInvoiceRowCount" aria-live="polite">Memuat...</span>
            <div class="btn-group btn-group-sm" role="group" aria-label="Navigasi halaman Surat Jalan">
                <button type="button" class="btn btn-outline-secondary" id="rentInvoicePrevious"><i class="bi bi-chevron-left"></i> Sebelumnya</button>
                <button type="button" class="btn btn-outline-secondary" id="rentInvoicePage" disabled>Halaman 1 / 1</button>
                <button type="button" class="btn btn-outline-secondary" id="rentInvoiceNext">Berikutnya <i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
    </div>

    <section class="card border-0 shadow-sm d-none" id="rentInvoiceFormCard" aria-labelledby="rentInvoiceFormTitle">
        <div class="card-header bg-white px-3 px-lg-4 py-3">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h2 class="h5 mb-1" id="rentInvoiceFormTitle">Informasi Invoice</h2>
                    <p class="small text-muted mb-0" id="rentInvoiceFormHint">Rincian diambil dari Surat Jalan yang dipilih.</p>
                </div>
                <button type="button" class="btn-close" id="rentInvoiceCloseForm" aria-label="Tutup form"></button>
            </div>
        </div>
        <div class="card-body px-3 px-lg-4">
            <form method="POST" action="{{ route('rent-pendingin-invoice.store') }}" id="rentInvoiceForm" data-debug="transaksiRentPendinginInvoiceForm">
                @csrf
                <input type="hidden" name="nosj" id="rentInvoiceNosj" value="{{ old('nosj') }}">
                <input type="hidden" name="invoice" id="rentInvoiceNumber" value="{{ old('invoice') }}">
                <input type="hidden" name="status_inv_gen" id="rentInvoiceStatus" value="{{ old('status_inv_gen', $status) }}">
                <div class="alert alert-primary d-flex align-items-start gap-2 mb-4" role="status">
                    <i class="bi bi-info-circle mt-1"></i>
                    <div id="rentInvoiceSummary">Pilih Surat Jalan untuk melihat rincian invoice.</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label small fw-semibold">No. Invoice</label>
                        <input type="text" id="rentInvoiceNumberDisplay" class="form-control font-monospace" value="Nomor dibuat saat disimpan" readonly>
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label small fw-semibold">Customer</label>
                        <input type="text" id="rentInvoiceCustomer" class="form-control" readonly>
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label small fw-semibold">No. Muat</label>
                        <input type="text" id="rentInvoiceNomuat" class="form-control font-monospace" readonly>
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label small fw-semibold">Kendaraan</label>
                        <input type="text" id="rentInvoiceVehicle" class="form-control" readonly>
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label small fw-semibold">Grand Total</label>
                        <input type="text" id="rentInvoiceGrand" class="form-control text-end fw-semibold" readonly>
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <label for="rentInvoicePayment" class="form-label small fw-semibold">Bayar</label>
                        <input type="number" name="bayar" id="rentInvoicePayment" class="form-control text-end" min="0" step="1" value="{{ old('bayar', 0) }}" required>
                        <div class="invalid-feedback">Bayar tidak boleh lebih besar dari Grand Total.</div>
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <label for="rentInvoiceTop" class="form-label small fw-semibold">TOP (Hari)</label>
                        <input type="number" name="top" id="rentInvoiceTop" class="form-control" min="0" max="3650" step="1" value="{{ old('top', 0) }}">
                        <div class="form-text">Mengikuti termin customer; tersimpan pada ARH sesuai perilaku legacy.</div>
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <label for="rentInvoiceDueDate" class="form-label small fw-semibold">Tanggal Jatuh Tempo</label>
                        <input type="date" name="tgl_jtp" id="rentInvoiceDueDate" class="form-control" value="{{ old('tgl_jtp') }}">
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label small fw-semibold">Sisa Piutang</label>
                        <input type="text" id="rentInvoiceBalance" class="form-control text-end fw-semibold" readonly>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="rentInvoiceCancel">Batal</button>
                    <button type="submit" class="btn btn-success" id="rentInvoiceSubmit" disabled>
                        <i class="bi bi-file-earmark-check me-1"></i><span>Simpan Invoice</span>
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const exportToggle = document.getElementById('rentInvoiceExportToggle');
    const exportPanel = document.getElementById('rentInvoiceExportPanel');
    if (exportToggle && exportPanel) {
        exportToggle.onclick = () => {
            const expanded = exportToggle.getAttribute('aria-expanded') === 'true';
            exportToggle.setAttribute('aria-expanded', String(!expanded));
            exportPanel.classList.toggle('d-none', expanded);
        };
    }

    const tbody = document.querySelector('#rentInvoiceTable tbody');
    const formCard = document.getElementById('rentInvoiceFormCard');
    const form = document.getElementById('rentInvoiceForm');
    const search = document.getElementById('rentInvoiceSearch');
    const pageSize = document.getElementById('rentInvoicePageSize');
    const payment = document.getElementById('rentInvoicePayment');
    const top = document.getElementById('rentInvoiceTop');
    const dueDate = document.getElementById('rentInvoiceDueDate');
    const submit = document.getElementById('rentInvoiceSubmit');
    const escapeHtml = value => String(value ?? '-').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    const money = value => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
    const dataUrl = @json(route('rent-pendingin-invoice.data'));
    const detailUrl = @json(route('rent-pendingin-invoice.surat-jalan', ['nosj' => '__NOSJ__']));
    let status = @json($status === 'sudah' ? 'sudah' : 'belum');
    let page = 1;
    let lastPage = 1;
    let selected = null;
    let loadController = null;
    let searchTimeout = null;
    let restoreSj = @json(old('nosj'));
    const oldValues = {
        bayar: @json(old('bayar')),
        top: @json(old('top')),
        tgl_jtp: @json(old('tgl_jtp')),
    };

    function setAlert(message, type = 'danger') {
        document.getElementById('rentInvoiceAlerts').innerHTML = `<div class="alert alert-${type}" role="alert">${escapeHtml(message)}</div>`;
    }

    function renderRows(rows, meta) {
        lastPage = meta.last_page;
        const firstRow = meta.total ? ((meta.page - 1) * meta.per_page) + 1 : 0;
        const lastRow = Math.min(meta.page * meta.per_page, meta.total);
        tbody.innerHTML = rows.length ? rows.map(row => `
            <tr class="${selected?.NOSJ === row.NOSJ ? 'table-primary' : ''}" data-sj-row="${escapeHtml(row.NOSJ)}" tabindex="0" role="button" style="cursor:pointer">
                <td class="ps-3"><div class="fw-semibold">${escapeHtml(row.NOMUAT)}</div><div class="small text-muted">${escapeHtml(row.TGLMUAT)}</div></td>
                <td><span class="font-monospace fw-semibold">${escapeHtml(row.NOSJ)}</span></td>
                <td><div>${escapeHtml(row.CUSTOMER)}</div><div class="small text-muted">${escapeHtml(row.KENDARAAN || '-')}</div></td>
                <td>${escapeHtml(row.PESANAN || '-')}</td>
                <td class="text-end text-nowrap fw-semibold">${money(row.GRAND)}</td>
                <td class="text-end text-nowrap">${money(row.BAYAR)}</td>
                <td class="text-end text-nowrap">${money(row.PIUTANG)}</td>
                <td class="text-end pe-3"><button type="button" class="btn btn-sm ${status === 'sudah' ? 'btn-outline-warning' : 'btn-primary'}" data-select-sj="${escapeHtml(row.NOSJ)}">
                    <i class="bi ${status === 'sudah' ? 'bi-pencil-square' : 'bi-arrow-right-circle'} me-1"></i>${status === 'sudah' ? 'Edit Pembayaran' : 'Proses'}
                </button></td>
            </tr>`).join('') : '<tr><td colspan="8" class="text-center py-5"><i class="bi bi-inbox fs-3 d-block text-muted mb-2"></i><span class="fw-semibold">Tidak ada Surat Jalan</span><span class="small text-muted d-block">Ubah status atau kata pencarian.</span></td></tr>';

        document.getElementById('rentInvoiceRowCount').textContent = meta.total
            ? `Menampilkan ${firstRow}-${lastRow} dari ${meta.total} Surat Jalan`
            : 'Tidak ada data yang cocok';
        document.getElementById('rentInvoicePage').textContent = `Halaman ${meta.page} / ${lastPage}`;
        document.getElementById('rentInvoicePrevious').disabled = meta.page <= 1;
        document.getElementById('rentInvoiceNext').disabled = meta.page >= lastPage;

        if (restoreSj && rows.some(row => row.NOSJ === restoreSj)) {
            const nosj = restoreSj;
            restoreSj = null;
            selectSj(nosj, true);
        }
    }

    async function loadRows() {
        loadController?.abort();
        loadController = new AbortController();
        document.getElementById('rentInvoiceTable').setAttribute('aria-busy', 'true');
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Memuat Surat Jalan...</td></tr>';
        const params = new URLSearchParams({status_inv_gen: status, page, per_page: pageSize.value});
        if (search.value.trim()) params.set('q', search.value.trim());
        try {
            const response = await fetch(`${dataUrl}?${params}`, {headers: {Accept: 'application/json'}, signal: loadController.signal});
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Data Surat Jalan gagal dimuat.');
            renderRows(payload.data, payload.meta);
        } catch (error) {
            if (error.name === 'AbortError') return;
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger">Data gagal dimuat.</td></tr>';
            setAlert(error.message);
        } finally {
            document.getElementById('rentInvoiceTable').setAttribute('aria-busy', 'false');
        }
    }

    function formatDateForInput(value) {
        if (!value || value === '-') return '';
        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value;
        const [day, month, year] = value.split('/');
        return `${year}-${month}-${day}`;
    }

    function updateBalance() {
        if (!selected) return;
        const grand = Number(selected.GRAND || 0);
        const paid = Number(payment.value || 0);
        const valid = Number.isFinite(paid) && paid >= 0 && paid <= grand;
        payment.setCustomValidity(valid ? '' : 'Bayar tidak boleh lebih besar dari Grand Total.');
        payment.classList.toggle('is-invalid', !valid);
        document.getElementById('rentInvoiceBalance').value = money(Math.max(0, grand - (Number.isFinite(paid) ? paid : 0)));
        submit.disabled = !valid;
    }

    function updateDueDate() {
        const invoiceDate = selected?.TGLINVOICE && selected.TGLINVOICE !== '-'
            ? formatDateForInput(selected.TGLINVOICE)
            : new Date().toISOString().slice(0, 10);
        if (!invoiceDate) return;
        const date = new Date(`${invoiceDate}T00:00:00`);
        date.setDate(date.getDate() + Number(top.value || 0));
        dueDate.value = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    }

    async function selectSj(nosj, restore = false) {
        selected = {NOSJ: nosj};
        tbody.querySelectorAll('tr').forEach(tr => tr.classList.toggle('table-primary', tr.querySelector(`[data-select-sj="${CSS.escape(nosj)}"]`) !== null));
        try {
            const response = await fetch(detailUrl.replace('__NOSJ__', encodeURIComponent(nosj)), {headers: {Accept: 'application/json'}});
            const payload = await response.json();
            if (!response.ok || !payload.status) throw new Error(payload.message || 'Rincian SJ gagal dimuat.');
            const data = payload.data;
            selected = {...selected, NOSJ: data.nomor_sj, GRAND: Number(data.grand || 0), INVOICE: data.invoice};
            document.getElementById('rentInvoiceNosj').value = data.nomor_sj;
            document.getElementById('rentInvoiceNumber').value = data.invoice || '';
            document.getElementById('rentInvoiceStatus').value = status;
            document.getElementById('rentInvoiceNumberDisplay').value = data.invoice || 'Nomor dibuat saat disimpan';
            document.getElementById('rentInvoiceCustomer').value = data.customer || '-';
            document.getElementById('rentInvoiceNomuat').value = data.nomor_muat || '-';
            document.getElementById('rentInvoiceVehicle').value = data.kendaraan || '-';
            document.getElementById('rentInvoiceGrand').value = money(data.grand);
            document.getElementById('rentInvoiceSummary').innerHTML = `<strong>${escapeHtml(data.nomor_sj)}</strong> · ${escapeHtml(data.customer)} · ${escapeHtml(data.item || 'Rent Pendingin')} · Grand ${money(data.grand)}${data.invoice ? ` · Invoice ${escapeHtml(data.invoice)}` : ''}`;
            document.getElementById('rentInvoiceFormTitle').textContent = data.invoice ? 'Perbarui Pembayaran Invoice' : 'Generate Invoice Rent Pendingin';
            document.getElementById('rentInvoiceFormHint').textContent = data.invoice
                ? 'Perubahan pembayaran, TOP, dan jatuh tempo akan disinkronkan mengikuti aturan legacy.'
                : 'Nomor invoice dibuat otomatis saat disimpan; nilai transaksi berasal dari SJ.';
            submit.querySelector('span').textContent = data.invoice ? 'Simpan Perubahan' : 'Generate Invoice';
            top.value = restore && oldValues.top !== null ? oldValues.top : data.top;
            payment.value = restore && oldValues.bayar !== null ? oldValues.bayar : data.bayar;
            dueDate.value = restore && oldValues.tgl_jtp ? oldValues.tgl_jtp : data.tgl_jt;
            formCard.classList.remove('d-none');
            updateBalance();
            if (!restore) formCard.scrollIntoView({behavior: 'smooth', block: 'start'});
        } catch (error) {
            formCard.classList.add('d-none');
            setAlert(error.message);
        }
    }

    tbody.addEventListener('click', event => {
        const button = event.target.closest('[data-select-sj]');
        const row = event.target.closest('[data-sj-row]');
        const nosj = button?.dataset.selectSj || row?.dataset.sjRow;
        if (nosj) selectSj(nosj);
    });
    tbody.addEventListener('keydown', event => {
        const row = event.target.closest('[data-sj-row]');
        if (row && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            selectSj(row.dataset.sjRow);
        }
    });
    document.querySelectorAll('[data-status]').forEach(button => button.addEventListener('click', () => {
        status = button.dataset.status;
        page = 1;
        selected = null;
        formCard.classList.add('d-none');
        document.getElementById('rentInvoiceStatus').value = status;
        document.querySelectorAll('[data-status]').forEach(tab => {
            tab.classList.toggle('btn-primary', tab === button);
            tab.classList.toggle('btn-outline-primary', tab !== button);
        });
        const url = new URL(window.location.href);
        url.searchParams.set('status_inv_gen', status);
        history.replaceState({}, '', url);
        loadRows();
    }));
    search.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { page = 1; loadRows(); }, 250);
    });
    pageSize.addEventListener('change', () => { page = 1; loadRows(); });
    document.getElementById('rentInvoiceRefresh').addEventListener('click', loadRows);
    document.getElementById('rentInvoiceClear').addEventListener('click', () => { search.value = ''; page = 1; loadRows(); });
    document.getElementById('rentInvoicePrevious').addEventListener('click', () => { if (page > 1) { page--; loadRows(); } });
    document.getElementById('rentInvoiceNext').addEventListener('click', () => { if (page < lastPage) { page++; loadRows(); } });
    document.getElementById('rentInvoiceCancel').addEventListener('click', () => formCard.classList.add('d-none'));
    document.getElementById('rentInvoiceCloseForm').addEventListener('click', () => formCard.classList.add('d-none'));
    payment.addEventListener('input', updateBalance);
    top.addEventListener('input', updateDueDate);
    form.addEventListener('submit', event => {
        if (!selected || !payment.reportValidity()) {
            event.preventDefault();
            return;
        }
        const amount = Number(payment.value || 0);
        const balance = Math.max(0, Number(selected.GRAND || 0) - amount);
        const message = selected.INVOICE
            ? `Simpan perubahan invoice ${selected.INVOICE} untuk SJ ${selected.NOSJ}? Bayar ${money(amount)}, sisa piutang ${money(balance)}.`
            : `Buat invoice untuk SJ ${selected.NOSJ} dengan nilai ${money(selected.GRAND)} dan pembayaran ${money(amount)}?`;
        if (!window.confirm(message)) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        submit.querySelector('span').textContent = 'Menyimpan...';
    });

    if (restoreSj) search.value = restoreSj;
    loadRows();
})();
</script>
@endpush
