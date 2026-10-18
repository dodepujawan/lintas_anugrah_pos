Lanjutkan migrasi `src_old` → `src` (Pure src Style).

Baca dulu SEBELUM mulai:
1. `docs/progress_migrasi.md` — status lengkap progres + changelog
2. `docs/milestone_lintas.md` — arsitektur & style guide
3. `docs/dokumen_perubahan.md` — history perubahan database

## Konteks saat ini
- Migrasi transaksi src_old → src sudah sebagian selesai, tapi banyak issue schema mismatch temuan pertama kali baru ketahuan pas import data
- Environment: Docker + MySQL 8 (host 192.168.1.7:3306, db `lintas_anugrah_full`). Sandbox hanya pakai SQLite testing.
- Test: `php artisan test --filter=Transaksi` (13/13 passed) — jalankan setelah setiap perubahan
- Route cache: `php artisan route:cache`

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
