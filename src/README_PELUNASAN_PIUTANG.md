# Panduan Proses Pelunasan Piutang

Panduan ini digunakan untuk mencatat uang yang diterima dari customer atas satu atau beberapa invoice yang masih mempunyai saldo, termasuk potongan PPh Pasal 23, kerugian pembayaran, dan administrasi bank.

## 1. Persiapan

Pastikan customer dan invoice sudah tersedia, tanggal pembayaran berada pada periode terbuka, serta bukti pembayaran sudah diperiksa.

Mapping pada kelompok **Pelunasan Piutang** harus diatur sebagai berikut:

| Mapping | Akun yang disarankan | Digunakan untuk |
|---|---|---|
| Piutang Usaha | Akun Piutang Usaha | Kredit jurnal pelunasan |
| Kas | Akun Kas | Pembayaran metode Cash |
| Bank Transfer Pelunasan Piutang | `01010201 - Bank BCA Rek 6115352010` | Penerimaan metode Transfer |
| PPh Pasal 23 | `01010502 - PPh Pasal 23` | PPh 23 yang dipotong customer |
| Kerugian Pembayaran Piutang | `05040103 - Kerugian Pembayaran Piutang` | Potongan atau selisih pembayaran |
| Beban Administrasi Bank | `05040102 - Beban Administrasi Bank` | Biaya administrasi yang mengurangi penerimaan |

> Mapping hanya menampilkan akun aktif dengan level Akun Posting. Periksa mapping sebelum transaksi pertama digunakan.

## 2. Membuka form

1. Buka **Piutang > Pelunasan Piutang**.
2. Klik **Tambah Pelunasan**.
3. Tanggal awal terisi tanggal hari ini dan jenis pembayaran awal adalah **Transfer**.
4. Akun pembayaran otomatis mengikuti mapping Bank Transfer Pelunasan Piutang.

Jika jenis pembayaran diubah, akun mengikuti mapping terkait:

| Jenis bayar | Akun awal |
|---|---|
| Cash | Mapping Kas |
| Transfer | Mapping Bank Transfer Pelunasan Piutang |
| Giro, Cek, atau QRIS | Mapping Bank |

Akun pembayaran tetap dapat diganti manual bila dana masuk ke akun yang berbeda.

## 3. Memilih customer

Customer dapat dipilih dengan dua cara:

1. Ketik minimal dua karakter kode atau nama pada kolom Kode Customer, lalu pilih hasil autocomplete.
2. Klik ikon pencarian untuk membuka modal. Seluruh customer langsung ditampilkan dan dapat disaring berdasarkan kode atau nama.

Setelah customer dipilih, pastikan Nama Customer terisi, lalu klik **Cari Invoice**. Hanya invoice customer tersebut yang masih mempunyai saldo yang ditampilkan.

## 4. Pengertian kolom pembayaran

| Kolom | Pengertian |
|---|---|
| Uang Diterima | Uang bersih yang benar-benar masuk ke kas atau rekening bank. |
| PPh 23 | PPh Pasal 23 yang dipotong customer berdasarkan bukti potong. |
| Kerugian | Potongan atau selisih pembayaran yang diakui sebagai Kerugian Pembayaran Piutang. |
| Adm. Bank | Biaya administrasi bank yang mengurangi uang masuk. |

Rumus per invoice:

```text
Total Pelunasan = Uang Diterima + PPh 23 + Kerugian + Adm. Bank
```

Total Pelunasan tidak boleh melebihi saldo invoice.

Contoh pelunasan penuh:

```text
Saldo invoice  : 1.400.000
Uang diterima  : 1.350.000
PPh 23         : 28.000
Kerugian       : 12.000
Adm. bank      : 10.000
Total pelunasan: 1.400.000
Sisa invoice   : 0
```

PPh 23 harus diisi berdasarkan bukti potong, bukan otomatis 2% dari saldo invoice karena dasar pengenaan pajak dapat berbeda.

## 5. Mengisi invoice

1. Isi nilai hanya pada invoice yang dibayar. Invoice lain boleh tetap bernilai nol.
2. Satu transaksi dapat membayar beberapa invoice milik customer yang sama.
3. Pembayaran sebagian diperbolehkan; sisa invoice akan tetap terbuka.
4. Angka diformat otomatis dengan pemisah ribuan setelah kursor keluar dari input.
5. Nilai pada satu invoice otomatis dibatasi agar tidak melebihi saldo.

Navigasi keyboard:

- **Enter** berpindah ke kolom berikutnya seperti Tab.
- **Shift + Enter** kembali ke kolom sebelumnya.
- Enter tidak menyimpan transaksi.
- Penyimpanan hanya dilakukan melalui tombol **Simpan Pelunasan**.

Saat Enter ditekan pada kolom Adm. Bank dan total belum sama dengan saldo, aplikasi menampilkan saldo, total input, dan sisa piutang. Pilih **OK** jika memang pembayaran sebagian atau **Batal** untuk memperbaiki nilai.

Header tabel dan ringkasan total tetap terlihat ketika daftar invoice digulir.

## 6. Validasi sebelum menyimpan

Pastikan:

- Uang diterima lebih dari nol.
- Semua nilai tidak negatif.
- Total setiap invoice tidak melebihi saldo.
- Akun PPh 23 dipilih jika nilai PPh 23 diisi.
- Akun Kerugian Pembayaran Piutang dipilih jika nilai Kerugian diisi.
- Akun Beban Administrasi Bank dipilih jika Adm. Bank diisi.
- Total pada footer sesuai dengan bukti pembayaran dan alokasi invoice.
- Nomor referensi dan keterangan sudah diisi bila diperlukan.

Klik **Simpan Pelunasan** setelah seluruh data diperiksa. Aplikasi menyimpan pembayaran, mengurangi saldo invoice, mengubah status menjadi LUNAS jika saldo nol, dan membuat jurnal dalam satu transaksi database.

## 7. Jurnal otomatis

Jurnal dibentuk sesuai komponen yang diisi:

| Posisi | Akun | Nilai |
|---|---|---|
| Debit | Kas atau Bank | Uang bersih diterima |
| Debit | PPh Pasal 23 | PPh 23 dipotong customer |
| Debit | Kerugian Pembayaran Piutang | Nilai kerugian/potongan |
| Debit | Beban Administrasi Bank | Administrasi bank |
| Kredit | Piutang Usaha | Total pelunasan invoice |

Nomor pembayaran otomatis menggunakan awalan `AR`. Nomor jurnal dapat dilihat pada halaman Detail Pelunasan.

## 8. Pemeriksaan hasil

1. Buka **Detail** transaksi yang baru disimpan.
2. Periksa customer, invoice, uang diterima, PPh 23, kerugian, administrasi bank, nomor pembayaran, dan nomor jurnal.
3. Buka **Laporan Piutang** dan pastikan saldo invoice sudah sesuai.
4. Cocokkan Uang Diterima dengan mutasi kas atau bank.

## 9. Edit, koreksi, dan pembatalan

Pelunasan dapat diedit walaupun sudah mempunyai jurnal apabila:

- Transaksi tidak berstatus VOID.
- Tahun transaksi lama belum ditutup.
- Tanggal baru yang dipilih juga berada pada tahun yang belum ditutup.
- User mempunyai izin **Edit** untuk menu Pelunasan Piutang.

Saat disimpan, aplikasi mengembalikan alokasi lama, menerapkan alokasi baru, dan membuat ulang jurnal pelunasan dalam satu transaksi database. Form edit mendukung Uang Diterima, PPh 23, Kerugian, dan Administrasi Bank.

Gunakan pembatalan jika transaksi memang harus ditiadakan:

Pembatalan hanya tersedia untuk user yang mempunyai izin **Void** pada menu Pelunasan Piutang dan transaksi masih berada pada tahun/periode yang belum ditutup.

1. Buka Detail Pelunasan.
2. Isi alasan pembatalan minimal 10 karakter.
3. Klik **Batalkan Pelunasan** dan konfirmasi.
4. Sistem mengubah transaksi menjadi VOID, mengembalikan saldo invoice, dan membuat jurnal pembalik.

Jangan mengubah saldo invoice atau jurnal secara manual karena dapat menyebabkan laporan piutang berbeda dengan buku besar.

## 10. Kendala umum

| Kondisi | Tindakan |
|---|---|
| Customer tidak ditemukan | Pilih dari autocomplete atau modal pencarian. |
| Invoice tidak muncul | Pastikan invoice milik customer dan masih mempunyai saldo. |
| Total melebihi saldo | Kurangi salah satu nilai Uang Diterima, PPh 23, Kerugian, atau Adm. Bank. |
| Total belum sesuai | Perbaiki nilai atau konfirmasi sebagai pembayaran sebagian. |
| Akun potongan belum dipilih | Periksa mapping Pelunasan Piutang atau pilih akun pada form. |
| Periode terkunci | Gunakan tanggal pada periode terbuka atau hubungi Administrator. |
| Tidak dapat diedit | Periksa izin Edit, status VOID, dan apakah tahun transaksi sudah ditutup. |

## Checklist

- [ ] Tanggal dan customer sudah benar.
- [ ] Invoice yang dialokasikan sudah benar.
- [ ] Uang Diterima sama dengan uang bersih yang masuk kas/bank.
- [ ] PPh 23 sesuai bukti potong.
- [ ] Kerugian dan administrasi bank memiliki dasar yang jelas.
- [ ] Total pelunasan tidak melebihi saldo invoice.
- [ ] Akun dan nomor referensi sudah benar.
- [ ] Jurnal otomatis berhasil terbentuk.
- [ ] Saldo laporan piutang sudah sesuai.
