{{-- Panduan Transaksi Coolroom --}}
<div class="modal fade" id="tutorialCoolroomModal" tabindex="-1" aria-labelledby="tutorialCoolroomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="tutorialCoolroomModalLabel">Panduan Transaksi Coolroom</h5>
                    <p class="mb-0 small text-white-50">Cara membuat atau memperbarui Surat Jalan Coolroom.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="note">
                    Transaksi Coolroom mencatat customer, jumlah barang dalam kilogram, harga, dan perhitungan tagihan. Nomor SJ dibuat otomatis ketika transaksi baru disimpan.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanBuatCoolroom">
                            <h6 class="fw-bold text-primary mb-3" id="panduanBuatCoolroom"><i class="bx bx-plus-circle me-1"></i>Membuat transaksi</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Klik <strong>Transaksi Baru</strong>, lalu isi tanggal SJ dan pilih customer melalui tombol cari.</li>
                                <li class="mb-2">Isi <strong>Jumlah KG</strong> dan <strong>Harga @</strong>. Tanpa Harga Boxing, subtotal dihitung dari jumlah kg x harga per kg.</li>
                                <li class="mb-2">Centang <strong>Harga Boxing</strong> jika harga yang diisi merupakan harga total satu paket; dalam mode ini jumlah kg tidak dikalikan lagi.</li>
                                <li class="mb-2">Atur diskon, periksa PPN yang diisi dari pengaturan pajak, lalu lengkapi keterangan bila perlu. Subtotal, potongan, DPP, PPN, dan Grand Total dihitung otomatis.</li>
                                <li>Klik <strong>SIMPAN</strong>. Setelah berhasil, data masuk ke daftar transaksi dan mendapat nomor SJ.</li>
                            </ol>
                        </section>
                    </div>
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanEditCoolroom">
                            <h6 class="fw-bold text-success mb-3" id="panduanEditCoolroom"><i class="bx bx-edit me-1"></i>Memperbarui transaksi</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Cari transaksi di tabel, lalu klik ikon edit pada kolom <strong>Aksi</strong>.</li>
                                <li class="mb-2">Periksa data customer, tanggal, jumlah, harga, opsi Harga Boxing, diskon, dan PPN.</li>
                                <li class="mb-2">Ubah nilai yang diperlukan dan pastikan Grand Total sudah sesuai.</li>
                                <li>Klik <strong>UPDATE</strong> untuk menyimpan perubahan.</li>
                            </ol>
                        </section>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0" role="note">
                    <strong>Perhatian:</strong> Transaksi yang sudah memiliki invoice tidak dapat diedit. Tombol <strong>KELUAR</strong> hanya menutup form dan kembali ke daftar; tidak menghapus transaksi.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
