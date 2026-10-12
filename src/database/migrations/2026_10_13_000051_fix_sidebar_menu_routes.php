<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus menu "Edit Invoice Rent" yang punya route_name berparameter
        // Route 'rent-pendingin-invoice-gen.edit' butuh parameter {noInvoice}
        // yang tidak tersedia di sidebar context → menyebabkan UrlGenerationException
        DB::table('menus')
            ->where('route_name', 'rent-pendingin-invoice-gen.edit')
            ->where('name', 'Edit Invoice Rent')
            ->delete();

        // Hapus menu "Edit Invoice Coolroom" yang punya route_name berparameter
        DB::table('menus')
            ->where('route_name', 'coolroom-invoice-gen.edit')
            ->where('name', 'Edit Invoice Coolroom')
            ->delete();
    }

    public function down(): void
    {
        DB::table('menus')->insertOrIgnore([
            'name' => 'Edit Invoice Rent',
            'route_name' => 'rent-pendingin-invoice-gen.edit',
            'section' => 'Transaksi',
            'icon' => 'bi bi-pencil-square',
            'sort_order' => 112,
            'is_sidebar' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menus')->insertOrIgnore([
            'name' => 'Edit Invoice Coolroom',
            'route_name' => 'coolroom-invoice-gen.edit',
            'section' => 'Transaksi',
            'icon' => 'bi bi-pencil-square',
            'sort_order' => 122,
            'is_sidebar' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
