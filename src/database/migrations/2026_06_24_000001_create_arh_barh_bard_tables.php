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
        // Tabel ARH - Faktur Penjualan (Accounts Receivable Header)
        Schema::create('arh', function (Blueprint $table) {
            $table->id();
            $table->string('NOFAKTUR', 50)->unique();
            $table->date('TGLFAKTUR');
            $table->date('TGLJT')->nullable();
            $table->string('CUSTOMER', 100);
            $table->string('SALES', 50)->nullable();
            $table->string('AREA', 50)->nullable();
            $table->string('DIVISI', 50)->nullable();
            $table->decimal('PIUTANG', 15, 2)->default(0);
            $table->decimal('BAYAR', 15, 2)->default(0);
            $table->decimal('RETUR', 15, 2)->default(0);
            $table->decimal('DISCOUNT', 15, 2)->default(0);
            $table->decimal('SALDO', 15, 2)->default(0);
            $table->string('CABANG', 50)->nullable();
            $table->text('KETERANGAN')->nullable();
            $table->string('STATUS', 20)->nullable();
            $table->string('SOURCE', 50)->nullable();
            $table->date('TGLLUNAS')->nullable();
            $table->string('NOJURNAL', 50)->nullable();
            $table->string('USER', 50)->nullable();
            $table->string('USER_UPDATE', 50)->nullable();
            $table->timestamps();
        });

        // Tabel BARH - Header Pembayaran (Accounts Receivable Payment Header)
        Schema::create('barh', function (Blueprint $table) {
            $table->id();
            $table->string('NOBAYAR', 50)->unique();
            $table->date('TGLBAYAR');
            $table->string('CUSTOMER', 100)->nullable();
            $table->decimal('JUMLAH_BAYAR', 15, 2)->default(0);
            $table->string('METODE_PEMBAYARAN', 50)->nullable(); // ARH, BARH, BARD
            $table->string('KETERANGAN', 255)->nullable();
            $table->string('CABANG', 50)->nullable();
            $table->string('USER', 50)->nullable();
            $table->string('USER_UPDATE', 50)->nullable();
            $table->timestamps();
        });

        // Tabel BARD - Detail Pembayaran (Accounts Receivable Payment Detail)
        Schema::create('bard', function (Blueprint $table) {
            $table->id();
            $table->string('NOBAYAR', 50);
            $table->string('NOFAKTUR', 50);
            $table->date('TGLFAKTUR');
            $table->decimal('SALDO_FAKTUR', 15, 2)->default(0);
            $table->decimal('NILAI_BAYAR', 15, 2)->default(0);
            $table->decimal('DISCOUNT', 15, 2)->default(0);
            $table->decimal('RETUR', 15, 2)->default(0);
            $table->decimal('SISA_FAKTUR', 15, 2)->default(0);
            $table->text('KETERANGAN')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('NOBAYAR')->references('NOBAYAR')->on('barh')->onDelete('cascade');
            $table->foreign('NOFAKTUR')->references('NOFAKTUR')->on('arh')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bard');
        Schema::dropIfExists('barh');
        Schema::dropIfExists('arh');
    }
};
