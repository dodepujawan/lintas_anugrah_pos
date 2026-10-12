# Pelunasan Hutang

Menu ini mencatat pembayaran satu atau beberapa faktur supplier dan membuat jurnal pengurangan hutang.

## Langkah input

1. Isi tanggal pembayaran dan pilih supplier.
2. Klik **Cari Faktur**.
3. Pilih faktur yang dibayar dan isi nilai pembayaran masing-masing.
4. Pilih jenis serta akun pembayaran, misalnya kas atau bank.
5. Isi diskon/retur hanya jika memiliki dokumen pendukung.
6. Pastikan total pembayaran sesuai bukti transfer, lalu simpan.

## Jurnal umum pembayaran

| Akun | Debit | Kredit |
|---|---:|---:|
| Hutang Usaha | Nilai penyelesaian | 0 |
| Kas/Bank | 0 | Uang dibayar |

Diskon dan retur akan menggunakan akun yang telah dipetakan sesuai konfigurasi.

## Kontrol

- Nilai penyelesaian tidak boleh melebihi saldo faktur.
- Tanggal pembayaran harus sesuai bukti bank/kas.
- Pembatalan dilakukan melalui fitur Void agar saldo faktur dan jurnal dipulihkan dengan jejak audit.

