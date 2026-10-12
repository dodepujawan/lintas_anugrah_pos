<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->date('TGL_SERVICE')->nullable();
            $table->string('KENDARAAN', 20)->nullable();
            $table->string('NAMA_KENDARAAN', 50)->nullable();
            $table->string('NOSJ', 20)->nullable();
            $table->string('NOBAYAR', 50)->nullable();
            $table->decimal('HARGA', 12, 0)->nullable();
            $table->decimal('JUMLAH', 12, 0)->nullable();
            $table->text('KETERANGAN')->nullable();
            $table->string('REK_BIAYA', 5)->nullable();
            $table->string('tipe', 1)->nullable();
            $table->string('USER', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->string('NO_JURNAL', 50)->nullable();
            $table->date('TGL_TEMP')->nullable();
            $table->date('TGLJT')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('area_name')->nullable();
            $table->string('NOJURNAL', 50)->nullable();
            $table->unsignedBigInteger('jurnal_id')->nullable();
            $table->nullableTimestamps();

            $table->index('KENDARAAN');
            $table->index('NOSJ');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
