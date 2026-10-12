{{-- Panduan Proses Invoice Expedisi --}}
<div class="modal fade" id="tutorialInvoiceGenExpModal" tabindex="-1" aria-labelledby="tutorialInvoiceGenExpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="tutorialInvoiceGenExpModalLabel">Panduan Proses Invoice Expedisi</h5>
                    <p class="mb-0 small text-white-50">Langkah membuat invoice dari Surat Jalan dan memperbarui invoice yang sudah ada.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="note">
                    Invoice dibuat dari data muatan/SJ Expedisi. Informasi customer, kendaraan, nomor muat, nomor SJ, dan rincian total ditampilkan dari data yang dipilih.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanBuatInvoiceExpGen">
                            <h6 class="fw-bold text-primary mb-3" id="panduanBuatInvoiceExpGen"><i class="bx bx-file me-1"></i>Membuat invoice</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Pilih status <strong>Belum Invoice</strong>, cari data yang akan diproses, lalu klik <strong>Buat Invoice</strong> pada kolom Aksi.</li>
                                <li class="mb-2">Periksa kembali customer, kendaraan, nomor muat/SJ, dan ringkasan tagihan. Rincian tagihan di form bersifat hanya-baca.</li>
                                <li class="mb-2">Isi nominal <strong>Bayar</strong> sesuai pembayaran yang diterima. Isi <strong>TOP (Hari)</strong> dengan angka 0 atau lebih dan periksa <strong>Tgl JTP</strong> (tanggal jatuh tempo).</li>
                                <li>Klik <strong>PROSES</strong>. Nomor faktur dibuat oleh sistem dan hasilnya ditampilkan setelah berhasil.</li>
                            </ol>
                        </section>
                    </div>
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanEditInvoiceExpGen">
                            <h6 class="fw-bold text-success mb-3" id="panduanEditInvoiceExpGen"><i class="bx bx-edit me-1"></i>Mengedit invoice</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Pilih status <strong>Sudah Invoice</strong>, cari invoice yang ingin diperbarui, lalu klik <strong>Edit</strong> pada kolom Aksi.</li>
                                <li class="mb-2">Periksa informasi invoice dan ubah nominal <strong>Bayar</strong>, <strong>TOP</strong>, atau <strong>Tgl JTP</strong> sesuai kebutuhan.</li>
                                <li>Klik <strong>PROSES</strong> untuk menyimpan perubahan. Gunakan <strong>Print</strong> atau <strong>Cetak PDF</strong> bila tombol tersebut tersedia.</li>
                            </ol>
                        </section>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0" role="note">
                    <strong>Perhatian:</strong> Invoice yang sudah memiliki pembayaran tidak dapat diedit melalui form ini. Jika perlu koreksi, hubungi admin agar perubahan diperiksa terlebih dahulu.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
