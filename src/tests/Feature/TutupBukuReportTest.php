<?php

namespace Tests\Feature;

use App\Exports\TutupBukuExport;
use App\Models\AccountMapping;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\JurnalItemArchive;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use App\Models\YearClosing;
use App\Services\NeracaReportService;
use App\Services\TutupBukuReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutupBukuReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutup_buku_report_service_calculates_retained_earnings_projection(): void
    {
        $user = User::factory()->create();

        $cash = Coa::create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $pendapatan = Coa::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Pendapatan Jasa',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $beban = Coa::create([
            'kode_akun' => '5101',
            'nama_akun' => 'Beban Operasional',
            'tipe' => 'Beban',
            'kategori' => 'Beban Operasional',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $labaDitahan = Coa::create([
            'kode_akun' => '3201',
            'nama_akun' => 'Laba Ditahan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Laba Ditahan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        AccountMapping::create([
            'mapping_key' => 'retained_earnings',
            'account_id' => $labaDitahan->id,
        ]);

        $tanggalAwal = now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = now()->endOfMonth()->format('Y-m-d');
        $tanggalSebelumnya = now()->startOfMonth()->subDay()->format('Y-m-d');

        Jurnal::create([
            'no_jurnal' => 'JU-RET-001',
            'tanggal' => $tanggalSebelumnya,
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Saldo awal laba ditahan',
            'total_debit' => 500,
            'total_kredit' => 500,
            'referensi' => 'REF-RET-001',
            'created_by' => $user->id,
            'is_posted' => true,
        ])->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Saldo awal kas', 'debit' => 500, 'kredit' => 0],
            ['coa_id' => $labaDitahan->id, 'deskripsi' => 'Saldo awal laba ditahan', 'debit' => 0, 'kredit' => 500],
        ]);

        Jurnal::create([
            'no_jurnal' => 'JU-TB-001',
            'tanggal' => $tanggalAwal,
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Pendapatan dan beban periode',
            'total_debit' => 1200,
            'total_kredit' => 1200,
            'referensi' => 'REF-TB-001',
            'created_by' => $user->id,
            'is_posted' => true,
        ])->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Cash inflow', 'debit' => 1200, 'kredit' => 0],
            ['coa_id' => $pendapatan->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 1000],
            ['coa_id' => $beban->id, 'deskripsi' => 'Beban operasional', 'debit' => 200, 'kredit' => 0],
        ]);

        $report = (new TutupBukuReportService())->build($tanggalAwal, $tanggalAkhir);

        $this->assertSame(1000.0, $report['totalPendapatan']);
        $this->assertSame(200.0, $report['totalBeban']);
        $this->assertSame(800.0, $report['labaBersih']);
        $this->assertSame(500.0, $report['saldoAwalLabaDitahan']);
        $this->assertSame(1300.0, $report['saldoAkhirLabaDitahanProyeksi']);
    }

    public function test_tutup_buku_uses_laba_rugi_profit_loss_formula(): void
    {
        $user = User::factory()->create();

        $cash = Coa::create([
            'kode_akun' => '1102',
            'nama_akun' => 'Kas Formula',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $revenue = Coa::create([
            'kode_akun' => '4102',
            'nama_akun' => 'Pendapatan Formula',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $expense = Coa::create([
            'kode_akun' => '5102',
            'nama_akun' => 'Beban Formula',
            'tipe' => 'Beban',
            'kategori' => 'Beban Operasional',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $tax = Coa::create([
            'kode_akun' => '5202',
            'nama_akun' => 'Pajak Penghasilan Formula',
            'tipe' => 'Beban',
            'kategori' => 'Pajak Penghasilan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $date = now()->toDateString();
        Jurnal::create([
            'no_jurnal' => 'JU-TB-FORMULA',
            'tanggal' => $date,
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Formula laba rugi tutup buku',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'REF-TB-FORMULA',
            'created_by' => $user->id,
            'is_posted' => true,
        ])->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Kas', 'debit' => 1000, 'kredit' => 0],
            ['coa_id' => $revenue->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 1000],
        ]);

        Jurnal::create([
            'no_jurnal' => 'JU-TB-FORMULA-2',
            'tanggal' => $date,
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Formula beban tutup buku',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'REF-TB-FORMULA-2',
            'created_by' => $user->id,
            'is_posted' => true,
        ])->items()->createMany([
            ['coa_id' => $expense->id, 'deskripsi' => 'Beban', 'debit' => 800, 'kredit' => 0],
            ['coa_id' => $tax->id, 'deskripsi' => 'Pajak', 'debit' => 200, 'kredit' => 0],
            ['coa_id' => $cash->id, 'deskripsi' => 'Kas keluar', 'debit' => 0, 'kredit' => 1000],
        ]);

        $report = (new TutupBukuReportService())->build($date, $date);

        $this->assertSame(1000.0, $report['totalPendapatan']);
        $this->assertSame(800.0, $report['totalBeban']);
        $this->assertSame(200.0, $report['taksiranPajakPenghasilan']);
        $this->assertSame(200.0, $report['labaSebelumPajak']);
        $this->assertSame(0.0, $report['labaBersih']);
    }

    public function test_tutup_buku_view_displays_mapping_and_retained_earnings(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        $pendapatan = Coa::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Pendapatan Jasa',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $beban = Coa::create([
            'kode_akun' => '5101',
            'nama_akun' => 'Beban Operasional',
            'tipe' => 'Beban',
            'kategori' => 'Beban Operasional',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $labaDitahan = Coa::create([
            'kode_akun' => '3201',
            'nama_akun' => 'Laba Ditahan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Laba Ditahan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        AccountMapping::create([
            'mapping_key' => 'retained_earnings',
            'account_id' => $labaDitahan->id,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'JU-TB-002',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Transaksi tutup buku',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'REF-TB-002',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnal->items()->createMany([
            ['coa_id' => $pendapatan->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 1000],
            ['coa_id' => $beban->id, 'deskripsi' => 'Beban operasional', 'debit' => 1000, 'kredit' => 0],
        ]);

        $response = $this->actingAs($user)
            ->get(route('laporan.tutup_buku', [
                'tanggal_awal' => now()->toDateString(),
                'tanggal_akhir' => now()->toDateString(),
            ]));

        $response->assertOk();
        $response->assertSeeText('Account Mapping');
        $response->assertSeeText('Laba Ditahan');
        $response->assertSeeText('Saldo Awal Laba Ditahan');
        $response->assertSeeText('Laba Bersih');
    }

    public function test_tutup_buku_post_actions_require_post_permission(): void
    {
        [, $user] = $this->createTutupBukuUser([
            'can_view' => true,
            'can_post' => false,
        ]);

        $response = $this->actingAs($user)->get(route('laporan.tutup_buku', [
            'tanggal_awal' => '2026-01-01',
            'tanggal_akhir' => '2026-12-31',
        ]));

        $response->assertOk();
        $response->assertDontSeeText('Generate Jurnal Penutup');
        $response->assertDontSeeText('Tandai Tahun Tutup');
        $response->assertDontSeeText('Arsipkan Jurnal');
        $response->assertDontSeeText('Selesaikan Tutup Tahun');

        $postResponse = $this->actingAs($user)->post(route('laporan.tutup_buku.close_year'), [
            'tanggal_awal' => '2026-01-01',
            'tanggal_akhir' => '2026-12-31',
        ]);

        $postResponse->assertRedirect(route('dashboard'));
        $postResponse->assertSessionHas('error', 'Anda tidak memiliki hak akses untuk menu ini.');
        $this->assertSame(0, YearClosing::count());
    }

    public function test_laporan_dashboard_hides_tutup_buku_card_without_view_permission(): void
    {
        [, $user] = $this->createTutupBukuUser([
            'can_view' => false,
        ]);

        $response = $this->actingAs($user)->get(route('laporan.index'));

        $response->assertOk();
        $response->assertDontSeeText('Tutup Buku');
    }

    public function test_tutup_buku_page_defaults_to_full_calendar_year(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create(['role_id' => $adminRole->id]);
        $year = now()->year;

        $response = $this->actingAs($user)->get(route('laporan.tutup_buku'));

        $response->assertOk();
        $response->assertSee($year . '-01-01', false);
        $response->assertSee($year . '-12-31', false);
    }

    public function test_close_year_rejects_period_that_is_not_full_calendar_year(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($user)->post(route('laporan.tutup_buku.close_year'), [
            'tanggal_awal' => '2026-07-01',
            'tanggal_akhir' => '2026-07-31',
        ]);

        $response->assertRedirect(route('laporan.tutup_buku', [
            'tanggal_awal' => '2026-07-01',
            'tanggal_akhir' => '2026-07-31',
        ], false));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('year_closings', ['year' => '2026']);
    }

    public function test_close_year_rejects_when_unposted_journals_exist(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        Jurnal::create([
            'no_jurnal' => 'JU-DRAFT-2026',
            'tanggal' => '2026-06-30',
            'status' => Jurnal::STATUS_DRAFT,
            'keterangan' => 'Draft belum selesai',
            'total_debit' => 0,
            'total_kredit' => 0,
            'referensi' => 'DRAFT',
            'created_by' => $user->id,
            'is_posted' => false,
        ]);

        $response = $this->actingAs($user)->post(route('laporan.tutup_buku.close_year'), [
            'tanggal_awal' => '2026-01-01',
            'tanggal_akhir' => '2026-12-31',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('year_closings', ['year' => '2026']);
    }

    public function test_close_year_creates_balanced_closing_journal_archives_and_balances_neraca(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        $cash = Coa::create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $pendapatan = Coa::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Pendapatan Jasa',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $beban = Coa::create([
            'kode_akun' => '5101',
            'nama_akun' => 'Beban Operasional',
            'tipe' => 'Beban',
            'kategori' => 'Beban Operasional',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $labaDitahan = Coa::create([
            'kode_akun' => '3201',
            'nama_akun' => 'Laba Ditahan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Laba Ditahan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $labaTahunBerjalan = Coa::create([
            'kode_akun' => '3202',
            'nama_akun' => 'Laba Tahun Berjalan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Laba Tahun Berjalan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        AccountMapping::create(['mapping_key' => 'retained_earnings', 'account_id' => $labaDitahan->id]);
        AccountMapping::create(['mapping_key' => 'current_year_profit', 'account_id' => $labaTahunBerjalan->id]);

        Jurnal::create([
            'no_jurnal' => 'JU-YE-2026',
            'tanggal' => '2026-06-30',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Transaksi tahun berjalan',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'YE-2026',
            'created_by' => $user->id,
            'is_posted' => true,
        ])->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Kas masuk', 'debit' => 800, 'kredit' => 0],
            ['coa_id' => $pendapatan->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 1000],
            ['coa_id' => $beban->id, 'deskripsi' => 'Beban', 'debit' => 200, 'kredit' => 0],
        ]);

        $response = $this->actingAs($user)->post(route('laporan.tutup_buku.close_year'), [
            'tanggal_awal' => '2026-01-01',
            'tanggal_akhir' => '2026-12-31',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('year_closings', ['year' => '2026']);
        $this->assertSame(0, JurnalItem::count());
        $this->assertGreaterThan(0, JurnalItemArchive::count());

        $closingJournal = Jurnal::where('referensi', 'AUTO-CLOSING')->firstOrFail();
        $this->assertSame((float) $closingJournal->total_debit, (float) $closingJournal->total_kredit);

        $neraca = app(NeracaReportService::class)->build([
            'from_date' => '2026-01-01',
            'to_date' => '2026-12-31',
        ]);

        $this->assertTrue($neraca['is_balance']);
        $this->assertSame(0.0, $neraca['laba_berjalan']);
    }

    public function test_tutup_buku_report_reads_archived_retained_earnings_as_opening_balance(): void
    {
        $user = User::factory()->create();

        $labaDitahan = Coa::create([
            'kode_akun' => '3201',
            'nama_akun' => 'Laba Ditahan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Laba Ditahan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        AccountMapping::create([
            'mapping_key' => 'retained_earnings',
            'account_id' => $labaDitahan->id,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'TJ-ARCHIVE-2026',
            'tanggal' => '2026-12-31',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Jurnal Tutup Otomatis',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'AUTO-CLOSING',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $labaDitahan->id,
            'deskripsi' => 'Laba ditahan archive',
            'debit' => 0,
            'kredit' => 1000,
        ]);

        $report = (new TutupBukuReportService())->build('2027-01-01', '2027-12-31');

        $this->assertSame(1000.0, $report['saldoAwalLabaDitahan']);
    }

    public function test_tutup_buku_export_headings_include_period(): void
    {
        $report = [
            'tanggalAwal' => now()->startOfMonth()->format('Y-m-d'),
            'tanggalAkhir' => now()->endOfMonth()->format('Y-m-d'),
            'pendapatanRows' => [],
            'bebanRows' => [],
            'totalPendapatan' => 0,
            'totalBeban' => 0,
            'labaBersih' => 0,
            'mappingAccounts' => [],
            'saldoAwalLabaDitahan' => 0,
            'saldoAkhirLabaDitahanProyeksi' => 0,
        ];

        $export = new TutupBukuExport($report);

        $headings = $export->headings();

        $this->assertSame('LAPORAN TUTUP BUKU', $headings[0][0]);
        $this->assertStringContainsString('Periode:', $headings[1][0]);
        $this->assertSame(['Kode Akun', 'Nama Akun', 'Jenis', 'Jumlah'], $headings[2]);
    }

    private function createTutupBukuUser(array $permissions): array
    {
        $role = Role::create([
            'name' => 'Year Close Permission Role ' . uniqid(),
        ]);

        $menu = Menu::updateOrCreate(
            ['route_name' => 'laporan.tutup_buku'],
            [
                'name' => 'Tutup Buku',
                'section' => 'Laporan',
                'icon' => 'bi bi-box-seam',
                'sort_order' => 6,
                'is_sidebar' => true,
            ]
        );

        $role->menus()->attach($menu->id, array_merge([
            'can_view' => false,
            'can_create' => false,
            'can_edit' => false,
            'can_delete' => false,
            'can_post' => false,
            'can_void' => false,
            'can_export' => false,
        ], $permissions));

        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        return [$role, $user];
    }
}
