# Import Data

Import digunakan untuk memindahkan data secara massal. Selalu unduh template dari sistem dan jangan mengubah nama kolom.

## Pilih jenis yang benar

| Jenis | Digunakan untuk | Membuat jurnal |
|---|---|---|
| Penjualan | Invoice sejak tanggal cutover | Ya |
| Hutang | Faktur sejak tanggal cutover | Ya |
| Saldo Piutang | Invoice lama yang masih terbuka | Tidak |
| Saldo Hutang | Faktur lama yang masih terbuka | Tidak |

## Langkah aman

1. Buat backup database sebelum import besar.
2. Unduh template sesuai jenis data.
3. Isi kode, tanggal, nomor dokumen, akun, dan nominal tanpa formula Excel.
4. Pastikan transaksi berjalan bertanggal sejak cutover dan saldo awal sebelum cutover.
5. Unggah file dan baca seluruh hasil validasi.
6. Setelah berhasil, periksa jurnal, daftar hutang/piutang, dan total data.

## Pencegahan duplikasi

- Jangan mengunggah ulang file yang sudah berhasil tanpa memeriksa nomor dokumen.
- Saldo awal tidak membuat jurnal; akun kontrolnya harus sudah ada di Saldo Awal Akun.
- Transaksi berjalan membuat jurnal otomatis, sehingga jangan dijurnal ulang secara manual.
- Perbaiki baris yang ditolak pada sumber data, lalu import kembali hanya data yang belum masuk.

