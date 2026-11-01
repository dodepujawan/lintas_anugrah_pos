<?php

namespace Tests\Feature;

use App\Exports\KwitansiExport;
use App\Models\Coolroom;
use App\Models\Expedisi;
use App\Models\Kwitansi;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class KwitansiHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::create(['name' => 'Administrator']);
        $menu = Menu::updateOrCreate(
            ['route_name' => 'kwitansi-history.index'],
            ['name' => 'Kwitansi History', 'section' => 'Laporan', 'is_sidebar' => true]
        );
        $role->menus()->attach($menu->id, ['can_view' => true, 'can_export' => true]);

        $this->actingAs(User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]));
    }

    public function test_default_view_lists_only_unreceipted_expedisi_and_coolroom_invoices(): void
    {
        $this->createExpedisiInvoice('EKS-OPEN', 'EKS', null);
        $this->createExpedisiInvoice('REN-OPEN', 'REN', null);
        $this->createExpedisiInvoice('EKS-RECEIPTED', 'EKS', 'KW-EKS-1');
        $this->createCoolroomInvoice('COOL-OPEN', null);

        $this->get(route('kwitansi-history.index'))
            ->assertOk()
            ->assertSee('Belum Kwitansi')
            ->assertSee('EKS-OPEN')
            ->assertSee('COOL-OPEN')
            ->assertDontSee('REN-OPEN')
            ->assertDontSee('EKS-RECEIPTED');
    }

    public function test_sudah_view_uses_legacy_kwitansi_columns_and_search_is_scoped(): void
    {
        Kwitansi::create([
            'NOKWT' => 'KW-REN-1',
            'TGL' => '2026-10-15',
            'FDOK_TRANS' => 'INV-REN-1',
            'FKETERANG' => 'Customer Rent',
            'FNIL_DOK' => 125000,
            'JENIS' => 'REN',
        ]);
        Kwitansi::create([
            'NOKWT' => 'KW-COL-1',
            'TGL' => '2026-10-16',
            'FDOK_TRANS' => 'INV-COL-1',
            'FKETERANG' => 'Customer Coolroom',
            'FNIL_DOK' => 250000,
            'JENIS' => 'COL',
        ]);
        $this->assertDatabaseHas('kwitansi', ['NOKWT' => 'KW-REN-1']);
        $this->assertSame(2, Kwitansi::count());
        $this->createExpedisiInvoice('EKS-OPEN', 'EKS', null);

        $this->get(route('kwitansi-history.index', ['status_kwt_his' => 'sudah', 'search' => 'INV-REN-1']))
            ->assertOk()
            ->assertSee('KW-REN-1')
            ->assertSee('Customer Rent')
            ->assertSee('125.000')
            ->assertDontSee('KW-COL-1')
            ->assertDontSee('EKS-OPEN');
    }

    public function test_sudah_total_sums_the_legacy_amount_column(): void
    {
        Kwitansi::create([
            'NOKWT' => 'KW-REN-1',
            'TGL' => '2026-10-15',
            'FDOK_TRANS' => 'INV-REN-1',
            'FNIL_DOK' => 125000,
            'JENIS' => 'REN',
        ]);
        Kwitansi::create([
            'NOKWT' => 'KW-COL-1',
            'TGL' => '2026-10-16',
            'FDOK_TRANS' => 'INV-COL-1',
            'FNIL_DOK' => 250000,
            'JENIS' => 'COL',
        ]);

        $this->get(route('kwitansi-history.index', ['status_kwt_his' => 'sudah']))
            ->assertOk()
            ->assertSee('375.000');
    }

    public function test_export_uses_requested_status_date_range_and_export_route(): void
    {
        Kwitansi::create([
            'NOKWT' => 'KW-IN',
            'TGL' => '2026-10-15',
            'FDOK_TRANS' => 'INV-IN-RANGE',
            'FKETERANG' => 'Included',
            'FNIL_DOK' => 125000,
            'JENIS' => 'REN',
        ]);
        Kwitansi::create([
            'NOKWT' => 'KW-OUT',
            'TGL' => '2026-09-15',
            'FDOK_TRANS' => 'INV-OUTSIDE-RANGE',
            'FNIL_DOK' => 300000,
            'JENIS' => 'REN',
        ]);
        $this->createExpedisiInvoice('EKS-UNPAID', 'EKS', null);

        Excel::fake();

        $this->get(route('kwitansi-history.export', [
            'status_kwt_his' => 'sudah',
            'tanggal_dari' => '2026-10-01',
            'tanggal_sampai' => '2026-10-31',
        ]))->assertOk();

        Excel::assertDownloaded(
            'laporan_kwitansi_sudah_2026-10-01_sd_2026-10-31.xlsx',
            function (KwitansiExport $export): bool {
                $rows = $export->collection();
                $this->assertCount(1, $rows);
                $this->assertSame('INV-IN-RANGE', $rows->first()[3]);

                return true;
            }
        );

        $this->get(route('kwitansi-history.export', [
            'status_kwt_his' => 'belum',
            'tanggal_dari' => '2026-10-01',
            'tanggal_sampai' => '2026-10-31',
        ]))->assertOk();

        Excel::assertDownloaded(
            'laporan_kwitansi_belum_2026-10-01_sd_2026-10-31.xlsx',
            function (KwitansiExport $export): bool {
                $rows = $export->collection();
                $this->assertCount(1, $rows);
                $this->assertSame('EKS-UNPAID', $rows->first()[3]);

                return true;
            }
        );
    }

    public function test_export_rejects_an_inverted_date_range(): void
    {
        $this->get(route('kwitansi-history.export', [
            'status_kwt_his' => 'belum',
            'tanggal_dari' => '2026-10-31',
            'tanggal_sampai' => '2026-10-01',
        ]))->assertSessionHasErrors('tanggal_sampai');
    }

    private function createExpedisiInvoice(string $invoice, string $jenis, ?string $kwt): void
    {
        Expedisi::create([
            'INVOICE' => $invoice,
            'TGLINVOICE' => '2026-10-15',
            'CUSTOMER' => 'Customer '.$jenis,
            'GRAND' => 150000,
            'JENIS' => $jenis,
            'kwt' => $kwt,
        ]);
    }

    private function createCoolroomInvoice(string $invoice, ?string $kwt): void
    {
        Coolroom::create([
            'INVOICE' => $invoice,
            'TGLINVOICE' => '2026-10-15',
            'CUSTOMER' => 'Customer Coolroom',
            'GRAND' => 200000,
            'KWT' => $kwt,
        ]);
    }
}
