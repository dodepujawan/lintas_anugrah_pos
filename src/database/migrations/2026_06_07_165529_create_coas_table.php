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
        Schema::create('coas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun', 8)->unique();
            $table->string('nama_akun', 100);
            $table->enum('tipe', ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban']);
            $table->enum('kategori', ['Aktiva Lancar', 'Aktiva Tetap', 'Kewajiban Lancar', 'Kewajiban Jangka Panjang', 'Modal', 'Prive', 'Pendapatan Usaha', 'Pendapatan Lainnya', 'Beban Usaha', 'Beban Lainnya'])->nullable();
            $table->string('saldo_normal', 20)->default('Debit');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coas');
    }
};
