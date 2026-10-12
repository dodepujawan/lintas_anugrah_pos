<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Jurnal;

$no = 'JU-TEST-001';
$j = Jurnal::where('no_jurnal', $no)->with('items.coa')->first();
if (! $j) {
    echo "Jurnal $no not found\n";
    exit(0);
}

echo "Jurnal: {$j->no_jurnal} tanggal={$j->tanggal}\n";
foreach ($j->items as $it) {
    $kode = $it->coa?->kode_akun ?? 'null';
    $nama = $it->coa?->nama_akun ?? 'null';
    echo "item: coa_id={$it->coa_id} kode={$kode} nama={$nama} debit={$it->debit} kredit={$it->kredit}\n";
}
echo "Totals: debit={$j->total_debit} kredit={$j->total_kredit}\n";
