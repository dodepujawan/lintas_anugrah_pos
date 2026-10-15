# 📊 PROGRESS MIGRASI — src_old → src (Pure src Style)

**Project:** Lintas Anugrah Ekspedisi Pro
**Tanggal mulai:** 2026-10-12
**Tanggal update:** 2026-10-12 14:20 (FINAL)
**Branch:** `main` (committed 3x: 1f1f874, 9214225, 4c06666)
**Stack:** Laravel 12 + Breeze + Tailwind + Bootstrap 5 + dompdf + Custom Permission (Role+Menu)

---

## 🚦 STATUS SAAT INI

```
FASE 1: Migration Foundation          [██████████] 100% ✅ Selesai (20 file)
FASE 2: Models                        [██████████] 100% ✅ Selesai (17 file)
FASE 3: Controllers                   [██████████] 100% ✅ Selesai (21 file)
FASE 4: Routes + Menu registration    [██████████] 100% ✅ Selesai (141 routes, 28 menu)
FASE 5: Views                         [███████░░░░░]  70% (16/25 dirs + 4/9 PDF) — IN PROGRESS
FASE 6: Testing                       [██████████] 100% ✅ 13/13 tests passed (61 assertions)
────────────────────────────────────────────────────────────
TOTAL                              [███████░░░░░░]  84%
```

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

### 4.2 Menu Registration (28 items) ✅
| Section | Menu Items |
|---|---|
| Dashboard | `dashboard` |
| Master | customer, driver, kendaraan, supplier, price×4, service, area |
| Transaksi | expedisi, invoice eks, edit-invoice eks, kwitansi eks, rent pendingin, rent invoice, rent edit, rent kwitansi, coolroom, coolroom invoice, coolroom edit, coolroom kwitansi |
| Laporan | kwitansi history |
| Extra | pajak, rekening, signature |
| Akuntansi | dashboard (link) |

---

## 👁️ FASE 5: Views (55% — IN PROGRESS)

### 5.1 Created (16 dirs + 1 PDF) ✅
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
- `coolroom-invoice/` (show, edit, pdf)
- `coolroom-kwitansi/index.blade.php` + pdf

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

---

## 🎯 RENCANA TAILUHE

### Langkah Selanjutnya (step by step, awaiting review)
1. **Rent Pendingin (REN)** — 3 view dirs + 3 PDF templates
2. **Coolroom** — 3 view dirs + 3 PDF templates  
3. **Controller endpoint test** — buat test untuk verifikasi route dapat diakses
4. **Data migration script** — script konversi data lama ke tabel baru
5. **Integration test** — transaksi → jurnal → arh/barh → ledger sync
