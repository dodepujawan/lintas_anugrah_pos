<?php

namespace Tests\Feature;

use App\Models\Arh;
use App\Models\Barh;
use App\Models\Bard;
use App\Models\User;
use Database\Seeders\CoaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelunasanPiutangViewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test show pelunasan details page
     */
    public function test_show_pelunasan_details(): void
    {
        $this->withoutMiddleware();
        $this->seed(CoaSeeder::class);
        $user = User::factory()->create();

        // Create test invoice and payment
        $arh = Arh::create([
            'NOFAKTUR' => 'INV-SHOW-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 5000000,
            'BAYAR' => 2500000,
            'SALDO' => 2500000,
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        $barh = Barh::create([
            'NOBAYAR' => 'AR260601001',
            'TGLBAYAR' => now(),
            'CUSTOMER' => 'CUST001',
            'JUMLAH_BAYAR' => 2500000,
            'METODE_PEMBAYARAN' => 'CASH',
            'USER' => $user->name,
        ]);

        Bard::create([
            'NOBAYAR' => $barh->NOBAYAR,
            'NOFAKTUR' => $arh->NOFAKTUR,
            'TGLFAKTUR' => $arh->TGLFAKTUR,
            'SALDO_FAKTUR' => 5000000,
            'NILAI_BAYAR' => 2500000,
            'SISA_FAKTUR' => 2500000,
        ]);

        $response = $this->actingAs($user)
            ->get(route('pelunasan-piutang.show', $barh->id));

        $response->assertOk()
            ->assertSee('AR260601001')
            ->assertSee('CUST001')
            ->assertSee('Detail Pembayaran')
            ->assertSee('INV-SHOW-001');
    }

    /**
     * Test edit pelunasan form
     */
    public function test_edit_pelunasan_form(): void
    {
        $this->withoutMiddleware();
        $this->seed(CoaSeeder::class);
        $user = User::factory()->create();

        // Create invoice first (for foreign key)
        $arh = Arh::create([
            'NOFAKTUR' => 'INV-EDIT-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 2500000,
            'BAYAR' => 2500000,
            'SALDO' => 0,
            'STATUS' => 'LUNAS',
            'USER' => $user->name,
        ]);

        $barh = Barh::create([
            'NOBAYAR' => 'AR260601002',
            'TGLBAYAR' => now(),
            'CUSTOMER' => 'CUST001',
            'JUMLAH_BAYAR' => 2500000,
            'METODE_PEMBAYARAN' => 'TRANSFER',
            'USER' => $user->name,
        ]);

        // Add a BARD detail so we have data
        Bard::create([
            'NOBAYAR' => $barh->NOBAYAR,
            'NOFAKTUR' => $arh->NOFAKTUR,
            'TGLFAKTUR' => $arh->TGLFAKTUR,
            'SALDO_FAKTUR' => 2500000,
            'NILAI_BAYAR' => 2500000,
            'SISA_FAKTUR' => 0,
        ]);

        $response = $this->actingAs($user)
            ->get(route('pelunasan-piutang.edit', $barh->id));

        $response->assertOk()
            ->assertSee('Edit Pelunasan Piutang')
            ->assertSee('AR260601002')
            ->assertSee('Update Pelunasan');
    }

    /**
     * Test index list shows payment records
     */
    public function test_index_shows_payment_list(): void
    {
        $this->withoutMiddleware();
        $this->seed(CoaSeeder::class);
        $user = User::factory()->create();

        Barh::create([
            'NOBAYAR' => 'AR260601003',
            'TGLBAYAR' => now(),
            'CUSTOMER' => 'CUST001',
            'JUMLAH_BAYAR' => 1000000,
            'METODE_PEMBAYARAN' => 'CASH',
            'USER' => $user->name,
        ]);

        $response = $this->actingAs($user)
            ->get(route('pelunasan-piutang.index'));

        $response->assertOk()
            ->assertSee('Daftar Pelunasan Piutang')
            ->assertSee('AR260601003')
            ->assertSee('Detail')
            ->assertSee('Edit');
    }
}
