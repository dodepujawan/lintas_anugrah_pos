<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerubahanEkuitasExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly array $report) {}

    public function collection(): Collection
    {
        return collect([
            ['Saldo Awal Laba Ditahan', $this->report['saldoAwal'] ?? 0],
            ['Laba/Rugi Bersih Periode', $this->report['labaBersih'] ?? 0],
            ['Koreksi/Dividen', $this->report['koreksi'] ?? 0],
            ['Saldo Akhir Ekuitas', $this->report['saldoAkhir'] ?? 0],
        ]);
    }

    public function headings(): array
    {
        return [
            ['LAPORAN PERUBAHAN EKUITAS'],
            ['Periode: ' . date('d F Y', strtotime($this->report['tanggalAwal'])) . ' - ' . date('d F Y', strtotime($this->report['tanggalAkhir'])) . ' | Status: ' . ($this->report['periodLockStatus'] ?? 'TERBUKA')],
            ['Keterangan', 'Jumlah'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A1:B3')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getStyle('A1:B2')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B4:B7')->getNumberFormat()->setFormatCode('#,##0.00');

        return [];
    }

    public function title(): string
    {
        return 'Perubahan Ekuitas';
    }
}
