# Transparent Query System - Usage Guide

## Overview

Sistem query transparan memungkinkan aplikasi membaca data dari tabel aktif (`jurnal_items`) dan tabel arsip (`jurnal_items_archives`) secara otomatis tanpa perlu controller mengetahui implementasi detail.

**Keuntungan:**

- ✅ Query otomatis memilih tabel berdasarkan rentang tanggal
- ✅ Data di dalam 2 tahun terakhir: query lebih cepat (hanya dari `jurnal_items`)
- ✅ Data melampaui 2 tahun: query otomatis gabung dengan `jurnal_items_archives`
- ✅ Efisiensi: filter dilakukan sebelum UNION untuk performa optimal
- ✅ Transparent: Controller tidak perlu tahu bahwa archive tabel ada

---

## 1. Scopes di Model JurnalItem

### A. withArchived() - Scope untuk UNION Query

Digunakan ketika rentang tanggal melampaui 2 tahun terakhir.

```php
// Contoh 1: Query dengan gabungan arsip dan aktif
$items = JournalItem::withArchived('2020-01-01', '2024-12-31')
    ->where('coa_id', 5)
    ->get();

// Contoh 2: Langsung dengan sum aggregate
$sums = JournalItem::withArchived('2020-01-01', '2024-12-31')
    ->where('coa_id', 5)
    ->selectRaw('SUM(CAST(debit AS DECIMAL(15,2))) as total_debit')
    ->first();
```

**Catatan:** Scope ini melakukan UNION internal dan menggunakan `fromSub()` untuk efisiensi.

---

### B. byCoaIds() - Filter Akun

Filter untuk one atau multiple akun IDs.

```php
// Single akun
$items = JournalItem::withArchived('2020-01-01', '2024-12-31')
    ->byCoaIds(5)
    ->get();

// Multiple akun
$items = JournalItem::withArchived('2020-01-01', '2024-12-31')
    ->byCoaIds([1, 5, 10])
    ->get();
```

---

### C. activeOnly() - Hanya Data Aktif

Filter untuk hanya mengambil data dalam 2 tahun terakhir (tidak mengakses arsip).

```php
// Untuk performa maksimal ketika Anda yakin data masih aktif
$items = JournalItem::activeOnly()
    ->where('coa_id', 5)
    ->get();
```

---

## 2. JournalQueryService - Smart Query Helper

Layanan yang mengotomatisasi pemilihan query berdasarkan rentang tanggal.

### A. getSmartQuery() - Pilihan Otomatis

Menentukan apakah menggunakan query aktif saja atau gabung dengan arsip.

```php
// Inject service di controller
public function __construct(private readonly JournalQueryService $journalQueryService) {}

// Gunakan di method
public function myReport(Request $request)
{
    $startDate = '2024-01-01';
    $endDate = '2024-12-31';

    // Service otomatis memilih query terbaik
    $query = $this->journalQueryService->getSmartQuery($startDate, $endDate);

    $items = $query->where('coa_id', 5)->get();
}
```

**Logika Keputusan:**

- Jika `endDate` >= (hari ini - 2 tahun): gunakan query aktif saja ✅
- Jika `endDate` < (hari ini - 2 tahun): gunakan UNION dengan arsip 📦

---

### B. getJournalsByCoaIds() - Query dengan Filter Akun

Contoh penggunaan lengkap.

```php
// Single akun
$items = $this->journalQueryService->getJournalsByCoaIds(
    '2024-01-01',
    '2024-12-31',
    5  // coa_id
);

// Multiple akun
$items = $this->journalQueryService->getJournalsByCoaIds(
    '2024-01-01',
    '2024-12-31',
    [1, 5, 10]
);
```

---

### C. getSumByCoaId() - Total Debit/Kredit Single Akun

Berguna untuk perhitungan saldo atau laporan.

```php
$sums = $this->journalQueryService->getSumByCoaId(
    '2024-01-01',
    '2024-12-31',
    5  // coa_id
);

// Result
// [
//     'debit' => 50000000.00,
//     'kredit' => 30000000.00
// ]

$balance = $sums['debit'] - $sums['kredit'];  // 20000000.00
```

---

### D. getSumByMultipleCoaIds() - Total untuk Multiple Akun

Optimasi untuk mendapatkan saldo beberapa akun sekaligus.

```php
$coaIds = [1, 5, 10, 15];
$sums = $this->journalQueryService->getSumByMultipleCoaIds(
    '2024-01-01',
    '2024-12-31',
    $coaIds
);

// Result
// [
//     1 => ['debit' => 100.00, 'kredit' => 50.00],
//     5 => ['debit' => 200.00, 'kredit' => 100.00],
//     10 => ['debit' => 300.00, 'kredit' => 150.00],
//     15 => ['debit' => 400.00, 'kredit' => 200.00],
// ]

foreach ($sums as $coaId => $totals) {
    $balance = $totals['debit'] - $totals['kredit'];
    // Use balance...
}
```

---

### E. getQueryInfo() - Debug & Monitoring

Lihat detail tentang query yang akan dijalankan.

```php
$info = $this->journalQueryService->getQueryInfo('2020-01-01', '2024-12-31');

// Result
// [
//     'start_date' => '2020-01-01',
//     'end_date' => '2024-12-31',
//     'is_within_active_range' => false,
//     'tables_used' => ['jurnal_items', 'jurnal_items_archives'],
//     'active_data_threshold_years' => 2,
//     'cutoff_date' => '2024-12-07',  // hari ini - 2 tahun
// ]
```

**Gunakan untuk:**

- Logging performa (tahu query mana yang heavy)
- Testing (verify query path yang dipilih)
- Debugging (track mana tabel yang diakses)

---

## 3. Contoh Real-World Implementation

### A. Buku Besar Report (dengan Smart Query)

```php
class LaporanController extends Controller
{
    public function __construct(
        private readonly JournalQueryService $journalQueryService
    ) {}

    public function bukuBesar(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

        // Smart query selection (automatic)
        $query = $this->journalQueryService->getSmartQuery($tanggalAwal, $tanggalAkhir)
            ->where('coa_id', $request->coa_id)
            ->with('jurnal', 'coa')
            ->orderBy('created_at');

        $items = $query->get();
        $totalDebit = $items->sum('debit');
        $totalKredit = $items->sum('kredit');

        return view('laporan.buku_besar', compact('items', 'totalDebit', 'totalKredit'));
    }
}
```

---

### B. Neraca/Balance Sheet (Multi-Akun)

```php
public function neraca(Request $request)
{
    $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
    $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

    // Get multiple account balances in one query
    $assetCoaIds = [1, 2, 3, 4];
    $liabilityCoaIds = [10, 11, 12];

    $assetSums = $this->journalQueryService->getSumByMultipleCoaIds(
        $tanggalAwal,
        $tanggalAkhir,
        $assetCoaIds
    );

    $liabilitySums = $this->journalQueryService->getSumByMultipleCoaIds(
        $tanggalAwal,
        $tanggalAkhir,
        $liabilityCoaIds
    );

    // Calculate balances
    $totalAssets = collect($assetSums)->sum(fn($s) => $s['debit'] - $s['kredit']);
    $totalLiabilities = collect($liabilitySums)->sum(fn($s) => $s['kredit'] - $s['debit']);

    return view('laporan.neraca', compact('totalAssets', 'totalLiabilities'));
}
```

---

### C. Custom Multi-Table Join

```php
public function laporanCustom(Request $request)
{
    $startDate = '2022-01-01';
    $endDate = '2024-12-31';

    // Get items dengan join ke Jurnal dan Coa
    $items = $this->journalQueryService->getJournalsByCoaIds(
        $startDate,
        $endDate,
        [1, 5, 10]
    );

    // Items sudah include jurnal dan coa (from 'with')
    foreach ($items as $item) {
        $jurnalDate = $item->jurnal->tanggal;
        $coaName = $item->coa->nama;
        $debit = $item->debit;
    }
}
```

---

## 4. Performance Considerations

### A. Query Path Selection

```
Data Range              Query Path              Tables Used         Performance
─────────────────────────────────────────────────────────────────────────────
Today - 2 years    →    Active Only            jurnal_items        ⚡⚡⚡ Fast
2-3 years ago      →    Active Only            jurnal_items        ⚡⚡ Medium
Before 3 years     →    UNION                  Both tables         ⚡ Slower
```

### B. Index Optimization

Pastikan indeks ada di database untuk performa optimal:

```sql
-- Pada tabel jurnal_items
ALTER TABLE jurnal_items ADD INDEX idx_coa_id (coa_id);
ALTER TABLE jurnal_items ADD INDEX idx_jurnal_id (jurnal_id);

-- Pada tabel jurnal_items_archives
ALTER TABLE jurnal_items_archives ADD INDEX idx_coa_id (coa_id);
ALTER TABLE jurnal_items_archives ADD INDEX idx_jurnal_id (jurnal_id);

-- Pada tabel jurnals (untuk whereHas)
ALTER TABLE jurnals ADD INDEX idx_tanggal (tanggal);
ALTER TABLE jurnals ADD INDEX idx_is_posted (is_posted);
```

### C. Query Explanation

Gunakan `EXPLAIN` untuk menganalisis query:

```php
$query = $this->journalQueryService->getSmartQuery('2024-01-01', '2024-12-31')
    ->where('coa_id', 5);

// Lihat query plan
dd($query->toSql(), $query->getBindings());

// Atau jalankan EXPLAIN di SQL client
// EXPLAIN FORMAT=JSON SELECT ...
```

---

## 5. Migration Guide (untuk existing controllers)

### Before (Manual Archive Handling):

```php
public function bukuBesar(Request $request)
{
    $items = JournalItem::where('coa_id', $request->coa_id)->get();
    // Harus manually check arsip
}
```

### After (Transparent Query):

```php
public function bukuBesar(Request $request)
{
    $items = $this->journalQueryService->getJournalsByCoaIds(
        $request->start_date,
        $request->end_date,
        $request->coa_id
    );
    // Archive check automatic!
}
```

---

## 6. Testing

### Unit Test Example:

```php
public function test_smart_query_uses_active_only_within_2_years()
{
    $service = new JournalQueryService();

    $startDate = now()->subMonths(6)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    $info = $service->getQueryInfo($startDate, $endDate);

    $this->assertTrue($info['is_within_active_range']);
    $this->assertEquals(['jurnal_items'], $info['tables_used']);
}

public function test_smart_query_uses_union_beyond_2_years()
{
    $service = new JournalQueryService();

    $startDate = '2020-01-01';
    $endDate = now()->format('Y-m-d');

    $info = $service->getQueryInfo($startDate, $endDate);

    $this->assertFalse($info['is_within_active_range']);
    $this->assertEquals(['jurnal_items', 'jurnal_items_archives'], $info['tables_used']);
}
```

---

## 7. Summary

| Component                  | Purpose                          | Usage                               |
| -------------------------- | -------------------------------- | ----------------------------------- |
| `withArchived()`           | UNION scope untuk rentang custom | Direct scope untuk advanced queries |
| `byCoaIds()`               | Filter by account IDs            | Combine dengan scopes lain          |
| `activeOnly()`             | Hanya data 2 tahun terakhir      | Performa maksimal untuk data recent |
| `getSmartQuery()`          | Auto-select best query path      | Primary method untuk reports        |
| `getJournalsByCoaIds()`    | Pre-formatted query dengan joins | Untuk quick implementation          |
| `getSumByCoaId()`          | Single account balance           | Neraca, Laba-Rugi calculations      |
| `getSumByMultipleCoaIds()` | Multiple account balances        | Batch calculations                  |
| `getQueryInfo()`           | Debug & monitoring info          | Logging dan testing                 |

**Next Steps:**

1. Inject `JournalQueryService` di semua report controllers
2. Update method laporan untuk menggunakan service
3. Test performa dengan data historis
4. Monitor query logs untuk verify correct table selection
