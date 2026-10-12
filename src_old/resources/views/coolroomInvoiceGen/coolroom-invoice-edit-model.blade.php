{{-- Panduan Edit Invoice Coolroom --}}
<div class="modal fade" id="tutorialCoolroomEditInvoiceModal" tabindex="-1" aria-labelledby="tutorialCoolroomEditInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="tutorialCoolroomEditInvoiceModalLabel">Panduan Edit Invoice Coolroom</h5>
                    <p class="mb-0 small text-white-50">Temukan invoice, periksa nilainya, lalu simpan perubahan.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="note">
                    Halaman ini digunakan untuk memperbarui invoice Coolroom yang sudah dibuat. Filter membantu menemukan invoice berdasarkan periode atau customer.
                </div>

                <ol class="ps-3 mb-3">
                    <li class="mb-2">Isi rentang tanggal <strong>DARI</strong> dan <strong>SAMPAI</strong>. Isi <strong>CARI</strong> dengan nomor invoice atau nama customer bila diperlukan, lalu tekan <strong>Reload</strong>.</li>
                    <li class="mb-2">Pada baris invoice yang ingin diperbarui, klik ikon pensil di kolom <strong>AKSI</strong>.</li>
                    <li class="mb-2">Periksa invoice, customer, jumlah, mode Boxing, harga, diskon, PPN, dan total. Nomor invoice serta customer ditampilkan sebagai identitas transaksi.</li>
                    <li class="mb-2">Sesuaikan jumlah, harga, diskon, PPN, pembayaran, dan tanggal jatuh tempo sesuai kebutuhan. Piutang dan nilai total dihitung ulang.</li>
                    <li>Klik <strong>SIMPAN</strong>. Jika berhasil, modal tertutup dan daftar invoice dimuat kembali.</li>
                </ol>

                <div class="alert alert-warning mb-0" role="note">
                    <strong>Perhatian:</strong> Pembayaran tidak boleh melebihi Grand Total. Invoice yang sudah memiliki catatan pembayaran ditolak untuk diedit; hubungi admin untuk pemeriksaan lebih lanjut. TGL INV mengikuti tanggal penerbitan dan tidak diperbarui dari form ini; gunakan TGL JT untuk mengubah jatuh tempo.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
