<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menus = [
            ['name' => 'Dashboard', 'route_name' => 'dashboard', 'section' => 'Dashboard', 'icon' => 'bi bi-speedometer2', 'sort_order' => 1],
            ['name' => 'Customer', 'route_name' => 'customers.index', 'section' => 'Master', 'icon' => 'bi bi-people', 'sort_order' => 10],
            ['name' => 'Driver', 'route_name' => 'drivers.index', 'section' => 'Master', 'icon' => 'bi bi-truck', 'sort_order' => 20],
            ['name' => 'Kendaraan', 'route_name' => 'kendaraan.index', 'section' => 'Master', 'icon' => 'bi bi-truck-flatbed', 'sort_order' => 25],
            ['name' => 'Supplier', 'route_name' => 'suppliers.index', 'section' => 'Master', 'icon' => 'bi bi-building', 'sort_order' => 30],
            ['name' => 'Price EKS', 'route_name' => 'price.index', 'section' => 'Master', 'icon' => 'bi bi-tag', 'sort_order' => 40],
            ['name' => 'Price Customer', 'route_name' => 'price-customer.index', 'section' => 'Master', 'icon' => 'bi bi-tag', 'sort_order' => 41],
            ['name' => 'Price Rent', 'route_name' => 'price-rent.index', 'section' => 'Master', 'icon' => 'bi bi-tag', 'sort_order' => 42],
            ['name' => 'Price Rent Cus', 'route_name' => 'price-rent-customer.index', 'section' => 'Master', 'icon' => 'bi bi-tag', 'sort_order' => 43],
            ['name' => 'Service', 'route_name' => 'service.index', 'section' => 'Master', 'icon' => 'bi bi-wrench', 'sort_order' => 50],
            ['name' => 'Area', 'route_name' => 'area.get', 'section' => 'Master', 'icon' => 'bi bi-geo-alt', 'sort_order' => 60],
            ['name' => 'Ekspedisi', 'route_name' => 'expedisi.index', 'section' => 'Transaksi', 'icon' => 'bi bi-journal-text', 'sort_order' => 100],
            ['name' => 'Invoice Ekspedisi', 'route_name' => 'expedisi-invoice.index', 'section' => 'Transaksi', 'icon' => 'bi bi-receipt-cutoff', 'sort_order' => 101],
            ['name' => 'Edit Invoice EKS', 'route_name' => 'expedisi-invoice-gen.index', 'section' => 'Transaksi', 'icon' => 'bi bi-pencil-square', 'sort_order' => 102],
            ['name' => 'Kwitansi EKS', 'route_name' => 'expedisi-kwitansi.index', 'section' => 'Transaksi', 'icon' => 'bi bi-receipt', 'sort_order' => 103],
            ['name' => 'Rent Pendingin', 'route_name' => 'rent-pendingin.index', 'section' => 'Transaksi', 'icon' => 'bi bi-snow2', 'sort_order' => 110],
            ['name' => 'Invoice Rent', 'route_name' => 'rent-pendingin-invoice.index', 'section' => 'Transaksi', 'icon' => 'bi bi-receipt-cutoff', 'sort_order' => 111],
            ['name' => 'Kwitansi Rent', 'route_name' => 'rent-pendingin-kwitansi.index', 'section' => 'Transaksi', 'icon' => 'bi bi-receipt', 'sort_order' => 113],
            ['name' => 'Coolroom', 'route_name' => 'coolroom.index', 'section' => 'Transaksi', 'icon' => 'bi bi-snow2', 'sort_order' => 120],
            ['name' => 'Invoice Coolroom', 'route_name' => 'coolroom-invoice.index', 'section' => 'Transaksi', 'icon' => 'bi bi-receipt-cutoff', 'sort_order' => 121],
            ['name' => 'Kwitansi Coolroom', 'route_name' => 'coolroom-kwitansi.index', 'section' => 'Transaksi', 'icon' => 'bi bi-receipt', 'sort_order' => 123],
            ['name' => 'Kwitansi History', 'route_name' => 'kwitansi-history.index', 'section' => 'Laporan', 'icon' => 'bi bi-clock-history', 'sort_order' => 200],
            ['name' => 'Pajak', 'route_name' => 'pajak.get', 'section' => 'Extra', 'icon' => 'bi bi-percent', 'sort_order' => 300],
            ['name' => 'Rekening', 'route_name' => 'rekening.index', 'section' => 'Extra', 'icon' => 'bi bi-bank', 'sort_order' => 310],
            ['name' => 'Signature', 'route_name' => 'signature.get', 'section' => 'Extra', 'icon' => 'bi bi-signature', 'sort_order' => 320],
            ['name' => 'Akuntansi', 'route_name' => 'dashboard', 'section' => 'Akuntansi', 'icon' => 'bi bi-journal-richtext', 'sort_order' => 400],
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->insertOrIgnore([
                'name' => $menu['name'],
                'route_name' => $menu['route_name'],
                'section' => $menu['section'],
                'icon' => $menu['icon'],
                'sort_order' => $menu['sort_order'],
                'is_sidebar' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menus')->whereIn('route_name', [
            'dashboard', 'customers.index', 'drivers.index', 'kendaraan.index',
            'suppliers.index', 'price.index', 'price-customer.index',
            'price-rent.index', 'price-rent-customer.index', 'service.index',
            'area.get', 'expedisi.index', 'expedisi-invoice.index',
            'expedisi-invoice-gen.index', 'expedisi-kwitansi.index',
            'rent-pendingin.index',             'rent-pendingin-invoice.index', 'rent-pendingin-kwitansi.index',
            'coolroom.index', 'coolroom-invoice.index',

            'coolroom-kwitansi.index', 'kwitansi-history.index',
            'pajak.get', 'rekening.index', 'signature.get',
        ])->delete();
    }
};
