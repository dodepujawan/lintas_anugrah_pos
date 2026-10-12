<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeracaReportService;
use Illuminate\Support\Facades\DB;

$service = new NeracaReportService();

$dates = [
    ['month' => 7, 'year' => 2026],
    ['month' => 8, 'year' => 2026],
];

$out = [];
foreach ($dates as $d) {
    $from = \Carbon\Carbon::createFromDate($d['year'], $d['month'], 1)->format('Y-m-d');
    $to = \Carbon\Carbon::createFromDate($d['year'], $d['month'], 1)->endOfMonth()->format('Y-m-d');
    $report = $service->build(['from_date' => $from, 'to_date' => $to, 'scope' => 'saldo', 'show_zero' => false]);

    $out[] = [
        'month' => $d['month'],
        'year' => $d['year'],
        'from' => $from,
        'to' => $to,
        'total_asset' => $report['total_asset'],
        'total_kewajiban' => $report['total_kewajiban'],
        'total_ekuitas' => $report['total_ekuitas'],
        'laba_berjalan' => $report['laba_berjalan'],
        'is_balance' => $report['is_balance'],
        'flat_rows_count' => count($report['flat_rows']),
    ];
}

file_put_contents(__DIR__ . '/debug_neraca_jul_aug.json', json_encode($out, JSON_PRETTY_PRINT));
print_r($out);
