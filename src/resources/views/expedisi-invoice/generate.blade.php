@extends('layouts.app')

@section('title', 'Generate Invoice Expedisi')

@section('content')
<div class="container-fluid" id="expedisiGeneratePage" data-debug="transaksiExpedisiInvoiceGenerate">
    <div class="d-flex justify-content-between align-items-start mb-3" data-debug="transaksiExpedisiInvoiceGenerateHeader">
        <div>
            <h1 class="page-title mb-1">Generate Invoice Expedisi</h1>
            <p class="text-muted small mb-0">Buat invoice dari SJ tunggal atau satu grup gabungan GB.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('expedisi-invoice-gen.index') }}" data-debug="transaksiExpedisiInvoiceGenerateEditBtn" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-pencil-square"></i> Edit Invoice
            </a>
            <a href="{{ route('expedisi-invoice.index') }}" data-debug="transaksiExpedisiInvoiceGenerateBackBtn" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Daftar Invoice
            </a>
        </div>
    </div>

    <div id="alertBox" aria-live="polite"></div>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div><strong>Surat Jalan Siap Invoice</strong><div class="small text-muted">Untuk GB, pilih satu baris saja. Seluruh SJ dalam grup akan ikut masuk invoice.</div></div>
            <button type="button" id="btnPanduanInvoiceGenerate" data-debug="transaksiExpedisiInvoiceGenerateGuideBtn" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#invoiceGuide">
                <i class="bi bi-question-circle me-1"></i>Panduan
            </button>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-lg-5 col-md-6">
                    <label for="searchSj" class="form-label small">Cari No Muat, No SJ, atau Customer</label>
                    <input type="search" id="searchSj" data-debug="transaksiExpedisiInvoiceGenerateSearch" class="form-control" placeholder="Ketik kata pencarian..." autocomplete="off">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label for="dateFrom" class="form-label small">Tanggal Muat Dari</label>
                    <input type="date" id="dateFrom" data-debug="transaksiExpedisiInvoiceGenerateDateFrom" class="form-control">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label for="dateTo" class="form-label small">Sampai</label>
                    <input type="date" id="dateTo" data-debug="transaksiExpedisiInvoiceGenerateDateTo" class="form-control">
                </div>
                <div class="col-lg-3 col-md-12 d-flex justify-content-lg-end align-items-end gap-2">
                    <button type="button" id="clearFilters" data-debug="transaksiExpedisiInvoiceGenerateClearFilters" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Reset</button>
                    <button type="button" id="refreshRows" data-debug="transaksiExpedisiInvoiceGenerateRefreshBtn" class="btn btn-primary"><i class="bi bi-arrow-clockwise me-1"></i>Muat Ulang</button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="invoiceRows" aria-busy="true">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-3">Pilih</th><th scope="col">No Muat / Tanggal</th><th scope="col">Surat Jalan</th>
                        <th scope="col">Customer / Kendaraan</th><th scope="col">Grup</th><th scope="col" class="text-end">Jumlah</th>
                        <th scope="col" class="text-end">Harga</th><th scope="col" class="text-end">Subtotal</th><th scope="col" class="text-end">D. Charge</th>
                        <th scope="col" class="text-end">Disc %</th><th scope="col" class="text-end">Disc Rp</th><th scope="col" class="text-end">DPP</th>
                        <th scope="col" class="text-end">PPN %</th><th scope="col" class="text-end pe-3">Grand Total</th>
                    </tr>
                </thead>
                <tbody><tr><td colspan="14" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Memuat Surat Jalan...</td></tr></tbody>
            </table>
        </div>
        <div class="card-footer d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <span class="small text-muted" id="rowCount" aria-live="polite">Memuat...</span>
            <div class="btn-group btn-group-sm" role="group" aria-label="Navigasi halaman Surat Jalan">
                <button type="button" id="previousPage" class="btn btn-outline-secondary" data-debug="transaksiExpedisiInvoiceGeneratePreviousPage"><i class="bi bi-chevron-left"></i> Sebelumnya</button>
                <button type="button" id="pageIndicator" class="btn btn-outline-secondary" disabled>Halaman 1 / 1</button>
                <button type="button" id="nextPage" class="btn btn-outline-secondary" data-debug="transaksiExpedisiInvoiceGenerateNextPage">Berikutnya <i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm d-none" id="invoiceFormCard">
        <div class="card-header"><strong>Informasi Invoice</strong><div class="small text-muted">Tanggal jatuh tempo otomatis mengikuti TOP customer, dan masih dapat diubah.</div></div>
        <div class="card-body">
            <form method="POST" action="{{ route('expedisi-generate-invoice.store') }}" id="invoiceForm" data-debug="transaksiExpedisiInvoiceGenerateForm">
                @csrf
                <div id="selectedInputs"></div>
                <div class="alert alert-primary d-flex align-items-start gap-2" id="selectedSummary" role="status"><i class="bi bi-info-circle mt-1"></i><span id="selectionSummary">Pilih satu Surat Jalan untuk melihat ringkasan invoice.</span></div>
                <div class="alert alert-light border py-2 small"><i class="bi bi-info-circle me-1"></i>Invoice, TOP, dan pembayaran awal disimpan pada data Expedisi/piutang. Jurnal penjualan tidak dibuat otomatis pada tahap ini.</div>
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label small" for="invoiceNumberPreview">Nomor Invoice (pratinjau)</label><input type="text" id="invoiceNumberPreview" data-debug="transaksiExpedisiInvoiceGenerate_INVOICE" class="form-control font-monospace" value="{{ $invoicePreview }}" readonly><div class="form-text">Nomor final bisa berubah jika invoice lain disimpan bersamaan.</div></div>
                    <div class="col-md-3"><label class="form-label small">Tanggal Invoice</label><input type="date" name="TGLFAKTUR" data-debug="transaksiExpedisiInvoiceGenerate_TGLFAKTUR" class="form-control" value="{{ old('TGLFAKTUR', now()->format('Y-m-d')) }}" required></div>
                    <div class="col-md-3"><label class="form-label small">Tanggal Jatuh Tempo</label><input type="date" name="TGLJT" data-debug="transaksiExpedisiInvoiceGenerate_TGLJT" class="form-control" value="{{ old('TGLJT') }}"></div>
                    <div class="col-md-3"><label class="form-label small" for="top_kwt_exp">TOP (Hari)</label><input type="number" id="top_kwt_exp" name="TOP" data-debug="transaksiExpedisiInvoiceGenerate_TOP" class="form-control" min="0" max="3650" step="1" value="{{ old('TOP') }}"><div class="form-text">Default dari termin customer; tanggal jatuh tempo dihitung otomatis.</div></div>
                    <div class="col-md-3"><label class="form-label small" for="initialPayment">Pembayaran Awal</label><input type="number" id="initialPayment" name="BAYAR" data-debug="transaksiExpedisiInvoiceGenerate_BAYAR" class="form-control text-end" min="0" step="1" value="{{ old('BAYAR', 0) }}"><div class="form-text">Opsional, tidak melebihi nilai invoice.</div><div id="initialPaymentError" class="small text-danger mt-1 d-none" role="alert">Pembayaran awal melebihi nilai invoice.</div></div>
                    <div class="col-md-3"><label class="form-label small" for="receivablePreview">Sisa Piutang</label><input type="text" id="receivablePreview" class="form-control text-end fw-semibold" readonly tabindex="-1"><div class="form-text">Grand dikurangi pembayaran awal.</div></div>
                    <div class="col-12"><label class="form-label small">Keterangan</label><input name="KETERANGAN" data-debug="transaksiExpedisiInvoiceGenerate_KETERANGAN" class="form-control" maxlength="500" value="{{ old('KETERANGAN', 'INVOICE DARI EXPEDISI') }}"></div>
                </div>
                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                    <button type="button" id="clearSelection" data-debug="transaksiExpedisiInvoiceGenerateClearSelection" class="btn btn-outline-secondary">Batal Pilih</button>
                    <button type="submit" class="btn btn-success" id="submitInvoice" data-debug="transaksiExpedisiInvoiceGenerateSubmit" disabled><i class="bi bi-file-earmark-check me-1"></i>Generate Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4" data-debug="transaksiExpedisiInvoiceGenerateExportCard">
    <div class="card-header"><strong>Laporan Excel</strong><div class="small text-muted">Export data Surat Jalan berdasarkan tanggal muat dan status invoice.</div></div>
    <div class="card-body">
        <form method="GET" action="{{ route('expedisi-generate-invoice.export') }}" class="row g-3 align-items-end" data-debug="transaksiExpedisiInvoiceGenerateExportForm">
            <div class="col-lg-3 col-md-6">
                <label class="form-label" for="exportStatus">Status Invoice</label>
                <select id="exportStatus" name="filter_inv_gen" class="form-select" data-debug="transaksiExpedisiInvoiceGenerateExportStatus">
                    <option value="belum">Belum Invoice</option><option value="sudah">Sudah Invoice</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6"><label class="form-label" for="exportDateFrom">Tanggal Dari</label><input type="date" id="exportDateFrom" name="tanggal_dari" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required data-debug="transaksiExpedisiInvoiceGenerateExportDateFrom"></div>
            <div class="col-lg-2 col-md-6"><label class="form-label" for="exportDateTo">Tanggal Sampai</label><input type="date" id="exportDateTo" name="tanggal_sampai" class="form-control" value="{{ now()->format('Y-m-d') }}" required data-debug="transaksiExpedisiInvoiceGenerateExportDateTo"></div>
            <div class="col-lg-3 col-md-6"><label class="form-label" for="exportCustomer">Customer (opsional)</label><input type="text" id="exportCustomer" name="customer_invoice_exp" list="invoiceCustomerOptions" class="form-control" maxlength="30" placeholder="Semua customer" autocomplete="off" data-debug="transaksiExpedisiInvoiceGenerateExportCustomer"><datalist id="invoiceCustomerOptions"></datalist><div class="form-text">Ketik kode atau nama, lalu pilih hasil pencarian.</div></div>
            <div class="col-lg-2"><button type="submit" class="btn btn-outline-success w-100" data-debug="transaksiExpedisiInvoiceGenerateExportSubmit"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Excel</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="invoiceGuide" data-debug="transaksiExpedisiInvoiceGenerateGuideModal" tabindex="-1" aria-labelledby="invoiceGuideLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="invoiceGuideLabel">Panduan Generate Invoice Expedisi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <ol class="mb-0"><li class="mb-2">Pilih satu SJ. Jika SJ termasuk grup GB, seluruh anggota grup otomatis menjadi satu invoice.</li><li class="mb-2">Rincian Subtotal, D.Charge, diskon, DPP, PPN, dan Grand di daftar bersifat informasi dari SJ. Geser tabel ke samping untuk melihat semuanya.</li><li class="mb-2">TOP customer menjadi nilai awal dan tanggal jatuh tempo akan dihitung dari TOP. Nomor invoice boleh dikosongkan agar nomor FJO dibuat otomatis.</li><li>Isi pembayaran awal bila ada. Klik <strong>Generate Invoice</strong> untuk menyimpan invoice dan piutang. Jurnal penjualan tidak dibuat otomatis.</li></ol>
            <div class="alert alert-warning mt-3 mb-0 small">SJ yang sudah memiliki invoice tidak akan tampil. Pembayaran dicatat terpisah melalui menu Kwitansi Expedisi.</div>
        </div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const tbody = document.querySelector('#invoiceRows tbody');
    const search = document.getElementById('searchSj');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const selectedInputs = document.getElementById('selectedInputs');
    const formCard = document.getElementById('invoiceFormCard');
    const submit = document.getElementById('submitInvoice');
    const paymentInput = document.getElementById('initialPayment');
    const topInput = document.getElementById('top_kwt_exp');
    const receivablePreview = document.getElementById('receivablePreview');
    const summary = document.getElementById('selectionSummary');
    const esc = value => String(value ?? '-').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const money = value => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
    const rowsPerPage = 25;
    const url = @json(route('expedisi-generate-invoice.data'));
    const customerLookupUrl = @json(route('expedisi-generate-invoice.customers'));
    let selectedRow = null, restoredSj = @json(old('selected_sj')), preserveDueDate = @json(old('TGLJT') !== null), preserveTop = @json(old('TOP') !== null), page = 1, lastPage = 1, requestController = null, searchTimer = null;
    let customerTimer = null, customerController = null;
    if (restoredSj) search.value = restoredSj;

    function render(rows, meta) {
        lastPage = meta.last_page;
        if (restoredSj) {
            selectedRow = rows.find(row => row.NOSJ === restoredSj) || null;
            restoredSj = null;
        }
        const firstRow = meta.total ? ((meta.page - 1) * meta.per_page) + 1 : 0;
        const lastRow = Math.min(meta.page * meta.per_page, meta.total);
        tbody.innerHTML = rows.length ? rows.map(row => `
            <tr data-debug="transaksiExpedisiInvoiceGenerateRow_${esc(row.NOSJ)}" class="${selectedRow?.NOSJ === row.NOSJ ? 'table-primary' : ''}">
                <td class="ps-3"><input type="radio" name="invoice_sj_choice" class="sj-choice" data-row="${esc(JSON.stringify(row))}" data-debug="transaksiExpedisiInvoiceGenerateSelect_${esc(row.NOSJ)}" value="${esc(row.NOSJ)}" aria-label="Pilih SJ ${esc(row.NOSJ)}" ${selectedRow?.NOSJ === row.NOSJ ? 'checked' : ''}></td>
                <td><div class="fw-semibold">${esc(row.NOMUAT)}</div><div class="small text-muted">${esc(row.TGLMUAT)}</div></td>
                <td><span class="font-monospace fw-semibold">${esc(row.NOSJ)}</span></td>
                <td><div>${esc(row.CUSTOMER)}</div><div class="small text-muted">${esc(row.NAMA_KENDARAAN)}</div></td>
                <td>${row.GB ? `<span class="badge text-bg-info">GB ${esc(row.GB)}</span><div class="small text-muted mt-1">${Number(row.GROUP_SIZE)} SJ digabung</div><div class="small font-monospace">${esc((row.GROUP_SJS || []).slice(0, 3).join(', '))}${row.GROUP_SJS.length > 3 ? ', ...' : ''}</div>` : '<span class="badge text-bg-light border">SJ tunggal</span>'}</td>
                <td class="text-end text-nowrap">${Number(row.JUMLAH || 0).toLocaleString('id-ID')} ${esc(row.UNIT)}</td>
                <td class="text-end text-nowrap">${money(row.HARGA)}</td>
                <td class="text-end text-nowrap">${money(row.SUBTOTAL)}</td>
                <td class="text-end text-nowrap">${money(row.DCHARGE)}</td>
                <td class="text-end text-nowrap">${Number(row.DISC || 0).toLocaleString('id-ID')}%</td>
                <td class="text-end text-nowrap">${money(row.DISC_AMOUNT)}</td>
                <td class="text-end text-nowrap">${money(row.DPP)}</td>
                <td class="text-end text-nowrap">${Number(row.PPN || 0).toLocaleString('id-ID')}%</td>
                <td class="text-end pe-3 fw-semibold text-nowrap">${money(row.GRAND)}</td>
            </tr>`).join('') : '<tr><td colspan="14" class="text-center py-5"><i class="bi bi-inbox fs-3 d-block text-muted mb-2"></i><span class="fw-semibold">Tidak ada SJ yang siap dibuatkan invoice</span><span class="small text-muted d-block">Ubah pencarian atau rentang tanggal.</span></td></tr>';

        document.getElementById('rowCount').textContent = meta.total
            ? `Menampilkan ${firstRow}-${lastRow} dari ${meta.total} tagihan siap invoice`
            : 'Tidak ada tagihan yang cocok';
        document.getElementById('pageIndicator').textContent = `Halaman ${meta.page} / ${lastPage}`;
        document.getElementById('previousPage').disabled = meta.page <= 1;
        document.getElementById('nextPage').disabled = meta.page >= lastPage;
        updateSelection();
    }

    function updateSelection() {
        const selected = Boolean(selectedRow);
        selectedInputs.innerHTML = selectedRow
            ? `<input type="hidden" name="selected_sj" value="${esc(selectedRow.NOSJ)}">`
            : '';
        formCard.classList.toggle('d-none', !selected);
        if (!selected) {
            submit.disabled = true;
            receivablePreview.value = '';
            summary.textContent = 'Pilih satu Surat Jalan untuk melihat ringkasan invoice.';
            return;
        }
        paymentInput.max = selectedRow.GRAND;
        const groupText = selectedRow.GB
            ? `Grup GB ${esc(selectedRow.GB)} mencakup ${selectedRow.GROUP_SIZE} SJ: ${esc((selectedRow.GROUP_SJS || []).join(', '))}.`
            : 'Invoice hanya akan mencakup SJ ini.';
        summary.innerHTML = `<strong>${esc(selectedRow.NOSJ)}</strong> · ${esc(selectedRow.CUSTOMER)} · ${groupText} Nilai invoice: <strong>${money(selectedRow.GRAND)}</strong>.`;
        updateBalance();
        if (preserveTop) {
            preserveTop = false;
        } else {
            topInput.value = Number(selectedRow.TOP || selectedRow.TOPKREDIT || 0);
        }
        setDueDate(topInput.value);
    }

    function updateBalance() {
        if (!selectedRow) return;
        const paid = Number(paymentInput.value || 0);
        const valid = Number.isFinite(paid) && paid >= 0 && paid <= Number(selectedRow.GRAND);
        paymentInput.setCustomValidity(valid ? '' : 'Pembayaran awal tidak boleh melebihi nilai invoice.');
        paymentInput.classList.toggle('is-invalid', !valid);
        document.getElementById('initialPaymentError').classList.toggle('d-none', valid);
        receivablePreview.value = money(Math.max(0, Number(selectedRow.GRAND) - (Number.isFinite(paid) ? paid : 0)));
        submit.disabled = !valid;
    }

    function setDueDate(topDays) {
        if (preserveDueDate) { preserveDueDate = false; return; }
        const invoiceDate = document.querySelector('[name="TGLFAKTUR"]');
        const dueDate = document.querySelector('[name="TGLJT"]');
        const date = new Date(`${invoiceDate.value}T00:00:00`);
        if (!invoiceDate.value || Number.isNaN(date.getTime())) return;
        date.setDate(date.getDate() + Number(topDays || 0));
        dueDate.value = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    }

    async function load() {
        requestController?.abort();
        requestController = new AbortController();
        document.getElementById('invoiceRows').setAttribute('aria-busy', 'true');
        document.getElementById('alertBox').innerHTML = '';
        tbody.innerHTML = '<tr><td colspan="14" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Memuat Surat Jalan...</td></tr>';
        const params = new URLSearchParams({page, per_page: rowsPerPage});
        if (search.value.trim()) params.set('q', search.value.trim());
        if (dateFrom.value) params.set('date_from', dateFrom.value);
        if (dateTo.value) params.set('date_to', dateTo.value);
        try {
            const response = await fetch(`${url}?${params}`, {headers: {Accept: 'application/json'}, signal: requestController.signal});
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Data Surat Jalan tidak dapat dimuat.');
            render(payload.data, payload.meta);
        } catch (error) {
            if (error.name === 'AbortError') return;
            tbody.innerHTML = '<tr><td colspan="14" class="text-center py-5 text-danger">Data gagal dimuat.</td></tr>';
            document.getElementById('alertBox').innerHTML = `<div class="alert alert-danger" role="alert">${esc(error.message)}</div>`;
        } finally {
            document.getElementById('invoiceRows').setAttribute('aria-busy', 'false');
        }
    }

    tbody.addEventListener('change', event => {
        if (!event.target.matches('.sj-choice')) return;
        const nextRow = JSON.parse(event.target.dataset.row);
        if (selectedRow?.NOSJ !== nextRow.NOSJ) paymentInput.value = '0';
        selectedRow = nextRow;
        tbody.querySelectorAll('tr').forEach(row => row.classList.toggle('table-primary', row.contains(event.target)));
        updateSelection();
    });
    paymentInput.addEventListener('input', updateBalance);
    search.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { page = 1; load(); }, 300);
    });
    [dateFrom, dateTo].forEach(input => input.addEventListener('change', () => { page = 1; load(); }));
    document.getElementById('refreshRows').addEventListener('click', load);
    document.getElementById('clearFilters').addEventListener('click', () => {
        search.value = ''; dateFrom.value = ''; dateTo.value = ''; page = 1; load();
    });
    document.getElementById('previousPage').addEventListener('click', () => { if (page > 1) { page--; load(); } });
    document.getElementById('nextPage').addEventListener('click', () => { if (page < lastPage) { page++; load(); } });
    document.getElementById('clearSelection').addEventListener('click', () => { selectedRow = null; updateSelection(); });
    document.querySelector('[name="TGLFAKTUR"]').addEventListener('change', () => {
        if (selectedRow) setDueDate(topInput.value);
    });
    topInput.addEventListener('input', () => { if (selectedRow) setDueDate(topInput.value); });
    document.getElementById('invoiceForm').addEventListener('submit', event => {
        if (!selectedRow) { event.preventDefault(); return; }
        const detail = selectedRow.GB ? `SJ ${selectedRow.GROUP_SJS.join(', ')} pada GB ${selectedRow.GB}` : `SJ ${selectedRow.NOSJ}`;
        const paid = Number(paymentInput.value || 0);
        const balance = Math.max(0, Number(selectedRow.GRAND) - paid);
        if (!window.confirm(`Simpan invoice ${detail} dengan nilai ${money(selectedRow.GRAND)}, pembayaran awal ${money(paid)}, dan sisa piutang ${money(balance)}?`)) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membuat invoice...';
    });
    document.querySelector('[name="tanggal_dari"]').addEventListener('change', event => {
        document.querySelector('[name="tanggal_sampai"]').min = event.target.value;
    });
    document.getElementById('exportCustomer').addEventListener('input', event => {
        clearTimeout(customerTimer);
        customerTimer = setTimeout(async () => {
            const q = event.target.value.trim();
            if (q.length < 2) return;
            customerController?.abort();
            customerController = new AbortController();
            try {
                const response = await fetch(`${customerLookupUrl}?q=${encodeURIComponent(q)}`, {
                    headers: {Accept: 'application/json'}, signal: customerController.signal,
                });
                if (!response.ok) return;
                const customers = await response.json();
                const options = document.getElementById('invoiceCustomerOptions');
                options.replaceChildren(...customers.map(customer => {
                    const option = document.createElement('option');
                    option.value = customer.code;
                    option.label = customer.name;
                    return option;
                }));
            } catch (error) {
                if (error.name !== 'AbortError') console.error('Customer lookup gagal.', error);
            }
        }, 250);
    });
    load();
})();
</script>
@endpush
