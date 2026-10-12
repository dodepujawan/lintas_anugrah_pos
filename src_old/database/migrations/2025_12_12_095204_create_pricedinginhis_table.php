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
        Schema::create('pricedinginhis', function (Blueprint $table) {
            $table->id();
            $table->string('KODEDGN', 20);
            $table->date('TANGGAL')->nullable();
            $table->string('KODE', 20);
            $table->string('PERIODE', 50);
            $table->string('PLAT', 50);
            $table->string('JENIS', 50);
            $table->string('ITEM', 100);
            $table->decimal('HARGA', 10, 0)->default(0);
            $table->string('USER', 50);
            $table->string('USEREDIT', 50)->nullable();
            $table->string('KUNCI', 100)->nullable();

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
        Schema::dropIfExists('pricedinginhis');
    }
};
