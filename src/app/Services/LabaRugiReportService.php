<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\JurnalItem;
use App\Models\JurnalItemArchive;
use Illuminate\Support\Collection;

class LabaRugiReportService
{
    public function build(string $tanggalAwal, string $tanggalAkhir): array
    {
        $pendapatans = Coa::active()->posting()->byTipe('Pendapatan')->orderBy('kode_akun')->get();
        $bebans = Coa::active()->posting()->byTipe('Beban')->orderBy('kode_akun')->get();

        $pendapatanSaldo = $this->buildSaldoMap($pendapatans, 'Pendapatan', $tanggalAwal, $tanggalAkhir);
        $bebanSaldo = $this->buildSaldoMap($bebans, 'Beban', $tanggalAwal, $tanggalAkhir);

        $totalPendapatan = array_sum($pendapatanSaldo);
        $totalBeban = $bebans->reduce(function ($carry, $coa) use ($bebanSaldo) {
            if ($this->isPajakPenghasilanAccount($coa)) {
                return $carry;
            }

            return $carry + ($bebanSaldo[$coa->id] ?? 0);
        }, 0.0);

        $taksiranPajakPenghasilan = $bebans->reduce(function ($carry, $coa) use ($bebanSaldo) {
            if (! $this->isPajakPenghasilanAccount($coa)) {
                return $carry;
            }

            return $carry + ($bebanSaldo[$coa->id] ?? 0);
        }, 0.0);

        $labaSebelumPajak = $totalPendapatan - $totalBeban;
        $labaBersih = $labaSebelumPajak - $taksiranPajakPenghasilan;

        return [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'pendapatans' => $pendapatans,
            'bebans' => $bebans,
            'pendapatanSaldo' => $pendapatanSaldo,
            'bebanSaldo' => $bebanSaldo,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'taksiranPajakPenghasilan' => $taksiranPajakPenghasilan,
            'labaSebelumPajak' => $labaSebelumPajak,
            'labaBersih' => $labaBersih,
        ];
    }

    private function buildSaldoMap(Collection $coas, string $tipe, string $tanggalAwal, string $tanggalAkhir): array
    {
        return $coas->mapWithKeys(function ($coa) use ($tipe, $tanggalAwal, $tanggalAkhir) {
            return [$coa->id => $this->hitungSaldoAkunLaba($coa->id, $tipe, $tanggalAwal, $tanggalAkhir)];
        })->toArray();
    }

    private function hitungSaldoAkunLaba(int $coaId, string $tipe, string $tanggalAwal, string $tanggalAkhir): float
    {
        $totals = $this->sumJournalItems($coaId, $tanggalAwal, $tanggalAkhir);

        return $tipe === 'Pendapatan'
            ? $totals['kredit'] - $totals['debit']
            : $totals['debit'] - $totals['kredit'];
    }

    private function sumJournalItems(int $coaId, string $tanggalAwal, string $tanggalAkhir): array
    {
        $debit = 0;
        $kredit = 0;

        foreach ([JurnalItem::query(), JurnalItemArchive::query()] as $query) {
            $totals = $query
                ->where('coa_id', $coaId)
                ->whereHas('jurnal', fn($query) => $query->where('is_posted', true)
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
                    ->whereDate('tanggal', '>=', $tanggalAwal)
                    ->whereDate('tanggal', '<=', $tanggalAkhir))
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

    private function isPajakPenghasilanAccount(Coa $coa): bool
    {
        $category = strtolower(trim((string) $coa->kategori));
        $name = strtolower(trim((string) $coa->nama_akun));

        return $category === 'pajak penghasilan'
            || str_contains($name, 'pajak penghasilan');
    }
}
