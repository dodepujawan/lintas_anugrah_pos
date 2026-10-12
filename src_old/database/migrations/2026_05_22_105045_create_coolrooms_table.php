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
        Schema::create('coolrooms', function (Blueprint $table) {
            // ======================
            // PRIMARY KEY
            // ======================
            $table->id();
            // ======================
            // CUSTOMER
            // ======================
            $table->char('CUSTOMER_KODE', 20)->nullable();
            $table->char('CUSTOMER', 100)->nullable();
            // ======================
            // TRANSAKSI
            // ======================
            $table->date('TGL')->nullable();
            $table->char('INVOICE', 30)->nullable();
            $table->date('TGLINVOICE')->nullable();
            $table->date('TGLJT')->nullable();
            // ======================
            // AKUN
            // ======================
            $table->char('AKUN_PENDAPATAN', 20)->nullable();
            $table->char('NAMA_AKUN_PENDAPATAN', 100)->nullable();
            $table->char('AKUN_PIUTANG', 20)->nullable();
            $table->char('NAMA_AKUN_PIUTANG', 100)->nullable();
            // ======================
            // BARANG / SEWA
            // ======================
            $table->decimal('JUMLAH', 12, 3)->nullable();
            $table->char('UNIT', 10)->nullable();
            $table->decimal('HARGA', 12, 0)->nullable();
            // checkbox boxing
            $table->boolean('BOXING')->default(false);
            // ======================
            // PERHITUNGAN
            // ======================
            $table->decimal('SUBTOTAL', 12, 0)->nullable();
            $table->decimal('DISC', 5, 2)->nullable();
            $table->decimal('NDISC', 12, 0)->nullable();
            $table->decimal('DPP', 12, 0)->nullable();
            $table->decimal('PPN', 5, 2)->nullable();
            $table->decimal('NPPN', 12, 0)->nullable();
            $table->decimal('TOTAL', 12, 0)->nullable();
            $table->decimal('GRAND', 12, 0)->nullable();
            // ======================
            // PEMBAYARAN
            // ======================
            $table->decimal('BAYAR', 12, 0)->nullable();
            $table->decimal('PIUTANG', 12, 0)->nullable();
            $table->integer('TOP')->nullable();
            // ======================
            // KETERANGAN
            // ======================
            $table->text('KETERANGAN')->nullable();
            // ======================
            // JURNAL
            // ======================
            $table->char('NOJURNAL', 30)->nullable();
            // ======================
            // KWITANSI
            // ======================
            $table->char('KWT', 30)->nullable();
            $table->date('TGLKW')->nullable();
            // ======================
            // STATUS
            // ======================
            $table->char('STS', 20)->nullable();
            // ======================
            // USER
            // ======================
            $table->char('USERINPUT', 50)->nullable();
            $table->char('USEREDIT', 50)->nullable();
            $table->char('USERINV', 50)->nullable();
            // ======================
            // CABANG
            // ======================
            $table->char('CABANG', 5)->nullable();
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
        Schema::dropIfExists('coolrooms');
    }
};
