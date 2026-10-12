<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menuId = DB::table('menus')->where('route_name', 'penjualan-hosting')->value('id');
        if (! $menuId) return;
        DB::table('role_menu')->where('menu_id', $menuId)->update(['can_void' => true, 'updated_at' => now()]);
    }

    public function down(): void
    {
        $menuId = DB::table('menus')->where('route_name', 'penjualan-hosting')->value('id');
        if ($menuId) DB::table('role_menu')->where('menu_id', $menuId)->update(['can_void' => false, 'updated_at' => now()]);
    }
};
