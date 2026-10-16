<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            if (! Schema::hasColumn('kendaraans', 'FNO_PRK_S')) {
                $table->char('FNO_PRK_S', 20)->nullable()->after('FNO_PRK_P');
            }
            if (! Schema::hasColumn('kendaraans', 'FNO_PRK_O')) {
                $table->char('FNO_PRK_O', 20)->nullable()->after('FNO_PRK_S');
            }
            if (! Schema::hasColumn('kendaraans', 'FNO_PRK_M')) {
                $table->char('FNO_PRK_M', 20)->nullable()->after('FNO_PRK_O');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->dropColumn(['FNO_PRK_S', 'FNO_PRK_O', 'FNO_PRK_M']);
        });
    }
};
