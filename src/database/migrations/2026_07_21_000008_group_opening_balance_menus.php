<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $menus = [
            'saldo-awal' => [
                'name' => 'Saldo Awal Akun',
                'section' => 'Saldo Awal',
                'sort_order' => 1,
                'description' => 'Input saldo awal akun pembukuan.',
            ],
            'piutang' => [
                'name' => 'Saldo Awal Piutang',
                'section' => 'Saldo Awal',
                'sort_order' => 2,
                'description' => 'Input rincian saldo awal piutang tanpa jurnal.',
            ],
            'saldo-awal-hutang' => [
                'name' => 'Saldo Awal Hutang',
                'section' => 'Saldo Awal',
                'sort_order' => 3,
                'description' => 'Input rincian saldo awal hutang tanpa jurnal.',
            ],
            'driver-opening-receivables' => [
                'name' => 'Saldo Awal Piutang Sopir',
                'section' => 'Saldo Awal',
                'sort_order' => 4,
                'description' => 'Input rincian saldo awal piutang sopir tanpa jurnal.',
            ],
        ];

        foreach ($menus as $routeName => $data) {
            Menu::where('route_name', $routeName)->update($data);
        }
    }

    public function down(): void
    {
        $menus = [
            'saldo-awal' => ['section' => 'Transaksi', 'sort_order' => 1],
            'piutang' => ['section' => 'Transaksi', 'sort_order' => 4],
            'saldo-awal-hutang' => ['name' => 'Input Saldo Awal Hutang', 'section' => 'Transaksi', 'sort_order' => 6],
            'driver-opening-receivables' => ['section' => 'Transaksi', 'sort_order' => 19],
        ];

        foreach ($menus as $routeName => $data) {
            Menu::where('route_name', $routeName)->update($data);
        }
    }
};
