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
        Schema::create('driver', function (Blueprint $table) {
            // Laravel standard
            $table->id();

            // FoxPro legacy columns (UPPERCASE)
            $table->char('KODE', 3)->nullable();
            $table->string('NAMA', 100)->default('');
            $table->char('ALAMAT', 100);
            $table->char('PHONE', 50);
            $table->date('MULAI');

            // Laravel timestamps (nullable, lowercase)
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
        Schema::dropIfExists('driver');
    }
};
