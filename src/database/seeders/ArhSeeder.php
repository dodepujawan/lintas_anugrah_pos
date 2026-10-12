<?php

namespace Database\Seeders;

use App\Models\Arh;
use Illuminate\Database\Seeder;

class ArhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoices = [
            [
                'NOFAKTUR' => 'FP001',
                'TGLFAKTUR' => now()->toDateString(),
                'TGLJT' => now()->addDays(30)->toDateString(),
                'CUSTOMER' => 'CUST001',
                'PIUTANG' => 5000000,
                'SALDO' => 5000000,
                'KETERANGAN' => 'Saldo awal piutang PT Maju Jaya',
            ],
            [
                'NOFAKTUR' => 'FP002',
                'TGLFAKTUR' => now()->subDays(5)->toDateString(),
                'TGLJT' => now()->addDays(25)->toDateString(),
                'CUSTOMER' => 'CUST002',
                'PIUTANG' => 7500000,
                'SALDO' => 7500000,
                'KETERANGAN' => 'Saldo awal piutang PT Mundur Jaya',
            ],
            [
                'NOFAKTUR' => 'FP003',
                'TGLFAKTUR' => now()->subDays(10)->toDateString(),
                'TGLJT' => now()->addDays(20)->toDateString(),
                'CUSTOMER' => 'CUST003',
                'PIUTANG' => 3000000,
                'SALDO' => 3000000,
                'KETERANGAN' => 'Saldo awal piutang CV Senang',
            ],
        ];

        foreach ($invoices as $data) {
            Arh::updateOrCreate(
                ['NOFAKTUR' => $data['NOFAKTUR']],
                array_merge($data, [
                    'BAYAR' => 0,
                    'STATUS' => 'OPEN',
                    'USER' => 'SYSTEM',
                ])
            );
        }

        echo "✅ ARH sample data berhasil ditambahkan!\n";
    }
}
