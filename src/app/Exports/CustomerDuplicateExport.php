<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerDuplicateExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private readonly Collection $groups) {}

    public function collection(): Collection
    {
        return $this->groups->flatMap(function (array $group, int $index): Collection {
            return $group['customers']->map(function (array $row, int $rowIndex) use ($group, $index): array {
                $summary = collect($row['transaction_summary'])
                    ->map(fn (array $item): string => $item['label'].' '.$item['count'].($item['samples'] ? ': '.$item['samples'] : ''))
                    ->implode(' | ');

                return [
                    $index + 1,
                    $group['name'],
                    $row['code'],
                    $row['name'],
                    $row['address'],
                    $row['city'],
                    $row['counts']['arh'] ?? 0,
                    $row['counts']['barh'] ?? 0,
                    $row['counts']['t_penjualan_hosting'] ?? 0,
                    $summary ?: 'Belum ada transaksi',
                    $rowIndex === 0 ? 'Pakai default' : 'Kandidat hapus',
                ];
            });
        })->values();
    }

    public function headings(): array
    {
        return [
            'Grup',
            'Nama Grup',
            'Kode Customer',
            'Nama Customer',
            'Alamat',
            'Kota',
            'Piutang',
            'Pelunasan',
            'Hosting',
            'Keterangan Transaksi',
            'Rekomendasi',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('J:J')->getAlignment()->setWrapText(true);
        $sheet->freezePane('A2');

        return [];
    }
}
