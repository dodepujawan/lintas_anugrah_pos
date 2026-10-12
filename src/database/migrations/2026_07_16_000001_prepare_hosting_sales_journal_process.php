<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_penjualan_hosting')) return;

        Schema::table('t_penjualan_hosting', function (Blueprint $table): void {
            if (! Schema::hasColumn('t_penjualan_hosting', 'status_jurnal')) $table->string('status_jurnal', 30)->default('BELUM DIPROSES')->index();
            if (! Schema::hasColumn('t_penjualan_hosting', 'no_jurnal')) $table->string('no_jurnal', 50)->nullable()->index();
            if (! Schema::hasColumn('t_penjualan_hosting', 'jurnal_id')) $table->unsignedBigInteger('jurnal_id')->nullable()->index();
            if (! Schema::hasColumn('t_penjualan_hosting', 'posted_at')) $table->timestamp('posted_at')->nullable();
            if (! Schema::hasColumn('t_penjualan_hosting', 'posted_by')) $table->unsignedBigInteger('posted_by')->nullable();
            if (! Schema::hasColumn('t_penjualan_hosting', 'posting_error')) $table->text('posting_error')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_penjualan_hosting')) return;
        $columns = collect(['status_jurnal','no_jurnal','jurnal_id','posted_at','posted_by','posting_error'])
            ->filter(fn ($column) => Schema::hasColumn('t_penjualan_hosting', $column))->all();
        if ($columns) Schema::table('t_penjualan_hosting', fn (Blueprint $table) => $table->dropColumn($columns));
    }
};
