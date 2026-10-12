FILE ASAL DI src/ (ledger): TIDAK DIUBAH SAMA SEKALI ✓
═══════════════════════════════════════════════
bootstrap/app.php    → hanya TAMBAH 1 line (middleware alias)
database/seeders/DatabaseSeeder.php → TAMBAH 1 line (seeder call)
composer.json        → TAMBAH autoload helper
.gitignore           → TAMBAH 2 lines (view cache exclusion)

MODEL RECONCILE (hanya TAMBAH, tidak hapus):
  User.php     → + role_old, area_id, area_name di fillable/casts
  Arh.php      → + SALES, AREA, DIVISI, area_id, jurnal_id + relasi jurnal()
  Aph.php      → + UM, AUTO, area_id, jurnal_id + relasi jurnal()
  Msupplier.php→ + KATEGORI, RETURAN, area_id, jurnal_id + HasFactory
  Mcustomer.php→ + 25 FoxPro columns + HasFactory + relasi arhs()
  Driver.php   → table name driver→drivers (sudah di src sebelumnya, tinggal align)

FILE YANG DIBUAT BARU (100% baru, tidak tersentuh ledger asli):
  18 migration files          (2026_10_13_*)
  11 model files              (Kendaraan, Expedisi, Price, dll)
  21 controller files         (ExpedisiController, CoolroomController, dll)
  17 view directories         (expedisi/, kendaraan/, coolroom/, dll)
  2 test files                (MigrationTest, ModelTest)
  1 seeder                    (TransaksiPermissionSeeder)
  1 helper                    (Helper.php - terbilang)