<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('barh') && ! Schema::hasColumn('barh', 'NO_REFERENSI')) {
            Schema::table('barh', function (Blueprint $table) {
                $table->string('NO_REFERENSI', 100)->nullable()->after('PAYMENT_COA_ID');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('barh') && Schema::hasColumn('barh', 'NO_REFERENSI')) {
            Schema::table('barh', function (Blueprint $table) {
                $table->dropColumn('NO_REFERENSI');
            });
        }
    }
};
