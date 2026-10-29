<?php

namespace Tests\Feature;

use App\Models\Expedisi;
use App\Models\Kwitansi;
use App\Models\Mcustomer;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RentPendinginKwitansiFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_index_renders_and_filters_legacy_invoice_rows_by_receipt_status(): void
    {
        $this->loginWithKwitansiPermissions();
        $this->createInvoice('FJO-OPEN', null);
        $this->createInvoice('FJO-RECEIPTED', 'KW20260000001');
        $this->createInvoice('EKS-001', null, 'EKS');

        $this->get(route('rent-pendingin-kwitansi.index'))
            ->assertOk()
            ->assertSee('Kwitansi Rent Pendingin')
            ->assertSee('FJO-OPEN')
            ->assertDontSee('FJO-RECEIPTED')
            ->assertDontSee('EKS-001');

        $this->get(route('rent-pendingin-kwitansi.index', ['status_kwt' => 'sudah']))
            ->assertOk()
            ->assertSee('FJO-RECEIPTED')
            ->assertSee('KW20260000001')
            ->assertDontSee('FJO-OPEN');
    }

    public function test_processing_a_receipt_updates_invoice_rows_and_creates_legacy_kwitansi_record(): void
    {
        Carbon::setTestNow('2026-10-29 09:00:00');
        $user = $this->loginWithKwitansiPermissions();
        $this->createInvoice('FJO-OPEN', null, 'REN', true);

        $this->post(route('rent-pendingin-kwitansi.store'), ['invoice' => 'FJO-OPEN'])
            ->assertRedirect(route('rent-pendingin-kwitansi.index', ['status_kwt' => 'sudah']))
            ->assertSessionHas('autoPrintKwitansi', 'KW20260000001');

        $this->assertDatabaseHas('expedisis', [
            'INVOICE' => 'FJO-OPEN', 'NOSJ' => 'REN-002', 'kwt' => 'KW20260000001', 'TGLKW' => '2026-10-29 00:00:00',
        ]);
        $this->assertDatabaseHas('kwitansi', [
            'NOKWT' => 'KW20260000001',
            'FDOK_TRANS' => 'FJO-OPEN',
            'CUSTOMER' => 'CUS-001',
            'NOSJ' => 'REN-001,REN-002',
            'FNAMA' => 'Customer Test Ltd',
            'FNIL_DOK' => 120000,
            'USERINPUT' => $user->id,
            'JENIS' => 'REN',
        ]);
        $this->assertSame(1, Kwitansi::where('NOKWT', 'KW20260000001')->count());

        $this->get(route('rent-pendingin-kwitansi.text', 'KW20260000001'))
            ->assertOk()
            ->assertJsonStructure(['text', 'lines', 'invoice', 'kwitansi'])
            ->assertJsonPath('invoice', 'FJO-OPEN');
        $this->get(route('rent-pendingin-kwitansi.pdf', 'KW20260000001'))->assertOk();
    }

    public function test_reversing_receipt_only_clears_receipt_fields_and_record(): void
    {
        $this->loginWithKwitansiPermissions();
        $this->createInvoice('FJO-RECEIPTED', 'KW20260000001', 'REN', true);
        Kwitansi::create([
            'NOKWT' => 'KW20260000001',
            'FDOK_TRANS' => 'FJO-RECEIPTED',
            'TGL' => '2026-10-29',
            'JENIS' => 'REN',
        ]);

        $this->post(route('rent-pendingin-kwitansi.destroy'), ['kwt' => 'KW20260000001'])
            ->assertRedirect(route('rent-pendingin-kwitansi.index', ['status_kwt' => 'belum']))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kwitansi', ['NOKWT' => 'KW20260000001']);
        $this->assertDatabaseHas('expedisis', [
            'INVOICE' => 'FJO-RECEIPTED', 'NOSJ' => 'REN-001', 'kwt' => null, 'TGLKW' => null,
        ]);
        $this->assertDatabaseHas('expedisis', [
            'INVOICE' => 'FJO-RECEIPTED', 'BAYAR' => 25000, 'PIUTANG' => 95000,
        ]);
    }

    private function loginWithKwitansiPermissions(): User
    {
        $role = Role::create(['name' => 'Administrator']);
        $menu = Menu::updateOrCreate(
            ['route_name' => 'rent-pendingin-kwitansi.index'],
            ['name' => 'Kwitansi Rent Pendingin', 'section' => 'Transaksi', 'is_sidebar' => true]
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

    private function createInvoice(string $invoice, ?string $kwt, string $jenis = 'REN', bool $withDetails = false): void
    {
        Mcustomer::updateOrCreate(
            ['kode_cus' => 'CUS-001'],
            ['CUSTOMER' => 'CUS-001', 'NAMACUST' => 'Customer Test Ltd']
        );

        $rows = $withDetails
            ? [['REN-001', 0], ['REN-002', 120000]]
            : [['REN-'.substr(md5($invoice), 0, 6), 120000]];

        foreach ($rows as [$nosj, $grand]) {
            Expedisi::create([
                'NOMUAT' => 'MUAT-001',
                'TGLMUAT' => '2026-10-25',
                'NOSJ' => $nosj,
                'CUSTOMER_KODE' => 'CUS-001',
                'CUSTOMER' => 'CUS-001',
                'NAMA_KENDARAAN' => 'DK 1234 XX',
                'PESANAN' => 'Rental Pendingin',
                'JUMLAH' => 1,
                'UNIT' => 'UNIT',
                'HARGA' => $grand,
                'TOTAL' => $grand,
                'GRAND' => $grand,
                'BAYAR' => 25000,
                'PIUTANG' => $grand - 25000,
                'INVOICE' => $invoice,
                'TGLINVOICE' => '2026-10-25',
                'kwt' => $kwt,
                'TGLKW' => $kwt ? '2026-10-29' : null,
                'JENIS' => $jenis,
            ]);
        }
    }
}
