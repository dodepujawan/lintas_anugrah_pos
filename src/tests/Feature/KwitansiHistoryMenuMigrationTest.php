<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KwitansiHistoryMenuMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_repairs_sidebar_flag_and_copies_receipt_permissions(): void
    {
        $admin = Role::create(['name' => 'Administrator']);
        $operator = Role::create(['name' => 'Receipt Operator']);
        $sourceMenu = Menu::query()->updateOrCreate(
            ['route_name' => 'expedisi-kwitansi.index'],
            [
                'name' => 'Kwitansi EKS',
                'section' => 'Transaksi',
                'icon' => 'bi bi-receipt',
                'sort_order' => 103,
                'is_sidebar' => true,
            ]
        );
        $operator->menus()->attach($sourceMenu->id, [
            'can_view' => true,
            'can_create' => true,
            'can_edit' => false,
            'can_delete' => false,
            'can_post' => false,
            'can_void' => false,
            'can_export' => false,
        ]);

        $historyMenu = Menu::query()->where('route_name', 'kwitansi-history.index')->firstOrFail();
        $historyMenu->update(['is_sidebar' => false]);

        $migration = require database_path('migrations/2026_11_01_000001_register_kwitansi_history_menu_access.php');
        $migration->up();

        $historyMenu->refresh();
        $adminUser = User::factory()->create(['role_id' => $admin->id]);
        $operatorUser = User::factory()->create(['role_id' => $operator->id]);

        $this->assertTrue($historyMenu->is_sidebar);
        $this->assertTrue($adminUser->hasMenuPermission('kwitansi-history.index', 'view'));
        $this->assertTrue($adminUser->hasMenuPermission('kwitansi-history.index', 'export'));
        $this->assertFalse($adminUser->hasMenuPermission('kwitansi-history.index', 'edit'));
        $this->assertTrue($operatorUser->hasMenuPermission('kwitansi-history.index', 'view'));
        $this->assertFalse($operatorUser->hasMenuPermission('kwitansi-history.index', 'export'));
        $this->assertTrue(Menu::sidebarForUser($adminUser)->contains('id', $historyMenu->id));
    }
}
