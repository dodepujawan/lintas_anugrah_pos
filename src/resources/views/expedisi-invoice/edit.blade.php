@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiExpedisiInvoiceEditList">
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Edit Invoice Expedisi</h1>
            <p class="text-muted small mb-0">Pilih invoice untuk dikoreksi nilai maupun tanggalnya</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('expedisi-invoice.index') }}" id="btnKembaliInvoiceEdit" data-debug="transaksiExpedisiInvoiceEditBack" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <form method="GET" id="formFilterInvoiceEdit" data-debug="transaksiExpedisiInvoiceEditFilter" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-lg-2 col-md-3">
                <label class="form-label small mb-1" for="tanggal_dari_invoice_eks">DARI</label>
                <input type="date" name="tanggal_dari" id="tanggal_dari_invoice_eks" class="form-control form-control-sm" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-lg-2 col-md-3">
                <label class="form-label small mb-1" for="tanggal_sampai_invoice_eks">SAMPAI</label>
                <input type="date" name="tanggal_sampai" id="tanggal_sampai_invoice_eks" class="form-control form-control-sm" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-lg-4 col-md-4">
                <label class="form-label small mb-1" for="search_invoice_eks">CARI</label>
                <input type="text" name="search" id="search_invoice_eks" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Invoice / Customer">
            </div>
            <div class="col-lg-4 col-md-2">
                <div class="input-group input-group-sm">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Reload</button>
                    <a href="{{ route('expedisi-invoice-gen.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="table_invoice_eks">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tgl Invoice</th>
                        <th>No. Invoice</th>
                        <th>Customer</th>
                        <th class="text-end">GRAND</th>
                        <th class="text-end">Piutang</th>
                        <th class="text-end">Bayar</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td>{{ $invoices->firstItem() + $loop->index }}</td>
                        <td>{{ $inv->TGLFAKTUR?->format('d/m/Y') }}</td>
                        <td class="font-monospace">{{ $inv->NOFAKTUR }}</td>
                        <td>{{ $inv->CUSTOMER }}</td>
                        <td class="text-end">{{ number_format((float) $inv->display_grand, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $inv->display_piutang, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $inv->display_bayar, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-warning btn_edit_invoice_eks" data-invoice="{{ $inv->NOFAKTUR }}" id="btnEditInvoice_{{ $inv->NOFAKTUR }}" data-debug="transaksiExpedisiInvoiceEditBtn_{{ $inv->NOFAKTUR }}" title="Edit Invoice">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="{{ route('expedisi-invoice.pdfGabungInvoice', $inv->NOFAKTUR) }}" id="btnPrintInvoiceEdit_{{ $inv->NOFAKTUR }}" data-debug="transaksiExpedisiInvoiceEditPrint_{{ $inv->NOFAKTUR }}" class="btn btn-sm btn-outline-primary" title="Print" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data invoice.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $invoices->links() }}</div>
</div>

{{-- ===================================================== --}}
{{-- MODAL EDIT INVOICE --}}
{{-- ===================================================== --}}
<div class="modal fade" id="modal_invoice_edit" tabindex="-1" data-debug="transaksiExpedisiInvoiceEditModal">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title fw-bold mb-0">EDIT INVOICE EXPEDISI</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light p-2">
                <div id="modalInvoiceEditAlert" class="alert alert-danger d-none py-2 mb-2"></div>
                <div class="row g-2">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-2">
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">INVOICE</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="invoice_invoice_edit" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">CUSTOMER</label>
                                    <input type="text" id="customer_invoice_edit_kd" hidden>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="customer_invoice_edit" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">KENDARAAN</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="kendaraan_invoice_edit" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">DRIVER</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="driver_invoice_edit" readonly>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold mb-1">NO MUAT</label>
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="nomuat_invoice_edit" readonly>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1">TGL INV</label>
                                        <input type="date" class="form-control form-control-sm" id="tgl_invoice_invoice_edit">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1">TGL JT</label>
                                        <input type="date" class="form-control form-control-sm" id="tgl_jt_invoice_edit">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1">BAYAR</label>
                                        <input type="text" class="form-control form-control-sm text-end fw-bold bg-success-subtle" id="bayar_invoice_edit">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold mb-1">PIUTANG</label>
                                        <input type="text" class="form-control form-control-sm text-end fw-bold bg-warning-subtle" id="piutang_invoice_edit" readonly>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end">
                                    <button class="btn btn-success btn-sm w-100" id="btn_simpan_invoice_edit" data-debug="transaksiExpedisiInvoiceEditSave">
                                        <i class="bi bi-save"></i> SIMPAN
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">WILAYAH</label>
                                <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="wilayah_invoice_edit" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">ITEM</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="item_invoice_edit" readonly>
                                    <button class="btn btn-primary" type="button" id="btnItemGabungExpEdit" data-debug="transaksiExpedisiInvoiceEditItemPick"><i class="bi bi-search"></i></button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">RUTE</label>
                                <input type="text" class="form-control form-control-sm bg-secondary-subtle" id="rute_invoice_edit" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">JUMLAH</label>
                                <input type="text" class="form-control form-control-sm text-end bg-secondary-subtle" id="jumlah_invoice_edit">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">HARGA</label>
                                <input type="text" class="form-control form-control-sm text-end" id="harga_invoice_edit">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">SUBTOTAL</label>
                                <input type="text" class="form-control form-control-sm text-end" id="subtotal_invoice_edit" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">DISC %</label>
                                <input type="text" class="form-control form-control-sm text-end" id="diskon_invoice_edit">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">TOTAL</label>
                                <input type="text" class="form-control form-control-sm text-end" id="total_invoice_edit" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">DEL.CHARGE</label>
                                <input type="text" class="form-control form-control-sm text-end" id="del_charge_invoice_edit">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">PPN %</label>
                                <input type="text" class="form-control form-control-sm text-end" id="ppn_invoice_edit">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small mb-0 d-block">GRAND</label>
                                <input type="text" class="form-control form-control-sm text-end fw-bold bg-danger-subtle" id="grand_invoice_edit">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1">KETERANGAN</label>
                                <textarea class="form-control form-control-sm" rows="2" id="keterangan_invoice_edit"></textarea>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div style="height:220px;overflow-y:auto;">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="table-success">
                                    <tr>
                                        <th>NO.SJ</th>
                                        <th class="text-end">JUMLAH</th>
                                        <th class="text-end">HARGA AW</th>
                                        <th class="text-end">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_detail_invoice_edit"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================================================== --}}
{{-- MODAL ITEM --}}
{{-- ===================================================== --}}
<div class="modal fade" id="itemModalInvEditExp" tabindex="-1" data-debug="transaksiExpedisiInvoiceEditItemModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div>
                    <h3 id="custNameInvExp" class="h5"></h3>
                    <h3 id="custKodeInvExp" class="h6 text-muted"></h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped w-100" id="modalItemInvExpEditTable">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>NAMA ITEM</th>
                                <th>DARI</th>
                                <th>SAMPAI</th>
                                <th>RUTE</th>
                                <th>HARGA</th>
                                <th>JENIS</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const showUrlTemplate = @json(route('expedisi-invoice-gen.show', ['noInvoice' => '__INVOICE__']));
    const updateUrlTemplate = @json(route('expedisi-invoice-gen.update', ['noInvoice' => '__INVOICE__']));
    const priceUrlTemplate = @json(route('price-customer.price', ['kodecus' => '__KODECUS__']));

    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const attr = value => String(value ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');

    function parseNominal(value) {
        const text = String(value ?? '').trim();
        const sign = text.startsWith('-') ? -1 : 1;
        const digits = text.replace(/\D/g, '');
        return sign * (parseInt(digits, 10) || 0);
    }

    function hitungInvoiceEdit() {
        const jumlah = parseFloat(document.getElementById('jumlah_invoice_edit').value) || 0;
        const harga = parseNominal(document.getElementById('harga_invoice_edit').value);
        const disc = parseFloat(document.getElementById('diskon_invoice_edit').value) || 0;
        const delCharge = parseNominal(document.getElementById('del_charge_invoice_edit').value);
        const ppnPersen = parseFloat(document.getElementById('ppn_invoice_edit').value) || 0;
        const bayar = parseNominal(document.getElementById('bayar_invoice_edit').value);

        const subTotal = Math.round(jumlah * harga);
        const discAmount = Math.round(subTotal * (disc / 100));
        const total = Math.round(subTotal - discAmount);
        const ppnNominal = Math.round(total * (ppnPersen / 100));
        const grand = Math.round(total + ppnNominal + delCharge);
        const piutang = Math.max(0, Math.round(grand - bayar));

        const fmt = (n) => n.toLocaleString('id-ID');
        document.getElementById('subtotal_invoice_edit').value = fmt(subTotal);
        document.getElementById('total_invoice_edit').value = fmt(total);
        document.getElementById('grand_invoice_edit').value = fmt(grand);
        document.getElementById('piutang_invoice_edit').value = fmt(piutang);
    }

    function showAlert(message) {
        const alertBox = document.getElementById('modalInvoiceEditAlert');
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
    }

    function clearAlert() {
        document.getElementById('modalInvoiceEditAlert').classList.add('d-none');
    }

    function showModal(id) {
        const el = document.getElementById(id);
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    }

    function hideModal(id) {
        const el = document.getElementById(id);
        if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
    }

    function clearFormInvoiceEdit() {
        ['invoice_invoice_edit', 'customer_invoice_edit_kd', 'customer_invoice_edit',
            'kendaraan_invoice_edit', 'driver_invoice_edit', 'nomuat_invoice_edit',
            'tgl_invoice_invoice_edit', 'tgl_jt_invoice_edit', 'wilayah_invoice_edit',
            'item_invoice_edit', 'rute_invoice_edit', 'jumlah_invoice_edit', 'harga_invoice_edit',
            'subtotal_invoice_edit', 'total_invoice_edit', 'diskon_invoice_edit',
            'del_charge_invoice_edit', 'ppn_invoice_edit', 'grand_invoice_edit',
            'bayar_invoice_edit', 'piutang_invoice_edit', 'keterangan_invoice_edit'
        ].forEach(id => { document.getElementById(id).value = ''; });
        document.getElementById('tbody_detail_invoice_edit').innerHTML = '';
    }

    // =====================================================
    // TAMPIL INVOICE DI MODAL
    // =====================================================
    document.addEventListener('click', async function (event) {
        const btn = event.target.closest('.btn_edit_invoice_eks');
        if (!btn) return;

        const invoice = btn.dataset.invoice;
        clearAlert();
        try {
            const response = await fetch(showUrlTemplate.replace('__INVOICE__', encodeURIComponent(invoice)), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (!response.ok || !payload.status) {
                showAlert(payload.message || 'Invoice tidak dapat dimuat.');
                return;
            }
            clearFormInvoiceEdit();
            const d = payload.master;
            const set = (id, value) => { document.getElementById(id).value = value ?? ''; };
            set('invoice_invoice_edit', d.invoice);
            set('customer_invoice_edit_kd', d.customer_kode);
            set('customer_invoice_edit', d.customer);
            set('kendaraan_invoice_edit', d.kendaraan);
            set('driver_invoice_edit', d.driver);
            set('nomuat_invoice_edit', d.nomuat);
            set('tgl_invoice_invoice_edit', (d.tgl_invoice || '').substring(0, 10));
            set('tgl_jt_invoice_edit', (d.tgl_jt || '').substring(0, 10));
            set('wilayah_invoice_edit', d.wilayah);
            set('item_invoice_edit', d.item);
            set('rute_invoice_edit', d.rute);
            set('jumlah_invoice_edit', parseFloat(d.jumlah));
            set('harga_invoice_edit', d.harga);
            set('diskon_invoice_edit', parseFloat(d.diskon));
            set('del_charge_invoice_edit', d.del_charge);
            set('ppn_invoice_edit', parseFloat(d.ppn));
            set('grand_invoice_edit', d.grand);
            set('bayar_invoice_edit', d.bayar);
            set('piutang_invoice_edit', d.piutang);
            set('keterangan_invoice_edit', d.keterangan);

            document.getElementById('tbody_detail_invoice_edit').innerHTML = (payload.details || []).map(row => `
                <tr>
                    <td>${esc(row.nosj)}</td>
                    <td class="text-end">${parseFloat(row.jumlah ?? 0).toLocaleString()}</td>
                    <td class="text-end">${parseFloat(row.hargaaw ?? 0).toLocaleString()}</td>
                    <td class="text-end fw-bold">${parseFloat(row.total ?? 0).toLocaleString()}</td>
                </tr>
            `).join('');

            hitungInvoiceEdit();
            showModal('modal_invoice_edit');
        } catch (e) {
            showAlert('Terjadi kesalahan: ' + e.message);
        }
    });

    // =====================================================
    // ITEM PICKER
    // =====================================================
    document.addEventListener('click', async function (event) {
        if (!event.target.closest('#btnItemGabungExpEdit')) return;

        const kodecus = document.getElementById('customer_invoice_edit_kd').value;
        if (!kodecus || kodecus.trim() === '') {
            showAlert('Silahkan Pilih Customer!');
            return;
        }
        clearAlert();
        document.getElementById('modalItemInvExpEditTable').querySelector('tbody').innerHTML =
            '<tr><td colspan="8" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Memuat...</td></tr>';
        showModal('itemModalInvEditExp');

        try {
            const response = await fetch(priceUrlTemplate.replace('__KODECUS__', encodeURIComponent(kodecus.trim())), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const rows = await response.json();
            if (!response.ok) throw new Error('Data harga customer gagal dimuat.');

            document.getElementById('custNameInvExp').textContent = kodecus.trim();
            document.getElementById('custKodeInvExp').textContent = kodecus.trim();
            document.getElementById('modalItemInvExpEditTable').querySelector('tbody').innerHTML = rows.length ? rows.map((row, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>${esc(row.KETERANGAN)}</td>
                    <td>${esc(row.DARI)}</td>
                    <td>${esc(row.SAMPAI)}</td>
                    <td>${esc(row.RUTE)}</td>
                    <td>${Number(row.HARGA ?? 0).toLocaleString()}</td>
                    <td>${Number(row.JENIS) == 1 ? 'Eceran' : 'Booking'}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-success btn-sm pick-price-exp"
                            data-item="${attr(row.KETERANGAN)}"
                            data-rute="${attr(row.RUTE)}"
                            data-harga="${Number(row.HARGA ?? 0)}">
                            <i class="bi bi-check-lg"></i>
                        </button>
                    </td>
                </tr>
            `).join('') : '<tr><td colspan="8" class="text-center py-3 text-muted">Tidak ada harga customer.</td></tr>';
        } catch (e) {
            document.getElementById('modalItemInvExpEditTable').querySelector('tbody').innerHTML =
                '<tr><td colspan="8" class="text-center py-3 text-danger">Data gagal dimuat.</td></tr>';
        }
    });

    document.addEventListener('click', function (event) {
        const btn = event.target.closest('.pick-price-exp');
        if (!btn) return;
        document.getElementById('item_invoice_edit').value = btn.dataset.item || '';
        document.getElementById('rute_invoice_edit').value = btn.dataset.rute || '';
        document.getElementById('harga_invoice_edit').value = btn.dataset.harga || '';
        hitungInvoiceEdit();
        hideModal('itemModalInvEditExp');
    });

    // =====================================================
    // KALKULASI LIVE
    // =====================================================
    ['jumlah_invoice_edit', 'harga_invoice_edit', 'diskon_invoice_edit', 'del_charge_invoice_edit', 'ppn_invoice_edit', 'bayar_invoice_edit']
        .forEach(id => {
            const field = document.getElementById(id);
            field.addEventListener('input', hitungInvoiceEdit);
            field.addEventListener('change', hitungInvoiceEdit);
        });

    // =====================================================
    // SIMPAN
    // =====================================================
    document.getElementById('btn_simpan_invoice_edit').addEventListener('click', async function () {
        const invoice = document.getElementById('invoice_invoice_edit').value;
        if (!invoice) {
            showAlert('Invoice tidak ditemukan');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>MENYIMPAN...';
        clearAlert();

        try {
            const url = updateUrlTemplate.replace('__INVOICE__', encodeURIComponent(invoice));
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    TGLFAKTUR: document.getElementById('tgl_invoice_invoice_edit').value,
                    TGLJT: document.getElementById('tgl_jt_invoice_edit').value,
                    KETERANGAN: document.getElementById('keterangan_invoice_edit').value,
                    item: document.getElementById('item_invoice_edit').value,
                    rute: document.getElementById('rute_invoice_edit').value,
                    jumlah: document.getElementById('jumlah_invoice_edit').value,
                    harga: parseNominal(document.getElementById('harga_invoice_edit').value),
                    disc: document.getElementById('diskon_invoice_edit').value,
                    del_charge: parseNominal(document.getElementById('del_charge_invoice_edit').value),
                    ppn: document.getElementById('ppn_invoice_edit').value,
                    bayar: parseNominal(document.getElementById('bayar_invoice_edit').value),
                }),
            });
            const payload = await response.json();
            if (!response.ok || payload.status === false) {
                showAlert(payload.message || 'Terjadi kesalahan');
                return;
            }
            hideModal('modal_invoice_edit');
            window.location.reload();
        } catch (e) {
            showAlert('Terjadi kesalahan: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> SIMPAN';
        }
    });
})();
</script>
@endpush
