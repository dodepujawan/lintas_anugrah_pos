<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('account_mappings')) {
            DB::table('account_mappings')
                ->where('mapping_key', 'default_purchase_expense')
                ->delete();
        }
    }

    public function down(): void
    {
        // Mapping lama tidak dipulihkan karena akun biaya harus dipilih per transaksi.
    }
};
