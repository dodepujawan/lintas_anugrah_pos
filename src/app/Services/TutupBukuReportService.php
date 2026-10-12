<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\JurnalItem;
use App\Models\JurnalItemArchive;
use App\Models\AccountMapping;
use Carbon\Carbon;

class TutupBukuReportService
{
    public function build(string $tanggalAwal, string $tanggalAkhir): array
    {
        $pendapatans = Coa::active()->posting()->byTipe('Pendapatan')->orderBy('kode_akun')->get();
        $bebans = Coa::active()->posting()->byTipe('Beban')->orderBy('kode_akun')->get();

        $pendapatanRows = collect();
        $totalPendapatan = 0;
        foreach ($pendapatans as $coa) {
            $amount = $this->calculateAccountPeriodBalance($coa, $tanggalAwal, $tanggalAkhir);
            $pendapatanRows->push([
                'coa' => $coa,
                'amount' => $amount,
            ]);
            $totalPendapatan += $amount;
        }

        $bebanRows = collect();
        $totalBeban = 0;
        $taksiranPajakPenghasilan = 0;
        foreach ($bebans as $coa) {
            $amount = $this->calculateAccountPeriodBalance($coa, $tanggalAwal, $tanggalAkhir);
            $bebanRows->push([
                'coa' => $coa,
                'amount' => $amount,
            ]);
            if ($this->isPajakPenghasilanAccount($coa)) {
                $taksiranPajakPenghasilan += $amount;
            } else {
                $totalBeban += $amount;
            }
        }

        $labaSebelumPajak = $totalPendapatan - $totalBeban;
        $labaBersih = $labaSebelumPajak - $taksiranPajakPenghasilan;

        $mappingKeys = [
            'cash_main',
            'equity_capital',
            'retained_earnings',
            'current_year_profit',
            'prive',
        ];

        $mappingAccounts = array_fill_keys($mappingKeys, null);
        $loadedMappings = AccountMapping::whereIn('mapping_key', $mappingKeys)
            ->with('account')
            ->get()
            ->filter(fn(AccountMapping $mapping) => $mapping->account !== null)
            ->mapWithKeys(fn(AccountMapping $mapping) => [$mapping->mapping_key => $mapping->account])
            ->all();

        $mappingAccounts = array_merge($mappingAccounts, $loadedMappings);

        $saldoAwalLabaDitahan = 0;
        $saldoAkhirLabaDitahanProyeksi = 0;
        if (! empty($mappingAccounts['retained_earnings'])) {
            $saldoAwalLabaDitahan = $this->calculateAccountBalanceUpToDate(
                $mappingAccounts['retained_earnings'],
                Carbon::parse($tanggalAwal)->subDay()->format('Y-m-d')
            );
            $saldoAkhirLabaDitahanProyeksi = $saldoAwalLabaDitahan + $labaBersih;
        }

        return [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'pendapatanRows' => $pendapatanRows,
            'bebanRows' => $bebanRows,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'taksiranPajakPenghasilan' => $taksiranPajakPenghasilan,
            'labaSebelumPajak' => $labaSebelumPajak,
            'labaBersih' => $labaBersih,
            'mappingAccounts' => $mappingAccounts,
            'saldoAwalLabaDitahan' => $saldoAwalLabaDitahan,
            'saldoAkhirLabaDitahanProyeksi' => $saldoAkhirLabaDitahanProyeksi,
        ];
    }

    private function calculateAccountPeriodBalance(Coa $coa, string $tanggalAwal, string $tanggalAkhir): float
    {
        $totals = $this->sumJournalItems($coa->id, $tanggalAwal, $tanggalAkhir);

        return $this->normalizeProfitLossBalance($coa, $totals['debit'], $totals['kredit']);
    }

    private function calculateAccountBalanceUpToDate(Coa $coa, string $toDate): float
    {
        $totals = $this->sumJournalItems($coa->id, null, $toDate);

        return $this->normalizeBalance($coa, $totals['debit'], $totals['kredit']);
    }

    private function sumJournalItems(int $coaId, ?string $tanggalAwal, string $tanggalAkhir): array
    {
        $debit = 0;
        $kredit = 0;

        foreach ([JurnalItem::query(), JurnalItemArchive::query()] as $query) {
            $totals = $query
                ->where('coa_id', $coaId)
                ->whereHas('jurnal', function ($query) use ($tanggalAwal, $tanggalAkhir): void {
                    $query->where('is_posted', true)
                        ->where(function ($query): void {
                            $query->whereNull('referensi')
                                ->orWhere('referensi', '!=', 'AUTO-CLOSING');
                        })
                        ->where(function ($query): void {
                            $query->whereNull('keterangan')
                                ->orWhere('keterangan', 'not like', '%Jurnal Tutup%');
                        })
                        ->where(function ($query): void {
                            $query->whereNull('no_jurnal')
                                ->orWhere('no_jurnal', 'not like', 'TJ%');
                        })
                        ->whereDate('tanggal', '<=', $tanggalAkhir);

                    if ($tanggalAwal !== null) {
                        $query->whereDate('tanggal', '>=', $tanggalAwal);
                    }
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(kredit), 0) as kredit')
                ->first();

            $debit += (float) $totals->debit;
            $kredit += (float) $totals->kredit;
        }

        return [
            'debit' => $debit,
            'kredit' => $kredit,
        ];
    }

    private function normalizeBalance(Coa $coa, float $debit, float $kredit): float
    {
        if (strtolower((string) $coa->saldo_normal) === 'kredit') {
            return $kredit - $debit;
        }

        return $debit - $kredit;
    }

    private function normalizeProfitLossBalance(Coa $coa, float $debit, float $kredit): float
    {
        return $coa->tipe === 'Pendapatan'
            ? $kredit - $debit
            : $debit - $kredit;
    }

    private function isPajakPenghasilanAccount(Coa $coa): bool
    {
        $category = strtolower(trim((string) $coa->kategori));
        $name = strtolower(trim((string) $coa->nama_akun));

        return $category === 'pajak penghasilan'
            || str_contains($name, 'pajak penghasilan');
    }
}
