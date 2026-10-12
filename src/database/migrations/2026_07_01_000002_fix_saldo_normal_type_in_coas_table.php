<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE coas SET saldo_normal = '0' WHERE saldo_normal IN ('D','K')");
        DB::statement("ALTER TABLE coas MODIFY saldo_normal DECIMAL(15,2) NOT NULL DEFAULT 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this migration may reintroduce invalid enum values.
    }
};
