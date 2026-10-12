<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Menu::query()->where('route_name', 'piutang')->update([
            'name' => 'Saldo Awal Piutang',
            'description' => 'Khusus sisa piutang sebelum aplikasi mulai dipakai, tanpa jurnal.',
        ]);

        Menu::query()->where('route_name', 'piutang-berjalan')->update([
            'name' => 'Daftar Piutang Berjalan',
            'description' => 'Invoice berjalan dari input manual, import penjualan, dan sinkronisasi.',
        ]);

        Menu::query()->where('route_name', 'laporan.piutang')->update([
            'name' => 'Laporan Piutang Keseluruhan',
            'description' => 'Gabungan saldo awal, piutang berjalan, import, dan sinkronisasi.',
        ]);
    }

    public function down(): void
    {
        Menu::query()->where('route_name', 'piutang')->update([
            'name' => 'Saldo Awal Piutang',
            'description' => 'Input rincian saldo awal piutang tanpa jurnal.',
        ]);

        Menu::query()->where('route_name', 'piutang-berjalan')->update([
            'name' => 'Input Piutang Berjalan',
            'description' => 'Input invoice piutang periode berjalan dengan jurnal otomatis.',
        ]);

        Menu::query()->where('route_name', 'laporan.piutang')->update([
            'name' => 'Laporan Piutang',
            'description' => null,
        ]);
    }
};
