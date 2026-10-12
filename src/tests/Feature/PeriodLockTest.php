<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\AuditLog;
use App\Models\Jurnal;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use App\Models\YearClosing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_period_lock_page(): void
    {
        $this->createPeriodLockMenu();
        $adminRole = Role::create(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->get(route('period-locks.index'));

        $response->assertOk();
        $response->assertSeeText('Lock Periode');
    }

    public function test_admin_can_access_audit_trail_page(): void
    {
        $this->createAuditLogMenu();
        $adminRole = Role::create(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->get(route('audit-logs.index'));

        $response->assertOk();
        $response->assertSeeText('Audit Trail');
    }

    public function test_lock_period_action_is_recorded_in_audit_trail(): void
    {
        $this->createPeriodLockMenu();
        $adminRole = Role::create(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($admin)->post(route('period-locks.store'), [
            'year' => '2026',
            'notes' => 'Final audit test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'lock',
            'module' => 'period-lock',
            'description' => 'Mengunci periode tahun 2026.',
        ]);
    }

    public function test_non_admin_cannot_access_period_lock_page(): void
    {
        $menu = $this->createPeriodLockMenu();
        $role = Role::create(['name' => 'User']);
        $role->menus()->attach($menu->id, [
            'can_view' => false,
            'can_create' => false,
            'can_edit' => false,
            'can_delete' => false,
            'can_post' => false,
            'can_void' => false,
            'can_export' => false,
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('period-locks.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Anda tidak memiliki hak akses untuk menu ini.');
    }

    public function test_locked_year_rejects_new_posted_journal(): void
    {
        $this->createPeriodLockMenu();
        $adminRole = Role::create(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        YearClosing::create([
            'year' => '2026',
            'closed_at' => now(),
            'notes' => 'Locked test',
        ]);

        [$cash, $revenue] = $this->createPostingAccounts();

        $response = $this->actingAs($admin)->post(route('jurnal.store'), [
            'no_jurnal' => 'JU-LOCK-001',
            'tanggal' => '2026-07-15',
            'keterangan' => 'Transaksi periode locked',
            'referensi' => 'LOCK',
            'coa_id' => [$cash->id, $revenue->id],
            'debit' => [1000, 0],
            'kredit' => [0, 1000],
            'deskripsi_item' => ['Kas', 'Pendapatan'],
        ]);

        $response->assertSessionHasErrors('tanggal');
        $this->assertDatabaseMissing('jurnals', ['no_jurnal' => 'JU-LOCK-001']);
    }

    public function test_locked_year_rejects_posting_existing_draft(): void
    {
        $this->createPeriodLockMenu();
        $adminRole = Role::create(['name' => 'Administrator']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        YearClosing::create([
            'year' => '2026',
            'closed_at' => now(),
            'notes' => 'Locked test',
        ]);

        $journal = Jurnal::create([
            'no_jurnal' => 'JU-LOCK-DRAFT',
            'tanggal' => '2026-08-01',
            'status' => Jurnal::STATUS_DRAFT,
            'keterangan' => 'Draft locked period',
            'total_debit' => 0,
            'total_kredit' => 0,
            'referensi' => 'LOCK',
            'created_by' => $admin->id,
            'is_posted' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('jurnal.post', $journal));

        $response->assertRedirect(route('jurnal.index'));
        $response->assertSessionHas('error');
        $this->assertFalse($journal->fresh()->is_posted);
    }

    private function createPeriodLockMenu(): Menu
    {
        return Menu::updateOrCreate(
            ['route_name' => 'period-locks'],
            [
                'name' => 'Lock Periode',
                'section' => 'Pengaturan',
                'icon' => 'bi bi-lock-fill',
                'sort_order' => 5,
                'is_sidebar' => true,
            ]
        );
    }

    private function createAuditLogMenu(): Menu
    {
        return Menu::updateOrCreate(
            ['route_name' => 'audit-logs'],
            [
                'name' => 'Audit Trail',
                'section' => 'Pengaturan',
                'icon' => 'bi bi-clock-history',
                'sort_order' => 6,
                'is_sidebar' => true,
            ]
        );
    }

    private function createPostingAccounts(): array
    {
        $cash = Coa::create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $revenue = Coa::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Pendapatan',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        return [$cash, $revenue];
    }
}
