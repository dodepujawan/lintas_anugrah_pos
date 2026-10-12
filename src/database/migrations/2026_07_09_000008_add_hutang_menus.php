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

        $menus = [
            [
                'route_name' => 'hutang',
                'name' => 'Hutang',
                'section' => 'Transaksi',
                'icon' => 'bi bi-journal-minus',
                'sort_order' => 6,
                'description' => 'Daftar faktur dan saldo hutang supplier.',
            ],
            [
                'route_name' => 'pelunasan-hutang',
                'name' => 'Pelunasan Hutang',
                'section' => 'Transaksi',
                'icon' => 'bi bi-credit-card-2-front',
                'sort_order' => 7,
                'description' => 'Pembayaran hutang supplier dan jurnal otomatis.',
            ],
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->updateOrInsert(
                ['route_name' => $menu['route_name']],
                $menu + [
                    'is_sidebar' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        DB::table('menus')->where('route_name', 'import-data')->update(['sort_order' => 8, 'updated_at' => now()]);

        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_menu')) {
            return;
        }

        $adminRoleId = DB::table('roles')->where('name', 'Administrator')->value('id');
        if (! $adminRoleId) {
            return;
        }

        foreach (['hutang', 'pelunasan-hutang'] as $routeName) {
            $menuId = DB::table('menus')->where('route_name', $routeName)->value('id');
            if (! $menuId) {
                continue;
            }

            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $adminRoleId, 'menu_id' => $menuId],
                [
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => false,
                    'can_post' => true,
                    'can_void' => false,
                    'can_export' => false,
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

        $menuIds = DB::table('menus')->whereIn('route_name', ['hutang', 'pelunasan-hutang'])->pluck('id');
        if ($menuIds->isNotEmpty() && Schema::hasTable('role_menu')) {
            DB::table('role_menu')->whereIn('menu_id', $menuIds)->delete();
        }

        DB::table('menus')->whereIn('route_name', ['hutang', 'pelunasan-hutang'])->delete();
        DB::table('menus')->where('route_name', 'import-data')->update(['sort_order' => 6, 'updated_at' => now()]);
    }
};
