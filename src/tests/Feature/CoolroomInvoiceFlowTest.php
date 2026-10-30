<?php

namespace Tests\Feature;

use App\Models\Arh;
use App\Models\Coolroom;
use App\Models\Kwitansi;
use App\Models\Menu;
use App\Models\Mcustomer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoolroomInvoiceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_uses_legacy_rupiah_parsing_creates_arh_and_redirects_to_printable_detail(): void
    {
        $this->loginWithCoolroomInvoicePermissions();
        $this->createCustomer();
        $coolroom = $this->createCoolroom('SC102600001', 200000);

        $this->get(route('coolroom-invoice.create'))
            ->assertOk()
            ->assertSee('transaksiCoolroomInvoiceGenerate')
            ->assertSee('Export Excel');

        $this->postJson(route('coolroom-invoice.store'), [
            'nosj' => $coolroom->NOSJ,
            'bayar' => '250.000',
            'top' => 30,
            'tgl_jtp' => '2026-11-30',
        ])->assertUnprocessable()->assertJsonPath('status', false);

        $response = $this->postJson(route('coolroom-invoice.store'), [
            'nosj' => $coolroom->NOSJ,
            'invoice' => '',
            'bayar' => '25.000',
            'top' => 30,
            'tgl_jtp' => '2026-11-30',
        ]);

        $coolroom->refresh();
        $this->assertMatchesRegularExpression('/^FCO\d{10}$/', $coolroom->INVOICE);
        $this->assertSame(25000.0, (float) $coolroom->BAYAR);
        $this->assertSame(175000.0, (float) $coolroom->PIUTANG);
        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('invoice', $coolroom->INVOICE)
            ->assertJsonPath('redirect_url', route('coolroom-invoice.show', $coolroom->INVOICE));

        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => $coolroom->INVOICE,
            'SOURCE' => 'Coolroom',
            'PIUTANG' => 175000,
            'SALDO' => 30,
        ]);

        $this->postJson(route('coolroom-invoice.store'), [
            'invoice' => $coolroom->INVOICE,
            'nosj' => $coolroom->NOSJ,
            'bayar' => '35.000',
            'top' => 30,
            'tgl_jtp' => '2026-11-30',
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('redirect_url', route('coolroom-invoice.show', $coolroom->INVOICE));
        $coolroom->refresh();
        $this->assertSame(35000.0, (float) $coolroom->BAYAR);
        $this->assertSame(165000.0, (float) $coolroom->PIUTANG);

        $this->get(route('coolroom-invoice.index'))
            ->assertOk()
            ->assertSee($coolroom->INVOICE)
            ->assertSee('Rp 165.000');
        $this->get(route('coolroom-invoice.show', $coolroom->INVOICE))
            ->assertOk()
            ->assertSee('Cetak Langsung')
            ->assertSee('Cetak PDF')
            ->assertSee($coolroom->NOSJ);
        $textResponse = $this->getJson(route('coolroom-invoice.text', $coolroom->INVOICE))
            ->assertOk()
            ->assertJsonStructure(['text']);
        $this->assertStringContainsString('INVOICE', $textResponse->json('text'));
        $this->get(route('coolroom-invoice.pdf', $coolroom->INVOICE))->assertOk();
    }

    public function test_fully_paid_invoice_remains_in_list_and_duplicate_or_paid_processing_is_blocked(): void
    {
        $this->loginWithCoolroomInvoicePermissions();
        $this->createCustomer();
        $fullyPaid = $this->createCoolroom('SC102600002', 100000);

        $response = $this->postJson(route('coolroom-invoice.store'), [
            'nosj' => $fullyPaid->NOSJ,
            'bayar' => '100.000',
            'top' => 0,
            'tgl_jtp' => '2026-10-30',
        ]);
        $fullyPaid->refresh();

        $response->assertOk()->assertJsonPath('status', true);
        $this->assertDatabaseMissing('arh', ['NOFAKTUR' => $fullyPaid->INVOICE]);
        $this->get(route('coolroom-invoice.index'))->assertSee($fullyPaid->INVOICE);

        $this->postJson(route('coolroom-invoice.store'), [
            'nosj' => $fullyPaid->NOSJ,
            'bayar' => '0',
            'top' => 0,
        ])->assertUnprocessable()->assertJsonPath('status', false);

        $partiallyPaid = $this->createCoolroom('SC102600003', 100000, [
            'INVOICE' => 'FCO2026000009',
            'TGLINVOICE' => '2026-10-15',
            'BAYAR' => 20000,
            'PIUTANG' => 80000,
        ]);
        Arh::create([
            'NOFAKTUR' => $partiallyPaid->INVOICE,
            'TGLFAKTUR' => '2026-10-15',
            'CUSTOMER' => 'Coolroom Customer',
            'PIUTANG' => 80000,
            'BAYAR' => 5000,
            'SALDO' => 30,
            'SOURCE' => 'Coolroom',
        ]);
        $this->postJson(route('coolroom-invoice.store'), [
            'invoice' => $partiallyPaid->INVOICE,
            'nosj' => $partiallyPaid->NOSJ,
            'bayar' => 30000,
            'top' => 30,
            'tgl_jtp' => '2026-11-15',
        ])->assertUnprocessable()->assertJsonPath('status', false);
    }

    public function test_edit_recalculates_arh_and_kwitansi_then_redirects_to_detail(): void
    {
        $this->loginWithCoolroomInvoicePermissions();
        $this->createCustomer();
        $coolroom = $this->createCoolroom('SC102600004', 100000, [
            'INVOICE' => 'FCO2026000010',
            'TGLINVOICE' => '2026-10-15',
            'PIUTANG' => 100000,
        ]);
        Arh::create([
            'NOFAKTUR' => $coolroom->INVOICE,
            'TGLFAKTUR' => '2026-10-15',
            'CUSTOMER' => 'Coolroom Customer',
            'PIUTANG' => 100000,
            'BAYAR' => 0,
            'SALDO' => 30,
            'SOURCE' => 'Coolroom',
        ]);
        Kwitansi::create(['FDOK_TRANS' => $coolroom->INVOICE, 'FNIL_DOK' => 100000, 'TOTAL' => 100000]);

        $this->get(route('coolroom-invoice-gen.index'))
            ->assertOk()
            ->assertSee('Edit Invoice Coolroom')
            ->assertSee($coolroom->INVOICE);
        $this->get(route('coolroom-invoice-gen.edit', $coolroom->INVOICE))
            ->assertOk()
            ->assertSee('transaksiCoolroomInvoiceEditForm');

        $this->put(route('coolroom-invoice-gen.update', $coolroom->INVOICE), [
            'jumlah' => 2.5,
            'harga' => 20000,
            'disc' => 5,
            'ppn' => 11,
            'bayar' => 10000,
            'tgl_jt' => '2026-11-15',
            'keterangan' => 'Edit invoice Coolroom',
        ])->assertRedirect(route('coolroom-invoice.show', $coolroom->INVOICE));

        $this->assertDatabaseHas('coolrooms', [
            'id' => $coolroom->id,
            'SUBTOTAL' => 50000,
            'NDISC' => 2500,
            'DPP' => 47500,
            'NPPN' => 5225,
            'GRAND' => 52725,
            'BAYAR' => 10000,
            'PIUTANG' => 42725,
        ]);
        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => $coolroom->INVOICE,
            'SOURCE' => 'Coolroom',
            'PIUTANG' => 42725,
        ]);
        $this->assertDatabaseHas('kwitansi', [
            'FDOK_TRANS' => $coolroom->INVOICE,
            'FNIL_DOK' => 52725,
            'TOTAL' => 47500,
            'NDISC' => 2500,
        ]);

        Arh::where('NOFAKTUR', $coolroom->INVOICE)->update(['BAYAR' => 100]);
        $this->get(route('coolroom-invoice-gen.edit', $coolroom->INVOICE))
            ->assertRedirect(route('coolroom-invoice-gen.index'))
            ->assertSessionHas('error');
    }

    public function test_export_requires_legacy_status_and_date_filters(): void
    {
        $this->loginWithCoolroomInvoicePermissions();

        $this->post(route('coolroom-invoice.export'), [
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-31',
            'status_invoice' => 'sudah',
            'customer' => '',
        ])->assertOk();
    }

    private function loginWithCoolroomInvoicePermissions(): User
    {
        $role = Role::create(['name' => 'Administrator']);
        foreach (['coolroom-invoice.index', 'coolroom-invoice-gen.index'] as $routeName) {
            $menu = Menu::updateOrCreate(
                ['route_name' => $routeName],
                ['name' => 'Coolroom Invoice', 'section' => 'Transaksi', 'is_sidebar' => true]
            );
            $role->menus()->attach($menu->id, [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_export' => true,
            ]);
        }

        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function createCustomer(): Mcustomer
    {
        return Mcustomer::create([
            'CUSTOMER' => 'CUS-CR-001',
            'kode_cus' => 'CUS-CR-001',
            'NAMACUST' => 'Coolroom Customer',
            'ALAMAT1' => 'Jl. Test No. 1',
            'KONTAK' => 'Purchasing',
            'TOPKREDIT' => 30,
        ]);
    }

    private function createCoolroom(string $nosj, float $grand, array $overrides = []): Coolroom
    {
        $subtotal = $grand;

        return Coolroom::create(array_merge([
            'CUSTOMER_KODE' => 'CUS-CR-001',
            'CUSTOMER' => 'Coolroom Customer',
            'TGL' => '2026-10-15',
            'TGLSJ' => '2026-10-15',
            'TGLMUAT' => '2026-10-15',
            'NOSJ' => $nosj,
            'UNIT' => 'KG',
            'JUMLAH' => 2,
            'HARGA' => (int) ($subtotal / 2),
            'BOXING' => false,
            'SUBTOTAL' => $subtotal,
            'DISC' => 0,
            'NDISC' => 0,
            'DPP' => $subtotal,
            'PPN' => 0,
            'NPPN' => 0,
            'TOTAL' => $subtotal,
            'GRAND' => $grand,
            'BAYAR' => 0,
            'PIUTANG' => $grand,
        ], $overrides));
    }
}
