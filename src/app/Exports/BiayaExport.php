<?php

namespace App\Exports;

use App\Models\Coa;
use App\Services\JournalQueryService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BiayaExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly string $tanggalAwal,
        private readonly string $tanggalAkhir,
        private readonly string $search = '',
        private readonly array|int|null $coaIds = null,
        private readonly bool $showZero = false,
    ) {
    }

    public function collection()
    {
        $service = app(JournalQueryService::class);
        $coasQuery = Coa::active()->posting()->where('tipe', 'Beban')->orderBy('kode_akun');

        if ($this->search !== '') {
            $coasQuery->where(function ($query): void {
                $query->where('kode_akun', 'like', "%{$this->search}%")
                    ->orWhere('nama_akun', 'like', "%{$this->search}%");
            });
        }

        $coas = $coasQuery->get();
        $totals = $coas->isEmpty()
            ? []
            : $service->getSumByMultipleCoaIdsExcludingClosing($this->tanggalAwal, $this->tanggalAkhir, $coas->pluck('id')->all());

        $data = collect([
            ['Ringkasan Biaya', '', '', '', ''],
            ['Kode Akun', 'Nama Akun', 'Debit', 'Kredit', 'Saldo Biaya'],
        ]);

        foreach ($coas as $coa) {
            $debit = (float) ($totals[$coa->id]['debit'] ?? 0);
            $kredit = (float) ($totals[$coa->id]['kredit'] ?? 0);
            $saldo = $debit - $kredit;

            if (! $this->showZero && abs($debit) <= 0.005 && abs($kredit) <= 0.005) {
                continue;
            }

            $data->push([$coa->kode_akun, $coa->nama_akun, $debit, $kredit, $saldo]);
        }

        $openCoaIds = collect(is_array($this->coaIds) ? $this->coaIds : [$this->coaIds])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($openCoaIds->isNotEmpty()) {
            $openCoas = Coa::active()
                ->posting()
                ->where('tipe', 'Beban')
                ->whereIn('id', $openCoaIds)
                ->orderBy('kode_akun')
                ->get();

            foreach ($openCoas as $coa) {
                $data->push(['', '', '', '', '']);
                $data->push(['Detail ' . $coa->kode_akun . ' - ' . $coa->nama_akun, '', '', '', '']);
                $data->push(['Tanggal', 'No. Jurnal', 'Deskripsi', 'Debit', 'Kredit', 'Saldo']);

                $running = 0.0;
                $items = $service->getSmartQueryExcludingClosing($this->tanggalAwal, $this->tanggalAkhir)
                    ->where('coa_id', $coa->id)
                    ->with('jurnal')
                    ->orderBy('created_at')
                    ->get();

                foreach ($items as $item) {
                    $running += (float) $item->debit - (float) $item->kredit;
                    $data->push([
                        $item->jurnal->tanggal->format('d/m/Y'),
                        $item->jurnal->no_jurnal,
                        $item->deskripsi ?? $item->jurnal->keterangan ?? '-',
                        (float) $item->debit,
                        (float) $item->kredit,
                        $running,
                    ]);
                }
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            [config('app.company_name')],
            ['LAPORAN BIAYA'],
            ['Periode: ' . date('d F Y', strtotime($this->tanggalAwal)) . ' - ' . date('d F Y', strtotime($this->tanggalAkhir))],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setWidth($column === 'C' ? 42 : 18);
        }

        $sheet->getStyle('A1:F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:F2')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFont()->setSize(14);

        $lastRow = $sheet->getHighestRow();
        for ($row = 4; $row <= $lastRow; $row++) {
            $cellA = (string) $sheet->getCell("A{$row}")->getValue();
            $cellB = (string) $sheet->getCell("B{$row}")->getValue();

            if (in_array($cellA, ['Kode Akun', 'Tanggal'], true)) {
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                ]);
            }

            if ($cellB === '' && str_contains($cellA, 'Biaya')) {
                $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            }

            $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("C{$row}:F{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("C{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return [];
    }

    public function title(): string
    {
        return 'Laporan Biaya';
    }
}
