{{-- Modal Muat Expedisi --}}
<div class="modal fade" id="surjalModalExp" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Expedisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal"></button>
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
                        <input type="text" class="form-control form-control-sm" id="filter_surjal">
                    </div>
                    <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button class="btn btn-sm btn-info" id="btn_filter_surjal">
                                    <i class='bx bx-filter'></i> Filter
                                </button>
                            </div>
                        </div>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalSurjalExpTable">
                    <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>NO SJ</th>
                        <th>TGL SJ</th>
                        <th>NO MUAT</th>
                        <th>CUSTOMER</th>
                        <th>RUTE</th>
                        <th>JUMLAH</th>
                        <th>HARGA</th>
                        <th>DISC</th>
                        <th>DEL CHARGE</th>
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
{{-- Modal Muat Expedisi --}}
<div class="modal fade" id="muatModalExp" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Expedisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal"></button>
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
                        <input type="text" class="form-control form-control-sm" id="filter_muat">
                    </div>
                    <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button class="btn btn-sm btn-info" id="btn_filter_muat">
                                    <i class='bx bx-filter'></i> Filter
                                </button>
                            </div>
                        </div>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalMuatExpTable">
                    <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>NO MUAT</th>
                        <th>TGL MUAT</th>
                        <th>CUSTOMER</th>
                        <th>RUTE</th>
                        <th>JUMLAH</th>
                        <th>HARGA</th>
                        <th>DISC</th>
                        <th>DEL CHARGE</th>
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
{{-- Modal Customer --}}
<div class="modal fade" id="customerModalExp" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped w-100" id="modalCusExpTable">
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
<div class="modal fade" id="itemModalExp" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal"></button>
            </div>
            <div class="modal-body">
                <div>
                    <h3 id="custNameExp"></h3>
                    <h3 id="custKodeExp"></h3>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalItemExpTable">
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
{{-- Modal Kendaraan --}}
<div class="modal fade" id="kendaraanModalExp" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Kendaraan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalKendaraanExpTable">
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
{{-- Modal Driver --}}
<div class="modal fade" id="driverModalExp" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="modalDriverExpTable">
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
<!-- Modal Tabel Rute -->
<!-- Modal -->
<div class="modal fade" id="ruteModalExp" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Rute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal"></button>
            </div>
            <div class="modal-body">
                {{-- <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0 text-dark">Data Rute</h2>
                    <button class="btn btn-primary btn-sm" id="tambah_rute">
                        <i class="fas fa-plus me-1"></i>Tambah Rute
                    </button>
                </div> --}}
                <div class="table-responsive">
                    <table id="ruteTableExp" class="table table-striped table-bordered table-hover w-100">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th>RUTE</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan di-load oleh DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tambah Rute -->
{{-- <div class="modal fade" id="addRuteModalExp" tabindex="-1" aria-labelledby="addRuteModalExpLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRuteModalExpLabel">Tambah Rute Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="add_rute_flag">
                <form id="ruteForm">
                    @csrf
                    <div class="mb-3">
                        <label for="newRute" class="form-label">Nama Rute</label>
                        <input type="text" class="form-control" id="newRuteExp" name="newRuteExp" placeholder="Contoh: DIY - DPS" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveRute()">Simpan</button>
            </div>
        </div>
    </div>
</div> --}}

{{-- Panduan Surat Jalan --}}
<div class="modal fade" id="panduanSuratJalanModalExp" tabindex="-1" aria-labelledby="panduanSuratJalanModalExpLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="panduanSuratJalanModalExpLabel">Panduan Surat Jalan (SJ)</h5>
                    <p class="mb-0 small text-white-50">Petunjuk membuat dan mengedit Surat Jalan Expedisi.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-4" role="note">
                    <strong>Apa itu Surat Jalan?</strong>
                    <div>SJ mencatat transaksi pengiriman, seperti customer, tujuan, barang, kendaraan, penerima, jumlah, dan biaya. Nomor SJ dibuat otomatis saat data baru disimpan. Proses No Muat dikelola terpisah dari penyimpanan SJ.</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanBuatSjExp">
                            <h6 class="fw-bold text-primary mb-3" id="panduanBuatSjExp"><i class="bx bx-file me-1"></i>Membuat SJ baru</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Isi <strong>Tgl SJ</strong>. Nomor SJ akan diberikan oleh sistem setelah disimpan.</li>
                                <li class="mb-2">Pilih <strong>Customer</strong> dan <strong>Item</strong> melalui tombol cari.</li>
                                <li class="mb-2">Lengkapi kendaraan, driver, rute, penerima, alamat, dan informasi barang.</li>
                                <li class="mb-2">Isi jumlah dan harga. Diskon serta Del Charge dapat disesuaikan; tombol <strong>Auto DC</strong> menghitung Del Charge sebesar 5% dari jumlah x harga.</li>
                                <li>Pastikan tanggal SJ, customer, rute, jumlah (lebih dari 0), dan harga sudah terisi. Periksa total, lalu klik <strong>SIMPAN</strong>.</li>
                            </ol>
                            <p class="small text-muted mt-3 mb-0">Subtotal, DPP, PPN, dan Grand Total dihitung otomatis dari nilai yang diisi.</p>
                        </section>
                    </div>
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanEditSjExp">
                            <h6 class="fw-bold text-success mb-3" id="panduanEditSjExp"><i class="bx bx-edit me-1"></i>Mengedit SJ</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Klik tombol cari di samping kolom <strong>No SJ</strong>.</li>
                                <li class="mb-2">Cari SJ yang ingin diperbaiki, lalu klik tombol centang <strong>Pilih</strong>.</li>
                                <li class="mb-2">Data SJ akan dimuat ke form dan tombol <strong>SIMPAN</strong> berubah menjadi <strong>UPDATE</strong>.</li>
                                <li>Periksa dan perbaiki data, lalu klik <strong>UPDATE</strong> untuk menyimpan perubahan.</li>
                            </ol>
                        </section>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0" role="note">
                    <strong>Perhatian:</strong> Data SJ yang sudah memiliki invoice atau GB tidak dapat diedit. Pastikan SJ yang dipilih benar sebelum melakukan perubahan.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
{{-- Panduan No Muat --}}
<div class="modal fade" id="panduanNoMuatModalExp" tabindex="-1" aria-labelledby="panduanNoMuatModalExpLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="panduanNoMuatModalExpLabel">Panduan No Muat</h5>
                    <p class="mb-0 small text-white-50">Cara menggabungkan Surat Jalan ke dalam satu muatan.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan No Muat"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-4" role="note">
                    <strong>Apa itu No Muat?</strong>
                    <div>No Muat adalah nomor untuk satu perjalanan muatan yang dapat berisi satu atau beberapa Surat Jalan (SJ). Informasi kendaraan, driver, rute, biaya perjalanan, dan daftar SJ disimpan bersama muatan tersebut.</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanBuatMuatExp">
                            <h6 class="fw-bold text-primary mb-3" id="panduanBuatMuatExp"><i class="bx bx-plus-circle me-1"></i>Membuat No Muat</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Siapkan SJ yang akan dimuat. SJ baru yang disimpan saat belum terikat No Muat akan masuk ke tabel muatan. SJ lama yang belum memiliki No Muat dapat dipilih, lalu ditambahkan lewat tombol <strong>TAMBAH MUATAN</strong>.</li>
                                <li class="mb-2">Ulangi untuk setiap SJ yang ikut dalam perjalanan. Periksa nomor SJ dan hapus baris yang keliru sebelum menyimpan.</li>
                                <li class="mb-2">Isi tanggal dan rute muat, lalu pilih kendaraan dan driver melalui tombol cari. KM Awal dapat terisi otomatis dari data kilometer kendaraan.</li>
                                <li class="mb-2">Isi KM Akhir serta Uang Jalan, Uang Driver + Makan, dan Uang Lain-lain. Pastikan KM Akhir tidak lebih kecil dari KM Awal.</li>
                                <li>Pastikan ada setidaknya satu SJ, lalu klik <strong>Simpan No Muat</strong>. Nomor No Muat dibuat otomatis; setelah berhasil tombol berubah menjadi <strong>Update No Muat</strong>.</li>
                            </ol>
                        </section>
                    </div>
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanEditMuatExp">
                            <h6 class="fw-bold text-success mb-3" id="panduanEditMuatExp"><i class="bx bx-edit me-1"></i>Mengedit No Muat</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Klik tombol cari di samping <strong>No Muat</strong>, cari muatan, lalu klik tombol centang <strong>Pilih</strong>.</li>
                                <li class="mb-2">Data header dan daftar SJ akan dimuat ke form. Periksa kembali daftar SJ, kendaraan, driver, rute, kilometer, dan biaya.</li>
                                <li class="mb-2">Ubah data yang diperlukan. Hapus baris SJ hanya jika memang ingin mengeluarkannya dari muatan; SJ yang belum terikat dapat ditambahkan melalui alur <strong>TAMBAH MUATAN</strong>.</li>
                                <li>Pastikan KM Akhir tidak lebih kecil dari KM Awal dan masih ada minimal satu SJ, lalu klik <strong>Update No Muat</strong>.</li>
                            </ol>
                        </section>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0" role="note">
                    <strong>Perhatian:</strong> <strong>Clear No Muat</strong> hanya mengosongkan form di layar. Untuk membatalkan muatan tersimpan, gunakan ikon tempat sampah pada daftar No Muat; SJ akan dilepas dari muatan, bukan dihapus. Pembatalan tidak dapat dilakukan jika muatan sudah terkait GB atau invoice. Tombol <strong>Tampilkan PDF</strong> membuka dokumen tanpa menyimpan perubahan.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>

  <!-- Action Buttons -->
    {{-- <div class="card-expedisi">
        <div class="row g-2">
            <div class="col-md-3 col-sm-6">
                <button class="btn btn-primary btn-action w-100">
                    <i class='bx bx-file me-1'></i>NEW [F1]
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button class="btn btn-success btn-action w-100">
                    <i class='bx bx-plus-circle me-1'></i>TAMBAH [F2]
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button class="btn btn-info btn-action w-100">
                    <i class='bx bx-save me-1'></i>SIMPAN [F3]
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button class="btn btn-warning btn-action w-100">
                    <i class='bx bx-edit me-1'></i>EDIT [F4]
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button class="btn btn-secondary btn-action w-100">
                    <i class='bx bx-x-circle me-1'></i>BATAL [F5]
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button class="btn btn-danger btn-action w-100">
                    <i class='bx bx-trash me-1'></i>HAPUS [F6]
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button class="btn btn-outline-primary btn-action w-100">
                    <i class='bx bx-arrow-back me-1'></i>RETUR SJ [F7]
                </button>
            </div>
            <div class="col-md-3 col-sm-6">
                <button class="btn btn-outline-danger btn-action w-100">
                    <i class='bx bx-log-out me-1'></i>KELUAR [F12]
                </button>
            </div>
        </div>
    </div> --}}
