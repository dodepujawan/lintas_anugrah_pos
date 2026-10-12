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
        Schema::create('kendaraan', function (Blueprint $table) {
            // Laravel standard
            $table->id();

            // FoxPro legacy columns (UPPERCASE)
            $table->char('KODE', 20)->nullable();
            $table->string('NAMA', 100)->default('');
            $table->char('PLAT', 50);
            $table->char('JENIS', 50);

            $table->char('FNO_PRK_B', 20)->nullable();
            $table->char('FNO_PRK_P', 20)->nullable();
            $table->char('FNO_PRK_S', 20)->nullable();
            $table->char('FNO_PRK_O', 20)->nullable();
            $table->char('FNO_PRK_M', 20)->nullable();

            // Laravel timestamps (nullable, lowercase)
            $table->nullableTimestamps();

            // FoxPro compatibility
            $table->charset = 'latin1';
            $table->collation = 'latin1_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};
