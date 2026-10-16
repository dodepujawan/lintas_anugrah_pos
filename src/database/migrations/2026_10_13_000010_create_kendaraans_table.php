<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kendaraans', function (Blueprint $table) {
            $table->id();
            $table->char('KODE', 20)->nullable()->unique();
            $table->char('NAMA', 50)->nullable();
            $table->char('PLAT', 20)->nullable();
            $table->char('FNO_PRK_B', 15)->nullable();
            $table->char('FNO_PRK_P', 20)->nullable();
            $table->char('FNO_PRK_S', 20)->nullable();
            $table->char('FNO_PRK_O', 20)->nullable();
            $table->char('FNO_PRK_M', 20)->nullable();
            $table->char('FNO_PRK_T', 15)->nullable();
            $table->char('JENIS', 1)->nullable();
            $table->char('FNO_PRK', 13)->nullable();
            $table->char('NO_BKK', 22)->nullable();
            $table->char('KENDARAAN_TYPE', 20)->nullable();
            $table->string('USER', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->nullableTimestamps();

            $table->index('KODE');
            $table->index('PLAT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kendaraans');
    }
};
