# Panduan Jurnal Penyesuaian

Jurnal Penyesuaian digunakan untuk mencatat koreksi atau penyesuaian akuntansi yang tidak berasal langsung dari transaksi harian. Contohnya penyusutan aset, beban yang masih harus dibayar, pendapatan yang masih harus diterima, pemakaian perlengkapan, dan koreksi salah akun.

> Gunakan jurnal penyesuaian berdasarkan bukti dan perhitungan yang dapat dipertanggungjawabkan. Total Debit wajib sama dengan Total Kredit.

## Ringkasan proses

1. Buka **Jurnal > Jurnal Penyesuaian**.
2. Klik **Tambah Jurnal Penyesuaian**.
3. Periksa tanggal dan isi keterangan umum.
4. Pilih akun posting dan masukkan Debit atau Kredit pada setiap baris.
5. Pastikan minimal dua baris terisi dan status menunjukkan **Seimbang**.
6. Simpan sebagai Draft jika masih perlu diperiksa, atau posting jika sudah benar.

## Mengisi informasi jurnal

- **No. Jurnal** dibuat otomatis dengan awalan `JP` dan tidak perlu diubah.
- **Tanggal** harus sesuai tanggal penyesuaian. Periode yang sudah dikunci tidak dapat digunakan atau dikoreksi.
- **Keterangan Umum** menjelaskan alasan penyesuaian, misalnya `Penyusutan kendaraan bulan Juli 2026`.

## Mengisi item jurnal

1. Ketik kode atau nama akun pada kolom **Akun Posting**, lalu pilih akun yang sesuai.
2. Isi **Keterangan Baris** bila diperlukan.
3. Masukkan nilai hanya pada salah satu sisi: **Debit** atau **Kredit**.
4. Tekan Enter dari Akun ke Keterangan, Debit, Kredit, lalu ke akun pada baris berikutnya.
5. Gunakan **Tambah Baris** jika transaksi membutuhkan lebih banyak akun.
6. Gunakan ikon tempat sampah untuk menghapus baris yang tidak diperlukan.

## Contoh penyusutan aset

Penyusutan kendaraan bulan berjalan sebesar 2.000.000:

| Akun | Debit | Kredit |
|---|---:|---:|
| Beban Penyusutan Kendaraan | 2.000.000 | 0 |
| Akumulasi Penyusutan Kendaraan | 0 | 2.000.000 |

## Status saldo dan validasi

Jurnal siap diposting apabila:

- minimal dua baris memiliki akun dan nilai;
- setiap baris hanya mengisi Debit atau Kredit;
- akun yang digunakan merupakan akun posting;
- Total Debit sama dengan Total Kredit; dan
- tanggal tidak berada pada periode terkunci.

Jika muncul status **Selisih**, periksa kembali nominal dan posisi Debit/Kredit.

## Draft dan posting

- **Simpan Draft** digunakan jika jurnal belum final. Sistem juga melakukan autosave saat input berubah.
- Draft masih dapat diedit, dihapus, atau diposting dari daftar jurnal.
- **Simpan & Posting** membuat jurnal memengaruhi buku besar.
- Setelah posting, koreksi hanya boleh dilakukan oleh user yang memiliki hak Edit dan selama periodenya belum dikunci atau diarsipkan.

## Edit jurnal

1. Dari daftar, klik ikon pensil pada jurnal yang diizinkan.
2. Perbaiki tanggal, keterangan, akun, atau nominal.
3. Pastikan jurnal kembali seimbang.
4. Untuk Draft, gunakan **Simpan Draft** bila koreksi belum final.
5. Klik tombol simpan akhir setelah koreksi selesai.

Jurnal pada periode terkunci atau item yang sudah diarsipkan tidak dapat diedit.

## Cetak dan Excel

- Gunakan tombol **Excel** untuk mengunduh data periode laporan.
- Gunakan tombol **Cetak** untuk mencetak daftar sesuai filter tanggal dan status.
- Gunakan tombol **Panduan** untuk membuka halaman ini; panduan juga dapat dicetak.

## Pemeriksaan sebelum posting

- Cocokkan tanggal dengan dokumen pendukung.
- Periksa bahwa akun Debit dan Kredit sudah benar.
- Pastikan nominal dan keterangan mudah ditelusuri.
- Pastikan tidak ada jurnal penyesuaian ganda untuk transaksi yang sama.
- Simpan bukti perhitungan sebagai arsip pemeriksaan.
