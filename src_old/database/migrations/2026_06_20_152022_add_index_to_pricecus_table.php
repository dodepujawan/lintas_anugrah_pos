<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Index KODECUS
        $indexKodecus = DB::selectOne("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'pricecus'
              AND index_name = 'idx_pricecus_kodecus'
            LIMIT 1
        ");

        if (!$indexKodecus) {
            Schema::table('pricecus', function (Blueprint $table) {
                $table->index(
                    'KODECUS',
                    'idx_pricecus_kodecus'
                );
            });
        }

        // Index KODECUS + KODE
        // Jika sudah ada, jangan dibuat lagi.
        $indexKodecusKode = DB::selectOne("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'pricecus'
              AND index_name = 'idx_pricecus_kodecus_kode'
            LIMIT 1
        ");

        if (!$indexKodecusKode) {
            Schema::table('pricecus', function (Blueprint $table) {
                $table->index(
                    ['KODECUS', 'KODE'],
                    'idx_pricecus_kodecus_kode'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus index KODECUS jika ada.
        // Index gabungan tidak dihapus karena sudah ada sebelumnya
        // di database sebelum migration ini dijalankan.
        $indexKodecus = DB::selectOne("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'pricecus'
              AND index_name = 'idx_pricecus_kodecus'
            LIMIT 1
        ");

        if ($indexKodecus) {
            Schema::table('pricecus', function (Blueprint $table) {
                $table->dropIndex('idx_pricecus_kodecus');
            });
        }
    }
};
