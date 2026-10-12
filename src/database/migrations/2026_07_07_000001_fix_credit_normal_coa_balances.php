<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('coas')
            ->whereIn('tipe', ['Kewajiban', 'Pendapatan'])
            ->update(['saldo_normal' => 'Kredit']);

        DB::table('coas')
            ->where('tipe', 'Ekuitas')
            ->where(function ($query): void {
                $query->whereNull('kategori')
                    ->orWhere(function ($query): void {
                        $query->where('kategori', 'not like', '%Prive%')
                            ->where('kategori', 'not like', '%Dividen%')
                            ->where('kategori', 'not like', '%Deviden%');
                    });
            })
            ->where('nama_akun', 'not like', '%Prive%')
            ->where('nama_akun', 'not like', '%Dividen%')
            ->where('nama_akun', 'not like', '%Deviden%')
            ->update(['saldo_normal' => 'Kredit']);

        DB::table('coas')
            ->where('tipe', 'Ekuitas')
            ->where(function ($query): void {
                $query->where('kategori', 'like', '%Prive%')
                    ->orWhere('nama_akun', 'like', '%Prive%')
                    ->orWhere('kategori', 'like', '%Dividen%')
                    ->orWhere('nama_akun', 'like', '%Dividen%')
                    ->orWhere('kategori', 'like', '%Deviden%')
                    ->orWhere('nama_akun', 'like', '%Deviden%');
            })
            ->update(['saldo_normal' => 'Debit']);

        DB::table('coas')
            ->where('tipe', 'Aset')
            ->where('nama_akun', 'like', '%Akumulasi%Penyusutan%')
            ->update(['saldo_normal' => 'Kredit']);

        $mappedCreditAccountIds = DB::table('account_mappings')
            ->whereIn('mapping_key', ['retained_earnings', 'current_year_profit', 'equity_capital'])
            ->whereNotNull('account_id')
            ->pluck('account_id')
            ->all();

        if (! empty($mappedCreditAccountIds)) {
            DB::table('coas')
                ->whereIn('id', $mappedCreditAccountIds)
                ->update(['saldo_normal' => 'Kredit']);
        }
    }

    public function down(): void
    {
        //
    }
};
