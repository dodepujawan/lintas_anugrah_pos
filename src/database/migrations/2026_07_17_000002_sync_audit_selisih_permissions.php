<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('roles') || ! Schema::hasTable('role_menu')) {
            return;
        }

        $auditMenuId = DB::table('menus')->where('route_name', 'audit-selisih')->value('id');
        if (! $auditMenuId) {
            return;
        }

        $roleIds = DB::table('role_menu')
            ->join('menus', 'menus.id', '=', 'role_menu.menu_id')
            ->where('menus.route_name', 'validation-dashboard')
            ->where('role_menu.can_view', true)
            ->pluck('role_menu.role_id')
            ->push(DB::table('roles')->where('name', 'Administrator')->value('id'))
            ->filter()
            ->unique();

        foreach ($roleIds as $roleId) {
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $roleId, 'menu_id' => $auditMenuId],
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
    }

    public function down(): void
    {
        // Hak Administrator dibuat oleh migrasi menu utama; hanya hapus hak role lain.
        if (! Schema::hasTable('menus') || ! Schema::hasTable('roles') || ! Schema::hasTable('role_menu')) {
            return;
        }

        $auditMenuId = DB::table('menus')->where('route_name', 'audit-selisih')->value('id');
        $adminRoleId = DB::table('roles')->where('name', 'Administrator')->value('id');

        if ($auditMenuId) {
            DB::table('role_menu')
                ->where('menu_id', $auditMenuId)
                ->when($adminRoleId, fn ($query) => $query->where('role_id', '<>', $adminRoleId))
                ->delete();
        }
    }
};
