<?php

namespace App\Exports;

use App\Models\AccountMapping;
use App\Models\Coa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ImportTemplateExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly string $type) {}

    public static function templates(): array
    {
        return [
            'penjualan' => [
                'title' => 'Template Import Penjualan',
                'description' => 'Untuk transaksi penjualan dari aplikasi lama. Customer yang belum terdaftar akan otomatis dibuat dari kode dan nama pelanggan.',
                'icon' => 'bi bi-receipt-cutoff',
            ],
            'hutang' => [
                'title' => 'Template Import Pembelian',
                'description' => 'Untuk transaksi pembelian mulai tanggal cutover. Supplier yang belum terdaftar akan otomatis dibuat dari kode dan nama supplier.',
                'icon' => 'bi bi-journal-minus',
            ],
            'saldo_piutang' => [
                'title' => 'Template Saldo Piutang',
                'description' => 'Untuk daftar piutang outstanding. Customer yang belum terdaftar akan otomatis dibuat dari kode dan nama pelanggan.',
                'icon' => 'bi bi-cash-coin',
            ],
            'saldo_hutang' => [
                'title' => 'Template Saldo Hutang',
                'description' => 'Untuk daftar hutang outstanding. Supplier yang belum terdaftar akan otomatis dibuat dari kode dan nama supplier.',
                'icon' => 'bi bi-wallet2',
            ],
        ];
    }

    public function headings(): array
    {
        return match ($this->type) {
            'penjualan' => [
                'tanggal',
                'no_invoice',
                'kode_pelanggan',
                'nama_pelanggan',
                'keterangan',
                'kode_akun_piutang',
                'kode_akun_penjualan',
                'kode_akun_pajak_keluaran',
                'nilai_penjualan',
                'nilai_pajak',
                'total_invoice',
                'jatuh_tempo',
            ],
            'hutang' => [
                'tanggal',
                'no_faktur',
                'kode_supplier',
                'nama_supplier',
                'keterangan',
                'kode_akun_beban_pembelian',
                'kode_akun_hutang',
                'kode_akun_pajak_masukan',
                'nilai_dpp',
                'nilai_pajak',
                'total_hutang',
                'jatuh_tempo',
            ],
            'saldo_piutang' => [
                'tanggal_invoice',
                'no_invoice',
                'kode_pelanggan',
                'nama_pelanggan',
                'keterangan',
                'kode_akun_piutang',
                'saldo_piutang',
                'jatuh_tempo',
            ],
            'saldo_hutang' => [
                'tanggal_faktur',
                'no_faktur',
                'kode_supplier',
                'nama_supplier',
                'keterangan',
                'kode_akun_hutang',
                'saldo_hutang',
                'jatuh_tempo',
            ],
            default => [],
        };
    }

    public function collection(): Collection
    {
        $transactionDate = now()->startOfMonth();
        $openingDate = $transactionDate->copy()->subDay();
        $receivable = $this->postingAccountCode('accounts_receivable', 'Aset');
        $revenue = $this->postingAccountCode('default_sales_revenue', 'Pendapatan');
        $outputTax = $this->postingAccountCode('output_tax', 'Kewajiban');
        $payable = $this->postingAccountCode('accounts_payable', 'Kewajiban');
        $purchaseExpense = $this->firstPostingAccountCode('Beban');
        $inputTax = $this->postingAccountCode('input_tax', 'Aset');

        return collect([
            match ($this->type) {
                'penjualan' => [$transactionDate->toDateString(), 'INV-0001', 'CUST001', 'PT Contoh Pelanggan', 'Penjualan jasa', $receivable, $revenue, $outputTax, 1000000, 110000, 1110000, $transactionDate->copy()->addDays(30)->toDateString()],
                'hutang' => [$transactionDate->toDateString(), 'FB-0001', 'SUP001', 'PT Contoh Supplier', 'Pembelian operasional', $purchaseExpense, $payable, $inputTax, 1000000, 110000, 1110000, $transactionDate->copy()->addDays(30)->toDateString()],
                'saldo_piutang' => [$openingDate->toDateString(), 'INV-LAMA-001', 'CUST001', 'PT Contoh Pelanggan', 'Piutang dari aplikasi lama', $receivable, 2500000, $transactionDate->copy()->addDays(29)->toDateString()],
                'saldo_hutang' => [$openingDate->toDateString(), 'FB-LAMA-001', 'SUP001', 'PT Contoh Supplier', 'Hutang dari aplikasi lama', $payable, 1750000, $transactionDate->copy()->addDays(29)->toDateString()],
                default => [],
            },
        ]);
    }

    public function columnFormats(): array
    {
        return match ($this->type) {
            'penjualan', 'hutang' => [
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_TEXT,
                'G' => NumberFormat::FORMAT_TEXT,
                'H' => NumberFormat::FORMAT_TEXT,
            ],
            'saldo_piutang', 'saldo_hutang' => [
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_TEXT,
            ],
            default => [],
        };
    }

    private function postingAccountCode(string $mappingKey, string $expectedType): string
    {
        $mapping = AccountMapping::query()
            ->with('account')
            ->where('mapping_key', $mappingKey)
            ->first();
        $mappedAccount = $mapping?->account;

        if ($mappedAccount
            && $mappedAccount->is_active
            && $mappedAccount->level === 'Akun Posting'
            && strcasecmp((string) $mappedAccount->tipe, $expectedType) === 0) {
            return (string) $mappedAccount->kode_akun;
        }

        return $this->firstPostingAccountCode($expectedType);
    }

    private function firstPostingAccountCode(string $expectedType): string
    {
        return (string) Coa::query()
            ->where('is_active', true)
            ->where('level', 'Akun Posting')
            ->where('tipe', $expectedType)
            ->orderBy('kode_akun')
            ->value('kode_akun');
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->freezePane('A2');

        return [];
    }

    public function title(): string
    {
        return str_replace('_', ' ', ucwords($this->type, '_'));
    }
}
