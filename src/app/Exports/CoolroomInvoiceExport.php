<?php

namespace App\Exports;

use App\Models\Coolroom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CoolroomInvoiceExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    private $dateFrom;
    private $dateTo;
    private $status;
    private $customer;
    private $totals = ['GRAND' => 0, 'BAYAR' => 0, 'PIUTANG' => 0];

    public function __construct(string $dateFrom, string $dateTo, string $status, ?string $customer = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->status = $status;
        $this->customer = $customer;
    }

    public function collection()
    {
        $query = Coolroom::query()
            ->select([
                'TGLSJ', 'NOSJ', 'CUSTOMER', 'JUMLAH', 'UNIT', 'HARGA', 'DISC', 'NDISC',
                'DPP', 'PPN', 'NPPN', 'INVOICE', 'TGLINVOICE', 'GRAND', 'BAYAR', 'PIUTANG',
            ])
            ->when($this->customer, fn ($builder) => $builder->where('CUSTOMER_KODE', $this->customer));

        if ($this->status === 'belum') {
            $query->whereBetween('TGLSJ', [$this->dateFrom, $this->dateTo])
                ->where(function ($builder) {
                    $builder->whereNull('INVOICE')->orWhere('INVOICE', '');
                })
                ->orderBy('TGLSJ');
        } else {
            $query->whereBetween('TGLINVOICE', [$this->dateFrom, $this->dateTo])
                ->whereNotNull('INVOICE')
                ->where('INVOICE', '!=', '')
                ->orderBy('TGLINVOICE');
        }

        $rows = $query->get();
        foreach (['GRAND', 'BAYAR', 'PIUTANG'] as $field) {
            $this->totals[$field] = (float) $rows->sum($field);
        }

        return $rows->map(fn (Coolroom $row) => [
            optional($row->TGLSJ)->format('d/m/Y'),
            $row->NOSJ,
            $row->CUSTOMER,
            (float) $row->JUMLAH,
            $row->UNIT,
            (float) $row->HARGA,
            (float) $row->DISC,
            (float) $row->NDISC,
            (float) $row->DPP,
            (float) $row->PPN,
            (float) $row->NPPN,
            $row->INVOICE,
            optional($row->TGLINVOICE)->format('d/m/Y'),
            (float) $row->GRAND,
            (float) $row->BAYAR,
            (float) $row->PIUTANG,
        ]);
    }

    public function headings(): array
    {
        return [
            'Tanggal SJ', 'No SJ', 'Customer', 'Jumlah', 'Unit', 'Harga', 'Disc %', 'Disc Rp',
            'DPP', 'PPN %', 'PPN Rp', 'Invoice', 'Tanggal Invoice', 'Grand Total', 'Bayar', 'Piutang',
        ];
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
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet;
            $lastRow = $sheet->getHighestRow();
            $totalRow = $lastRow + 2;

            $sheet->getStyle("A1:P{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => 'thin']],
                'alignment' => ['wrapText' => true],
            ]);
            $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H2:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("K2:P{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

            $sheet->setCellValue("M{$totalRow}", 'TOTAL');
            $sheet->setCellValue("N{$totalRow}", $this->totals['GRAND']);
            $sheet->setCellValue("O{$totalRow}", $this->totals['BAYAR']);
            $sheet->setCellValue("P{$totalRow}", $this->totals['PIUTANG']);
            $sheet->getStyle("M{$totalRow}:P{$totalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D9EAF7']],
                'borders' => ['allBorders' => ['borderStyle' => 'thin']],
            ]);
        }];
    }
}
