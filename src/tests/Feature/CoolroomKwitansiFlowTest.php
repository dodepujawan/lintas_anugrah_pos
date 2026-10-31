<?php

namespace Tests\Feature;

use App\Models\Coolroom;
use App\Models\Kwitansi;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CoolroomKwitansiFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_index_filters_legacy_coolroom_invoice_rows_by_receipt_status(): void
    {
        $this->loginWithKwitansiPermissions();
        $this->createInvoice('FCO-OPEN', null);
        $this->createInvoice('FCO-RECEIPTED', 'KW20260000001');
        $this->createInvoice('FCO-ZERO', null, 0);

        $this->get(route('coolroom-kwitansi.index'))
            ->assertOk()
            ->assertSee('Kwitansi Coolroom')
            ->assertSee('FCO-OPEN')
            ->assertDontSee('FCO-RECEIPTED')
            ->assertDontSee('FCO-ZERO');

        $this->get(route('coolroom-kwitansi.index', ['status_kwt' => 'sudah']))
            ->assertOk()
            ->assertSee('FCO-RECEIPTED')
            ->assertSee('KW20260000001')
            ->assertDontSee('FCO-OPEN');
    }

    public function test_processing_invoice_sets_legacy_fields_and_supports_text_and_pdf_printing(): void
    {
        Carbon::setTestNow('2026-10-31 09:00:00');
        $user = $this->loginWithKwitansiPermissions();
        $this->createInvoice('FCO-OPEN', null, 120000, true);

        $this->post(route('coolroom-kwitansi.store'), ['invoice' => 'FCO-OPEN'])
            ->assertRedirect(route('coolroom-kwitansi.index', ['status_kwt' => 'sudah']))
            ->assertSessionHas('autoPrintKwitansi', 'KW20260000001');

        $this->assertDatabaseHas('coolrooms', [
            'INVOICE' => 'FCO-OPEN',
            'NOSJ' => 'SC-002',
            'KWT' => 'KW20260000001',
            'TGLKW' => '2026-10-31 00:00:00',
        ]);
        $this->assertDatabaseHas('kwitansi', [
            'NOKWT' => 'KW20260000001',
            'TGL' => '2026-10-31 00:00:00',
            'FDOK_TRANS' => 'FCO-OPEN',
            'CUSTOMER' => 'Coolroom Customer',
            'NOSJ' => 'SC-001,SC-002',
            'FKETERANG' => 'SEWA RUANG DINGIN PADA Coolroom Customer, INVOICE : FCO-OPEN',
            'FNAMA' => 'Coolroom Customer',
            'FNIL_DOK' => 120000,
            'USERINPUT' => $user->user_id ?? $user->id,
            'TOTAL' => 110000,
            'PPN' => 11,
            'DISC' => 5,
            'NDISC' => 10000,
            'JENIS' => 'COL',
        ]);
        $this->assertDatabaseHas('coolrooms', [
            'INVOICE' => 'FCO-OPEN', 'NOSJ' => 'SC-002', 'BAYAR' => 25000, 'PIUTANG' => 95000,
        ]);

        $this->getJson(route('coolroom-kwitansi.text', 'KW20260000001'))
            ->assertOk()
            ->assertJsonStructure(['text', 'lines', 'invoice', 'kwitansi'])
            ->assertJsonPath('invoice', 'FCO-OPEN');
        $this->get(route('coolroom-kwitansi.pdf', 'KW20260000001'))->assertOk();
    }

    public function test_reversing_receipt_only_clears_coolroom_receipt_fields_and_col_record(): void
    {
        $this->loginWithKwitansiPermissions();
        $this->createInvoice('FCO-RECEIPTED', 'KW20260000001', 120000, true);
        Kwitansi::create([
            'NOKWT' => 'KW20260000001',
            'FDOK_TRANS' => 'FCO-RECEIPTED',
            'TGL' => '2026-10-31',
            'JENIS' => 'COL',
        ]);
        Kwitansi::create([
            'NOKWT' => 'KW20260000001',
            'FDOK_TRANS' => 'OTHER-DOCUMENT',
            'TGL' => '2026-10-31',
            'JENIS' => 'EKS',
        ]);

        $this->post(route('coolroom-kwitansi.destroy'), ['kwt' => 'KW20260000001'])
            ->assertRedirect(route('coolroom-kwitansi.index', ['status_kwt' => 'belum']))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kwitansi', ['NOKWT' => 'KW20260000001', 'JENIS' => 'COL']);
        $this->assertDatabaseHas('kwitansi', ['NOKWT' => 'KW20260000001', 'JENIS' => 'EKS']);
        $this->assertDatabaseHas('coolrooms', [
            'INVOICE' => 'FCO-RECEIPTED', 'NOSJ' => 'SC-001', 'KWT' => null, 'TGLKW' => null,
        ]);
        $this->assertDatabaseHas('coolrooms', [
            'INVOICE' => 'FCO-RECEIPTED', 'BAYAR' => 25000, 'PIUTANG' => 95000,
        ]);
    }

    private function loginWithKwitansiPermissions(): User
    {
        $role = Role::create(['name' => 'Administrator']);
        $menu = Menu::updateOrCreate(
            ['route_name' => 'coolroom-kwitansi.index'],
            ['name' => 'Kwitansi Coolroom', 'section' => 'Transaksi', 'is_sidebar' => true]
        );
        $role->menus()->attach($menu->id, [
            'can_view' => true,
            'can_create' => true,
            'can_delete' => true,
        ]);

        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function createInvoice(string $invoice, ?string $kwt, float $grand = 120000, bool $withDetails = false): void
    {
        $rows = $withDetails
            ? [
                ['SC-001', 0],
                ['SC-002', $grand],
            ]
            : [['SC-'.substr(md5($invoice), 0, 6), $grand]];

        foreach ($rows as [$nosj, $rowGrand]) {
            Coolroom::create([
                'CUSTOMER_KODE' => 'CUS-001',
                'CUSTOMER' => 'Coolroom Customer',
                'TGL' => '2026-10-30',
                'TGLSJ' => '2026-10-30',
                'NOSJ' => $nosj,
                'INVOICE' => $invoice,
                'TGLINVOICE' => '2026-10-30',
                'TOTAL' => $rowGrand > 0 ? 110000 : 0,
                'PPN' => $rowGrand > 0 ? 11 : 0,
                'DISC' => $rowGrand > 0 ? 5 : 0,
                'NDISC' => $rowGrand > 0 ? 10000 : 0,
                'GRAND' => $rowGrand,
                'BAYAR' => $rowGrand > 0 ? 25000 : 0,
                'PIUTANG' => $rowGrand > 0 ? $rowGrand - 25000 : 0,
                'KWT' => $kwt,
                'TGLKW' => $kwt ? '2026-10-30' : null,
            ]);
        }
    }
}
