<?php

use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanHutangController;
use App\Models\Coa;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! Auth::loginUsingId((int) ($argv[1] ?? 2))) {
    fwrite(STDERR, "User pengujian tidak ditemukan.\n");
    exit(1);
}

$tests = [];
$longPeriod = ['tanggal_awal' => '2018-01-01', 'tanggal_akhir' => now()->format('Y-m-d')];
foreach (['standard', 'dot_matrix'] as $format) {
    $print = $format === 'dot_matrix' ? ['print_format' => 'dot_matrix'] : [];
    $tests["hutang-{$format}"] = fn () => $app->make(LaporanHutangController::class)
        ->pdf(Request::create('/laporan/hutang/pdf', 'GET', $print));
    $tests["jurnal-{$format}"] = fn () => $app->make(LaporanController::class)
        ->streamReportPdf(Request::create('/laporan/jurnal-umum/pdf', 'GET', $longPeriod + $print), 'jurnal-umum');
}

$coa = Coa::active()->posting()->whereHas('jurnalItems')->first() ?? Coa::active()->posting()->first();
if ($coa) {
    foreach (['standard', 'dot_matrix'] as $format) {
        $parameters = ['coa_id' => $coa->id] + $longPeriod;
        if ($format === 'dot_matrix') $parameters['print_format'] = 'dot_matrix';
        $tests["buku-besar-{$format}"] = fn () => $app->make(LaporanController::class)
            ->exportBukuBesarPdf(Request::create('/laporan/buku-besar/pdf', 'GET', $parameters));
    }
}

foreach ($tests as $name => $test) {
    $startedAt = microtime(true);
    try {
        $response = $test();
        echo $name.': OK, '.strlen($response->getContent()).' bytes, '.round((microtime(true) - $startedAt) * 1000).'ms'.PHP_EOL;
    } catch (Throwable $exception) {
        fwrite(STDERR, $name.': '.$exception::class.' - '.$exception->getMessage().PHP_EOL);
        exit(1);
    }
}
