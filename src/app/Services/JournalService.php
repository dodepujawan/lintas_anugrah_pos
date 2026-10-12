<?php

namespace App\Services;

use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class JournalService
{
    private const VALID_JOURNAL_TYPES = [
        'Penerimaan Kas',
        'Pengeluaran Kas',
        'Jurnal Umum',
        'Penjualan',
        'Pembelian',
        'Pelunasan Piutang',
    ];

    public function createJournal(
        string $noJurnal,
        string $tanggal,
        string $keterangan,
        array $items,
        ?string $referensi = null,
        ?int $createdBy = null,
        string $moduleType = 'Jurnal Umum',
        string $tipe = 'Jurnal Umum'
    ): Jurnal {
        $totalDebit = array_sum(array_column($items, 'debit'));
        $totalKredit = array_sum(array_column($items, 'kredit'));

        if (abs($totalDebit - $totalKredit) > 0.01) {
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
                'status' => Jurnal::STATUS_POSTED,
                'keterangan' => $keterangan,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'referensi' => $referensi,
                'created_by' => $createdBy ?? (auth()->id() ?? User::first()->id ?? 1),
                'is_posted' => true,
                'posted_at' => now(),
                'module_type' => $moduleType,
                'tipe' => in_array($tipe, self::VALID_JOURNAL_TYPES, true) ? $tipe : 'Jurnal Umum',
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
            $sequence = ((int) substr($latest->no_jurnal, -6)) + 1;
        }

        return "{$prefix}{$datePart}" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }
}
