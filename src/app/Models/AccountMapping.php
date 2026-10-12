<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountMapping extends Model
{
    protected $table = 'account_mappings';

    public $timestamps = false;

    protected $fillable = [
        'mapping_key',
        'account_id',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'account_id');
    }

    public static function mappingKeys(): array
    {
        return [
            'retained_earnings' => 'Laba Ditahan',
            'current_year_profit' => 'Laba Tahun Berjalan',
            'cash_main' => 'Kas Utama',
            'cash_account' => 'Kas',
            'bank_account' => 'Bank',
            'accounts_receivable' => 'Piutang Usaha',
            'driver_receivable' => 'Piutang Sopir/Karyawan',
            'receivable_transfer_bank' => 'Bank Transfer Pelunasan Piutang',
            'receivable_pph23' => 'PPh Pasal 23 Pelunasan Piutang',
            'receivable_payment_loss' => 'Kerugian Pembayaran Piutang',
            'receivable_bank_admin' => 'Beban Administrasi Bank',
            'accounts_payable' => 'Hutang Usaha',
            'input_tax' => 'Pajak Masukan',
            'output_tax' => 'Pajak Keluaran',
            'default_sales_revenue' => 'Pendapatan Penjualan',
            'sales_revenue_eks' => 'Pendapatan Ekspedisi (EKS)',
            'sales_revenue_ren' => 'Pendapatan Sewa Unit Armada (REN)',
            'sales_revenue_cold_room' => 'Pendapatan Sewa Cold Room (CO)',
            'equity_capital' => 'Modal Utama',
            'prive' => 'Prive / Dividen',
        ];
    }

    public static function mappingDescriptions(): array
    {
        return [
            'retained_earnings' => 'Default proses tutup buku untuk menampung akumulasi laba/rugi tahun-tahun sebelumnya.',
            'current_year_profit' => 'Default laporan dan proses tutup buku untuk laba/rugi tahun berjalan.',
            'cash_main' => 'Default akun kas/bank utama pada proses yang membutuhkan akun kas utama.',
            'cash_account' => 'Default transaksi pelunasan piutang atau hutang dengan metode CASH.',
            'bank_account' => 'Default transaksi pelunasan piutang atau hutang dengan metode Transfer, Giro, Cek, atau QRIS jika akun pembayaran tidak dipilih.',
            'accounts_receivable' => 'Default jurnal penjualan kredit, saldo piutang, pelunasan piutang, laporan piutang, dan rekonsiliasi piutang.',
            'driver_receivable' => 'Akun kontrol khusus tanggungan dan piutang sopir/karyawan.',
            'receivable_transfer_bank' => 'Default akun penerimaan bank ketika Pelunasan Piutang menggunakan metode TRANSFER.',
            'receivable_pph23' => 'Default akun aset PPh Pasal 23 yang dipotong customer pada Pelunasan Piutang.',
            'receivable_payment_loss' => 'Default akun beban untuk potongan atau selisih Kerugian Pembayaran Piutang.',
            'receivable_bank_admin' => 'Default akun Beban Administrasi Bank pada Pelunasan Piutang.',
            'accounts_payable' => 'Default jurnal pembelian kredit, saldo hutang, pelunasan hutang, laporan hutang, dan rekonsiliasi hutang.',
            'input_tax' => 'Default akun PPN Masukan pada transaksi pembelian yang menggunakan pajak.',
            'output_tax' => 'Default akun PPN Keluaran pada transaksi penjualan yang menggunakan pajak.',
            'default_sales_revenue' => 'Default akun pendapatan pada input penjualan/piutang berjalan.',
            'sales_revenue_eks' => 'Akun 04010101 Pendapatan Ekspedisi untuk jurnal invoice sinkronisasi dengan sumber EKS.',
            'sales_revenue_ren' => 'Akun 04010201 Pendapatan Sewa Unit Armada untuk jurnal invoice sinkronisasi dengan sumber REN.',
            'sales_revenue_cold_room' => 'Akun 04010202 Pendapatan Sewa Cold Room untuk jurnal invoice sinkronisasi dengan sumber CO.',
            'equity_capital' => 'Default akun modal yang digunakan dalam laporan dan validasi proses tutup buku.',
            'prive' => 'Default akun prive atau dividen untuk laporan perubahan modal dan proses tutup buku.',
        ];
    }

    public static function mappingGroups(): array
    {
        return [
            'Pelunasan Piutang' => [
                'accounts_receivable',
                'driver_receivable',
                'cash_account',
                'receivable_transfer_bank',
                'receivable_pph23',
                'receivable_payment_loss',
                'receivable_bank_admin',
            ],
            'Penjualan dan Pembelian' => [
                'default_sales_revenue',
                'sales_revenue_eks',
                'sales_revenue_ren',
                'sales_revenue_cold_room',
                'accounts_payable',
                'bank_account',
                'input_tax',
                'output_tax',
            ],
            'Kas, Modal, dan Tutup Buku' => [
                'cash_main',
                'retained_earnings',
                'current_year_profit',
                'equity_capital',
                'prive',
            ],
        ];
    }

    public static function getCoaId(string $mappingKey): ?int
    {
        $mapping = static::query()
            ->where('mapping_key', $mappingKey)
            ->first();

        if ($mapping) {
            return (int) $mapping->account_id;
        }

        $coa = static::findDefaultCoa($mappingKey);

        if (! $coa) {
            $coa = static::createDefaultCoa($mappingKey);
        }

        if (! $coa) {
            return null;
        }

        static::query()->updateOrCreate(
            ['mapping_key' => $mappingKey],
            ['account_id' => $coa->id]
        );

        return (int) $coa->id;
    }

    private static function findDefaultCoa(string $mappingKey): ?Coa
    {
        return match ($mappingKey) {
            'accounts_receivable' => Coa::query()
                ->where('nama_akun', 'like', '%Piutang Usaha%')
                ->where('tipe', 'Aset')
                ->first(),
            'driver_receivable' => Coa::query()->where('nama_akun','like','%Piutang%Sopir%')->where('tipe','Aset')->first(),
            'cash_account', 'cash_main' => Coa::query()
                ->where('nama_akun', 'like', '%Kas%')
                ->where('tipe', 'Aset')
                ->first(),
            'bank_account' => Coa::query()
                ->where('nama_akun', 'like', '%Bank%')
                ->where('tipe', 'Aset')
                ->first(),
            'receivable_transfer_bank' => Coa::query()
                ->where('kode_akun', '01010201')
                ->where('tipe', 'Aset')
                ->first(),
            'receivable_pph23' => Coa::query()
                ->where('kode_akun', '01010502')
                ->where('tipe', 'Aset')
                ->first(),
            'receivable_payment_loss' => Coa::query()
                ->where('kode_akun', '05040103')
                ->where('tipe', 'Beban')
                ->first(),
            'receivable_bank_admin' => Coa::query()
                ->where('kode_akun', '05040102')
                ->where('tipe', 'Beban')
                ->first(),
            'default_sales_revenue' => Coa::query()
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%Pendapatan Penjualan%')
                        ->orWhere('nama_akun', 'like', '%Pendapatan Usaha%');
                })
                ->where('tipe', 'Pendapatan')
                ->first(),
            'accounts_payable' => Coa::query()
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%Hutang Usaha%')
                        ->orWhere('nama_akun', 'like', '%Utang Usaha%');
                })
                ->where('tipe', 'Kewajiban')
                ->first(),
            'input_tax' => Coa::query()
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%Pajak Masukan%')
                        ->orWhere('nama_akun', 'like', '%PPN Masukan%');
                })
                ->where('tipe', 'Aset')->first(),
            'output_tax' => Coa::query()
                ->where(function ($query) {
                    $query->where('nama_akun', 'like', '%Pajak Keluaran%')
                        ->orWhere('nama_akun', 'like', '%PPN Keluaran%');
                })
                ->where('tipe', 'Kewajiban')->first(),
            default => null,
        };
    }

    private static function createDefaultCoa(string $mappingKey): ?Coa
    {
        $defaults = [
            'accounts_receivable' => [
                'kode_akun' => '11020000',
                'nama_akun' => 'Piutang Usaha',
                'tipe' => 'Aset',
                'kategori' => 'Aktiva Lancar',
                'level' => 'Akun Posting',
                'saldo_normal' => 'Debit',
                'is_active' => true,
            ],
            'driver_receivable' => ['kode_akun'=>'01010401','nama_akun'=>'Piutang Sopir/Karyawan','tipe'=>'Aset','kategori'=>'Aktiva Lancar','level'=>'Akun Posting','saldo_normal'=>'Debit','is_active'=>true],
            'cash_account' => [
                'kode_akun' => '11000000',
                'nama_akun' => 'Kas',
                'tipe' => 'Aset',
                'kategori' => 'Aktiva Lancar',
                'level' => 'Akun Posting',
                'saldo_normal' => 'Debit',
                'is_active' => true,
            ],
            'cash_main' => [
                'kode_akun' => '11000000',
                'nama_akun' => 'Kas',
                'tipe' => 'Aset',
                'kategori' => 'Aktiva Lancar',
                'level' => 'Akun Posting',
                'saldo_normal' => 'Debit',
                'is_active' => true,
            ],
            'bank_account' => [
                'kode_akun' => '11010000',
                'nama_akun' => 'Bank',
                'tipe' => 'Aset',
                'kategori' => 'Aktiva Lancar',
                'level' => 'Akun Posting',
                'saldo_normal' => 'Debit',
                'is_active' => true,
            ],
            'default_sales_revenue' => [
                'kode_akun' => '41000000',
                'nama_akun' => 'Pendapatan Usaha',
                'tipe' => 'Pendapatan',
                'kategori' => 'Pendapatan Usaha',
                'level' => 'Akun Posting',
                'saldo_normal' => 'Kredit',
                'is_active' => true,
            ],
            'accounts_payable' => [
                'kode_akun' => '21000000',
                'nama_akun' => 'Utang Usaha',
                'tipe' => 'Kewajiban',
                'kategori' => 'Kewajiban Lancar',
                'level' => 'Akun Posting',
                'saldo_normal' => 'Kredit',
                'is_active' => true,
            ],
            'input_tax' => [
                'kode_akun' => '01010503',
                'nama_akun' => 'PPN Masukan',
                'tipe' => 'Aset',
                'kategori' => 'Pajak Dibayar Dimuka',
                'level' => 'Akun Posting',
                'saldo_normal' => 'Debit',
                'is_active' => true,
            ],
            'output_tax' => [
                'kode_akun' => '02010301',
                'nama_akun' => 'Pajak Keluaran',
                'tipe' => 'Kewajiban',
                'kategori' => 'Hutang Pajak',
                'level' => 'Akun Posting',
                'saldo_normal' => 'Kredit',
                'is_active' => true,
            ],
        ];

        if (! isset($defaults[$mappingKey])) {
            return null;
        }

        $attributes = $defaults[$mappingKey];

        return Coa::query()->firstOrCreate(
            ['kode_akun' => $attributes['kode_akun']],
            $attributes
        );
    }
}
