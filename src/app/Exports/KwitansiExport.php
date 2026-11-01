<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KwitansiExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    private float $totalNilai = 0;

    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        $this->totalNilai = (float) $this->rows->sum('NILAI');

        return $this->rows->values()->map(fn ($row, $index) => [
            $index + 1,
            $row->JENIS ?? '',
            $row->NOKWT ?? '-',
            $row->INVOICE ?? '',
            $row->TGLINVOICE ? date('d-m-Y', strtotime($row->TGLINVOICE)) : '-',
            $row->KETERANGAN ?? '',
            (float) ($row->NILAI ?? 0),
        ]);
    }

    public function headings(): array
    {
        return ['No', 'Jenis', 'No Kwitansi', 'Invoice', 'Tanggal Invoice', 'Keterangan', 'Nilai'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E78']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet;
            $lastRow = $sheet->getHighestRow();
            $totalRow = $lastRow + 2;

            $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => 'thin']],
                'alignment' => ['vertical' => 'center', 'wrapText' => true],
            ]);
            $sheet->getStyle("G2:G{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue("F{$totalRow}", 'TOTAL');
            $sheet->setCellValue("G{$totalRow}", $this->totalNilai);
            $sheet->getStyle("F{$totalRow}:G{$totalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D9EAF7']],
                'borders' => ['allBorders' => ['borderStyle' => 'thin']],
            ]);
            $sheet->freezePane('A2');
        }];
    }

    public function title(): string
    {
        return 'Data Kwitansi';
    }
}
