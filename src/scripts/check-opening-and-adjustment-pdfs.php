<?php

use App\Http\Controllers\HutangController;
use App\Http\Controllers\JurnalPenyesuaianController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\SaldoAwalController;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$reports = [
    'neraca-saldo' => fn (Request $request) => $app->make(LaporanController::class)->streamReportPdf($request, 'neraca-saldo'),
    'jurnal-penyesuaian' => fn (Request $request) => $app->make(JurnalPenyesuaianController::class)->pdf($request),
    'saldo-awal-piutang' => fn (Request $request) => $app->make(PiutangController::class)->pdf($request),
    'saldo-awal-hutang' => fn (Request $request) => $app->make(HutangController::class)->pdfSaldoAwal($request),
    'saldo-awal-akun' => fn (Request $request) => $app->make(SaldoAwalController::class)->pdf($request),
];

foreach ($reports as $name => $callback) {
    foreach (['standard' => [], 'dot_matrix' => ['print_format' => 'dot_matrix']] as $format => $query) {
        $started = microtime(true);
        $response = $callback(Request::create('/check', 'GET', $query));
        echo $name.'-'.$format.': '.($response->getStatusCode() === 200 ? 'OK' : 'FAILED')
            .', '.strlen($response->getContent()).' bytes, '.round((microtime(true) - $started) * 1000)."ms\n";
    }
}

$openingJournal = App\Models\Jurnal::query()->where('no_jurnal', 'like', 'SA%')->first();
if ($openingJournal) {
    foreach (['standard' => [], 'dot_matrix' => ['print_format' => 'dot_matrix']] as $format => $query) {
        $started = microtime(true);
        $response = $app->make(SaldoAwalController::class)->pdfDetail(Request::create('/check', 'GET', $query), $openingJournal);
        echo 'detail-saldo-awal-'.$format.': '.($response->getStatusCode() === 200 ? 'OK' : 'FAILED')
            .', '.strlen($response->getContent()).' bytes, '.round((microtime(true) - $started) * 1000)."ms\n";
    }
} else {
    echo "detail-saldo-awal: SKIPPED (data lokal belum tersedia)\n";
}
