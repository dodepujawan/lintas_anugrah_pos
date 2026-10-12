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

class OpeningReceivableExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly Collection $rows,
        private readonly string $search = '',
        private readonly string $customer = '',
    ) {}

    public function collection(): Collection
    {
        $data = $this->rows->map(fn ($row): array => [
            $row->TGLFAKTUR?->format('d/m/Y'), $row->NOFAKTUR,
            trim((string) $row->CUSTOMER).' - '.($row->CUSTOMER_NAME ?: '-'),
            $row->TGLJT?->format('d/m/Y'), (float) $row->PIUTANG,
            (float) $row->BAYAR, (float) $row->SALDO,
            (float) $row->SALDO <= 0 ? 'Lunas' : 'Belum Lunas',
        ]);

        $data->push(['', '', 'TOTAL', '', $this->rows->sum('PIUTANG'), $this->rows->sum('BAYAR'), $this->rows->sum('SALDO'), '']);
        return $data;
    }

    public function headings(): array
    {
        return [
            ['SALDO AWAL PIUTANG'],
            [collect([
                $this->customer !== '' ? 'Customer: '.$this->customer : null,
                $this->search !== '' ? 'Pencarian: '.$this->search : null,
            ])->filter()->join(' | ') ?: 'Seluruh data saldo awal'],
            ['Tanggal Faktur', 'Nomor Faktur', 'Pelanggan', 'Jatuh Tempo', 'Piutang Awal', 'Pembayaran', 'Sisa', 'Status'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:H1'); $sheet->mergeCells('A2:H2'); $sheet->freezePane('A4');
        foreach (range('A', 'H') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
        $sheet->getStyle('A1:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:H3')->applyFromArray(['font'=>['bold'=>true], 'fill'=>['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>'E2E8F0']]]);
        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 4) $sheet->getStyle('E4:G'.$lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle('A'.$lastRow.':H'.$lastRow)->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'Saldo Awal Piutang'; }
}
