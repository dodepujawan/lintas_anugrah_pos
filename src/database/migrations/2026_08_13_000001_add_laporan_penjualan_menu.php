<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent: aman dijalankan ulang / aman jika menu sudah di-insert manual via SQL hosting.
 * Setelah insert manual, catat juga baris di tabel migrations (lihat SQL di bawah file ini / chat).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $now = now();
        $existingId = DB::table('menus')->where('route_name', 'laporan.penjualan')->value('id');

        if ($existingId) {
            DB::table('menus')->where('id', $existingId)->update([
                'name' => 'Laporan Penjualan',
                'section' => 'Laporan',
                'icon' => 'bi bi-cart-check',
                'sort_order' => 10,
                'is_sidebar' => true,
                'description' => 'Laporan penjualan dari penambahan data piutang dengan filter tanggal, customer, dan jenis EKS/REN/CO.',
                'updated_at' => $now,
            ]);
            $menuId = (int) $existingId;
        } else {
            $menuId = (int) DB::table('menus')->insertGetId([
                'name' => 'Laporan Penjualan',
                'route_name' => 'laporan.penjualan',
                'section' => 'Laporan',
                'icon' => 'bi bi-cart-check',
                'sort_order' => 10,
                'is_sidebar' => true,
                'description' => 'Laporan penjualan dari penambahan data piutang dengan filter tanggal, customer, dan jenis EKS/REN/CO.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! $menuId || ! Schema::hasTable('roles') || ! Schema::hasTable('role_menu')) {
            return;
        }

        $this->syncRolePermissions($menuId, $now);
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $menuId = DB::table('menus')->where('route_name', 'laporan.penjualan')->value('id');
        if ($menuId && Schema::hasTable('role_menu')) {
            DB::table('role_menu')->where('menu_id', $menuId)->delete();
        }

        DB::table('menus')->where('route_name', 'laporan.penjualan')->delete();
    }

    private function syncRolePermissions(int $menuId, $now): void
    {
        $sourceMenuId = DB::table('menus')->where('route_name', 'laporan.piutang')->value('id')
            ?: DB::table('menus')->where('route_name', 'piutang-berjalan')->value('id');

        if ($sourceMenuId) {
            foreach (DB::table('role_menu')->where('menu_id', $sourceMenuId)->get() as $permission) {
                $exists = DB::table('role_menu')
                    ->where('role_id', $permission->role_id)
                    ->where('menu_id', $menuId)
                    ->exists();

                $payload = [
                    'can_view' => (bool) $permission->can_view,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'can_post' => false,
                    'can_void' => false,
                    'can_export' => (bool) $permission->can_export,
                    'updated_at' => $now,
                ];

                if ($exists) {
                    DB::table('role_menu')
                        ->where('role_id', $permission->role_id)
                        ->where('menu_id', $menuId)
                        ->update($payload);
                } else {
                    DB::table('role_menu')->insert($payload + [
                        'role_id' => $permission->role_id,
                        'menu_id' => $menuId,
                        'created_at' => $now,
                    ]);
                }
            }
        }

        $adminRoleId = DB::table('roles')->where('name', 'Administrator')->value('id');
        if (! $adminRoleId) {
            return;
        }

        $adminExists = DB::table('role_menu')
            ->where('role_id', $adminRoleId)
            ->where('menu_id', $menuId)
            ->exists();

        $adminPayload = [
            'can_view' => true,
            'can_create' => false,
            'can_edit' => false,
            'can_delete' => false,
            'can_post' => false,
            'can_void' => false,
            'can_export' => true,
            'updated_at' => $now,
        ];

        if ($adminExists) {
            DB::table('role_menu')
                ->where('role_id', $adminRoleId)
                ->where('menu_id', $menuId)
                ->update($adminPayload);
        } else {
            DB::table('role_menu')->insert($adminPayload + [
                'role_id' => $adminRoleId,
                'menu_id' => $menuId,
                'created_at' => $now,
            ]);
        }
    }
};
