<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleMenuSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Administrator', 'description' => 'Access penuh ke seluruh modul.'],
            ['name' => 'User', 'description' => 'Akses dasar sesuai izin yang ditetapkan.'],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                ['description' => $roleData['description']]
            );
        }

        $menus = [
            ['name' => 'Dashboard', 'route_name' => 'dashboard', 'section' => 'Menu Utama', 'icon' => 'bi bi-grid-1x2', 'sort_order' => 1],
            ['name' => 'Register User', 'route_name' => 'register', 'section' => 'Setting', 'icon' => 'bi bi-person-plus', 'sort_order' => 2, 'is_sidebar' => false],
            ['name' => 'Saldo Awal Akun', 'route_name' => 'saldo-awal', 'section' => 'Saldo Awal', 'icon' => 'bi bi-wallet2', 'sort_order' => 1, 'description' => 'Input saldo awal akun pembukuan.'],
            ['name' => 'Jurnal Umum', 'route_name' => 'jurnal', 'section' => 'Transaksi', 'icon' => 'bi bi-receipt', 'sort_order' => 2],
            ['name' => 'Jurnal Penyesuaian', 'route_name' => 'jurnal-penyesuaian', 'section' => 'Transaksi', 'icon' => 'bi bi-journal-text', 'sort_order' => 3],
            ['name' => 'Saldo Awal Piutang', 'route_name' => 'piutang', 'section' => 'Saldo Awal', 'icon' => 'bi bi-cash-stack', 'sort_order' => 2, 'description' => 'Khusus sisa piutang sebelum aplikasi mulai dipakai, tanpa jurnal.'],
            ['name' => 'Daftar Piutang Berjalan', 'route_name' => 'piutang-berjalan', 'section' => 'Transaksi', 'icon' => 'bi bi-receipt', 'sort_order' => 5, 'description' => 'Invoice berjalan dari input manual, import penjualan, dan sinkronisasi.'],
            ['name' => 'Pelunasan Piutang', 'route_name' => 'pelunasan-piutang', 'section' => 'Transaksi', 'icon' => 'bi bi-credit-card', 'sort_order' => 6],
            ['name' => 'Laporan Piutang Keseluruhan', 'route_name' => 'laporan.piutang', 'section' => 'Laporan', 'icon' => 'bi bi-file-earmark-bar-graph', 'sort_order' => 8],
            ['name' => 'Laporan Penjualan', 'route_name' => 'laporan.penjualan', 'section' => 'Laporan', 'icon' => 'bi bi-cart-check', 'sort_order' => 10, 'description' => 'Laporan penjualan dari penambahan data piutang dengan filter tanggal, customer, dan jenis EKS/REN/CO.'],
            ['name' => 'Saldo Awal Hutang', 'route_name' => 'saldo-awal-hutang', 'section' => 'Saldo Awal', 'icon' => 'bi bi-wallet2', 'sort_order' => 3, 'description' => 'Input rincian saldo awal hutang tanpa jurnal.'],
            ['name' => 'Input Hutang Berjalan', 'route_name' => 'hutang', 'section' => 'Transaksi', 'icon' => 'bi bi-journal-minus', 'sort_order' => 7],
            ['name' => 'Pelunasan Hutang', 'route_name' => 'pelunasan-hutang', 'section' => 'Transaksi', 'icon' => 'bi bi-credit-card-2-front', 'sort_order' => 8],
            ['name' => 'Laporan Hutang', 'route_name' => 'laporan.hutang', 'section' => 'Laporan', 'icon' => 'bi bi-file-earmark-bar-graph', 'sort_order' => 9],
            ['name' => 'Import Data', 'route_name' => 'import-data', 'section' => 'Transaksi', 'icon' => 'bi bi-cloud-arrow-up', 'sort_order' => 8],
            ['name' => 'Master COA', 'route_name' => 'coa', 'section' => 'Master', 'icon' => 'bi bi-list-columns', 'sort_order' => 1],
            ['name' => 'Master Customer', 'route_name' => 'customers', 'section' => 'Master', 'icon' => 'bi bi-people', 'sort_order' => 2, 'description' => 'Input dan edit master customer dari mcustomer.'],
            ['name' => 'Master Supplier', 'route_name' => 'suppliers', 'section' => 'Master', 'icon' => 'bi bi-building', 'sort_order' => 3, 'description' => 'Input dan edit master supplier dari msupplier.'],
            ['name' => 'Laporan Jurnal', 'route_name' => 'laporan.jurnal_umum', 'section' => 'Laporan', 'icon' => 'bi bi-file-text', 'sort_order' => 1],
            ['name' => 'Laporan Buku Besar', 'route_name' => 'laporan.buku_besar', 'section' => 'Laporan', 'icon' => 'bi bi-book', 'sort_order' => 2],
            ['name' => 'Laporan Laba Rugi', 'route_name' => 'laporan.laba_rugi', 'section' => 'Laporan', 'icon' => 'bi bi-bar-chart-line', 'sort_order' => 3],
            ['name' => 'Laporan Biaya', 'route_name' => 'laporan.biaya', 'section' => 'Laporan', 'icon' => 'bi bi-receipt-cutoff', 'sort_order' => 4],
            ['name' => 'Laporan Neraca', 'route_name' => 'laporan.neraca', 'section' => 'Laporan', 'icon' => 'bi bi-balance-scale', 'sort_order' => 5],
            ['name' => 'Laporan Neraca Saldo', 'route_name' => 'laporan.neraca_saldo', 'section' => 'Laporan', 'icon' => 'bi bi-columns-gap', 'sort_order' => 6],
            ['name' => 'Laporan Perubahan Ekuitas', 'route_name' => 'laporan.perubahan_ekuitas', 'section' => 'Laporan', 'icon' => 'bi bi-diagram-3', 'sort_order' => 7],
            ['name' => 'Tutup Buku', 'route_name' => 'laporan.tutup_buku', 'section' => 'Laporan', 'icon' => 'bi bi-box-seam', 'sort_order' => 8],
            ['name' => 'Role, Menu & Hak Akses', 'route_name' => 'settings', 'section' => 'Pengaturan', 'icon' => 'bi bi-person-gear', 'sort_order' => 1],
            ['name' => 'Account Mapping', 'route_name' => 'account-mappings.index', 'section' => 'Pengaturan', 'icon' => 'bi bi-map', 'sort_order' => 2],
            ['name' => 'Audit Saldo Normal COA', 'route_name' => 'coa-saldo-normal', 'section' => 'Pengaturan', 'icon' => 'bi bi-shield-check', 'sort_order' => 3],
            ['name' => 'Backup Database', 'route_name' => 'backup', 'section' => 'Pengaturan', 'icon' => 'bi bi-hdd-stack', 'sort_order' => 4],
            ['name' => 'Lock Periode', 'route_name' => 'period-locks', 'section' => 'Pengaturan', 'icon' => 'bi bi-lock-fill', 'sort_order' => 5],
            ['name' => 'Audit Trail', 'route_name' => 'audit-logs', 'section' => 'Pengaturan', 'icon' => 'bi bi-clock-history', 'sort_order' => 6],
            ['name' => 'Validasi Data', 'route_name' => 'validation-dashboard', 'section' => 'Pengaturan', 'icon' => 'bi bi-clipboard2-check', 'sort_order' => 7],
            ['name' => 'Manajemen User', 'route_name' => 'users', 'section' => 'Pengaturan', 'icon' => 'bi bi-people', 'sort_order' => 8],
        ];

        foreach ($menus as $menuData) {
            Menu::updateOrCreate(
                ['route_name' => $menuData['route_name']],
                $menuData
            );
        }

        $admin = Role::where('name', 'Administrator')->first();
        $user = Role::where('name', 'User')->first();

        if ($admin) {
            $permissions = [];
            foreach (Menu::pluck('id') as $menuId) {
                $permissions[$menuId] = [
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'can_post' => true,
                    'can_void' => true,
                    'can_export' => true,
                ];
            }
            $admin->menus()->sync($permissions);
        }

        if ($user) {
            $permissions = [];
            foreach (Menu::pluck('id') as $menuId) {
                $permissions[$menuId] = [
                    'can_view' => false,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'can_post' => false,
                    'can_void' => false,
                    'can_export' => false,
                ];
            }
            $user->menus()->sync($permissions);
        }
    }
}
