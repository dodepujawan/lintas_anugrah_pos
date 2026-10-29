<?php

namespace Tests\Feature;

use App\Models\Arh;
use App\Models\Expedisi;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentPendinginInvoiceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_list_uses_only_loaded_rent_pendingin_rows_and_exposes_legacy_fields(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createSj('REN-001', ['JENIS' => 'REN']);
        $this->createSj('EKS-001', ['JENIS' => 'EKS']);
        $this->createSj('REN-UNLOADED', ['JENIS' => 'REN', 'NOMUAT' => null]);

        $this->get(route('rent-pendingin-invoice.index'))
            ->assertOk()
            ->assertSee('Surat Jalan siap invoice')
            ->assertSee('rentInvoiceFormCard');

        $this->getJson(route('rent-pendingin-invoice.data', ['status_inv_gen' => 'belum']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.NOSJ', 'REN-001')
            ->assertJsonPath('data.0.NOMUAT', 'MUAT-001')
            ->assertJsonPath('data.0.GRAND', 120000);

        $this->get(route('rent-pendingin-invoice.export', [
            'tanggal_dari' => '2026-10-01',
            'tanggal_sampai' => '2026-10-31',
            'filter_kwt_dgn' => 'belum',
        ]))->assertOk();
    }

    public function test_saving_invoice_follows_single_sj_legacy_flow_and_redirects_to_preview(): void
    {
        $this->loginWithInvoicePermissions();
        $this->createSj('REN-001', ['JENIS' => 'REN']);

        $this->post(route('rent-pendingin-invoice.store'), [
            'nosj' => 'REN-001',
            'bayar' => 20000,
            'top' => 14,
            'tgl_jtp' => '2026-11-12',
            'status_inv_gen' => 'belum',
        ])->assertRedirect(route('rent-pendingin-invoice.show', 'FJO2026000001'));

        $this->assertDatabaseHas('expedisis', [
            'NOSJ' => 'REN-001',
            'JENIS' => 'REN',
            'INVOICE' => 'FJO2026000001',
            'STS' => 'INVOICE',
            'BAYAR' => 20000,
            'PIUTANG' => 100000,
            'TGLJT' => '2026-11-12 00:00:00',
        ]);
        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => 'FJO2026000001',
            'PIUTANG' => 100000,
            'BAYAR' => 0,
            'SALDO' => 14,
        ]);

        $this->get(route('rent-pendingin-invoice.show', 'FJO2026000001'))
            ->assertOk()
            ->assertSee('Preview Invoice Rent Pendingin')
            ->assertSee('rentInvoiceDirectPrint')
            ->assertSee('Cetak PDF');
        $this->getJson(route('rent-pendingin-invoice.text', 'FJO2026000001'))
            ->assertOk()
            ->assertJsonStructure(['text', 'total_pages'])
            ->assertJsonPath('total_pages', 1);
        $this->get(route('rent-pendingin-invoice.pdf', 'FJO2026000001'))->assertOk();
    }

    public function test_detailed_edit_updates_expedisi_and_rejects_paid_invoice(): void
    {
        $this->loginWithInvoicePermissions();
        $row = $this->createSj('REN-001', [
            'JENIS' => 'REN',
            'INVOICE' => 'FJO2026000001',
            'TGLINVOICE' => '2026-10-29',
            'TGLJT' => '2026-11-12',
        ]);
        Arh::create([
            'NOFAKTUR' => 'FJO2026000001',
            'TGLFAKTUR' => '2026-10-29',
            'TGLJT' => '2026-11-12',
            'CUSTOMER' => 'Customer Test',
            'PIUTANG' => 120000,
            'BAYAR' => 0,
            'SALDO' => 14,
        ]);

        $this->get(route('rent-pendingin-invoice-gen.edit', 'FJO2026000001'))
            ->assertOk()
            ->assertSee('Rincian yang dapat dikoreksi');

        $this->put(route('rent-pendingin-invoice-gen.update', 'FJO2026000001'), [
            'item' => 'Rental terkoreksi',
            'jumlah' => 2,
            'harga' => 50000,
            'disc' => 10,
            'del_charge' => 5000,
            'ppn' => 11,
            'bayar' => 10000,
            'tgl_jt' => '2026-11-15',
            'keterangan' => 'Koreksi invoice',
        ])->assertRedirect(route('rent-pendingin-invoice-gen.edit', 'FJO2026000001'));

        $this->assertDatabaseHas('expedisis', [
            'id' => $row->id,
            'PESANAN' => 'Rental terkoreksi',
            'NDISC' => 10000,
            'TOTAL' => 90000,
            'GRAND' => 104900,
            'BAYAR' => 10000,
            'PIUTANG' => 94900,
        ]);
        $this->assertDatabaseHas('arh', ['NOFAKTUR' => 'FJO2026000001', 'PIUTANG' => 94900]);

        Arh::where('NOFAKTUR', 'FJO2026000001')->update(['BAYAR' => 100]);
        $this->get(route('rent-pendingin-invoice-gen.edit', 'FJO2026000001'))
            ->assertRedirect(route('rent-pendingin-invoice-gen.index'))
            ->assertSessionHas('error');
    }

    private function loginWithInvoicePermissions(): User
    {
        $role = Role::create(['name' => 'Administrator']);
        foreach (['rent-pendingin-invoice.index', 'rent-pendingin-invoice-gen.index'] as $routeName) {
            $menu = Menu::updateOrCreate(
                ['route_name' => $routeName],
                ['name' => 'Invoice Rent Pendingin', 'section' => 'Transaksi', 'is_sidebar' => true]
            );
            $role->menus()->attach($menu->id, [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_export' => true,
            ]);
        }

        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function createSj(string $nosj, array $overrides = []): Expedisi
    {
        return Expedisi::create(array_merge([
            'NOSJ' => $nosj,
            'NOMUAT' => 'MUAT-001',
            'TGLMUAT' => '2026-10-29',
            'CUSTOMER_KODE' => 'CUS-001',
            'CUSTOMER' => 'Customer Test',
            'NAMA_KENDARAAN' => 'DK 1234 XX',
            'PESANAN' => 'Rental Ruangan',
            'JUMLAH' => 1,
            'UNIT' => 'UNIT',
            'HARGA' => 120000,
            'TOTAL' => 120000,
            'GRAND' => 120000,
            'PIUTANG' => 120000,
            'JENIS' => 'REN',
        ], $overrides));
    }
}
