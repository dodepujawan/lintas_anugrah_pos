<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekenings', function (Blueprint $table) {
            $table->id();
            $table->char('BANK', 40)->nullable();
            $table->char('NOREK', 40)->nullable();
            $table->char('NAMA', 40)->nullable();
            $table->char('USER', 50)->nullable();
            $table->char('AKTIF', 1)->nullable();
            $table->nullableTimestamps();

            $table->index('BANK');
            $table->index('NOREK');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekenings');
    }
};
