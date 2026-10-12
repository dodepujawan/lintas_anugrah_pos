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
        Schema::table('service', function (Blueprint $table) {
            $table->date('TGL_TEMPO')->nullable()->after('TGL_SERVIS');
            $table->char('NO_JURNAL', 30)->nullable()->after('TGL_TEMPO');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service', function (Blueprint $table) {
            $table->dropColumn(['TGL_TEMPO','NO_JURNAL']);
        });
    }
};
