<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('jurnals')
            || ! Schema::hasTable('jurnal_items')
            || ! Schema::hasTable('jurnal_items_archives')
            || ! Schema::hasTable('account_mappings')
        ) {
            return;
        }

        $currentYearProfitId = DB::table('account_mappings')
            ->where('mapping_key', 'current_year_profit')
            ->value('account_id');

        if (! $currentYearProfitId) {
            return;
        }

        $journals = DB::table('jurnals')
            ->where(function ($query): void {
                $query->where('referensi', 'AUTO-CLOSING')
                    ->orWhere('keterangan', 'like', '%Jurnal Tutup%')
                    ->orWhere('no_jurnal', 'like', 'TJ-%');
            })
            ->pluck('id');

        foreach ($journals as $journalId) {
            foreach (['jurnal_items', 'jurnal_items_archives'] as $table) {
                $this->repairClosingItems((int) $journalId, $table, (int) $currentYearProfitId);
            }

            $this->refreshJournalTotals((int) $journalId);
        }
    }

    public function down(): void
    {
        //
    }

    private function repairClosingItems(int $journalId, string $table, int $currentYearProfitId): void
    {
        $closedRevenue = (float) DB::table($table)
            ->where('jurnal_id', $journalId)
            ->where('deskripsi', 'Penutupan akun pendapatan')
            ->sum('debit');

        $postedRevenueToProfit = (float) DB::table($table)
            ->where('jurnal_id', $journalId)
            ->where('coa_id', $currentYearProfitId)
            ->where('deskripsi', 'Penutupan akun pendapatan ke laba tahun berjalan')
            ->sum('kredit');

        $missingRevenueToProfit = round($closedRevenue - $postedRevenueToProfit, 2);
        if ($missingRevenueToProfit > 0) {
            DB::table($table)->insert([
                'jurnal_id' => $journalId,
                'coa_id' => $currentYearProfitId,
                'deskripsi' => 'Penutupan akun pendapatan ke laba tahun berjalan',
                'debit' => 0,
                'kredit' => $missingRevenueToProfit,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $closedExpense = (float) DB::table($table)
            ->where('jurnal_id', $journalId)
            ->where('deskripsi', 'Penutupan akun beban')
            ->sum('kredit');

        $postedExpenseFromProfit = (float) DB::table($table)
            ->where('jurnal_id', $journalId)
            ->where('coa_id', $currentYearProfitId)
            ->where('deskripsi', 'Penutupan akun beban dari laba tahun berjalan')
            ->sum('debit');

        $missingExpenseFromProfit = round($closedExpense - $postedExpenseFromProfit, 2);
        if ($missingExpenseFromProfit > 0) {
            DB::table($table)->insert([
                'jurnal_id' => $journalId,
                'coa_id' => $currentYearProfitId,
                'deskripsi' => 'Penutupan akun beban dari laba tahun berjalan',
                'debit' => $missingExpenseFromProfit,
                'kredit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function refreshJournalTotals(int $journalId): void
    {
        $totalDebit = 0.0;
        $totalKredit = 0.0;

        foreach (['jurnal_items', 'jurnal_items_archives'] as $table) {
            $totals = DB::table($table)
                ->where('jurnal_id', $journalId)
                ->selectRaw('COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(kredit), 0) as kredit')
                ->first();

            $totalDebit += (float) $totals->debit;
            $totalKredit += (float) $totals->kredit;
        }

        DB::table('jurnals')
            ->where('id', $journalId)
            ->update([
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'updated_at' => now(),
            ]);
    }
};
