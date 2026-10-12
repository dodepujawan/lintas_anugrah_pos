<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void{
        // 🔥 RESET CACHE WAJIB
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ================= PERMISSIONS =================
        $permissions = [
            'customer.view',
            'kendaraan.view',
            'driver.view',

            'price.expedisi',
            'price.customer',
            'price.rent',
            'price.customer_rent',

            'penjualan.expedisi',
            'penjualan.invoice',
            'penjualan.invoice_generate',
            'penjualan.kwitansi',
            'penjualan.edit_expedisi',
            'penjualan.rent_dingin',
            'penjualan.invoice_rent_dingin',
            'penjualan.kwitansi_rent_dingin',
            'penjualan.edit_rent_dingin',
            'penjualan.coolroom',
            'penjualan.coolroom_invoice',
            'penjualan.coolroom_kwitansi',
            'penjualan.edit_coolroom_invoice',

            'kwitansi.history',

            'supplier.view',
            'service.view',

            'user.view',
            'user.create',

            'akuntansi.view',

            'extra.pajak',
            'extra.rekening',
            'extra.signature',
            'extra.printer',
            // 🔥 TAMBAHAN BARU
            'extra.permissions',
            'extra.area',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web'
            ]);
        }

        // ================= ROLES =================
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        // ================= ASSIGN PERMISSION KE ROLE =================
        // ### Fungsinya agar role bisa akses permission tertentu
        // $admin->syncPermissions($permissions);

        // $staff->syncPermissions([
        //     'customer.view',
        //     'kendaraan.view',
        //     'driver.view',
        //     'penjualan.expedisi',
        // ]);

        // ================= SUPER ADMIN (USER ID 1) =================
        $user = User::find(1);

        if ($user) {
            $user->assignRole('admin');
        }
    }
}
