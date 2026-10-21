<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('kendaraans')) {
            return;
        }

        if (! Schema::hasColumn('kendaraans', 'JENIS')) {
            return;
        }

        $column = DB::table('information_schema.columns')
            ->select(['DATA_TYPE', 'CHARACTER_MAXIMUM_LENGTH', 'IS_NULLABLE'])
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'kendaraans')
            ->where('COLUMN_NAME', 'JENIS')
            ->first();

        if (! $column || ! in_array(strtolower((string) $column->DATA_TYPE), ['char', 'varchar'], true)) {
            return;
        }

        if ((int) $column->CHARACTER_MAXIMUM_LENGTH >= 50) {
            return;
        }

        $nullable = $column->IS_NULLABLE === 'YES' ? 'NULL' : 'NOT NULL';

        DB::statement("ALTER TABLE `kendaraans` MODIFY COLUMN `JENIS` CHAR(50) {$nullable}");
    }

    public function down(): void
    {
        // Tidak diturunkan ke CHAR(1) karena dapat memotong data kendaraan.
    }
};
