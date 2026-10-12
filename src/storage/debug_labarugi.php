<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\LabaRugiReportService;
use App\Services\TutupBukuReportService;
use App\Models\AccountMapping;

$tanggal = date('Y-m-d');
$lr = (new LabaRugiReportService())->build($tanggal, $tanggal);
$tb = (new TutupBukuReportService())->build($tanggal, $tanggal);

echo "Laporan Laba Rugi untuk $tanggal\n";
echo "totalPendapatan={$lr['totalPendapatan']} totalBeban={$lr['totalBeban']} labaBersih={$lr['labaBersih']}\n";

echo "Tutup Buku mapping:\n";
foreach (AccountMapping::mappingKeys() as $k => $label) {
    $m = AccountMapping::where('mapping_key', $k)->with('account')->first();
    $acc = $m?->account;
    echo "- $k ($label): " . ($acc ? $acc->kode_akun . ' - ' . $acc->nama_akun : 'null') . "\n";
}

echo "Tutup Buku report saldoAwalLabaDitahan={$tb['saldoAwalLabaDitahan']} proyeksi={$tb['saldoAkhirLabaDitahanProyeksi']}\n";
