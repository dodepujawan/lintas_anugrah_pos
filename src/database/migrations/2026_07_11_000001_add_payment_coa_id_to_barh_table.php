<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('barh') && ! Schema::hasColumn('barh', 'PAYMENT_COA_ID')) {
            Schema::table('barh', function (Blueprint $table) {
                $table->foreignId('PAYMENT_COA_ID')
                    ->nullable()
                    ->after('METODE_PEMBAYARAN')
                    ->constrained('coas')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('barh') && Schema::hasColumn('barh', 'PAYMENT_COA_ID')) {
            Schema::table('barh', function (Blueprint $table) {
                $table->dropConstrainedForeignId('PAYMENT_COA_ID');
            });
        }
    }
};
