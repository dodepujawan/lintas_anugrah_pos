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
            ['route_name' => 'laporan.tutup_buku'],
            [
                'name' => 'Tutup Buku',
                'section' => 'Laporan',
                'icon' => 'bi bi-box-seam',
                'sort_order' => 6,
                'is_sidebar' => true,
                'description' => 'Proses tutup buku akhir tahun.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_menu')) {
            return;
        }

        $menuId = DB::table('menus')->where('route_name', 'laporan.tutup_buku')->value('id');
        $adminRoleId = DB::table('roles')->where('name', 'Administrator')->value('id');

        if (! $menuId || ! $adminRoleId) {
            return;
        }

        DB::table('role_menu')->updateOrInsert(
            ['role_id' => $adminRoleId, 'menu_id' => $menuId],
            [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_post' => true,
                'can_void' => true,
                'can_export' => true,
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

        $menuId = DB::table('menus')->where('route_name', 'laporan.tutup_buku')->value('id');

        if ($menuId && Schema::hasTable('role_menu')) {
            DB::table('role_menu')->where('menu_id', $menuId)->delete();
        }

        DB::table('menus')->where('route_name', 'laporan.tutup_buku')->delete();
    }
};
