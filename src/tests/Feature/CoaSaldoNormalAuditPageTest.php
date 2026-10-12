<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoaSaldoNormalAuditPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_coa_saldo_normal_audit_page(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        Coa::create([
            'kode_akun' => '3101',
            'nama_akun' => 'Laba Ditahan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Modal',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('coa-saldo-normal'));

        $response->assertOk();
        $response->assertSee('Audit Saldo Normal COA');
        $response->assertSee('Laba Ditahan');
    }

    public function test_non_admin_cannot_view_coa_saldo_normal_audit_page(): void
    {
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $response = $this->actingAs($user)->get(route('coa-saldo-normal'));

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_can_fix_coa_saldo_normal_from_page(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $liability = Coa::create([
            'kode_akun' => '2101',
            'nama_akun' => 'Utang Usaha',
            'tipe' => 'Kewajiban',
            'kategori' => 'Kewajiban Lancar',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('coa-saldo-normal.fix'));

        $response->assertRedirect(route('coa-saldo-normal', absolute: false));
        $this->assertSame('Kredit', $liability->fresh()->saldo_normal);
    }
}
