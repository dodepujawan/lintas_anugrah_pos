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
        Schema::table('mcustomer', function (Blueprint $table) {
            $table->decimal('KTP', 5, 0)->default(0)->after('DC');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcustomer', function (Blueprint $table) {
            $table->dropColumn('KTP');
        });
    }
};
