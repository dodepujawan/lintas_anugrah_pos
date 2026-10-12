<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class McustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            ['CUSTOMER' => 'CUST001', 'NAMACUST' => 'PT Maju Jaya', 'ALAMAT1' => 'Jl. Merdeka No. 123', 'KOTA' => 'Jakarta', 'TELEPON' => '021-1234567'],
            ['CUSTOMER' => 'CUST002', 'NAMACUST' => 'CV Bersama Kita', 'ALAMAT1' => 'Jl. Sudirman No. 456', 'KOTA' => 'Surabaya', 'TELEPON' => '031-2345678'],
            ['CUSTOMER' => 'CUST003', 'NAMACUST' => 'PT Sejahtera Sentosa', 'ALAMAT1' => 'Jl. Ahmad Yani No. 789', 'KOTA' => 'Bandung', 'TELEPON' => '022-3456789'],
            ['CUSTOMER' => 'CUST004', 'NAMACUST' => 'CV Indo Makmur', 'ALAMAT1' => 'Jl. Gatot Subroto No. 321', 'KOTA' => 'Medan', 'TELEPON' => '061-4567890'],
            ['CUSTOMER' => 'CUST005', 'NAMACUST' => 'PT Cahaya Bisnis', 'ALAMAT1' => 'Jl. Diponegoro No. 654', 'KOTA' => 'Makassar', 'TELEPON' => '0411-5678901'],
            ['CUSTOMER' => 'CUST006', 'NAMACUST' => 'CV Toko Elektronik', 'ALAMAT1' => 'Jl. Imam Bonjol No. 987', 'KOTA' => 'Jakarta', 'TELEPON' => '021-6789012'],
            ['CUSTOMER' => 'CUST007', 'NAMACUST' => 'PT Industri Tekstil', 'ALAMAT1' => 'Jl. Jenderal Sudirman No. 147', 'KOTA' => 'Semarang', 'TELEPON' => '024-7890123'],
            ['CUSTOMER' => 'CUST008', 'NAMACUST' => 'CV Perdagangan Umum', 'ALAMAT1' => 'Jl. Ade Sucipto No. 258', 'KOTA' => 'Yogyakarta', 'TELEPON' => '0274-8901234'],
        ];

        foreach ($customers as $customer) {
            $exists = DB::table('mcustomer')->where('CUSTOMER', $customer['CUSTOMER'])->exists();
            if (!$exists) {
                DB::table('mcustomer')->insert(array_merge($customer, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        echo "✅ Customer data berhasil diisi!\n";
    }
}
