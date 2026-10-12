<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LabaRugiExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected array $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function collection()
    {
        $pendapatan = $this->report['pendapatans'];
        $beban = $this->report['bebans'];
        $pendapatanSaldo = $this->report['pendapatanSaldo'];
        $bebanSaldo = $this->report['bebanSaldo'];
        $totalPendapatan = $this->report['totalPendapatan'];
        $totalBeban = $this->report['totalBeban'];
        $taksiranPajakPenghasilan = $this->report['taksiranPajakPenghasilan'];
        $labaBersih = $this->report['labaBersih'];

        $data = collect();
        $data->push(['', 'PENDAPATAN', '']);
        foreach ($pendapatan as $p) {
            $saldo = $pendapatanSaldo[$p->id] ?? 0;
            $data->push([$p->kode_akun, '  ' . $p->nama_akun, $saldo]);
        }
        $data->push(['', 'Total Pendapatan', $totalPendapatan]);
        $data->push(['', '', 0]);

        $data->push(['', 'BEBAN', '']);
        foreach ($beban as $b) {
            $saldo = $bebanSaldo[$b->id] ?? 0;
            $data->push([$b->kode_akun, '  ' . $b->nama_akun, $saldo]);
        }
        $data->push(['', 'Total Beban', $totalBeban]);
        $data->push(['', '', 0]);

        $data->push(['', 'LABA SEBELUM PAJAK', '']);
        $data->push(['', 'Total Pendapatan', $totalPendapatan]);
        $data->push(['', 'Total Beban', $totalBeban]);
        $data->push(['', 'Taksiran Pajak Penghasilan', $taksiranPajakPenghasilan]);
        $data->push(['', '', 0]);

        $data->push(['', 'LABA BERSIH', '']);
        $data->push(['', ($labaBersih >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH'), abs($labaBersih)]);

        return $data;
    }

    public function headings(): array
    {
        $periodLine = 'Periode: ' . ($this->report['periodeLabel'] ?? (date('d F Y', strtotime($this->report['tanggalAwal'])) . ' - ' . date('d F Y', strtotime($this->report['tanggalAkhir']))));
        if (($this->report['periodLockStatus'] ?? 'TERBUKA') === 'TERKUNCI') {
            $periodLine .= ' | Status: TERKUNCI';
        }

        return [
            [config('app.company_name')],
            ['LAPORAN LABA RUGI'],
            [$periodLine],
            ['Kode Akun', 'Nama Akun', 'Jumlah'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('A3:C3');
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->freezePane('A5');

        $styles = [];
        $styles[1] = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $styles[2] = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $styles[3] = ['font' => ['italic' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $styles[4] = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $lastRow = $sheet->getHighestRow();
        for ($row = 5; $row <= $lastRow; $row++) {
            $cellB = $sheet->getCell("B{$row}")->getValue();

            if (in_array(trim($cellB ?? ''), ['PENDAPATAN', 'BEBAN']) || str_starts_with($cellB ?? '', 'Total') || str_contains($cellB ?? '', 'LABA BERSIH') || str_contains($cellB ?? '', 'RUGI BERSIH')) {
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1D5DB']],
                ]);
            }

            $sheet->getStyle("A{$row}:C{$row}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }

        // Ensure header 'Jumlah' is right aligned.
        $sheet->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return $styles;
    }

    public function title(): string
    {
        return 'Laba Rugi';
    }
}
