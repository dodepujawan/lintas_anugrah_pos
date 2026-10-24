@extends('layouts.app')

@section('content')
<div class="container-fluid mt-3" id="muatPage">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="page-title mb-1">Tambah / Edit No Muat</h1>
            <p class="text-muted small mb-0">Pilih SJ yang belum bermuatan atau cari No Muat untuk diperbarui.</p>
        </div>
        <a href="{{ route('expedisi.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    {{-- BAGIAN FORM: header No Muat, kendaraan, driver, kilometer, dan biaya perjalanan. --}}
    <section class="card shadow-sm border-0 mb-4" aria-labelledby="muatFormTitle">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0 text-primary fw-bold" id="muatFormTitle"><i class="bi bi-box-seam me-2"></i>FORM NO MUAT</h2>
            <span class="badge bg-primary" id="muatModeBadge">MODE TAMBAH</span>
        </div>
        <div class="card-body">
            <div id="muatAlert" class="alert d-none" role="alert"></div>
            <form id="formNoMuat" autocomplete="off">
                @csrf
                <div class="row align-items-end g-3 mb-3">
                    <div class="col-md-2">
                        <label for="tgl_muat_expedisi" class="form-label fw-semibold small">TGL MUAT</label>
                        <input type="date" class="form-control form-control-sm" id="tgl_muat_expedisi" name="tgl_muat_expedisi" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="no_muat_expedisi" class="form-label fw-semibold small">NO MUAT</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="no_muat_expedisi" name="no_muat_expedisi" readonly placeholder="Auto Generate / Click Search">
                            <button class="btn btn-outline-secondary" type="button" id="muat_expedisi_btn" title="Cari No Muat tersimpan" aria-label="Cari No Muat"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="rute_muat_expedisi" class="form-label fw-semibold small">RUTE</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="rute_muat_expedisi" name="rute_muat_expedisi" placeholder="Pilih atau isi rute">
                            <button class="btn btn-outline-secondary" type="button" id="rute_muat_btn" title="Cari rute" aria-label="Cari rute"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="kendaraan_muat_expedisi" class="form-label fw-semibold small">KENDARAAN</label>
                        <div class="input-group input-group-sm">
                            <input type="hidden" id="kendaraan_kode_muat_expedisi" name="kendaraan_kode_muat_expedisi">
                            <input type="text" class="form-control" id="kendaraan_muat_expedisi" name="kendaraan_muat_expedisi" readonly placeholder="Pilih kendaraan">
                            <button class="btn btn-outline-secondary" type="button" id="kendaraan_muat_expedisi_btn" aria-label="Pilih kendaraan"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row align-items-end g-3 mb-3">
                    <div class="col-md-3">
                        <label for="plat_nomor_muat_expedisi" class="form-label fw-semibold small">PLAT</label>
                        <input type="text" class="form-control form-control-sm" id="plat_nomor_muat_expedisi" name="plat_nomor_muat_expedisi" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="driver1_muat_expedisi" class="form-label fw-semibold small">DRIVER 1</label>
                        <div class="input-group input-group-sm">
                            <input type="hidden" id="driver1_kode_muat_expedisi" name="driver1_kode_muat_expedisi">
                            <input type="text" class="form-control" id="driver1_muat_expedisi" name="driver1_muat_expedisi" readonly placeholder="Pilih driver 1">
                            <button class="btn btn-outline-secondary driver-lookup" type="button" data-driver-target="1" aria-label="Pilih driver 1"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="driver2_muat_expedisi" class="form-label fw-semibold small">DRIVER 2</label>
                        <div class="input-group input-group-sm">
                            <input type="hidden" id="driver2_kode_muat_expedisi" name="driver2_kode_muat_expedisi">
                            <input type="text" class="form-control" id="driver2_muat_expedisi" name="driver2_muat_expedisi" readonly placeholder="Pilih driver 2">
                            <button class="btn btn-outline-secondary driver-lookup" type="button" data-driver-target="2" aria-label="Pilih driver 2"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="km_awal_muat_expedisi" class="form-label fw-semibold small">KM AWAL</label>
                        <input type="number" step="0.1" min="0" class="form-control form-control-sm" id="km_awal_muat_expedisi" name="km_awal_muat_expedisi">
                    </div>
                </div>
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label for="km_akhir_muat_expedisi" class="form-label fw-semibold small">KM AKHIR</label>
                        <input type="number" step="0.1" min="0" class="form-control form-control-sm" id="km_akhir_muat_expedisi" name="km_akhir_muat_expedisi">
                    </div>
                    <div class="col-md-3">
                        <label for="uang_jalan_muat_expedisi" class="form-label fw-semibold small">UANG JALAN</label>
                        <input type="number" min="0" step="1" class="form-control form-control-sm text-end" id="uang_jalan_muat_expedisi" name="uang_jalan_muat_expedisi" value="0">
                    </div>
                    <div class="col-md-3">
                        <label for="uang_driver_makan_muat_expedisi" class="form-label fw-semibold small">UANG DRIVER + MAKAN</label>
                        <input type="number" min="0" step="1" class="form-control form-control-sm text-end" id="uang_driver_makan_muat_expedisi" name="uang_driver_makan_muat_expedisi" value="0">
                    </div>
                    <div class="col-md-3">
                        <label for="uang_lain_lain_muat_expedisi" class="form-label fw-semibold small">UANG LAIN-LAIN</label>
                        <input type="number" min="0" step="1" class="form-control form-control-sm text-end" id="uang_lain_lain_muat_expedisi" name="uang_lain_lain_muat_expedisi" value="0">
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- BAGIAN TABEL: daftar SJ terpilih, kolom mengikuti tabel muat src_old. --}}
    <section class="card shadow-sm border-0" aria-labelledby="muatSjTitle">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0 text-primary fw-bold" id="muatSjTitle"><i class="bi bi-table me-2"></i>DATA SURAT JALAN</h2>
            <button type="button" class="btn btn-primary btn-sm" id="btnBukaModalSJ"><i class="bi bi-plus-circle me-1"></i>TAMBAH SJ</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover align-middle" id="tableProsesExpedisi">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th><th>NO.SJ</th><th>TGL.SJ</th><th>DELCHARGE</th><th>JUMLAH</th><th>UNIT</th><th>JENIS</th><th>HARGA</th><th>DISC</th><th>PPN</th><th>TOTAL</th><th>PENGIRIM</th><th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="muatSjRows"><tr><td colspan="13" class="text-center text-muted py-3">Belum ada SJ. Tekan TAMBAH SJ untuk memilih.</td></tr></tbody>
                    <tfoot><tr><th colspan="10" class="text-end">GRAND TOTAL</th><th class="text-end" id="grandTotalMuat">0</th><th colspan="2"></th></tr></tfoot>
                </table>
            </div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-primary btn-sm" id="simpanMuatExpBtn"><i class="bi bi-save me-1"></i>Simpan No Muat</button>
                <button type="button" class="btn btn-info btn-sm d-none" id="tampilkanPdfMuatExpBtn"><i class="bi bi-file-earmark-pdf me-1"></i>Tampilkan PDF</button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="clearMuatExpBtn"><i class="bi bi-eraser me-1"></i>Clear No Muat</button>
            </div>
        </div>
    </section>
</div>

{{-- MODAL LOOKUP: memilih SJ yang belum muat. --}}
<div class="modal fade" id="surjalModalExp" tabindex="-1" aria-labelledby="surjalModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title h5" id="surjalModalTitle">Pilih Surat Jalan Ekspedisi yang Belum Muat</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <div class="row g-2 mb-3">
                <div class="col-md-3"><label class="form-label small" for="sjDateFrom">Tanggal mulai</label><input type="date" class="form-control form-control-sm" id="sjDateFrom"></div>
                <div class="col-md-3"><label class="form-label small" for="sjDateTo">Tanggal akhir</label><input type="date" class="form-control form-control-sm" id="sjDateTo"></div>
                <div class="col-md-4"><label class="form-label small" for="sjSearch">Filter SJ</label><input type="search" class="form-control form-control-sm" id="sjSearch" placeholder="No SJ, customer, rute"></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-info btn-sm w-100" type="button" id="sjFilterBtn">Filter</button></div>
            </div>
            <div class="table-responsive"><table class="table table-bordered table-sm align-middle">
                <thead class="table-light"><tr><th>No SJ</th><th>Tgl SJ</th><th>Customer</th><th>Rute</th><th>Jumlah</th><th>Harga</th><th>Disc</th><th>Del Charge</th><th>Total</th><th>Aksi</th></tr></thead>
                <tbody id="sjLookupRows"><tr><td colspan="10" class="text-center text-muted">Memuat data...</td></tr></tbody>
            </table></div>
            <div class="d-flex justify-content-between align-items-center mt-2"><small class="text-muted" id="sjPageInfo"></small><div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" id="sjPrev" type="button">Sebelumnya</button><button class="btn btn-outline-secondary" id="sjNext" type="button">Berikutnya</button></div></div>
        </div>
    </div></div>
</div>

{{-- MODAL LOOKUP: mencari No Muat lama untuk memuat detail dan masuk mode update. --}}
<div class="modal fade" id="muatModalExp" tabindex="-1" aria-labelledby="muatModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title h5" id="muatModalTitle">Pilih No Muat Tersimpan</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <div class="row g-2 mb-3">
                <div class="col-md-3"><label class="form-label small" for="muatDateFrom">Tanggal mulai</label><input type="date" class="form-control form-control-sm" id="muatDateFrom"></div>
                <div class="col-md-3"><label class="form-label small" for="muatDateTo">Tanggal akhir</label><input type="date" class="form-control form-control-sm" id="muatDateTo"></div>
                <div class="col-md-4"><label class="form-label small" for="muatSearch">Filter No Muat</label><input type="search" class="form-control form-control-sm" id="muatSearch" placeholder="Cari No Muat, customer, rute, atau SJ"></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-info btn-sm w-100" type="button" id="muatFilterBtn">Filter</button></div>
            </div>
            <div class="table-responsive"><table class="table table-bordered table-sm align-middle"><thead class="table-light"><tr><th>No</th><th>No Muat</th><th>Tgl Muat</th><th>Customer</th><th>Rute</th><th>Jumlah</th><th>Harga</th><th>Disc</th><th>Del Charge</th><th>Total</th><th>No SJ</th><th colspan="2">Aksi</th></tr></thead><tbody id="muatLookupRows"><tr><td colspan="13" class="text-center text-muted">Memuat data...</td></tr></tbody></table></div>
            <div class="d-flex justify-content-between align-items-center mt-2"><small class="text-muted" id="muatPageInfo"></small><div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" id="muatPrev" type="button">Sebelumnya</button><button class="btn btn-outline-secondary" id="muatNext" type="button">Berikutnya</button></div></div>
        </div>
    </div></div>
</div>

{{-- MODAL LOOKUP: kendaraan master dari endpoint JSON src. --}}
<div class="modal fade" id="kendaraanModalExp" tabindex="-1" aria-labelledby="kendaraanModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title h5" id="kendaraanModalTitle">Pilih Kendaraan</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body"><input type="search" class="form-control form-control-sm mb-3" id="vehicleSearch" placeholder="Cari kode, nama, atau plat"><div class="table-responsive"><table class="table table-bordered table-sm"><thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Plat</th><th>Aksi</th></tr></thead><tbody id="vehicleLookupRows"></tbody></table></div><div class="d-flex justify-content-between"><button class="btn btn-sm btn-outline-secondary" id="vehiclePrev" type="button">Sebelumnya</button><span class="small text-muted" id="vehiclePageInfo"></span><button class="btn btn-sm btn-outline-secondary" id="vehicleNext" type="button">Berikutnya</button></div></div>
    </div></div>
</div>

{{-- MODAL LOOKUP: driver master dari endpoint JSON src. --}}
<div class="modal fade" id="driverModalExp" tabindex="-1" aria-labelledby="driverModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title h5" id="driverModalTitle">Pilih Driver</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body"><input type="search" class="form-control form-control-sm mb-3" id="driverSearch" placeholder="Cari kode atau nama driver"><div class="table-responsive"><table class="table table-bordered table-sm"><thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Aksi</th></tr></thead><tbody id="driverLookupRows"></tbody></table></div><div class="d-flex justify-content-between"><button class="btn btn-sm btn-outline-secondary" id="driverPrev" type="button">Sebelumnya</button><span class="small text-muted" id="driverPageInfo"></span><button class="btn btn-sm btn-outline-secondary" id="driverNext" type="button">Berikutnya</button></div></div>
    </div></div>
</div>

{{-- MODAL LOOKUP: rute master. --}}
<div class="modal fade" id="ruteModalExp" tabindex="-1" aria-labelledby="ruteModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title h5" id="ruteModalTitle">Pilih Rute</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body"><input type="search" class="form-control form-control-sm mb-3" id="routeSearch" placeholder="Cari rute"><div class="table-responsive"><table class="table table-bordered table-sm"><thead class="table-light"><tr><th>Rute</th><th>Dari</th><th>Sampai</th><th>Aksi</th></tr></thead><tbody id="routeLookupRows"></tbody></table></div><div class="d-flex justify-content-between"><button class="btn btn-sm btn-outline-secondary" id="routePrev" type="button">Sebelumnya</button><span class="small text-muted" id="routePageInfo"></span><button class="btn btn-sm btn-outline-secondary" id="routeNext" type="button">Berikutnya</button></div></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const root = document.getElementById('muatPage');
    if (!root) return;

    const urls = {
        sj: @json(route('expedisi.data-sj-tersedia')),
        showSj: @json(route('expedisi.surjal.show')),
        muat: @json(route('expedisi.dataMuat')),
        showMuat: @json(route('expedisi.showMuat')),
        storeMuat: @json(route('expedisi.storeMuat')),
        updateMuat: @json(route('expedisi.updateMuat', ['nomuat' => '__NOMUAT__'])),
        pdfMuat: @json(route('expedisi.pdfMuat', ['nomuat' => '__NOMUAT__'])),
        deleteMuat: @json(route('expedisi.destroyMuat', ['nomuat' => '__NOMUAT__'])),
        vehicles: @json(route('expedisi.data.vehicles')),
        drivers: @json(route('expedisi.data.drivers')),
        routes: @json(route('expedisi.rute-muat')),
        km: @json(route('expedisi.dataKm')),
        printSj: @json(route('expedisi.print.surjal', ['expedisi' => '__ID__'])),
    };
    const selectedSJ = new Map();
    let updateMode = false;
    let activeDriver = '1';
    const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
    const byId = id => document.getElementById(id);
    const format = value => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));

    async function getJson(url) {
        const response = await fetch(url, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
        const body = await response.json();
        if (!response.ok) throw new Error(body.message || 'Permintaan gagal.');
        return body;
    }
    function alertMuat(message, type = 'danger') {
        const alert = byId('muatAlert');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.classList.remove('d-none');
        alert.scrollIntoView({behavior: 'smooth', block: 'nearest'});
    }
    function clearAlert() { byId('muatAlert').classList.add('d-none'); }
    function pageUrl(base, page, params = {}) {
        const url = new URL(base, window.location.origin);
        url.searchParams.set('page', page);
        Object.entries(params).forEach(([key, value]) => value && url.searchParams.set(key, value));
        return url;
    }
    function updatePager(prefix, payload, page, load) {
        byId(`${prefix}PageInfo`).textContent = `Halaman ${payload.current_page || page} dari ${payload.last_page || 1} · ${payload.total || 0} data`;
        byId(`${prefix}Prev`).disabled = (payload.current_page || page) <= 1;
        byId(`${prefix}Next`).disabled = (payload.current_page || page) >= (payload.last_page || 1);
        byId(`${prefix}Prev`).onclick = () => load(page - 1);
        byId(`${prefix}Next`).onclick = () => load(page + 1);
    }

    async function loadSj(page = 1) {
        const body = byId('sjLookupRows');
        body.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Memuat data...</td></tr>';
        try {
            const payload = await getJson(pageUrl(urls.sj, page, {
                search: byId('sjSearch').value.trim(),
                date_from: byId('sjDateFrom').value,
                date_to: byId('sjDateTo').value,
            }));
            body.innerHTML = '';
            if (!payload.data?.length) body.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Tidak ada SJ yang belum bermuatan.</td></tr>';
            (payload.data || []).forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${escape(row.NOSJ)}</td><td>${escape(row.tglsj || '-')}</td><td>${escape(row.CUSTOMER)}</td><td>${escape(row.rute)}</td><td class="text-end">${format(row.JUMLAH)} ${escape(row.UNIT)}</td><td class="text-end">${format(row.HARGA)}</td><td class="text-end">${format(row.DISC)}%</td><td class="text-end">${format(row.DC)}</td><td class="text-end">${format(row.GRAND)}</td><td><button class="btn btn-sm btn-outline-primary pick-sj" type="button" data-id="${row.id}" aria-label="Pilih ${escape(row.NOSJ)}"><i class="bi bi-check-lg"></i> Pilih</button></td>`;
                body.append(tr);
            });
            updatePager('sj', payload, page, loadSj);
        } catch (error) { body.innerHTML = `<tr><td colspan="10" class="text-danger">${escape(error.message)}</td></tr>`; }
    }

    async function loadMuat(page = 1) {
        const body = byId('muatLookupRows');
        body.innerHTML = '<tr><td colspan="13" class="text-center text-muted">Memuat data...</td></tr>';
        try {
            const payload = await getJson(pageUrl(urls.muat, page, {search: byId('muatSearch').value.trim(), date_from: byId('muatDateFrom').value, date_to: byId('muatDateTo').value}));
            body.innerHTML = '';
            if (!payload.data?.length) body.innerHTML = '<tr><td colspan="13" class="text-center text-muted">Tidak ada No Muat yang dapat diedit.</td></tr>';
            (payload.data || []).forEach((row, index) => {
                const tr = document.createElement('tr');
                const rowNumber = ((payload.current_page || page) - 1) * (payload.per_page || 15) + index + 1;
                tr.innerHTML = `<td>${rowNumber}</td><td>${escape(row.NOMUAT)}</td><td>${escape(row.TGLMUAT || '-')}</td><td>${escape(row.CUSTOMER)}</td><td>${escape(row.rute)}</td><td class="text-end">${format(row.JUMLAH)} ${escape(row.UNIT)}</td><td class="text-end">${format(row.HARGA)}</td><td class="text-end">${format(row.DISC)}%</td><td class="text-end">${format(row.DC)}</td><td class="text-end">${format(row.GRAND)}</td><td>${escape(row.NOSJ)}</td><td><button class="btn btn-sm btn-outline-primary pick-muat" type="button" data-nomuat="${escape(row.NOMUAT)}"><i class="bi bi-check-lg"></i> Pilih</button></td><td><button class="btn btn-sm btn-outline-danger delete-muat" type="button" data-nomuat="${escape(row.NOMUAT)}" title="Batalkan muatan"><i class="bi bi-trash"></i></button></td>`;
                body.append(tr);
            });
            updatePager('muat', payload, page, loadMuat);
        } catch (error) { body.innerHTML = `<tr><td colspan="13" class="text-danger">${escape(error.message)}</td></tr>`; }
    }

    function renderSelected() {
        const body = byId('muatSjRows');
        body.innerHTML = '';
        if (!selectedSJ.size) {
            body.innerHTML = '<tr><td colspan="13" class="text-center text-muted py-3">Belum ada SJ. Tekan TAMBAH SJ untuk memilih.</td></tr>';
            byId('grandTotalMuat').textContent = '0';
            return;
        }
        let grandTotal = 0;
        [...selectedSJ.values()].forEach((row, index) => {
            grandTotal += Number(row.total || 0);
            const kind = row.jenishrg === '1' ? 'Eceran' : (row.jenishrg === '2' ? 'Booking' : '-');
            const tr = document.createElement('tr');
            tr.innerHTML = `<td class="text-center">${index + 1}</td><td class="font-monospace">${escape(row.nosj)}</td><td>${escape(row.tgl_sj || '-')}</td><td class="text-end">${format(row.dc)}</td><td class="text-end">${format(row.jumlah)}</td><td class="text-center">${escape(row.unit || 'KG')}</td><td>${kind}</td><td class="text-end">${format(row.harga)}</td><td class="text-end">${format(row.disc)}%</td><td class="text-end">${format(row.ppn)}%</td><td class="text-end">${format(row.total)}</td><td><input type="text" class="form-control form-control-sm pengirim-input" maxlength="100" aria-label="Pengirim untuk ${escape(row.nosj)}" value="${escape(row.pengirim)}" data-nosj="${escape(row.nosj)}"></td><td class="text-center"><div class="btn-group btn-group-sm"><a class="btn btn-outline-info" target="_blank" rel="noopener" href="${urls.printSj.replace('__ID__', encodeURIComponent(row.id))}" title="Cetak SJ"><i class="bi bi-printer"></i></a><button type="button" class="btn btn-outline-danger remove-sj" data-nosj="${escape(row.nosj)}" title="Lepas dari daftar"><i class="bi bi-trash"></i></button></div></td>`;
            body.append(tr);
        });
        byId('grandTotalMuat').textContent = format(grandTotal);
    }

    async function loadLookup(url, page, search, tbodyId, columns, prefix, load) {
        const body = byId(tbodyId);
        body.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Memuat data...</td></tr>';
        try {
            const payload = await getJson(pageUrl(url, page, {q: search, search}));
            body.innerHTML = '';
            (payload.data || []).forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = columns(row);
                body.append(tr);
            });
            if (!payload.data?.length) body.innerHTML = `<tr><td colspan="${prefix === 'driver' ? 3 : 4}" class="text-center text-muted">Data tidak ditemukan.</td></tr>`;
            updatePager(prefix, payload, page, load);
        } catch (error) { body.innerHTML = `<tr><td colspan="4" class="text-danger">${escape(error.message)}</td></tr>`; }
    }

    let vehiclePage = 1, driverPage = 1, routePage = 1;
    const loadVehicles = page => { vehiclePage = page; return loadLookup(urls.vehicles, page, byId('vehicleSearch').value.trim(), 'vehicleLookupRows', row => `<td>${escape(row.code)}</td><td>${escape(row.name)}</td><td>${escape(row.plat || '-')}</td><td><button type="button" class="btn btn-sm btn-primary choose-vehicle" data-code="${escape(row.code)}" data-name="${escape(row.name)}" data-plat="${escape(row.plat || '')}">Pilih</button></td>`, 'vehicle', loadVehicles); };
    const loadDrivers = page => { driverPage = page; return loadLookup(urls.drivers, page, byId('driverSearch').value.trim(), 'driverLookupRows', row => `<td>${escape(row.code)}</td><td>${escape(row.name)}</td><td><button type="button" class="btn btn-sm btn-primary choose-driver" data-code="${escape(row.code)}" data-name="${escape(row.name)}">Pilih</button></td>`, 'driver', loadDrivers); };
    const loadRoutes = page => { routePage = page; return loadLookup(urls.routes, page, byId('routeSearch').value.trim(), 'routeLookupRows', row => `<td>${escape(row.RUTE)}</td><td>${escape(row.FROM)}</td><td>${escape(row.TO)}</td><td><button type="button" class="btn btn-sm btn-primary choose-route" data-route="${escape(row.RUTE)}">Pilih</button></td>`, 'route', loadRoutes); };

    byId('btnBukaModalSJ').addEventListener('click', () => { modal('surjalModalExp').show(); loadSj(1); });
    byId('muat_expedisi_btn').addEventListener('click', () => { modal('muatModalExp').show(); loadMuat(1); });
    byId('sjFilterBtn').addEventListener('click', () => loadSj(1));
    byId('sjSearch').addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); loadSj(1); } });
    byId('muatFilterBtn').addEventListener('click', () => loadMuat(1));
    byId('muatSearch').addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); loadMuat(1); } });

    document.addEventListener('click', async event => {
        const pickSj = event.target.closest('.pick-sj');
        if (pickSj) {
            try {
                const response = await getJson(`${urls.showSj}?id=${encodeURIComponent(pickSj.dataset.id)}`);
                const row = response.data;
                selectedSJ.set(row.no_sj, {...row, nosj: row.no_sj, jenishrg: row.jenis});
                renderSelected();
                modal('surjalModalExp').hide();
            } catch (error) { alertMuat(error.message); }
        }

        const pickMuat = event.target.closest('.pick-muat');
        if (pickMuat) {
            try {
                const response = await getJson(`${urls.showMuat}?nomuat=${encodeURIComponent(pickMuat.dataset.nomuat)}`);
                const first = response.data[0];
                selectedSJ.clear();
                response.data.forEach(row => selectedSJ.set(row.nosj, row));
                byId('no_muat_expedisi').value = first.nomuat;
                byId('tgl_muat_expedisi').value = first.tglmuat || '';
                byId('rute_muat_expedisi').value = first.rute || '';
                byId('kendaraan_kode_muat_expedisi').value = first.kendaraan_kode || '';
                byId('kendaraan_muat_expedisi').value = first.kendaraan || '';
                byId('plat_nomor_muat_expedisi').value = first.plat_nomor || '';
                byId('driver1_kode_muat_expedisi').value = first.driver1_kode || '';
                byId('driver1_muat_expedisi').value = first.driver1 || '';
                byId('driver2_kode_muat_expedisi').value = first.driver2_kode || '';
                byId('driver2_muat_expedisi').value = first.driver2 || '';
                byId('km_awal_muat_expedisi').value = first.km_awal ?? '';
                byId('km_akhir_muat_expedisi').value = first.km_akhir ?? '';
                byId('uang_jalan_muat_expedisi').value = first.uang_jalan ?? 0;
                byId('uang_driver_makan_muat_expedisi').value = first.uang_driver_makan ?? 0;
                byId('uang_lain_lain_muat_expedisi').value = first.uang_lain_lain ?? 0;
                updateMode = true;
                byId('muatModeBadge').textContent = 'MODE UPDATE';
                byId('muatModeBadge').className = 'badge bg-warning text-dark';
                byId('simpanMuatExpBtn').innerHTML = '<i class="bi bi-save me-1"></i>Update No Muat';
                byId('tampilkanPdfMuatExpBtn').classList.remove('d-none');
                renderSelected();
                modal('muatModalExp').hide();
            } catch (error) { alertMuat(error.message); }
        }

        const deleteMuat = event.target.closest('.delete-muat');
        if (deleteMuat) {
            if (!window.confirm(`Batalkan No Muat ${deleteMuat.dataset.nomuat}? Surat Jalan akan dilepas dari muatan.`)) return;
            try {
                const response = await fetch(urls.deleteMuat.replace('__NOMUAT__', encodeURIComponent(deleteMuat.dataset.nomuat)), {
                    method: 'DELETE',
                    headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest'},
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'No Muat gagal dibatalkan.');
                await loadMuat(1);
                alertMuat(result.message, 'success');
            } catch (error) { alertMuat(error.message); }
        }

        const removeSj = event.target.closest('.remove-sj');
        if (removeSj) { selectedSJ.delete(removeSj.dataset.nosj); renderSelected(); }

        const chooseVehicle = event.target.closest('.choose-vehicle');
        if (chooseVehicle) {
            byId('kendaraan_kode_muat_expedisi').value = chooseVehicle.dataset.code;
            byId('kendaraan_muat_expedisi').value = chooseVehicle.dataset.name;
            byId('plat_nomor_muat_expedisi').value = chooseVehicle.dataset.plat;
            modal('kendaraanModalExp').hide();
            try {
                const km = await getJson(`${urls.km}?kendaraan=${encodeURIComponent(chooseVehicle.dataset.code)}`);
                byId('km_awal_muat_expedisi').value = km.km_terakhir || 0;
            } catch (error) { alertMuat(error.message); }
        }

        const chooseDriver = event.target.closest('.choose-driver');
        if (chooseDriver) {
            byId(`driver${activeDriver}_kode_muat_expedisi`).value = chooseDriver.dataset.code;
            byId(`driver${activeDriver}_muat_expedisi`).value = chooseDriver.dataset.name;
            modal('driverModalExp').hide();
        }

        const chooseRoute = event.target.closest('.choose-route');
        if (chooseRoute) { byId('rute_muat_expedisi').value = chooseRoute.dataset.route; modal('ruteModalExp').hide(); }
    });

    byId('muatSjRows').addEventListener('input', event => {
        if (!event.target.matches('.pengirim-input')) return;
        const row = selectedSJ.get(event.target.dataset.nosj);
        if (row) row.pengirim = event.target.value;
    });
    byId('kendaraan_muat_expedisi_btn').addEventListener('click', () => { modal('kendaraanModalExp').show(); loadVehicles(1); });
    document.querySelectorAll('.driver-lookup').forEach(button => button.addEventListener('click', () => {
        activeDriver = button.dataset.driverTarget;
        modal('driverModalExp').show();
        loadDrivers(1);
    }));
    byId('rute_muat_btn').addEventListener('click', () => { modal('ruteModalExp').show(); loadRoutes(1); });
    byId('vehicleSearch').addEventListener('input', () => loadVehicles(1));
    byId('driverSearch').addEventListener('input', () => loadDrivers(1));
    byId('routeSearch').addEventListener('input', () => loadRoutes(1));

    byId('simpanMuatExpBtn').addEventListener('click', async () => {
        clearAlert();
        if (!selectedSJ.size) return alertMuat('Pilih minimal satu Surat Jalan terlebih dahulu.', 'warning');
        const kmAwal = Number(byId('km_awal_muat_expedisi').value || 0);
        const kmAkhir = Number(byId('km_akhir_muat_expedisi').value || 0);
        if (kmAkhir < kmAwal) return alertMuat('KM Akhir tidak boleh lebih kecil dari KM Awal.', 'warning');
        const payload = {
            tglmuat: byId('tgl_muat_expedisi').value,
            rute: byId('rute_muat_expedisi').value,
            kendaraan_kode: byId('kendaraan_kode_muat_expedisi').value,
            kendaraan: byId('kendaraan_muat_expedisi').value,
            plat_nomor: byId('plat_nomor_muat_expedisi').value,
            driver1_kode: byId('driver1_kode_muat_expedisi').value,
            driver1: byId('driver1_muat_expedisi').value,
            driver2_kode: byId('driver2_kode_muat_expedisi').value,
            driver2: byId('driver2_muat_expedisi').value,
            km_awal: kmAwal,
            km_akhir: kmAkhir,
            uang_jalan: Number(byId('uang_jalan_muat_expedisi').value || 0),
            uang_driver_makan: Number(byId('uang_driver_makan_muat_expedisi').value || 0),
            uang_lain_lain: Number(byId('uang_lain_lain_muat_expedisi').value || 0),
            nosj: [...selectedSJ.values()].map(row => row.nosj),
            pengirim: [...selectedSJ.values()].map(row => ({nosj: row.nosj, pengirim: row.pengirim || ''})),
        };
        const url = updateMode ? urls.updateMuat.replace('__NOMUAT__', encodeURIComponent(byId('no_muat_expedisi').value)) : urls.storeMuat;
        const button = byId('simpanMuatExpBtn');
        button.disabled = true;
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest'},
                body: JSON.stringify(payload),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Gagal menyimpan No Muat.');
            byId('no_muat_expedisi').value = result.nomuat;
            updateMode = true;
            byId('muatModeBadge').textContent = 'MODE UPDATE';
            byId('muatModeBadge').className = 'badge bg-warning text-dark';
            button.innerHTML = '<i class="bi bi-save me-1"></i>Update No Muat';
            byId('tampilkanPdfMuatExpBtn').classList.remove('d-none');
            alertMuat(result.message, 'success');
        } catch (error) { alertMuat(error.message); }
        finally { button.disabled = false; }
    });

    byId('tampilkanPdfMuatExpBtn').addEventListener('click', () => {
        const nomuat = byId('no_muat_expedisi').value;
        if (nomuat) window.open(urls.pdfMuat.replace('__NOMUAT__', encodeURIComponent(nomuat)), '_blank', 'noopener');
    });
    byId('clearMuatExpBtn').addEventListener('click', () => {
        selectedSJ.clear();
        updateMode = false;
        byId('formNoMuat').reset();
        byId('tgl_muat_expedisi').value = @json(now()->format('Y-m-d'));
        byId('no_muat_expedisi').value = '';
        byId('muatModeBadge').textContent = 'MODE TAMBAH';
        byId('muatModeBadge').className = 'badge bg-primary';
        byId('simpanMuatExpBtn').innerHTML = '<i class="bi bi-save me-1"></i>Simpan No Muat';
        byId('tampilkanPdfMuatExpBtn').classList.add('d-none');
        clearAlert();
        renderSelected();
    });
})();
</script>
@endpush
