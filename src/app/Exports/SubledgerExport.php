<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubledgerExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly Collection $rows,
        private readonly string $reportTitle,
        private readonly string $partyLabel,
        private readonly string $positionDate,
    ) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn (array $row): array => [
            $row['invoice']->NOFAKTUR,
            $row['invoice']->TGLFAKTUR?->format('d/m/Y'),
            $row['invoice']->TGLJT?->format('d/m/Y'),
            $row['invoice']->CUSTOMER ?? $row['invoice']->SUPPLIER,
            $row['invoice_amount'] ?? $row['amount'],
            $row['paid'], $row['balance'], $row['age_label'], $row['status'],
        ]);
    }

    public function headings(): array
    {
        return [
            [strtoupper($this->reportTitle)],
            ['Posisi per '.date('d F Y', strtotime($this->positionDate))],
            ['Nomor Faktur', 'Tanggal', 'Jatuh Tempo', $this->partyLabel, 'Nilai', 'Terbayar', 'Sisa', 'Umur', 'Status'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:I1'); $sheet->mergeCells('A2:I2'); $sheet->freezePane('A4');
        foreach (range('A', 'I') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
        $sheet->getStyle('A1:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:I3')->applyFromArray(['font'=>['bold'=>true],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'E2E8F0']]]);
        $sheet->getStyle('E4:G'.$sheet->getHighestRow())->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        return [];
    }

    public function title(): string { return mb_substr($this->reportTitle, 0, 31); }
}
