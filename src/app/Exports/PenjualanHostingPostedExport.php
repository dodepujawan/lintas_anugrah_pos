<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenjualanHostingPostedExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private readonly Collection $rows, private readonly array $filters = []) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn ($sale): array => [
            $sale->invoice,
            $sale->tglinvoice ?: $sale->tgl_kirim,
            $sale->posted_at ?? null,
            $sale->customer,
            $sale->namacust,
            $sale->jenis ?? $sale->sumber,
            (float) $sale->dpp,
            (float) $sale->nilai_ppn,
            (float) $sale->total,
            $sale->no_jurnal,
            $sale->status_jurnal,
        ]);
    }

    public function headings(): array
    {
        $dateLabel = ($this->filters['date_field'] ?? 'invoice') === 'posted' ? 'Tanggal Posting' : 'Tanggal Invoice';
        $period = trim(trim((string) ($this->filters['tanggal_awal'] ?? '')).' s/d '.trim((string) ($this->filters['tanggal_akhir'] ?? '')), ' s/d');

        return [
            ['REPORT PENJUALAN HOSTING POSTED'],
            ['Filter: '.$dateLabel.' | Periode: '.($period !== '' ? $period : 'Semua tanggal')],
            ['Invoice', 'Tanggal Invoice', 'Tanggal Posting', 'Kode Customer', 'Nama Customer', 'Jenis', 'DPP', 'PPN', 'Total', 'No Jurnal', 'Status'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->getStyle('A1:K1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:K3')->getFont()->setBold(true);
        $sheet->freezePane('A4');

        return [];
    }
}
