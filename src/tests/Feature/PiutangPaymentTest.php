<?php

namespace Tests\Feature;

use App\Models\AccountMapping;
use App\Models\Arh;
use App\Models\Coa;
use App\Models\User;
use Database\Seeders\CoaSeeder;
use Database\Seeders\McustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiutangPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoaSeeder::class);

        // Ensure COA mappings exist for journaling
        AccountMapping::updateOrCreate(
            ['mapping_key' => 'accounts_receivable'],
            ['account_id' => Coa::firstOrCreate(
                ['kode_akun' => '11300000'],
                [
                    'nama_akun' => 'Piutang Usaha',
                    'tipe' => 'Aset',
                    'kategori' => 'Piutang',
                    'level' => 'Akun Posting',
                    'saldo_normal' => 'Debit',
                    'is_active' => true,
                ]
            )->id]
        );
        AccountMapping::updateOrCreate(
            ['mapping_key' => 'default_sales_revenue'],
            ['account_id' => Coa::firstOrCreate(
                ['kode_akun' => '41000000'],
                [
                    'nama_akun' => 'Pendapatan Penjualan',
                    'tipe' => 'Pendapatan',
                    'kategori' => 'Pendapatan',
                    'level' => 'Akun Posting',
                    'saldo_normal' => 'Kredit',
                    'is_active' => true,
                ]
            )->id]
        );
    }

    public function test_can_store_saldo_awal_piutang(): void
    {
        $this->withoutMiddleware();
        $this->seed(CoaSeeder::class);
        // COA seeding and mapping setup moved to setUp()
        // $this->seed(CoaSeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/piutang', [
                'nofaktur' => 'INV-001',
                'tglfaktur' => now()->toDateString(),
                'tgljt' => now()->addDays(30)->toDateString(),
                'pelanggan' => 'PT Contoh',
                'nominal' => 1000000,
                'deskripsi' => 'Saldo awal piutang',
            ])
            ->assertRedirect(route('piutang.index'))
            ->assertSessionHas('success', 'Saldo awal piutang berhasil ditambahkan tanpa jurnal baru.');

        $arh = Arh::query()->firstOrFail();

        $this->assertEquals(1000000, (float) $arh->PIUTANG);
        $this->assertEquals(1000000, (float) $arh->SALDO);
        $this->assertEquals(0, (float) $arh->BAYAR);
        $this->assertSame('PT Contoh', $arh->CUSTOMER);
        $this->assertSame('INV-001', $arh->NOFAKTUR);
        $this->assertSame('SALDO_AWAL_MANUAL', $arh->SOURCE);
        $this->assertNull($arh->NOJURNAL);
    }

    public function test_can_view_receivable_detail(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();

        $arh = Arh::create([
            'NOFAKTUR' => 'INV-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'CUSTOMER' => 'PT Contoh',
            'PIUTANG' => 1000000,
            'BAYAR' => 0,
            'SALDO' => 1000000,
            'KETERANGAN' => 'Saldo awal piutang',
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        $this->actingAs($user)
            ->get(route('piutang.show', $arh))
            ->assertOk()
            ->assertViewHas('piutang', $arh)
            ->assertSee('PT Contoh')
            ->assertSee('INV-001')
            ->assertSee('Pelunasan Piutang');
    }

    public function test_can_search_customer_from_mcustomer(): void
    {
        $this->withoutMiddleware();
        $this->seed(McustomerSeeder::class);

        $this->getJson('/piutang/pelanggan/search?q=maju')
            ->assertOk()
            ->assertJsonFragment(['value' => 'CUST001', 'label' => 'CUST001 - PT Maju Jaya']);
    }
}
