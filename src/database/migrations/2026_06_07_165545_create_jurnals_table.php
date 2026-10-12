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
        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->string('no_jurnal', 30)->unique();
            $table->date('tanggal');
            $table->enum('tipe', ['Penerimaan Kas', 'Pengeluaran Kas', 'Jurnal Umum', 'Penjualan', 'Pembelian', 'Pelunasan Piutang']);
            $table->text('keterangan')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_kredit', 15, 2)->default(0);
            $table->string('referensi', 50)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};
