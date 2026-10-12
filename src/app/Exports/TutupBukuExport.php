<?php

namespace App\Exports;

use App\Models\Coa;
use App\Services\AccountMap;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TutupBukuExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->report['pendapatanRows'] as $row) {
            $rows->push([
                $row['coa']->kode_akun,
                $row['coa']->nama_akun,
                'Pendapatan',
                $row['amount'],
            ]);
        }

        $rows->push(['', '', 'Total Pendapatan', $this->report['totalPendapatan']]);
        $rows->push(['', '', '', '']);

        foreach ($this->report['bebanRows'] as $row) {
            $rows->push([
                $row['coa']->kode_akun,
                $row['coa']->nama_akun,
                'Beban',
                $row['amount'],
            ]);
        }

        $rows->push(['', '', 'Total Beban', $this->report['totalBeban']]);
        $rows->push(['', '', '', '']);
        $rows->push(['', '', 'Laba Bersih', $this->report['labaBersih']]);
        $rows->push(['', '', '', '']);
        $rows->push(['', '', 'Saldo Awal Laba Ditahan', $this->report['saldoAwalLabaDitahan']]);
        $rows->push(['', '', 'Proyeksi Saldo Akhir Laba Ditahan', $this->report['saldoAkhirLabaDitahanProyeksi']]);
        $rows->push(['', '', '', '']);
        $rows->push(['', 'Mapping Akun', '', '']);

        foreach ($this->report['mappingAccounts'] as $key => $coa) {
            $rows->push([
                '',
                AccountMap::mappingLabels()[$key] ?? ucfirst(str_replace('_', ' ', $key)),
                $coa ? ($coa->kode_akun . ' - ' . $coa->nama_akun) : 'Belum ditetapkan',
                '',
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            ['LAPORAN TUTUP BUKU'],
            ['Periode: ' . date('d F Y', strtotime($this->report['tanggalAwal'])) . ' - ' . date('d F Y', strtotime($this->report['tanggalAkhir'])) . ' | Status: ' . ($this->report['periodLockStatus'] ?? 'TERBUKA')],
            ['Kode Akun', 'Nama Akun', 'Jenis', 'Jumlah'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->freezePane('A4');

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2:D2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A3:D3')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        for ($row = 4; $row <= $lastRow; $row++) {
            $sheet->getStyle("A{$row}:D{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return [];
    }

    public function title(): string
    {
        return 'Tutup Buku';
    }
}
