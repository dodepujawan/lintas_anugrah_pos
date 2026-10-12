<?php

namespace App\Console\Commands;

use App\Models\YearClosing;
use Illuminate\Console\Command;

class MarkYearClosed extends Command
{
    protected $signature = 'app:mark-year-closed {year : Tahun yang akan ditandai sebagai sudah ditutup}';

    protected $description = 'Tandai tutup buku untuk tahun tertentu agar dapat diarsipkan.';

    public function handle(): int
    {
        $year = $this->argument('year');

        if (! preg_match('/^[0-9]{4}$/', $year)) {
            $this->error('Tahun tidak valid. Gunakan format YYYY.');
            return self::FAILURE;
        }

        if ((int) $year >= now()->year) {
            $this->error("Tahun {$year} masih berjalan atau berada di masa depan.");
            return self::FAILURE;
        }

        $yearClosing = YearClosing::updateOrCreate(
            ['year' => $year],
            [
                'closed_at' => now(),
                'notes' => "Tutup buku ditandai pada " . now()->format('Y-m-d H:i:s'),
            ]
        );

        $this->info("✅ Tutup buku untuk tahun {$year} berhasil dicatat.");
        $this->info("Waktu: {$yearClosing->closed_at->format('Y-m-d H:i:s')}");
        $this->line('');
        $this->info("Sekarang Anda bisa menjalankan: php artisan app:archive-journal {$year}");

        return self::SUCCESS;
    }
}
