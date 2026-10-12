<?php

namespace App\Exports;

use App\Models\Expedisi;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class InvoiceExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    protected $tanggalDari;
    protected $tanggalSampai;
    protected $status;
    protected $customer;
    protected $grandTotal = 0;
    protected $bayarTotal = 0;
    protected $piutangTotal = 0;

    public function __construct(
        $tanggalDari,
        $tanggalSampai,
        $status,
        $customer = null
    ) {
        $this->tanggalDari = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
        $this->status = $status;
        $this->customer = $customer;
    }

    public function collection()
    {
        $query = Expedisi::query()
            ->select([
                'TGLMUAT',
                'NOSJ',
                'NOJALAN',
                'CUSTOMER',
                'NAMA_KENDARAAN',
                'NAMA_DRIVER',
                'rute',
                'barang',
                'JUMLAH',
                'UNIT',
                DB::raw('CAST(DISC AS DECIMAL(15,2)) as DISC'),
                DB::raw('CAST(PPN AS DECIMAL(15,2)) as PPN'),
                DB::raw('CAST(DC AS DECIMAL(15,2)) as DC'),
                'INVOICE',
                'TGLINVOICE',
                DB::raw('CAST(GRAND AS DECIMAL(15,2)) as GRAND'),
                DB::raw('CAST(BAYAR AS DECIMAL(15,2)) as BAYAR'),
                DB::raw('CAST(PIUTANG AS DECIMAL(15,2)) as PIUTANG'),
            ])
            ->whereBetween('TGLMUAT', [
                $this->tanggalDari,
                $this->tanggalSampai
            ])
            ->where('JENIS', 'EKS');

        // FILTER CUSTOMER
        $query->when($this->customer, function ($q) {
            $q->where(
                'CUSTOMER_KODE',
                $this->customer
            );
        });

        // BELUM INVOICE
        if ($this->status == 'belum') {

            $query->where(function ($q) {

                $q->whereNull('INVOICE')
                    ->orWhere('INVOICE', '');

            });

        } else {

            // SUDAH INVOICE
            $query->whereNotNull('INVOICE')
                ->where('INVOICE', '!=', '');

        }

        $data = $query
            ->orderBy('TGLMUAT')
            ->get();

        $this->grandTotal   = $data->sum('GRAND');
        $this->bayarTotal   = $data->sum('BAYAR');
        $this->piutangTotal = $data->sum('PIUTANG');

        return $data;
    }

    public function headings(): array
    {
        return [
            'Tanggal Muat',
            'No SJ',
            'No Jalan',
            'Customer',
            'Kendaraan',
            'Driver',
            'Rute',
            'Barang',
            'Jumlah',
            'Unit',
            'Disc',
            'PPN',
            'DC(Delcharge)',
            'Invoice',
            'Tgl Invoice',
            'Grand Total',
            'Bayar',
            'Piutang',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [

            1 => [

                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => [
                        'rgb' => 'FFFFFF'
                    ]
                ],

                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],

                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'rgb' => '1F4E78'
                    ]
                ]

            ]

        ];
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                $lastRow = $sheet->getHighestRow();

                // BORDER
                $sheet->getStyle('A1:R'.$lastRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => 'thin',
                            ],
                        ],
                    ]);

                // FORMAT RUPIAH
                $sheet->getStyle('P2:R'.($lastRow + 2))
                ->getNumberFormat()
                ->setFormatCode('#,##0');

                // WRAP TEXT
                $sheet->getStyle('A1:R'.$lastRow)
                    ->getAlignment()
                    ->setWrapText(true);

                // BARIS TOTAL
                $totalRow = $lastRow + 2;
                // LABEL
                $sheet->setCellValue('O'.$totalRow, 'TOTAL');
                // GRAND
                $sheet->setCellValue('P'.$totalRow, $this->grandTotal);
                // BAYAR
                $sheet->setCellValue('Q'.$totalRow, $this->bayarTotal);
                // PIUTANG
                $sheet->setCellValue('R'.$totalRow, $this->piutangTotal);
                // STYLE
                $sheet->getStyle('O'.$totalRow.':R'.$totalRow)
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                        ],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => [
                                'rgb' => 'D9EAF7'
                            ]
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => 'thin',
                            ],
                        ],
                    ]);
                // CENTER VERTICAL
                $sheet->getStyle('A1:R'.$lastRow)
                    ->getAlignment()
                    ->setVertical('center');
            }

        ];
    }
}

// namespace App\Exports;

// use App\Models\Expedisi;
// use Illuminate\Support\Facades\DB;

// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Concerns\WithStyles;
// use Maatwebsite\Excel\Concerns\WithEvents;

// use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
// use Maatwebsite\Excel\Events\AfterSheet;

// class InvoiceExport implements
//     FromCollection,
//     WithHeadings,
//     ShouldAutoSize,
//     WithStyles,
//     WithEvents
// {
//     protected $tanggalDari;
//     protected $tanggalSampai;
//     protected $status;
//     protected $customer;
//     protected $grandTotal = 0;
//     protected $bayarTotal = 0;
//     protected $piutangTotal = 0;

//     public function __construct(
//         $tanggalDari,
//         $tanggalSampai,
//         $status,
//         $customer = null
//     ) {
//         $this->tanggalDari = $tanggalDari;
//         $this->tanggalSampai = $tanggalSampai;
//         $this->status = $status;
//         $this->customer = $customer;
//     }

//     public function collection()
//     {
//         $query = Expedisi::query()
//             ->select([
//                 'TGLMUAT',
//                 'NOSJ',
//                 'NOJALAN',
//                 'CUSTOMER',
//                 'NAMA_KENDARAAN',
//                 'NAMA_DRIVER',
//                 'rute',
//                 'barang',
//                 'JUMLAH',
//                 'UNIT',
//                 'INVOICE',
//                 'TGLINVOICE',
//                 DB::raw('CAST(GRAND AS DECIMAL(15,2)) as GRAND'),
//                 DB::raw('CAST(BAYAR AS DECIMAL(15,2)) as BAYAR'),
//                 DB::raw('CAST(PIUTANG AS DECIMAL(15,2)) as PIUTANG'),
//             ])
//             ->whereBetween('TGLMUAT', [
//                 $this->tanggalDari,
//                 $this->tanggalSampai
//             ])
//             ->where('JENIS', 'EKS');

//         // FILTER CUSTOMER
//         $query->when($this->customer, function ($q) {
//             $q->where(
//                 'CUSTOMER_KODE',
//                 $this->customer
//             );
//         });

//         // BELUM INVOICE
//         if ($this->status == 'belum') {

//             $query->where(function ($q) {

//                 $q->whereNull('INVOICE')
//                     ->orWhere('INVOICE', '');

//             });

//         } else {

//             // SUDAH INVOICE
//             $query->whereNotNull('INVOICE')
//                 ->where('INVOICE', '!=', '');

//         }

//         $data = $query
//             ->orderBy('TGLMUAT')
//             ->get();

//         $this->grandTotal   = $data->sum('GRAND');
//         $this->bayarTotal   = $data->sum('BAYAR');
//         $this->piutangTotal = $data->sum('PIUTANG');

//         return $data;
//     }

//     public function headings(): array
//     {
//         return [
//             'Tanggal Muat',
//             'No SJ',
//             'No Jalan',
//             'Customer',
//             'Kendaraan',
//             'Driver',
//             'Rute',
//             'Barang',
//             'Jumlah',
//             'Unit',
//             'Invoice',
//             'Tgl Invoice',
//             'Grand Total',
//             'Bayar',
//             'Piutang',
//         ];
//     }

//     public function styles(Worksheet $sheet)
//     {
//         return [

//             1 => [

//                 'font' => [
//                     'bold' => true,
//                     'size' => 12,
//                     'color' => [
//                         'rgb' => 'FFFFFF'
//                     ]
//                 ],

//                 'alignment' => [
//                     'horizontal' => 'center',
//                     'vertical' => 'center',
//                 ],

//                 'fill' => [
//                     'fillType' => 'solid',
//                     'startColor' => [
//                         'rgb' => '1F4E78'
//                     ]
//                 ]

//             ]

//         ];
//     }

//     public function registerEvents(): array
//     {
//         return [

//             AfterSheet::class => function (AfterSheet $event) {

//                 $sheet = $event->sheet;

//                 $lastRow = $sheet->getHighestRow();

//                 // BORDER
//                 $sheet->getStyle('A1:O'.$lastRow)
//                     ->applyFromArray([
//                         'borders' => [
//                             'allBorders' => [
//                                 'borderStyle' => 'thin',
//                             ],
//                         ],
//                     ]);

//                 // FORMAT RUPIAH
//                 $sheet->getStyle('M2:O'.($lastRow + 2))
//                 ->getNumberFormat()
//                 ->setFormatCode('#,##0');

//                 // WRAP TEXT
//                 $sheet->getStyle('A1:O'.$lastRow)
//                     ->getAlignment()
//                     ->setWrapText(true);

//                 // BARIS TOTAL
//                 $totalRow = $lastRow + 2;
//                 // LABEL
//                 $sheet->setCellValue('L'.$totalRow, 'TOTAL');
//                 // GRAND
//                 $sheet->setCellValue('M'.$totalRow, $this->grandTotal);
//                 // BAYAR
//                 $sheet->setCellValue('N'.$totalRow, $this->bayarTotal);
//                 // PIUTANG
//                 $sheet->setCellValue('O'.$totalRow, $this->piutangTotal);
//                 // STYLE
//                 $sheet->getStyle('L'.$totalRow.':O'.$totalRow)
//                     ->applyFromArray([
//                         'font' => [
//                             'bold' => true,
//                             'size' => 12,
//                         ],
//                         'fill' => [
//                             'fillType' => 'solid',
//                             'startColor' => [
//                                 'rgb' => 'D9EAF7'
//                             ]
//                         ],
//                         'borders' => [
//                             'allBorders' => [
//                                 'borderStyle' => 'thin',
//                             ],
//                         ],
//                     ]);
//                 // CENTER VERTICAL
//                 $sheet->getStyle('A1:O'.$lastRow)
//                     ->getAlignment()
//                     ->setVertical('center');
//             }

//         ];
//     }
// }
