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
        Schema::create('service', function (Blueprint $table) {
            // Laravel primary key
            $table->id();
            // Nomor transaksi otomatis
            $table->char('NO_SERVICE', 20)->nullable();
            // Nomor nota tempat service
            $table->char('NO_REF', 30)->nullable();
            // tanggal servis
            $table->date('TGL_SERVIS')->nullable();
            // tanggal input transaksi
            $table->dateTime('TGL_TRANSAKSI')->nullable();
            // kode mobil
            $table->char('KODE_MOBIL', 10)->nullable();
            // supplier
            $table->char('KODE_SUPPLIER', 8)->nullable();
            // perkiraan biaya
            $table->char('FNO_PRK_B', 10)->nullable();
            // keterangan
            $table->char('KETERANGAN', 60)->nullable();
            // nilai servis
            $table->decimal('NILAI_SERVIS', 12, 0)->nullable()->default(0);
            // user
            $table->char('USER_INPUT', 20)->nullable();
            $table->char('USER_EDIT', 20)->nullable();

            // Laravel timestamps
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
        Schema::dropIfExists('service');
    }
};
