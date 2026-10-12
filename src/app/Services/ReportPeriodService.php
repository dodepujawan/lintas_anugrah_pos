<?php

namespace App\Services;

use Carbon\Carbon;

class ReportPeriodService
{
    public const MONTH_NAMES = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];

    public function monthYearLabel(string|int|null $month, string|int|null $year, ?string $fallbackDate = null): string
    {
        $date = $fallbackDate ? Carbon::parse($fallbackDate) : now();
        $periodMonth = str_pad((string) ($month ?: $date->format('m')), 2, '0', STR_PAD_LEFT);
        $periodYear = (string) ($year ?: $date->format('Y'));

        return strtoupper((self::MONTH_NAMES[$periodMonth] ?? $date->format('F')) . ' ' . $periodYear);
    }

    public function rangeLabel(string $tanggalAwal, string $tanggalAkhir): string
    {
        return date('d F Y', strtotime($tanggalAwal)) . ' - ' . date('d F Y', strtotime($tanggalAkhir));
    }
}
