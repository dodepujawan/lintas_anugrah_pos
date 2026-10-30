@extends('layouts.app')

@section('title', 'Edit Invoice Rent Pendingin')

@section('content')
<div class="container-fluid" data-debug="transaksiRentPendinginInvoiceEditList">
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-semibold text-warning mb-1">Transaksi / Rent Pendingin</div>
            <h1 class="page-title mb-1">Edit Rincian Invoice Total</h1>
            <p class="text-muted small mb-0">Pilih invoice untuk dikoreksi. Invoice yang sudah memiliki pembayaran tidak dapat diedit.</p>
        </div>
        <a href="{{ route('rent-pendingin-invoice.index') }}" class="btn btn-outline-secondary btn-sm" data-debug="transaksiRentPendinginInvoiceEditBack">
            <i class="bi bi-arrow-left me-1"></i>Generate Invoice
        </a>
    </div>

    <form method="GET" id="rentInvoiceEditFilter" data-debug="transaksiRentPendinginInvoiceEditFilter" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label for="rentInvoiceEditSearch" class="form-label small fw-semibold">Cari Invoice / Customer</label>
                <input type="search" id="rentInvoiceEditSearch" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="Nomor invoice atau customer">
            </div>
            <div class="col-lg-2 col-md-3">
                <label class="form-label small fw-semibold" for="rentInvoiceEditFrom">DARI</label>
                <input type="date" id="rentInvoiceEditFrom" name="tanggal_dari" class="form-control form-control-sm" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-lg-2 col-md-3">
                <label class="form-label small fw-semibold" for="rentInvoiceEditTo">SAMPAI</label>
                <input type="date" id="rentInvoiceEditTo" name="tanggal_sampai" class="form-control form-control-sm" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-lg-5 d-flex gap-2">
                <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i>Reload</button>
                <a href="{{ route('rent-pendingin-invoice-gen.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="rentInvoiceEditTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Tgl Invoice</th>
                        <th>No. Invoice</th>
                        <th>No. SJ / No. Muat</th>
                        <th>Customer</th>
                        <th class="text-end">Grand</th>
                        <th class="text-end">Piutang</th>
                        <th class="text-end">Bayar</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="ps-3">{{ $invoices->firstItem() + $loop->index }}</td>
                            <td>{{ $inv->TGLINVOICE?->format('d/m/Y') ?? '-' }}</td>
                            <td class="font-monospace fw-semibold">{{ $inv->INVOICE }}</td>
                            <td><div class="font-monospace">{{ $inv->NOSJ }}</div><div class="small text-muted">{{ $inv->NOMUAT }}</div></td>
                            <td>{{ $inv->CUSTOMER }}</td>
                            <td class="text-end">{{ number_format((float) $inv->GRAND, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) $inv->PIUTANG, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) (($inv->GRAND ?? 0) - ($inv->PIUTANG ?? 0)), 0, ',', '.') }}</td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-warning btn-edit-rent-invoice" data-invoice="{{ $inv->INVOICE }}" data-debug="transaksiRentPendinginInvoiceEditBtn_{{ $inv->INVOICE }}" title="Edit Invoice">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Tidak ada invoice Rent Pendingin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $invoices->links() }}</div>
</div>

<div class="modal fade" id="rentInvoiceEditModal" tabindex="-1" aria-labelledby="rentInvoiceEditModalTitle" aria-hidden="true" data-debug="transaksiRentPendinginInvoiceEditModal">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-2">
                <h2 class="modal-title fs-5 fw-bold mb-0" id="rentInvoiceEditModalTitle">EDIT INVOICE RENT PENDINGIN</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body bg-light p-2">
                <div id="rentInvoiceEditAlert" class="alert alert-danger d-none py-2 mb-2" role="alert"></div>
                <div class="row g-2">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-2">
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1" for="rentEditInvoice">INVOICE</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle font-monospace" id="rentEditInvoice" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1" for="rentEditCustomer">CUSTOMER</label>
                                    <input type="hidden" id="rentEditCustomerCode">
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="rentEditCustomer" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1" for="rentEditVehicle">KENDARAAN</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="rentEditVehicle" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1" for="rentEditDriver">DRIVER</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="rentEditDriver" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1" for="rentEditMuat">NO MUAT / SURAT JALAN</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle font-monospace" id="rentEditMuat" readonly>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1" for="rentEditInvoiceDate">TGL INV</label>
                                        <input type="date" class="form-control form-control-sm bg-secondary-subtle" id="rentEditInvoiceDate" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1" for="rentEditDueDate">TGL JT</label>
                                        <input type="date" class="form-control form-control-sm" id="rentEditDueDate">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1" for="rentEditPayment">BAYAR</label>
                                        <input type="number" min="0" step="1" class="form-control form-control-sm text-end fw-bold bg-success-subtle" id="rentEditPayment">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1" for="rentEditReceivable">PIUTANG</label>
                                        <input type="text" class="form-control form-control-sm text-end fw-bold bg-warning-subtle" id="rentEditReceivable" readonly>
                                    </div>
                                </div>
                                <button class="btn btn-success btn-sm w-100" id="rentInvoiceEditSave" type="button" data-debug="transaksiRentPendinginInvoiceEditSave">
                                    <i class="bi bi-save me-1"></i>SIMPAN
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-2">
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold small mb-1" for="rentEditItem">ITEM / PESANAN</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control bg-secondary-subtle" id="rentEditItem" readonly>
                                            <button class="btn btn-primary" type="button" id="rentEditPickItem" data-debug="transaksiRentPendinginInvoiceEditItemPick" title="Pilih item dan harga customer"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small mb-1" for="rentEditUnit">UNIT</label>
                                        <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="rentEditUnit" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small mb-1" for="rentEditQuantity">JUMLAH</label>
                                        <input type="number" min="0" step="0.001" class="form-control form-control-sm text-end" id="rentEditQuantity">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small mb-1" for="rentEditPrice">HARGA</label>
                                        <input type="number" min="0" step="1" class="form-control form-control-sm text-end" id="rentEditPrice">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small mb-1">SUBTOTAL</label>
                                        <input type="text" class="form-control form-control-sm text-end bg-secondary-subtle" id="rentEditSubtotal" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small mb-1" for="rentEditDiscount">DISC %</label>
                                        <input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm text-end" id="rentEditDiscount">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small mb-1">TOTAL</label>
                                        <input type="text" class="form-control form-control-sm text-end bg-secondary-subtle" id="rentEditTotal" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small mb-1" for="rentEditDelivery">DEL. CHARGE</label>
                                        <input type="number" min="0" step="1" class="form-control form-control-sm text-end" id="rentEditDelivery">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small mb-1" for="rentEditPpn">PPN %</label>
                                        <input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm text-end" id="rentEditPpn">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small mb-1">GRAND</label>
                                        <input type="text" class="form-control form-control-sm text-end fw-bold bg-danger-subtle" id="rentEditGrand" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small mb-1" for="rentEditNote">KETERANGAN</label>
                                        <textarea class="form-control form-control-sm" rows="2" maxlength="500" id="rentEditNote"></textarea>
                                    </div>
                                </div>
                                <div class="small text-danger d-none mt-2" id="rentEditValidation" role="alert">Bayar tidak boleh lebih besar dari Grand.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rentPricePickerModal" tabindex="-1" aria-labelledby="rentPricePickerTitle" aria-hidden="true" data-debug="transaksiRentPendinginInvoiceEditPriceModal">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5" id="rentPricePickerTitle">Harga Sewa Customer</h2>
                    <div class="small text-muted" id="rentPricePickerCustomer"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="rentPricePickerSearch" class="row g-2 mb-3">
                    <div class="col-md-8"><label class="visually-hidden" for="rentPricePickerQuery">Cari item</label><input type="search" id="rentPricePickerQuery" class="form-control" placeholder="Cari kendaraan, item, periode, atau plat"></div>
                    <div class="col-md-4"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i>Cari Harga</button></div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle" id="rentPricePickerTable">
                        <thead class="table-light"><tr><th>No</th><th>Kendaraan</th><th>Item</th><th>Periode</th><th>Plat</th><th>Jenis</th><th class="text-end">Harga</th><th>Aksi</th></tr></thead>
                        <tbody><tr><td colspan="8" class="text-center text-muted py-4">Pilih item untuk memuat harga customer.</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const showUrlTemplate = @json(route('rent-pendingin-invoice-gen.show', ['noInvoice' => '__INVOICE__']));
    const updateUrlTemplate = @json(route('rent-pendingin-invoice-gen.update', ['noInvoice' => '__INVOICE__']));
    const priceUrlTemplate = @json(route('rent-pendingin-invoice-gen.prices', ['kodecus' => '__KODECUS__']));
    const money = value => Number(value || 0).toLocaleString('id-ID');
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char]));
    const attr = value => String(value ?? '').replace(/[&"<>]/g, char => ({'&': '&amp;', '"': '&quot;', '<': '&lt;', '>': '&gt;'}[char]));
    const value = id => document.getElementById(id).value;
    const set = (id, next) => { document.getElementById(id).value = next ?? ''; };
    const editModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('rentInvoiceEditModal'));
    const priceModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('rentPricePickerModal'));

    function showAlert(message) {
        const alertBox = document.getElementById('rentInvoiceEditAlert');
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
    }

    function clearAlert() {
        const alertBox = document.getElementById('rentInvoiceEditAlert');
        alertBox.textContent = '';
        alertBox.classList.add('d-none');
    }

    function responseMessage(payload, fallback) {
        return payload?.message || Object.values(payload?.errors || {}).flat()[0] || fallback;
    }

    function updateSummary() {
        const quantity = Number(value('rentEditQuantity') || 0);
        const price = Number(value('rentEditPrice') || 0);
        const discount = Number(value('rentEditDiscount') || 0);
        const delivery = Number(value('rentEditDelivery') || 0);
        const ppn = Number(value('rentEditPpn') || 0);
        const payment = Number(value('rentEditPayment') || 0);
        const subtotal = Math.round(quantity * price);
        const discountValue = Math.round(subtotal * discount / 100);
        const total = Math.round(subtotal - discountValue);
        const ppnValue = Math.round(total * ppn / 100);
        const grand = Math.round(total + ppnValue + delivery);

        set('rentEditSubtotal', money(subtotal));
        set('rentEditTotal', money(total));
        set('rentEditGrand', money(grand));
        set('rentEditReceivable', money(Math.max(0, grand - payment)));
        document.getElementById('rentEditValidation').classList.toggle('d-none', payment <= grand);
        document.getElementById('rentInvoiceEditSave').disabled = payment > grand;
    }

    function renderSavedSummary(master) {
        set('rentEditSubtotal', money(master.subtotal));
        set('rentEditTotal', money(master.total));
        set('rentEditGrand', money(master.grand));
        set('rentEditReceivable', money(master.piutang));
        const invalid = Number(master.bayar || 0) > Number(master.grand || 0);
        document.getElementById('rentEditValidation').classList.toggle('d-none', !invalid);
        document.getElementById('rentInvoiceEditSave').disabled = invalid;
    }

    function clearForm() {
        [
            'rentEditInvoice', 'rentEditCustomerCode', 'rentEditCustomer', 'rentEditVehicle',
            'rentEditDriver', 'rentEditMuat', 'rentEditInvoiceDate', 'rentEditDueDate',
            'rentEditPayment', 'rentEditItem', 'rentEditUnit', 'rentEditQuantity',
            'rentEditPrice', 'rentEditDiscount', 'rentEditDelivery', 'rentEditPpn', 'rentEditNote',
        ].forEach(id => set(id, ''));
        ['rentEditSubtotal', 'rentEditTotal', 'rentEditGrand', 'rentEditReceivable'].forEach(id => set(id, ''));
        document.getElementById('rentInvoiceEditSave').disabled = true;
        document.getElementById('rentInvoiceEditSave').innerHTML = '<i class="bi bi-save me-1"></i>SIMPAN';
        document.getElementById('rentEditValidation').classList.add('d-none');
    }

    document.addEventListener('click', async event => {
        const button = event.target.closest('.btn-edit-rent-invoice');
        if (!button) return;

        const invoice = button.dataset.invoice;
        clearAlert();
        clearForm();
        editModal.show();
        try {
            const response = await fetch(showUrlTemplate.replace('__INVOICE__', encodeURIComponent(invoice)), {
                headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            });
            const payload = await response.json();
            if (!response.ok || !payload.status) {
                showAlert(responseMessage(payload, 'Invoice tidak dapat dimuat.'));
                return;
            }

            const master = payload.master;
            set('rentEditInvoice', master.invoice);
            set('rentEditCustomerCode', master.customer_kode);
            set('rentEditCustomer', master.customer);
            set('rentEditVehicle', master.kendaraan);
            set('rentEditDriver', master.driver);
            set('rentEditMuat', `${master.nomuat || '-'} / ${master.nosj || '-'}`);
            set('rentEditInvoiceDate', master.tgl_invoice);
            set('rentEditDueDate', master.tgl_jt);
            set('rentEditPayment', master.bayar);
            set('rentEditItem', master.item);
            set('rentEditUnit', master.unit);
            set('rentEditQuantity', master.jumlah);
            set('rentEditPrice', master.harga);
            set('rentEditDiscount', master.disc);
            set('rentEditDelivery', master.del_charge);
            set('rentEditPpn', master.ppn);
            set('rentEditNote', master.keterangan);
            renderSavedSummary(master);
        } catch (error) {
            showAlert(`Gagal memuat invoice: ${error.message}`);
        }
    });

    async function loadPrices() {
        const kodecus = value('rentEditCustomerCode').trim();
        if (!kodecus) {
            showAlert('Kode customer invoice tidak ditemukan.');
            return;
        }

        const body = document.querySelector('#rentPricePickerTable tbody');
        body.innerHTML = '<tr><td colspan="8" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Memuat harga customer...</td></tr>';
        try {
            const params = new URLSearchParams();
            if (value('rentPricePickerQuery').trim()) params.set('q', value('rentPricePickerQuery').trim());
            const url = priceUrlTemplate.replace('__KODECUS__', encodeURIComponent(kodecus));
            const response = await fetch(`${url}?${params}`, {headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            const payload = await response.json();
            if (!response.ok) throw new Error(responseMessage(payload, 'Data harga customer gagal dimuat.'));

            document.getElementById('rentPricePickerCustomer').textContent = `${payload.customer_nama} (${payload.customer_kode})`;
            body.innerHTML = payload.data.length ? payload.data.map((row, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>${esc(row.nama_kendaraan)}</td>
                    <td>${esc(row.ITEM)}</td>
                    <td>${esc(row.PERIODE)}</td>
                    <td>${esc(row.PLAT)}</td>
                    <td>${esc(row.JENIS)}</td>
                    <td class="text-end">${money(row.HARGA)}</td>
                    <td><button type="button" class="btn btn-success btn-sm rent-price-pick" data-item="${attr(row.ITEM)}" data-unit="${attr(row.UNIT || '')}" data-harga="${Number(row.HARGA || 0)}"><i class="bi bi-check-lg me-1"></i>Pilih</button></td>
                </tr>`).join('') : '<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada harga sewa untuk customer ini.</td></tr>';
        } catch (error) {
            body.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">${esc(error.message)}</td></tr>`;
        }
    }

    document.getElementById('rentEditPickItem').addEventListener('click', () => {
        clearAlert();
        set('rentPricePickerQuery', '');
        priceModal.show();
        loadPrices();
    });

    document.getElementById('rentPricePickerSearch').addEventListener('submit', event => {
        event.preventDefault();
        loadPrices();
    });

    document.addEventListener('click', event => {
        const button = event.target.closest('.rent-price-pick');
        if (!button) return;
        set('rentEditItem', button.dataset.item);
        if (button.dataset.unit) set('rentEditUnit', button.dataset.unit);
        set('rentEditPrice', button.dataset.harga);
        updateSummary();
        priceModal.hide();
    });

    ['rentEditQuantity', 'rentEditPrice', 'rentEditDiscount', 'rentEditDelivery', 'rentEditPpn', 'rentEditPayment']
        .forEach(id => document.getElementById(id).addEventListener('input', updateSummary));

    document.getElementById('rentInvoiceEditSave').addEventListener('click', async event => {
        const button = event.currentTarget;
        const invoice = value('rentEditInvoice');
        if (!invoice) {
            showAlert('Invoice tidak ditemukan. Muat ulang daftar lalu coba kembali.');
            return;
        }

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>MENYIMPAN...';
        clearAlert();
        try {
            const response = await fetch(updateUrlTemplate.replace('__INVOICE__', encodeURIComponent(invoice)), {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    item: value('rentEditItem'),
                    jumlah: value('rentEditQuantity'),
                    harga: value('rentEditPrice'),
                    disc: value('rentEditDiscount'),
                    del_charge: value('rentEditDelivery'),
                    ppn: value('rentEditPpn'),
                    bayar: value('rentEditPayment'),
                    tgl_jt: value('rentEditDueDate'),
                    keterangan: value('rentEditNote'),
                }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.status) {
                showAlert(responseMessage(payload, 'Invoice gagal diperbarui.'));
                return;
            }
            window.location.href = payload.redirect_url;
        } catch (error) {
            showAlert(`Terjadi kesalahan saat menyimpan: ${error.message}`);
        } finally {
            button.innerHTML = '<i class="bi bi-save me-1"></i>SIMPAN';
            if (document.getElementById('rentInvoiceEditModal').classList.contains('show')) {
                updateSummary();
            }
        }
    });
})();
</script>
@endpush
