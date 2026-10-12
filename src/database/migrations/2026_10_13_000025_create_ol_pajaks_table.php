<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ol_pajaks', function (Blueprint $table) {
            $table->id();
            $table->decimal('PPN', 5, 2)->default(0);
            $table->decimal('DISC1', 5, 2)->default(0);
            $table->decimal('DISC2', 5, 2)->default(0);
            $table->string('USER', 50)->nullable();
            $table->string('USEREDIT', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->nullableTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ol_pajaks');
    }
};
