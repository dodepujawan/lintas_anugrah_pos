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
        Schema::create('pricecushis', function (Blueprint $table) {
            $table->id('id');
            $table->string('KODE', 20); // Kolom baru untuk kode unik
            $table->date('TANGGAL')->nullable();
            $table->string('KETERANGAN', 50); // ganti char jadi string
            $table->decimal('DARI', 10, 0)->default(0);
            $table->decimal('SAMPAI', 10, 0)->default(0);
            $table->string('RUTE', 30); // ganti char jadi string
            $table->decimal('HARGA', 12, 0);
            $table->decimal('HV', 10, 0)->default(0);
            $table->decimal('HKG', 10, 0)->default(0);
            $table->decimal('HBOK', 10, 0);
            $table->string('USER', 50); // ganti char jadi string
            $table->string('USEREDIT', 50); // ganti char jadi string
            $table->string('KUNCI', 100); // ganti char jadi string
            $table->decimal('HG', 1, 0);
            $table->string('JENIS', 1); // ganti char jadi string

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
        Schema::dropIfExists('pricecushis');
    }
};
