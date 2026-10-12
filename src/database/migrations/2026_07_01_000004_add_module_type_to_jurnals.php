<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            if (!Schema::hasColumn('jurnals', 'module_type')) {
                $table->string('module_type', 50)->nullable()->after('status');
            }
        });

        // Populate module_type for existing Saldo Awal entries (identified by no_jurnal prefix 'SA')
        DB::table('jurnals')
            ->where('no_jurnal', 'like', 'SA%')
            ->update(['module_type' => 'Saldo Awal']);
    }

    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            if (Schema::hasColumn('jurnals', 'module_type')) {
                $table->dropColumn('module_type');
            }
        });
    }
};
