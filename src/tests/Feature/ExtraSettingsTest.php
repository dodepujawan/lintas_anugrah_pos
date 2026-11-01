<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Rekening;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtraSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Administrator']);
        foreach ([
            'pajak.get' => ['name' => 'Pajak', 'section' => 'Extra'],
            'rekening.index' => ['name' => 'Rekening', 'section' => 'Extra'],
            'signature.get' => ['name' => 'Signature', 'section' => 'Extra'],
        ] as $route => $attributes) {
            $menu = Menu::query()->updateOrCreate(
                ['route_name' => $route],
                $attributes + ['icon' => 'bi bi-gear', 'is_sidebar' => true]
            );
            $role->menus()->attach($menu->id, [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_post' => true,
                'can_void' => false,
                'can_export' => false,
            ]);
        }

        $this->actingAs(User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]));
    }

    public function test_pajak_settings_page_updates_the_legacy_rate_and_keeps_json_reads(): void
    {
        $this->get(route('pajak.get'))
            ->assertOk()
            ->assertSee('Pengaturan Pajak')
            ->assertSee('PPN (%)');

        $this->put(route('pajak.get.update'), ['PPN' => 11.5])
            ->assertRedirect(route('pajak.get'));

        $this->assertDatabaseHas('ol_pajaks', [
            'PPN' => 11.5,
            'is_active' => true,
        ]);
        $this->getJson(route('pajak.get'))
            ->assertOk()
            ->assertJsonPath('PPN', '11.50');
    }

    public function test_signature_settings_page_updates_the_active_signer(): void
    {
        $this->get(route('signature.get'))
            ->assertOk()
            ->assertSee('Penanggung Jawab');

        $this->put(route('signature.get.update'), [
            'NAMA' => 'Budi Santoso',
            'JABATAN' => 'Direktur',
        ])->assertRedirect(route('signature.get'));

        $this->assertDatabaseHas('signatures', [
            'NAMA' => 'Budi Santoso',
            'JABATAN' => 'Direktur',
            'is_active' => true,
        ]);
        $this->getJson(route('signature.get'))
            ->assertOk()
            ->assertJsonPath('NAMA', 'Budi Santoso');
    }

    public function test_rekening_form_saves_new_accounts_and_activation_is_exclusive(): void
    {
        $this->get(route('rekening.index'))->assertOk()->assertSee('Tambah Rekening');
        $this->get(route('rekening.create'))->assertOk()->assertSee('Nomor Rekening');

        $this->post(route('rekening.store'), [
            'BANK' => 'Bank Pertama',
            'NOREK' => '001-234',
            'NAMA' => 'Pemilik Pertama',
        ])->assertRedirect(route('rekening.index'));

        $first = Rekening::query()->where('NOREK', '001-234')->firstOrFail();
        $this->assertSame('0', (string) $first->AKTIF);
        $this->get(route('rekening.edit', $first))->assertOk()->assertSee('Bank Pertama');
        $this->get(route('rekening.show', $first))->assertOk()->assertSee('001-234');

        $second = Rekening::create([
            'BANK' => 'Bank Kedua',
            'NOREK' => '987-654',
            'NAMA' => 'Pemilik Kedua',
            'AKTIF' => '0',
        ]);
        Rekening::query()->whereKey($first->id)->update(['AKTIF' => '1']);

        $this->post(route('rekening.pilih.post', $second))
            ->assertRedirect(route('rekening.index'));

        $this->assertSame('0', (string) $first->fresh()->AKTIF);
        $this->assertSame('1', (string) $second->fresh()->AKTIF);
        $this->assertSame(1, Rekening::query()->where('AKTIF', '1')->count());
    }
}
