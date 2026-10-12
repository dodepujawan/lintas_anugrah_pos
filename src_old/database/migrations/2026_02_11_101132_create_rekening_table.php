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
        Schema::create('rekening', function (Blueprint $table) {
            $table->id();

            $table->string('BANK', 100);
            $table->string('NOREK', 50);
            $table->string('NAMA', 100);

            $table->string('USER', 50)->nullable();

            $table->integer('AKTIF')->default(0); // 1 = dipakai di PDF

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
        Schema::dropIfExists('rekening');
    }
};
