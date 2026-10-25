<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceExpedisiAccessSeeder extends Seeder
{
    /**
     * Register the generate-invoice menu and grant it to Administrator.
     *
     * This seeder is safe to run repeatedly on a VPS.
     */
    public function run(): void
    {
        $menu = Menu::updateOrCreate(
            ['route_name' => 'expedisi-generate-invoice.index'],
            [
                'name' => 'Generate Invoice EKS',
                'section' => 'Transaksi',
                'icon' => 'bi bi-file-earmark-plus',
                'sort_order' => 101,
                'is_sidebar' => true,
                'description' => 'Generate invoice dari Surat Jalan Expedisi.',
            ]
        );

        $administrator = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Access penuh ke seluruh modul.']
        );

        DB::table('role_menu')->updateOrInsert(
            [
                'role_id' => $administrator->id,
                'menu_id' => $menu->id,
            ],
            [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_post' => true,
                'can_void' => true,
                'can_export' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command?->info('Menu Generate Invoice EKS dan permission Administrator siap.');
    }
}
