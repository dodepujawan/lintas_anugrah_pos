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
        Schema::table('driver', function (Blueprint $table) {
            $table->string('user_id', 50)->nullable()->after('MULAI');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
