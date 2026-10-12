<?php

namespace App\Exports;

use App\Models\Coolroom;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class CoolroomExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    protected $tanggalDari;
    protected $tanggalSampai;
    protected $status;
    protected $grandTotal = 0;
    protected $bayarTotal = 0;
    protected $piutangTotal = 0;
    protected $customer;

    public function __construct(
        $tanggalDari,
        $tanggalSampai,
        $status,
        $customer = null
    ){

        $this->tanggalDari=$tanggalDari;

        $this->tanggalSampai=$tanggalSampai;

        $this->status=$status;

        $this->customer = $customer;

    }

    public function collection()
    {
        $query = Coolroom::query()
            ->select([
                'TGLSJ',
                'NOSJ',
                'CUSTOMER',
                'JUMLAH',
                'UNIT',
                'HARGA',
                'DISC',
                'NDISC',
                'DPP',
                'PPN',
                'NPPN',
                'INVOICE',
                'TGLINVOICE',
                DB::raw('CAST(GRAND AS DECIMAL(15,2)) as GRAND'),
                DB::raw('CAST(BAYAR AS DECIMAL(15,2)) as BAYAR'),
                DB::raw('CAST(PIUTANG AS DECIMAL(15,2)) as PIUTANG'),
            ]);

        $query->when($this->customer, function ($q) {
            $q->where(
                'CUSTOMER_KODE',
                $this->customer
            );
        });

        if ($this->status === 'belum') {

            $query->whereBetween('TGLSJ', [
                $this->tanggalDari,
                $this->tanggalSampai
            ]);

            $query->where(function ($q) {
                $q->whereNull('INVOICE')
                ->orWhere('INVOICE', '');
            });

            $orderBy = 'TGLSJ';

        } else {

            $query->whereBetween('TGLINVOICE', [
                $this->tanggalDari,
                $this->tanggalSampai
            ]);

            $query->whereNotNull('INVOICE')
                ->where('INVOICE', '!=', '');

            $orderBy = 'TGLINVOICE';
        }

        $data = $query
            ->orderBy($orderBy)
            ->get();

        $this->grandTotal   = $data->sum('GRAND');
        $this->bayarTotal   = $data->sum('BAYAR');
        $this->piutangTotal = $data->sum('PIUTANG');

        return $data;
    }

    public function headings(): array
    {
        return [
            'Tanggal SJ',
            'No SJ',
            'Customer',
            'Jumlah',
            'Unit',
            'Harga',
            'Disc %',
            'Disc Rp',
            'DPP',
            'PPN %',
            'PPN Rp',
            'Invoice',
            'Tanggal Invoice',
            'Grand Total',
            'Bayar',
            'Piutang'
        ];
    }

    // =====================
    // STYLE HEADER
    // =====================
    public function styles(Worksheet $sheet)
    {
        return [

            1=>[

                'font'=>[

                    'bold'=>true,

                    'size'=>12,

                    'color'=>[
                        'rgb'=>'FFFFFF'
                    ]

                ],

                'alignment'=>[

                    'horizontal'=>'center',

                    'vertical'=>'center'

                ],

                'fill'=>[

                    'fillType'=>'solid',

                    'startColor'=>[
                        'rgb'=>'1F4E78'
                    ]

                ]

            ]

        ];
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class=>function(AfterSheet $event){

                $sheet=$event->sheet;

                $lastRow=$sheet
                    ->getHighestRow();

                // =====================
                // BORDER
                // =====================
                $sheet->getStyle(
                    'A1:P'.$lastRow
                )->applyFromArray([

                    'borders'=>[

                        'allBorders'=>[
                            'borderStyle'=>'thin'
                        ]

                    ]

                ]);

                // =====================
                // FORMAT RUPIAH
                // =====================
                $sheet->getStyle('F2:F'.($lastRow+2))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
                $sheet->getStyle('H2:I'.($lastRow+2))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
                $sheet->getStyle('K2:P'.($lastRow+2))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
                // =====================
                // WRAP TEXT
                // =====================
                $sheet->getStyle(
                    'A1:P'.$lastRow
                )
                ->getAlignment()
                ->setWrapText(true);

                // =====================
                // TOTAL ROW
                // =====================
                $totalRow = $lastRow + 2;
                $sheet->setCellValue('M'.$totalRow, 'TOTAL');
                $sheet->setCellValue('N'.$totalRow, $this->grandTotal);
                $sheet->setCellValue('O'.$totalRow, $this->bayarTotal);
                $sheet->setCellValue('P'.$totalRow, $this->piutangTotal);
                // =====================
                // STYLE TOTAL
                // =====================
                $sheet->getStyle(
                    'M'.$totalRow.':P'.$totalRow
                )->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => [
                            'rgb' => 'D9EAF7'
                        ]
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin'
                        ]
                    ]
                ]);
            }
        ];
    }
}
