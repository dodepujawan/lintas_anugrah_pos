<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiPermissionSeeder extends Seeder
{
    /**
     * Assign permission untuk semua menu transaksi baru ke semua role.
     *
     * Jalankan di VPS:
     *   php artisan db:seed --class=TransaksiPermissionSeeder
     *
     * Atau sebagai bagian DatabaseSeeder:
     *   php artisan db:seed
     */
    public function run(): void
    {
        $menuIds = DB::table('menus')->pluck('id')->all();

        if (empty($menuIds)) {
            $this->command->info('Tidak ada menu di tabel menus. Jalankan migration terlebih dahulu.');
            return;
        }

        $roleIds = DB::table('roles')->pluck('id')->all();

        if (empty($roleIds)) {
            $this->command->info('Tidak ada role di tabel roles.');
            return;
        }

        $count = 0;

        foreach ($roleIds as $roleId) {
            foreach ($menuIds as $menuId) {
                $result = DB::table('role_menu')->insertOrIgnore([
                    'role_id'          => $roleId,
                    'menu_id'          => $menuId,
                    'can_view'         => true,
                    'can_create'       => true,
                    'can_edit'         => true,
                    'can_delete'       => true,
                    'can_post'         => true,
                    'can_void'         => false,
                    'can_export'       => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                if ($result) {
                    $count++;
                }
            }
        }

        $this->command->info("✓ Berhasil assign permission: {$count} role_menu records ditambahkan");
    }
}
