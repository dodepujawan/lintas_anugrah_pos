<?php

namespace App\Exports;

use App\Models\Expedisi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ExpedisiInvoiceGenerateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    private float $grandTotal = 0;
    private float $paidTotal = 0;
    private float $receivableTotal = 0;

    public function __construct(
        private readonly string $dateFrom,
        private readonly string $dateTo,
        private readonly string $status,
        private readonly ?string $customerCode = null,
    ) {
    }

    public function collection()
    {
        $query = Expedisi::query()
            ->leftJoin('arh', 'arh.NOFAKTUR', '=', 'expedisis.INVOICE')
            ->where('JENIS', 'EKS')
            ->whereBetween('TGLMUAT', [$this->dateFrom, $this->dateTo])
            ->when($this->customerCode, fn ($q) => $q->where(fn ($match) => $match
                ->where('expedisis.CUSTOMER_KODE', $this->customerCode)
                ->orWhereExists(fn ($customer) => $customer->selectRaw('1')->from('mcustomer')
                    ->whereColumn('mcustomer.kode_cus', 'expedisis.CUSTOMER_KODE')
                    ->where('mcustomer.CUSTOMER', $this->customerCode))));

        if ($this->status === 'belum') {
            $query->where(fn ($q) => $q->whereNull('INVOICE')->orWhere('INVOICE', ''));
        } else {
            $query->whereNotNull('INVOICE')->where('INVOICE', '<>', '');
        }

        $rows = $query->orderBy('expedisis.TGLMUAT')->orderBy('expedisis.NOSJ')->get([
            'expedisis.TGLMUAT', 'expedisis.NOSJ', 'expedisis.NOJALAN', 'expedisis.CUSTOMER',
            'expedisis.NAMA_KENDARAAN', 'expedisis.NAMA_DRIVER', 'expedisis.rute',
            'expedisis.barang', 'expedisis.JUMLAH', 'expedisis.UNIT', 'expedisis.DISC',
            'expedisis.PPN', 'expedisis.DC', 'expedisis.INVOICE', 'expedisis.TGLINVOICE',
            'expedisis.GRAND',
            DB::raw('CASE WHEN expedisis.INVOICE IS NOT NULL AND expedisis.INVOICE <> \'\' AND expedisis.GRAND <= 0 THEN 0 '
                . 'ELSE COALESCE(arh.BAYAR, expedisis.BAYAR, 0) END as BAYAR'),
            DB::raw('CASE WHEN expedisis.INVOICE IS NOT NULL AND expedisis.INVOICE <> \'\' AND expedisis.GRAND <= 0 THEN 0 '
                . 'WHEN arh.NOFAKTUR IS NULL THEN COALESCE(expedisis.PIUTANG, 0) '
                . 'WHEN arh.SALDO > COALESCE(arh.BAYAR, 0) THEN arh.SALDO - COALESCE(arh.BAYAR, 0) ELSE 0 END as PIUTANG'),
        ]);

        $this->grandTotal = (float) $rows->sum('GRAND');
        $this->paidTotal = (float) $rows->sum('BAYAR');
        $this->receivableTotal = (float) $rows->sum('PIUTANG');

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal Muat', 'No SJ', 'No Jalan', 'Customer', 'Kendaraan', 'Driver',
            'Rute', 'Barang', 'Jumlah', 'Unit', 'Disc (%)', 'PPN (%)', 'DC',
            'Invoice', 'Tgl Invoice', 'Grand Total', 'Bayar', 'Piutang',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();
                $totalRow = $lastRow + 2;

                $sheet->getStyle("A1:R{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => 'thin']],
                    'alignment' => ['vertical' => 'center', 'wrapText' => true],
                ]);
                $sheet->getStyle('A1:R1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E78']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);
                $sheet->getStyle("P2:R{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->setCellValue("O{$totalRow}", 'TOTAL');
                $sheet->setCellValue("P{$totalRow}", $this->grandTotal);
                $sheet->setCellValue("Q{$totalRow}", $this->paidTotal);
                $sheet->setCellValue("R{$totalRow}", $this->receivableTotal);
                $sheet->getStyle("O{$totalRow}:R{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D9EAF7']],
                    'borders' => ['allBorders' => ['borderStyle' => 'thin']],
                ]);
            },
        ];
    }
}
