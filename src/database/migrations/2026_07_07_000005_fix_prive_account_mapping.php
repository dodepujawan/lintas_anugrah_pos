<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('account_mappings') || ! Schema::hasTable('coas')) {
            return;
        }

        $current = DB::table('account_mappings')
            ->where('mapping_key', 'prive')
            ->first();

        if ($current && $this->isValidPriveAccount((int) $current->account_id)) {
            return;
        }

        $accountId = $this->findPriveAccountId();

        if (! $accountId) {
            return;
        }

        DB::table('account_mappings')->updateOrInsert(
            ['mapping_key' => 'prive'],
            ['account_id' => $accountId]
        );
    }

    public function down(): void
    {
        // Data repair only; do not guess the previous manual mapping.
    }

    private function isValidPriveAccount(int $accountId): bool
    {
        $query = DB::table('coas')
            ->where('id', $accountId)
            ->where('tipe', 'Ekuitas')
            ->where('saldo_normal', 'Debit');

        if (Schema::hasColumn('coas', 'is_active')) {
            $query->where('is_active', true);
        }

        if (Schema::hasColumn('coas', 'level')) {
            $query->where('level', 'Akun Posting');
        }

        $this->wherePriveAccountName($query);

        return $query->exists();
    }

    private function findPriveAccountId(): ?int
    {
        $query = DB::table('coas')
            ->where('tipe', 'Ekuitas')
            ->where('saldo_normal', 'Debit');

        if (Schema::hasColumn('coas', 'is_active')) {
            $query->where('is_active', true);
        }

        if (Schema::hasColumn('coas', 'level')) {
            $query->orderByRaw("CASE WHEN level = 'Akun Posting' THEN 0 ELSE 1 END");
        }

        $this->wherePriveAccountName($query);

        $accountId = $query
            ->orderByRaw("
                CASE
                    WHEN LOWER(nama_akun) LIKE '%prive%' THEN 0
                    WHEN LOWER(nama_akun) LIKE '%deviden%' THEN 1
                    WHEN LOWER(nama_akun) LIKE '%dividen%' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('kode_akun')
            ->value('id');

        return $accountId ? (int) $accountId : null;
    }

    private function wherePriveAccountName($query): void
    {
        $query->where(function ($query): void {
            $query->whereRaw('LOWER(nama_akun) LIKE ?', ['%prive%'])
                ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%dividen%'])
                ->orWhereRaw('LOWER(nama_akun) LIKE ?', ['%deviden%']);
        });
    }
};
