<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenjualanReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        private Collection $rows,
        private array $filters,
        private array $jenisOptions,
    ) {}

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No. Faktur',
            'Kode Customer',
            'Nama Customer',
            'Jenis',
            'Nilai Penjualan',
            'Terbayar',
            'Saldo',
            'Status',
            'No. Jurnal',
            'Keterangan',
            'Sumber',
        ];
    }

    public function map($row): array
    {
        $jenisCode = (string) ($row->jenis_code ?? 'LAINNYA');

        return [
            $row->TGLFAKTUR?->format('d/m/Y'),
            $row->NOFAKTUR,
            $row->CUSTOMER,
            $row->customer_name ?: '-',
            $this->jenisOptions[$jenisCode] ?? 'Lainnya',
            (float) $row->PIUTANG,
            (float) $row->BAYAR,
            (float) $row->SALDO,
            $row->STATUS,
            $row->NOJURNAL,
            $row->KETERANGAN,
            $row->SOURCE,
        ];
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A5F');
        $sheet->getStyle('F:H')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle('F:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $sheet->freezePane('A2');

        return [];
    }
}
