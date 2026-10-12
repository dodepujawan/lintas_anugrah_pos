# Hutang Berjalan

Gunakan menu ini untuk faktur pembelian kredit setelah sistem mulai digunakan. Sistem membuat hutang supplier sekaligus jurnal pembelian.

## Jurnal otomatis

| Akun | Debit | Kredit |
|---|---:|---:|
| Beban atau akun pembelian | Nilai faktur | 0 |
| Hutang Usaha | 0 | Nilai faktur |

## Langkah input

1. Isi nomor faktur, tanggal faktur, dan jatuh tempo.
2. Pilih supplier.
3. Isi nilai hutang.
4. Pilih akun Debit sesuai sifat pembelian dan akun Hutang Usaha.
5. Periksa jurnal yang akan terbentuk, lalu simpan.

## Hindari kesalahan

- Jangan mencatat faktur yang sama lagi melalui Jurnal Umum.
- Pastikan akun Debit bukan selalu beban; pembelian aset harus masuk akun aset yang sesuai.
- Gunakan Saldo Awal Hutang untuk faktur sebelum cutover.
- Tanggal transaksi tidak boleh berada pada tahun yang sudah dikunci.

