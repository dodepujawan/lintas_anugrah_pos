# Panduan Piutang Sopir

Panduan ini digunakan untuk mencatat tanggungan sopir kepada perusahaan dan penerimaan pelunasannya. Piutang sopir dipisahkan dari piutang pelanggan.

## Sebelum Memulai

- Pastikan sopir sudah terdaftar pada menu **Master Sopir**.
- Pastikan mapping akun **Piutang Sopir** dan **Kas** sudah tersedia.
- Gunakan tanggal transaksi yang sebenarnya dan pastikan periodenya belum dikunci.
- Siapkan nomor bukti, berita acara, nomor kendaraan, serta dokumen pendukung.

## Saldo Awal Piutang Sopir

Gunakan menu ini hanya untuk tanggungan sopir yang sudah ada sebelum perusahaan mulai memakai modul Piutang Sopir pada aplikasi.

1. Buka **Transaksi > Saldo Awal Piutang Sopir** lalu klik **Input Saldo Awal**.
2. Periksa nomor saldo awal. Nomor harus unik dan disarankan menggunakan awalan `SAPS`.
3. Isi **Tanggal Saldo** sesuai tanggal cutover atau tanggal mulai penggunaan sistem.
4. Isi jatuh tempo bila tanggungan mempunyai batas pembayaran.
5. Pilih sopir, kemudian isi nomor kendaraan bila berkaitan dengan kendaraan tertentu.
6. Isi referensi dokumen lama, berita acara, atau nomor bukti sumber.
7. Isi nilai saldo yang benar-benar masih belum dibayar pada tanggal cutover.
8. Isi keterangan yang menjelaskan asal saldo, lalu klik **Simpan Saldo Awal**.

### Perlakuan Akuntansi Saldo Awal

- Input Saldo Awal Piutang Sopir **tidak membuat jurnal baru**.
- Saldo akun Piutang Sopir harus sudah terdapat dalam jurnal saldo awal atau proses migrasi akuntansi.
- Jangan memasukkan nilai awal penuh jika sebagian sudah dibayar sebelum cutover; masukkan hanya sisa yang masih terutang.
- Jangan mencatat transaksi baru pada menu Saldo Awal. Gunakan menu **Piutang Sopir** agar jurnal transaksi dibuat otomatis.

> Jika saldo awal dimasukkan di sini dan kembali dijurnal manual sebagai piutang baru, saldo Buku Besar dapat menjadi ganda. Pastikan nilai buku pembantu dan akun Piutang Sopir pada Buku Besar sama pada tanggal cutover.

### Edit dan Hapus Saldo Awal

- Saldo awal dapat diedit atau dihapus hanya selama belum pernah dibayar.
- Setelah mempunyai pelunasan posted, data tidak dapat diubah atau dihapus agar riwayat saldo tetap konsisten.
- Edit dan hapus saldo awal tidak mengubah jurnal karena transaksi ini memang tidak mempunyai jurnal sendiri.
- Transaksi pada periode terkunci tidak dapat diubah atau dihapus.

### Pemeriksaan Saldo Awal

- Jumlahkan seluruh saldo awal per tanggal cutover.
- Cocokkan dengan bagian saldo Piutang Sopir yang berasal dari data lama pada Buku Besar.
- Pastikan satu tanggungan tidak dimasukkan dua kali pada Saldo Awal dan Piutang Sopir biasa.
- Simpan dokumen sumber agar nomor dan nilai saldo dapat ditelusuri.

## Input Piutang Sopir

1. Buka **Transaksi > Piutang Sopir** lalu klik **Input Piutang**.
2. Periksa nomor transaksi. Nomor harus unik dan akan menjadi referensi jurnal.
3. Isi tanggal kejadian dan jatuh tempo bila ada.
4. Pilih sopir yang menanggung piutang.
5. Pilih jenis tanggungan: kerusakan kendaraan, kas bon, kehilangan barang, tilang, selisih setoran, atau lainnya.
6. Isi nomor kendaraan dan referensi/berita acara agar transaksi mudah ditelusuri.
7. Isi nilai tanggungan sesuai dokumen.
8. Pilih **Akun Piutang Sopir** sebagai akun debit.
9. Pilih **Akun Lawan/Kredit** sesuai asal transaksi.
10. Isi keterangan yang menjelaskan kejadian, lalu klik **Simpan & Jurnal**.

### Jurnal Input Piutang

- **Debit:** Piutang Sopir sebesar nilai tanggungan.
- **Kredit:** Akun lawan yang dipilih sebesar nilai tanggungan.

Contoh: kerusakan kendaraan 500.000 dapat dicatat Debit Piutang Sopir dan Kredit akun biaya/penggantian yang ditentukan bagian akuntansi.

> Pemilihan akun lawan harus mengikuti kebijakan akuntansi perusahaan. Jangan memilih Kas jika tidak ada pengeluaran kas yang benar-benar terjadi.

## Mengedit atau Menghapus Piutang

- Piutang hanya dapat diedit atau dihapus bila belum mempunyai pembayaran.
- Edit akan memperbarui data piutang dan jurnal asalnya.
- Jika tanggal berubah, nomor jurnal dapat dibuat ulang mengikuti tanggal baru.
- Hapus akan menghapus piutang beserta jurnal terkait; periksa dokumen sebelum melanjutkan.
- Transaksi pada periode terkunci tidak dapat diubah.

## Input Pelunasan Piutang Sopir

1. Buka **Transaksi > Pelunasan Piutang Sopir** lalu klik **Input Pelunasan**.
2. Pilih piutang. Sistem menampilkan nomor, nama sopir, dan saldo yang masih tersedia.
3. Isi tanggal penerimaan atau tanggal pemotongan.
4. Isi nilai bayar. Nilainya tidak boleh melebihi saldo piutang.
5. Pilih metode pembayaran.
6. Pilih akun pembayaran/debit yang sesuai.
7. Isi referensi dan keterangan, lalu klik **Simpan & Jurnal**.

## Pemilihan Metode dan Akun Pembayaran

- **CASH:** gunakan akun Kas yang benar-benar menerima uang.
- **TRANSFER:** gunakan akun Bank tujuan penerimaan.
- **POTONG GAJI:** gunakan akun utang gaji atau akun clearing potongan gaji sesuai kebijakan perusahaan.
- **POTONG UANG JALAN:** gunakan akun uang jalan/clearing yang sesuai, bukan otomatis Kas bila tidak ada penerimaan kas.

### Jurnal Pelunasan

- **Debit:** akun pembayaran yang dipilih sebesar nilai bayar.
- **Kredit:** Piutang Sopir sebesar nilai bayar.

Saldo piutang berkurang otomatis. Jika saldo menjadi nol, status berubah menjadi **LUNAS**.

## Edit dan Void Pelunasan

- Pelunasan berstatus **POSTED** dapat diedit selama periodenya belum dikunci dan belum di-void.
- Edit akan mengembalikan pengaruh pembayaran lama, menerapkan nilai baru, lalu memperbarui jurnal.
- Gunakan **Void** untuk membatalkan pembayaran yang sudah tidak boleh diedit atau memang batal.
- Void membuka kembali saldo piutang dan membuat jurnal pembalik; transaksi lama tetap tersimpan sebagai jejak audit.
- Jangan membuat pembayaran negatif untuk membatalkan transaksi.

## Pemeriksaan Setelah Simpan

- Pastikan transaksi muncul pada daftar Piutang Sopir atau Pelunasan Piutang Sopir.
- Buka **Laporan Piutang Sopir** untuk memeriksa nilai awal, pembayaran, saldo, status, dan jatuh tempo.
- Periksa jurnal dengan referensi nomor piutang `PS...` atau nomor pembayaran `BPS...`.
- Pastikan total pembayaran tidak melebihi nilai tanggungan.
- Cocokkan saldo laporan Piutang Sopir dengan saldo akun Piutang Sopir pada Buku Besar.

## Kesalahan yang Sering Terjadi

- Memilih akun Piutang Pelanggan, bukan Piutang Sopir.
- Memilih Kas untuk potong gaji padahal tidak ada uang kas masuk.
- Menginput nilai pelunasan lebih besar dari saldo.
- Menggunakan tanggal pada periode yang sudah dikunci.
- Tidak mengisi referensi berita acara sehingga transaksi sulit ditelusuri.
- Menghapus atau mengubah dokumen tanpa memeriksa jurnal terkait.
