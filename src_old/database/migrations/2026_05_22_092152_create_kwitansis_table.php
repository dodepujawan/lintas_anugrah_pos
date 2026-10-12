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
        Schema::create('kwitansi', function (Blueprint $table) {
            // ======================
            // PRIMARY KEY
            // ======================
            $table->id();
            // ======================
            // DOKUMEN & TRANSAKSI
            // ======================
            $table->char('NOKWT', 30)->nullable();
            $table->date('TGL')->nullable();
            $table->char('FDOK_TRANS', 50)->nullable();
            $table->date('TGL_TRANS')->nullable();
            // ======================
            // CUSTOMER & SJ
            // ======================
            $table->char('CUSTOMER', 30)->nullable();
            $table->char('NOSJ', 30)->nullable();
            // ======================
            // KETERANGAN
            // ======================
            $table->char('FKETERANG', 100)->nullable();
            $table->char('FNAMA', 100)->nullable();
            // ======================
            // NILAI & TOTAL
            // ======================
            $table->decimal('FNIL_DOK', 12, 0)->nullable();
            $table->decimal('TOTAL', 12, 0)->nullable();
            $table->decimal('PPN', 5, 2)->nullable();
            $table->decimal('DISC', 5, 2)->nullable();
            $table->decimal('NDISC', 12, 0)->nullable();
            // ======================
            // USER & JENIS
            // ======================
            $table->char('USERINPUT', 10)->nullable();
            $table->char('JENIS', 3)->nullable();
            // ======================
            // TIMESTAMPS
            // ======================
            $table->nullableTimestamps();
            // ======================
            // CHARSET
            // ======================
            $table->charset = 'latin1';
            $table->collation = 'latin1_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kwitansis');
    }
};
