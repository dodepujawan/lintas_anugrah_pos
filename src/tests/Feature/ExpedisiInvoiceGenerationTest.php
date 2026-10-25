<?php

namespace Tests\Feature;

use App\Models\Arh;
use App\Models\Expedisi;
use App\Models\Menu;
use App\Models\Mcustomer;
use App\Models\Role;
use App\Models\User;
use App\Models\YearClosing;
use App\Exports\ExpedisiInvoiceGenerateExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpedisiInvoiceGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_rows_include_group_information_and_show_one_invoiceable_group(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createGroup();

        $this->get(route('expedisi-generate-invoice.index'))
            ->assertOk()
            ->assertSee('Surat Jalan Siap Invoice')
            ->assertSee('transaksiExpedisiInvoiceGenerateSubmit');

        $this->getJson(route('expedisi-generate-invoice.data'))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.NOMUAT', 'MUAT-001')
            ->assertJsonPath('data.0.NOSJ', 'SJ-001')
            ->assertJsonPath('data.0.GB', 'GB-001')
            ->assertJsonPath('data.0.GROUP_SIZE', 2)
            ->assertJsonPath('data.0.GROUP_SJS.1', 'SJ-002')
            ->assertJsonPath('data.0.TOPKREDIT', 5)
            ->assertJsonPath('data.0.GRAND', 120000);

        $this->getJson(route('expedisi-generate-invoice.data', ['q' => 'SJ-002']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.NOSJ', 'SJ-001');

        $this->getJson(route('expedisi-generate-invoice.customers', ['q' => 'Customer Uji']))
            ->assertOk()
            ->assertJsonPath('0.code', 'CUS-001')
            ->assertJsonPath('0.name', 'Customer Uji');
    }

    public function test_generating_invoice_from_any_group_member_invoices_the_group_once(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createGroup();

        $this->post(route('expedisi-generate-invoice.store'), [
            'selected_sj' => 'SJ-002',
            'TGLFAKTUR' => '2026-10-25',
            'BAYAR' => 25000,
            'KETERANGAN' => 'INVOICE DARI EXPEDISI',
        ])->assertRedirect(route('expedisi-invoice.show', 'FJO2026000001'));

        $this->assertDatabaseCount('arh', 1);
        $invoice = Arh::firstOrFail();
        $this->assertSame('FJO2026000001', $invoice->NOFAKTUR);
        $this->assertSame('120000.00', $invoice->PIUTANG);
        $this->assertSame('25000.00', $invoice->BAYAR);
        $this->assertSame('120000.00', $invoice->SALDO);
        $this->assertSame('Expedisi', $invoice->SOURCE);
        $this->assertNull($invoice->NOJURNAL);
        $this->assertNull($invoice->jurnal_id);
        $this->assertSame('2026-10-30', $invoice->TGLJT->format('Y-m-d'));
        $this->assertDatabaseCount('jurnals', 0);

        $this->assertDatabaseHas('expedisis', [
            'NOSJ' => 'SJ-001', 'INVOICE' => 'FJO2026000001', 'BAYAR' => 25000, 'PIUTANG' => 95000,
        ]);
        $this->assertDatabaseHas('expedisis', [
            'NOSJ' => 'SJ-002', 'INVOICE' => 'FJO2026000001', 'PIUTANG' => 0,
        ]);
    }

    public function test_export_reads_live_receivables_without_duplicating_group_payment_totals(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createGroup();
        Expedisi::where('GB', 'GB-001')->update(['INVOICE' => 'FJO2026000001', 'TGLINVOICE' => '2026-10-25']);
        Arh::create([
            'NOFAKTUR' => 'FJO2026000001',
            'TGLFAKTUR' => '2026-10-25',
            'CUSTOMER' => 'CUS-001',
            'PIUTANG' => 120000,
            'BAYAR' => 25000,
            'SALDO' => 120000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'Expedisi',
        ]);

        $rows = (new ExpedisiInvoiceGenerateExport('2026-10-01', '2026-10-31', 'sudah'))->collection();

        $this->assertCount(2, $rows);
        $this->assertSame(25000.0, (float) $rows[0]->BAYAR);
        $this->assertSame(95000.0, (float) $rows[0]->PIUTANG);
        $this->assertSame(0.0, (float) $rows[1]->BAYAR);
        $this->assertSame(0.0, (float) $rows[1]->PIUTANG);
        $this->assertSame(25000.0, (float) $rows->sum('BAYAR'));
        $this->assertSame(95000.0, (float) $rows->sum('PIUTANG'));

        $this->get(route('expedisi-generate-invoice.export', [
            'tanggal_dari' => '2026-10-01',
            'tanggal_sampai' => '2026-10-31',
            'filter_inv_gen' => 'sudah',
        ]))->assertOk();
    }

    public function test_generation_rejects_multiple_selected_sj_instead_of_merging_invoices(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createGroup();

        $this->from(route('expedisi-generate-invoice.index'))
            ->post(route('expedisi-generate-invoice.store'), [
                'selected_sj' => ['SJ-001', 'SJ-002'],
                'TGLFAKTUR' => '2026-10-25',
            ])
            ->assertSessionHasErrors('selected_sj');

        $this->assertDatabaseCount('arh', 0);
    }

    public function test_generation_does_not_create_invoice_in_a_closed_accounting_year(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createGroup();
        YearClosing::create(['year' => '2026', 'closed_at' => now()]);

        $this->from(route('expedisi-generate-invoice.index'))
            ->post(route('expedisi-generate-invoice.store'), [
                'selected_sj' => 'SJ-001',
                'TGLFAKTUR' => '2026-10-25',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('arh', 0);
        $this->assertDatabaseHas('expedisis', ['NOSJ' => 'SJ-001', 'INVOICE' => null]);
    }

    public function test_initial_payment_cannot_exceed_the_invoice_total(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createGroup();

        $this->from(route('expedisi-generate-invoice.index'))
            ->post(route('expedisi-generate-invoice.store'), [
                'selected_sj' => 'SJ-001',
                'TGLFAKTUR' => '2026-10-25',
                'BAYAR' => 120001,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('arh', 0);
        $this->assertDatabaseHas('expedisis', ['NOSJ' => 'SJ-001', 'INVOICE' => null]);
    }

    public function test_fully_paid_invoice_is_preserved_in_invoice_history_without_journal(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createGroup();

        $this->post(route('expedisi-generate-invoice.store'), [
            'selected_sj' => 'SJ-001',
            'TGLFAKTUR' => '2026-10-25',
            'BAYAR' => 120000,
        ])->assertRedirect(route('expedisi-invoice.show', 'FJO2026000001'));

        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => 'FJO2026000001', 'STATUS' => 'PAID', 'BAYAR' => 120000,
        ]);
        $this->assertDatabaseHas('expedisis', [
            'NOSJ' => 'SJ-001', 'INVOICE' => 'FJO2026000001', 'PIUTANG' => 0,
        ]);
        $this->assertDatabaseCount('jurnals', 0);
    }

    public function test_editing_invoice_metadata_updates_expedisi_rows_without_touching_journals(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createGroup();
        $this->createExistingInvoice();

        $this->get(route('expedisi-invoice-gen.edit', 'FJO2026000001'))
            ->assertOk()
            ->assertSee('transaksiExpedisiInvoiceEdit_TGLFAKTUR');

        $this->put(route('expedisi-invoice-gen.update', 'FJO2026000001'), [
            'TGLFAKTUR' => '2026-10-26',
            'TGLJT' => '2026-11-01',
            'KETERANGAN' => 'Invoice dikoreksi',
            'CUSTOMER' => 'Customer yang tidak boleh diubah',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => 'FJO2026000001',
            'CUSTOMER' => 'Customer Uji',
            'TGLFAKTUR' => '2026-10-26 00:00:00',
            'TGLJT' => '2026-11-01 00:00:00',
        ]);
        $this->assertDatabaseHas('expedisis', [
            'NOSJ' => 'SJ-002',
            'TGLINVOICE' => '2026-10-26',
            'TGLJT' => '2026-11-01',
            'KETERANGAN' => 'Invoice dikoreksi',
        ]);
        $this->assertDatabaseCount('jurnals', 0);
    }

    public function test_invoice_edit_is_blocked_when_payment_or_receipt_exists(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createGroup();
        $this->createExistingInvoice(['BAYAR' => 10000]);

        $this->get(route('expedisi-invoice-gen.edit', 'FJO2026000001'))
            ->assertRedirect(route('expedisi-invoice-gen.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => 'FJO2026000001', 'TGLFAKTUR' => '2026-10-25 00:00:00',
        ]);
    }

    private function loginWithInvoicePermissions(): User
    {
        $role = Role::create(['name' => 'Administrator']);
        $menu = Menu::updateOrCreate(
            ['route_name' => 'expedisi-generate-invoice.index'],
            ['name' => 'Generate Invoice EKS', 'section' => 'Transaksi', 'is_sidebar' => true]
        );
        $role->menus()->attach($menu->id, [
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_export' => true,
        ]);
        $editMenu = Menu::updateOrCreate(
            ['route_name' => 'expedisi-invoice-gen.index'],
            ['name' => 'Edit Invoice EKS', 'section' => 'Transaksi', 'is_sidebar' => true]
        );
        $role->menus()->attach($editMenu->id, ['can_view' => true, 'can_edit' => true]);

        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function createGroup(): void
    {
        Mcustomer::create([
            'CUSTOMER' => 'CUS-001',
            'kode_cus' => 'LEGACY-001',
            'NAMACUST' => 'Customer Uji',
            'TOPKREDIT' => 5,
        ]);

        foreach ([
            ['SJ-001', 120000],
            ['SJ-002', 0],
        ] as [$noSj, $grand]) {
            Expedisi::create([
                'NOMUAT' => 'MUAT-001',
                'TGLMUAT' => '2026-10-24',
                'NOSJ' => $noSj,
                'CUSTOMER_KODE' => 'CUS-001',
                'CUSTOMER' => 'Customer Uji',
                'NAMA_KENDARAAN' => 'DK 1234 XX',
                'GRAND' => $grand,
                'PIUTANG' => $grand,
                'GB' => 'GB-001',
                'JENIS' => 'EKS',
            ]);
        }
    }

    private function createExistingInvoice(array $overrides = []): void
    {
        Expedisi::where('GB', 'GB-001')->update([
            'INVOICE' => 'FJO2026000001',
            'TGLINVOICE' => '2026-10-25',
            'TGLJT' => '2026-10-30',
        ]);

        Arh::create(array_merge([
            'NOFAKTUR' => 'FJO2026000001',
            'TGLFAKTUR' => '2026-10-25',
            'TGLJT' => '2026-10-30',
            'CUSTOMER' => 'Customer Uji',
            'PIUTANG' => 120000,
            'BAYAR' => 0,
            'SALDO' => 120000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'Expedisi',
        ], $overrides));
    }
}
