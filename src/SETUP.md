# Setup & Migration Guide

## 🚀 Inisialisasi Database Pertama Kali

### 🎯 Cara Mudah (Recommended):

```bash
php artisan app:fresh-db
```

### 📝 Cara Manual (kalau perlu):

```bash
php artisan migrate:fresh --seed
```

Atau gunakan helper script:

```bash
bash fresh-migrate.sh
```

## 📋 Seeder yang Tersedia

1. **UserSeeder** - Membuat admin user
    - Email: `admin@akuntansi.com`
    - Password: `password`

2. **CoaSeeder** - Membuat default Chart of Accounts

3. **McustomerSeeder** - Membuat 8 customer dummy untuk testing

## 🔑 User & Migration Commands

**Sekarang user akan otomatis dibuat via `UserSeeder`!** Tidak perlu khawatir user hilang setelah migration.

### ✅ Command yang Tersedia:

```bash
# RECOMMENDED: Gunakan custom command (auto --seed)
php artisan app:fresh-db

# ATAU: Reset database manual dengan flag --seed
php artisan migrate:fresh --seed

# ATAU: Tambah migration baru + seed
php artisan migrate --seed

# ATAU: Hanya seed (kalau database sudah exist)
php artisan db:seed
```

### ℹ️ Penjelasan:

- `app:fresh-db` → Reset database + auto-seed (User, CoA, Customer) ⭐ Recommended
- `migrate:fresh --seed` → Reset database + auto-seed (manual)
- `migrate --seed` → Jalankan migration baru + seed
- `db:seed` → Hanya seed (database sudah ada)
- `migrate` → JANGAN gunakan tanpa `--seed`

## 📊 Database Structure

### Tables Utama:

- **users** - User authentication
- **arh** - Piutang/Faktur Penjualan (Accounts Receivable Header)
- **barh** - Header Pembayaran (Accounts Receivable Payment Header)
- **bard** - Detail Pembayaran (Accounts Receivable Payment Detail)
- **mcustomer** - Master Customer
- **coas** - Chart of Accounts
- **jurnals** - Jurnal Umum
- **jurnal_items** - Item Jurnal

## 🧪 Running Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=PiutangPaymentTest
```

## 📝 Credential untuk Login

- **Email:** admin@akuntansi.com
- **Password:** password

⚠️ JANGAN gunakan credential ini di production!
