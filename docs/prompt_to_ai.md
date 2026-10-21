Lanjutkan migrasi `src_old` → `src` (Pure src Style).

Baca dulu SEBELUM mulai:
1. `docs/progress_migrasi.md` — status lengkap progres + changelog
2. `docs/milestone_lintas.md` — arsitektur & style guide
3. `docs/dokumen_perubahan.md` — history perubahan database

## Konteks saat ini
- Migrasi transaksi src_old → src sudah sebagian selesai, tapi banyak issue schema mismatch temuan pertama kali baru ketahuan pas import data
- Environment: Docker + MySQL 8. Host/database harus diverifikasi dari `.env`; error runtime terakhir menunjuk ke `192.168.1.10:3306`, database `lintas_anugrah_full`, sedangkan catatan lama menunjuk ke `192.168.1.7`.
- Test: `php artisan test --filter=Transaksi` (13/13 passed) — jalankan setelah setiap perubahan
- Route cache: `php artisan route:cache`

## Source Of Truth: Price Customer (status 2026-10-20)
- Modul lama: `src_old/resources/views/priceCustomer/priceCustomer.blade.php` + `src_old/app/Http/Controllers/PricesCustomerController.php`.
- Modul aktif: `src/resources/views/price-customer/index.blade.php` (daftar customer), `customer-prices.blade.php` (detail harga), `create.blade.php`, `edit.blade.php`.
- Controller aktif: `src/app/Http/Controllers/PriceCusController.php`.
- Route aktif:
  - `price-customer.index`: daftar customer.
  - `price-customer.customer-prices`: detail berdasarkan `{kodecus}`.
  - `price-customer.update-all`: sinkronisasi harga master ke customer.
  - `price-customer.update-row`: simpan harga inline.
  - resource routes memakai `->parameters(['price-customer' => 'priceCus'])` agar implicit model binding `PriceCus $priceCus` konsisten.
- Fitur yang sudah ekuivalen dengan `src_old`: info customer, tabel detail, HARGA editable, simpan harga per baris, Update All Harga Customer, pencarian, pagination, pengecekan delete terhadap `expedisis`.
- Perbedaan yang disengaja: `src` memakai Bootstrap 5 + Alpine.js + Fetch/full reload, bukan jQuery/DataTables/SweetAlert2. Daftar customer dan detail berada di halaman berbeda, bukan toggle satu halaman.
- Fitur yang belum ekuivalen penuh: modal pilih/tambah rute lama. Form `src` saat ini menggunakan input RUTE biasa; tombol tambah harga lama pada source lama juga `hidden disabled` dan endpoint store lamanya diberi catatan tidak dipakai.
- Konvensi KODE: data legacy dapat menyimpan `pricecus.KODE = prices.id` atau `prices.KODE`. `Update All` wajib membandingkan keduanya agar tidak membuat duplikasi. Data baru diarahkan memakai `prices.KODE`.
- Performa detail: `customerPrices()` memakai `simplePaginate(20)` dan urutan `DARI`, `SAMPAI`, `id`. Migration `2026_10_20_000001_add_price_customer_query_indexes.php` menambah index aman `pricecus_kodecus_dari_sampai_id_index` hanya jika belum ada.
- TANGGAL: model `Price` tidak lagi memakai cast `date` karena schema runtime legacy menerima `VARCHAR(10)`. Controller menormalisasi ke `Y-m-d` agar tidak terkirim sebagai `Y-m-d H:i:s`.

## Protokol Lanjut Price Customer
1. Baca bagian source of truth ini dan changelog paling bawah `docs/progress_migrasi.md`.
2. Verifikasi schema/runtime di MySQL server dengan `php artisan migrate:status`, `SHOW COLUMNS`, dan `SHOW INDEX`; sandbox SQLite bukan sumber kebenaran schema produksi.
3. Jangan mengedit migration yang sudah recorded. Migration index baru harus memakai `Schema::hasTable()` dan `Schema::hasIndex()`.
4. Setelah perubahan, jalankan `php artisan test --filter=Transaksi`, lint terkait, dan catat hasilnya di `docs/progress_migrasi.md`.
5. Jika melanjutkan fitur lama, prioritaskan modal/pengelolaan RUTE; jangan mengembalikan Yajra DataTables atau jQuery.

## Source Of Truth: Price Rent / Price Dingin (audit 2026-10-21)
- View lama: `src_old/resources/views/priceDingin/priceDingin.blade.php`.
- Controller lama: `src_old/app/Http/Controllers/PricedinginController.php`.
- View aktif: `src/resources/views/price-rent/index.blade.php`, `create.blade.php`, `edit.blade.php`.
- Controller aktif: `src/app/Http/Controllers/PricedinginController.php`.
- Status: core migration sudah diimplementasikan pada 2026-10-21; browser verification dan migration status di MySQL masih wajib dilakukan.
- Gap kritis yang harus dipenuhi sebelum modul dianggap selesai:
- 1-8 di atas sudah ditangani pada core implementation: store memvalidasi kendaraan, generate `KODEDGN`/`TANGGAL`/`KUNCI`, mengisi field turunan, membuat copy seluruh customer, index menampilkan kendaraan, search diperluas, dan route binding diperbaiki. Endpoint `getData()` lama tetap hanya compatibility JSON dan tidak dipakai view baru.
- Form `src` sengaja menggunakan select kendaraan + Alpine field turunan, bukan modal jQuery; `FROM`, `TO`, dan `KUNCI` tidak lagi menjadi input user.
- Compatibility detail: master `pricedingins.KODEDGN` memakai kode generated `PRD...`, sedangkan copy `pricedingincus.KODEDGN` mengikuti kontrak lama dan menyimpan numeric `pricedingins.id`.
- TANGGAL price rent: runtime legacy migration menggunakan string `VARCHAR(10)`; `Pricedingin`, `Pricedingincus`, dan `Pricedinginhis` tidak boleh memakai cast `date` yang men-serialize menjadi `Y-m-d H:i:s`. Controller menyimpan `Y-m-d`.
- `pricedingincus.NAMACUS` harus menampung data seperti `mcustomer.NAMACUST`; migration `2026_10_21_000002_fix_pricedingincus_namacus_length.php` memperbesar kapasitas secara aman dari 50 ke 100 karakter.
- Delete masih mengikuti resource MVC dan belum menambahkan cascade ke `pricedingincus`; jangan mengubah policy ini tanpa verifikasi kebutuhan bisnis.
- Keputusan stack: implementasi lanjutan tetap memakai Bootstrap 5 + Alpine.js + form MVC/full reload, bukan jQuery/DataTables.

## Price Rent Customer Current State
- `price-rent-customer.index` adalah daftar customer.
- Detail customer berada di `price-rent-customer/{kodecus}/prices`.
- Detail menyediakan info customer, `Update Harga Sewa`, dan inline update HARGA melalui Alpine.
- Endpoint aktif: `price-rent-customer.update-all` dan `price-rent-customer.update-row`.
- Ini menggantikan toggle DataTables lama dengan halaman MVC + Alpine; master tetap membuat copy awal ke `pricedingincus` seperti source lama.

## ⚠️ WARNING: Step-by-step, satu modul per satu modul
CONTOH: ada 3 modul Ekspedisi, Rent Pendingin, Coolroom → kerjakan Eksedisi dulu sampai DONE, setelah itu programmer cek di browser, baru lanjut ke Rent Pendingin, lalu Coolroom. Jangan lompat ke semua modul sekaligus.

Catat progress ke `docs/progress_migrasi.md` setiap kali selesai satu langkah.

## Debug attribute pattern
Tambahkan atribut unik untuk setiap elemen penting agar programmer bisa klik kanan → inspect → cari di VSCode:
```
data-debug="transaksiExpedisiCreateBtn"
data-debug="transaksiKendaraanEdit_FNO_PRK_S"
data-debug="transaksiPriceRentCusEdit_KODEDGN"
```
Format: `transaksi[Modul][Halaman]_[Element]`

## ⚠️ ATURAN EMAS: JANGAN PERNAH EDIT MIGRATION YANG SUDAH TER-RECORD DI DB
- Jika migration sudah pernah dijalankan (`php artisan migrate` sudah berhasil), **JANGAN EDIT FILE MIGRASINYA**. Buat migration **BARU** dengan timestamp unik.
- Migration yang sudah gagal (FAIL) belum ter-record — boleh di-edit dan di-retry.
- Verifikasi dengan `php artisan migrate:status` di server MySQL (bukan di sandbox).

## Migration best practices
- Schema original migration (`2026_10_13_*_create_*`): update untuk **fresh install** — ini sudah benar sebagai referensi.
- Migration baru (`2026_10_16_*`): gunakan **`Schema::hasColumn()` check** di dalam migration supaya idempotent (bisa di-run berulang tanpa error "column already exists").
- Untuk MySQL-specific syntax (`ALTER TABLE ... MODIFY`): selalu wrap di `if (DB::getDriverName() === 'mysql')` — SQLite (testing) tidak support.
- Untuk kolom yang ada data lama: **clean data first** (`UPDATE ... SET col = NULL WHERE invalid`), baru ubah tipe kolom.
- Untuk kolom timestamp old FoxPro data: `SET SESSION sql_mode = ''` sebelum ALTER TABLE untuk avoid '0000-00-00' datetime validation error.
- Migration baru HARUS punya `down()` method yang valid (atau kosong dengan comment).
- Koreksi `kendaraans.JENIS`: jangan edit migration lama `2026_10_13_000010` yang mungkin sudah recorded; migration baru `2026_10_21_000001_fix_kendaraans_jenis_length.php` mengubah schema existing maupun fresh-install menjadi `CHAR(50)` secara aman/idempotent. Jangan mengubah `JENIS` pada `prices`/`pricecus` karena kolom tersebut adalah kode harga 1 karakter.

## Schema mismatch pattern (sering terjadi!)
Schema lama (FoxPro, Uppercase) vs schema baru (Laravel, migrasi ke FROM/TO) punya perbedaan kolom:
- Old: `DARI(decimal), SAMPAI(decimal), KODE, KETERANGAN, RUTE, HV, HKG, HBOK, KUNCI, HG, JENIS`
- New: `FROM(string), TO(string)`

**Solusi:** Tambahkan SEMUA kolom lama ke schema baru, jangan ganti nama. Controller auto-map DARI→FROM, SAMPAI→TO jika FROM/TO kosong.

## Controller best practices
- Tambahkan `$fillable` lengkap di model (semua kolom lama + baru)
- Tambahkan `$casts = ['TANGGAL' => 'date']` jika kolom TANGGAL ada datanya
- Validation: semua kolom yang ada di old schema wajib ada di validation rules (use `nullable` supaya import data tidak gagal)
- Resource controller → gunakan `->except()` untuk method yang tidak ada (show, create, destroy)

## phpMyAdmin import workflow
1. `TRUNCATE TABLE tablename;` atau phpMyAdmin → Operations → Truncate table
2. Import SQL **Data only** (bukan Structure+Data)
3. Jika error #1054 Unknown column: beri tahu AI, kolom belum ditambah di migration
4. Jika error #1062 Duplicate entry: table belum ditruncate
5. Jika error #1068 Multiple PK: dump mengandung ALTER TABLE, hapus di SQL atau gunakan "Data only" + "Add DROP TABLE"

## Style guide
- Views: `@extends('layouts.app')`, pakai Bootstrap 5, Alpine.js (bukan jQuery)
- Full reload (bukan AJAX SPA) kecuali memang dibutuhkan untuk lookup customer/driver
- Permission: ikuti `TransaksiPermissionSeeder.php` — hak akses otomatis
- Debug: gunakan `php artisan test --filter=NamaTest` untuk verifikasi

## ⛔ LANGGENG
- JANGAN edit migration yang sudah ter-record di DB — buat migration baru
- JANGAN gunakan MySQL-specific syntax tanpa check driver
- JANGAN gunakan column alias yang menghilangkan kolom lama
- JANGAN gunakan Yajra DataTables (tidak terpasang di composer.json) — pakai Laravel pagination manual
