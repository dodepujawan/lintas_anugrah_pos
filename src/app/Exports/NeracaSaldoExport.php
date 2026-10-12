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

class NeracaSaldoExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected string $periodLockStatus;
    protected ?string $periodeLabel;

    public function __construct($tanggalAwal, $tanggalAkhir, string $periodLockStatus = 'TERBUKA', ?string $periodeLabel = null)
    {
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->periodLockStatus = $periodLockStatus;
        $this->periodeLabel = $periodeLabel;
    }

    public function collection()
    {
        $rows = collect();
        $coas = Coa::active()->posting()->orderBy('tipe')->orderBy('kode_akun')->get();
        $grouped = $coas->groupBy('tipe');
        $totalsByCoa = $coas->isEmpty()
            ? []
            : app(JournalQueryService::class)->getSumByMultipleCoaIds(
                '1900-01-01',
                $this->tanggalAkhir,
                $coas->pluck('id')->all()
            );

        foreach ($grouped as $tipe => $group) {
            $rows->push([$tipe, '', '', '', '']);
            $groupDebit = 0;
            $groupKredit = 0;

            foreach ($group as $coa) {
                $mutasiDebit = (float) ($totalsByCoa[$coa->id]['debit'] ?? 0);
                $mutasiKredit = (float) ($totalsByCoa[$coa->id]['kredit'] ?? 0);
                $saldoBersih = $mutasiDebit - $mutasiKredit;
                $debit = $saldoBersih > 0.005 ? $saldoBersih : 0;
                $kredit = $saldoBersih < -0.005 ? abs($saldoBersih) : 0;
                $groupDebit += $debit;
                $groupKredit += $kredit;

                $indent = match ($coa->level) {
                    'Header' => '',
                    'Sub Header' => '  ',
                    'Kelompok Akun' => '    ',
                    'Akun Posting' => '      ',
                    default => '',
                };

                $rows->push([
                    $coa->kode_akun,
                    $indent . $coa->nama_akun,
                    $coa->tipe,
                    $debit,
                    $kredit,
                ]);
            }

            $rows->push(['', 'Total ' . $tipe, '', $groupDebit, $groupKredit]);
        }

        return $rows;
    }

    public function headings(): array
    {
        $status = $this->periodLockStatus === 'TERKUNCI' ? ' | Status: TERKUNCI' : '';

        return [
            [config('app.company_name')],
            ['LAPORAN NERACA SALDO'],
            ['Posisi per: ' . date('d F Y', strtotime($this->tanggalAkhir)) . $status],
            ['Kode Akun', 'Nama Akun', 'Tipe', 'Saldo Debit', 'Saldo Kredit'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->freezePane('A5');

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:E2')->getFont()->setBold(true);
        $sheet->getStyle('A1:E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:E4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        for ($row = 5; $row <= $lastRow; $row++) {
            $cellA = $sheet->getCell("A{$row}")->getValue();
            $cellB = $sheet->getCell("B{$row}")->getValue();

            if ($cellB === '' && $cellA !== '') {
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1D5DB']],
                ]);
            }

            if ($cellA === '' && str_starts_with($cellB ?? '', 'Total')) {
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                ]);
            }

            $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$row}:E{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }

        return [];
    }

    public function title(): string
    {
        return 'Neraca Saldo';
    }
}
