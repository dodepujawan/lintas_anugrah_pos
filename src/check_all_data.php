<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DATA MCUSTOMER ===\n";
$customers = \DB::table('mcustomer')->select('CUSTOMER', 'NAMACUST')->limit(10)->get();
echo "Total customers: " . \DB::table('mcustomer')->count() . "\n";
foreach ($customers as $c) {
    echo "  - {$c->CUSTOMER}: {$c->NAMACUST}\n";
}

echo "\n=== DATA ARH ===\n";
$invoices = \App\Models\Arh::select('NOFAKTUR', 'TGLFAKTUR', 'TGLJT', 'CUSTOMER', 'SALDO')->get();
echo "Total invoices: " . count($invoices) . "\n";
foreach ($invoices as $inv) {
    echo "  - {$inv->NOFAKTUR} ({$inv->CUSTOMER}): " . number_format($inv->SALDO, 0, ',', '.') . "\n";
}

echo "\n=== DATA BARH ===\n";
$payments = \App\Models\Barh::select('NOBAYAR', 'CUSTOMER', 'JUMLAH_BAYAR')->get();
echo "Total payments: " . count($payments) . "\n";
foreach ($payments as $p) {
    echo "  - {$p->NOBAYAR} ({$p->CUSTOMER}): " . number_format($p->JUMLAH_BAYAR, 0, ',', '.') . "\n";
}

echo "\n✅ Data check complete!\n";
