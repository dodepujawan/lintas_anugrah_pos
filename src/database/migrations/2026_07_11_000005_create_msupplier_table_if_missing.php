<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('msupplier')) {
            return;
        }

        Schema::create('msupplier', function (Blueprint $table) {
            $table->string('SUPPLIER', 30)->primary();
            $table->string('NAMA', 100)->nullable();
            $table->string('ALAMAT1', 200)->nullable();
            $table->string('ALAMAT2', 200)->nullable();
            $table->string('KOTA', 100)->nullable();
            $table->string('TELEPON', 100)->nullable();
            $table->string('FAX', 100)->nullable();
            $table->string('EMAIL', 100)->nullable();
            $table->string('KONTAK', 100)->nullable();
            $table->string('NOREK', 100)->nullable();
            $table->string('BANK', 100)->nullable();
            $table->string('ATASNAMA', 100)->nullable();
            $table->decimal('SALDO', 15, 2)->default(0);
            $table->decimal('RETURAN', 15, 2)->default(0);
            $table->integer('FTOP')->default(0);
            $table->decimal('DISC_REG', 10, 2)->default(0);

            $table->index('NAMA');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msupplier');
    }
};
