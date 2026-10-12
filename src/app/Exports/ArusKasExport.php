<?php

namespace App\Exports;

use App\Models\Coa;
use App\Services\JournalQueryService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ArusKasExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected string $periodLockStatus;

    public function __construct($tanggalAwal, $tanggalAkhir, string $periodLockStatus = 'TERBUKA')
    {
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->periodLockStatus = $periodLockStatus;
    }

    public function collection()
    {
        $cashAccounts = Coa::active()->posting()->where('tipe', 'Aset')
            ->where(function ($query) {
                $query->where('nama_akun', 'like', '%Kas%')
                    ->orWhere('nama_akun', 'like', '%Bank%')
                    ->orWhere('kategori', 'like', '%Kas%')
                    ->orWhere('kategori', 'like', '%Bank%');
            })
            ->orderBy('kode_akun')
            ->get();

        if ($cashAccounts->isEmpty()) {
            $cashAccounts = Coa::active()->posting()->byTipe('Aset')->orderBy('kode_akun')->get();
        }

        $rows = collect();
        $totalsByCoa = $cashAccounts->isEmpty()
            ? []
            : app(JournalQueryService::class)->getSumByMultipleCoaIds(
                $this->tanggalAwal,
                $this->tanggalAkhir,
                $cashAccounts->pluck('id')->all()
            );

        foreach ($cashAccounts as $coa) {
            $debit = $totalsByCoa[$coa->id]['debit'] ?? 0;
            $kredit = $totalsByCoa[$coa->id]['kredit'] ?? 0;

            $rows->push([
                $coa->kode_akun,
                $coa->nama_akun,
                $debit,
                $kredit,
                $debit - $kredit,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            ['LAPORAN ARUS KAS'],
            ['Periode: ' . date('d F Y', strtotime($this->tanggalAwal)) . ' - ' . date('d F Y', strtotime($this->tanggalAkhir)) . ' | Status: ' . $this->periodLockStatus],
            ['Kode Akun', 'Nama Akun', 'Penerimaan (Debit)', 'Pengeluaran (Kredit)', 'Nett'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->freezePane('A4');

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:E2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        for ($row = 3; $row <= $lastRow; $row++) {
            $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("C{$row}:E{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }

        return [];
    }

    public function title(): string
    {
        return 'Arus Kas';
    }
}
