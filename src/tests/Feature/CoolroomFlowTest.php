<?php

namespace Tests\Feature;

use App\Models\Coolroom;
use App\Models\Menu;
use App\Models\Mcustomer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoolroomFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_calculates_legacy_amounts_and_generates_surat_jalan(): void
    {
        $this->loginWithCoolroomPermissions();
        $this->createCustomer();

        $this->get(route('coolroom.create'))
            ->assertOk()
            ->assertSee('transaksiCoolroomCreateForm')
            ->assertSee('Harga Boxing');

        $response = $this->post(route('coolroom.store'), [
            'TGLSJ' => '2026-10-20',
            'CUSTOMER_KODE' => 'CUS-001',
            'JUMLAH' => 1.234,
            'HARGA' => 50000,
            'BOXING' => false,
            'DISC' => 10,
            'PPN' => 11,
            'KETERANGAN' => 'Sewa ruang dingin',
        ]);

        $coolroom = Coolroom::firstOrFail();
        $response->assertRedirect(route('coolroom.show', $coolroom));
        $this->assertMatchesRegularExpression('/^SC\d{9}$/', $coolroom->NOSJ);
        $this->assertSame('1.234', $coolroom->JUMLAH);
        $this->assertSame('2026-10-20', $coolroom->TGLSJ->format('Y-m-d'));
        $this->assertDatabaseHas('coolrooms', [
            'id' => $coolroom->id,
            'CUSTOMER_KODE' => 'CUS-001',
            'CUSTOMER' => 'Customer Coolroom',
            'UNIT' => 'KG',
            'BOXING' => false,
            'SUBTOTAL' => 61700,
            'NDISC' => 6170,
            'DPP' => 55530,
            'NPPN' => 6108,
            'TOTAL' => 55530,
            'GRAND' => 61638,
        ]);
        $this->get(route('coolroom.show', $coolroom))->assertOk()->assertSee($coolroom->NOSJ);
        $this->get(route('coolroom.pdf', $coolroom->NOSJ))->assertOk();

        $response = $this->post(route('coolroom.store'), [
            'TGLSJ' => '2026-10-20',
            'CUSTOMER_KODE' => 'CUS-001',
            'JUMLAH' => 3,
            'HARGA' => 150000,
            'BOXING' => true,
            'DISC' => 0,
            'PPN' => 11,
        ]);

        $boxing = Coolroom::orderByDesc('id')->firstOrFail();
        $response->assertRedirect(route('coolroom.show', $boxing));
        $this->assertSame(150000.0, (float) $boxing->SUBTOTAL);
        $this->assertSame(166500.0, (float) $boxing->GRAND);
        $this->assertNotSame($coolroom->NOSJ, $boxing->NOSJ);
    }

    public function test_update_recalculates_and_invoiced_rows_cannot_be_edited_or_deleted(): void
    {
        $this->loginWithCoolroomPermissions();
        $this->createCustomer();
        $coolroom = $this->createCoolroom();
        $this->get(route('coolroom.edit', $coolroom))
            ->assertOk()
            ->assertSee('transaksiCoolroomEditForm');

        $this->put(route('coolroom.update', $coolroom), [
            'TGLSJ' => '2026-10-21',
            'CUSTOMER_KODE' => 'CUS-001',
            'JUMLAH' => 2.5,
            'HARGA' => 20000,
            'BOXING' => false,
            'DISC' => 5,
            'PPN' => 11,
            'KETERANGAN' => 'Koreksi transaksi',
        ])->assertRedirect(route('coolroom.show', $coolroom));

        $coolroom->refresh();
        $this->assertSame('2026-10-21', $coolroom->TGLSJ->format('Y-m-d'));
        $this->assertDatabaseHas('coolrooms', [
            'id' => $coolroom->id,
            'SUBTOTAL' => 50000,
            'NDISC' => 2500,
            'DPP' => 47500,
            'NPPN' => 5225,
            'GRAND' => 52725,
        ]);

        $coolroom->update(['INVOICE' => 'FJO-COOL-001']);
        $this->get(route('coolroom.edit', $coolroom))
            ->assertRedirect(route('coolroom.index'))
            ->assertSessionHas('error');

        $this->put(route('coolroom.update', $coolroom), [
            'TGLSJ' => '2026-10-22',
            'CUSTOMER_KODE' => 'CUS-001',
            'JUMLAH' => 1,
            'HARGA' => 100,
            'DISC' => 0,
            'PPN' => 0,
        ])->assertRedirect(route('coolroom.index'))
            ->assertSessionHas('error');

        $this->delete(route('coolroom.destroy', $coolroom))
            ->assertRedirect(route('coolroom.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('coolrooms', ['id' => $coolroom->id, 'INVOICE' => 'FJO-COOL-001']);
    }

    public function test_index_only_lists_uninvoiced_rows_and_customer_lookup_returns_legacy_code(): void
    {
        $this->loginWithCoolroomPermissions();
        $this->createCustomer();
        $available = $this->createCoolroom();
        $invoiced = $this->createCoolroom(['NOSJ' => 'SC102600002', 'INVOICE' => 'FJO-COOL-002']);

        $this->get(route('coolroom.index'))
            ->assertOk()
            ->assertSee($available->NOSJ)
            ->assertDontSee($invoiced->NOSJ)
            ->assertSee('Jumlah KG')
            ->assertSee('transaksiCoolroomIndexDelete_' . $available->NOSJ);

        $this->postJson(route('coolroom.customer.search'), ['q' => 'Customer Coolroom'])
            ->assertOk()
            ->assertJsonPath('data.0.CUSTOMER', 'CUS-001')
            ->assertJsonPath('data.0.NAMACUST', 'Customer Coolroom');

        $this->delete(route('coolroom.destroy', $available))
            ->assertRedirect(route('coolroom.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('coolrooms', ['id' => $available->id]);
    }

    private function loginWithCoolroomPermissions(): User
    {
        $role = Role::create(['name' => 'Administrator']);
        $menu = Menu::updateOrCreate(
            ['route_name' => 'coolroom.index'],
            ['name' => 'Coolroom', 'section' => 'Transaksi', 'is_sidebar' => true]
        );
        $role->menus()->attach($menu->id, [
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
        ]);

        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function createCustomer(): Mcustomer
    {
        return Mcustomer::create([
            'CUSTOMER' => 'CUS-001',
            'kode_cus' => 'CUS-001',
            'NAMACUST' => 'Customer Coolroom',
            'TELEPON' => '08123456789',
        ]);
    }

    private function createCoolroom(array $overrides = []): Coolroom
    {
        return Coolroom::create(array_merge([
            'CUSTOMER_KODE' => 'CUS-001',
            'CUSTOMER' => 'Customer Coolroom',
            'TGL' => '2026-10-20',
            'TGLSJ' => '2026-10-20',
            'TGLMUAT' => '2026-10-20',
            'NOSJ' => 'SC102600001',
            'UNIT' => 'KG',
            'JUMLAH' => 1.234,
            'HARGA' => 50000,
            'BOXING' => false,
            'SUBTOTAL' => 61700,
            'DISC' => 10,
            'NDISC' => 6170,
            'DPP' => 55530,
            'PPN' => 11,
            'NPPN' => 6108,
            'TOTAL' => 55530,
            'GRAND' => 61638,
        ], $overrides));
    }
}
