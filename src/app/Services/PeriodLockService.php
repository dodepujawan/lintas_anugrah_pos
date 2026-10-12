<?php

namespace App\Services;

use App\Models\YearClosing;
use Carbon\Carbon;

class PeriodLockService
{
    public function isLocked(string|\DateTimeInterface|null $date): bool
    {
        if (! $date) {
            return false;
        }

        return YearClosing::query()
            ->where('year', (string) Carbon::parse($date)->year)
            ->whereNotNull('closed_at')
            ->exists();
    }

    public function statusLabel(string|\DateTimeInterface|null $date): string
    {
        return $this->isLocked($date) ? 'TERKUNCI' : 'TERBUKA';
    }

    public function lockedMessage(string|\DateTimeInterface|null $date): string
    {
        $year = $date ? Carbon::parse($date)->year : now()->year;

        return "Periode tahun {$year} sudah dikunci. Transaksi pada tahun tersebut tidak dapat ditambah, diubah, diposting, di-void, atau dihapus.";
    }
}
