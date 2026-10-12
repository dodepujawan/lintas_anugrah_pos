{{-- Modal Muat Expedisi --}}
<div class="modal fade" id="muatModalRentDgn" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Rental Muat Mobil Pendingin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Tanggal Mulai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_tgl_mulai_rentdgn">
                    </div>
                    <div class="col-md-3">
                        <label>Tanggal Akhir</label>
                        <input type="date" class="form-control form-control-sm" id="filter_tgl_akhir_rentdgn">
                    </div>
                    <div class="col-md-3">
                        <label>Filter Data</label>
                        <input type="text" class="form-control form-control-sm" id="filter_rent_dgn">
                    </div>
                    <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button class="btn btn-sm btn-info" id="btn_filter_rent_dgn">
                                    <i class='bx bx-filter'></i> Filter
                                </button>
                            </div>
                        </div>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalMuatRentDgnTable">
                    <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>NO MUAT</th>
                        <th>TGL MUAT</th>
                        <th>CUSTOMER</th>
                        <th>PESANAN</th>
                        <th>JUMLAH</th>
                        <th>HARGA</th>
                        <th>DISC</th>
                        <th>TOTAL</th>
                        <th>NO SJ</th>
                        <th width="120">AKSI</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Surjal Expedisi --}}
<div class="modal fade" id="surjalModalRentPendingin" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Expedisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Tanggal Mulai</label>
                        <input type="date" class="form-control form-control-sm" id="filter_tgl_mulai">
                    </div>
                    <div class="col-md-3">
                        <label>Tanggal Akhir</label>
                        <input type="date" class="form-control form-control-sm" id="filter_tgl_akhir">
                    </div>
                    <div class="col-md-3">
                        <label>Filter Data</label>
                        <input type="text" class="form-control form-control-sm" id="filter_surjal_rent_dgn">
                    </div>
                    <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button class="btn btn-sm btn-info" id="btn_filter_surjal_rent_dgn">
                                    <i class='bx bx-filter'></i> Filter
                                </button>
                            </div>
                        </div>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalSurjalRentDgnTable">
                    <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>NO SJ</th>
                        <th>TGL SJ</th>
                        <th>CUSTOMER</th>
                        <th>PESANAN</th>
                        <th>JUMLAH</th>
                        <th>HARGA</th>
                        <th>DISC</th>
                        <th>TOTAL</th>
                        <th width="120">AKSI</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Customer --}}
<div class="modal fade" id="customerModalDgn" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal pelanggan"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped w-100" id="modalCusDgnTable">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Jenis Usaha</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{{-- Modal Item --}}
<div class="modal fade" id="itemModalDgn" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div>
                    <h3 id="custNameDgn"></h3>
                    <h3 id="custKodeDgn"></h3>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalItemDgnTable">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Kendaraan</th>
                            <th>ITEM</th>
                            <th>PERIODE</th>
                            <th>PLAT</th>
                            <th>JENIS</th>
                            <th>HARGA</th>
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
{{-- Modal Driver --}}
<div class="modal fade" id="driverModalDgn" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalDriverDgnTable">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Phone</th>
                            <th>Mulai Kerja</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Kendaraan --}}
<div class="modal fade" id="kendaraanModalDgn" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Kendaraan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalKendaraanDgnTable">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Plat</th>
                            <th>Jenis</th>
                            <th>FNO PRK B</th>
                            <th>FNO PRK P</th>
                            <th>FNO PRK S</th>
                            <th>FNO PRK O</th>
                            <th>FNO PRK M</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Biaya -->
{{-- <div class="row mt-4">
    <div class="col-md-12">
        <div class="driver-section">
            <div class="driver-header">BIAYA TAMBAHAN</div>

            <!-- Input Biaya Baru -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">NO.BKK</label>
                    <input type="text" id="no_bkk_rent_dingin" name="no_bkk_rent_dingin"
                            class="form-control form-control-sm" placeholder="Masukkan nomor BKK">
                </div>
                <div class="col-md-6">
                    <label class="form-label">BIAYA</label>
                    <input type="text" id="biaya_rent_dingin" name="biaya_rent_dingin"
                            class="form-control form-control-sm" placeholder="Masukkan deskripsi biaya">
                </div>
                <div class="col-md-3">
                    <label class="form-label">INPUT BIAYA</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="input_biaya_rent_dingin" name="input_biaya_rent_dingin"
                                class="form-control form-control-sm" placeholder="0">
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Biaya -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <button class="btn btn-sm btn-outline-success btn-action">TAMBAH BIAYA</button>
                    <button class="btn btn-sm btn-outline-warning btn-action">REFRESH BKK</button>
                </div>
            </div>

            <!-- Tabel Daftar Biaya -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-custom">
                    <thead>
                        <tr>
                            <th width="5%">NO</th>
                            <th width="20%">NO.BKK</th>
                            <th width="45%">BIAYA</th>
                            <th width="20%">JUMLAH</th>
                            <th width="10%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td>BKK001</td>
                            <td>Biaya Tol</td>
                            <td class="text-end">Rp 150.000</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger btn-action">Hapus</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td>BKK002</td>
                            <td>Biaya Parkir</td>
                            <td class="text-end">Rp 75.000</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger btn-action">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> --}}

{{-- Panduan Rent Pendingin --}}
<div class="modal fade" id="tutorialRentPendinginModal" tabindex="-1" aria-labelledby="tutorialRentPendinginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="tutorialRentPendinginModalLabel">Panduan Rent Pendingin</h5>
                    <p class="mb-0 small text-white-50">Cara membuat atau mengedit Surat Jalan dan memproses No Muat.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="note">
                    Simpan terlebih dahulu data sewa sebagai Surat Jalan (SJ). No SJ dibuat otomatis oleh sistem. Untuk user admin, SJ tersebut kemudian dapat diproses untuk membuat atau memperbarui No Muat.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="tutorialSurjalRentPendingin">
                            <h6 class="fw-bold text-primary mb-3" id="tutorialSurjalRentPendingin"><i class="bx bx-file me-1"></i>Membuat atau mengedit SJ</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Isi tanggal SJ dan wilayah, lalu pilih <strong>Customer</strong> melalui tombol cari.</li>
                                <li class="mb-2">Periksa nama, telepon, dan alamat penerima. Pilih <strong>Item</strong>, lalu isi jumlah hari dan harga per hari.</li>
                                <li class="mb-2">Pilih driver dan kendaraan. Diskon dapat diisi dalam persen; pajak diambil dari pengaturan sistem. Subtotal, DPP, dan Total dihitung otomatis.</li>
                                <li class="mb-2">Klik <strong>Simpan</strong>. No SJ dibuat otomatis; tombol Print Surat Jalan tersedia setelah berhasil.</li>
                                <li>Untuk mengedit, klik cari di samping <strong>No. Surat Jalan</strong>, pilih SJ, ubah data, lalu klik <strong>UPDATE</strong>.</li>
                            </ol>
                        </section>
                    </div>
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="tutorialMuatRentPendingin">
                            <h6 class="fw-bold text-success mb-3" id="tutorialMuatRentPendingin"><i class="bx bx-car me-1"></i>Memproses No Muat</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Setelah SJ tersimpan atau dipilih untuk diedit, user admin dapat mengisi tanggal muat dan menekan <strong>PROSES NOMUAT</strong>.</li>
                                <li class="mb-2">Jika SJ belum memiliki No Muat, sistem membuat nomor secara otomatis. Jika SJ sudah memiliki No Muat, proses ini memperbarui datanya.</li>
                                <li>Untuk mengedit muatan yang sudah ada, klik cari di samping <strong>No. Muat</strong>, pilih data, periksa isian, lalu klik <strong>UPDATE NOMUAT</strong>.</li>
                            </ol>
                        </section>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0" role="note">
                    <strong>Perhatian:</strong> SJ yang sudah terhubung ke invoice atau GB tidak dapat diedit. Tombol <strong>Clear</strong> hanya mengosongkan form di layar dan tidak menghapus data yang tersimpan. Fitur No Muat hanya tersedia untuk user admin.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
