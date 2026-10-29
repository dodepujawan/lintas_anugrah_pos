<?php

namespace App\Exports;

use App\Models\Expedisi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceDgnExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    private float $grandTotal = 0;
    private float $paidTotal = 0;
    private float $receivableTotal = 0;

    public function __construct(
        private readonly string $dateFrom,
        private readonly string $dateTo,
        private readonly string $status,
        private readonly ?string $customer = null,
    ) {
    }

    public function collection(): Collection
    {
        $dateColumn = $this->status === 'belum' ? 'TGLMUAT' : 'TGLINVOICE';
        $query = Expedisi::query()
            ->select([
                'TGLMUAT', 'NOSJ', 'NOJALAN', 'CUSTOMER', 'NAMA_KENDARAAN', 'NAMA_DRIVER',
                'rute', 'barang', 'JUMLAH', 'UNIT', 'DISC', 'PPN', 'DC', 'INVOICE',
                'TGLINVOICE', 'GRAND', 'BAYAR', 'PIUTANG',
            ])
            ->where('JENIS', 'REN')
            ->whereBetween($dateColumn, [$this->dateFrom, $this->dateTo])
            ->when($this->customer, fn ($q) => $q->where('CUSTOMER_KODE', $this->customer));

        if ($this->status === 'belum') {
            $query->where(fn ($q) => $q->whereNull('INVOICE')->orWhere('INVOICE', ''));
        } else {
            $query->whereNotNull('INVOICE')->where('INVOICE', '!=', '');
        }

        $rows = $query->orderBy($dateColumn)->orderBy('NOSJ')->get();
        $this->grandTotal = (float) $rows->sum('GRAND');
        $this->paidTotal = (float) $rows->sum('BAYAR');
        $this->receivableTotal = (float) $rows->sum('PIUTANG');

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal Muat', 'No SJ', 'No Jalan', 'Customer', 'Kendaraan', 'Driver', 'Rute',
            'Barang', 'Jumlah', 'Unit', 'Disc (%)', 'PPN (%)', 'DC (Delcharge)', 'Invoice',
            'Tgl Invoice', 'Grand Total', 'Bayar', 'Piutang',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '243B64']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:R{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle('thin');
                $sheet->getStyle("P2:R{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("A1:R{$lastRow}")->getAlignment()->setVertical('center')->setWrapText(true);

                $totalRow = $lastRow + 2;
                $sheet->setCellValue("O{$totalRow}", 'TOTAL');
                $sheet->setCellValue("P{$totalRow}", $this->grandTotal);
                $sheet->setCellValue("Q{$totalRow}", $this->paidTotal);
                $sheet->setCellValue("R{$totalRow}", $this->receivableTotal);
                $sheet->getStyle("O{$totalRow}:R{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DCE8F5']],
                    'borders' => ['allBorders' => ['borderStyle' => 'thin']],
                ]);
            },
        ];
    }
}
