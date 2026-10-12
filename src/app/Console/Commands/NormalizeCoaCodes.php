<?php

namespace App\Console\Commands;

use App\Models\Coa;
use Illuminate\Console\Command;

class NormalizeCoaCodes extends Command
{
    protected $signature = 'coa:normalize';

    protected $description = 'Normalize all COA codes to 8-digit numeric format';

    public function handle(): int
    {
        $updated = 0;

        Coa::query()->get()->each(function (Coa $coa) use (&$updated) {
            $digits = preg_replace('/\D/', '', (string) $coa->kode_akun);

            if (strlen($digits) === 5) {
                $digits .= '000';
            }

            $normalized = str_pad($digits, 8, '0', STR_PAD_LEFT);

            if ($coa->kode_akun !== $normalized) {
                $coa->kode_akun = $normalized;
                $coa->save();
                $updated++;
            }
        });

        $this->info("COA codes normalized: {$updated} akun diperbarui.");

        return self::SUCCESS;
    }
}
