<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arh', function (Blueprint $table): void {
            $table->index('TGLFAKTUR', 'arh_tglfaktur_index');
            $table->index(['CUSTOMER', 'TGLFAKTUR'], 'arh_customer_tglfaktur_index');
        });

        Schema::table('barh', function (Blueprint $table): void {
            $table->index(['TGLBAYAR', 'STATUS'], 'barh_tglbayar_status_index');
            $table->index('VOIDED_AT', 'barh_voided_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('barh', function (Blueprint $table): void {
            $table->dropIndex('barh_voided_at_index');
            $table->dropIndex('barh_tglbayar_status_index');
        });

        Schema::table('arh', function (Blueprint $table): void {
            $table->dropIndex('arh_customer_tglfaktur_index');
            $table->dropIndex('arh_tglfaktur_index');
        });
    }
};
