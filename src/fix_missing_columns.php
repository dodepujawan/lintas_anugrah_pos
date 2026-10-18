<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Driver: " . DB::getDriverName() . "\n";

if (DB::getDriverName() === 'mysql') {
    DB::statement("SET SESSION sql_mode = ''");
}

// Define all columns to add
$priceCols = [
    'KODE'        => 'VARCHAR(50) NULL AFTER KODECUS',
    'DARI'        => 'DECIMAL(10,0) DEFAULT 0 AFTER TANGGAL',
    'SAMPAI'      => 'DECIMAL(10,0) DEFAULT 0 AFTER DARI',
    'RUTE'        => 'VARCHAR(30) NULL AFTER SAMPAI',
    'KETERANGAN'  => 'VARCHAR(50) NULL AFTER RUTE',
    'HV'          => 'DECIMAL(10,0) DEFAULT 0 AFTER KETERANGAN',
    'HKG'         => 'DECIMAL(10,0) DEFAULT 0 AFTER HV',
    'HBOK'        => 'DECIMAL(10,0) DEFAULT 0 AFTER HKG',
    'KUNCI'       => 'VARCHAR(100) NULL AFTER HBOK',
    'HG'          => 'DECIMAL(1,0) DEFAULT 0 AFTER KUNCI',
    'JENIS'       => 'VARCHAR(1) NULL AFTER HG',
];

$dginginCols = [
    'KODEDGN' => 'VARCHAR(20) NULL AFTER id',
    'KODE'    => 'VARCHAR(20) NULL AFTER KODEDGN',
    'PERIODE' => 'VARCHAR(50) NULL AFTER KODE',
    'PLAT'    => 'VARCHAR(50) NULL AFTER PERIODE',
    'JENIS'   => 'VARCHAR(50) NULL AFTER PLAT',
    'ITEM'    => 'VARCHAR(100) NULL AFTER JENIS',
    'KUNCI'   => 'VARCHAR(100) NULL AFTER ITEM',
];

function cleanDates($table) {
    if (Schema::hasTable($table)) {
        DB::statement("UPDATE `$table` SET `updated_at` = NULL WHERE `updated_at` = '0000-00-00 00:00:00'");
        DB::statement("UPDATE `$table` SET `created_at` = NULL WHERE `created_at` = '0000-00-00 00:00:00'");
        if (Schema::hasColumn($table, 'TANGGAL')) {
            DB::statement("UPDATE `$table` SET `TANGGAL` = NULL WHERE `TANGGAL` REGEXP '^[0-9]$'");
        }
    }
}

function widenTanggal($table) {
    if (Schema::hasColumn($table, 'TANGGAL')) {
        try {
            DB::statement("ALTER TABLE `$table` MODIFY COLUMN `TANGGAL` VARCHAR(10) NULL");
            echo "  TANGGAL widened to VARCHAR(10)\n";
        } catch (\Exception $e) {
            echo "  TANGGAL skip: " . $e->getMessage() . "\n";
        }
    }
}

function addColumns($table, $cols) {
    if (!Schema::hasTable($table)) {
        echo "  Table $table not found!\n";
        return;
    }
    foreach ($cols as $name => $def) {
        if (!Schema::hasColumn($table, $name)) {
            try {
                DB::statement("ALTER TABLE `$table` ADD COLUMN `$name` $def");
                echo "  Added: $name\n";
            } catch (\Exception $e) {
                echo "  Error $name: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  Exists: $name (skip)\n";
        }
    }
}

// Kendaraans
echo "\n=== kendaraans ===\n";
cleanDates('kendaraans');
addColumns('kendaraans', [
    'FNO_PRK_S' => 'VARCHAR(20) NULL AFTER FNO_PRK_P',
    'FNO_PRK_O' => 'VARCHAR(20) NULL AFTER FNO_PRK_S',
    'FNO_PRK_M' => 'VARCHAR(20) NULL AFTER FNO_PRK_O',
]);

// Price tables (pricecus, pricecushis, prices)
$priceTables = ['pricecus', 'pricecushis', 'prices'];
foreach ($priceTables as $t) {
    echo "\n=== $t ===\n";
    cleanDates($t);
    addColumns($t, $priceCols);
    widenTanggal($t);
}

// Rent price tables
$rentTables = ['pricedingins', 'pricedingincus', 'pricedinginhis'];
foreach ($rentTables as $t) {
    echo "\n=== $t ===\n";
    cleanDates($t);
    addColumns($t, $dginginCols);
    widenTanggal($t);
}

echo "\n=== DONE ===\n";
