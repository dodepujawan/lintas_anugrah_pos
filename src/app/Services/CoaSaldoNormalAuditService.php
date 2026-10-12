<?php

namespace App\Services;

use App\Models\Coa;
use Illuminate\Support\Collection;

class CoaSaldoNormalAuditService
{
    public function audit(): Collection
    {
        return Coa::query()
            ->orderBy('kode_akun')
            ->get()
            ->map(function (Coa $coa): array {
                $recommendation = $this->recommendedSaldoNormal($coa);

                return [
                    'id' => $coa->id,
                    'kode' => $coa->kode_akun,
                    'nama' => $coa->nama_akun,
                    'tipe' => $coa->tipe,
                    'kategori' => $coa->kategori ?? '-',
                    'sekarang' => $coa->saldo_normal,
                    'rekomendasi' => $recommendation['saldo_normal'],
                    'alasan' => $recommendation['reason'],
                    'perlu_update' => $coa->saldo_normal !== $recommendation['saldo_normal'],
                ];
            });
    }

    public function suspiciousRows(): Collection
    {
        return $this->audit()->where('perlu_update', true)->values();
    }

    public function fix(): Collection
    {
        $rows = $this->suspiciousRows();

        foreach ($rows as $row) {
            Coa::whereKey($row['id'])->update([
                'saldo_normal' => $row['rekomendasi'],
            ]);
        }

        return $rows;
    }

    private function recommendedSaldoNormal(Coa $coa): array
    {
        $name = strtolower((string) $coa->nama_akun);
        $category = strtolower((string) $coa->kategori);

        if ($coa->tipe === 'Aset' && $this->looksLikeContraAsset($name, $category)) {
            return [
                'saldo_normal' => 'Kredit',
                'reason' => 'Kontra aset',
            ];
        }

        if (in_array($coa->tipe, ['Kewajiban', 'Pendapatan'], true)) {
            return [
                'saldo_normal' => 'Kredit',
                'reason' => $coa->tipe . ' normalnya kredit',
            ];
        }

        if ($coa->tipe === 'Ekuitas') {
            if ($this->looksLikeContraEquity($name, $category)) {
                return [
                    'saldo_normal' => 'Debit',
                    'reason' => 'Prive/dividen/deviden adalah kontra ekuitas',
                ];
            }

            return [
                'saldo_normal' => 'Kredit',
                'reason' => 'Ekuitas normalnya kredit',
            ];
        }

        return [
            'saldo_normal' => 'Debit',
            'reason' => $coa->tipe . ' normalnya debit',
        ];
    }

    private function looksLikeContraAsset(string $name, string $category): bool
    {
        $text = $name . ' ' . $category;

        return str_contains($text, 'akumulasi penyusutan')
            || str_contains($text, 'penyisihan')
            || str_contains($text, 'cadangan kerugian');
    }

    private function looksLikeContraEquity(string $name, string $category): bool
    {
        $text = $name . ' ' . $category;

        return str_contains($text, 'prive')
            || str_contains($text, 'dividen')
            || str_contains($text, 'deviden');
    }
}
