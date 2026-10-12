<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricedingins', function (Blueprint $table) {
            $table->id();
            $table->string('FROM', 30)->nullable();
            $table->string('TO', 30)->nullable();
            $table->decimal('HARGA', 12, 0)->default(0);
            $table->char('TANGGAL', 1)->nullable();
            $table->string('USER', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->nullableTimestamps();

            $table->index('FROM');
            $table->index('TO');
        });

        Schema::create('pricedingincus', function (Blueprint $table) {
            $table->id();
            $table->string('KODECUS', 50)->nullable();
            $table->string('NAMACUS', 50)->nullable();
            $table->string('FROM', 30)->nullable();
            $table->string('TO', 30)->nullable();
            $table->decimal('HARGA', 12, 0)->default(0);
            $table->char('TANGGAL', 1)->nullable();
            $table->string('USER', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->nullableTimestamps();

            $table->index('KODECUS');
        });

        Schema::create('pricedinginhis', function (Blueprint $table) {
            $table->id();
            $table->string('KODECUS', 50)->nullable();
            $table->string('NAMACUS', 50)->nullable();
            $table->string('FROM', 30)->nullable();
            $table->string('TO', 30)->nullable();
            $table->decimal('HARGA', 12, 0)->default(0);
            $table->char('TANGGAL', 1)->nullable();
            $table->string('USER', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->nullableTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricedinginhis');
        Schema::dropIfExists('pricedingincus');
        Schema::dropIfExists('pricedingins');
    }
};
