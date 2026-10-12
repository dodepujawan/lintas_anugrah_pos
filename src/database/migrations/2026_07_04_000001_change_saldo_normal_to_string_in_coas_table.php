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
        Schema::table('coas', function (Blueprint $table) {
            $table->string('saldo_normal', 20)->default('Debit')->change();
        });

        DB::table('coas')
            ->whereRaw("CAST(saldo_normal AS CHAR) IN ('0', '0.00', '0.0')")
            ->update(['saldo_normal' => 'Debit']);

        DB::table('coas')
            ->whereRaw("CAST(saldo_normal AS CHAR) IN ('1', '1.00', '1.0')")
            ->update(['saldo_normal' => 'Kredit']);

        DB::table('coas')
            ->whereNotIn('saldo_normal', ['Debit', 'Kredit'])
            ->update(['saldo_normal' => 'Debit']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $table->decimal('saldo_normal', 15, 2)->default(0)->change();
        });
    }
};
