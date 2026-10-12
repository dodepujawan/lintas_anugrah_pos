<?php

use App\Http\Controllers\LaporanPiutangController;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$userId = (int) ($argv[1] ?? 2);
$section = (string) ($argv[2] ?? 'invoice');
$formatArgument = (string) ($argv[3] ?? 'all');
$outputPath = $argv[4] ?? null;
$agingDetailArgument = $argv[5] ?? null;
$allowedSections = ['invoice', 'pelanggan', 'aging', 'mutasi', 'penambahan', 'pembayaran'];

if (! in_array($section, $allowedSections, true)) {
    fwrite(STDERR, "Bagian laporan tidak valid: {$section}.\n");
    exit(1);
}

if (! Auth::loginUsingId($userId)) {
    fwrite(STDERR, "User ID {$userId} tidak ditemukan.\n");
    exit(1);
}

echo 'invoice_database=' . \App\Models\Arh::count() . PHP_EOL;
echo 'payment_database=' . \App\Models\Barh::count() . PHP_EOL;

if ($formatArgument === 'view') {
    $parameters = ['section' => $section];
    if ($agingDetailArgument === 'all') $parameters['aging_detail'] = 'all';
    $view = $app->make(LaporanPiutangController::class)->index(Request::create('/laporan/piutang', 'GET', $parameters));
    echo 'view: OK ('.strlen($view->render())." bytes)\n";
    exit(0);
}

$formats = $formatArgument === 'all' ? ['standard', 'dot_matrix'] : [$formatArgument];

foreach ($formats as $format) {
    $parameters = ['section' => $section];
    if ($agingDetailArgument === 'all') $parameters['aging_detail'] = 'all';
    if ($format === 'dot_matrix') {
        $parameters['print_format'] = 'dot_matrix';
    }

    try {
        $request = Request::create('/laporan/piutang/pdf', 'GET', $parameters);
        $response = $app->make(LaporanPiutangController::class)->pdf($request);
        $content = $response->getContent();
        if ($outputPath && $outputPath !== '-' && count($formats) === 1) {
            file_put_contents($outputPath, $content);
        }
        echo $format.': OK ('.strlen($content).' bytes, data='.
            $response->headers->get('X-Piutang-Data-Ms').'ms, pdf='.
            $response->headers->get('X-Piutang-Pdf-Ms')."ms)\n";
    } catch (Throwable $exception) {
        fwrite(STDERR, $format.': '.$exception::class.' - '.$exception->getMessage()."\n");
        fwrite(STDERR, $exception->getTraceAsString()."\n");
        exit(1);
    }
}
