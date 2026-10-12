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
        'msupplier', 'mcustomer', 'drivers',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // FOXPRO legacy data meng Meng meng menggunakan '0000-00-00' untuk tanggal kosong.
        // MySQL strict mode akan reject ini saat CONVERT. We temporarily
        // allow invalid dates just for this migration session.
        DB::statement("SET SESSION sql_mode = 'ALLOW_INVALID_DATES'");

        foreach (self::LEGACY_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            try {
                DB::statement(
                    "ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
                );
            } catch (\Throwable $e) {
                // Log error dan lanjut ke tabel berikutnya — jangan blokir seluruh migrasi
                \Log::warning("Charset conversion failed for {$table}: " . $e->getMessage());
            }
        }

        DB::statement("SET SESSION sql_mode = DEFAULT");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("SET SESSION sql_mode = 'ALLOW_INVALID_DATES'");

        foreach (self::LEGACY_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            try {
                DB::statement(
                    "ALTER TABLE `{$table}` CONVERT TO CHARACTER SET latin1 COLLATE latin1_general_ci"
                );
            } catch (\Throwable $e) {
                \Log::warning("Charset revert failed for {$table}: " . $e->getMessage());
            }
        }

        DB::statement("SET SESSION sql_mode = DEFAULT");
    }
};
