<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class KwitansiExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithEvents,
    WithTitle
{
    protected $status;
    protected $tanggalDari;
    protected $tanggalSampai;
    protected $totalNilai = 0;

    public function __construct($status, $tanggalDari = null, $tanggalSampai = null)
    {
        $this->status = $status;
        $this->tanggalDari = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
    }

    public function collection()
    {
        if ($this->status == 'belum') {
            $data = $this->getBelumKwitansi();
        } else {
            $data = $this->getSudahKwitansi();
        }

        // Hitung total Nilai
        $this->totalNilai = $data->sum('NILAI');

        // Format ulang data sesuai urutan yang diinginkan
        $formattedData = collect();
        $no = 1;
        foreach ($data as $item) {
            $formattedData->push([
                'NO' => $no++,
                'JENIS' => $item->JENIS ?? '',
                'NOKWT' => $item->NOKWT ?? '-',
                'INVOICE' => $item->INVOICE ?? '',
                'TGLINVOICE' => $item->TGLINVOICE ? date('d-m-Y', strtotime($item->TGLINVOICE)) : '-',
                'KETERANGAN' => $item->KETERANGAN ?? '',
                'NILAI' => (float) ($item->NILAI ?? 0),
            ]);
        }

        return $formattedData;
    }

    private function getBelumKwitansi()
    {
        // Query Expedisi
        $expedisi = DB::table('expedisi')
            ->select([
                DB::raw("'EKS' as JENIS"),
                DB::raw("'-' as NOKWT"),
                'INVOICE',
                'TGLINVOICE',
                DB::raw("CUSTOMER as KETERANGAN"),
                DB::raw("GRAND as NILAI"),
            ])
            ->where('JENIS', 'EKS')
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '<>', '')
            ->where('GRAND', '>', 0)
            ->where(function ($q) {
                $q->whereNull('kwt')
                    ->orWhere('kwt', '');
            });

        // Query Coolroom
        $coolroom = DB::table('coolrooms')
            ->select([
                DB::raw("'COOL' as JENIS"),
                DB::raw("'-' as NOKWT"),
                'INVOICE',
                'TGLINVOICE',
                DB::raw("CUSTOMER as KETERANGAN"),
                DB::raw("GRAND as NILAI"),
            ])
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '<>', '')
            ->where('GRAND', '>', 0)
            ->where(function ($q) {
                $q->whereNull('kwt')
                    ->orWhere('kwt', '');
            });

        // Filter tanggal jika ada
        if ($this->tanggalDari && $this->tanggalSampai) {
            $expedisi->whereBetween('TGLINVOICE', [$this->tanggalDari, $this->tanggalSampai]);
            $coolroom->whereBetween('TGLINVOICE', [$this->tanggalDari, $this->tanggalSampai]);
        }

        $union = $expedisi->unionAll($coolroom);

        return DB::query()->fromSub($union, 'kwitansi')
            ->orderBy('TGLINVOICE', 'desc')
            ->get();
    }

    private function getSudahKwitansi()
    {
        $query = DB::table('kwitansi')
            ->select([
                'JENIS',
                'NOKWT',
                'FDOK_TRANS as INVOICE',
                'TGL as TGLINVOICE',
                'FKETERANG as KETERANGAN',
                'FNIL_DOK as NILAI',
            ]);

        // Filter tanggal jika ada
        if ($this->tanggalDari && $this->tanggalSampai) {
            $query->whereBetween('TGL', [$this->tanggalDari, $this->tanggalSampai]);
        }

        return $query->orderBy('TGL', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Jenis',
            'No Kwitansi',
            'Invoice',
            'Tanggal Invoice',
            'Keterangan',
            'Nilai'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '1F4E78']
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

                // Border semua cell
                $sheet->getStyle('A1:G' . $lastRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => 'thin',
                            ],
                        ],
                    ]);

                // Format Rupiah untuk kolom Nilai (kolom G)
                $sheet->getStyle('G2:G' . ($lastRow + 2))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Wrap text dan center vertical
                $sheet->getStyle('A1:G' . $lastRow)
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical('center');

                // Bold untuk header
                $sheet->getStyle('A1:G1')->getFont()->setBold(true);

                // Warna background header
                $sheet->getStyle('A1:G1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('1F4E78');

                // Baris Total
                $totalRow = $lastRow + 2;
                $sheet->setCellValue('F' . $totalRow, 'TOTAL');
                $sheet->setCellValue('G' . $totalRow, $this->totalNilai);

                // Style total
                $sheet->getStyle('F' . $totalRow . ':G' . $totalRow)
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                        ],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['rgb' => 'D9EAF7']
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => 'thin',
                            ],
                        ],
                    ]);

                // Format Rupiah untuk total
                $sheet->getStyle('G' . $totalRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Set lebar kolom
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(10);  // Jenis
                $sheet->getColumnDimension('C')->setWidth(20);  // No Kwitansi
                $sheet->getColumnDimension('D')->setWidth(25);  // Invoice
                $sheet->getColumnDimension('E')->setWidth(18);  // Tanggal Invoice
                $sheet->getColumnDimension('F')->setWidth(50);  // Keterangan
                $sheet->getColumnDimension('G')->setWidth(18);  // Nilai

                // Warna alternatif untuk baris (zebra)
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':G' . $row)
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F5F5F5');
                    }
                }

                // Alignment untuk kolom angka (G) rata kanan
                $sheet->getStyle('G2:G' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal('right');
            }
        ];
    }

    public function title(): string
    {
        return 'Data Kwitansi';
    }
}
