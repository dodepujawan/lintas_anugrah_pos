# Archive Journal Command - Dokumentasi

## Overview

Fitur ini menyediakan sistem otomatis untuk mengarsipkan data jurnal tahunan ke tabel `jurnal_items_archives` setelah proses "Tutup Buku" selesai dilakukan.

---

## Commands yang Tersedia

### 1. `php artisan app:mark-year-closed {year}`

Mencatat bahwa proses tutup buku untuk tahun tertentu telah selesai dilakukan.

**Parameter:**

- `year` (required): Tahun dalam format YYYY (contoh: 2025)

**Contoh Penggunaan:**

```bash
php artisan app:mark-year-closed 2025
```

**Output Contoh:**

```
✅ Tutup buku untuk tahun 2025 berhasil dicatat.
Waktu: 2026-07-06 12:29:41

Sekarang Anda bisa menjalankan: php artisan app:archive-journal 2025
```

---

### 2. `php artisan app:archive-journal {year}`

Memindahkan semua jurnal items dari tabel `jurnal_items` ke `jurnal_items_archives` untuk tahun yang ditentukan.

**Parameter:**

- `year` (required): Tahun dalam format YYYY (contoh: 2025)

**Prasyarat:**

- Tutup buku untuk tahun tersebut harus sudah dicatat menggunakan `app:mark-year-closed`
- Semua jurnal untuk tahun tersebut harus dalam status "Posted" (`is_posted = true`)

**Contoh Penggunaan:**

```bash
php artisan app:archive-journal 2025
```

**Output Contoh - Sukses:**

```
Memulai arsip jurnal untuk tahun 2025...
1500 baris jurnal berhasil diarsipkan.
Proses arsip jurnal selesai.
```

**Output Contoh - Gagal (Tutup Buku belum dicatat):**

```
Tidak ada catatan penutupan untuk tahun 2025.
Periksa apakah Tutup Buku sudah dijalankan dan dicatat dengan benar.
```

---

## Database Schema

### Tabel: `jurnal_items_archives`

Menyimpan salinan data jurnal items dari tahun-tahun sebelumnya.

| Kolom        | Tipe          | Keterangan                     |
| ------------ | ------------- | ------------------------------ |
| `id`         | BIGINT        | Primary Key                    |
| `jurnal_id`  | BIGINT (FK)   | Foreign Key ke tabel `jurnals` |
| `coa_id`     | BIGINT (FK)   | Foreign Key ke tabel `coas`    |
| `deskripsi`  | TEXT          | Deskripsi transaksi            |
| `debit`      | DECIMAL(15,2) | Nilai debit                    |
| `kredit`     | DECIMAL(15,2) | Nilai kredit                   |
| `created_at` | TIMESTAMP     | Timestamp pembuatan            |
| `updated_at` | TIMESTAMP     | Timestamp update terakhir      |

### Tabel: `year_closings`

Mencatat kapan tutup buku dilakukan untuk setiap tahun.

| Kolom        | Tipe       | Keterangan                       |
| ------------ | ---------- | -------------------------------- |
| `id`         | BIGINT     | Primary Key                      |
| `year`       | VARCHAR(4) | Tahun (YYYY) - UNIQUE            |
| `closed_at`  | TIMESTAMP  | Tanggal & jam tutup buku dicatat |
| `notes`      | TEXT       | Catatan tambahan (opsional)      |
| `created_at` | TIMESTAMP  | Timestamp pembuatan record       |
| `updated_at` | TIMESTAMP  | Timestamp update terakhir        |

---

## Workflow Tutup Buku dan Arsip

### Langkah-langkah Rekomendasi:

1. **Admin melakukan Tutup Buku di aplikasi**
    - Buka halaman `Laporan → Tutup Buku`
    - Pilih periode tahun yang diinginkan
    - Verifikasi data pendapatan dan beban

2. **Catat Tutup Buku Selesai**

    ```bash
    php artisan app:mark-year-closed 2025
    ```

3. **Arsipkan Data Jurnal**

    ```bash
    php artisan app:archive-journal 2025
    ```

    - Command ini akan memindahkan semua jurnal items tahun 2025 ke tabel archive
    - Data original di `jurnal_items` akan dihapus setelah berhasil dipindahkan

4. **Verifikasi Arsip**
    - Cek tabel `jurnal_items_archives` untuk memastikan data sudah tersimpan
    - Pastikan `jurnal_items` tidak lagi memiliki record untuk tahun 2025

---

## Model yang Digunakan

### Model: `YearClosing`

```php
use App\Models\YearClosing;

// Cek apakah tahun sudah ditutup
$yearClosing = YearClosing::where('year', '2025')->first();
if ($yearClosing && $yearClosing->closed_at) {
    echo "Tahun 2025 sudah ditutup pada: " . $yearClosing->closed_at;
}

// Catat tutup buku baru
YearClosing::create([
    'year' => '2025',
    'closed_at' => now(),
    'notes' => 'Tutup buku manual via API'
]);
```

### Model: `JurnalItemArchive`

```php
use App\Models\JurnalItemArchive;

// Query data archive
$archived = JurnalItemArchive::whereYear('created_at', 2025)
    ->with('jurnal', 'coa')
    ->get();
```

---

## Integrasi dengan Controller (Opsional)

Jika ingin membuat UI untuk menjalankan command ini, tambahkan route dan controller:

```php
// routes/web.php
Route::post('/admin/tutup-buku/mark-closed', [TutupBukuController::class, 'markClosed'])
    ->name('tutup-buku.mark-closed')
    ->middleware(['auth', 'verified']);

Route::post('/admin/tutup-buku/archive', [TutupBukuController::class, 'archiveJournal'])
    ->name('tutup-buku.archive')
    ->middleware(['auth', 'verified']);
```

```php
// app/Http/Controllers/TutupBukuController.php
use Illuminate\Support\Facades\Artisan;

class TutupBukuController extends Controller
{
    public function markClosed(Request $request)
    {
        $year = $request->input('year');

        try {
            Artisan::call('app:mark-year-closed', ['year' => $year]);
            return redirect()->back()->with('success',
                "Tutup buku tahun {$year} berhasil dicatat.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function archiveJournal(Request $request)
    {
        $year = $request->input('year');

        try {
            Artisan::call('app:archive-journal', ['year' => $year]);
            return redirect()->back()->with('success',
                "Data jurnal tahun {$year} berhasil diarsipkan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
```

---

## Error Handling

| Error                          | Penyebab                                | Solusi                                          |
| ------------------------------ | --------------------------------------- | ----------------------------------------------- |
| "Tahun tidak valid"            | Format tahun bukan YYYY                 | Gunakan format 4 digit: `2025`                  |
| "Tidak ada catatan penutupan"  | Tutup buku belum dicatat                | Jalankan `app:mark-year-closed` terlebih dahulu |
| "Tidak ada jurnal yang sesuai" | Tidak ada jurnal posted untuk tahun itu | Pastikan ada transaksi yang sudah diposting     |

---

## Keamanan & Best Practices

1. **Backup Database**
    - Selalu backup database sebelum menjalankan arsip command
    - Data di `jurnal_items` akan dihapus permanent setelah arsip

2. **Hanya Archive Setelah Verifikasi**
    - Pastikan Tutup Buku sudah final dan verifikasi sebelum archive
    - Command ini irreversible kecuali dari backup

3. **Batch Processing**
    - Untuk database besar, pertimbangkan untuk menggunakan queue/job
    - Update command ini untuk menggunakan chunking jika perlu

4. **Audit Trail**
    - Catat siapa yang menjalankan command ini
    - Pertimbangkan menambah kolom `archived_by` dan `archived_at` di model

---

## Files yang Dibuat/Dimodifikasi

- ✅ `database/migrations/2026_07_06_000002_create_jurnal_items_archives_table.php`
- ✅ `database/migrations/2026_07_06_000003_create_year_closings_table.php`
- ✅ `app/Models/JurnalItemArchive.php`
- ✅ `app/Models/YearClosing.php`
- ✅ `app/Console/Commands/ArchiveJournal.php`
- ✅ `app/Console/Commands/MarkYearClosed.php`
- ✅ `routes/console.php` (updated)

---

## Testing

Untuk test command secara manual:

```bash
# 1. Catat tutup buku tahun 2025
php artisan app:mark-year-closed 2025

# 2. Archive jurnal tahun 2025 (jika ada data)
php artisan app:archive-journal 2025

# 3. Verifikasi data di database
php artisan tinker
>>> App\Models\YearClosing::all();
>>> App\Models\JurnalItemArchive::count();
```

---
