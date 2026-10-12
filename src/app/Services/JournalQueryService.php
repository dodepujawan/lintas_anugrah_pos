<?php

namespace App\Services;

use App\Models\JurnalItem;
use Illuminate\Database\Eloquent\Builder;

class JournalQueryService
{
    /**
     * Ambil query builder gabungan data aktif dan arsip.
     *
     * @param string $startDate Format: YYYY-MM-DD
     * @param string $endDate Format: YYYY-MM-DD
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getSmartQuery(string $startDate, string $endDate): Builder
    {
        return JurnalItem::withArchived($startDate, $endDate)
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('jurnals')
                    ->whereColumn('jurnals.id', 'combined_items.jurnal_id')
                    ->where('jurnals.is_posted', true);
            });
    }

    public function getSmartQueryExcludingClosing(string $startDate, string $endDate): Builder
    {
        return $this->getSmartQuery($startDate, $endDate)
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('jurnals')
                    ->whereColumn('jurnals.id', 'combined_items.jurnal_id')
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
                    });
            });
    }

    /**
     * Ambil data jurnal dengan filter akun tertentu secara smart.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|array $coaIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getJournalsByCoaIds(string $startDate, string $endDate, int|array $coaIds)
    {
        return $this->getSmartQuery($startDate, $endDate)
            ->byCoaIds($coaIds)
            ->with('jurnal', 'coa')
            ->orderBy('jurnal_id')
            ->get();
    }

    /**
     * Hitung total debit/kredit untuk akun tertentu dalam periode.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $coaId
     * @return array ['debit' => float, 'kredit' => float]
     */
    public function getSumByCoaId(string $startDate, string $endDate, int $coaId): array
    {
        $query = $this->getSmartQuery($startDate, $endDate)
            ->where('coa_id', $coaId);

        $result = $query->selectRaw('SUM(CAST(debit AS DECIMAL(15,2))) as total_debit, SUM(CAST(kredit AS DECIMAL(15,2))) as total_kredit')
            ->first();

        return [
            'debit' => (float) ($result?->total_debit ?? 0),
            'kredit' => (float) ($result?->total_kredit ?? 0),
        ];
    }

    /**
     * Hitung total debit/kredit untuk beberapa akun dalam periode.
     *
     * @param string $startDate
     * @param string $endDate
     * @param array $coaIds
     * @return array ['coa_id' => ['debit' => float, 'kredit' => float]]
     */
    public function getSumByMultipleCoaIds(string $startDate, string $endDate, array $coaIds): array
    {
        return $this->sumByMultipleCoaIds($this->getSmartQuery($startDate, $endDate), $coaIds);
    }

    public function getSumByMultipleCoaIdsExcludingClosing(string $startDate, string $endDate, array $coaIds): array
    {
        return $this->sumByMultipleCoaIds($this->getSmartQueryExcludingClosing($startDate, $endDate), $coaIds);
    }

    private function sumByMultipleCoaIds(Builder $query, array $coaIds): array
    {
        $results = $query->whereIn('coa_id', $coaIds)
            ->selectRaw('coa_id, SUM(CAST(debit AS DECIMAL(15,2))) as total_debit, SUM(CAST(kredit AS DECIMAL(15,2))) as total_kredit')
            ->groupBy('coa_id')
            ->get();

        $output = [];
        foreach ($coaIds as $coaId) {
            $result = $results->firstWhere('coa_id', $coaId);
            $output[$coaId] = [
                'debit' => (float) ($result?->total_debit ?? 0),
                'kredit' => (float) ($result?->total_kredit ?? 0),
            ];
        }

        return $output;
    }

    /**
     * Item buku besar untuk satu akun, diurutkan tanggal jurnal terkecil dulu.
     */
    public function getLedgerItemsForCoa(string $startDate, string $endDate, int $coaId)
    {
        return $this->getSmartQuery($startDate, $endDate)
            ->where('coa_id', $coaId)
            ->with(['jurnal', 'coa'])
            ->join('jurnals', 'jurnals.id', '=', 'combined_items.jurnal_id')
            ->orderBy('jurnals.tanggal')
            ->orderBy('jurnals.id')
            ->orderBy('combined_items.id')
            ->select('combined_items.*')
            ->get();
    }

    /**
     * Dapatkan informasi tentang data yang akan diquery (untuk logging/debug).
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getQueryInfo(string $startDate, string $endDate): array
    {
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_within_active_range' => false,
            'tables_used' => ['jurnal_items', 'jurnal_items_archives'],
            'active_data_threshold_years' => null,
            'cutoff_date' => null,
        ];
    }
}
