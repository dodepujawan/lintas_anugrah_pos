<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PiutangReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly array $report,
        private readonly array $filters,
    ) {}

    public function collection(): Collection
    {
        return match ($this->section()) {
            'pelanggan' => $this->report['customers']->map(fn (array $row): array => [
                $row['customer'], $row['invoice_count'], $row['invoice_amount'], $row['paid'], $row['balance'],
            ]),
            'mutasi' => collect([
                ['Saldo awal', $this->report['movement']['opening']],
                ['Penambahan invoice', $this->report['movement']['invoices']],
                ['Pembayaran', -$this->report['movement']['payments']],
                ['Diskon dan retur', -$this->report['movement']['adjustments']],
                ['Saldo akhir mutasi', $this->report['movement']['ending']],
            ]),
            'penambahan' => $this->invoiceRows($this->report['periodInvoices'], false),
            'pembayaran' => $this->report['payments']->map(fn ($payment): array => [
                $payment->NOBAYAR,
                $payment->TGLBAYAR?->format('d/m/Y'),
                $payment->CUSTOMER,
                $payment->METODE_PEMBAYARAN ?? '-',
                $payment->details->pluck('NOFAKTUR')->join(', '),
                (float) $payment->details->sum('NILAI_BAYAR'),
            ]),
            'aging' => $this->agingRows(),
            default => $this->invoiceRows($this->report['invoices']),
        };
    }

    public function headings(): array
    {
        return [
            [strtoupper($this->titleText())],
            ['Posisi per '.date('d F Y', strtotime($this->filters['tanggalPosisi']))],
            $this->columnHeadings(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->freezePane('A4');
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->getStyle("A1:{$lastColumn}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
        ]);
        foreach ($this->numericColumns() as $column) {
            $sheet->getStyle("{$column}3")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            if ($highestRow >= 4) {
                $sheet->getStyle("{$column}4:{$column}{$highestRow}")->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("{$column}4:{$column}{$highestRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        if ($this->section() === 'pelanggan' && $highestRow >= 4) {
            $sheet->getStyle("B4:B{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
        }
        if ($this->section() === 'aging' && $this->filters['agingDetail'] === 'all' && $highestRow >= 4) {
            $sheet->getColumnDimension('B')->setAutoSize(false);
            $sheet->getColumnDimension('B')->setWidth(17);
            $sheet->getStyle("B4:B{$highestRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $sheet->getStyle("B4:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            for ($row = 4; $row <= $highestRow; $row++) {
                $label = (string) $sheet->getCell("A{$row}")->getValue();
                $description = (string) $sheet->getCell("B{$row}")->getValue();
                $summaryTitle = (string) $sheet->getCell("A{$row}")->getValue();
                $customerSubtotalLabel = (string) $sheet->getCell("D{$row}")->getValue();
                $isCustomerSubtotal = $customerSubtotalLabel === 'SUB TTL';
                $isSummaryHeader = $summaryTitle === 'Subtotal Per Kategori Umur';
                $isSummaryTotal = $label === 'Total Semua Umur';
                if ($isCustomerSubtotal || $isSummaryHeader || $isSummaryTotal) {
                    $color = $isCustomerSubtotal
                        ? 'DCFCE7'
                        : ($isSummaryTotal ? 'FDE68A' : 'E2E8F0');
                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                    ]);
                }
            }
            $sheet->getStyle("A4:E{$highestRow}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("A4:{$lastColumn}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        return [];
    }

    public function title(): string
    {
        return mb_substr($this->titleText(), 0, 31);
    }

    private function invoiceRows(Collection $rows, bool $withSettlement = true): Collection
    {
        return $rows->map(function (array $row) use ($withSettlement): array {
            $base = [
                $row['invoice']->NOFAKTUR,
                $row['invoice']->TGLFAKTUR?->format('d/m/Y'),
                $row['invoice']->TGLJT?->format('d/m/Y'),
                $row['invoice']->CUSTOMER,
                $row['invoice_amount'],
            ];
            return $withSettlement
                ? [...$base, $row['paid'], $row['balance'], $row['age_label'], $row['status']]
                : [...$base, $row['status']];
        });
    }

    private function agingRows(): Collection
    {
        if ($this->filters['agingDetail'] === 'all') {
            $labels = [
                'belum_jatuh_tempo' => 'Belum JT',
                '1_30' => '1-30 Hari',
                '31_60' => '31-60 Hari',
                '61_90' => '61-90 Hari',
                'lebih_90' => '> 90 Hari',
            ];

            $rows = collect();
            foreach ($labels as $key => $label) {
                $groupRows = $this->report['agingGroups']->get($key, collect())
                    ->sortBy(fn (array $row): string => trim((string) $row['invoice']->CUSTOMER).'|'.trim((string) $row['invoice']->NOFAKTUR))
                    ->values();

                foreach ($groupRows->groupBy(fn (array $row): string => trim((string) $row['invoice']->CUSTOMER)) as $customer => $customerRows) {
                    foreach ($customerRows->sortBy(fn (array $row): string => trim((string) $row['invoice']->NOFAKTUR))->values() as $row) {
                        $customerName = (string) ($row['customer_name'] ?? '');
                        $customerLabel = (string) $row['invoice']->CUSTOMER;
                        if ($customerName !== '') {
                            $customerLabel .= ' - '.$customerName;
                        }

                        $rows->push([
                            $label,
                            $row['invoice']->NOFAKTUR,
                            $row['invoice']->TGLFAKTUR?->format('d/m/Y'),
                            $row['invoice']->TGLJT?->format('d/m/Y'),
                            $customerLabel,
                            $row['invoice_amount'],
                            $row['paid'],
                            $row['balance'],
                        ]);
                    }

                    $rows->push([
                        '',
                        '',
                        '',
                        'SUB TTL',
                        $customer,
                        $customerRows->sum('invoice_amount'),
                        $customerRows->sum('paid'),
                        $customerRows->sum('balance'),
                    ]);
                }
            }

            $allRows = $this->report['agingGroups']->flatten(1);
            $rows->push([
                'Subtotal Per Kategori Umur',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ]);
            foreach ($labels as $key => $label) {
                $groupRows = $this->report['agingGroups']->get($key, collect());
                $rows->push([
                    $label,
                    $groupRows->count(),
                    '',
                    '',
                    '',
                    $groupRows->sum('invoice_amount'),
                    $groupRows->sum('paid'),
                    $groupRows->sum('balance'),
                ]);
            }
            $rows->push([
                'Total Semua Umur',
                $allRows->count(),
                '',
                '',
                '',
                $allRows->sum('invoice_amount'),
                $allRows->sum('paid'),
                $allRows->sum('balance'),
            ]);

            return $rows;
        }

        if ($this->filters['agingFilter'] !== '' || $this->filters['agingDetail'] === 'all') {
            return $this->invoiceRows($this->report['invoices']);
        }

        $labels = [
            'belum_jatuh_tempo' => 'Belum Jatuh Tempo', '1_30' => '1–30 Hari',
            '31_60' => '31–60 Hari', '61_90' => '61–90 Hari', 'lebih_90' => '> 90 Hari',
        ];
        return collect($labels)->map(fn (string $label, string $key): array => [$label, $this->report['aging'][$key]]); 
    }

    private function section(): string
    {
        return $this->filters['reportSection'];
    }

    private function titleText(): string
    {
        return match ($this->section()) {
            'pelanggan' => 'Daftar Pelanggan Piutang',
            'aging' => 'Umur Piutang',
            'mutasi' => 'Mutasi Piutang',
            'penambahan' => 'Penambahan Piutang',
            'pembayaran' => 'Pembayaran Piutang',
            default => 'Laporan Piutang Per Faktur',
        };
    }

    private function columnHeadings(): array
    {
        return match ($this->section()) {
            'pelanggan' => ['Kode / Nama Pelanggan', 'Invoice', 'Nilai', 'Terbayar', 'Saldo'],
            'mutasi' => ['Keterangan', 'Nilai'],
            'penambahan' => ['Nomor Faktur', 'Tanggal', 'Jatuh Tempo', 'Pelanggan', 'Nilai Piutang', 'Status'],
            'pembayaran' => ['No. Bayar', 'Tanggal', 'Pelanggan', 'Metode', 'Invoice', 'Jumlah'],
            'aging' => $this->filters['agingDetail'] === 'all'
                ? ['Umur', 'Nomor Faktur', 'Tanggal', 'Jatuh Tempo', 'Pelanggan', 'Nilai', 'Terbayar', 'Sisa']
                : ($this->filters['agingFilter'] !== ''
                    ? ['Nomor Faktur', 'Tanggal', 'Jatuh Tempo', 'Pelanggan', 'Nilai', 'Terbayar', 'Sisa', 'Umur', 'Status']
                    : ['Kelompok Umur', 'Saldo']),
            default => ['Nomor Faktur', 'Tanggal', 'Jatuh Tempo', 'Pelanggan', 'Nilai', 'Terbayar', 'Sisa', 'Umur', 'Status'],
        };
    }

    private function numericColumns(): array
    {
        return match ($this->section()) {
            'pelanggan' => ['B', 'C', 'D', 'E'],
            'mutasi', 'aging' => ($this->section() === 'aging' && $this->filters['agingDetail'] === 'all')
                ? ['F', 'G', 'H']
                : (($this->section() === 'aging' && $this->filters['agingFilter'] !== '') ? ['E', 'F', 'G'] : ['B']),
            'penambahan' => ['E'],
            'pembayaran' => ['F'],
            default => ['E', 'F', 'G'],
        };
    }
}
