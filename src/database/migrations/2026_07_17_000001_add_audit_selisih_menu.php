<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) return;

        DB::table('menus')->updateOrInsert(['route_name' => 'audit-selisih'], [
            'name' => 'Audit Selisih', 'section' => 'Laporan', 'icon' => 'bi bi-search-heart',
            'sort_order' => 30, 'is_sidebar' => true,
            'description' => 'Mencari dan melacak penyebab selisih transaksi dengan buku besar.',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_menu')) return;
        $menuId = DB::table('menus')->where('route_name', 'audit-selisih')->value('id');
        $roleIds = DB::table('role_menu')
            ->join('menus', 'menus.id', '=', 'role_menu.menu_id')
            ->where('menus.route_name', 'validation-dashboard')
            ->where('role_menu.can_view', true)
            ->pluck('role_menu.role_id')
            ->push(DB::table('roles')->where('name', 'Administrator')->value('id'))
            ->filter()
            ->unique();

        foreach ($roleIds as $roleId) {
            DB::table('role_menu')->updateOrInsert(['role_id' => $roleId, 'menu_id' => $menuId], [
                'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false,
                'can_post' => false, 'can_void' => false, 'can_export' => false,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) return;
        $menuId = DB::table('menus')->where('route_name', 'audit-selisih')->value('id');
        if ($menuId && Schema::hasTable('role_menu')) DB::table('role_menu')->where('menu_id', $menuId)->delete();
        DB::table('menus')->where('route_name', 'audit-selisih')->delete();
    }
};
