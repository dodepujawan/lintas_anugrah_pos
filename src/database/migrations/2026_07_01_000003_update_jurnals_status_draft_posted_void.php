<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            $table->string('status', 20)->default('Draft')->after('created_by');
        });

        DB::table('jurnals')->update([
            'status' => DB::raw("CASE WHEN is_posted = 1 THEN 'Posted' ELSE 'Draft' END"),
        ]);

        Schema::table('jurnals', function (Blueprint $table) {
            if (Schema::hasColumn('jurnals', 'tipe')) {
                $table->dropColumn('tipe');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            $table->enum('tipe', ['Penerimaan Kas', 'Pengeluaran Kas', 'Jurnal Umum', 'Penjualan', 'Pembelian', 'Pelunasan Piutang'])->after('tanggal')->default('Jurnal Umum');
        });

        DB::table('jurnals')->update([
            'tipe' => DB::raw("CASE WHEN status = 'Posted' THEN 'Jurnal Umum' ELSE 'Jurnal Umum' END"),
        ]);

        Schema::table('jurnals', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
