{{-- Panduan Invoice Rent Pendingin --}}
<div class="modal fade" id="tutorialRentPendinginInvoiceModal" tabindex="-1" aria-labelledby="tutorialRentPendinginInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="tutorialRentPendinginInvoiceModalLabel">Panduan Invoice Mobil Pendingin</h5>
                    <p class="mb-0 small text-white-50">Cara membuat invoice, memperbarui data, dan mencetak dokumen.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="note">
                    Gunakan filter status untuk memilih data yang belum memiliki invoice atau membuka invoice yang sudah dibuat. Tanggal invoice, rincian muatan/SJ, dan nilai tagihan ditampilkan dari data Rent Pendingin.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanBuatInvoiceRentDgn">
                            <h6 class="fw-bold text-primary mb-3" id="panduanBuatInvoiceRentDgn"><i class="bx bx-file me-1"></i>Membuat invoice</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Pilih status <strong>Belum Invoice</strong>, cari data yang akan diproses, lalu klik <strong>Proses</strong> pada kolom Aksi.</li>
                                <li class="mb-2">Periksa customer, No Muat, No SJ, kendaraan, dan ringkasan tagihan. Data rincian invoice bersifat hanya-baca.</li>
                                <li class="mb-2">Isi <strong>Bayar</strong> sesuai uang yang diterima, <strong>TOP</strong> dengan angka 0 atau lebih, dan periksa <strong>Tgl JTP</strong> (tanggal jatuh tempo). Piutang menyesuaikan nominal bayar.</li>
                                <li>Klik <strong>PROSES</strong>. Nomor faktur dibuat otomatis. Setelah berhasil, sistem mencoba mencetak melalui print service lokal.</li>
                            </ol>
                        </section>
                    </div>
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="panduanEditInvoiceRentDgn">
                            <h6 class="fw-bold text-success mb-3" id="panduanEditInvoiceRentDgn"><i class="bx bx-edit me-1"></i>Membuka dan mengedit invoice</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Pilih status <strong>Sudah Invoice</strong>, lalu klik tombol edit pada invoice yang ingin diperiksa.</li>
                                <li class="mb-2">Periksa data invoice. Jika perubahan diizinkan, sesuaikan Bayar, TOP, atau tanggal jatuh tempo.</li>
                                <li class="mb-2">Klik <strong>PROSES</strong> untuk menyimpan perubahan.</li>
                                <li>Gunakan <strong>Print</strong> atau <strong>Cetak PDF</strong> untuk mencetak ulang jika tombol tersedia.</li>
                            </ol>
                        </section>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0" role="note">
                    <strong>Perhatian:</strong> Jika sistem menolak edit karena invoice sudah memiliki pembayaran, jangan mencoba mengubahnya lewat cara lain; hubungi admin untuk pemeriksaan. Filter Customer dan tanggal pada bagian <strong>Export Laporan Excel</strong> hanya untuk ekspor laporan.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
