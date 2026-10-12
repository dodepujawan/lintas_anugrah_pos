<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('arh') && ! Schema::hasColumn('arh', 'NOJURNAL')) {
            Schema::table('arh', function (Blueprint $table) {
                $table->string('NOJURNAL', 50)->nullable()->after('SOURCE');
            });
        }

        if (Schema::hasTable('barh') && ! Schema::hasColumn('barh', 'NOJURNAL')) {
            Schema::table('barh', function (Blueprint $table) {
                $table->string('NOJURNAL', 50)->nullable()->after('USER_UPDATE');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('barh') && Schema::hasColumn('barh', 'NOJURNAL')) {
            Schema::table('barh', function (Blueprint $table) {
                $table->dropColumn('NOJURNAL');
            });
        }

        if (Schema::hasTable('arh') && Schema::hasColumn('arh', 'NOJURNAL')) {
            Schema::table('arh', function (Blueprint $table) {
                $table->dropColumn('NOJURNAL');
            });
        }
    }
};
