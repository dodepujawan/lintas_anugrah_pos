<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kwitansi', function (Blueprint $table) {
            $table->id();
            $table->string('NO_KWT', 50)->nullable();
            $table->date('TGL_KWT')->nullable();
            $table->string('NOFAKTUR', 50)->nullable();
            $table->decimal('SALDO_FAKTUR', 12, 0)->nullable();
            $table->decimal('NILAI_BAYAR', 12, 0)->nullable();
            $table->decimal('DISCOUNT', 12, 0)->nullable();
            $table->decimal('RETUR', 12, 0)->nullable();
            $table->decimal('SISA_FAKTUR', 12, 0)->nullable();
            $table->char('JENIS_KWT', 10)->nullable();
            $table->char('CUSTOMER', 30)->nullable();
            $table->string('NAMACUST', 100)->nullable();
            $table->char('NOBAYAR', 50)->nullable();
            $table->string('KETERANGAN', 200)->nullable();
            $table->char('user', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('area_name')->nullable();
            $table->string('NOJURNAL', 50)->nullable();
            $table->unsignedBigInteger('jurnal_id')->nullable();
            $table->nullableTimestamps();

            $table->index('NO_KWT');
            $table->index('NOFAKTUR');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kwitansi');
    }
};
