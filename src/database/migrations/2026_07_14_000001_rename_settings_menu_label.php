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

        DB::table('menus')
            ->where('route_name', 'settings')
            ->update([
                'name' => 'Role, Menu & Hak Akses',
                'icon' => 'bi bi-person-gear',
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        DB::table('menus')
            ->where('route_name', 'settings')
            ->update([
                'name' => 'Settings',
                'icon' => 'bi bi-gear',
            ]);
    }
};
