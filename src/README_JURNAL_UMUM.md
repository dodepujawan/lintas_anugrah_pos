# Panduan Input Jurnal Umum

Jurnal Umum digunakan untuk mencatat transaksi yang tidak dibuat otomatis oleh modul penjualan, pembelian, piutang, hutang, atau pelunasan. Setiap jurnal harus memiliki minimal dua baris dan **total Debit harus sama dengan total Kredit**.

> Siapkan bukti transaksi dan tentukan akun yang bertambah atau berkurang sebelum mulai menginput.

## Ringkasan langkah

1. Buka menu **Jurnal**, lalu klik **Tambah Jurnal**.
2. Periksa tanggal dan isi referensi serta keterangan transaksi.
3. Pilih akun posting pada setiap baris.
4. Masukkan nilai pada Debit atau Kredit.
5. Pastikan status menunjukkan **Balance**.
6. Simpan sebagai Draft jika masih perlu diperiksa, atau klik **Simpan & Posting Jurnal** jika sudah final.

## 1. Tentukan posisi Debit dan Kredit

Gunakan pedoman saldo normal berikut.

| Kelompok akun | Bertambah | Berkurang |
|---|---|---|
| Aset, Kas, Bank, Piutang | Debit | Kredit |
| Beban | Debit | Kredit |
| Kewajiban atau Hutang | Kredit | Debit |
| Modal atau Ekuitas | Kredit | Debit |
| Pendapatan | Kredit | Debit |

Contoh: perusahaan membeli alat tulis tunai sebesar 250.000. Beban ATK bertambah di Debit dan Kas berkurang di Kredit.

| Akun | Debit | Kredit |
|---|---:|---:|
| Beban Alat Tulis Kantor | 250.000 | 0 |
| Kas | 0 | 250.000 |

## 2. Isi informasi jurnal

- **No. Jurnal** dibuat otomatis oleh sistem dan tidak perlu diubah.
- **Tanggal** wajib diisi sesuai tanggal pada bukti transaksi. Tanggal pada periode yang telah dikunci tidak dapat digunakan.
- **Referensi / Bukti Transaksi** bersifat opsional. Isi dengan nomor kuitansi, faktur, BKK, BKM, atau identitas dokumen lain, maksimal 50 karakter.
- **Keterangan Umum** menjelaskan transaksi secara ringkas, misalnya `Pembelian alat tulis kantor secara tunai`.

## 3. Isi baris jurnal

1. Pada kolom **Akun Posting**, ketik kode atau nama akun.
2. Pilih akun dari daftar saran. Tombol bergambar kaca pembesar dapat digunakan untuk membuka pencarian COA.
3. Isi **Keterangan Baris** bila diperlukan untuk memberi rincian khusus pada akun tersebut.
4. Masukkan nominal pada salah satu kolom, **Debit** atau **Kredit**. Jangan mengisi keduanya pada baris yang sama.
5. Klik **Tambah Baris** bila transaksi menggunakan lebih dari dua akun.
6. Gunakan ikon tempat sampah untuk menghapus baris yang tidak diperlukan.

> Hanya akun dengan level Akun Posting yang dapat dipilih. Akun kelompok atau akun induk tidak dapat digunakan untuk transaksi.

## 4. Periksa keseimbangan jurnal

Lihat bagian bawah form untuk memeriksa **Total Debit**, **Total Kredit**, dan **Status Saldo**. Tombol posting akan aktif jika:

- minimal dua baris sudah diisi;
- setiap baris memiliki akun posting;
- setiap baris berisi nilai Debit atau Kredit;
- Debit dan Kredit tidak diisi bersamaan pada satu baris; dan
- total Debit sama dengan total Kredit.

Selisih kecil sekalipun harus diperbaiki sebelum jurnal dapat diposting.

## 5. Simpan Draft atau Posting

- Klik **Simpan Draft** jika input belum final atau masih menunggu pemeriksaan. Perubahan form juga disimpan otomatis setelah terdeteksi.
- Saat menekan Enter pada kolom Kredit di baris terakhir, sistem memvalidasi baris, menyimpan Draft, lalu menyiapkan baris kosong berikutnya.
- Klik **Simpan & Posting Jurnal** jika semua informasi sudah benar dan balance.

Jurnal berstatus Draft masih dapat diedit atau dihapus dari halaman Daftar Jurnal. Jurnal yang sudah diposting tidak dapat diedit atau dihapus melalui form Jurnal Umum karena telah memengaruhi buku besar.

## Contoh transaksi lain

### Penerimaan pendapatan tunai 1.500.000

| Akun | Debit | Kredit |
|---|---:|---:|
| Kas | 1.500.000 | 0 |
| Pendapatan Jasa | 0 | 1.500.000 |

### Pembayaran hutang usaha 2.000.000

| Akun | Debit | Kredit |
|---|---:|---:|
| Hutang Usaha | 2.000.000 | 0 |
| Bank | 0 | 2.000.000 |

## Jika terjadi kendala

| Pesan atau kondisi | Cara mengatasi |
|---|---|
| Akun posting belum dipilih | Pilih salah satu akun dari daftar saran, jangan hanya mengetik nama akun. |
| Debit dan Kredit tidak boleh diisi bersamaan | Kosongkan atau isi 0 pada salah satu sisi baris tersebut. |
| Minimal dua baris jurnal | Lengkapi sedikitnya satu baris Debit dan satu baris Kredit. |
| Total Debit dan Kredit harus sama | Bandingkan total di bawah form lalu koreksi nominal yang selisih. |
| Periode telah dikunci | Gunakan tanggal pada periode yang masih terbuka atau hubungi pihak yang berwenang. |
| Draft gagal tersimpan | Periksa koneksi, pastikan semua baris yang bernilai memiliki akun, lalu klik Simpan Draft lagi. |

## Pemeriksaan sebelum posting

- [ ] Tanggal sesuai bukti transaksi.
- [ ] Referensi dan keterangan mudah ditelusuri.
- [ ] Akun yang dipilih sudah tepat.
- [ ] Nominal Debit dan Kredit berada di sisi yang benar.
- [ ] Total Debit sama dengan total Kredit.
- [ ] Tidak ada transaksi yang diinput dua kali.

Jika seluruh pemeriksaan sudah terpenuhi, jurnal siap diposting.
