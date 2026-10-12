<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_penjualan_hosting')) return;

        if (! Schema::hasColumn('t_penjualan_hosting', 'jenis')) {
            Schema::table('t_penjualan_hosting', function (Blueprint $table): void {
                $table->string('jenis', 15)->nullable()->after('jenis_bayar')->index();
            });

            if (Schema::hasColumn('t_penjualan_hosting', 'status_bayar')) {
                DB::table('t_penjualan_hosting')
                    ->whereIn('status_bayar', ['EKS', 'REN', 'CO', 'EXSPEDISI'])
                    ->update(['jenis' => DB::raw('status_bayar')]);
            }
            if (Schema::hasColumn('t_penjualan_hosting', 'sumber')) {
                DB::table('t_penjualan_hosting')
                    ->whereNull('jenis')
                    ->whereIn('sumber', ['EKS', 'REN', 'CO', 'EXSPEDISI'])
                    ->update(['jenis' => DB::raw('sumber')]);
            }
        }

        if (Schema::hasColumn('t_penjualan_hosting', 'status_bayar')) {
            Schema::table('t_penjualan_hosting', fn (Blueprint $table) => $table->dropColumn('status_bayar'));
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_penjualan_hosting')) return;
        if (! Schema::hasColumn('t_penjualan_hosting', 'status_bayar')) {
            Schema::table('t_penjualan_hosting', fn (Blueprint $table) => $table->string('status_bayar', 15)->nullable());
            if (Schema::hasColumn('t_penjualan_hosting', 'jenis')) DB::table('t_penjualan_hosting')->update(['status_bayar' => DB::raw('jenis')]);
        }
        if (Schema::hasColumn('t_penjualan_hosting', 'jenis')) Schema::table('t_penjualan_hosting', fn (Blueprint $table) => $table->dropColumn('jenis'));
    }
};
