<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'sales_revenue_eks' => '04010101',
            'sales_revenue_ren' => '04010201',
            'sales_revenue_cold_room' => '04010202',
        ];

        foreach ($defaults as $mappingKey => $accountCode) {
            if (DB::table('account_mappings')->where('mapping_key', $mappingKey)->exists()) {
                continue;
            }

            $accountId = DB::table('coas')
                ->where('kode_akun', $accountCode)
                ->where('tipe', 'Pendapatan')
                ->where('level', 'Akun Posting')
                ->where('is_active', true)
                ->value('id');

            if ($accountId) {
                DB::table('account_mappings')->insert([
                    'mapping_key' => $mappingKey,
                    'account_id' => $accountId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Mapping tidak dihapus saat rollback agar pilihan akun user tetap aman.
    }
};
