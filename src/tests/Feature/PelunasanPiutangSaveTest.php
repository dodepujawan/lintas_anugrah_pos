<?php

namespace Tests\Feature;

use App\Models\Arh;
use App\Models\AccountMapping;
use App\Models\Jurnal;
use App\Models\Barh;
use App\Models\Bard;
use App\Models\User;
use App\Services\PaymentFormTokenService;
use Database\Seeders\CoaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PelunasanPiutangSaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seed(CoaSeeder::class);

        // Ensure COA mappings exist for journaling
        // This would typically be handled by a seeder or migration
        AccountMapping::updateOrCreate(
            ['mapping_key' => 'accounts_receivable'],
            ['account_id' => \App\Models\Coa::firstOrCreate(
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
            ['mapping_key' => 'cash_account'],
            ['account_id' => \App\Models\Coa::firstOrCreate(
                ['kode_akun' => '11100000'],
                [
                    'nama_akun' => 'Kas',
                    'tipe' => 'Aset',
                    'kategori' => 'Kas',
                    'level' => 'Akun Posting',
                    'saldo_normal' => 'Debit',
                    'is_active' => true,
                ]
            )->id]
        );
        AccountMapping::updateOrCreate(
            ['mapping_key' => 'bank_account'],
            ['account_id' => \App\Models\Coa::firstOrCreate(
                ['kode_akun' => '11200000'],
                [
                    'nama_akun' => 'Bank',
                    'tipe' => 'Aset',
                    'kategori' => 'Bank',
                    'level' => 'Akun Posting',
                    'saldo_normal' => 'Debit',
                    'is_active' => true,
                ]
            )->id]
        );
    }

    public function test_store_pelunasan_saves_to_barh_and_bard(): void
    {
        // COA seeding and mapping setup moved to setUp()
        $user = User::factory()->create();

        // Create invoice first
        DB::table('mcustomer')->insert([
            'CUSTOMER' => 'CUST001',
            'NAMACUST' => 'Customer Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $arh = Arh::create([
            'NOFAKTUR' => 'INV-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 5000000,
            'BAYAR' => 0,
            'SALDO' => 5000000,
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        $details = [
            [
                'nofaktur' => 'INV-001',
                'bayar' => 2500000,
            ]
        ];

        $response = $this->actingAs($user)
            ->withSession(['receivable_payment_form_tokens' => ['receivable-token-1']])
            ->postJson(
            '/pelunasan-piutang',
            [
                'tglbayar' => now()->toDateString(),
                'customer' => 'CUST001',
                'jenis_bayar' => 'CASH',
                'kode_bank' => 'BCA',
                'no_referensi' => 'REF-001',
                'keterangan' => 'Pelunasan piutang',
                'total_bayar' => 2500000,
                'details' => $details,
                '_payment_token' => 'receivable-token-1',
            ]
        );

        $response->assertOk()
            ->assertJsonFragment(['success' => true, 'message' => 'Pelunasan berhasil disimpan dan dijurnal.']);

        // Verify BARH created
        $barh = Barh::latest()->first();
        $this->assertNotNull($barh);
        $this->assertEquals('CUST001', $barh->CUSTOMER);
        $this->assertEquals(2500000, (float) $barh->JUMLAH_BAYAR);
        $this->assertEquals('CASH', $barh->METODE_PEMBAYARAN);

        // Verify BARD created
        $bard = Bard::where('NOBAYAR', $barh->NOBAYAR)->first();
        $this->assertNotNull($bard);
        $this->assertEquals('INV-001', $bard->NOFAKTUR);
        $this->assertEquals(2500000, (float) $bard->NILAI_BAYAR);

        // Verify ARH updated
        $arh->refresh();
        $this->assertEquals(2500000, (float) $arh->BAYAR);
        $this->assertEquals(2500000, (float) $arh->SALDO);

        // Verify Jurnal created
        $jurnal = Jurnal::where('no_jurnal', $barh->NOJURNAL)->first();
        $this->assertNotNull($jurnal);
        $this->assertTrue($jurnal->is_posted);
        $this->assertEquals(2500000, (float) $jurnal->total_debit);
        $this->assertEquals(2500000, (float) $jurnal->total_kredit);
        // Verify journal is linked to BARH
        $this->assertNotNull($barh->NOJURNAL);
        $this->assertEquals($jurnal->no_jurnal, $barh->NOJURNAL);

        // Verify Jurnal Items
        $this->assertCount(2, $jurnal->items);
        $cashItem = $jurnal->items->where('coa_id', AccountMapping::getCoaId('cash_account'))->first();
        $arItem = $jurnal->items->where('coa_id', AccountMapping::getCoaId('accounts_receivable'))->first();

        $this->assertEquals(2500000, (float) $cashItem->debit);
        $this->assertEquals(2500000, (float) $arItem->kredit);
    }

    public function test_failed_store_can_be_retried_with_same_payment_token(): void
    {
        $user = User::factory()->create();

        DB::table('mcustomer')->insert([
            'CUSTOMER' => 'CUST001',
            'NAMACUST' => 'Customer Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $arh = Arh::create([
            'NOFAKTUR' => 'INV-RETRY-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 1000000,
            'BAYAR' => 0,
            'SALDO' => 1000000,
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        $payload = [
            'tglbayar' => now()->toDateString(),
            'customer' => 'CUST001',
            'jenis_bayar' => 'CASH',
            'no_referensi' => 'REF-RETRY',
            'keterangan' => 'Retry pelunasan piutang',
            'details' => [[
                'nofaktur' => 'INV-RETRY-001',
                'bayar' => 1500000,
            ]],
            '_payment_token' => 'receivable-token-retry',
        ];

        $this->actingAs($user)
            ->withSession(['receivable_payment_form_tokens' => ['receivable-token-retry']])
            ->postJson('/pelunasan-piutang', $payload)
            ->assertStatus(500)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Total bayar dan potongan faktur INV-RETRY-001 melebihi sisa piutang.',
            ]);

        $payload['details'][0]['bayar'] = 1000000;

        $this->actingAs($user)
            ->postJson('/pelunasan-piutang', $payload)
            ->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Pelunasan berhasil disimpan dan dijurnal.',
            ]);

        $arh->refresh();
        $this->assertEquals(0, (float) $arh->SALDO);
        $this->assertDatabaseHas('bard', [
            'NOFAKTUR' => 'INV-RETRY-001',
            'NILAI_BAYAR' => 1000000,
        ]);
    }

    public function test_duplicate_submit_returns_existing_pelunasan_instead_of_conflict(): void
    {
        $user = User::factory()->create();

        DB::table('mcustomer')->insert([
            'CUSTOMER' => 'CUST001',
            'NAMACUST' => 'Customer Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Arh::create([
            'NOFAKTUR' => 'INV-DUP-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 1000000,
            'BAYAR' => 0,
            'SALDO' => 1000000,
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        $token = app(PaymentFormTokenService::class)
            ->issue('receivable_payment_form_tokens', $user->id);

        $payload = [
            'tglbayar' => now()->toDateString(),
            'customer' => 'CUST001',
            'jenis_bayar' => 'CASH',
            'no_referensi' => 'REF-DUP',
            'keterangan' => 'Duplicate submit protection',
            'details' => [[
                'nofaktur' => 'INV-DUP-001',
                'bayar' => 1000000,
            ]],
            '_payment_token' => $token,
        ];

        $first = $this->actingAs($user)
            ->postJson('/pelunasan-piutang', $payload)
            ->assertOk()
            ->assertJsonFragment(['success' => true]);

        $second = $this->actingAs($user)
            ->postJson('/pelunasan-piutang', $payload)
            ->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'nobayar' => $first->json('nobayar'),
                'receipt_url' => $first->json('receipt_url'),
            ]);

        $this->assertSame(1, Barh::count());
        $this->assertSame(1, Bard::where('NOFAKTUR', 'INV-DUP-001')->count());
        $this->assertNotEmpty($second->json('nobayar'));
    }
}
