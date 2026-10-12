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
        Schema::create('msupplier', function (Blueprint $table) {

            // Laravel standard primary key
            $table->id();

            // FoxPro legacy columns (UPPERCASE)
            $table->char('SUPPLIER', 8)->nullable();
            $table->char('KATEGORI', 4)->nullable();
            $table->char('NAMA', 40)->nullable();
            $table->char('ALAMAT1', 40)->nullable();
            $table->char('ALAMAT2', 40)->nullable();
            $table->char('KOTA', 40)->nullable();
            $table->char('TELEPON', 40)->nullable();
            $table->char('FAX', 40)->nullable();
            $table->char('EMAIL', 40)->nullable();
            $table->char('KONTAK', 40)->nullable();
            $table->char('NOREK', 40)->nullable();
            $table->char('BANK', 40)->nullable();
            $table->char('ATASNAMA', 40)->nullable();

            $table->decimal('SALDO', 10, 0)->nullable()->default(0);
            $table->decimal('RETURAN', 10, 0)->nullable()->default(0);
            $table->decimal('FTOP', 6, 0)->nullable()->default(0);
            $table->decimal('DISC_REG', 6, 2)->nullable()->default(0.00);

            // Laravel timestamps (optional)
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
        Schema::dropIfExists('msupplier');
    }
};
