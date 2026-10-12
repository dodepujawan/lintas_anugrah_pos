<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class JournalService
{
    /**
     * Creates a new journal entry with multiple items.
     *
     * @param string $noJurnal The journal number.
     * @param string $tanggal The date of the journal entry (Y-m-d).
     * @param string $keterangan A description for the journal.
     * @param array $items An array of journal items, each with 'coa_id', 'debit', 'kredit', 'deskripsi'.
     * @param string $referensi A reference number (optional).
     * @param int|null $createdBy The ID of the user creating the journal (optional, defaults to 1 if null).
     * @param string $moduleType The module type (e.g., 'Pelunasan Piutang', 'Pembelian').
     * @param string $tipe The journal type (e.g., 'Jurnal Umum', 'Pelunasan Piutang').
     * @return Jurnal The created Jurnal model.
     * @throws InvalidArgumentException If total debit does not equal total credit.
     */
    public function createJournal(
        string $noJurnal,
        string $tanggal,
        string $keterangan,
        array $items,
        string $referensi = null,
        ?int $createdBy = null,
        string $moduleType = 'Jurnal Umum',
        string $tipe = 'Jurnal Umum'
    ): Jurnal {
        $totalDebit = array_sum(array_column($items, 'debit'));
        $totalKredit = array_sum(array_column($items, 'kredit'));

        if (abs($totalDebit - $totalKredit) > 0.01) { // Allow for minor floating point discrepancies
            throw new InvalidArgumentException("Total debit ({$totalDebit}) does not equal total credit ({$totalKredit}). Journal cannot be created.");
        }

        return DB::transaction(function () use (
            $noJurnal,
            $tanggal,
            $keterangan,
            $items,
            $referensi,
            $createdBy,
            $moduleType,
            $tipe,
            $totalDebit,
            $totalKredit
        ) {
            $journal = Jurnal::create([
                'no_jurnal' => $noJurnal,
                'tanggal' => $tanggal,
                'status' => Jurnal::STATUS_POSTED, // Assuming automatic journals are always posted
                'keterangan' => $keterangan,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'referensi' => $referensi,
                'created_by' => $createdBy ?? (auth()->id() ?? User::first()->id ?? 1), // Fallback to first user or 1
                'is_posted' => true,
                'posted_at' => now(),
                'module_type' => $moduleType,
                'tipe' => $tipe,
            ]);

            foreach ($items as $itemData) {
                $journal->items()->create([
                    'coa_id' => $itemData['coa_id'],
                    'deskripsi' => $itemData['deskripsi'],
                    'debit' => $itemData['debit'],
                    'kredit' => $itemData['kredit'],
                ]);
            }

            return $journal;
        });
    }

    public function generateJournalNumber(string $prefix, string $date): string
    {
        $datePart = \Carbon\Carbon::parse($date)->format('Ym');
        $latest = Jurnal::where('no_jurnal', 'like', "{$prefix}{$datePart}%")
            ->orderBy('no_jurnal', 'desc')
            ->first();

        $sequence = 1;
        if ($latest) {
            $lastNumber = (int) substr($latest->no_jurnal, -6);
            $sequence = $lastNumber + 1;
        }

        return "{$prefix}{$datePart}" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }
}
