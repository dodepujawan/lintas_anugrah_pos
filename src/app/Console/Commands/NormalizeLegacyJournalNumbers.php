<?php

namespace App\Console\Commands;

use App\Models\Jurnal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeLegacyJournalNumbers extends Command
{
    protected $signature = 'app:normalize-legacy-journal-numbers
        {--execute : Simpan perubahan ke database}
        {--prefix=JU : Prefix jurnal yang dinormalisasi}
        {--only= : Nomor jurnal lama tertentu, contoh JU260702001}';

    protected $description = 'Normalize legacy journal numbers from PREFIXyymmdd### to PREFIXYYYYMM###### and update text references safely.';

    public function handle(): int
    {
        $prefix = strtoupper(trim((string) $this->option('prefix')));
        $only = strtoupper(trim((string) $this->option('only')));
        $execute = (bool) $this->option('execute');

        if ($prefix === '' || ! preg_match('/^[A-Z]+$/', $prefix)) {
            $this->error('Prefix hanya boleh huruf, contoh: JU, PJ, AR, AP.');
            return self::FAILURE;
        }

        $journals = Jurnal::query()
            ->where('no_jurnal', 'like', $prefix.'%')
            ->when($only !== '', fn ($query) => $query->where('no_jurnal', $only))
            ->orderBy('tanggal')
            ->orderBy('no_jurnal')
            ->get();

        $legacyPattern = '/^'.preg_quote($prefix, '/').'(\d{2})(\d{2})(\d{2})(\d{3})$/';
        $legacyJournals = $journals->filter(fn ($journal) => preg_match($legacyPattern, (string) $journal->no_jurnal))->values();

        if ($legacyJournals->isEmpty()) {
            $this->info($only !== ''
                ? "Tidak ditemukan nomor lama {$only}."
                : "Tidak ditemukan nomor lama dengan prefix {$prefix}.");
            return self::SUCCESS;
        }

        $plans = $this->buildPlans($legacyJournals, $prefix);

        $this->table(
            ['ID', 'Tanggal', 'Nomor Lama', 'Nomor Baru'],
            $plans->map(fn ($plan) => [
                $plan['id'],
                $plan['tanggal'],
                $plan['old'],
                $plan['new'],
            ])->all()
        );

        $duplicates = $plans->pluck('new')->duplicates()->unique()->values();
        if ($duplicates->isNotEmpty()) {
            $this->error('Batal: ada nomor baru duplikat di rencana: '.$duplicates->implode(', '));
            return self::FAILURE;
        }

        $existingNewNumbers = Jurnal::query()
            ->whereIn('no_jurnal', $plans->pluck('new')->all())
            ->whereNotIn('id', $plans->pluck('id')->all())
            ->pluck('no_jurnal');

        if ($existingNewNumbers->isNotEmpty()) {
            $this->error('Batal: nomor baru sudah dipakai: '.$existingNewNumbers->implode(', '));
            return self::FAILURE;
        }

        if (! $execute) {
            $this->warn('DRY RUN: belum ada data yang diubah.');
            $this->line('Jalankan dengan --execute jika daftar di atas sudah benar.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($plans) {
            foreach ($plans as $plan) {
                $journal = Jurnal::query()->whereKey($plan['id'])->lockForUpdate()->firstOrFail();

                if ($journal->no_jurnal !== $plan['old']) {
                    throw new \RuntimeException("Nomor jurnal berubah saat proses: {$plan['old']}.");
                }

                $journal->update(['no_jurnal' => $plan['new']]);
                $this->updateTextReferences($plan['old'], $plan['new']);
            }
        });

        $this->info('Selesai. Nomor jurnal lama dan referensi teks terkait sudah dinormalisasi.');

        return self::SUCCESS;
    }

    private function buildPlans($legacyJournals, string $prefix)
    {
        $counters = [];

        return $legacyJournals->map(function (Jurnal $journal) use ($prefix, &$counters) {
            $date = $journal->tanggal instanceof Carbon
                ? $journal->tanggal
                : Carbon::parse($journal->tanggal);
            $newPrefix = $prefix.$date->format('Ym');

            if (! array_key_exists($newPrefix, $counters)) {
                $latest = Jurnal::query()
                    ->where('no_jurnal', 'like', $newPrefix.'%')
                    ->orderByDesc('no_jurnal')
                    ->value('no_jurnal');

                $counters[$newPrefix] = $latest ? (int) substr($latest, -6) : 0;
            }

            $counters[$newPrefix]++;

            return [
                'id' => $journal->id,
                'tanggal' => $date->format('Y-m-d'),
                'old' => $journal->no_jurnal,
                'new' => $newPrefix.str_pad((string) $counters[$newPrefix], 6, '0', STR_PAD_LEFT),
            ];
        });
    }

    private function updateTextReferences(string $oldNumber, string $newNumber): void
    {
        foreach ($this->referenceColumns() as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->where($column, $oldNumber)
                ->update([$column => $newNumber]);
        }
    }

    private function referenceColumns(): array
    {
        return [
            ['arh', 'NOJURNAL'],
            ['aph', 'NOJURNAL'],
            ['barh', 'NOJURNAL'],
            ['barh', 'NOJURNAL_PEMBALIK'],
            ['baph', 'NOJURNAL'],
            ['baph', 'NOJURNAL_PEMBALIK'],
            ['t_penjualan_hosting', 'no_jurnal'],
        ];
    }
}
