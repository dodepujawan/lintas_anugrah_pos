<?php

namespace App\Exports;

use App\Models\Coa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CoaExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $tipe;
    protected $level;

    public function __construct($tipe = null, $level = null)
    {
        $this->tipe = $tipe;
        $this->level = $level;
    }

    public function collection()
    {
        $query = Coa::with('parent')->orderBy('kode_akun');

        if ($this->tipe) {
            $query->where('tipe', $this->tipe);
        }

        if ($this->level) {
            $query->where('level', $this->level);
        }

        $coas = $query->get();

        $coas->each(function (Coa $coa) {
            $coa->setRelation('children', collect());
        });

        $coasById = $coas->keyBy('id');
        $rootCoas = collect();

        foreach ($coas as $coa) {
            if ($coa->parent_id && $coasById->has($coa->parent_id)) {
                $coasById->get($coa->parent_id)->children->push($coa);
            } else {
                $rootCoas->push($coa);
            }
        }

        $rows = collect();
        $this->flattenCoaTree($rootCoas, $rows);

        return $rows;
    }

    private function flattenCoaTree($items, $output, int $depth = 0): void
    {
        foreach ($items as $item) {
            $output->push([
                'kode_akun' => $item->kode_akun,
                'nama_akun' => str_repeat('    ', $depth) . $item->nama_akun,
                'level' => $item->level,
                'parent' => $item->parent ? sprintf('%s - %s', $item->parent->kode_akun, $item->parent->nama_akun) : '-',
                'tipe' => $item->tipe,
                'kategori' => $item->kategori ?: '-',
                'saldo_normal' => $item->saldo_normal_label,
                'status' => $item->is_active ? 'Aktif' : 'Nonaktif',
                'deskripsi' => $item->deskripsi ?: '-',
            ]);

            if ($item->children->isNotEmpty()) {
                $this->flattenCoaTree($item->children, $output, $depth + 1);
            }
        }
    }

    public function headings(): array
    {
        return [
            ['DAFTAR CHART OF ACCOUNTS'],
            ['Filter: ' . ($this->tipe ?: 'Semua Tipe') . ' | Level: ' . ($this->level ?: 'Semua Level')],
            ['Kode Akun', 'Nama Akun', 'Level', 'Parent', 'Tipe', 'Kategori', 'Saldo Normal', 'Status', 'Deskripsi'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(40);

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->freezePane('A4');

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A3:I3')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A4:I' . $sheet->getHighestRow())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:A' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B4:B' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('I4:I' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);

        return [];
    }

    public function title(): string
    {
        return 'COA';
    }
}
