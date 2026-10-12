<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_TABLES = [
        'kendaraans', 'prices', 'pricecus', 'pricecushis',
        'pricedingins', 'pricedingincus', 'pricedinginhis',
        'rutes', 'expedisis', 'coolrooms', 'kwitansi',
        'services', 'rekenings', 'signatures', 'areas',
        'ol_pajaks', 'arh', 'aph', 'barh', 'bard', 'baph', 'bapd',
        'msupplier', 'mcustomer', 'drivers', 'kwitansi',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::LEGACY_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = DB::select("SHOW COLUMNS FROM `{$table}`");

            foreach ($columns as $column) {
                $field = $column->Field;
                $type = $column->Type;
                $collation = $column->Collation;

                // Skip kolom yang sudah utf8mb4
                if (is_null($collation)) {
                    continue;
                }

                // Konversi kolom teks ke utf8mb4
                if (in_array($column->Type, ['char', 'varchar', 'text', 'mediumtext', 'longtext'])) {
                    DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    break; // break inner loop, sudah convert satu kolom artinya seluruh tabel sudah convert
                }
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::LEGACY_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET latin1 COLLATE latin1_general_ci");
        }
    }
};
