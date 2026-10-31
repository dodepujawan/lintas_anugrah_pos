# 📊 PROGRESS MIGRASI — src_old → src (Pure src Style)

**Project:** Lintas Anugrah Ekspedisi Pro
**Tanggal mulai:** 2026-10-12
**Tanggal update:** 2026-10-12 14:20 (FINAL)
**Branch:** `main` (committed 3x: 1f1f874, 9214225, 4c06666)
**Stack:** Laravel 12 + Breeze + Tailwind + Bootstrap 5 + dompdf + Custom Permission (Role+Menu)
| 2026-10-21 08:15 | Audit: Price Rent / Price Dingin migration | CRUD dasar `src` tersedia, tetapi belum setara dengan `src_old`: belum ada lookup kendaraan/modal Alpine, belum generate `KODEDGN`/`TANGGAL`/`KUNCI`, belum derive `JENIS` dari kendaraan, belum sync master ke semua `pricedingincus`, index belum menampilkan nama kendaraan, search belum mencakup PLAT/ITEM/PERIODE/JENIS, dan resource binding `{price_rent}` belum konsisten dengan `Pricedingin $price`. Backlog ini dicatat di `docs/prompt_to_ai.md`; implementasi berikutnya tetap MVC + Alpine/full reload. | Audit selesai, implementasi belum dimulai |
| 2026-10-21 08:30 | Implementasi Price Rent / Price Dingin | Disamakan dengan alur `src_old` menggunakan MVC + Alpine: form sekarang memilih kendaraan dan otomatis mengisi PLAT/JENIS/ITEM, FROM/TO/KUNCI dihapus dari input, controller generate KODEDGN `PRD0000001+`, TANGGAL, KUNCI, derive field dari kendaraan, dan membuat copy ke semua `pricedingincus` dalam transaction. Index menampilkan nama kendaraan dan search KODEDGN/KODE/PERIODE/PLAT/JENIS/ITEM; route binding memakai `{price}` dan resource mengecualikan show. | Blade compile OK, 13/13 tests pass |
| 2026-10-21 09:20 | Fix: `kendaraans.JENIS` length | Migration lama `2026_10_13_000010` tidak diubah karena dapat sudah recorded. Ditambahkan migration baru aman `2026_10_21_000001_fix_kendaraans_jenis_length.php`: hanya MySQL, cek metadata aktual, alter hanya jika panjang masih <50, dan no-op jika sudah diperbaiki manual di phpMyAdmin. `JENIS` pada tabel harga tetap tidak diubah karena merupakan kode harga. | Pending VPS `php artisan migrate` |
| 2026-10-21 09:35 | Price Rent form completeness | `JENIS MOBIL` sekarang eksplisit di create/edit dengan `name="JENIS"`, terisi dari kendaraan saat select berubah, dan backend tetap derive ulang dari `kendaraans.JENIS`. `PLAT`/`ITEM` juga diberi name. Memperbaiki wrapper Alpine pada edit form yang sebelumnya menyebabkan field turunan tidak terinisialisasi. | Blade compile OK, 13/13 tests pass |
| 2026-10-21 09:45 | Fix: Price Rent legacy field mapping | Koreksi mapping sesuai `src_old`: field tampilan Jenis Mobil memakai `kendaraan.NAMA`, field database `JENIS` tetap memakai `kendaraan.JENIS`, dan `ITEM` dibuat dari `PLAT + kendaraan.JENIS`; `PERIODE` disimpan terpisah dan tidak masuk formula `ITEM`. | Blade compile OK, 13/13 tests pass |
| 2026-10-21 10:40 | Fix: Price Rent `TANGGAL` truncation | Runtime `pricedingins.TANGGAL` masih `VARCHAR(10)`, sedangkan cast date pada model mengirim datetime 19 karakter. Removed `TANGGAL` date casts from `Pricedingin`, `Pricedingincus`, dan `Pricedinginhis`; controller sudah menyimpan format `Y-m-d`. | Pint passed, 13/13 tests pass |
| 2026-10-21 10:50 | Fix: Price Rent customer `NAMACUS` truncation | `mcustomer.NAMACUST` dapat 100 karakter, tetapi `pricedingincus.NAMACUS` masih 50 sehingga batch copy customer gagal pada row 443. Added safe idempotent migration `2026_10_21_000002_fix_pricedingincus_namacus_length.php` untuk mengubah ke `VARCHAR(100)` hanya jika kapasitas aktual kurang dari 100. | Pending VPS `php artisan migrate` |

---

## 🚦 STATUS SAAT INI

```
FASE 1: Migration Foundation          [██████████] 100% ✅ Selesai (20 file)
FASE 2: Models                        [██████████] 100% ✅ Selesai (17 file)
FASE 3: Controllers                   [██████████] 100% ✅ Selesai (21 file)
FASE 4: Routes + Menu registration    [██████████] 100% ✅ Selesai (141 routes, 28 menu)
FASE 5: Views                         [████████░░░░]  74% (17/25 dirs + 5/9 PDF) — Step 1 (EKS) COMPLETE, Step 2 (REN) pending review
FASE 6: Testing                       [██████████] 100% ✅ 13/13 tests passed (61 assertions)
────────────────────────────────────────────────────────────
TOTAL                              [████████░░░░░]  86%
```

**Update 2026-10-18:** Price module enhanced — auto KODE generation, KUNCI generation, pricecus sync, simplified forms, delete button with transaction check for kendaraan.

---

## 🔨 FASE 1: Migration Foundation ✅ DONE

**19 migration baru + 1 charset conversion = 20 total**

### 1.1 Schema Reconciliation (3 file) ✅
| Migration | Tables | Summary |
|---|---|---|
| `2026_10_13_000001_reconcile_users_table.php` | `users` | Tambah `role_old`, `area_id`, `area_name` |
| `2026_10_13_000002_reconcile_ar_aph_tables.php` | `arh`, `aph`, `barh`, `baph` | Tambah `SALES/AREA/DIVISI/area_id/jurnal_id` ke arh; `UM/AUTO` ke aph |
| `2026_10_13_000003_reconcile_msupplier_mcustomer_driver.php` | `msupplier`, `mcustomer`, `drivers` | FoxPro columns ke mcustomer; KODE/NAMA/ALAMAT/MULAI ke drivers |

### 1.2 New Tables (12 file) ✅
| Migration | Tables | Notes |
|---|---|---|
| `2026_10_13_000010_create_kendaraans_table.php` | `kendaraans` | Vehikel master |
| `2026_10_13_000011_create_prices_table.php` | `prices` | Harga EKS |
| `2026_10_13_000012_create_pricecus_pricecushis_tables.php` | `pricecus`, `pricecushis` | Harga per customer (+ kode_index) |
| `2026_10_13_000014_create_pricedingin_tables.php` | `pricedingins`, `pricedingincus`, `pricedinginhis` | Harga rent (3 tables) |
| `2026_10_13_000017_create_rutes_table.php` | `rutes` | Rute master |
| `2026_10_13_000018_create_expedisis_table.php` | `expedisis` | **TRANSAKSI UTAMA EKS** (40+ kolom UPPERCASE) |
| `2026_10_13_000019_create_coolrooms_table.php` | `coolrooms` | Coolroom |
| `2026_10_13_000020_create_kwitantis_table.php` | `kwitansi` | Kwitansi (nama tabel: `kwitansi`) |
| `2026_10_13_000021_create_services_table.php` | `services` | Service |
| `2026_10_13_000022_create_rekenings_table.php` | `rekenings` | Rekening bank |
| `2026_10_13_000023_create_signatures_table.php` | `signatures` | Penanggung jawab |
| `2026_10_13_000024_create_areas_table.php` | `areas` | Area/cabang |
| `2026_10_13_000025_create_ol_pajaks_table.php` | `ol_pajaks` | Setting PPN |

### 1.3 Menu + Charset ✅
| Migration | Purpose |
|---|---|
| `2026_10_13_000030_register_transaction_menus.php` | 28 menu items di tabel `menus` |
| `2026_10_13_000040_convert_legacy_charset_to_utf8mb4.php` | MySQL charset conversion (latin1 → utf8mb4) |

### 1.4 Verification
```
$ php artisan migrate --force
✓ 2026_10_13_000001_reconcile_users_table ........................ DONE
✓ 2026_10_13_000002_reconcile_ar_aph_tables ....................... DONE
✓ 2026_10_13_000003_reconcile_msupplier_mcustomer_driver .......... DONE
✓ 2026_10_13_000010_create_kendaraans_table ....................... DONE
✓ 2026_10_13_000011_create_prices_table ........................... DONE
✓ 2026_10_13_000012_create_pricecus_pricecushis_tables ............ DONE
✓ 2026_10_13_000014_create_pricedingin_tables ...................... DONE
✓ 2026_10_13_000017_create_rutes_table ............................. DONE
✓ 2026_10_13_000018_create_expedisis_table ....................... DONE
✓ 2026_10_13_000019_create_coolrooms_table ......................... DONE
✓ 2026_10_13_000020_create_kwitantis_table ......................... DONE
✓ 2026_10_13_000021_create_services_table ........................ DONE
... (semua 89 migrations berhasil)
```

---

## 📦 FASE 2: Models ✅ DONE (17 file)

### 2.1 Reconciled Models (6 file) ✅
| Model | Changes |
|---|---|
| `User.php` | +`role_old`, `area_id`, `area_name` di fillable/casts |
| `Arh.php` | +`SALES`, `AREA`, `DIVISI`, `area_id`, `area_name`, `jurnal_id` + `jurnal()`, `jurnalItem()` relasi |
| `Aph.php` | +`UM`, `AUTO`, `area_id`, `area_name`, `jurnal_id` + `jurnal()`, `jurnalItem()` relasi |
| `Msupplier.php` | +`KATEGORI`, `RETURAN`, `area_id`, `area_name`, `jurnal_id` + `HasFactory`, `aphs()` relasi |
| `Mcustomer.php` | +25 FoxPro columns + `HasFactory`, `arhs()` relasi |
| `Driver.php` | Table `driver`→`drivers`, +UPPERCASE alias cols (KODE, NAMA, ALAMAT, MULAI) |

### 2.2 New Models (11 file) ✅
| Model | Table | Key Relationships |
|---|---|---|
| `Expedisi.php` | `expedisis` | `driver()`, `kendaraan()`, `customer()`, `jurnal()`, `arh()` |
| `Coolroom.php` | `coolrooms` | `jurnal()`, `customer()` |
| `Kwitansi.php` | `kwitansi` | `arh()`, `jurnal()` |
| `Kendaraan.php` | `kendaraans` | |
| `Price.php` | `prices` | |
| `PriceCus.php` | `pricecus` | |
| `PriceCushis.php` | `pricecushis` | |
| `Pricedingin.php` | `pricedingins` | |
| `Pricedingincus.php` | `pricedingincus` | |
| `Pricedinginhis.php` | `pricedinginhis` | |
| `Rute.php` | `rutes` | |

### 2.3 Extra Models (4 file) ✅
`Service.php`, `Rekening.php`, `Signature.php`, `Area.php`, `Pajak.php`

### 2.4 Helper ✅
- `app/Helpers/Helper.php` — `terbilang()`, `terbilang_angka()`
- Registered di `composer.json` autoload.files

---

## 🎮 FASE 3: Controllers ✅ DONE (21 file)

### 3.1 Transaction Controllers (11 file) ✅
| Controller | Pattern | JournalService | Key Methods |
|---|---|---|---|
| `ExpedisiController` | Resource | ✅ auto-create jurnal | index, create, store, show, edit, update, destroy, print |
| `CoolroomController` | Resource | — (invoice controller) | index, create, store, show, edit, update, destroy |
| `RentPendinginController` | AJAX-based | — | storeSurjal, storeMuat, updateSurjal, print |
| `KendaraanController` | Resource | — | index, create, store, show, edit, update, destroy, data, dataModel |
| `ServiceController` | Resource | — | index, create, store, show, edit, update, destroy |
| `PriceController` | Resource | — | CRUD |
| `PriceCusController` | Resource | — | CRUD + getPrice |
| `PricedinginController` | Resource | — | CRUD |
| `PricedingincusController` | Resource | — | CRUD + history sync |
| `PajakController` | AJAX | — | get, update |
| `RekeningController` | Resource | — | CRUD + pilih |
| `SignatureController` | AJAX | — | get, update |
| `AreaController` | AJAX CRUD | — | get, store, update, destroy |

### 3.2 Invoice/Kwitansi Controllers (5 file) ✅
| Controller | Integration |
|---|---|
| `ExpedisiInvoiceController` | Gabung SJ → Arh + JournalService |
| `ExpedisiGenerateInvoiceController` | Invoice gen → Arh + JournalService + PDF |
| `ExpedisiKwitansiController` | Payment → Barh + Arh.update + JournalService |
| `RentPendinginGenerateInvoiceController` | Invoice gen → Arh + JournalService |
| `RentPendinginKwitansiController` | Payment → Barh + Arh.update + JournalService |
| `CoolroomGenerateInvoiceController` | Invoice gen → Arh + JournalService + PDF |
| `CoolroomKwitansiController` | Payment → Arh.update + JournalService |
| `KwitansiHistoryController` | History all kwitansi (EKS + REN + Coolroom) + export |

**Verification:** `php -l` semua controller — ✅ No syntax errors

---

## 🔀 FASE 4: Routes + Menu ✅ DONE

### 4.1 Routes (141 routes) ✅
- Resource routes: `expedisi, kendaraan, coolroom, service, rekening, price, price-customer, price-rent, price-rent-customer`
- Transaction routes: `expedisi-invoice, expedisi-generate-invoice, expedisi-kwitansi, rent-pendingin, rent-pendingin-invoice, rent-pendingin-kwitansi, coolroom-invoice, coolroom-kwitansi, kwitansi-history`
- AJAX routes: customer lookup, driver lookup, kendaraan data, COA autocomplete, rekening pilih, area CRUD
- PDF/Print routes: surat jalan, muat, invoice, kwitansi

### 4.2 Menu Registration (29 items) ✅
| Section | Menu Items |
|---|---|
| Dashboard | `dashboard` |
| Master | customer, driver, kendaraan, supplier, price×4, service, area |
| Transaksi | expedisi, invoice eks, edit-invoice eks, kwitansi eks, rent pendingin, rent invoice, rent edit, rent kwitansi, coolroom, coolroom invoice, edit invoice coolroom, coolroom kwitansi |
| Laporan | kwitansi history |
| Extra | pajak, rekening, signature |
| Akuntansi | dashboard (link) |

---

## 👁️ FASE 5: Views (55% — IN PROGRESS)

### 5.1 Created (17 dirs + 2 PDF) ✅
- `expedisi/index.blade.php` — list + filter + pagination
- `expedisi/create.blade.php` — form SJ + AJAX customer lookup
- `expedisi/show.blade.php` — detail view
- `expedisi/edit.blade.php` — edit form (+debug attrs)
- `expedisi/pdf/surjal.blade.php` — PDF template ⭐ (rewritten: proper SJ, not kwitansi)
- `expedisi/pdf/muat.blade.php` — PDF template ⭐ (+baru)
- `kendaraan/index.blade.php` + `create.blade.php` + `edit.blade.php`
- `coolroom/index.blade.php`
- `rent-pendingin/index.blade.php`
- `service/index.blade.php` + `create.blade.php`
- `expedisi-invoice/index.blade.php` (+debug attrs, fixed route refs)
- `expedisi-invoice-gabung/index.blade.php` — gabung SJ form ⭐ (+baru)
- `expedisi-invoice/show.blade.php` ⭐ (+baru)
- `expedisi-invoice/edit.blade.php` ⭐ (+baru)
- `expedisi-invoice/show-edit.blade.php` ⭐ (+baru)
- `expedisi-invoice/print/text.blade.php` ⭐ (+baru)
- `expedisi-invoice/pdf/index.blade.php` — PDF template ⭐ (+baru)
- `coolroom-invoice/index, generate, show, edit, show-edit, pdf/index` — generation, edit list/form, detail, dan PDF
- `expedisi-kwitansi/index.blade.php` (+debug attrs, fixed Sisa calc)
- `expedisi-kwitansi/pdf/index.blade.php` — PDF template ⭐ (+baru)
- `kwitansi-history/index.blade.php` — all kwitansi + export
- `rekening/index.blade.php`
- `price/index.blade.php` + `price-customer/index` + `price-rent/index` + `price-rent-customer/index`

### 5.2 TODO (remaining) — Step by step
- `rent-pendingin/create.blade.php`
- `rent-pendingin/pdf/surjal.blade.php` — PDF template
- `rent-pendingin-invoice/` (index, edit, show-edit, pdf)
- `rent-pendingin-kwitansi/index.blade.php` + pdf
- `coolroom/create.blade.php` + `edit.blade.php`
- `coolroom/pdf/index.blade.php` — PDF template

---

## ✅ FASE 6: Testing — 13/13 PASSED

```
$ php artisan test --filter=Transaksi

   PASS  Tests\Feature\Transaksi\MigrationTest
  ✓ semua tabel transaksi ada di database                2.80s
  ✓ kolom users mengandung reconciled columns            0.12s
  ✓ kolom arh mengandung reconciled columns              0.10s
  ✓ kolom aph mengandung reconciled columns              0.14s
  ✓ kolom drivers mengandung uppercase compatible        0.15s
  ✓ expedisis table ada dengan ledger columns            0.09s
  ✓ menu transaksi terdaftar                             0.10s

   PASS  Tests\Feature\Transaksi\ModelTest
  ✓ expedisi model bisa create dengan fillable            0.22s
  ✓ kendaraan model bisa create                           0.20s
  ✓ price model berhasil create                           0.21s
  ✓ coolroom model bisa create                            0.17s
  ✓ area model bisa create                                0.24s
  ✓ arh model membuat invoice                             0.19s

  Tests:    13 passed (61 assertions)
  Duration: 5.33s
```

**Test coverage:** Migration, Schema reconciliation, Menu registration, Model CRUD (Expedisi, Kendaraan, Price, Coolroom, Area, Arh)

---

## 📝 LOG PERUBAHAN

| Tanggal | Perubahan | Catatan |
|---|---|---|
| 2026-10-12 13:06 | Created progress tracker | Initial checkpoint |
| 2026-10-12 13:30 | Updated milestone_lintas.md | Refined: pure src style |
| 2026-10-12 13:45 | FASE 1: Migration | 18 migration file (3 reconcile + 12 new + 1 menu + 1 charset) |
| 2026-10-12 13:52 | FASE 1: Verify | `migrate:fresh` — 89 migrations ✅ (SQLite) |
| 2026-10-12 14:05 | FASE 2: Models | 11 new + 6 reconcile models + Helper.php |
| 2026-10-12 14:20 | FASE 3: Controllers | 21 controller (resource style) |
| 2026-10-12 14:30 | FASE 4: Routes | 141 routes in web.php + 28 menu items |
| 2026-10-12 14:40 | FASE 5: Views | 16 view dirs + 1 PDF template |
| 2026-10-12 14:50 | Fix: charset | Removed latin1 → utf8mb4 default (SQLite compat) |
| 2026-10-12 14:55 | Fix: table kwitansi | `kwitantis` → `kwitansi` (singular) |
| 2026-10-12 14:58 | Fix: drivers.PHONE | Skip PHONE (already exist in src schema) |
| 2026-10-12 14:59 | Fix: services syntax | Trailing comma → semicolon |
| 2026-10-12 15:10 | FASE 6: Testing | 13/13 tests passed, 61 assertions |
| 2026-10-12 15:05 | Fix: charset v1 | `SHOW COLUMNS->Collation` tidak ada untuk integer → ERROR |
| 2026-10-12 15:10 | Fix: charset v2 (committed 1f1f874) | Simplifikasi → langsung convert, 0.58ms |
| 2026-10-12 15:20 | Fix: FOXPRO dates (committed 9214225) | `0000-00-00` → `ALLOW_INVALID_DATES` + try-catch |
| 2026-10-12 15:25 | FASE 7: Seeder | `TransaksiPermissionSeeder.php` — auto-assign permissions — committed 4c06666 |
| 2026-10-12 15:30 | FINAL VERIFY | 13/13 tests passed, route cache SUCCESS, 91 migrations ✅ |
| 2026-10-15 00:15 | FASE 5 Step 1: Expedisi (EKS) | Fix view not found di expedisi-invoice + expedisi-kwitansi | Added 6 views, 3 PDF templates, 5 routes | Fixed surjal PDF (was kwitansi template with wrong vars) | Fixed kwitansi index Sisa calc (`$k->BAYAR`→`$k->SISA_FAKTUR`) | Added `show()` method to ExpedisiInvoiceController | Added `$signature`+`$rekening` to PDF methods | Debug attrs added to all views |
| 2026-10-15 00:20 | Tests re-verify | 13/13 passed, route cache SUCCESS |
| 2026-10-15 01:15 | Milestone: Driver accessor fix | Fixed `$d->getDisplayName()` → `$d->display_name` in expedisi create/edit (Driver model accessor, not method) |
| 2026-10-15 01:18 | Milestone: Price views fix | Created 8 missing price views (price ×2, price-customer ×2, price-rent ×2, price-rent-customer ×2) |
| 2026-10-15 01:19 | Milestone: Kendaraan fix | Removed unused Yajra DataTables import; Added `->except('show')` to kendaraan route (no show view) |
| 2026-10-15 01:20 | Milestone: Debug attrs | Added data-debug attrs to kendarawan + price views |
| 2026-10-15 00:22 | Tests re-verify | 13/13 passed, route cache SUCCESS |
| 2026-10-15 01:15 | Milestone: Driver accessor fix | Fixed `$d->getDisplayName()` → `$d->display_name` in expedisi create/edit (Driver model accessor, not method) |
| 2026-10-15 01:18 | Milestone: Price views | Created 8 missing price views (price ×2, price-customer ×2, price-rent ×2, price-rent-customer ×2) |
| 2026-10-15 01:19 | Milestone: Kendaraan fix | Removed unused Yajra DataTables import; Added `->except('show')` to kendaraan route (no show view) |
| 2026-10-15 01:20 | Milestone: Debug attrs | Added data-debug attrs to kendarawan + price views |
| 2026-10-15 01:22 | Tests re-verify | 13/13 passed, route cache SUCCESS |
| 2026-10-16 09:30 | Bug fix: FNO_PRK_P/S/O/M missing columns (#1054) | Added `FNO_PRK_P`, `FNO_PRK_S`, `FNO_PRK_O`, `FNO_PRK_M` to kendaraans: original migration + new migration `2026_10_16_091500` + model + controller + views; Original migration already ran so created new migration for existing DB | 13/13 tests pass |
| 2026-10-16 09:50 | Bug fix: prices #1054 unknown column KODE | Added ALL legacy columns (KODE, KETERANGAN, DARI, SAMPAI, RUTE, HV, HKG, HBOK, KUNCI, HG, JENIS) to prices migration + new migration + model + controller + all price-customer views rewritten with legacy cols | 13/13 tests pass |
| 2026-10-16 09:50 | Bug fix: pricecus #1054 same pattern as prices | Same fix for pricecus/pricecushis + new migration `2026_10_16_093000` + model + controller + all price-customer views rewritten with legacy cols | 13/13 tests pass |
| 2026-10-16 09:52 | Bug fix: pricedingin/pricedingincus #1054 | Added KODEDGN, KODE, PERIODE, PLAT, JENIS, ITEM, KUNCI to pricedingin tables + migration + model + controller + all price-rent views | 13/13 tests pass |
| 2026-10-16 09:55 | Tests re-verify | 13/13 passed, route cache SUCCESS |
| 2026-10-16 10:30 | Fix: migration edit after run | User complaint: edited migration 093000 setelah sudah di-run → gak work. Created 2 NEW migration files (103000 + 103500) untuk pricecushis + pricedingin tables yang belum ter-apply | 13/13 tests pass |
| 2026-10-16 10:35 | Fix: SQLite MODIFY COLUMN error | Wrapped MySQL-specific `ALTER TABLE` in `if (DB::getDriverName() === 'mysql')` | 13/13 tests pass |
| 2026-10-16 10:45 | Fix: TANGGAL mysql strict mode error | `SET SESSION sql_mode = ''` + clean `updated_at`/`created_at` (0000-00-00→NULL) + clean TANGGAL (single-char→NULL) BEFORE ALTER TABLE | 13/13 tests pass |
| 2026-10-16 10:48 | Fix: missing closing brace in migration | Added `};` di akhir file `103000` | 13/13 tests pass |
| 2026-10-16 10:47 | Tests re-verify | 13/13 passed, route cache SUCCESS |
| 2026-10-18 10:00 | Milestone: Price module enhancement | Updated PriceController: auto-generate KODE (PRC0000001+), set default HV/HKG/HBOK/HG=0, generate KUNCI, sync to pricecus on store/update. Updated price/index.blade.php: columns KETERANGAN/DARI/SAMPAI/RUTE/HARGA/JENIS, tombol aksi ikon-only rata tengah. Updated price/create.blade.php + edit.blade.php: simplified form (Nama Item, DARI, SAMPAI, RUTE, HARGA, JENIS radio Eceran/Booking). Added expedisis() relasi to Kendaraan model. Added delete button to kendaraan/index.blade.php with transaction check. | 13/13 tests pass |
| 2026-10-18 10:30 | Milestone: Price Customer sync | Updated PriceCusController: set default HV/HKG/HBOK/HG=0, generate KUNCI on store/update. Updated price-customer/index.blade.php: columns KETERANGAN/DARI/SAMPAI/RUTE/HARGA/JENIS, tombol aksi ikon-only rata tengah, delete button. Updated price-customer/create.blade.php + edit.blade.php: simplified form (Nama Item, DARI, SAMPAI, RUTE, HARGA, JENIS radio Eceran/Booking). | 13/13 tests pass |
| 2026-10-18 11:00 | Fix: Price Customer two-level navigation | Changed price-customer/index.blade.php to show customer list (not price list). Added customerPrices() method in PriceCusController for viewing price per customer. Created customer-prices.blade.php view. Updated create.blade.php to pre-fill customer when coming from customer prices page. Updated routes: added price-customer.customer-prices route. | 13/13 tests pass |
| 2026-10-18 10:45 | Feature: Price Customer full migration (src_old → src) | Migrated price-customer feature from src_old: (1) Fixed UrlGenerationException by clearing stale route cache (bootstrap/cache/routes-v7.php) + added null-safe route params; (2) Added updateAll() & saveRow() AJAX endpoints to PriceCusController (+ Price model import); (3) Added 2 new routes: price-customer.update-all & price-customer.update-row; (4) Rewrote customer-prices.blade.php with src_old features: customer info panel (Nama/Jenis Usaha/Pemilik/Alamat), inline HARGA input (yellow bg #fff7d1, always editable), save button per row (Fetch API → saveRow()), Update All button (Fetch API → updateAll()); (5) Added Email column to price-customer/index.blade.php. Stack follows src: Alpine.js (no jQuery), Fetch API (no AJAX SPA), Bootstrap 5, no DataTables. | 13/13 tests pass |
| 2026-10-18 10:50 | Fix: HARGA display + save button | Fixed HARGA not showing when DB value is empty/null — use `(float)` cast in Blade @json + `Number() \| 0` in Alpine factory. Fixed save button icon not showing — replaced `x-show` with `x-if` + `<template>` for proper Alpine.js rendering. | 13/13 tests pass |
| 2026-10-20 14:30 | Fix: TANGGAL price insert | Runtime DB masih menerima `TANGGAL` sebagai string maksimal 10 karakter, sedangkan cast `Price::$casts` mengirim datetime 19 karakter. Removed `TANGGAL` date cast from `Price` model and normalized `PriceController` store/update to `Y-m-d`. Also cleaned duplicate closing tags in `price-customer/create.blade.php`. | 13/13 tests pass |
| 2026-10-20 14:45 | Fix: Price customer delete binding | Resource route `price-customer` sekarang memakai `->parameters(['price-customer' => 'priceCus'])`, sehingga `{priceCus}` konsisten dengan `PriceCus $priceCus` di edit/update/destroy. Delete tetap memakai primary key `pricecus.id`; destroy memvalidasi pemakaian di `expedisis` berdasarkan `CUSTOMER_KODE` + `PESANAN`. | 13/13 tests pass |
| 2026-10-20 15:00 | Price customer performance + compatibility | Detail query memakai `simplePaginate(20)` dan `orderBy(DARI, SAMPAI, id)`. Added safe idempotent migration `2026_10_20_000001_add_price_customer_query_indexes.php` for `pricecus_kodecus_dari_sampai_id_index`. Fixed `Update All` to recognize legacy `pricecus.KODE` values stored as either `prices.id` or `prices.KODE`; new sync rows use `prices.KODE` to prevent duplicates. | 13/13 tests pass |

---

| 2026-10-21 08:15 | Audit: Price Rent / Price Dingin migration | CRUD dasar `src` tersedia, tetapi belum setara dengan `src_old`: belum ada lookup kendaraan/modal Alpine, belum generate `KODEDGN`/`TANGGAL`/`KUNCI`, belum derive `JENIS` dari kendaraan, belum sync master ke semua `pricedingincus`, index belum menampilkan nama kendaraan, search belum mencakup PLAT/ITEM/PERIODE/JENIS, dan resource binding `{price_rent}` belum konsisten dengan `Pricedingin $price`. Backlog ini dicatat di `docs/prompt_to_ai.md`; implementasi berikutnya tetap MVC + Alpine/full reload. | Audit selesai, implementasi belum dimulai |

| 2026-10-25 13:10 | Kwitansi Expedisi: ikuti src_old | Proses kwitansi per invoice sama seperti src_old: mengisi `expedisis.kwt`/`TGLKW` dan insert `kwitansi` (nilai = GRAND). Batal kwitansi mereset `expedisis` dan menghapus `kwitansi`. Arh hanya dibaca untuk PDF/print, tidak diubah. Tidak ada barh dan jurnal (mengikuti src_old; akuntansi akan ditentukan kemudian). Index: filter Belum/Sudah, Proses/Print/PDF/Batal. Print langsung ESC/P ke Electron (`expedisi-kwitansi.text`), pola sama dengan `expedisi-invoice/show`. | 13/13 tests pass, belum diuji di browser/printer |

| 2026-10-27 07:15 | Edit Invoice EKS: samakan dengan src_old (modal + AJAX) | `expedisi-invoice/edit` sekarang setara src_old: filter DARI/SAMPAI/CARI, daftar invoice (dari `arh` SOURCE=Expedisi), tombol Edit membuka modal (AJAX `expedisi-invoice-gen.show` → `showEditInvoiceJson`), item picker memakai `price-customer.price` (`PriceCusController::getPrice`, route baru), kalkulasi live (subtotal/disc/ppn/grand/piutang) di JS, dan simpan via AJAX PUT `expedisi-invoice-gen.update`. Backend `updateEditInvoice` di-extend: recompute nilai (item, rute, jumlah, harga, DISC, NDISC, DC, TOTAL, PPN, GRAND, BAYAR, PIUTANG) + update/create/delete `arh` + tetap update metadata; jalur metadata-only lama tetap dipertahankan agar test lama lulus. Route baru: `expedisi-invoice-gen.data`, `expedisi-invoice-gen.show`, `price-customer.price`. Stack tetap Bootstrap 5 + Fetch (tanpa jQuery/DataTables). | Blade compile OK, 13/13 tests pass |

| 2026-10-27 07:30 | Review fix: Edit Invoice EKS | Perbaikan hasil code review: (1) filter DARI/SAMPAI dipindah ke `indexEdit` (form GET) & hapus `tableEdit` + route `.data` yang mati; (2) `TGLINVOICE` kini ikut diupdate di jalur financial; (3) `SALDO` arh konsisten = GRAND (sama dengan generate), bukan PIUTANG; (4) tidak lagi `arh->delete()` saat piutang 0 — arh dipertahankan dengan `STATUS=PAID` + `TGLLUNAS` (hindari 404 di show/PDF); (5) detail modal memakai `row.TOTAL` asli, bukan `hargaaw`; (6) `getPrice` dibatasi (limit 500 + kolom terpilih); (7) view ditambah helper `esc()`/`attr()` untuk cegah stored XSS di item picker & tabel detail; (8) kalkulasi JS dibuat step-by-step `Math.round` agar sinkron dengan PHP. | 13/13 tests pass, route cache OK |

| 2026-10-27 08:00 | Edit Invoice EKS: izinkan edit walau ada kwitansi | Skema tabel `kwitansi` di `src` memang diubah total vs `src_old` (relasi `FDOK_TRANS`→`NOFAKTUR`, nilai `TOTAL`/`PPN`/`DISC`/`NDISC`→`SALDO_FAKTUR`/`NILAI_BAYAR`/`DISCOUNT`/`SISA_FAKTUR`). Tidak ada kode `src` yang memakai kolom lama, jadi tidak ditambahkan migrasi kolom lama (hindari duplikasi/drift). Untuk setara perilaku `src_old` (invoice tetap bisa diedit walau sudah ada kwitansi): guard `ensureInvoiceEditable()` tidak lagi memblokir karena kwitansi — tetap blokir bila `BAYAR>0` / `STATUS=PAID` (uang benar-benar masuk). `updateEditInvoice()` jalur financial kini menyinkronkan nilai kwitansi yang sudah ada (`SALDO_FAKTUR`/`NILAI_BAYAR`=`$grand`, `DISCOUNT`=`$ndisc`, `SISA_FAKTUR`=0) — setara logika update `TOTAL`/`PPN`/`DISC`/`NDISC` di `src_old`. | 13/13 tests pass |

| 2026-10-27 08:20 | Skema `kwitansi` dikembalikan ke kolom lama (src_old) — perintah bos | Migrasi create `2026_10_13_000020_create_kwitantis_table.php` ditulis ulang mengikuti persis skema `src_old` (`NOKWT`, `TGL`, `FDOK_TRANS`, `TGL_TRANS`, `CUSTOMER`, `NOSJ`, `FKETERANG`, `FNAMA`, `FNIL_DOK`, `TOTAL`, `PPN`, `DISC`, `NDISC`, `USERINPUT`, `JENIS`). Ditambah migrasi baru `2026_10_27_000001_align_kwitansi_columns_to_legacy.php` (idempotent) untuk DB yang sudah terlanjur memakai skema baru: rename `NO_KWT→NOKWT`, `TGL_KWT→TGL`, `NOFAKTUR→FDOK_TRANS`, `SALDO_FAKTUR→FNIL_DOK`, `NAMACUST→FNAMA`, `KETERANGAN→FKETERANG`, `JENIS_KWT→JENIS`, `DISCOUNT→NDISC`, `user→USERINPUT`, tambah `TGL_TRANS`/`NOSJ`/`TOTAL`/`PPN`/`DISC`/`NDISC`, hapus kolom tambahan `NILAI_BAYAR`/`RETUR`/`SISA_FAKTUR`/`NOBAYAR`/`USEREDIT`/`area_id`/`area_name`/`NOJURNAL`/`jurnal_id`. Model `Kwitansi` diubah (fillable/casts/relasi `arh` via `FDOK_TRANS`). `ExpedisiKwitansiController` (proses/delete/generateKW) dan sinkronisasi kwitansi di `updateEditInvoice` memakai kolom lama. | 13/13 tests pass. Catatan: `CoolroomKwitansiController`, `RentPendinginKwitansiController`, `KwitansiHistoryController`, view `kwitansi-history`, dan laporan yang masih memakai kolom baru (`NO_KWT`/`NOFAKTUR`/`JENIS_KWT`/`NILAI_BAYAR`) sengaja dibiarkan dulu — akan diperbaiki satu-satu menyusul (perintah bos). |

| 2026-10-27 08:35 | Edit Invoice EKS: guard disamakan persis src_old | `ensureInvoiceEditable()` sebelumnya memblokir bila `BAYAR>0` ATAU `STATUS='PAID'`. Di `src_old` guard hanya memblokir `arh.BAYAR > 0` (showEditInvoice baris 603-609) — tidak memblokir `STATUS='PAID'` dan tidak memblokir kwitansi. Agar setara persis: cek `STATUS='PAID'` dihapus, hanya `BAYAR > 0` yang memblokir, pesan disamakan `'Invoice sudah memiliki pembayaran. Silakan hubungi admin.'`. Tidak ada celah karena `prosesInvoiceStore` menandai `STATUS='PAID'` hanya saat bayar penuh (BAYAR>0), jadi sudah tercakup. | 13/13 tests pass |

| 2026-10-21 11:20 | Implementasi Price Rent Customer | `price-rent-customer/index` diubah menjadi daftar customer; ditambahkan detail customer, info customer, `Update Harga Sewa`, inline edit HARGA, route/controller `customer-prices`, `update-all`, dan `update-row`. Resource binding memakai `{pricedingincus}`; stack tetap MVC + Alpine tanpa DataTables. | Blade compile OK, 13/13 tests pass |
| 2026-10-30 15:00 | Rent Pendingin invoice migration review | Legacy behavior dari `src_old` dipertahankan: list invoice generate tetap filter `JENIS='REN'`, `NOMUAT` wajib terisi, validasi bayar/PIUTANG dan ARH sync tetap mengikuti flow lama; visual modern hanya diterapkan di halaman `src` tanpa mengubah kolom DB, controller method, atau AJAX payload. Pada halaman edit invoice, ringkasan nilai otomatis, status invalid, dan debug attr ditambahkan agar lebih profesional dan mudah diklik inspect-user. | Review + polish selesai; target test flow `RentPendinginInvoiceFlowTest` 3/3 passed |
| 2026-10-30 15:20 | Edit Invoice Rent Pendingin diseragamkan dengan Expedisi | Daftar edit REN kini membuka modal melalui Fetch JSON, termasuk picker harga customer dari `pricedingincus`; simpan memakai endpoint `PUT` yang mempertahankan hitungan legacy serta sinkronisasi `expedisis`/`arh`/`kwitansi`, lalu redirect ke detail invoice. Filter edit mengikuti legacy (tanpa syarat `NOMUAT`) dan Bayar ditampilkan sebagai `GRAND - PIUTANG`. | `RentPendinginInvoiceFlowTest` 4/4, `ExpedisiInvoiceGenerationTest` 10/10; route cache dan Blade cache sukses |
| 2026-10-30 17:45 | Coolroom resource direkonsiliasi dengan legacy | Ditambahkan migration kompatibilitas baru (migration lama tidak diubah), field legacy `CUSTOMER_KODE`/`TGLSJ`/`BOXING`/`SUBTOTAL`/`DPP`/`NPPN`/`INVOICE`/pembayaran dan presisi jumlah 3 desimal; CRUD menghitung ulang nilai di server, membuat NOSJ `SCmmyy#####`, serta menolak edit/hapus data ber-invoice. Index memfilter data belum invoice; create/edit/show, customer picker, pratinjau hitungan, dan PDF Surat Jalan tersedia. | `CoolroomFlowTest` 3/3 (45 assertions), `ModelTest` 6/6, route list dan Blade cache sukses. MySQL `migrate:status` tidak bisa diverifikasi karena koneksi `192.168.1.7:3306` refused; migration tidak dijalankan pada server. |
| 2026-10-30 19:00 | Coolroom invoice diselaraskan dengan Rent Pendingin dan legacy | Alur POST invoice kini terhubung ke controller, menjaga nomor `FCOyyyy######`, parsing nominal rupiah lokal, guard pembayaran/duplikasi, update `coolrooms` + ARH `SOURCE=Coolroom`, dan delete ARH saat lunas mengikuti legacy. Daftar membaca Coolroom supaya invoice lunas tetap ada; ditambahkan detail printable, PDF, direct-print text, export Excel dengan filter tanggal/status/customer, serta daftar edit dan sync kwitansi. Halaman utama memakai kartu ringkasan, filter, tabel responsif, dan form Fetch tanpa jQuery; simpan sukses menuju detail invoice. | `CoolroomInvoiceFlowTest` 4/4; gabungan Coolroom, Model, Rent Pendingin, dan Expedisi 27/27 (261 assertions); route/view cache dan lint sukses. |
| 2026-10-31 02:10 | Coolroom kwitansi dipulihkan ke alur legacy | Index mengambil invoice dari `coolrooms` (`INVOICE` terisi, `GRAND > 0`) dan memfilter `KWT`; proses menerima payload `invoice`, mengisi `KWT`/`TGLKW` pada semua baris invoice, membuat record kolom legacy dengan `JENIS=COL`, dan menghasilkan nomor `KWyyyy#######`. Pembatalan menerima `kwt`, mengosongkan hanya field kwitansi, menghapus record `COL`, serta tidak mengubah `BAYAR`/`PIUTANG` atau membuat jurnal. Ditambahkan MVC view Bootstrap/pagination, direct-print text, PDF DomPDF, dan route eksplisit; tidak ada perubahan schema. | `CoolroomKwitansiFlowTest` 3/3 (29 assertions), route list dan Blade cache sukses. Seluruh suite: 175 passed, 43 failed pada modul existing di luar Coolroom Kwitansi. |
| 2026-10-31 10:47 | Coolroom invoice edit dipoles dan menu sidebar didaftarkan | Route `/coolroom-invoice-gen` ditambahkan sebagai menu sidebar non-parameter dengan hak akses yang disalin dari menu Invoice Coolroom; Bayar pada tabel edit dihitung sebagai `GRAND - PIUTANG` sesuai legacy. Ditambahkan modal Panduan Edit dan regression test menu, nilai Bayar, serta redirect sukses ke `/coolroom-invoice/{invoice}` berikut flash message. | `CoolroomInvoiceFlowTest` 5/5 (59 assertions), PHP lint/Pint, route cache, dan Blade cache sukses. |
| 2026-10-31 11:11 | Format angka dan pembulatan edit Coolroom diseragamkan | Jumlah KG, harga/bayar, diskon, dan PPN pada form edit kini menerima format Indonesia (titik ribuan, koma desimal); nol desimal yang tidak perlu tidak lagi dipaksa tampil dan unit KG dipisah. Server serta preview memakai integer berskala yang sama untuk membulatkan subtotal, diskon, DPP, PPN, dan grand sesuai urutan legacy. List generate juga merapikan jumlah serta persentase. | `CoolroomInvoiceFlowTest` 6/6 (74 assertions), gabungan test `--filter=Coolroom` 13/13 (149 assertions), PHP syntax check, dan Blade cache sukses. |

## 📊 Migration File Status (untuk user)

### SUDAH DI-RUN oleh user (jangan di-edit lagi):
- `2026_10_16_000001_add_fno_prk_p_to_kendaraans.php` ✅
- `2026_10_16_093000_add_price_legacy_columns.php` ✅ (hanya pricecus kolom)

### BARU — belum di-run, jalankan `php artisan migrate`:
- `2026_10_31_000001_register_coolroom_invoice_edit_menu.php` — register menu sidebar Edit Invoice Coolroom dan salin hak akses dari menu invoice Coolroom
- `2026_10_16_091500_add_fno_prk_som_to_kendaraans.php` — FNO_PRK_S/O/M untuk kendaraans
- `2026_10_16_103000_add_price_legacy_columns_v2.php` — pricecushis kolom + prices kolom tambahan + TANGGAL date fix (MySQL only)
- `2026_10_16_103500_add_pricedingin_legacy_columns.php` — pricedingins + pricedingincus + pricedinginhis kolom

### Untuk user — jalankan di server:
```bash
cd /home/dode/project/lintas_anugrah_pos/src
php artisan config:clear && php artisan cache:clear
php artisan migrate  # akan menjalankan migration baru yang belum diterapkan
```

---

## 🎯 RENCANA TAILUHE

### Langkah Selanjutnya (step by step, awaiting review)
1. **Rent Pendingin (REN)** — 3 view dirs + 3 PDF templates
2. **Coolroom** — 3 view dirs + 3 PDF templates  
3. **Controller endpoint test** — buat test untuk verifikasi route dapat diakses
4. **Data migration script** — script konversi data lama ke tabel baru
5. **Integration test** — transaksi → jurnal → arh/barh → ledger sync
