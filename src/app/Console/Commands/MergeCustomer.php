<?php

namespace App\Console\Commands;

use App\Services\PeriodLockService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MergeCustomer extends Command
{
    protected $signature = 'customer:merge {from : Kode customer duplikat/salah} {to : Kode customer master yang benar} {--execute : Jalankan perubahan. Tanpa opsi ini hanya preview.}';

    protected $description = 'Merge customer duplicate codes into one master customer code';

    public function handle(): int
    {
        $from = mb_strtoupper(trim((string) $this->argument('from')));
        $to = mb_strtoupper(trim((string) $this->argument('to')));

        if ($from === '' || $to === '' || $from === $to) {
            $this->error('Kode asal dan tujuan wajib diisi dan harus berbeda.');

            return self::FAILURE;
        }

        $source = DB::table('mcustomer')->where('CUSTOMER', $from)->first();
        $target = DB::table('mcustomer')->where('CUSTOMER', $to)->first();

        if (! $source) {
            $this->error("Customer asal {$from} tidak ditemukan.");

            return self::FAILURE;
        }

        if (! $target) {
            $this->error("Customer tujuan {$to} tidak ditemukan.");

            return self::FAILURE;
        }

        $tables = [
            ['table' => 'arh', 'column' => 'CUSTOMER'],
            ['table' => 'barh', 'column' => 'CUSTOMER'],
            ['table' => 't_penjualan_hosting', 'column' => 'customer'],
        ];

        $counts = collect($tables)
            ->filter(fn (array $ref): bool => Schema::hasTable($ref['table']) && Schema::hasColumn($ref['table'], $ref['column']))
            ->map(function (array $ref) use ($from): array {
                $ref['count'] = DB::table($ref['table'])->where($ref['column'], $from)->count();

                return $ref;
            })
            ->values();

        $this->info("Merge customer: {$from} -> {$to}");
        $this->line('Nama asal   : '.($source->NAMACUST ?? '-'));
        $this->line('Nama tujuan : '.($target->NAMACUST ?? '-'));
        $counts->each(fn (array $ref) => $this->line("{$ref['table']}.{$ref['column']}: {$ref['count']} baris"));

        $lockedMessage = $this->lockedMergeMessage($from);
        if ($lockedMessage) {
            $this->error($lockedMessage);

            return self::FAILURE;
        }

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk menjalankan merge.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($from, $to, $source, $target, $counts): void {
            foreach ($counts as $ref) {
                DB::table($ref['table'])->where($ref['column'], $from)->update([$ref['column'] => $to]);
            }

            $updates = ['updated_at' => now()];
            if (trim((string) ($target->NAMACUST ?? '')) === '' && trim((string) ($source->NAMACUST ?? '')) !== '') {
                $updates['NAMACUST'] = trim((string) $source->NAMACUST);
            }
            if (trim((string) ($target->ALAMAT1 ?? '')) === '' && trim((string) ($source->ALAMAT1 ?? '')) !== '') {
                $updates['ALAMAT1'] = trim((string) $source->ALAMAT1);
            }

            DB::table('mcustomer')->where('CUSTOMER', $to)->update($updates);
            DB::table('mcustomer')->where('CUSTOMER', $from)->delete();
        });

        $this->info('Merge selesai.');

        return self::SUCCESS;
    }

    private function lockedMergeMessage(string $customerCode): ?string
    {
        $locks = app(PeriodLockService::class);
        $checks = [
            ['table' => 'arh', 'column' => 'CUSTOMER', 'date' => 'TGLFAKTUR', 'label' => 'Piutang'],
            ['table' => 'barh', 'column' => 'CUSTOMER', 'date' => 'TGLBAYAR', 'label' => 'Pelunasan'],
            ['table' => 't_penjualan_hosting', 'column' => 'customer', 'date' => 'tglinvoice', 'label' => 'Sinkron Hosting'],
            ['table' => 't_penjualan_hosting', 'column' => 'customer', 'date' => 'tgl_kirim', 'label' => 'Sinkron Hosting'],
        ];

        foreach ($checks as $check) {
            if (! Schema::hasTable($check['table']) || ! Schema::hasColumn($check['table'], $check['column']) || ! Schema::hasColumn($check['table'], $check['date'])) {
                continue;
            }

            $dates = DB::table($check['table'])
                ->where($check['column'], $customerCode)
                ->whereNotNull($check['date'])
                ->distinct()
                ->pluck($check['date']);

            foreach ($dates as $date) {
                if ($locks->isLocked($date)) {
                    return "Merge dibatalkan. {$check['label']} customer {$customerCode} memiliki transaksi pada periode terkunci. ".$locks->lockedMessage($date);
                }
            }
        }

        return null;
    }
}
