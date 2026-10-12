<?php

namespace App\Exports;

use App\Models\Jurnal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class JurnalUmumExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $jenis;
    protected string $periodLockStatus;
    protected string $search;

    public function __construct($tanggalAwal, $tanggalAkhir, $jenis = null, string $periodLockStatus = 'TERBUKA', string $search = '')
    {
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->jenis = $jenis;
        $this->periodLockStatus = $periodLockStatus;
        $this->search = trim($search);
    }

    public function collection()
    {
        $jurnals = Jurnal::with(['items.coa'])
            ->where('is_posted', true)
            ->whereDate('tanggal', '>=', $this->tanggalAwal)
            ->whereDate('tanggal', '<=', $this->tanggalAkhir)
            ->when($this->jenis, function ($q) {
                if ($this->jenis === 'Saldo Awal' || $this->jenis === 'Jurnal Saldo Awal') {
                    return $q->where(function ($sub) {
                        $sub->where('tipe', 'Saldo Awal')
                            ->orWhere('module_type', 'Saldo Awal')
                            ->orWhere('no_jurnal', 'like', 'SA%');
                    });
                }
                if ($this->jenis === 'Saldo Awal Hutang') {
                    return $q->where(function ($sub) {
                        $sub->where('tipe', 'Saldo Awal Hutang')
                            ->orWhere('module_type', 'Saldo Awal Hutang')
                            ->orWhere('keterangan', 'like', '%Saldo Awal Hutang%')
                            ->orWhere('keterangan', 'like', '%saldo awal hutang%')
                            ->orWhere('no_jurnal', 'like', 'SAH%');
                    });
                }
                if ($this->jenis === 'Penambahan Piutang Sopir') {
                    return $q->where(function ($sub) {
                        $sub->where('module_type', 'Piutang Sopir')
                            ->orWhere(function ($journal) {
                                $journal->where('no_jurnal', 'like', 'PS%')
                                    ->where('keterangan', 'like', '%Piutang sopir%');
                            });
                    });
                }
                if ($this->jenis === 'Pelunasan Piutang Sopir') {
                    return $q->where('module_type', 'Pelunasan Piutang Sopir');
                }
                if ($this->jenis === 'Pelunasan Hutang') {
                    return $q->where(function ($sub) {
                        $sub->where('module_type', 'Pelunasan Hutang')
                            ->orWhere(function ($journal) {
                                $journal->where('no_jurnal', 'like', 'AP%')
                                    ->where('keterangan', 'like', '%Pelunasan Hutang%');
                            });
                    });
                }
                if (in_array($this->jenis, ['Pembatalan Pelunasan Piutang', 'Pembatalan Pelunasan Hutang'], true)) {
                    return $q->where('module_type', $this->jenis);
                }
                $q->where('tipe', $this->jenis);
            })
            ->when($this->search !== '', function ($q): void {
                $like = '%'.$this->search.'%';
                $q->where(function ($sub) use ($like): void {
                    $sub->where('no_jurnal', 'like', $like)
                        ->orWhere('referensi', 'like', $like)
                        ->orWhere('keterangan', 'like', $like)
                        ->orWhere('tipe', 'like', $like)
                        ->orWhere('module_type', 'like', $like)
                        ->orWhereHas('items.coa', function ($item) use ($like): void {
                            $item->where('kode_akun', 'like', $like)
                                ->orWhere('nama_akun', 'like', $like);
                        })
                        ->orWhereHas('items', fn ($item) => $item->where('deskripsi', 'like', $like))
                        ->orWhereHas('archivedItems.coa', function ($item) use ($like): void {
                            $item->where('kode_akun', 'like', $like)
                                ->orWhere('nama_akun', 'like', $like);
                        })
                        ->orWhereHas('archivedItems', fn ($item) => $item->where('deskripsi', 'like', $like));
                });
            })
            ->where(function ($query): void {
                $query->whereHas('items.coa', fn($q) => $q->posting())
                    ->orWhereHas('archivedItems.coa', fn($q) => $q->posting());
            })
            ->with('archivedItems.coa')
            ->orderBy('tanggal')
            ->orderBy('no_jurnal')
            ->get();

        $data = collect();

        foreach ($jurnals as $jurnal) {
            $data->push([
                'TANGGAL: ' . $jurnal->tanggal->format('d/m/Y') . ' | NO: ' . $jurnal->no_jurnal . ' | TIPE: ' . $jurnal->tipe,
                '',
                '',
                '',
                ''
            ]);

            foreach ($this->combinedItems($jurnal) as $item) {
                $data->push([
                    $item->coa->kode_akun,
                    $item->coa->nama_akun,
                    $item->deskripsi ?? '-',
                    $item->debit,
                    $item->kredit,
                ]);
            }

            $data->push([
                '',
                'Keterangan: ' . ($jurnal->keterangan ?? '-'),
                '',
                $jurnal->total_debit,
                $jurnal->total_kredit
            ]);
            $data->push(['', '', '', '', '']);
        }

        return $data;
    }

    private function combinedItems(Jurnal $jurnal)
    {
        return $jurnal->items
            ->concat($jurnal->archivedItems)
            ->filter(fn($item) => $item->coa?->level === 'Akun Posting')
            ->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }

    public function headings(): array
    {
        return [
            ['JURNAL UMUM'],
            ['Periode: ' . date('d F Y', strtotime($this->tanggalAwal)) . ' - ' . date('d F Y', strtotime($this->tanggalAkhir)) . ' | Status: ' . $this->periodLockStatus],
            ['Kode Akun', 'Nama Akun', 'Deskripsi', 'Debit', 'Kredit'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
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
            $cellA = $sheet->getCell("A{$row}")->getValue();
            $cellB = $sheet->getCell("B{$row}")->getValue();

            if (str_starts_with((string) $cellA, 'TANGGAL:') || str_starts_with((string) $cellA, 'Keterangan:')) {
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1D5DB']],
                ]);
            }

            if ($cellA === 'Kode Akun' || $cellA === 'Tanggal') {
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                ]);
            }

            $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$row}:E{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$row}:E{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            // Align description cell for wrapped text
            $sheet->getStyle("C{$row}")->getAlignment()->setWrapText(true);
        }

        return $styles;
    }

    public function title(): string
    {
        return 'Jurnal Umum';
    }
}
