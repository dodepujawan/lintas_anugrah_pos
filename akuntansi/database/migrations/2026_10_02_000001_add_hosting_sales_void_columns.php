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
            if (! Schema::hasColumn('t_penjualan_hosting', 'void_journal_id')) $table->unsignedBigInteger('void_journal_id')->nullable();
            if (! Schema::hasColumn('t_penjualan_hosting', 'voided_at')) $table->timestamp('voided_at')->nullable();
            if (! Schema::hasColumn('t_penjualan_hosting', 'voided_by')) $table->unsignedBigInteger('voided_by')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('t_penjualan_hosting')) return;
        Schema::table('t_penjualan_hosting', fn (Blueprint $table) => $table->dropColumn(['void_journal_id', 'voided_at', 'voided_by']));
    }
};
