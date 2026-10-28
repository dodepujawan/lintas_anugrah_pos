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
                         <input type="text" id="pajak_rent_dingin" name="PPN" class="form-control" value="0" readonly>
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

    function parseCurrencyNumber(value) {
        if (value === null || value === undefined || value === '') return 0;

        let cleaned = String(value)
            .replace(/Rp/gi, '')
            .replace(/\s+/g, '')
            .replace(/[^0-9,.-]/g, '');

        if (!cleaned) return 0;

        if (cleaned.includes(',') && cleaned.includes('.')) {
            const lastComma = cleaned.lastIndexOf(',');
            const lastDot = cleaned.lastIndexOf('.');
            const decimalSep = lastComma > lastDot ? ',' : '.';
            const thousandSep = decimalSep === ',' ? '.' : ',';
            const normalized = cleaned.replace(new RegExp(`\\${thousandSep}`, 'g'), '').replace(decimalSep, '.');
            return Number(normalized || 0);
        }

        if (cleaned.includes(',')) {
            const commaParts = cleaned.split(',');
            if (commaParts.length > 2) {
                return Number(commaParts.join('').replace(/,/g, '.'));
            }
            if (commaParts[1]?.length <= 2) {
                return Number(commaParts[0].replace(/\./g, '') + '.' + commaParts[1]);
            }
            return Number(commaParts.join('').replace(/\./g, ''));
        }

        if (cleaned.includes('.')) {
            const dotParts = cleaned.split('.');
            if (dotParts.length > 2) {
                return Number(dotParts.join(''));
            }
            if (dotParts[1] && dotParts[1].length === 3) {
                return Number(dotParts.join(''));
            }
            if (dotParts[1] && dotParts[1].length <= 2) {
                return Number(cleaned);
            }
            return Number(cleaned.replace(/\./g, ''));
        }

        return Number(cleaned);
    }

    function normalizeFormCurrencyFields() {
        const hargaInput = document.getElementById('harga_rent_dingin');
        const totalInput = document.getElementById('total_rent_dingin');
        const harga = parseCurrencyNumber(hargaInput?.value || 0);
        const grand = parseCurrencyNumber(totalInput?.value || 0);
        const jumlah = Number(document.getElementById('jml_hari_rent_dingin').value || 0);

        if (hargaInput) hargaInput.value = String(harga);
        if (totalInput) {
            totalInput.value = String(grand);
            totalInput.setAttribute('value', String(grand));
        }

        const formData = new FormData(form);
        formData.set('HARGA', String(harga));
        formData.set('GRAND', String(grand));
        formData.set('JUMLAH', String(jumlah));
        return formData;
    }

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

    function calculateTotalDgn() {
        const quantity = Number(document.getElementById('jml_hari_rent_dingin').value || 0);
        const price = parseCurrencyNumber(document.getElementById('harga_rent_dingin').value || 0);
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

    async function fetchJson(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
        });
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

    function renderRentRows(tbodyId, rows, emptyMessage, columns) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="${columns}" class="text-center text-muted py-3">${emptyMessage}</td></tr>`;
            return;
        }
        tbody.innerHTML = rows;
    }

    async function loadMuatRows() {
        const tbody = document.getElementById('muatRowsRentDgn');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted py-3">Memuat data...</td></tr>';
        const q = document.getElementById('filter_rent_dgn').value.trim();

        try {
            const payload = await fetchJson(`{{ route('rent-pendingin.data.muat') }}?search=${encodeURIComponent(q)}`);
            const rows = payload.data || [];
            renderRentRows('muatRowsRentDgn', rows.map((row, index) => `
                <tr>
                    <td>${index + 1}</td><td>${row.NOMUAT || '-'}</td><td>${row.TGLMUAT || '-'}</td>
                    <td>${row.CUSTOMER || '-'}</td><td>${row.PESANAN || '-'}</td><td>${row.JUMLAH || 0}</td>
                    <td class="text-end">Rp ${formatRupiah(row.HARGA || 0)}</td><td>${row.DISC || 0}%</td>
                    <td class="text-end">Rp ${formatRupiah(row.GRAND || 0)}</td><td>${row.NOSJ || '-'}</td>
                    <td class="text-nowrap">
                        <button type="button" class="btn btn-sm btn-primary pick-rent-muat" data-nomuat="${row.NOMUAT || ''}">Pilih</button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-rent-muat" data-id="${row.id || ''}" data-nomuat="${row.NOMUAT || ''}" title="Kembalikan ke Surat Jalan"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`), 'Data No Muat tidak ditemukan.', 11);
        } catch (error) {
            renderRentRows('muatRowsRentDgn', [], error.message || 'Gagal memuat data No Muat.', 11);
        }
    }

    async function loadSurjalRows() {
        const tbody = document.getElementById('surjalRowsRentDgn');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">Memuat data...</td></tr>';
        const q = document.getElementById('filter_surjal_rent_dgn').value.trim();

        try {
            const payload = await fetchJson(`{{ route('rent-pendingin.data.surjal') }}?search=${encodeURIComponent(q)}`);
            const rows = payload.data || [];
            renderRentRows('surjalRowsRentDgn', rows.map((row, index) => `
                <tr>
                    <td>${index + 1}</td><td>${row.NOSJ || '-'}</td><td>${row.tglsj || '-'}</td>
                    <td>${row.CUSTOMER || '-'}</td><td>${row.PESANAN || '-'}</td><td>${row.JUMLAH || 0}</td>
                    <td class="text-end">Rp ${formatRupiah(row.HARGA || 0)}</td><td>${row.DISC || 0}%</td>
                    <td class="text-end">Rp ${formatRupiah(row.GRAND || 0)}</td>
                    <td class="text-nowrap">
                        <button type="button" class="btn btn-sm btn-primary pick-rent-surjal" data-nosj="${row.NOSJ || ''}">Pilih</button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-rent-surjal" data-id="${row.id || ''}" data-nosj="${row.NOSJ || ''}" title="Hapus Surat Jalan"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`), 'Data Surat Jalan tidak ditemukan.', 10);
        } catch (error) {
            renderRentRows('surjalRowsRentDgn', [], error.message || 'Gagal memuat data Surat Jalan.', 10);
        }
    }

    function fillFormFromRecord(data = {}) {
        const setValue = (id, value, fallback = '') => {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = value ?? fallback;
        };

        const formatCurrencyInput = (value) => {
            const numeric = Number(value || 0);
            return Number.isFinite(numeric) ? formatRupiah(numeric) : '0';
        };

        const rawSj = data.NOSJ || '';
        const rawMuat = data.NOMUAT || '';
        const selectedCustomerCode = data.CUSTOMER_KODE || data.CUSTOMER || '';
        const selectedCustomerName = data.NAMACUST || data.CUSTOMER_NAME || data.CUSTOMER || '';
        const selectedDriverCode = data.DRIVER || '';
        const selectedDriverName = data.NAMA_DRIVER || data.DRIVER || '';
        const selectedVehicleCode = data.KENDARAAN || '';
        const selectedVehicleName = data.NAMA_KENDARAAN || data.KENDARAAN || '';

        setValue('no_muat_rent_dingin', rawMuat);
        setValue('tanggal_rent_dingin', data.TGLMUAT || '{{ now()->format('Y-m-d') }}');
        setValue('no_surjal_rent_dingin', rawSj);
        setValue('tanggal_surjal_rent_dingin', data.tglsj || '{{ now()->format('Y-m-d') }}');

        setValue('customer_rent_dingin_id', selectedCustomerCode);
        setValue('customer_rent_dingin', selectedCustomerName);
        setValue('customer_kode_dingin', selectedCustomerCode);
        setValue('nama_penerima_rent_dingin', data.P_NAMA || selectedCustomerName || '');
        setValue('telpon_rent_dingin', data.P_PHONE || '');
        setValue('alamat_rent_dingin', data.P_ALAMAT || '');

        setValue('item_rent_dingin', data.PESANAN || '');
        setValue('jml_hari_rent_dingin', data.JUMLAH ?? 1);
        setValue('harga_rent_dingin', formatCurrencyInput(data.HARGA));
        setValue('discount_rent_dingin', data.DISC ?? 0);
        setValue('keterangan_rent_dingin', data.catatan || '');

        setValue('driver_rent_dingin_id', selectedDriverCode);
        setValue('driver_rent_dingin', selectedDriverName);
        setValue('kendaraan_rent_dingin_id', selectedVehicleCode);
        setValue('kendaraan_rent_dingin', selectedVehicleName);

        const jumlah = Number(data.JUMLAH ?? 0);
        const harga = Number(data.HARGA ?? 0);
        const discount = Number(data.DISC ?? 0);
        const subtotal = jumlah * harga;
        const dpp = subtotal - (subtotal * (discount / 100));
        const total = Number(data.GRAND ?? dpp);

        document.getElementById('sub_total_rent_dingin').value = formatRupiah(subtotal);
        document.getElementById('dpp_rent_dingin').value = formatRupiah(dpp);
        document.getElementById('pajak_rent_dingin').value = data.PPN ?? 0;
        document.getElementById('total_rent_dingin').value = formatRupiah(total);
        document.getElementById('total_rent_dingin').setAttribute('value', String(total));

        const saveBtn = document.getElementById('btnSimpanRentPendinginSurjal');
        if (rawSj) {
            setButtonToUpdateMode();
            saveBtn.dataset.mode = 'update';
            document.getElementById('btnPrintSuratJalanRent').dataset.sj = rawSj;
            document.getElementById('divPrintSuratJalanRent').classList.remove('d-none');
            return;
        }

        setButtonToSaveMode();
        saveBtn.dataset.mode = 'save';
    }

    document.getElementById('customerSearchRentDgn')?.addEventListener('input', loadCustomers);
    document.getElementById('customerRowsRentDgn')?.addEventListener('click', (event) => {
        const button = event.target.closest('.pick-customer-rent-dingin');
        if (!button) return;

        const code = button.dataset.code || '';
        const name = button.dataset.name || '';
        const alamat = button.dataset.alamat || '';
        const phone = button.dataset.phone || '';

        document.getElementById('customer_rent_dingin_id').value = code;
        document.getElementById('customer_rent_dingin').value = name;
        document.getElementById('customer_kode_dingin').value = code;
        document.getElementById('nama_penerima_rent_dingin').value = name;
        document.getElementById('alamat_rent_dingin').value = alamat;
        document.getElementById('telpon_rent_dingin').value = phone;
        customerModal.hide();
    });

    document.getElementById('itemSearchRentDgn')?.addEventListener('input', loadItems);
    document.getElementById('itemRowsRentDgn')?.addEventListener('click', (event) => {
        const button = event.target.closest('.pick-item-rent-dingin');
        if (!button) return;

        const item = button.dataset.item || '';
        const price = button.dataset.price || 0;

        document.getElementById('item_rent_dingin_id').value = button.dataset.id || '';
        document.getElementById('item_rent_dingin').value = item;
        document.getElementById('jml_hari_rent_dingin').value = '1';
        document.getElementById('harga_rent_dingin').value = formatRupiah(price);
        calculateTotalDgn();
        itemModal.hide();
    });

    document.getElementById('driverSearchRentDgn')?.addEventListener('input', loadDrivers);
    document.getElementById('driverRowsRentDgn')?.addEventListener('click', (event) => {
        const button = event.target.closest('.pick-driver-rent-dingin');
        if (!button) return;

        document.getElementById('driver_rent_dingin_id').value = button.dataset.code || '';
        document.getElementById('driver_rent_dingin').value = button.dataset.name || '';
        driverModal.hide();
    });

    document.getElementById('vehicleSearchRentDgn')?.addEventListener('input', loadVehicles);
    document.getElementById('vehicleRowsRentDgn')?.addEventListener('click', (event) => {
        const button = event.target.closest('.pick-vehicle-rent-dingin');
        if (!button) return;

        document.getElementById('kendaraan_rent_dingin_id').value = button.dataset.code || '';
        document.getElementById('kendaraan_rent_dingin').value = button.dataset.name || '';
        vehicleModal.hide();
    });

    document.getElementById('muatRowsRentDgn')?.addEventListener('click', async (event) => {
        const button = event.target.closest('.pick-rent-muat');
        if (!button) return;

        const nomuat = (button.dataset.nomuat || '').trim();
        if (!nomuat) return;

        try {
            const payload = await fetchJson(`{{ url('rent-pendingin/show/muat') }}/${encodeURIComponent(nomuat)}`);
            if (!payload?.success) {
                throw new Error(payload?.message || 'Data No Muat tidak ditemukan.');
            }
            muatModal.hide();
            fillFormFromRecord(payload.data || {});
            const rawSj = payload.data?.NOSJ || '';
            if (rawSj) {
                document.getElementById('no_surjal_rent_dingin').value = rawSj;
                document.getElementById('btnSimpanRentPendinginSurjal').dataset.mode = 'update';
            }
        } catch (error) {
            alert(error.message || 'Gagal mengambil data No Muat.');
        }
    });

    document.getElementById('surjalRowsRentDgn')?.addEventListener('click', async (event) => {
        const button = event.target.closest('.pick-rent-surjal');
        if (!button) return;

        const nosj = (button.dataset.nosj || '').trim();
        if (!nosj) return;

        try {
            const payload = await fetchJson(`{{ url('rent-pendingin/show/surjal') }}/${encodeURIComponent(nosj)}`);
            if (!payload?.success) {
                throw new Error(payload?.message || 'Data Surat Jalan tidak ditemukan.');
            }
            surjalModal.hide();
            fillFormFromRecord(payload.data || {});
            document.getElementById('no_surjal_rent_dingin').value = payload.data?.NOSJ || nosj;
            document.getElementById('btnSimpanRentPendinginSurjal').dataset.mode = 'update';
        } catch (error) {
            alert(error.message || 'Gagal mengambil data Surat Jalan.');
        }
    });

    document.getElementById('muatRowsRentDgn')?.addEventListener('click', async (event) => {
        const button = event.target.closest('.delete-rent-muat');
        if (!button) return;

        const nomuat = button.dataset.nomuat || '';
        if (!button.dataset.id || !confirm(`No Muat ${nomuat} akan dikembalikan menjadi Surat Jalan. Lanjutkan?`)) return;

        try {
            await fetchJson(`{{ url('rent-pendingin/muat') }}/${encodeURIComponent(button.dataset.id)}/reset` , {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            await loadMuatRows();
            alert('Data berhasil dikembalikan ke Surat Jalan.');
        } catch (error) {
            alert(error.message || 'Gagal mengembalikan No Muat.');
        }
    });

    document.getElementById('surjalRowsRentDgn')?.addEventListener('click', async (event) => {
        const button = event.target.closest('.delete-rent-surjal');
        if (!button) return;

        const nosj = button.dataset.nosj || '';
        if (!button.dataset.id || !confirm(`Surat Jalan ${nosj} akan dihapus. Lanjutkan?`)) return;

        try {
            await fetchJson(`{{ url('rent-pendingin/surjal') }}/${encodeURIComponent(button.dataset.id)}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            await loadSurjalRows();
            alert('Surat Jalan berhasil dihapus.');
        } catch (error) {
            alert(error.message || 'Gagal menghapus Surat Jalan.');
        }
    });

    document.getElementById('customer_rent_dingin_btn')?.addEventListener('click', async () => {
        customerModal.show();
        await loadCustomers();
    });
    document.getElementById('item_rent_dingin_btn')?.addEventListener('click', async () => {
        if (!document.getElementById('customer_rent_dingin_id').value) {
            alert('Silakan pilih customer terlebih dahulu.');
            return;
        }
        itemModal.show();
        await loadItems();
    });
    document.getElementById('driver_rent_dingin_btn')?.addEventListener('click', async () => {
        driverModal.show();
        await loadDrivers();
    });
    document.getElementById('kendaraan_rent_dingin_btn')?.addEventListener('click', async () => {
        vehicleModal.show();
        await loadVehicles();
    });
    document.getElementById('no_muat_rent_dingin_btn')?.addEventListener('click', async () => {
        muatModal.show();
        await loadMuatRows();
    });
    document.getElementById('no_surjal_rent_dingin_btn')?.addEventListener('click', async () => {
        surjalModal.show();
        await loadSurjalRows();
    });

    ['harga_rent_dingin', 'jml_hari_rent_dingin', 'discount_rent_dingin'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', calculateTotalDgn);
        }
    });

    document.getElementById('btn_filter_rent_dgn')?.addEventListener('click', loadMuatRows);
    document.getElementById('btn_filter_surjal_rent_dgn')?.addEventListener('click', loadSurjalRows);

    document.getElementById('btnPrintSuratJalanRent')?.addEventListener('click', () => {
        const nosj = document.getElementById('btnPrintSuratJalanRent').dataset.sj;
        if (!nosj) {
            alert('Surat Jalan belum tersedia untuk dicetak.');
            return;
        }

        window.open(`{{ url('rent-pendingin/print/surjal') }}/${encodeURIComponent(nosj)}`, '_blank');
    });

    document.getElementById('btnClearRentPendinginSurjal')?.addEventListener('click', () => {
        form.reset();
        document.getElementById('tanggal_rent_dingin').value = '{{ now()->format('Y-m-d') }}';
        document.getElementById('tanggal_surjal_rent_dingin').value = '{{ now()->format('Y-m-d') }}';
        document.getElementById('sub_total_rent_dingin').value = '0';
        document.getElementById('dpp_rent_dingin').value = '0';
        document.getElementById('pajak_rent_dingin').value = '0';
        document.getElementById('total_rent_dingin').value = '0';
        document.getElementById('total_rent_dingin').setAttribute('value', '0');
        setButtonToSaveMode();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const saveBtn = document.getElementById('btnSimpanRentPendinginSurjal');
        const originalText = saveBtn.innerHTML;
        const nosjValue = (document.getElementById('no_surjal_rent_dingin').value || '').trim();
        const mode = (saveBtn.dataset.mode || (nosjValue ? 'update' : 'save')).toLowerCase();
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

        try {
            const normalizedForm = normalizeFormCurrencyFields();
            const url = mode === 'update' && nosjValue
                ? `{{ url('rent-pendingin/update-sj') }}/${encodeURIComponent(nosjValue)}`
                : '{{ route('rent-pendingin.store-sj') }}';

            const response = await fetch(url, {
                method: 'POST',
                body: normalizedForm,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = payload.message || (payload.errors ? Object.values(payload.errors || {}).flat().join('\n') : 'Gagal menyimpan data.');
                throw new Error(message);
            }

            const savedNosj = payload.data?.NOSJ || document.getElementById('no_surjal_rent_dingin').value || '';
            if (savedNosj) {
                document.getElementById('no_surjal_rent_dingin').value = savedNosj;
            }
            if (payload.data?.NOMUAT) {
                document.getElementById('no_muat_rent_dingin').value = payload.data.NOMUAT;
            }

            document.getElementById('btnPrintSuratJalanRent').dataset.sj = savedNosj;
            document.getElementById('divPrintSuratJalanRent').classList.remove('d-none');
            setButtonToUpdateMode();
            saveBtn.dataset.mode = 'update';
            alert(payload.message || 'Data berhasil disimpan.');
        } catch (error) {
            alert(error.message || 'Terjadi kesalahan saat menyimpan.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    });

    document.getElementById('btnMuatRentPendingin')?.addEventListener('click', async () => {
        const nosj = (document.getElementById('no_surjal_rent_dingin').value || '').trim();
        if (!nosj) {
            alert('Silakan pilih No Surat Jalan terlebih dahulu.');
            return;
        }

        try {
            const response = await fetch(`{{ url('rent-pendingin/proses-muat') }}/${encodeURIComponent(nosj)}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(payload?.message || 'Gagal memproses No Muat.');
            }

            const nomuat = payload?.nomuat || payload?.data?.NOMUAT || '';
            if (nomuat) {
                document.getElementById('no_muat_rent_dingin').value = nomuat;
            }

            alert(payload?.message || 'No Muat berhasil diproses.');
            setButtonToUpdateMode();
        } catch (error) {
            alert(error.message || 'Terjadi kesalahan saat memproses No Muat.');
        }
    });
})();
</script>
@endpush
