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
        Schema::create('rute', function (Blueprint $table) {
            $table->id(); // id auto increment (Laravel side)

            $table->char('RUTE', 50)
                ->charset('latin1')
                ->collation('latin1_general_ci');

            $table->nullableTimestamps(); // created_at & updated_at NULLABLE
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rute');
    }
};
