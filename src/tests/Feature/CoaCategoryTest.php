<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoaCategoryTest extends TestCase
{
    use RefreshDatabase;
    public function test_create_page_shows_bank_category_option(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('coa.create'));

        $response->assertOk();
        $response->assertSee('Bank');
    }

    public function test_edit_page_keeps_the_saved_category_option_visible(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        $coa = Coa::create([
            'kode_akun' => '10000001',
            'nama_akun' => 'Kas Test',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Header',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('coa.edit', $coa));

        $response->assertOk();
        $response->assertSee('Kas');
    }

    public function test_store_persists_saldo_normal_as_debit_or_kredit_string(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('coa.store'), [
            'kode_akun' => '10000002',
            'nama_akun' => 'Bank Test',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva Lancar',
            'level' => 'Header',
            'saldo_normal' => 'Debit',
            'deskripsi' => 'Test saldo normal',
            'is_active' => true,
        ]);

        $coa = Coa::where('kode_akun', '10000002')->firstOrFail();

        $response->assertRedirect(route('coa.index', ['page' => 1, 'focus' => $coa->id]));
        $this->assertSame('Debit', $coa->fresh()->saldo_normal);
    }

    public function test_update_redirects_to_the_updated_account_view(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        $coa = Coa::create([
            'kode_akun' => '10000003',
            'nama_akun' => 'Kas Test 2',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva Lancar',
            'level' => 'Header',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('coa.update', $coa), [
            'kode_akun' => '10000003',
            'nama_akun' => 'Kas Test 2 Updated',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva Lancar',
            'level' => 'Header',
            'saldo_normal' => 'Debit',
            'deskripsi' => 'Updated',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('coa.index', ['page' => 1, 'focus' => $coa->id]));
        $this->assertSame('Kas Test 2 Updated', $coa->fresh()->nama_akun);
    }
}
