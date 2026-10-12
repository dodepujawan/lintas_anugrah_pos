<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Coa;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('account_mappings')) {
            return;
        }

        // --- Piutang (Accounts Receivable) Mappings ---
        $piutangUsahaCoa = Coa::where('nama_akun', 'like', '%Piutang Usaha%')
            ->where('tipe', 'Aset')
            ->where('saldo_normal', 'Debit')
            ->first();

        if ($piutangUsahaCoa) {
            DB::table('account_mappings')->updateOrInsert(
                ['mapping_key' => 'accounts_receivable'],
                ['account_id' => $piutangUsahaCoa->id]
            );
        } else {
            echo "Warning: 'Piutang Usaha' COA not found for mapping 'accounts_receivable'.\n";
        }

        $kasCoa = Coa::where('nama_akun', 'like', '%Kas%')
            ->where('tipe', 'Aset')
            ->where('saldo_normal', 'Debit')
            ->first();

        if ($kasCoa) {
            DB::table('account_mappings')->updateOrInsert(
                ['mapping_key' => 'cash_account'],
                ['account_id' => $kasCoa->id]
            );
        } else {
            echo "Warning: 'Kas' COA not found for mapping 'cash_account'.\n";
        }

        $bankCoa = Coa::where('nama_akun', 'like', '%Bank%')
            ->where('tipe', 'Aset')
            ->where('saldo_normal', 'Debit')
            ->first();

        if ($bankCoa) {
            DB::table('account_mappings')->updateOrInsert(
                ['mapping_key' => 'bank_account'],
                ['account_id' => $bankCoa->id]
            );
        } else {
            echo "Warning: 'Bank' COA not found for mapping 'bank_account'.\n";
        }

        $salesRevenueCoa = Coa::where(function ($query) {
                $query->where('nama_akun', 'like', '%Pendapatan Penjualan%')
                    ->orWhere('nama_akun', 'like', '%Pendapatan Usaha%');
            })
            ->where('tipe', 'Pendapatan')
            ->where('saldo_normal', 'Kredit')
            ->first();

        if ($salesRevenueCoa) {
            DB::table('account_mappings')->updateOrInsert(
                ['mapping_key' => 'default_sales_revenue'],
                ['account_id' => $salesRevenueCoa->id]
            );
        } else {
            echo "Warning: sales revenue COA not found for mapping 'default_sales_revenue'.\n";
        }

        // --- Hutang (Accounts Payable) Mappings ---
        $hutangUsahaCoa = Coa::where(function ($query) {
                $query->where('nama_akun', 'like', '%Hutang Usaha%')
                    ->orWhere('nama_akun', 'like', '%Utang Usaha%');
            })
            ->where('tipe', 'Kewajiban')
            ->where('saldo_normal', 'Kredit')
            ->first();

        if ($hutangUsahaCoa) {
            DB::table('account_mappings')->updateOrInsert(
                ['mapping_key' => 'accounts_payable'],
                ['account_id' => $hutangUsahaCoa->id]
            );
        } else {
            echo "Warning: 'Hutang Usaha' COA not found for mapping 'accounts_payable'.\n";
        }

        $purchaseExpenseCoa = Coa::where('nama_akun', 'like', '%Pembelian%')
            ->where('tipe', 'Beban')
            ->where('saldo_normal', 'Debit')
            ->first();

        if (!$purchaseExpenseCoa) {
            $purchaseExpenseCoa = Coa::where('nama_akun', 'like', '%Beban Pokok Penjualan%')
                ->where('tipe', 'Beban')
                ->where('saldo_normal', 'Debit')
                ->first();
        }

        if ($purchaseExpenseCoa) {
            DB::table('account_mappings')->updateOrInsert(
                ['mapping_key' => 'default_purchase_expense'],
                ['account_id' => $purchaseExpenseCoa->id]
            );
        } else {
            echo "Warning: 'Pembelian' or 'Beban Pokok Penjualan' COA not found for mapping 'default_purchase_expense'.\n";
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('account_mappings')) {
            return;
        }

        DB::table('account_mappings')->whereIn('mapping_key', [
            'accounts_receivable',
            'cash_account',
            'bank_account',
            'default_sales_revenue',
            'accounts_payable',
            'default_purchase_expense',
        ])->delete();
    }
};
