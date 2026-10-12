# Piutang Berjalan

Gunakan menu ini untuk membuat invoice penjualan kredit setelah sistem mulai digunakan. Sistem membuat data piutang dan jurnal penjualan secara bersamaan.

## Jurnal otomatis

| Akun | Debit | Kredit |
|---|---:|---:|
| Piutang Usaha | Nilai invoice | 0 |
| Pendapatan | 0 | Nilai invoice |

## Langkah input

1. Isi nomor invoice yang unik, tanggal transaksi, dan jatuh tempo.
2. Pilih pelanggan.
3. Pilih akun Piutang Usaha dan akun Pendapatan yang sesuai.
4. Isi nilai invoice dan keterangan.
5. Periksa pratinjau jurnal, lalu simpan.

## Hindari kesalahan

- Jangan mencatat invoice yang sama melalui Jurnal Umum karena akan menggandakan piutang.
- Gunakan Saldo Awal Piutang hanya untuk invoice sebelum cutover.
- Tanggal tidak boleh berada pada tahun yang sudah dikunci.
- Perubahan invoice akan memengaruhi jurnal terkait, sehingga periksa kembali sebelum menyimpan.

