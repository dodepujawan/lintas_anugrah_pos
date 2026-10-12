<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aph', function (Blueprint $table) {
            $table->id();
            $table->string('NOFAKTUR', 50)->unique();
            $table->date('TGLFAKTUR');
            $table->date('TGLJT')->nullable();
            $table->string('SUPPLIER', 100);
            $table->decimal('HUTANG', 15, 2)->default(0);
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

        Schema::create('baph', function (Blueprint $table) {
            $table->id();
            $table->string('NOBAYAR', 50)->unique();
            $table->date('TGLBAYAR');
            $table->string('SUPPLIER', 100)->nullable();
            $table->decimal('JUMLAH_BAYAR', 15, 2)->default(0);
            $table->string('METODE_PEMBAYARAN', 50)->nullable();
            $table->string('NO_REFERENSI', 100)->nullable();
            $table->string('NOJURNAL', 50)->nullable();
            $table->string('KETERANGAN', 255)->nullable();
            $table->string('CABANG', 50)->nullable();
            $table->string('USER', 50)->nullable();
            $table->string('USER_UPDATE', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('bapd', function (Blueprint $table) {
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

            $table->foreign('NOBAYAR')->references('NOBAYAR')->on('baph')->cascadeOnDelete();
            $table->foreign('NOFAKTUR')->references('NOFAKTUR')->on('aph')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bapd');
        Schema::dropIfExists('baph');
        Schema::dropIfExists('aph');
    }
};
