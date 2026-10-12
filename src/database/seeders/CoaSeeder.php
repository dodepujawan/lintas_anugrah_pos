<?php

namespace Database\Seeders;

use App\Models\Coa;
use Illuminate\Database\Seeder;

class CoaSeeder extends Seeder
{
    private function normalizeKodeAkun(string $kodeAkun): string
    {
        $digits = preg_replace('/\D/', '', $kodeAkun);

        if (strlen($digits) === 5) {
            return $digits . '000';
        }

        return str_pad($digits, 8, '0', STR_PAD_LEFT);
    }

    public function run(): void
    {
        $coas = [
            // Aset - Aktiva Lancar
            ['kode_akun' => '1-1000', 'nama_akun' => 'Kas', 'tipe' => 'Aset', 'kategori' => 'Aktiva Lancar', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1-1010', 'nama_akun' => 'Bank', 'tipe' => 'Aset', 'kategori' => 'Aktiva Lancar', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1-1020', 'nama_akun' => 'Piutang Usaha', 'tipe' => 'Aset', 'kategori' => 'Aktiva Lancar', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1-1030', 'nama_akun' => 'Perlengkapan', 'tipe' => 'Aset', 'kategori' => 'Aktiva Lancar', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1-1040', 'nama_akun' => 'Persediaan Barang', 'tipe' => 'Aset', 'kategori' => 'Aktiva Lancar', 'saldo_normal' => 'Debit'],

            // Aset - Aktiva Tetap
            ['kode_akun' => '1-2000', 'nama_akun' => 'Tanah', 'tipe' => 'Aset', 'kategori' => 'Aktiva Tetap', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1-2010', 'nama_akun' => 'Gedung', 'tipe' => 'Aset', 'kategori' => 'Aktiva Tetap', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1-2020', 'nama_akun' => 'Akumulasi Penyusutan Gedung', 'tipe' => 'Aset', 'kategori' => 'Aktiva Tetap', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '1-2030', 'nama_akun' => 'Kendaraan', 'tipe' => 'Aset', 'kategori' => 'Aktiva Tetap', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '1-2040', 'nama_akun' => 'Peralatan', 'tipe' => 'Aset', 'kategori' => 'Aktiva Tetap', 'saldo_normal' => 'Debit'],

            // Kewajiban - Kewajiban Lancar
            ['kode_akun' => '2-1000', 'nama_akun' => 'Utang Usaha', 'tipe' => 'Kewajiban', 'kategori' => 'Kewajiban Lancar', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '2-1010', 'nama_akun' => 'Utang Gaji', 'tipe' => 'Kewajiban', 'kategori' => 'Kewajiban Lancar', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '2-1020', 'nama_akun' => 'Utang Pajak', 'tipe' => 'Kewajiban', 'kategori' => 'Kewajiban Lancar', 'saldo_normal' => 'Kredit'],

            // Kewajiban - Kewajiban Jangka Panjang
            ['kode_akun' => '2-2000', 'nama_akun' => 'Utang Bank', 'tipe' => 'Kewajiban', 'kategori' => 'Kewajiban Jangka Panjang', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '2-2010', 'nama_akun' => 'Utang Obligasi', 'tipe' => 'Kewajiban', 'kategori' => 'Kewajiban Jangka Panjang', 'saldo_normal' => 'Kredit'],

            // Ekuitas
            ['kode_akun' => '3-1000', 'nama_akun' => 'Modal', 'tipe' => 'Ekuitas', 'kategori' => 'Modal', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '3-1010', 'nama_akun' => 'Prive', 'tipe' => 'Ekuitas', 'kategori' => 'Prive', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '3-1020', 'nama_akun' => 'Laba Ditahan', 'tipe' => 'Ekuitas', 'kategori' => 'Modal', 'saldo_normal' => 'Kredit'],

            // Pendapatan
            ['kode_akun' => '4-1000', 'nama_akun' => 'Pendapatan Usaha', 'tipe' => 'Pendapatan', 'kategori' => 'Pendapatan Usaha', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => '4-1010', 'nama_akun' => 'Pendapatan Bunga', 'tipe' => 'Pendapatan', 'kategori' => 'Pendapatan Lainnya', 'saldo_normal' => 'Kredit'],

            // Beban
            ['kode_akun' => '5-1000', 'nama_akun' => 'Beban Gaji', 'tipe' => 'Beban', 'kategori' => 'Beban Usaha', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5-1010', 'nama_akun' => 'Beban Sewa', 'tipe' => 'Beban', 'kategori' => 'Beban Usaha', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5-1020', 'nama_akun' => 'Beban Listrik & Air', 'tipe' => 'Beban', 'kategori' => 'Beban Usaha', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5-1030', 'nama_akun' => 'Beban Telepon & Internet', 'tipe' => 'Beban', 'kategori' => 'Beban Usaha', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5-1040', 'nama_akun' => 'Beban Transportasi', 'tipe' => 'Beban', 'kategori' => 'Beban Usaha', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5-1050', 'nama_akun' => 'Beban Perlengkapan', 'tipe' => 'Beban', 'kategori' => 'Beban Usaha', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5-1060', 'nama_akun' => 'Beban Penyusutan', 'tipe' => 'Beban', 'kategori' => 'Beban Usaha', 'saldo_normal' => 'Debit'],
            ['kode_akun' => '5-1070', 'nama_akun' => 'Beban Lain-lain', 'tipe' => 'Beban', 'kategori' => 'Beban Lainnya', 'saldo_normal' => 'Debit'],
        ];

        foreach ($coas as $coa) {
            $kodeAkun = $this->normalizeKodeAkun($coa['kode_akun']);

            Coa::updateOrCreate(
                ['kode_akun' => $kodeAkun],
                [
                    ...$coa,
                    'kode_akun' => $kodeAkun,
                ]
            );
        }

        $this->command->info('Chart of Accounts berhasil diisi!');
    }
}
