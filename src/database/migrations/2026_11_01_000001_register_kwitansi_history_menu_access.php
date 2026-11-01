<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menu = Menu::query()->updateOrCreate(
            ['route_name' => 'kwitansi-history.index'],
            [
                'name' => 'Kwitansi History',
                'section' => 'Laporan',
                'icon' => 'bi bi-clock-history',
                'sort_order' => 200,
                'is_sidebar' => true,
                'description' => 'Riwayat kwitansi EKS, Rent, dan Coolroom.',
            ]
        );

        $sourceMenuId = Menu::query()
            ->where('route_name', 'expedisi-kwitansi.index')
            ->value('id');

        foreach (DB::table('roles')->get(['id', 'name']) as $role) {
            $targetPermissionExists = DB::table('role_menu')
                ->where('role_id', $role->id)
                ->where('menu_id', $menu->id)
                ->exists();

            if ($targetPermissionExists) {
                continue;
            }

            $sourcePermission = $sourceMenuId
                ? DB::table('role_menu')
                    ->where('role_id', $role->id)
                    ->where('menu_id', $sourceMenuId)
                    ->first()
                : null;

            if (! $sourcePermission && $role->name !== 'Administrator') {
                continue;
            }

            DB::table('role_menu')->insert([
                'role_id' => $role->id,
                'menu_id' => $menu->id,
                'can_view' => $sourcePermission?->can_view ?? true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
                'can_post' => false,
                'can_void' => false,
                'can_export' => $sourcePermission?->can_export ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // The menu itself is registered by the earlier transaction-menu migration.
    }
};
