# 📋 MILESTONE: Migrasi `src_old` → `src` (Pure src Style)

**Tanggal mulai:** 2026-10-12
**Stack target:** Laravel 12, Breeze Auth, Tailwind 3 + Alpine.js, dompdf, maatwebsite/excel, Custom Permission (Role + Menu)
**Path sumber:** `/home/dode/project/lintas_anugrah_pos/src_old`
**Path target:** `/home/dode/project/lintas_anugrah_pos/src`

---

## 🎯 FILOSOFI MIGRASI

> **Ikuti style `src` total.** Jangan bawa pola lama (AJAX container, DataTables, Spatie, mpdf, custom auth).
> Tulis ulang semua konten transaksi `src_old` ke dalam arsitektur `src` yang sudah ada.

```
src_old (FOXPRO legacy)         →   src (modern Laravel)
  • UPPERCASE columns              • lowercase (tabela baru) + UPPERCASE legacy (transitional)
  • latin1 charset                 • utf8mb4
  • AJAX container pattern         • resource controllers
  • DataTables + jQuery            • pagination + Alpine.js
  • Spatie permission              • custom Role + Menu
  • mpdf                           • dompdf
  • custom LoginController         • Laravel Breeze
```

---

## 🔧 KONFIGURASI STACK (konstan sepanjang migrasi)

| Komponen | Keputusan | Alasan |
|---|---|---|
| Auth | **Laravel Breeze** (sudah ada di src) | Jangan bawa src_old LoginController/RegisterController |
| Permission | **Custom (Role + Menu + role_menu)** (sudah ada di src) | Jangan pakai spatie — migrasi data per-role |
| DataTables | **Manual pagination** (src pattern) | jQuery+Datatables → Alpine.js + paginate() |
| PDF | **barryvdh/laravel-dompdf** (sudah ada di src) | Konversi 9 template mpdf → dompdf |
| Excel | **maatwebsite/excel** (sudah ada di keduanya) | ✅ compatible |
| Debug | **barryvdh/laravel-debugbar** (hanya dev) | Opsional, tambahkan untuk debugging |
| Layout | **Breeze layouts + Tailwind** | Hapus AJAX container → resource views |
| Model style | `HasFactory`, `$casts`, `$fillable`, accessors, `BelongsTo/HasMany` | Ikut pattern `Aph.php`, `Arh.php` di src |

---

## 📊 SCOPE — Modul di Sidebar Aktif (28 item)

Berdasarkan `src_old/resources/views/dashboard/sidebar.blade.php`:

### Master Data (6 modul)
| No | Sidebar ID | src_old Controller | src Controller | Status |
|---|---|---|---|---|
| 1 | `sidebar_register_customer` | CustomerController | CustomerController | ✓ EXISTS — reconcile |
| 2 | `sidebar_new_kendaraan` | KendaraanController | — | ❌ NEW |
| 3 | `sidebar_new_driver` | DriverController | DriverController | ✓ EXISTS — reconcile schema |
| 4 | `sidebar_new_prices` | PricesController | — | ❌ NEW |
| 5 | `sidebar_prices_customer` | PricesCustomerController | — | ❌ NEW |
| 6 | `sidebar_new_rent` | PricedinginController | — | ❌ NEW |
| 7 | `sidebar_rent_customer` | PricedinginCustomerController | — | ❌ NEW |
| 8 | `sidebar_new_supplier` | MsupplierController | SupplierController | ✓ EXISTS — map to src |
| 9 | `sidebar_new_service` | ServiceController | — | ❌ NEW |

### Transaksi EKS (6 modul)
| No | Sidebar ID | src_old Controller | src Controller | Status |
|---|---|---|---|---|
| 1 | `sidebar_new_expedisi` | ExpedisiController (1197 ln) | ❌ NEW | ⭐ PALING PENTING |
| 2 | `sidebar_invoice_expedisi` | ExpedisiInvoiceController | ❌ NEW | |
| 3 | `sidebar_Invoice_gen_expedisi` | ExpedisiGenerateInvoiceController (1614 ln) | ❌ NEW | ⭐ PALING PANJANG |
| 4 | `sidebar_Invoice_kwt_expedisi` | ExpedisiKwitansiController (596 ln) | ❌ NEW | |
| 5 | `sidebar_Invoice_edit_expedisi` | (bagian ExpedisiGenerateInvoiceController) | ❌ NEW | |
| 6 | `sidebar_new_kwitansi` | KwitansiHistoryController | ❌ NEW | |

### Transaksi REN (4 modul)
| No | Sidebar ID | src_old Controller | src Controller | Status |
|---|---|---|---|---|
| 1 | `sidebar_new_rent_dingin` | RentPendinginController (840 ln) | ❌ NEW | |
| 2 | `sidebar_generate_rent_dingin` | RentPendinginGenerateInvoiceController (728 ln) | ❌ NEW | |
| 3 | `sidebar_generate_kwt_dingin` | RentPendinginKwitansiController (413 ln) | ❌ NEW | |
| 4 | `sidebar_Invoice_edit_rent` | (bagian RentPendinginGenerateInvoice) | ❌ NEW | |
| ⚠️ | `sidebar_invoice_rent_dingin` | RentPendinginInvoiceController | — | ❌ ORPHAN (dikomentar) |

### Transaksi Coolroom (4 modul)
| No | Sidebar ID | src_old Controller | src Controller | Status |
|---|---|---|---|---|
| 1 | `sidebar_coolroom` | CoolroomController (362 ln) | ❌ NEW | |
| 2 | `sidebar_coolroom_inv` | CoolroomGenerateInvoiceController (1212 ln) | ❌ NEW | |
| 3 | `sidebar_coolroom_kwt` | CoolroomKwitansiController (206 ln) | ❌ NEW | |
| 4 | `sidebar_coolroom_edit` | (bagian CoolroomGenerateInvoiceController) | ❌ NEW | |

### Extra / Pendukung (8 modul)
| No | Sidebar ID | src_old Controller | src Controller | Status |
|---|---|---|---|---|
| 1 | `sidebar_extra_pajak` | PajakController | — perlu buat | ❌ NEW |
| 2 | `sidebar_extra_rekening` | RekeningController | — | ❌ NEW |
| 3 | `sidebar_extra_signature` | SignatureController | — | ❌ NEW |
| 4 | `sidebar_extra_permissions` | UserPermissionController | SettingsController | ✓ EXISTS — map |
| 5 | `sidebar_extra_area` | AreaController | — | ❌ NEW |
| 6 | `sidebar_list_user` | (RegisterController) | UserController | ✓ EXISTS |
| 7 | `sidebar_new_user` | (RegisterController) | UserController | ✓ EXISTS |
| ⚠️ | `sidebar_extra_printer` | PrinterController | — | ❌ ORPHAN (dikomentar) — SKIP |

### Akuntansi
| No | Sidebar ID | Action |
|---|---|---|
| 1 | `sidebar_akuntansi` | External link ke ledger — tidak perlu migrasi |

**TOTAL modul yang perlu dibangun: 17 NEW controller + 3 reconcile controller**

---

## 📋 CHECKLIST MIGRASI

### FASE 1: Migration Foundation (BAB 1)

#### 1.1 Migration Conflict Resolution
- [ ] **1.1.1** — Fix `users` table conflict: merge `is_admin` (src) + `role_old` (src_old) ke satu skema
- [ ] **1.1.2** — Schema reconciliation: `arh` — gabung kolom src_old (SALES, AREA, DIVISI, CABANG char(2)) ke src schema (string(50))
- [ ] **1.1.3** — Schema reconciliation: `aph` — tambah `UM`, `AUTO` columns ke src schema; fix `SUPPLIER` char(6) → string(100)
- [ ] **1.1.4** — Schema reconciliation: `msupplier` — pilih `SUPPLIER` sebagai PK (ikuti src style)
- [ ] **1.1.5** — Schema reconciliation: `mcustomer` — tambah FoxPro columns (desa, ktp_p, dsb.) ke src schema
- [ ] **1.1.6** — Table rename: `driver` → `drivers` — migration rename + model update

#### 1.2 New Migrations (11 tabel baru)
- [ ] **1.2.1** — `2026_10_13_000010_create_kendaraans_table.php` (UPPERCASE, latin1)
- [ ] **1.2.2** — `2026_10_13_000011_create_prices_table.php` (+TANGGAL, latin1)
- [ ] **1.2.3** — `2026_10_13_000012_create_pricecus_table.php` (KODECUS(50), +TANGGAL, latin1)
- [ ] **1.2.4** — `2026_10_13_000013_create_pricecushis_table.php`
- [ ] **1.2.5** — `2026_10_13_000014_create_pricedingin_table.php` (+TANGGAL, latin1)
- [ ] **1.2.6** — `2026_10_13_000015_create_pricedingincus_table.php`
- [ ] **1.2.7** — `2026_10_13_000016_create_pricedinginhis_table.php`
- [ ] **1.2.8** — `2026_10_13_000017_create_rute_table.php`
- [ ] **1.2.9** — `2026_10_13_000018_create_expedisi_table.php` ⭐ TRANSAKSI UTAMA
- [ ] **1.2.10** — `2026_10_13_000019_create_coolrooms_table.php`
- [ ] **1.2.11** — `2026_10_13_000020_create_kwitansi_table.php`
- [ ] **1.2.12** — `2026_10_13_000021_create_service_table.php`
- [ ] **1.2.13** — `2026_10_13_000022_create_rekening_table.php`
- [ ] **1.2.14** — `2026_10_13_000023_create_signatures_table.php`
- [ ] **1.2.15** — `2026_10_13_000024_create_areas_table.php`
- [ ] **1.2.16** — `2026_10_13_000025_create_ol_pajak_table.php`
- [ ] **1.2.17** — `2026_10_13_000026_create_user_printers_table.php` (SKIP — printer dikomentar)
- [ ] **1.2.18** — Tambahkan kolom ledger integration: `NOJURNAL, jurnal_id` ke tabel AR/AP (arh, barh, bard, aph, baph, bapd)

#### 1.3 Charset Conversion
- [ ] **1.3.1** — Convert semua tabel legacy dari latin1 → utf8mb4
- [ ] **1.3.2** — `php artisan migrate:fresh --seed` — verifikasi semua migration berhasil

---

### FASE 2: Models (BAB 4)

#### 2.1 New Models (11 file)
- [ ] `Kendaraan.php` — table `kendaraan`, UPPERCASE cols, HasFactory, casts
- [ ] `Price.php` — table `prices`, +TANGGAL cast
- [ ] `PriceCus.php` — table `pricecus`, +TANGGAL
- [ ] `PriceCushis.php` — table `pricecushis`, +TANGGAL
- [ ] `Pricedingin.php` — table `pricedingin`, +TANGGAL
- [ ] `Pricedingincus.php` — table `pricedingincus`, +TANGGAL
- [ ] `Pricedinginhis.php` — table `pricedinginhis`, +TANGGAL
- [ ] `Rute.php` — table `rute`
- [ ] `Expedisi.php` — table `expedisi`, ledger relationships
- [ ] `Coolroom.php` — table `coolrooms`, +SJ column
- [ ] `Kwitansi.php` — table `kwitansi`
- [ ] `Service.php` — table `service`
- [ ] `Rekening.php` — table `rekening`
- [ ] `Signature.php` — table `signatures`
- [ ] `Area.php` — table `areas`
- [ ] `Pajak.php` — table `ol_pajak`

#### 2.2 Model Reconciliation (6 file)
- [ ] `User.php` — merge: is_admin (src) + role_old (src_old) + area_id, area_name
- [ ] `Mcustomer.php` — tambah FoxPro kolom yang hilang di src
- [ ] `Driver.php` → `drivers` table — kolom mapping KODE→code, NAMA→name, ALAMAT→address, MULAI→mulai_kerja
- [ ] `Arh.php` — merge src_old kolom (SALES, AREA) ke src
- [ ] `Aph.php` — tambah UM, AUTO columns dari src_old
- [ ] `Msupplier.php` — reconcile PK (src punya SUPPLIER sebagai PK)

#### 2.3 Ledger Integration
- [ ] Tambahkan ke semua model: `public function jurnal(): BelongsTo { return $this->belongsTo(Jurnal::class, 'NOJURNAL', 'no_jurnal'); }`
- [ ] Tambahkan ke semua model: `public function jurnalItem(): HasMany { return $this->hasMany(JurnalItem::class, 'NOJURNAL', 'no_jurnal'); }`

---

### FASE 3: Controllers (BAB 5)

> **Pola:** Resource controller, bukan AJAX callback di container

#### 3.0 Existing Controllers (3 file — reconcile)
- [ ] `DashboardController.php` — tambahkan `summary()` dan `chart()` method
- [ ] `CustomerController.php` — update ke skema UPPERCASE
- [ ] `DriverController.php` — update ke skema drivers (lowercase) + ledger integration

#### 3.1 Master Data Controllers (5 file)
- [ ] `KendaraanController.php` — Resource: index, create, store, edit, update, destroy
- [ ] `PriceController.php` — Resource (ganti PricesController)
- [ ] `PriceCusController.php` — Resource (ganti PricesCustomerController)
- [ ] `PricedinginController.php` — Resource
- [ ] `ServiceController.php` — Resource, dependensi ke Msupplier+Kendaraan

#### 3.2 Extra Controllers (4 file)
- [ ] `PajakController.php` — Resource (PPN rate settings)
- [ ] `RekeningController.php` — Resource (bank accounts)
- [ ] `SignatureController.php` — Resource (authorized signatures)
- [ ] `AreaController.php` — Resource (branch/area)

#### 3.3 Expedisi — EKS (4 file) ⭐
- [ ] `ExpedisiController.php` — Resource: index, create, store, show, edit, update, destroy<br>
  Integration: store() → auto create Arh + Jurnal via JournalService
- [ ] `ExpedisiInvoiceController.php` — Resource/Process: gabung SJ → invoice → create Arh
- [ ] `ExpedisiGenerateInvoiceController.php` — Resource: generate invoice, create Jurnal+JurnalItem, Excel export
- [ ] `ExpedisiKwitansiController.php` — Resource: kwitansi processing → create Barh/Bard + Jurnal

#### 3.4 Rent Pendingin — REN (3 file)
- [ ] `RentPendinginController.php` — Resource (filter JENIS='REN')
- [ ] `RentPendinginGenerateInvoiceController.php` — Resource: invoice gen, create Arh + Jurnal
- [ ] `RentPendinginKwitansiController.php` — Resource: kwitansi → Barh/Bard + Jurnal
- [ ] ❌ `RentPendinginInvoiceController.php` — **ORPHAN** (hapus)

#### 3.5 Coolroom (3 file)
- [ ] `CoolroomController.php` — Resource
- [ ] `CoolroomGenerateInvoiceController.php` — Resource: invoice gen, create Arh + Jurnal
- [ ] `CoolroomKwitansiController.php` — Resource: kwitansi → Barh/Bard + Jurnal

#### 3.6 Kwitansi History & Export
- [ ] `KwitansiHistoryController.php` — Resource: history semua kwitansi (EKS + REN + Coolroom)

---

### FASE 4: Routes (BAB 6)

#### 4.0 Existing Routes (update)
- [ ] Update `web.php` — tambahkan resource routes untuk controller baru
- [ ] Update middleware — gunakan `CheckPermission` (bukan `@can()`)

#### 4.1 New Routes (21 resource routes)
```php
Route::resource('expedisi', ExpedisiController::class);
Route::resource('expedisi-invoice', ExpedisiInvoiceController::class);
Route::resource('expedisi-generate-invoice', ExpedisiGenerateInvoiceController::class);
Route::resource('expedisi-kwitansi', ExpedisiKwitansiController::class);
// ... dst untuk semua controller
```

#### 4.2 Menu Registration
- [ ] Daftarkan 32 sidebar menu ke `RoleMenuSeeder` / migration Menu
- [ ] Map permission names: `penjualan.expedisi`, `penjualan.coolroom`, dll.

---

### FASE 5: Views (BAB 7)

> **Pola:** Tailwind + Breeze components + Alpine.js, bukan jQuery + DataTables

#### 5.0 Layout (2 file)
- [ ] Hapus AJAX container pattern
- [ ] Gunakan `layouts/app.blade.php` + Breeze navbar

#### 5.1 View Directories (25 directory → rewrite)
```
expedisi/                    → resource-style views
expedisi-invoice/            → resource-style views
expedisi-generate-invoice/   → resource-style views
expedisi-kwitansi/           → resource-style views
rent-pendingin/              → resource-style views
rent-pendingin-generate-invoice/
rent-pendingin-kwitansi/
coolroom/
coolroom-generate-invoice/
coolroom-kwitansi/
kwitansi-history/
kendaraan/
prices/
price-customer/
price-rent/
price-rent-customer/
service/
supplier/
pajak/
rekening/
signature/
area/
```

#### 5.2 PDF Templates (9 file)
| No | src_old | src | Target format |
|---|---|---|---|
| 1 | `expedisi-surjal-pdf.blade.php` | `expedisi/pdf/surjal.blade.php` | dompdf |
| 2 | `expedisi-muat-pdf.blade.php` | `expedisi/pdf/muat.blade.php` | dompdf |
| 3 | `expedisi-invoice-pdf.blade.php` | `expedisi-invoice/pdf/index.blade.php` | dompdf |
| 4 | `expedisi-kwitansi-pdf.blade.php` | `expedisi-kwitansi/pdf/index.blade.php` | dompdf |
| 5 | `rentPendingin-surjal-pdf.blade.php` | `rent-pendingin/pdf/surjal.blade.php` | dompdf |
| 6 | `rentPendingin-gen-pdf.blade.php` | `rent-pendingin-generate-invoice/pdf/index.blade.php` | dompdf |
| 7 | `coolroom-pdf.blade.php` | `coolroom/pdf/index.blade.php` | dompdf |
| 8 | `coolroomInvoicePdf.blade.php` | `coolroom-generate-invoice/pdf/index.blade.php` | dompdf |
| 9 | `coolroom-kwt-pdf.blade.php` | `coolroom-kwitansi/pdf/index.blade.php` | dompdf |

#### 5.3 Helper
- [ ] Salin `Helper.php` (terbilang function) ke `src/app/Helpers/`
- [ ] Update `composer.json` autoload files

---

### FASE 6: Testing (BAB 8)

#### 6.1 Unit & Feature Tests
- [ ] Migration test
- [ ] Model test (fillabel, casts, relationships)
- [ ] Controller test (resource methods, permission)
- [ ] Ledger integration test (transaksi → Jurnal creation)
- [ ] PDF export test
- [ ] API endpoint test

---

## 📏 ESTIMATE EFFORT

| Fase | Aktivitas | Estimasi |
|---|---|---|
| FASE 1 | Migrations + Schema (18 file) | 1-2 hari |
| FASE 2 | Models (15 file) | 1-2 hari |
| FASE 3 | Controllers (21 file) | 3-4 hari |
| FASE 4 | Routes + Permission | 1 hari |
| FASE 5 | Views (25 dir + 9 PDF) | 2-3 hari |
| FASE 6 | Testing | 1-2 hari |
| | **TOTAL** | **10-16 hari kerja** |
