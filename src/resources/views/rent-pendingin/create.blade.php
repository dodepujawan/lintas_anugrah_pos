@extends('layouts.app')

@section('content')
<div class="container-fluid py-3" data-debug="transaksiRentPendinginCreateLegacy">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="page-title mb-1">Form Sewa Mobil Berpendingin</h1>
            <p class="text-muted small mb-0">Single-page transaction flow sesuai legacy</p>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#rentPendinginTutorialModal">
            <i class="bi bi-question-circle me-1"></i>Panduan
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" data-debug="transaksiRentPendinginCreateErrors">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form id="rentPendinginForm" method="POST" action="{{ route('rent-pendingin.store') }}" data-debug="transaksiRentPendinginCreateForm">
        @csrf

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">No. muat</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="no_muat_rent_dingin" name="NOMUAT" class="form-control" placeholder="Auto generate" readonly>
                            <button class="btn btn-outline-secondary" id="no_muat_rent_dingin_btn" type="button" aria-label="Pilih No Muat"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal muat</label>
                        <input type="date" id="tanggal_rent_dingin" name="TGLMUAT" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">No. surat jalan</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="no_surjal_rent_dingin" name="NOSJ" class="form-control" placeholder="Auto generate" readonly>
                            <button class="btn btn-outline-secondary" id="no_surjal_rent_dingin_btn" type="button" aria-label="Pilih No Surat Jalan"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal surat jalan</label>
                        <input type="date" id="tanggal_surjal_rent_dingin" name="tglsj" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="fw-semibold small text-uppercase text-secondary mb-3">Data customer</div>
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <div class="input-group input-group-sm">
                                <input type="hidden" id="customer_rent_dingin_id" name="CUSTOMER_KODE">
                                <input type="text" id="customer_rent_dingin" name="CUSTOMER" class="form-control" readonly placeholder="Pilih customer...">
                                <button class="btn btn-outline-secondary" id="customer_rent_dingin_btn" type="button" aria-label="Pilih customer"><i class="bi bi-search"></i></button>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span class="badge bg-light text-dark border">Kode</span>
                                <input type="text" id="customer_kode_dingin" class="form-control form-control-sm border-0 bg-transparent px-0" readonly>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama penerima</label>
                                <input type="text" id="nama_penerima_rent_dingin" name="P_NAMA" class="form-control form-control-sm" placeholder="Nama penerima">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telepon penerima</label>
                                <input type="text" id="telpon_rent_dingin" name="P_PHONE" class="form-control form-control-sm" placeholder="Nomor telepon">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Alamat penerima</label>
                            <textarea id="alamat_rent_dingin" name="P_ALAMAT" class="form-control form-control-sm" rows="2" placeholder="Alamat lengkap penerima"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="fw-semibold small text-uppercase text-secondary mb-3">Item & tarif</div>
                        <div class="mb-3">
                            <label class="form-label">Item</label>
                            <div class="input-group input-group-sm">
                                <input type="hidden" id="item_rent_dingin_id" name="item_rent_dingin_id">
                                <input type="text" id="item_rent_dingin" name="PESANAN" class="form-control" placeholder="Pilih item..." readonly>
                                <button class="btn btn-outline-secondary" id="item_rent_dingin_btn" type="button" aria-label="Pilih item"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Jumlah hari</label>
                                <input type="number" id="jml_hari_rent_dingin" name="JUMLAH" class="form-control form-control-sm" min="1" value="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Harga / hari</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="harga_rent_dingin" name="HARGA" class="form-control" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="fw-semibold small text-uppercase text-secondary mb-3">Driver & kendaraan</div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Driver</label>
                        <div class="input-group input-group-sm">
                            <input type="hidden" id="driver_rent_dingin_id" name="DRIVER">
                            <input type="text" id="driver_rent_dingin" name="NAMA_DRIVER" class="form-control" readonly placeholder="Pilih driver...">
                            <button class="btn btn-outline-secondary" id="driver_rent_dingin_btn" type="button" aria-label="Pilih driver"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kendaraan</label>
                        <div class="input-group input-group-sm">
                            <input type="hidden" id="kendaraan_rent_dingin_id" name="KENDARAAN">
                            <input type="text" id="kendaraan_rent_dingin" name="NAMA_KENDARAAN" class="form-control" readonly placeholder="Pilih kendaraan...">
                            <button class="btn btn-outline-secondary" id="kendaraan_rent_dingin_btn" type="button" aria-label="Pilih kendaraan"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="fw-semibold small text-uppercase text-secondary mb-3">Perhitungan biaya</div>
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Sub total</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="sub_total_rent_dingin" class="form-control" value="0" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Diskon</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">%</span>
                            <input type="number" id="discount_rent_dingin" name="DISC" class="form-control" value="0" min="0" max="100">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">DPP</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="dpp_rent_dingin" class="form-control" value="0" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Pajak</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">%</span>
                            <input type="text" id="pajak_rent_dingin" class="form-control" value="0" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Keterangan</label>
                        <textarea id="keterangan_rent_dingin" name="catatan" class="form-control form-control-sm" rows="1" style="resize:none;" placeholder="Keterangan tambahan"></textarea>
                    </div>
                    <div class="col-md-2">
                        <div class="border rounded p-2 bg-light">
                            <div class="small text-uppercase text-secondary">Total</div>
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <small class="text-muted">Rp</small>
                                <input type="text" id="total_rent_dingin" name="GRAND" class="form-control border-0 bg-transparent p-0 fw-semibold" value="0" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <div id="btnSimpanRentPendinginSurjalDiv">
                <button class="btn btn-primary btn-sm px-4" id="btnSimpanRentPendinginSurjal" type="submit">
                    <i class="bi bi-save me-1"></i>Simpan
                </button>
            </div>
            <button class="btn btn-outline-danger btn-sm px-4" id="btnClearRentPendinginSurjal" type="button">
                <i class="bi bi-trash me-1"></i>Clear
            </button>
            @if(auth()->user()?->isAdmin() ?? false)
            <div id="btnMuatRentPendinginDiv" class="d-none">
                <button class="btn btn-success btn-sm px-4" id="btnMuatRentPendingin" type="button">
                    <i class="bi bi-car-front me-1"></i>Proses no muat
                </button>
            </div>
            @endif
            <div id="divPrintSuratJalanRent" class="d-none">
                <button class="btn btn-warning btn-sm px-4" id="btnPrintSuratJalanRent" type="button" data-sj="">
                    <i class="bi bi-printer me-1"></i>Print surat jalan
                </button>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="customerModalRentDgn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="customerSearchRentDgn" class="form-control form-control-sm mb-3" placeholder="Cari kode atau nama customer...">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Kode</th><th>Nama Customer</th><th>Aksi</th></tr>
                        </thead>
                        <tbody id="customerRowsRentDgn"><tr><td colspan="3" class="text-center text-muted py-3">Memuat data...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itemModalRentDgn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Item Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="itemCustomerInfoRentDgn" class="small text-muted mb-2"></div>
                <input type="search" id="itemSearchRentDgn" class="form-control form-control-sm mb-3" placeholder="Cari item, periode, atau rute...">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Nama Item</th><th>Periode</th><th>Plt</th><th>Jenis</th><th>Harga</th><th>Aksi</th></tr>
                        </thead>
                        <tbody id="itemRowsRentDgn"><tr><td colspan="6" class="text-center text-muted py-3">Pilih customer dahulu.</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="driverModalRentDgn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="driverSearchRentDgn" class="form-control form-control-sm mb-3" placeholder="Cari kode atau nama driver...">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Kode</th><th>Nama Driver</th><th>Aksi</th></tr></thead>
                        <tbody id="driverRowsRentDgn"><tr><td colspan="3" class="text-center text-muted py-3">Memuat data...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="vehicleModalRentDgn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Kendaraan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="vehicleSearchRentDgn" class="form-control form-control-sm mb-3" placeholder="Cari kode, nama, atau plat...">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Plat</th><th>Aksi</th></tr></thead>
                        <tbody id="vehicleRowsRentDgn"><tr><td colspan="4" class="text-center text-muted py-3">Memuat data...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rentPendinginTutorialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Panduan Rent Pendingin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <ol class="mb-0 small">
                    <li>Pilih customer untuk membuka item dan tarif yang berlaku.</li>
                    <li>Pilih item agar jumlah hari dan harga otomatis terisi.</li>
                    <li>Isi driver dan kendaraan lalu simpan surat jalan.</li>
                    <li>Untuk update mode, pilih No Muat atau No SJ yang sudah ada.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="muatModalRentDgn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih No Muat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Filter</label>
                    <div class="row g-2">
                        <div class="col">
                            <input type="text" id="filter_rent_dgn" class="form-control form-control-sm" placeholder="Cari No Muat...">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm" id="btn_filter_rent_dgn" type="button">
                                <i class="bi bi-filter-right me-1"></i>Terapkan
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>No. Muat</th>
                                <th>Tgl Muat</th>
                                <th>Customer</th>
                                <th>Item</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Diskon</th>
                                <th>Grand Total</th>
                                <th>No. SJ</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="muatRowsRentDgn">
                            <tr>
                                <td colspan="11" class="text-center text-muted py-3">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="surjalModalRentPendingin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih No Surat Jalan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Filter</label>
                    <div class="row g-2">
                        <div class="col">
                            <input type="text" id="filter_surjal_rent_dgn" class="form-control form-control-sm" placeholder="Cari No Surat Jalan...">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm" id="btn_filter_surjal_rent_dgn" type="button">
                                <i class="bi bi-filter-right me-1"></i>Terapkan
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>No. SJ</th>
                                <th>Tgl SJ</th>
                                <th>Customer</th>
                                <th>Item</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Diskon</th>
                                <th>Grand Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="surjalRowsRentDgn">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">Memuat data...</td>
                            </tr>
                        </tbody>
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
    const form = document.getElementById('rentPendinginForm');
    const customerModal = new bootstrap.Modal(document.getElementById('customerModalRentDgn'));
    const itemModal = new bootstrap.Modal(document.getElementById('itemModalRentDgn'));
    const driverModal = new bootstrap.Modal(document.getElementById('driverModalRentDgn'));
    const vehicleModal = new bootstrap.Modal(document.getElementById('vehicleModalRentDgn'));
    const muatModal = new bootstrap.Modal(document.getElementById('muatModalRentDgn'));
    const surjalModal = new bootstrap.Modal(document.getElementById('surjalModalRentPendingin'));

    const formatRupiah = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

    function setButtonToSaveMode() {
        document.getElementById('btnSimpanRentPendinginSurjalDiv').classList.remove('d-none');
        const saveBtn = document.getElementById('btnSimpanRentPendinginSurjal');
        saveBtn.classList.remove('btn-success');
        saveBtn.classList.add('btn-primary');
        saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan';
        saveBtn.dataset.mode = 'save';

        const muat = document.getElementById('btnMuatRentPendinginDiv');
        if (muat) muat.classList.add('d-none');

        const printWrap = document.getElementById('divPrintSuratJalanRent');
        if (printWrap) printWrap.classList.add('d-none');
    }

    function setButtonToUpdateMode() {
        document.getElementById('btnSimpanRentPendinginSurjalDiv').classList.remove('d-none');
        const saveBtn = document.getElementById('btnSimpanRentPendinginSurjal');
        saveBtn.classList.remove('btn-primary');
        saveBtn.classList.add('btn-success');
        saveBtn.innerHTML = '<i class="bi bi-pencil-square me-1"></i>Update';
        saveBtn.dataset.mode = 'update';

        const muat = document.getElementById('btnMuatRentPendinginDiv');
        if (muat) muat.classList.remove('d-none');

        const printBtn = document.getElementById('btnPrintSuratJalanRent');
        if (printBtn && printBtn.dataset.sj) {
            const wrap = document.getElementById('divPrintSuratJalanRent');
            if (wrap) wrap.classList.remove('d-none');
        }
    }

    function dataModeRentDgn(mode) {
        if (mode === 'update') {
            setButtonToUpdateMode();
            return;
        }
        setButtonToSaveMode();
    }

    function calculateTotalDgn() {
        const quantity = Number(document.getElementById('jml_hari_rent_dingin').value || 0);
        const price = Number(String(document.getElementById('harga_rent_dingin').value || '0').replace(/[Rp.,\s]/g, ''));
        const discount = Number(document.getElementById('discount_rent_dingin').value || 0);
        const subtotal = quantity * price;
        const dpp = subtotal - (subtotal * (discount / 100));
        const total = dpp;

        document.getElementById('sub_total_rent_dingin').value = formatRupiah(subtotal);
        document.getElementById('dpp_rent_dingin').value = formatRupiah(dpp);
        document.getElementById('pajak_rent_dingin').value = '0';
        document.getElementById('total_rent_dingin').value = formatRupiah(total);
        document.getElementById('total_rent_dingin').setAttribute('value', String(total));
    }

    async function fetchJson(url) {
        const response = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'Permintaan gagal.');
        }
        return payload;
    }

    async function loadCustomers() {
        const q = document.getElementById('customerSearchRentDgn').value.trim();
        const payload = await fetchJson(`{{ route('expedisi.data.customers') }}?q=${encodeURIComponent(q)}`);
        const rows = payload.data || [];
        const tbody = document.getElementById('customerRowsRentDgn');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Data customer tidak ditemukan.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((row) => `
            <tr>
                <td>${row.CUSTOMER || '-'}</td>
                <td>${row.NAMACUST || '-'}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary pick-customer-rent-dingin" data-code="${row.CUSTOMER || ''}" data-name="${row.NAMACUST || ''}" data-alamat="${row.ALAMAT1 || ''}" data-phone="${row.TELEPON || ''}">Pilih</button>
                </td>
            </tr>
        `).join('');
    }

    async function loadItems() {
        const customerCode = document.getElementById('customer_rent_dingin_id').value;
        const q = document.getElementById('itemSearchRentDgn').value.trim();
        const tbody = document.getElementById('itemRowsRentDgn');
        if (!customerCode) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Pilih customer dahulu.</td></tr>';
            return;
        }

        const payload = await fetchJson(`{{ url('rent-pendingin/price-items') }}/${encodeURIComponent(customerCode)}?q=${encodeURIComponent(q)}`);
        const rows = payload.data || [];
        document.getElementById('itemCustomerInfoRentDgn').textContent = `${payload.customer_kode} — ${payload.customer_nama}`;
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada item untuk customer ini.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((row) => `
            <tr>
                <td>${row.ITEM || '-'}</td>
                <td>${row.PERIODE || '-'}</td>
                <td>${row.PLAT || '-'}</td>
                <td>${row.JENIS || '-'}</td>
                <td class="text-end">Rp ${formatRupiah(row.HARGA || 0)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary pick-item-rent-dingin" data-id="${row.id}" data-item="${row.ITEM || ''}" data-price="${row.HARGA || 0}">Pilih</button>
                </td>
            </tr>
        `).join('');
    }

    async function loadDrivers() {
        const q = document.getElementById('driverSearchRentDgn').value.trim();
        const payload = await fetchJson(`{{ route('expedisi.data.drivers') }}?q=${encodeURIComponent(q)}`);
        const rows = payload.data || [];
        const tbody = document.getElementById('driverRowsRentDgn');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada driver.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((row) => `
            <tr>
                <td>${row.code || '-'}</td>
                <td>${row.name || '-'}</td>
                <td><button type="button" class="btn btn-sm btn-primary pick-driver-rent-dingin" data-code="${row.code || ''}" data-name="${row.name || ''}">Pilih</button></td>
            </tr>
        `).join('');
    }

    async function loadVehicles() {
        const q = document.getElementById('vehicleSearchRentDgn').value.trim();
        const payload = await fetchJson(`{{ route('expedisi.data.vehicles') }}?q=${encodeURIComponent(q)}`);
        const rows = payload.data || [];
        const tbody = document.getElementById('vehicleRowsRentDgn');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada kendaraan.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((row) => `
            <tr>
                <td>${row.code || '-'}</td>
                <td>${row.name || '-'}</td>
                <td>${row.plat || '-'}</td>
                <td><button type="button" class="btn btn-sm btn-primary pick-vehicle-rent-dingin" data-code="${row.code || ''}" data-name="${row.name || ''}">Pilih</button></td>
            </tr>
        `).join('');
    }

    async function loadMuatRows() {
        const search = document.getElementById('filter_rent_dgn')?.value?.trim() || '';
        const tglMulai = document.getElementById('filter_tgl_mulai_rentdgn')?.value || '';
        const tglAkhir = document.getElementById('filter_tgl_akhir_rentdgn')?.value || '';
        const tbody = document.getElementById('muatRowsRentDgn');

        try {
            const payload = await fetchJson(`{{ route('rent-pendingin.data.muat') }}?search=${encodeURIComponent(search)}&tgl_mulai=${encodeURIComponent(tglMulai)}&tgl_akhir=${encodeURIComponent(tglAkhir)}`);
            const rows = payload.data || [];

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted py-3">Tidak ada data No Muat.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map((row, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${row.NOMUAT || '-'}</td>
                    <td>${row.TGLMUAT || '-'}</td>
                    <td>${row.CUSTOMER || '-'}</td>
                    <td>${row.PESANAN || '-'}</td>
                    <td>${row.JUMLAH || 0}</td>
                    <td>Rp ${formatRupiah(row.HARGA || 0)}</td>
                    <td>${row.DISC || 0}%</td>
                    <td>Rp ${formatRupiah(row.GRAND || 0)}</td>
                    <td>${row.NOSJ || '-'}</td>
                    <td><button type="button" class="btn btn-sm btn-primary pick-rent-muat" data-nomuat="${row.NOMUAT || ''}">Pilih</button></td>
                </tr>
            `).join('');
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center text-danger py-3">Gagal memuat data.</td></tr>';
            console.error(error);
        }
    }

    async function loadSurjalRows() {
        const search = document.getElementById('filter_surjal_rent_dgn')?.value?.trim() || '';
        const tglMulai = document.getElementById('filter_tgl_mulai')?.value || '';
        const tglAkhir = document.getElementById('filter_tgl_akhir')?.value || '';
        const tbody = document.getElementById('surjalRowsRentDgn');

        try {
            const payload = await fetchJson(`{{ route('rent-pendingin.data.surjal') }}?search=${encodeURIComponent(search)}&tgl_mulai=${encodeURIComponent(tglMulai)}&tgl_akhir=${encodeURIComponent(tglAkhir)}`);
            const rows = payload.data || [];

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">Tidak ada data Surat Jalan.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map((row, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${row.NOSJ || '-'}</td>
                    <td>${row.tglsj || '-'}</td>
                    <td>${row.CUSTOMER || '-'}</td>
                    <td>${row.PESANAN || '-'}</td>
                    <td>${row.JUMLAH || 0}</td>
                    <td>Rp ${formatRupiah(row.HARGA || 0)}</td>
                    <td>${row.DISC || 0}%</td>
                    <td>Rp ${formatRupiah(row.GRAND || 0)}</td>
                    <td><button type="button" class="btn btn-sm btn-primary pick-rent-surjal" data-nosj="${row.NOSJ || ''}">Pilih</button></td>
                </tr>
            `).join('');
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger py-3">Gagal memuat data.</td></tr>';
            console.error(error);
        }
    }

    document.getElementById('no_muat_rent_dingin_btn')?.addEventListener('click', async () => {
        await loadMuatRows();
        muatModal.show();
    });
    document.getElementById('no_surjal_rent_dingin_btn')?.addEventListener('click', async () => {
        await loadSurjalRows();
        surjalModal.show();
    });
    document.getElementById('btn_filter_rent_dgn')?.addEventListener('click', loadMuatRows);
    document.getElementById('btn_filter_surjal_rent_dgn')?.addEventListener('click', loadSurjalRows);

    document.addEventListener('click', async (event) => {
        const muatButton = event.target.closest('.pick-rent-muat');
        if (muatButton) {
            const nomuat = muatButton.dataset.nomuat;
            if (!nomuat) return;

            try {
                const payload = await fetchJson(`{{ url('rent-pendingin/show/muat') }}/${encodeURIComponent(nomuat)}`);
                const d = payload.data || {};
                muatModal.hide();

                document.getElementById('no_muat_rent_dingin').value = d.NOMUAT || '';
                document.getElementById('tanggal_rent_dingin').value = d.TGLMUAT || '{{ now()->format('Y-m-d') }}';
                document.getElementById('no_surjal_rent_dingin').value = d.NOSJ || '';
                document.getElementById('tanggal_surjal_rent_dingin').value = d.tglsj || '{{ now()->format('Y-m-d') }}';
                document.getElementById('customer_rent_dingin_id').value = d.CUSTOMER_KODE || '';
                document.getElementById('customer_rent_dingin').value = d.CUSTOMER || '';
                document.getElementById('customer_kode_dingin').value = d.CUSTOMER_KODE || '';
                document.getElementById('nama_penerima_rent_dingin').value = d.P_NAMA || '';
                document.getElementById('telpon_rent_dingin').value = d.P_PHONE || '';
                document.getElementById('alamat_rent_dingin').value = d.P_ALAMAT || '';
                document.getElementById('item_rent_dingin').value = d.PESANAN || '';
                document.getElementById('driver_rent_dingin_id').value = d.DRIVER || '';
                document.getElementById('driver_rent_dingin').value = d.NAMA_DRIVER || '';
                document.getElementById('kendaraan_rent_dingin_id').value = d.KENDARAAN || '';
                document.getElementById('kendaraan_rent_dingin').value = d.NAMA_KENDARAAN || '';
                document.getElementById('jml_hari_rent_dingin').value = d.JUMLAH || 1;
                document.getElementById('harga_rent_dingin').value = formatRupiah(d.HARGA || 0);
                document.getElementById('discount_rent_dingin').value = d.DISC || 0;
                document.getElementById('keterangan_rent_dingin').value = d.catatan || '';
                document.getElementById('btnPrintSuratJalanRent').dataset.sj = d.NOSJ || '';
                calculateTotalDgn();
                setButtonToUpdateMode();
            } catch (error) {
                console.error(error);
                alert('Gagal mengambil detail No Muat.');
            }
            return;
        }

        const surjalButton = event.target.closest('.pick-rent-surjal');
        if (surjalButton) {
            const nosj = surjalButton.dataset.nosj;
            if (!nosj) return;

            try {
                const payload = await fetchJson(`{{ url('rent-pendingin/show/surjal') }}/${encodeURIComponent(nosj)}`);
                const d = payload.data || {};
                surjalModal.hide();

                document.getElementById('no_muat_rent_dingin').value = d.NOMUAT || '';
                document.getElementById('tanggal_rent_dingin').value = d.TGLMUAT || '{{ now()->format('Y-m-d') }}';
                document.getElementById('no_surjal_rent_dingin').value = d.NOSJ || '';
                document.getElementById('tanggal_surjal_rent_dingin').value = d.tglsj || '{{ now()->format('Y-m-d') }}';
                document.getElementById('customer_rent_dingin_id').value = d.CUSTOMER_KODE || '';
                document.getElementById('customer_rent_dingin').value = d.CUSTOMER || '';
                document.getElementById('customer_kode_dingin').value = d.CUSTOMER_KODE || '';
                document.getElementById('nama_penerima_rent_dingin').value = d.P_NAMA || '';
                document.getElementById('telpon_rent_dingin').value = d.P_PHONE || '';
                document.getElementById('alamat_rent_dingin').value = d.P_ALAMAT || '';
                document.getElementById('item_rent_dingin').value = d.PESANAN || '';
                document.getElementById('driver_rent_dingin_id').value = d.DRIVER || '';
                document.getElementById('driver_rent_dingin').value = d.NAMA_DRIVER || '';
                document.getElementById('kendaraan_rent_dingin_id').value = d.KENDARAAN || '';
                document.getElementById('kendaraan_rent_dingin').value = d.NAMA_KENDARAAN || '';
                document.getElementById('jml_hari_rent_dingin').value = d.JUMLAH || 1;
                document.getElementById('harga_rent_dingin').value = formatRupiah(d.HARGA || 0);
                document.getElementById('discount_rent_dingin').value = d.DISC || 0;
                document.getElementById('keterangan_rent_dingin').value = d.catatan || '';
                document.getElementById('btnPrintSuratJalanRent').dataset.sj = d.NOSJ || '';
                calculateTotalDgn();
                setButtonToUpdateMode();
            } catch (error) {
                console.error(error);
                alert('Gagal mengambil detail Surat Jalan.');
            }
            return;
        }

        const customerButton = event.target.closest('.pick-customer-rent-dingin');
        if (customerButton) {
            document.getElementById('customer_rent_dingin_id').value = customerButton.dataset.code;
            document.getElementById('customer_rent_dingin').value = customerButton.dataset.name;
            document.getElementById('customer_kode_dingin').value = customerButton.dataset.code;
            document.getElementById('nama_penerima_rent_dingin').value = customerButton.dataset.name;
            document.getElementById('telpon_rent_dingin').value = customerButton.dataset.phone || '';
            document.getElementById('alamat_rent_dingin').value = customerButton.dataset.alamat || '';
            customerModal.hide();
            return;
        }

        const itemButton = event.target.closest('.pick-item-rent-dingin');
        if (itemButton) {
            document.getElementById('item_rent_dingin_id').value = itemButton.dataset.id;
            document.getElementById('item_rent_dingin').value = itemButton.dataset.item;
            document.getElementById('harga_rent_dingin').value = formatRupiah(itemButton.dataset.price || 0);
            document.getElementById('jml_hari_rent_dingin').value = 1;
            calculateTotalDgn();
            itemModal.hide();
            return;
        }

        const driverButton = event.target.closest('.pick-driver-rent-dingin');
        if (driverButton) {
            document.getElementById('driver_rent_dingin_id').value = driverButton.dataset.code;
            document.getElementById('driver_rent_dingin').value = driverButton.dataset.name;
            driverModal.hide();
            return;
        }

        const vehicleButton = event.target.closest('.pick-vehicle-rent-dingin');
        if (vehicleButton) {
            document.getElementById('kendaraan_rent_dingin_id').value = vehicleButton.dataset.code;
            document.getElementById('kendaraan_rent_dingin').value = vehicleButton.dataset.name;
            vehicleModal.hide();
            return;
        }
    });

    document.getElementById('item_rent_dingin_btn').addEventListener('click', async () => {
        await loadItems();
        itemModal.show();
    });
    document.getElementById('itemSearchRentDgn').addEventListener('input', loadItems);

    document.getElementById('driver_rent_dingin_btn').addEventListener('click', async () => {
        await loadDrivers();
        driverModal.show();
    });
    document.getElementById('driverSearchRentDgn').addEventListener('input', loadDrivers);

    document.getElementById('kendaraan_rent_dingin_btn').addEventListener('click', async () => {
        await loadVehicles();
        vehicleModal.show();
    });
    document.getElementById('vehicleSearchRentDgn').addEventListener('input', loadVehicles);

    document.getElementById('jml_hari_rent_dingin').addEventListener('input', calculateTotalDgn);
    document.getElementById('discount_rent_dingin').addEventListener('input', calculateTotalDgn);
    document.getElementById('harga_rent_dingin').addEventListener('input', calculateTotalDgn);

    const btnPrint = document.getElementById('btnPrintSuratJalanRent');
    if (btnPrint) {
        btnPrint.addEventListener('click', () => {
            const nosj = btnPrint.dataset.sj;
            if (!nosj) return;
            window.open(`{{ url('rent-pendingin/print/surjal') }}/${encodeURIComponent(nosj)}`, '_blank');
        });
    }

    document.getElementById('btnClearRentPendinginSurjal').addEventListener('click', () => {
        form.reset();
        document.getElementById('tanggal_rent_dingin').value = '{{ now()->format('Y-m-d') }}';
        document.getElementById('tanggal_surjal_rent_dingin').value = '{{ now()->format('Y-m-d') }}';
        document.getElementById('total_rent_dingin').value = '0';
        document.getElementById('sub_total_rent_dingin').value = '0';
        document.getElementById('dpp_rent_dingin').value = '0';
        document.getElementById('pajak_rent_dingin').value = '0';
        setButtonToSaveMode();
    });

    document.getElementById('btnSimpanRentPendinginSurjal').addEventListener('click', () => {
        const mode = document.getElementById('btnSimpanRentPendinginSurjal').dataset.mode || 'save';
        if (mode === 'update') {
            setButtonToUpdateMode();
            return;
        }
        setButtonToSaveMode();
    });

    document.getElementById('btnMuatRentPendingin')?.addEventListener('click', () => {
        const nomuat = document.getElementById('no_muat_rent_dingin').value;
        if (!nomuat) {
            alert('Silakan pilih No Muat terlebih dahulu.');
            return;
        }
        setButtonToUpdateMode();
    });

    setButtonToSaveMode();
    calculateTotalDgn();
})();
</script>
@endpush
