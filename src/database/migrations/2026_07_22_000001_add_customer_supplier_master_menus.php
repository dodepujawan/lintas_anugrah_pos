<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $menus = [
            ['route_name' => 'customers', 'name' => 'Master Customer', 'section' => 'Master', 'icon' => 'bi bi-people', 'sort_order' => 2, 'description' => 'Input dan edit master customer dari mcustomer.'],
            ['route_name' => 'suppliers', 'name' => 'Master Supplier', 'section' => 'Master', 'icon' => 'bi bi-building', 'sort_order' => 3, 'description' => 'Input dan edit master supplier dari msupplier.'],
        ];

        $sourceId = Menu::where('route_name', 'coa')->value('id');

        foreach ($menus as $data) {
            $menu = Menu::updateOrCreate(['route_name' => $data['route_name']], $data + ['is_sidebar' => true]);

            if ($sourceId) {
                foreach (DB::table('role_menu')->where('menu_id', $sourceId)->get() as $permission) {
                    DB::table('role_menu')->updateOrInsert(
                        ['role_id' => $permission->role_id, 'menu_id' => $menu->id],
                        [
                            'can_view' => $permission->can_view,
                            'can_create' => $permission->can_create,
                            'can_edit' => $permission->can_edit,
                            'can_delete' => $permission->can_delete,
                            'can_post' => false,
                            'can_void' => false,
                            'can_export' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['customers', 'suppliers'] as $route) {
            $menu = Menu::where('route_name', $route)->first();
            if ($menu) {
                DB::table('role_menu')->where('menu_id', $menu->id)->delete();
                $menu->delete();
            }
        }
    }
};
