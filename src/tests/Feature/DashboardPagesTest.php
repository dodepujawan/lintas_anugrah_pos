<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_compact_expandable_financial_summary(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Total Akun')
            ->assertSeeText('Total Jurnal')
            ->assertSeeText('Aktivitas Jurnal Terbaru')
            ->assertSee('dashboard-topbar-group', false);
    }

    public function test_dashboard_shows_monthly_profit_loss_not_all_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-21'));

        $user = $this->adminUser();
        $menu = Menu::create([
            'name' => 'Laporan Laba Rugi',
            'route_name' => 'laporan.laba_rugi',
            'section' => 'Laporan',
            'icon' => 'bi bi-bar-chart-line',
            'sort_order' => 3,
            'is_active' => true,
        ]);
        $user->role->menus()->attach($menu->id, [
            'can_view' => true,
            'can_create' => false,
            'can_edit' => false,
            'can_delete' => false,
            'can_post' => false,
            'can_void' => false,
            'can_export' => false,
        ]);

        $cash = Coa::create([
            'kode_akun' => '11010001',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);
        $revenue = Coa::create([
            'kode_akun' => '41010001',
            'nama_akun' => 'Pendapatan Jasa',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $prior = Jurnal::create([
            'no_jurnal' => 'JU-PRIOR-001',
            'tanggal' => '2026-07-15',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Pendapatan bulan lalu',
            'total_debit' => 5000000,
            'total_kredit' => 5000000,
            'referensi' => 'REF-PRIOR',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);
        $prior->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Kas', 'debit' => 5000000, 'kredit' => 0],
            ['coa_id' => $revenue->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 5000000],
        ]);

        $current = Jurnal::create([
            'no_jurnal' => 'JU-CURR-001',
            'tanggal' => '2026-08-10',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Pendapatan bulan ini',
            'total_debit' => 1200000,
            'total_kredit' => 1200000,
            'referensi' => 'REF-CURR',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);
        $current->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Kas', 'debit' => 1200000, 'kredit' => 0],
            ['coa_id' => $revenue->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 1200000],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeText('Laba Rugi Bulan Ini');
        $response->assertSeeText('Laba/rugi bersih setelah pajak');
        $response->assertViewHas('labaBersih', 1200000.0);

        Carbon::setTestNow();
    }

    public function test_main_pages_render_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/coa')
            ->assertStatus(200)
            ->assertSee('Chart of Account');

        $this->actingAs($user)
            ->get('/jurnal')
            ->assertStatus(200)
            ->assertSee('Daftar Jurnal');

        $this->actingAs($user)
            ->get('/laporan')
            ->assertStatus(200)
            ->assertSee('Laporan Keuangan');
    }

    public function test_coa_page_restores_default_accounts_when_database_is_empty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/coa')
            ->assertStatus(200)
            ->assertSee('Kas');
    }

    public function test_default_coa_codes_are_stored_as_8_digit_numeric_strings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/coa')
            ->assertStatus(200);

        $this->assertNotEmpty(Coa::all());
        $this->assertTrue(Coa::all()->every(fn (Coa $coa) => preg_match('/^[0-9]{8}$/', $coa->kode_akun) === 1));
    }

    public function test_laporan_pages_render_without_route_errors(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get('/laporan/jurnal-umum')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/laporan/buku-besar')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/laporan/neraca')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/laporan/laba-rugi')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/laporan/perubahan-ekuitas')
            ->assertStatus(200)
            ->assertSee('Laporan Perubahan Ekuitas');
    }

    public function test_neraca_saldo_keeps_explicit_date_range_when_month_and_year_are_also_sent(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->get(route('laporan.neraca_saldo', [
            'mode_periode' => 'range',
            'tanggal_awal' => '2026-06-10',
            'tanggal_akhir' => '2026-07-12',
            'month' => '07',
            'year' => '2026',
        ]));

        $response->assertOk();
        $response->assertViewHas('modePeriode', 'range');
        $response->assertViewHas('tanggalAwal', '2026-06-10');
        $response->assertViewHas('tanggalAkhir', '2026-07-12');
        $response->assertViewHas('periodeLabel', '10 June 2026 - 12 July 2026');
        $response->assertSee('value="range" selected', false);
    }

    public function test_neraca_saldo_monthly_header_uses_selected_month_and_year(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->get(route('laporan.neraca_saldo', [
            'mode_periode' => 'bulanan',
            'month' => '07',
            'year' => '2026',
        ]));

        $response->assertOk();
        $response->assertViewHas('periodeLabel', 'JULI 2026');
        $response->assertSeeText('Periode: JULI 2026');
    }

    public function test_neraca_saldo_keeps_october_through_december_month_selection(): void
    {
        $user = $this->adminUser();

        foreach ([
            '10' => ['2026-10-01', '2026-10-31'],
            '11' => ['2026-11-01', '2026-11-30'],
            '12' => ['2026-12-01', '2026-12-31'],
        ] as $month => [$tanggalAwal, $tanggalAkhir]) {
            $response = $this->actingAs($user)->get(route('laporan.neraca_saldo', [
                'mode_periode' => 'bulanan',
                'month' => $month,
                'year' => '2026',
            ]));

            $response->assertOk()
                ->assertViewHas('month', $month)
                ->assertViewHas('tanggalAwal', $tanggalAwal)
                ->assertViewHas('tanggalAkhir', $tanggalAkhir)
                ->assertSeeText('Posisi per ' . \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y'))
                ->assertSee("value=\"{$month}\" selected", false);
        }
    }

    public function test_validation_dashboard_renders(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get('/validation-dashboard')
            ->assertStatus(200)
            ->assertSee('Validasi Data');
    }

    public function test_import_data_template_page_renders(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('import-data.index'))
            ->assertStatus(200)
            ->assertSee('Template Import Penjualan')
            ->assertSee('Template Saldo Hutang');
    }

    public function test_import_data_template_can_be_downloaded(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('import-data.template', 'penjualan'))
            ->assertStatus(200)
            ->assertHeader('content-disposition');
    }

    public function test_generated_journal_number_has_no_separator(): void
    {
        Carbon::setTestNow('2026-07-09 10:00:00');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('jurnal.create'))
            ->assertStatus(200)
            ->assertSee('JU202607000001');

        Carbon::setTestNow();
    }

    private function adminUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'Administrator'], ['description' => 'Access penuh ke seluruh modul.']);

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_edit_draft_journal_page_renders_account_options(): void
    {
        $user = User::factory()->create();
        $coa = Coa::create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva Lancar',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'JU-TEST-001',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_DRAFT,
            'keterangan' => 'Draft test',
            'total_debit' => 0,
            'total_kredit' => 0,
            'referensi' => 'REF-001',
            'created_by' => $user->id,
            'is_posted' => false,
        ]);

        $jurnal->items()->create([
            'coa_id' => $coa->id,
            'deskripsi' => 'Awal',
            'debit' => 1000,
            'kredit' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('jurnal.edit', $jurnal))
            ->assertStatus(200)
            ->assertSee('Tambah Baris')
            ->assertSee('Ketik kode/nama akun')
            ->assertSee('Cari Chart of Account')
            ->assertSee('journal-edit-header')
            ->assertSee('journal-edit-items')
            ->assertSee('sticky-action-bar')
            ->assertSee('journal-editor-page')
            ->assertSee('overflow: hidden')
            ->assertSee('position: fixed')
            ->assertSee('bottom: 16px')
            ->assertSee($coa->kode_akun.' - '.$coa->nama_akun);
    }

    public function test_adjusting_journal_and_opening_balance_use_coa_autocomplete(): void
    {
        $user = $this->adminUser();

        foreach ([
            'jurnal-penyesuaian.create' => 'Hapus item jurnal penyesuaian',
            'saldo-awal.create' => 'Hapus item saldo awal',
        ] as $route => $confirmation) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertSee('Ketik kode/nama akun')
                ->assertSee('Cari Chart of Account')
                ->assertSee('coa-inline-results')
                ->assertSee('locked-editor-shell')
                ->assertSee('locked-editor-header')
                ->assertSee('locked-items-panel')
                ->assertSee('position: fixed')
                ->assertSee('grid-template-columns')
                ->assertSee('min-width: 0 !important')
                ->assertSee('width: calc(100vw - 288px) !important')
                ->assertSee($confirmation);
        }
    }

    public function test_general_journal_keeps_header_visible_and_scrolls_items(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('jurnal.create'))
            ->assertOk()
            ->assertSee('journal-header-fields')
            ->assertSee('journal-items-card')
            ->assertSee('item-table-wrapper')
            ->assertSee('position: fixed')
            ->assertSee('Hapus item jurnal');
    }
}
