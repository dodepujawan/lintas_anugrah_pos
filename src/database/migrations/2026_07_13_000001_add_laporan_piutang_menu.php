<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menu = Menu::query()->updateOrCreate(
            ['route_name' => 'laporan.piutang'],
            [
                'name' => 'Laporan Piutang',
                'section' => 'Laporan',
                'icon' => 'bi bi-file-earmark-bar-graph',
                'sort_order' => 8,
                'is_sidebar' => true,
                'description' => 'Saldo pelanggan, kartu piutang, aging, pembayaran, dan rekonsiliasi.',
            ]
        );

        $sourceMenuId = Menu::query()->where('route_name', 'piutang')->value('id');
        if (! $sourceMenuId) {
            return;
        }

        $permissions = DB::table('role_menu')->where('menu_id', $sourceMenuId)->get();
        foreach ($permissions as $permission) {
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $permission->role_id, 'menu_id' => $menu->id],
                [
                    'can_view' => $permission->can_view,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'can_post' => false,
                    'can_void' => false,
                    'can_export' => $permission->can_export,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        $menu = Menu::query()->where('route_name', 'laporan.piutang')->first();
        if ($menu) {
            DB::table('role_menu')->where('menu_id', $menu->id)->delete();
            $menu->delete();
        }
    }
};
