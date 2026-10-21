<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('pricedingincus')) {
            return;
        }

        if (! Schema::hasColumn('pricedingincus', 'NAMACUS')) {
            return;
        }

        // Legacy rows may contain zero dates. MySQL strict mode validates
        // existing rows during ALTER TABLE, so normalize them first.
        DB::statement("SET SESSION sql_mode = ''");
        foreach (['created_at', 'updated_at'] as $timestampColumn) {
            if (Schema::hasColumn('pricedingincus', $timestampColumn)) {
                DB::table('pricedingincus')
                    ->where($timestampColumn, '0000-00-00 00:00:00')
                    ->update([$timestampColumn => null]);
            }
        }

        $column = DB::table('information_schema.columns')
            ->select(['DATA_TYPE', 'CHARACTER_MAXIMUM_LENGTH', 'IS_NULLABLE'])
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'pricedingincus')
            ->where('COLUMN_NAME', 'NAMACUS')
            ->first();

        if (! $column || ! in_array(strtolower((string) $column->DATA_TYPE), ['char', 'varchar'], true)) {
            return;
        }

        if ((int) $column->CHARACTER_MAXIMUM_LENGTH >= 100) {
            return;
        }

        $nullable = $column->IS_NULLABLE === 'YES' ? 'NULL' : 'NOT NULL';

        DB::statement("ALTER TABLE `pricedingincus` MODIFY COLUMN `NAMACUS` VARCHAR(100) {$nullable}");
    }

    public function down(): void
    {
        // Tidak diturunkan ke VARCHAR(50) karena dapat memotong nama customer.
    }
};
