<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('KODE', 6)->nullable();
            $table->string('NAMA', 100)->nullable();
            $table->string('CABANG', 2)->nullable();
            $table->text('KETERANGAN')->nullable();
            $table->string('USER', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->nullableTimestamps();

            $table->index('KODE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
