{{-- Panduan Edit Invoice Rent Pendingin --}}
<div class="modal fade" id="tutorialEditRentInvoiceModal" tabindex="-1" aria-labelledby="tutorialEditRentInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="tutorialEditRentInvoiceModalLabel">Panduan Edit Invoice Rent Pendingin</h5>
                    <p class="mb-0 small text-white-50">Cari invoice, perbarui informasi, dan simpan koreksi.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup panduan"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="note">
                    Halaman ini digunakan untuk memperbarui invoice Rent Pendingin yang sudah dibuat. Filter tanggal membantu menemukan invoice yang akan diedit.
                </div>

                <ol class="ps-3 mb-3">
                    <li class="mb-2">Isi tanggal <strong>DARI</strong> dan <strong>SAMPAI</strong>, lalu tekan <strong>Reload</strong> untuk memfilter daftar invoice.</li>
                    <li class="mb-2">Pada baris invoice yang ingin diperbarui, klik ikon pensil di kolom <strong>AKSI</strong>.</li>
                    <li class="mb-2">Periksa invoice, customer, kendaraan, driver, No Muat, serta nilai tagihan. Data identitas utama ditampilkan hanya-baca.</li>
                    <li class="mb-2">Jika perlu, pilih item lewat tombol cari. Sesuaikan jumlah, harga, diskon, Del Charge, PPN, tanggal jatuh tempo, pembayaran, atau keterangan.</li>
                    <li>Klik <strong>SIMPAN</strong>. Subtotal, total, Grand, dan Piutang dihitung ulang; daftar invoice akan dimuat kembali jika penyimpanan berhasil.</li>
                </ol>

                <div class="alert alert-warning mb-0" role="note">
                    <strong>Perhatian:</strong> Pastikan pembayaran tidak melebihi Grand Total. Jika sistem menolak edit karena invoice sudah memiliki pembayaran, hubungi admin untuk pemeriksaan. Jangan mencoba melewati pembatasan tersebut.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
