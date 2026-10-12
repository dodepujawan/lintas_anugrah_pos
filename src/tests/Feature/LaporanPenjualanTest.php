<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckPermission;
use App\Models\Arh;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LaporanPenjualanTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_penjualan_filters_by_date_customer_and_jenis(): void
    {
        $this->withoutMiddleware(CheckPermission::class);

        $user = User::factory()->create();

        DB::table('mcustomer')->insert([
            'CUSTOMER' => 'CUST001',
            'NAMACUST' => 'Customer Satu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Arh::create([
            'NOFAKTUR' => 'INV-EKS-001',
            'TGLFAKTUR' => '2026-08-01',
            'TGLJT' => '2026-08-31',
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 1000000,
            'BAYAR' => 200000,
            'SALDO' => 800000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SYNC_EKS',
            'KETERANGAN' => 'Pendapatan Ekspedisi CUST001 - INV-EKS-001',
            'USER' => $user->name,
        ]);

        Arh::create([
            'NOFAKTUR' => 'INV-REN-001',
            'TGLFAKTUR' => '2026-08-02',
            'TGLJT' => '2026-08-31',
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 500000,
            'BAYAR' => 0,
            'SALDO' => 500000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SYNC_REN',
            'KETERANGAN' => 'Pendapatan Sewa Unit Armada CUST001 - INV-REN-001',
            'USER' => $user->name,
        ]);

        Arh::create([
            'NOFAKTUR' => 'INV-OPENING-001',
            'TGLFAKTUR' => '2026-08-01',
            'TGLJT' => '2026-08-31',
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 750000,
            'BAYAR' => 0,
            'SALDO' => 750000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SALDO_AWAL_MANUAL',
            'KETERANGAN' => 'Saldo awal',
            'USER' => $user->name,
        ]);

        $response = $this->actingAs($user)->get(route('laporan.penjualan', [
            'date_from' => '2026-08-01',
            'date_to' => '2026-08-31',
            'customer' => 'CUST001',
            'jenis' => 'EKS',
        ]));

        $response->assertOk()
            ->assertSee('INV-EKS-001')
            ->assertDontSee('INV-REN-001')
            ->assertDontSee('INV-OPENING-001')
            ->assertSee('1.000.000');

        $this->actingAs($user)
            ->get(route('laporan.penjualan.export', [
                'date_from' => '2026-08-01',
                'date_to' => '2026-08-31',
            ]))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('laporan.penjualan.pdf', [
                'date_from' => '2026-08-01',
                'date_to' => '2026-08-31',
            ]))
            ->assertOk();
    }
}
