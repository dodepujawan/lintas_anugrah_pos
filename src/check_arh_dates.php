<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$arh = \App\Models\Arh::first();
if ($arh) {
    echo "✅ Arh ditemukan:\n";
    echo "NOFAKTUR: " . $arh->NOFAKTUR . "\n";
    echo "TGLFAKTUR: " . ($arh->TGLFAKTUR ? $arh->TGLFAKTUR->format('d/m/Y') : 'null') . "\n";
    echo "TGLJT: " . ($arh->TGLJT ? $arh->TGLJT->format('d/m/Y') : 'null') . "\n";
    echo "SALDO: " . $arh->SALDO . "\n";
} else {
    echo "❌ Tidak ada data Arh\n";
}
