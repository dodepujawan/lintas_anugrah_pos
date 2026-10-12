<?php

namespace App\Console\Commands;

use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\JurnalItemArchive;
use App\Models\YearClosing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ArchiveJournal extends Command
{
    protected $signature = 'app:archive-journal {year : Tahun jurnal yang akan diarsipkan}';

    protected $description = 'Pindahkan data jurnal_items ke jurnal_items_archives setelah tutup buku tahun yang ditentukan selesai.';

    public function handle(): int
    {
        $year = $this->argument('year');

        if (! preg_match('/^[0-9]{4}$/', $year)) {
            $this->error('Tahun tidak valid. Gunakan format YYYY.');
            return self::FAILURE;
        }

        if ((int) $year >= now()->year) {
            $this->error("Tahun {$year} masih berjalan atau berada di masa depan dan belum dapat diarsipkan.");
            return self::FAILURE;
        }

        if (! $this->isYearClosed($year)) {
            $this->error("Tutup Buku untuk tahun {$year} belum selesai. Proses dibatalkan.");
            return self::FAILURE;
        }

        $this->info("Memulai arsip jurnal untuk tahun {$year}...");

        DB::transaction(function () use ($year) {
            $journalIds = Jurnal::query()
                ->whereYear('tanggal', $year)
                ->where('is_posted', true)
                ->pluck('id');

            if ($journalIds->isEmpty()) {
                $this->info('Tidak ada jurnal yang sesuai untuk diarsipkan.');
                return;
            }

            $items = JurnalItem::query()
                ->whereIn('jurnal_id', $journalIds)
                ->get(['jurnal_id', 'coa_id', 'deskripsi', 'debit', 'kredit', 'created_at', 'updated_at']);

            $rows = $items->map(function (JurnalItem $item) {
                return [
                    'jurnal_id' => $item->jurnal_id,
                    'coa_id' => $item->coa_id,
                    'deskripsi' => $item->deskripsi,
                    'debit' => $item->debit,
                    'kredit' => $item->kredit,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            if (count($rows) > 0) {
                JurnalItemArchive::insert($rows);
                JurnalItem::query()->whereIn('jurnal_id', $journalIds)->delete();
            }

            $this->info(count($rows) . ' baris jurnal berhasil diarsipkan.');
        });

        $this->info('Proses arsip jurnal selesai.');

        return self::SUCCESS;
    }

    private function isYearClosed(string $year): bool
    {
        $yearClosing = YearClosing::where('year', $year)->first();

        if (! $yearClosing || ! $yearClosing->closed_at) {
            $this->warn("Tidak ada catatan penutupan untuk tahun {$year}.");
            $this->line('Periksa apakah Tutup Buku sudah dijalankan dan dicatat dengan benar.');
            return false;
        }

        return true;
    }
}
