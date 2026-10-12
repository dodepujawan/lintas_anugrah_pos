{{-- Panduan Invoice Coolroom --}}
<div class="modal fade" id="tutorialCoolroomInvoiceModal" tabindex="-1" aria-labelledby="tutorialCoolroomInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="tutorialCoolroomInvoiceModalLabel">Panduan Invoice Coolroom</h5>
                    <p class="mb-0 small text-white-50">Langkah membuat invoice dari SJ dan memperbarui invoice yang sudah dibuat.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="note">
                    Invoice Coolroom dibuat dari transaksi/SJ yang sudah tersimpan. Rincian customer dan tagihan ditampilkan dari transaksi tersebut; nomor faktur dibuat otomatis saat invoice baru diproses.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="tutorialBuatInvoiceCoolroom">
                            <h6 class="fw-bold text-primary mb-3" id="tutorialBuatInvoiceCoolroom"><i class="bx bx-file me-1"></i>Membuat invoice</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Pilih status <strong>Belum Invoice</strong>, cari SJ yang akan ditagihkan, lalu klik <strong>Proses</strong> pada kolom Aksi.</li>
                                <li class="mb-2">Periksa nomor SJ, tanggal, customer, jumlah, dan Grand Total. Rincian transaksi ditampilkan dari data Coolroom.</li>
                                <li class="mb-2">Isi <strong>Bayar</strong> sesuai pembayaran yang diterima, masukkan <strong>TOP</strong>, lalu periksa <strong>Tgl JTP</strong> (tanggal jatuh tempo). Piutang dihitung otomatis; Bayar tidak boleh melebihi Grand Total.</li>
                                <li>Klik <strong>PROSES INVOICE</strong>. Invoice tersimpan dengan nomor faktur otomatis dan sistem mencoba mencetak melalui print service lokal.</li>
                            </ol>
                        </section>
                    </div>
                    <div class="col-md-6">
                        <section class="h-100 border rounded p-3 bg-white" aria-labelledby="tutorialEditInvoiceCoolroom">
                            <h6 class="fw-bold text-success mb-3" id="tutorialEditInvoiceCoolroom"><i class="bx bx-edit me-1"></i>Mengedit invoice</h6>
                            <ol class="ps-3 mb-0">
                                <li class="mb-2">Pilih status <strong>Sudah Invoice</strong>, lalu klik aksi edit pada invoice yang ingin diperiksa.</li>
                                <li class="mb-2">Periksa pembayaran, TOP, tanggal jatuh tempo, serta detail invoice yang dimuat.</li>
                                <li class="mb-2">Sesuaikan pembayaran dan informasi jatuh tempo jika diperlukan, lalu klik <strong>PROSES INVOICE</strong> untuk menyimpan.</li>
                                <li>Gunakan tombol <strong>PRINT</strong> atau <strong>PDF</strong> setelah invoice yang sudah ada berhasil dimuat.</li>
                            </ol>
                        </section>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0" role="note">
                    <strong>Perhatian:</strong> Jika print service lokal tidak aktif, invoice tetap dapat tersimpan meskipun proses cetaknya gagal. Jika sistem menolak edit karena invoice sudah memiliki pembayaran, hubungi admin untuk pemeriksaan.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
