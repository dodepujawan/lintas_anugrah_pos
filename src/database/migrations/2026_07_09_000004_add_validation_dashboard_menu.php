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
            ['route_name' => 'validation-dashboard'],
            [
                'name' => 'Validasi Data',
                'section' => 'Pengaturan',
                'icon' => 'bi bi-clipboard2-check',
                'sort_order' => 7,
                'is_sidebar' => true,
                'description' => 'Dashboard pengecekan kualitas data akuntansi.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_menu')) {
            return;
        }

        $menuId = DB::table('menus')->where('route_name', 'validation-dashboard')->value('id');
        $adminRoleId = DB::table('roles')->where('name', 'Administrator')->value('id');

        if (! $menuId || ! $adminRoleId) {
            return;
        }

        DB::table('role_menu')->updateOrInsert(
            ['role_id' => $adminRoleId, 'menu_id' => $menuId],
            [
                'can_view' => true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
                'can_post' => false,
                'can_void' => false,
                'can_export' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $menuId = DB::table('menus')->where('route_name', 'validation-dashboard')->value('id');

        if ($menuId && Schema::hasTable('role_menu')) {
            DB::table('role_menu')->where('menu_id', $menuId)->delete();
        }

        DB::table('menus')->where('route_name', 'validation-dashboard')->delete();
    }
};
