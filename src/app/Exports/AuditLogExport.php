<?php

namespace App\Exports;

use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditLogExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters = []) {}

    public function collection(): Collection
    {
        $query = AuditLog::with('user')->latest();

        if (! empty($this->filters['module'])) {
            $query->where('module', $this->filters['module']);
        }

        if (! empty($this->filters['action'])) {
            $query->where('action', $this->filters['action']);
        }

        if (! empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        if (! empty($this->filters['tanggal_awal'])) {
            $query->whereDate('created_at', '>=', $this->filters['tanggal_awal']);
        }

        if (! empty($this->filters['tanggal_akhir'])) {
            $query->whereDate('created_at', '<=', $this->filters['tanggal_akhir']);
        }

        return $query->get()->map(fn(AuditLog $log) => [
            $log->created_at?->format('d/m/Y H:i:s'),
            $log->user?->name ?? '-',
            $log->module,
            $log->action,
            $log->description,
            $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE) : '',
            $log->ip_address,
        ]);
    }

    public function headings(): array
    {
        return ['Waktu', 'User', 'Module', 'Aksi', 'Deskripsi', 'Metadata', 'IP Address'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }

    public function title(): string
    {
        return 'Audit Trail';
    }
}
