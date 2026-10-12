{{-- Panduan Edit Invoice Expedisi --}}
<div class="modal fade" id="tutorialEditInvoiceExpModal" tabindex="-1" aria-labelledby="tutorialEditInvoiceExpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="tutorialEditInvoiceExpModalLabel">Panduan Edit Invoice Expedisi</h5>
                    <p class="mb-0 small text-white-50">Cari invoice, periksa rincian, lalu simpan koreksi dengan aman.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="note">
                    Gunakan halaman ini untuk memperbarui invoice Expedisi yang sudah dibuat. Periksa nomor invoice dan rincian SJ sebelum mengubah nilai.
                </div>

                <ol class="ps-3 mb-3">
                    <li class="mb-2">Atur rentang tanggal pada <strong>DARI</strong> dan <strong>SAMPAI</strong>, atau cari nomor invoice/nama customer di kolom <strong>CARI</strong>. Tekan <strong>Reload</strong> untuk menerapkan filter.</li>
                    <li class="mb-2">Pada invoice yang ingin diperbarui, klik ikon pensil di kolom <strong>AKSI</strong>.</li>
                    <li class="mb-2">Periksa customer, kendaraan, driver, No Muat, serta daftar detail No SJ. Untuk mengganti item, gunakan tombol cari pada kolom <strong>ITEM</strong>.</li>
                    <li class="mb-2">Perbarui tanggal invoice/jatuh tempo, jumlah, harga, diskon, Del Charge, PPN, pembayaran, atau keterangan sesuai kebutuhan. Subtotal, total, Grand, dan Piutang akan dihitung ulang saat nilainya diubah.</li>
                    <li>Klik <strong>SIMPAN</strong> setelah semua data benar. Jika berhasil, invoice di daftar akan dimuat ulang.</li>
                </ol>

                <div class="alert alert-warning mb-0" role="note">
                    <strong>Perhatian:</strong> Invoice yang sudah memiliki pembayaran tidak dapat diedit melalui halaman ini. Jika koreksi tetap diperlukan, hubungi admin untuk pemeriksaan lebih lanjut.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
