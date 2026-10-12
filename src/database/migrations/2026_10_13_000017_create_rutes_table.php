<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutes', function (Blueprint $table) {
            $table->id();
            $table->string('RUTE', 50)->nullable();
            $table->string('FROM', 50)->nullable();
            $table->string('TO', 50)->nullable();
            $table->decimal('HARGA', 12, 0)->default(0);
            $table->string('USER', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->nullableTimestamps();

            $table->index('RUTE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutes');
    }
};
