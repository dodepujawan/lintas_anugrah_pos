<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expedisi', function (Blueprint $table) {
            $table->char('barang', 100)->nullable()->after('JURNAL');
            $table->char('penyimpanan', 1)->nullable()->after('barang');
            $table->integer('koli')->nullable()->after('penyimpanan');
            $table->text('catatan')->nullable()->after('koli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expedisi', function (Blueprint $table) {
            $table->dropColumn(['barang', 'penyimpanan', 'koli', 'catatan']);
        });
    }
};
