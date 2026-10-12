<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\User;
use App\Services\NeracaReportService;

// ensure test data state for the current app database
User::factory()->create([ 'is_admin' => true ]);
$header = Coa::create([
    'kode_akun' => '2000',
    'nama_akun' => 'Kewajiban',
    'tipe' => 'Kewajiban',
    'kategori' => 'Utang',
    'level' => 'Header',
    'saldo_normal' => 'Kredit',
    'is_active' => true,
]);
$subHeader = Coa::create([
    'kode_akun' => '2100',
    'nama_akun' => 'Utang Lancar',
    'tipe' => 'Kewajiban',
    'kategori' => 'Utang',
    'level' => 'Sub Header',
    'parent_id' => $header->id,
    'saldo_normal' => 'Kredit',
    'is_active' => true,
]);
$posting = Coa::create([
    'kode_akun' => '2110',
    'nama_akun' => 'Utang Dagang',
    'tipe' => 'Kewajiban',
    'kategori' => 'Utang',
    'level' => 'Akun Posting',
    'parent_id' => $subHeader->id,
    'saldo_normal' => 'Kredit',
    'is_active' => true,
]);
$zeroPosting = Coa::create([
    'kode_akun' => '2120',
    'nama_akun' => 'Utang Lainnya',
    'tipe' => 'Kewajiban',
    'kategori' => 'Utang',
    'level' => 'Akun Posting',
    'parent_id' => $subHeader->id,
    'saldo_normal' => 'Kredit',
    'is_active' => true,
]);
$user = User::first();
$jurnal = Jurnal::create([
    'no_jurnal' => 'JU-TEST-002',
    'tanggal' => now()->toDateString(),
    'status' => Jurnal::STATUS_POSTED,
    'keterangan' => 'Utang',
    'total_debit' => 0,
    'total_kredit' => 500000,
    'referensi' => 'REF-002',
    'created_by' => $user->id,
    'is_posted' => true,
]);
$jurnal->items()->create([
    'coa_id' => $posting->id,
    'deskripsi' => 'Pembelian',
    'debit' => 0,
    'kredit' => 500000,
]);
$service = new NeracaReportService();
$report = $service->build(['from_date' => now()->toDateString(), 'to_date' => now()->toDateString(), 'scope' => 'saldo', 'show_zero' => false, 'expand_all' => true, 'show_code' => true]);
file_put_contents('temp_debug_neraca.json', json_encode($report, JSON_PRETTY_PRINT));
echo "ok\n";
