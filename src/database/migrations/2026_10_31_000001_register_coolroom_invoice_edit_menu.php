<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menu = Menu::query()->updateOrCreate(['route_name' => 'coolroom-invoice-gen.index'], [
            'name' => 'Edit Invoice Coolroom',
            'section' => 'Transaksi',
            'icon' => 'bi bi-pencil-square',
            'sort_order' => 122,
            'is_sidebar' => true,
            'description' => 'Cari dan koreksi invoice Coolroom yang belum memiliki pembayaran.',
        ]);

        $sourceId = Menu::query()->where('route_name', 'coolroom-invoice.index')->value('id');
        if (! $sourceId) {
            return;
        }

        foreach (DB::table('role_menu')->where('menu_id', $sourceId)->get() as $permission) {
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $permission->role_id, 'menu_id' => $menu->id],
                [
                    'can_view' => $permission->can_view,
                    'can_create' => false,
                    'can_edit' => $permission->can_edit,
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
        $menu = Menu::query()->where('route_name', 'coolroom-invoice-gen.index')->first();
        if ($menu) {
            DB::table('role_menu')->where('menu_id', $menu->id)->delete();
            $menu->delete();
        }
    }
};
