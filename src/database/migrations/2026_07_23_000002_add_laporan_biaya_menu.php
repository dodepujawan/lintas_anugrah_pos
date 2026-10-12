<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        DB::table('menus')->updateOrInsert(
            ['route_name' => 'laporan.biaya'],
            [
                'name' => 'Laporan Biaya',
                'section' => 'Laporan',
                'icon' => 'bi bi-receipt-cutoff',
                'sort_order' => 4,
                'is_sidebar' => true,
                'description' => 'Ringkasan akun beban dan detail transaksi biaya.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('menus')
            ->whereIn('route_name', ['laporan.neraca', 'laporan.neraca_saldo', 'laporan.perubahan_ekuitas', 'laporan.tutup_buku'])
            ->increment('sort_order');

        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_menu')) {
            return;
        }

        $menuId = DB::table('menus')->where('route_name', 'laporan.biaya')->value('id');
        $sourceMenuId = DB::table('menus')->where('route_name', 'laporan.laba_rugi')->value('id');

        if (! $menuId) {
            return;
        }

        if ($sourceMenuId) {
            foreach (DB::table('role_menu')->where('menu_id', $sourceMenuId)->get() as $permission) {
                DB::table('role_menu')->updateOrInsert(
                    ['role_id' => $permission->role_id, 'menu_id' => $menuId],
                    [
                        'can_view' => (bool) $permission->can_view,
                        'can_create' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                        'can_post' => false,
                        'can_void' => false,
                        'can_export' => (bool) $permission->can_export,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $adminRoleId = DB::table('roles')->where('name', 'Administrator')->value('id');
        if ($adminRoleId) {
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $adminRoleId, 'menu_id' => $menuId],
                [
                    'can_view' => true,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'can_post' => false,
                    'can_void' => false,
                    'can_export' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $menuId = DB::table('menus')->where('route_name', 'laporan.biaya')->value('id');
        if ($menuId && Schema::hasTable('role_menu')) {
            DB::table('role_menu')->where('menu_id', $menuId)->delete();
        }

        DB::table('menus')->where('route_name', 'laporan.biaya')->delete();

        DB::table('menus')
            ->whereIn('route_name', ['laporan.neraca', 'laporan.neraca_saldo', 'laporan.perubahan_ekuitas', 'laporan.tutup_buku'])
            ->decrement('sort_order');
    }
};
