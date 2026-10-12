<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_penjualan_hosting') || ! Schema::hasColumn('t_penjualan_hosting', 'customer')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE t_penjualan_hosting MODIFY customer VARCHAR(30) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE t_penjualan_hosting ALTER COLUMN customer TYPE VARCHAR(30)');
        }
    }

    public function down(): void
    {
        // Sengaja tidak dipersempit lagi agar kode customer 30 karakter tidak terpotong.
    }
};
