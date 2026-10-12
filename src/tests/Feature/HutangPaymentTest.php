<?php

namespace Tests\Feature;

use App\Models\Aph;
use App\Models\Baph;
use App\Models\Bapd;
use App\Models\User;
use App\Models\Jurnal;
use App\Models\AccountMapping;
use App\Models\Msupplier;
use App\Http\Middleware\CheckPermission;
use Database\Seeders\CoaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HutangPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoaSeeder::class);

        // Ensure COA mappings exist for journaling
        AccountMapping::updateOrCreate(
            ['mapping_key' => 'accounts_payable'],
            ['account_id' => \App\Models\Coa::updateOrCreate(
                ['kode_akun' => '21000000'],
                [
                    'nama_akun' => 'Hutang Usaha',
                    'tipe' => 'Kewajiban',
                    'kategori' => 'Hutang',
                    'level' => 'Akun Posting',
                    'saldo_normal' => 'Kredit',
                    'is_active' => true,
                ]
            )->id]
        );
        AccountMapping::updateOrCreate(
            ['mapping_key' => 'default_purchase_expense'],
            ['account_id' => \App\Models\Coa::updateOrCreate(
                ['kode_akun' => '51000000'],
                [
                    'nama_akun' => 'Beban Pembelian',
                    'tipe' => 'Beban',
                    'kategori' => 'Beban',
                    'level' => 'Akun Posting',
                    'saldo_normal' => 'Debit',
                    'is_active' => true,
                ]
            )->id]
        );
        AccountMapping::updateOrCreate(
            ['mapping_key' => 'cash_account'],
            ['account_id' => \App\Models\Coa::updateOrCreate(
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
            ['account_id' => \App\Models\Coa::updateOrCreate(
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

    private function createSupplier(string $code, string $name): void
    {
        Msupplier::updateOrCreate(
            ['SUPPLIER' => $code],
            ['NAMA' => $name]
        );
    }

    public function test_can_store_hutang_and_generates_journal(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $this->createSupplier('SP-JAYA', 'PT Supplier Jaya');

        $response = $this->actingAs($user)
            ->post('/hutang', [
                'nofaktur' => 'AP-001',
                'tglfaktur' => now()->toDateString(),
                'tgljt' => now()->addDays(30)->toDateString(),
                'supplier' => 'SP-JAYA',
                'coa_hutang_id' => AccountMapping::getCoaId('accounts_payable'),
                'coa_beban_id' => AccountMapping::getCoaId('default_purchase_expense'),
                'nilai_dpp' => 1500000,
                'nilai_pajak' => 0,
                'deskripsi' => 'Pembelian barang',
            ]);

        $response->assertRedirect(route('hutang.index'));
        $response->assertSessionHasNoErrors();

        $aph = Aph::query()->firstOrFail();

        $this->assertEquals(1500000, (float) $aph->HUTANG);
        $this->assertEquals(1500000, (float) $aph->SALDO);
        $this->assertEquals(0, (float) $aph->BAYAR);
        $this->assertSame('SP-JAYA', $aph->SUPPLIER);
        $this->assertSame('AP-001', $aph->NOFAKTUR);
        $this->assertSame('HUTANG_BERJALAN_MANUAL', $aph->SOURCE);
        $this->assertNotNull($aph->NOJURNAL);

        // Verify Jurnal created
        $jurnal = Jurnal::where('referensi', 'AP-001')->first();
        $this->assertNotNull($jurnal);
        $this->assertTrue($jurnal->is_posted);
        $this->assertEquals(1500000, (float) $jurnal->total_debit);
        $this->assertEquals(1500000, (float) $jurnal->total_kredit);

        // Verify Jurnal Items
        $this->assertCount(2, $jurnal->items);
        $expenseItem = $jurnal->items->where('coa_id', AccountMapping::getCoaId('default_purchase_expense'))->first();
        $apItem = $jurnal->items->where('coa_id', AccountMapping::getCoaId('accounts_payable'))->first();

        $this->assertEquals(1500000, (float) $expenseItem->debit);
        $this->assertEquals(1500000, (float) $apItem->kredit);
    }

    public function test_can_store_opening_payable_without_journal(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $this->createSupplier('SP-AWAL', 'PT Supplier Awal');

        $response = $this->actingAs($user)
            ->post(route('saldo-awal-hutang.store'), [
                'nofaktur' => 'SAH-002',
                'tglfaktur' => now()->subMonth()->toDateString(),
                'tgljt' => now()->addDays(15)->toDateString(),
                'supplier' => 'SP-AWAL',
                'nominal' => 1250000,
                'deskripsi' => 'Saldo hutang dari sistem lama',
            ]);

        $response->assertRedirect(route('saldo-awal-hutang.index'));
        $response->assertSessionHasNoErrors();

        $aph = Aph::where('NOFAKTUR', 'SAH-002')->firstOrFail();
        $this->assertSame('SALDO_AWAL_MANUAL', $aph->SOURCE);
        $this->assertNull($aph->NOJURNAL);
        $this->assertEquals(1250000, (float) $aph->HUTANG);
        $this->assertEquals(1250000, (float) $aph->SALDO);
        $this->assertDatabaseMissing('jurnals', ['referensi' => 'SAH-002']);
    }

    public function test_hutang_index_only_shows_running_payables(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $this->createSupplier('SP-FILTER', 'PT Supplier Filter');

        Aph::create([
            'NOFAKTUR' => 'SAH-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'SUPPLIER' => 'SP-FILTER',
            'HUTANG' => 100000,
            'BAYAR' => 0,
            'SALDO' => 100000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SALDO_AWAL_MANUAL',
            'USER' => $user->name,
        ]);

        Aph::create([
            'NOFAKTUR' => 'HB-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'SUPPLIER' => 'SP-FILTER',
            'HUTANG' => 200000,
            'BAYAR' => 0,
            'SALDO' => 200000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'HUTANG_BERJALAN_MANUAL',
            'USER' => $user->name,
        ]);

        $this->actingAs($user)
            ->get(route('hutang.index'))
            ->assertOk()
            ->assertSee('Jumlah Faktur')
            ->assertSee('Total Hutang')
            ->assertSee('Total Bayar')
            ->assertSee('Total Saldo')
            ->assertSee('200.000,00')
            ->assertSee('HB-001')
            ->assertDontSee('SAH-001');
    }

    public function test_can_search_running_payables(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $this->createSupplier('SP-CARI', 'PT Supplier Dicari');
        $this->createSupplier('SP-LAIN', 'PT Supplier Lain');

        Aph::create([
            'NOFAKTUR' => 'HB-CARI-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'SUPPLIER' => 'SP-CARI',
            'HUTANG' => 200000,
            'BAYAR' => 0,
            'SALDO' => 200000,
            'KETERANGAN' => 'Pembelian sparepart',
            'STATUS' => 'OPEN',
            'SOURCE' => 'HUTANG_BERJALAN_MANUAL',
            'USER' => $user->name,
        ]);

        Aph::create([
            'NOFAKTUR' => 'HB-LAIN-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'SUPPLIER' => 'SP-LAIN',
            'HUTANG' => 300000,
            'BAYAR' => 0,
            'SALDO' => 300000,
            'KETERANGAN' => 'Pembelian lain',
            'STATUS' => 'OPEN',
            'SOURCE' => 'HUTANG_BERJALAN_MANUAL',
            'USER' => $user->name,
        ]);

        Aph::create([
            'NOFAKTUR' => 'SAH-CARI-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'SUPPLIER' => 'SP-CARI',
            'HUTANG' => 400000,
            'BAYAR' => 0,
            'SALDO' => 400000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SALDO_AWAL_MANUAL',
            'USER' => $user->name,
        ]);

        $this->actingAs($user)
            ->get(route('hutang.index', ['search' => 'dicari']))
            ->assertOk()
            ->assertSee('HB-CARI-001')
            ->assertDontSee('HB-LAIN-001')
            ->assertDontSee('SAH-CARI-001');

        $this->actingAs($user)
            ->get(route('hutang.index', ['supplier' => 'SP-LAIN']))
            ->assertOk()
            ->assertSee('HB-LAIN-001')
            ->assertDontSee('HB-CARI-001')
            ->assertDontSee('SAH-CARI-001');
    }

    public function test_can_store_pelunasan_hutang_and_generates_journal(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $this->createSupplier('SP-MAJU', 'PT Supplier Maju');

        // Create an APH first
        $aph = Aph::create([
            'NOFAKTUR' => 'AP-002',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'SUPPLIER' => 'SP-MAJU',
            'HUTANG' => 2000000,
            'BAYAR' => 0,
            'SALDO' => 2000000,
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        $details = [
            [
                'nofaktur' => 'AP-002',
                'bayar' => 1000000,
            ]
        ];

        $response = $this->actingAs($user)
            ->withSession(['payable_payment_form_tokens' => ['payable-token-1']])
            ->postJson(
            '/pelunasan-hutang',
            [
                'tglbayar' => now()->toDateString(),
                'supplier' => 'SP-MAJU',
                'jenis_bayar' => 'TRANSFER',
                'no_referensi' => 'TRF-001',
                'keterangan' => 'Pembayaran hutang sebagian',
                'total_bayar' => 1000000,
                'details' => $details,
                '_payment_token' => 'payable-token-1',
            ]
        );

        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        // Verify BAPH created
        $baph = Baph::latest()->first();
        $this->assertNotNull($baph);
        $this->assertEquals('SP-MAJU', $baph->SUPPLIER);
        $this->assertEquals(1000000, (float) $baph->JUMLAH_BAYAR);
        $this->assertEquals('TRANSFER', $baph->METODE_PEMBAYARAN);

        // Verify BAPD created
        $bapd = Bapd::where('NOBAYAR', $baph->NOBAYAR)->first();
        $this->assertNotNull($bapd);
        $this->assertEquals('AP-002', $bapd->NOFAKTUR);
        $this->assertEquals(1000000, (float) $bapd->NILAI_BAYAR);

        // Verify APH updated
        $aph->refresh();
        $this->assertEquals(1000000, (float) $aph->BAYAR);
        $this->assertEquals(1000000, (float) $aph->SALDO);
        $this->assertSame('OPEN', $aph->STATUS);

        // Verify Jurnal created
        $jurnal = Jurnal::where('referensi', $baph->NOBAYAR)->first();
        $this->assertNotNull($jurnal);
        $this->assertTrue($jurnal->is_posted);
        $this->assertEquals(1000000, (float) $jurnal->total_debit);
        $this->assertEquals(1000000, (float) $jurnal->total_kredit);

        // Verify Jurnal Items
        $this->assertCount(2, $jurnal->items);
        $apItem = $jurnal->items->where('coa_id', AccountMapping::getCoaId('accounts_payable'))->first();
        $bankItem = $jurnal->items->where('coa_id', AccountMapping::getCoaId('bank_account'))->first();

        $this->assertEquals(1000000, (float) $apItem->debit);
        $this->assertEquals(1000000, (float) $bankItem->kredit);
    }

    public function test_can_store_full_pelunasan_hutang_with_selected_payment_account(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $this->createSupplier('SP-LUNAS', 'PT Supplier Lunas');

        $paymentAccount = \App\Models\Coa::firstOrCreate(
            ['kode_akun' => '11210000'],
            [
                'nama_akun' => 'Bank Operasional',
                'tipe' => 'Aset',
                'kategori' => 'Bank',
                'level' => 'Akun Posting',
                'saldo_normal' => 'Debit',
                'is_active' => true,
            ]
        );

        $aph = Aph::create([
            'NOFAKTUR' => 'AP-004',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'SUPPLIER' => 'SP-LUNAS',
            'HUTANG' => 750000,
            'BAYAR' => 0,
            'SALDO' => 750000,
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['payable_payment_form_tokens' => ['payable-token-2']])
            ->postJson('/pelunasan-hutang', [
            'tglbayar' => now()->toDateString(),
            'supplier' => 'SP-LUNAS',
            'jenis_bayar' => 'TRANSFER',
            'payment_coa_id' => $paymentAccount->id,
            'no_referensi' => 'TRF-002',
            'keterangan' => 'Pelunasan penuh',
            'details' => [
                [
                    'nofaktur' => 'AP-004',
                    'bayar' => 750000,
                ],
            ],
            '_payment_token' => 'payable-token-2',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        $baph = Baph::latest()->firstOrFail();
        $this->assertEquals($paymentAccount->id, $baph->PAYMENT_COA_ID);
        $this->assertEquals(750000, (float) $baph->JUMLAH_BAYAR);

        $aph->refresh();
        $this->assertEquals(750000, (float) $aph->BAYAR);
        $this->assertEquals(0, (float) $aph->SALDO);
        $this->assertSame('LUNAS', $aph->STATUS);
        $this->assertNotNull($aph->TGLLUNAS);

        $jurnal = Jurnal::where('referensi', $baph->NOBAYAR)->firstOrFail();
        $paymentItem = $jurnal->items->where('coa_id', $paymentAccount->id)->first();

        $this->assertNotNull($paymentItem);
        $this->assertEquals(750000, (float) $paymentItem->kredit);
    }

    public function test_can_update_unpaid_hutang_and_refresh_journal(): void
    {
        $this->withoutMiddleware(CheckPermission::class);
        $user = User::factory()->create();
        $this->createSupplier('SP-LAMA', 'PT Supplier Lama');
        $this->createSupplier('SP-BARU', 'PT Supplier Baru');

        $this->actingAs($user)->post('/hutang', [
            'nofaktur' => 'AP-005',
            'tglfaktur' => now()->toDateString(),
            'tgljt' => now()->addDays(30)->toDateString(),
            'supplier' => 'SP-LAMA',
            'coa_hutang_id' => AccountMapping::getCoaId('accounts_payable'),
            'coa_beban_id' => AccountMapping::getCoaId('default_purchase_expense'),
            'nilai_dpp' => 500000,
            'nilai_pajak' => 0,
            'deskripsi' => 'Pembelian awal',
        ]);

        $aph = Aph::where('NOFAKTUR', 'AP-005')->latest('id')->firstOrFail();
        $this->assertSame('HUTANG_BERJALAN_MANUAL', $aph->SOURCE);
        $this->assertNotNull($aph->NOJURNAL);

        $response = $this->actingAs($user)->put(route('hutang.update', $aph), [
            'nofaktur' => 'AP-005-REV',
            'tglfaktur' => now()->toDateString(),
            'tgljt' => now()->addDays(45)->toDateString(),
            'supplier' => 'SP-BARU',
            'coa_hutang_id' => AccountMapping::getCoaId('accounts_payable'),
            'coa_beban_id' => AccountMapping::getCoaId('default_purchase_expense'),
            'nilai_dpp' => 650000,
            'nilai_pajak' => 0,
            'deskripsi' => 'Pembelian revisi',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');
        $aph = Aph::where('NOFAKTUR', 'AP-005-REV')->latest('id')->firstOrFail();
        $response->assertRedirect(route('hutang.show', $aph));

        $this->assertSame('AP-005-REV', $aph->NOFAKTUR);
        $this->assertSame('SP-BARU', $aph->SUPPLIER);
        $this->assertEquals(650000, (float) $aph->HUTANG);
        $this->assertEquals(650000, (float) $aph->SALDO);
        $this->assertSame('HUTANG_BERJALAN_MANUAL', $aph->SOURCE);
        $this->assertNotNull($aph->NOJURNAL);

        $jurnal = Jurnal::where('referensi', 'AP-005-REV')->firstOrFail();
        $this->assertSame('AP-005-REV', $jurnal->referensi);
        $this->assertEquals(650000, (float) $jurnal->total_debit);
        $this->assertEquals(650000, (float) $jurnal->total_kredit);
    }

    public function test_can_delete_pelunasan_hutang_and_restore_invoice_balance(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $this->createSupplier('SP-RESTORE', 'PT Supplier Restore');

        $aph = Aph::create([
            'NOFAKTUR' => 'AP-006',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'SUPPLIER' => 'SP-RESTORE',
            'HUTANG' => 900000,
            'BAYAR' => 0,
            'SALDO' => 900000,
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        $storeResponse = $this->actingAs($user)
            ->withSession(['payable_payment_form_tokens' => ['payable-token-3']])
            ->postJson('/pelunasan-hutang', [
            'tglbayar' => now()->toDateString(),
            'supplier' => 'SP-RESTORE',
            'jenis_bayar' => 'CASH',
            'details' => [
                [
                    'nofaktur' => 'AP-006',
                    'bayar' => 400000,
                ],
            ],
            '_payment_token' => 'payable-token-3',
        ])->assertOk();

        $baph = Baph::where('NOBAYAR', $storeResponse->json('nobayar'))->firstOrFail();
        $noJurnal = $baph->NOJURNAL;
        $this->assertDatabaseHas('bapd', ['NOBAYAR' => $baph->NOBAYAR, 'NOFAKTUR' => 'AP-006']);

        $response = $this->actingAs($user)->delete(route('pelunasan-hutang.destroy', $baph));

        $response->assertRedirect(route('pelunasan-hutang.index'));

        $this->assertDatabaseMissing('baph', ['id' => $baph->id]);
        $this->assertDatabaseMissing('bapd', ['NOBAYAR' => $baph->NOBAYAR]);
        $this->assertDatabaseMissing('jurnals', ['no_jurnal' => $noJurnal]);
        $aph->refresh();
        $this->assertEquals(0, (float) $aph->BAYAR);
        $this->assertEquals(900000, (float) $aph->SALDO);
        $this->assertSame('OPEN', $aph->STATUS);
    }

    public function test_can_search_supplier_from_aph(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $this->createSupplier('SP-CEPAT', 'PT Supplier Cepat');

        Aph::create([
            'NOFAKTUR' => 'AP-003',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'SUPPLIER' => 'SP-CEPAT',
            'HUTANG' => 100000,
            'BAYAR' => 0,
            'SALDO' => 100000,
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        $this->actingAs($user)
            ->getJson('/hutang/supplier/search?q=cepat')
            ->assertOk()
            ->assertJsonFragment(['value' => 'SP-CEPAT', 'label' => 'SP-CEPAT - PT Supplier Cepat']);
    }
}
