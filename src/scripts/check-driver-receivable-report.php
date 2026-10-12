<?php

use App\Http\Controllers\DriverReceivableReportController;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$controller = $app->make(DriverReceivableReportController::class);

$viewStarted = microtime(true);
$view = $controller->index(Request::create('/laporan/piutang-sopir', 'GET'));
echo 'view: OK, '.strlen($view->render()).' bytes, '.round((microtime(true) - $viewStarted) * 1000)."ms\n";

foreach (['standard' => [], 'dot_matrix' => ['print_format' => 'dot_matrix']] as $format => $query) {
    $started = microtime(true);
    $response = $controller->pdf(Request::create('/laporan/piutang-sopir/pdf', 'GET', $query));
    echo $format.': '.($response->getStatusCode() === 200 ? 'OK' : 'FAILED')
        .', '.strlen($response->getContent()).' bytes, '
        .round((microtime(true) - $started) * 1000)."ms\n";
}
