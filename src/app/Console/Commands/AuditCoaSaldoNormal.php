<?php

namespace App\Console\Commands;

use App\Services\CoaSaldoNormalAuditService;
use Illuminate\Console\Command;

class AuditCoaSaldoNormal extends Command
{
    protected $signature = 'app:audit-coa-saldo-normal
        {--fix : Update saldo normal akun yang tidak sesuai rekomendasi}';

    protected $description = 'Audit dan opsional koreksi saldo normal COA berdasarkan tipe akun.';

    public function handle(CoaSaldoNormalAuditService $auditService): int
    {
        $suspiciousRows = $auditService->suspiciousRows();

        if ($suspiciousRows->isEmpty()) {
            $this->info('Semua saldo normal COA sudah sesuai rekomendasi.');
            return self::SUCCESS;
        }

        $this->warn($suspiciousRows->count() . ' akun perlu dicek/dikoreksi.');
        $this->table(
            ['ID', 'Kode', 'Nama Akun', 'Tipe', 'Kategori', 'Sekarang', 'Rekomendasi', 'Alasan'],
            $suspiciousRows->map(fn(array $row) => [
                $row['id'],
                $row['kode'],
                $row['nama'],
                $row['tipe'],
                $row['kategori'],
                $row['sekarang'],
                $row['rekomendasi'],
                $row['alasan'],
            ])->all()
        );

        if (! $this->option('fix')) {
            $this->line('Jalankan dengan --fix untuk menerapkan rekomendasi ini.');
            return self::SUCCESS;
        }

        $auditService->fix();

        $this->info($suspiciousRows->count() . ' akun berhasil dikoreksi.');

        return self::SUCCESS;
    }
}
