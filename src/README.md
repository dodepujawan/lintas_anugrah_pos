# Akuntansi Laravel

Aplikasi akuntansi sederhana berbasis Laravel untuk manajemen CoA, jurnal, piutang, pelunasan, dan laporan keuangan.

## Fitur Utama

- Dashboard ringkasan akun dan jurnal
- Manajemen Chart of Accounts (COA)
- Jurnal Umum dengan posting dan void
- Saldo Awal Akun
- Jurnal Penyesuaian
- Piutang / faktur pelanggan
- Pelunasan Piutang
- Laporan: Jurnal Umum, COA, Buku Besar, Neraca, Neraca Saldo, Arus Kas, Laba Rugi, Tutup Buku
- Export Excel untuk laporan tertentu
- Pengaturan user, role, menu, dan hak akses

## Persyaratan

- PHP 8.1+ atau lebih baru
- Composer
- MySQL / MariaDB / PostgreSQL
- Node.js + npm (jika ingin build frontend)

## Instalasi

1. Clone repositori ke mesin lokal:

```bash
git clone <repository-url> akuntansi
cd akuntansi
```

2. Install dependensi PHP:

```bash
composer install
```

3. Install dependensi frontend (opsional):

```bash
npm install
```

4. Buat file `.env` dan atur konfigurasi database:

```bash
copy .env.example .env
php artisan key:generate
```

5. Sesuaikan pengaturan database di file `.env`.

## Setup Database

Untuk setup awal, jalankan:

```bash
php artisan app:fresh-db
```

Jika perintah custom tidak tersedia, gunakan:

```bash
php artisan migrate:fresh --seed
```

Perintah ini akan:

- menghapus semua tabel lama (fresh)
- menjalankan semua migration
- memasukkan data seeder awal

## Jalankan Aplikasi

```bash
php artisan serve
```

Lalu buka di browser:

```text
http://127.0.0.1:8000
```

## Login Default

- Email: `admin@akuntansi.com`
- Password: `password`

> Gunakan hanya untuk development. Ubah credential sebelum deployment.

## Struktur Navigasi Utama

### Dashboard

Menampilkan ringkasan:

- total akun CoA
- total jurnal
- jumlah jurnal yang diposting
- total aset, kewajiban, ekuitas
- jurnal terbaru
- grafik saldo akun

### COA (Chart of Accounts)

- List akun dalam struktur hirarki
- Tambah akun baru
- Edit akun
- Hapus akun (jika belum punya transaksi)
- Export daftar akun ke Excel

### Jurnal Umum

- Buat jurnal harian
- Input nomor jurnal otomatis `JU...`
- Masukkan baris debit dan kredit
- Posting jurnal langsung
- Simpan draft jurnal
- Void jurnal jika perlu

### Saldo Awal Akun

- Tambah saldo awal akun dengan nomor jurnal `SA...`
- Bisa menyimpan draft
- Post saldo awal menjadi transaksi resmi
- Export data saldo awal ke Excel

### Jurnal Penyesuaian

- Buat jurnal penyesuaian dengan nomor `JP...`
- Digunakan untuk penyesuaian akhir periode
- Simpan draft, lalu post
- Edit hanya untuk draft

### Piutang

- Tambah saldo awal piutang / faktur pelanggan
- Input data:
  - No Faktur
  - Tanggal Faktur
  - Tanggal Jatuh Tempo
  - Customer
  - Nominal
  - Deskripsi
- Sistem akan menyimpan saldo awal piutang dan status faktur

### Pelunasan Piutang

- Cari customer aktif
- Tampilkan faktur terbuka
- Masukkan nilai pembayaran untuk tiap faktur
- Sistem akan memperbarui nilai `SALDO`, `BAYAR`, dan status

### Laporan

Tersedia laporan:

- Jurnal Umum
- COA
- Buku Besar
- Neraca
- Neraca Saldo
- Arus Kas
- Laba Rugi
- Tutup Buku

Beberapa laporan bisa diexport ke Excel.

### Pengaturan (Settings)

- Manajemen User
- Manajemen Role
- Manajemen Menu
- Atur hak akses / permission per role

## Perintah Berguna

- `php artisan app:fresh-db` : reset database + seeder
- `php artisan migrate:fresh --seed` : reset database + seeder manual
- `php artisan migrate --seed` : migrasi baru + seed
- `php artisan db:seed` : hanya seed
- `php artisan test` : jalankan semua test

## Catatan Penting

- Jurnal yang sudah diposting tidak bisa dihapus
- Hanya draft yang bisa diedit untuk Saldo Awal dan Jurnal Penyesuaian
- Akun COA tidak bisa dihapus jika memiliki sub-akun atau transaksi
- Piutang dan pelunasan terhubung melalui tabel `arh`, `barh`, dan `bard`

## Alur Kerja Rekomendasi

1. Setup database dan jalankan seed
2. Login sebagai admin
3. Periksa struktur CoA, tambahkan akun baru jika perlu
4. Input Saldo Awal Akun
5. Buat Jurnal Umum untuk transaksi harian
6. Input Piutang / faktur dan lakukan Pelunasan Piutang
7. Lihat laporan Neraca, Laba Rugi, Buku Besar
8. Atur user, role, dan permission jika diperlukan

## Lisensi

Proyek ini menggunakan lisensi MIT.
