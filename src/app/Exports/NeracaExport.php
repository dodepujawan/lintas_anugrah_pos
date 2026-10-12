<?php

namespace App\Exports;

use App\Models\Coa;
use App\Models\JurnalItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class NeracaExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnFormatting
{
    protected array $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function collection()
    {
        $report = $this->report;
        $showCode = (bool) ($report['show_code'] ?? true);
        $assetTree = $report['asset_tree'] ?? [];
        $liabilityTree = $report['liability_tree'] ?? [];
        $equityTree = $report['equity_tree'] ?? [];

        $totalAset = $report['total_asset'] ?? 0;
        $totalKewajiban = $report['total_kewajiban'] ?? 0;
        $totalEkuitas = $report['total_ekuitas'] ?? 0;
        $totalPasiva = $report['total_pasiva'] ?? 0;
        $labaBerjalan = $report['laba_berjalan'] ?? 0;
        $selisih = $report['selisih'] ?? 0;
        $isBalance = $report['is_balance'] ?? ($selisih < 1);

        $data = collect();

        $data->push([config('app.company_name'), '', '', '']);
        $data->push(['NERACA', '', '', '']);
        $status = ($report['periodLockStatus'] ?? 'TERBUKA') === 'TERKUNCI' ? ' | Status: TERKUNCI' : '';
        $data->push(['Periode: ' . $this->periodLabel() . $status, '', '', '']);
        $data->push(['', '', '', '']);

        $data->push(['AKTIVA', '', '', '']);
        $this->appendHeader($data, $showCode);
        $this->appendTreeRows($data, $assetTree, 0, $showCode);
        $data->push([$showCode ? '' : '', 'TOTAL AKTIVA', $totalAset, '']);
        $data->push(['', '', '', '']);

        $data->push(['PASIVA', '', '', '']);
        $data->push([$showCode ? '' : '', 'KEWAJIBAN', '', '']);
        $this->appendHeader($data, $showCode);
        $this->appendTreeRows($data, $liabilityTree, 0, $showCode);
        $data->push([$showCode ? '' : '', 'Total Kewajiban', $totalKewajiban, '']);

        $data->push([$showCode ? '' : '', 'EKUITAS', '', '']);
        $this->appendHeader($data, $showCode);
        $this->appendTreeRows($data, $equityTree, 0, $showCode);
        if ($labaBerjalan != 0) {
            $data->push([$showCode ? '' : '', 'Laba Berjalan', $labaBerjalan, '']);
        }
        $data->push([$showCode ? '' : '', 'Total Ekuitas', $totalEkuitas, '']);

        $data->push(['', '', '', '']);
        $data->push([$showCode ? '' : '', 'TOTAL PASIVA', $totalPasiva, '']);
        $data->push(['', '', '', '']);
        $data->push([$showCode ? '' : '', '====================================', '', '']);
        $data->push([$showCode ? '' : '', 'TOTAL AKTIVA', $totalAset, '']);
        $data->push([$showCode ? '' : '', 'TOTAL PASIVA', $totalPasiva, '']);
        $data->push([$showCode ? '' : '', ($isBalance ? '✅ BALANCE' : '⚠️ TIDAK BALANCE (Selisih: ' . number_format($selisih, 2) . ')'), '', '']);

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);
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

        for ($row = 1; $row <= $lastRow; $row++) {
            $cellA = $sheet->getCell("A{$row}")->getValue();
            $cellB = $sheet->getCell("B{$row}")->getValue();

            if (
                in_array(trim($cellB ?? ''), ['AKTIVA', 'PASIVA', 'KEWAJIBAN', 'EKUITAS']) ||
                strpos($cellB ?? '', 'TOTAL') === 0 ||
                strpos($cellB ?? '', 'BALANCE') === 0 ||
                strpos($cellB ?? '', 'TIDAK') === 0
            ) {
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1D5DB']],
                ]);
            }

            if ($cellB === 'Kode' || $cellA === 'AKTIVA' || $cellA === 'PASIVA') {
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                ]);
            }

            if ($row > 3) {
                $sheet->getStyle("A{$row}:C{$row}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$row}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("C{$row}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'Neraca';
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    private function periodLabel(): string
    {
        $monthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $periodType = $this->report['period_type'] ?? 'date_range';
        $month = $this->report['month'] ?? null;
        $year = $this->report['year'] ?? null;
        $fromDate = $this->report['from_date'] ?? null;
        $toDate = $this->report['to_date'] ?? null;

        if ($periodType === 'month_year' || (filled($month) && filled($year))) {
            $periodMonth = str_pad((string) ($month ?? date('m', strtotime((string) $fromDate))), 2, '0', STR_PAD_LEFT);
            $periodYear = (string) ($year ?? date('Y', strtotime((string) $fromDate)));

            return strtoupper(($monthNames[$periodMonth] ?? date('F', strtotime((string) $fromDate))) . ' ' . $periodYear);
        }

        return trim(($fromDate ?? '') . ' s/d ' . ($toDate ?? ''));
    }

    private function buildBalanceSheetTree($coas, array $allowedTypes, $parentId = null): array
    {
        $nodes = $coas->filter(function ($coa) use ($parentId, $allowedTypes) {
            if ($parentId === null) {
                return is_null($coa->parent_id) && in_array($coa->tipe, $allowedTypes, true);
            }

            return (int) $coa->parent_id === (int) $parentId;
        })->sortBy('kode_akun')->values();

        return $nodes->map(function ($coa) use ($coas, $allowedTypes) {
            $children = $this->buildBalanceSheetTree($coas, $allowedTypes, $coa->id);
            $balance = $this->hitungSaldoAkunNeraca($coa);
            $subtotal = $balance + collect($children)->sum(fn($child) => $child['subtotal']);

            return [
                'coa' => $coa,
                'balance' => $balance,
                'subtotal' => $subtotal,
                'children' => $children,
            ];
        })->all();
    }

    private function appendHeader($rows, bool $showCode): void
    {
        $rows->push($showCode ? ['Kode', 'Nama Akun', 'Saldo', ''] : ['', 'Nama Akun', 'Saldo', '']);
    }

    private function appendTreeRows($rows, array $nodes, int $level = 0, bool $showCode = true): void
    {
        foreach ($nodes as $node) {
            $indent = str_repeat('  ', max(0, $level));
            $name = $indent . $node['coa']->nama_akun;
            $rows->push($showCode ? [$node['coa']->kode_akun, $name, $node['subtotal'], ''] : ['', $name, $node['subtotal'], '']);

            if (!empty($node['children'])) {
                $this->appendTreeRows($rows, $node['children'], $level + 1, $showCode);
            }
        }
    }

    private function hitungSaldoAkunNeraca($coa)
    {
        $totalDebit = JurnalItem::where('coa_id', $coa->id)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereDate('tanggal', '<=', $this->tanggal))
            ->sum('debit');
        $totalKredit = JurnalItem::where('coa_id', $coa->id)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereDate('tanggal', '<=', $this->tanggal))
            ->sum('kredit');

        return match ($coa->tipe) {
            'Aset', 'Beban' => $totalDebit - $totalKredit,
            'Kewajiban', 'Ekuitas', 'Pendapatan' => $totalKredit - $totalDebit,
            default => strtolower((string) $coa->saldo_normal) === 'kredit'
                ? $totalKredit - $totalDebit
                : $totalDebit - $totalKredit,
        };
    }

    private function hitungTotalLabaRugi($tipe)
    {
        $coaIds = Coa::byTipe($tipe)->pluck('id');
        $totalDebit = JurnalItem::whereIn('coa_id', $coaIds)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereDate('tanggal', '<=', $this->tanggal))
            ->sum('debit');
        $totalKredit = JurnalItem::whereIn('coa_id', $coaIds)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereDate('tanggal', '<=', $this->tanggal))
            ->sum('kredit');
        return $tipe == 'Pendapatan' ? $totalKredit - $totalDebit : $totalDebit - $totalKredit;
    }
}
