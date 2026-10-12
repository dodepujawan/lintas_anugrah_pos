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
        Schema::table('coolrooms', function (Blueprint $table) {
            $table->char('NOSJ', 30)->nullable()->after('id');
            $table->date('TGLSJ')->nullable()->after('NOSJ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coolrooms', function (Blueprint $table) {
            $table->dropColumn([
                'NOSJ',
                'TGLSJ'
            ]);
        });
    }
};
