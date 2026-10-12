<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenjualanHostingIssueExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn ($sale) => [
            $sale->invoice,
            $sale->tglinvoice ?: $sale->tgl_kirim,
            $sale->customer,
            $sale->namacust,
            $sale->jenis ?? $sale->sumber,
            (float) $sale->dpp,
            (float) $sale->nilai_ppn,
            (float) $sale->total,
            implode('; ', $sale->validation_issues),
            $sale->posting_error,
        ]);
    }

    public function headings(): array
    {
        return ['Invoice', 'Tanggal Invoice', 'Kode Customer', 'Nama Customer', 'Jenis', 'DPP', 'PPN', 'Total', 'Masalah Pra-Jurnal', 'Error Posting Terakhir'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('I:J')->getAlignment()->setWrapText(true);

        return [];
    }
}
