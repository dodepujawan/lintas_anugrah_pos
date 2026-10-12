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

class BukuBesarExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $coaId;
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $isAllCoa;

    public function __construct($coaId = null, $tanggalAwal = null, $tanggalAkhir = null)
    {
        $this->coaId = $coaId;
        $this->tanggalAwal = $tanggalAwal ?? now()->startOfMonth()->format('Y-m-d');
        $this->tanggalAkhir = $tanggalAkhir ?? now()->format('Y-m-d');
        $this->isAllCoa = is_null($coaId);
    }

    public function collection()
    {
        $data = collect();

        if ($this->isAllCoa) {
            $coas = Coa::active()->posting()->orderBy('kode_akun')->get();
        } else {
            $coas = Coa::active()->posting()->where('id', $this->coaId)->get();
        }

        foreach ($coas as $coa) {
            $items = app(JournalQueryService::class)
                ->getLedgerItemsForCoa($this->tanggalAwal, $this->tanggalAkhir, (int) $coa->id);

            $openingBalance = $this->calculateOpeningBalance($coa);

            if ($items->count() > 0 || abs($openingBalance) > 0.005) {
                $data->push(['', '']);
                $data->push([$coa->kode_akun . ' - ' . $coa->nama_akun, '', '', '', '', '']);
                $data->push(['Tanggal', 'No. Jurnal', 'Deskripsi', 'Debit', 'Kredit', 'Saldo']);

                $runningBalance = $openingBalance;
                $data->push([
                    date('d/m/Y', strtotime($this->tanggalAwal)),
                    '-',
                    'Saldo Awal per ' . date('d/m/Y', strtotime($this->tanggalAwal)),
                    null,
                    null,
                    $runningBalance,
                ]);

                foreach ($items as $item) {
                    if ($coa->tipe == 'Aset' || $coa->tipe == 'Beban') {
                        $runningBalance += $item->debit - $item->kredit;
                    } else {
                        $runningBalance += $item->kredit - $item->debit;
                    }

                    $data->push([
                        $item->jurnal->tanggal->format('d/m/Y'),
                        $item->jurnal->no_jurnal,
                        $item->deskripsi ?: ($item->jurnal->keterangan ?? '-'),
                        $item->debit > 0 ? $item->debit : null,
                        $item->kredit > 0 ? $item->kredit : null,
                        $runningBalance,
                    ]);
                }

                $data->push([
                    date('d/m/Y', strtotime($this->tanggalAkhir)),
                    '-',
                    'Saldo Akhir per ' . date('d/m/Y', strtotime($this->tanggalAkhir)),
                    null,
                    null,
                    $runningBalance,
                ]);
            }
        }

        if ($data->count() == 0) {
            $data->push(['Tidak ada data transaksi untuk periode ini', '', '', '', '', '']);
        }

        return $data;
    }

    private function calculateOpeningBalance(Coa $coa): float
    {
        $previousDate = \Carbon\Carbon::parse($this->tanggalAwal)->subDay()->format('Y-m-d');
        $totals = app(JournalQueryService::class)->getSumByCoaId('1900-01-01', $previousDate, $coa->id);

        return in_array($coa->tipe, ['Aset', 'Beban'], true)
            ? $totals['debit'] - $totals['kredit']
            : $totals['kredit'] - $totals['debit'];
    }

    public function headings(): array
    {
        $title = $this->isAllCoa ? 'BUKU BESAR - SEMUA AKUN' : 'BUKU BESAR';
        $akun = $this->isAllCoa ? 'Semua Akun Aktif' : (Coa::find($this->coaId)?->kode_akun . ' - ' . Coa::find($this->coaId)?->nama_akun);
        return [
            [config('app.company_name')],
            [$title],
            [$akun],
            ['Periode: ' . date('d F Y', strtotime($this->tanggalAwal)) . ' - ' . date('d F Y', strtotime($this->tanggalAkhir))],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        $sheet->mergeCells('A4:F4');
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->freezePane('A5');

        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2:F2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A3:F3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => ['italic' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        for ($row = 5; $row <= $lastRow; $row++) {
            $cellA = $sheet->getCell("A{$row}")->getValue();
            $cellB = $sheet->getCell("B{$row}")->getValue();

            if (strpos($cellA ?? '', ' - ') !== false && $cellB == '') {
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1D5DB']],
                ]);
                $sheet->mergeCells("A{$row}:F{$row}");
            }

            if ($cellA === 'Tanggal' && $cellB === 'No. Jurnal') {
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                ]);
            }

            if ($cellB !== '' || $cellA === '' || strpos($cellA ?? '', ' - ') !== false) {
                $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            }

            $sheet->getStyle("D{$row}:F{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }

        return [];
    }

    public function title(): string
    {
        return 'Buku Besar';
    }
}
