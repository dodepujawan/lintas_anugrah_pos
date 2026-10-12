<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menu = Menu::query()->updateOrCreate(['route_name' => 'piutang-berjalan'], [
            'name' => 'Input Piutang Berjalan', 'section' => 'Transaksi',
            'icon' => 'bi bi-receipt', 'sort_order' => 5, 'is_sidebar' => true,
            'description' => 'Input invoice piutang periode berjalan dengan jurnal otomatis.',
        ]);
        Menu::query()->where('route_name', 'piutang')->update([
            'name' => 'Saldo Awal Piutang',
            'sort_order' => 4,
        ]);
        Menu::query()->where('route_name', 'pelunasan-piutang')->update(['sort_order' => 6]);
        $sourceId = Menu::query()->where('route_name', 'piutang')->value('id');
        if (! $sourceId) return;
        foreach (DB::table('role_menu')->where('menu_id', $sourceId)->get() as $permission) {
            DB::table('role_menu')->updateOrInsert(['role_id' => $permission->role_id, 'menu_id' => $menu->id], [
                'can_view' => $permission->can_view, 'can_create' => $permission->can_create,
                'can_edit' => false, 'can_delete' => false, 'can_post' => $permission->can_post,
                'can_void' => false, 'can_export' => false, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $menu = Menu::query()->where('route_name', 'piutang-berjalan')->first();
        if ($menu) { DB::table('role_menu')->where('menu_id', $menu->id)->delete(); $menu->delete(); }
    }
};
