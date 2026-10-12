<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->string('NAMA', 100)->nullable();
            $table->string('JABATAN', 100)->nullable();
            $table->text('TTD')->nullable(); // base64 encoded signature image
            $table->string('USER', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->nullableTimestamps();

            $table->index('NAMA');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
