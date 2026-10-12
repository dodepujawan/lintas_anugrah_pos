<?php

namespace App\Exports;

use App\Models\Jurnal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SaldoAwalExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $tanggalAwal;
    protected $tanggalAkhir;

    public function __construct($tanggalAwal, $tanggalAkhir)
    {
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function collection()
    {
        $jurnals = Jurnal::with(['items.coa'])
            ->where('no_jurnal', 'like', 'SA%')
            ->where('is_posted', true)
            ->whereBetween('tanggal', [$this->tanggalAwal, $this->tanggalAkhir])
            ->orderBy('tanggal')
            ->orderBy('no_jurnal')
            ->get();

        $rows = collect();
        foreach ($jurnals as $jurnal) {
            foreach ($jurnal->items as $item) {
                $rows->push([
                    $jurnal->no_jurnal,
                    $jurnal->tanggal->format('d/m/Y'),
                    $item->coa->kode_akun,
                    $item->coa->nama_akun,
                    $jurnal->keterangan ?? '-',
                    number_format($item->debit, 2, ',', '.'),
                    number_format($item->kredit, 2, ',', '.'),
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            ['SALDO AWAL AKUN'],
            ['Periode: ' . date('d F Y', strtotime($this->tanggalAwal)) . ' - ' . date('d F Y', strtotime($this->tanggalAkhir))],
            ['No. Saldo Awal', 'Tanggal', 'Kode Akun', 'Nama Akun', 'Keterangan', 'Debit', 'Kredit'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(32);
        $sheet->getColumnDimension('E')->setWidth(35);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->freezePane('A4');

        $styles = [];
        $styles[1] = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $styles[2] = ['font' => ['italic' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $styles[3] = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $lastRow = $sheet->getHighestRow();
        for ($row = 4; $row <= $lastRow; $row++) {
            $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("F{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return $styles;
    }

    public function title(): string
    {
        return 'Saldo Awal';
    }
}
