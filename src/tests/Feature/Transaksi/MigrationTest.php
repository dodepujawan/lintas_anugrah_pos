<?php

namespace Tests\Feature\Transaksi;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function semua_tabel_transaksi_ada_di_database()
    {
        $expectedTables = [
            'users', 'mcustomer', 'msupplier', 'drivers',
            'arh', 'barh', 'bard', 'aph', 'baph', 'bapd', 'coas', 'jurnals',
            'expedisis', 'prices', 'pricecus', 'pricecushis',
            'pricedingins', 'pricedingincus', 'pricedinginhis',
            'rutes', 'coolrooms', 'kwitansi', 'services',
            'rekenings', 'signatures', 'areas', 'ol_pajaks',
            'menus', 'roles', 'role_menu',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Tabel [{$table}] tidak ditemukan di database."
            );
        }
    }

    /** @test */
    public function kolom_users_mengandung_reconciled_columns()
    {
        $this->assertTrue(Schema::hasColumn('users', 'is_admin'));
        $this->assertTrue(Schema::hasColumn('users', 'role_old'));
        $this->assertTrue(Schema::hasColumn('users', 'area_id'));
        $this->assertTrue(Schema::hasColumn('users', 'area_name'));
        $this->assertTrue(Schema::hasColumn('users', 'username'));
        $this->assertTrue(Schema::hasColumn('users', 'is_active'));
    }

    /** @test */
    public function kolom_arh_mengandung_reconciled_columns()
    {
        $this->assertTrue(Schema::hasColumn('arh', 'SALES'));
        $this->assertTrue(Schema::hasColumn('arh', 'AREA'));
        $this->assertTrue(Schema::hasColumn('arh', 'DIVISI'));
        $this->assertTrue(Schema::hasColumn('arh', 'area_id'));
        $this->assertTrue(Schema::hasColumn('arh', 'jurnal_id'));
    }

    /** @test */
    public function kolom_aph_mengandung_reconciled_columns()
    {
        $this->assertTrue(Schema::hasColumn('aph', 'UM'));
        $this->assertTrue(Schema::hasColumn('aph', 'AUTO'));
        $this->assertTrue(Schema::hasColumn('aph', 'area_id'));
        $this->assertTrue(Schema::hasColumn('aph', 'jurnal_id'));
    }

    /** @test */
    public function kolom_drivers_mengandung_uppercase_compatible()
    {
        $this->assertTrue(Schema::hasColumn('drivers', 'code'));
        $this->assertTrue(Schema::hasColumn('drivers', 'name'));
        $this->assertTrue(Schema::hasColumn('drivers', 'KODE'));
        $this->assertTrue(Schema::hasColumn('drivers', 'NAMA'));
    }

    /** @test */
    public function expedisis_table_ada_dengan_ledger_columns()
    {
        $this->assertTrue(Schema::hasColumn('expedisis', 'NOSJ'));
        $this->assertTrue(Schema::hasColumn('expedisis', 'JENIS'));
        $this->assertTrue(Schema::hasColumn('expedisis', 'NOJURNAL'));
        $this->assertTrue(Schema::hasColumn('expedisis', 'jurnal_id'));
    }

    /** @test */
    public function menu_transaksi_terdaftar()
    {
        $menus = \App\Models\Menu::whereIn('route_name', [
            'expedisi.index', 'kendaraan.index', 'price.index',
            'rent-pendingin.index', 'coolroom.index', 'service.index',
            'kwitansi-history.index',
        ])->get();

        $this->assertGreaterThanOrEqual(7, $menus->count());
    }
}
