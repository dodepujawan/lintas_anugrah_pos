<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menu = Menu::query()->updateOrCreate(['route_name' => 'penjualan-hosting'], [
            'name' => 'Proses Penjualan Hosting', 'section' => 'Transaksi',
            'icon' => 'bi bi-cloud-check', 'sort_order' => 7, 'is_sidebar' => true,
            'description' => 'Periksa dan posting invoice hasil sinkronisasi ke jurnal dan AR.',
        ]);
        $sourceId = Menu::query()->where('route_name', 'piutang-berjalan')->value('id');
        if (! $sourceId) return;
        foreach (DB::table('role_menu')->where('menu_id', $sourceId)->get() as $permission) {
            DB::table('role_menu')->updateOrInsert(['role_id' => $permission->role_id, 'menu_id' => $menu->id], [
                'can_view' => $permission->can_view, 'can_create' => false, 'can_edit' => false,
                'can_delete' => false, 'can_post' => ($permission->can_post || $permission->can_create),
                'can_void' => false, 'can_export' => false, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $menu = Menu::query()->where('route_name', 'penjualan-hosting')->first();
        if ($menu) { DB::table('role_menu')->where('menu_id', $menu->id)->delete(); $menu->delete(); }
    }
};
