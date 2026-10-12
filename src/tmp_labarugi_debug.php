<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\User;
use App\Services\LabaRugiReportService;
use Illuminate\Support\Facades\DB;

$user = User::factory()->create();
$random = rand(1000, 9999);
$revenue = Coa::create([
    'kode_akun' => '41' . $random,
    'nama_akun' => 'Pendapatan Jasa',
    'tipe' => 'Pendapatan',
    'kategori' => 'Pendapatan',
    'level' => 'Akun Posting',
    'saldo_normal' => 'Kredit',
    'is_active' => true,
]);

$expense = Coa::create([
    'kode_akun' => '5101',
    'nama_akun' => 'Beban Operasional',
    'tipe' => 'Beban',
    'kategori' => 'Beban Operasional',
    'level' => 'Akun Posting',
    'saldo_normal' => 'Debit',
    'is_active' => true,
]);

$tax = Coa::create([
    'kode_akun' => '5201',
    'nama_akun' => 'Pajak Penghasilan',
    'tipe' => 'Beban',
    'kategori' => 'Pajak Penghasilan',
    'level' => 'Akun Posting',
    'saldo_normal' => 'Debit',
    'is_active' => true,
]);

$jurnal = Jurnal::create([
    'no_jurnal' => 'JU-TEST-001',
    'tanggal' => now()->toDateString(),
    'status' => Jurnal::STATUS_POSTED,
    'keterangan' => 'Transaksi laba rugi',
    'total_debit' => 1000,
    'total_kredit' => 1000,
    'referensi' => 'REF-001',
    'created_by' => $user->id,
    'is_posted' => true,
]);

$jurnal->items()->create([
    'coa_id' => $revenue->id,
    'deskripsi' => 'Pendapatan',
    'debit' => 0,
    'kredit' => 1000,
]);

DB::enableQueryLog();

$saldo = JurnalItem::where('coa_id', $revenue->id)
    ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
        ->whereBetween('tanggal', [now()->toDateString(), now()->toDateString()]))
    ->sum('kredit');

$queries = DB::getQueryLog();

echo "saldo query result: {$saldo}\n";
echo "queries: \n";
print_r($queries);

$report = (new LabaRugiReportService())->build(now()->toDateString(), now()->toDateString());
echo "TotalPendapatan: " . $report['totalPendapatan'] . PHP_EOL;
echo "Pendapatan count: " . Coa::active()->posting()->byTipe('Pendapatan')->count() . PHP_EOL;
echo "Pendapatan IDs: " . implode(',', Coa::active()->posting()->byTipe('Pendapatan')->pluck('id')->all()) . PHP_EOL;
print_r($report['pendapatans']->toArray());
