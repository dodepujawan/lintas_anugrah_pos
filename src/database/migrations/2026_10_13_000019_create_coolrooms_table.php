<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coolrooms', function (Blueprint $table) {
            $table->id();
            $table->date('TGLMUAT')->nullable();
            $table->char('NOSJ', 20)->nullable();
            $table->char('NOMUAT', 20)->nullable();
            $table->char('CUSTOMER', 100)->nullable();
            $table->string('NAMACUST', 100)->nullable();
            $table->char('KENDARAAN', 20)->nullable();
            $table->char('NAMA_KENDARAAN', 50)->nullable();
            $table->char('DRIVER', 20)->nullable();
            $table->char('NAMA_DRIVER', 50)->nullable();
            $table->decimal('HARGA', 12, 0)->nullable();
            $table->decimal('JUMLAH', 12, 0)->nullable();
            $table->decimal('DISC', 5, 2)->nullable();
            $table->decimal('NDISCAW', 12, 0)->nullable();
            $table->decimal('NDISC', 12, 0)->nullable();
            $table->decimal('TOTAL', 12, 0)->nullable();
            $table->decimal('PPN', 5, 2)->nullable();
            $table->decimal('GRAND', 12, 0)->nullable();
            $table->char('SJ', 30)->nullable();
            $table->char('KETERANGAN', 100)->nullable();
            $table->char('STATUS', 20)->nullable();
            $table->char('user', 50)->nullable();
            $table->char('USEREDIT', 50)->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('area_name')->nullable();
            $table->string('NOJURNAL', 50)->nullable();
            $table->unsignedBigInteger('jurnal_id')->nullable();
            $table->nullableTimestamps();

            $table->index('NOSJ');
            $table->index('CUSTOMER');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coolrooms');
    }
};
