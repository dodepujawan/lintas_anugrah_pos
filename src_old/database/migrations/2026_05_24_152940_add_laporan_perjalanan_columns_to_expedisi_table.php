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
        Schema::table('expedisi', function (Blueprint $table) {
             $table->string('PLAT_NOMOR', 20)
                ->nullable()
                ->after('NAMA_KENDARAAN');
            $table->decimal('KM_AWAL', 10, 1)
                ->nullable()
                ->after('PLAT_NOMOR');
            $table->decimal('KM_AKHIR', 10, 1)
                ->nullable()
                ->after('KM_AWAL');
            $table->decimal('UANG_JALAN', 12, 0)
                ->nullable()
                ->after('KM_AKHIR');
            $table->decimal('UANG_DRIVER_MAKAN', 12, 0)
                ->nullable()
                ->after('UANG_JALAN');
            $table->decimal('UANG_LAIN_LAIN', 12, 0)
                ->nullable()
                ->after('UANG_DRIVER_MAKAN');
            $table->string('PENGIRIM', 100)
                ->nullable()
                ->after('UANG_LAIN_LAIN');
            $table->boolean('AC_KENDARAAN')
                ->nullable()
                ->after('PENGIRIM');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expedisi', function (Blueprint $table) {
             $table->dropColumn([
                'PLAT_NOMOR',
                'KM_AWAL',
                'KM_AKHIR',
                'UANG_JALAN',
                'UANG_DRIVER_MAKAN',
                'UANG_LAIN_LAIN',
                'PENGIRIM',
                'AC_KENDARAAN',
            ]);
        });
    }
};
