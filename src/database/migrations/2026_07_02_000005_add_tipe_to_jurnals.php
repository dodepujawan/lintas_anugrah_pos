<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('jurnals', 'tipe')) {
            Schema::table('jurnals', function (Blueprint $table) {
                $table->enum('tipe', ['Penerimaan Kas', 'Pengeluaran Kas', 'Jurnal Umum', 'Penjualan', 'Pembelian', 'Pelunasan Piutang'])->after('tanggal')->default('Jurnal Umum');
            });

            // set default for existing rows
            DB::table('jurnals')->whereNull('tipe')->update(['tipe' => 'Jurnal Umum']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jurnals', 'tipe')) {
            Schema::table('jurnals', function (Blueprint $table) {
                $table->dropColumn('tipe');
            });
        }
    }
};
