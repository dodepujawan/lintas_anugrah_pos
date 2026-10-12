<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('msupplier')) {
            return;
        }

        foreach (['aph', 'baph'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'SUPPLIER')) {
                continue;
            }

            DB::table('msupplier')
                ->select('SUPPLIER', 'NAMA')
                ->whereNotNull('NAMA')
                ->orderBy('SUPPLIER')
                ->chunk(100, function ($suppliers) use ($table) {
                    foreach ($suppliers as $supplier) {
                        DB::table($table)
                            ->where('SUPPLIER', $supplier->NAMA)
                            ->update(['SUPPLIER' => $supplier->SUPPLIER]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Data normalization is intentionally not reversed.
    }
};
