<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItemArchive;
use App\Models\User;
use App\Exports\NeracaExport;
use App\Services\NeracaReportService;
use Database\Seeders\CoaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeracaReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_neraca_renders_recursive_coa_tree_with_subtotals(): void
    {
        $user = User::factory()->create([
            'is_admin' => true,
        ]);

        $assetHeader = Coa::create([
            'kode_akun' => '1000',
            'nama_akun' => 'Aset',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva',
            'level' => 'Header',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $currentAssets = Coa::create([
            'kode_akun' => '1100',
            'nama_akun' => 'Aktiva Lancar',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva',
            'level' => 'Sub Header',
            'parent_id' => $assetHeader->id,
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $cash = Coa::create([
            'kode_akun' => '1110',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'parent_id' => $currentAssets->id,
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'JU-TEST-001',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Saldo awal',
            'total_debit' => 1500000,
            'total_kredit' => 0,
            'referensi' => 'REF-001',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnal->items()->create([
            'coa_id' => $cash->id,
            'deskripsi' => 'Setoran awal',
            'debit' => 1500000,
            'kredit' => 0,
        ]);

        $response = $this->actingAs($user)
            ->get(route('laporan.neraca', ['tanggal' => now()->toDateString()]));

        $response->assertOk();
        $response->assertSee('Aktiva Lancar');
        $response->assertSee('Kas');
        $response->assertDontSee('Subtotal Aktiva Lancar');
    }

    public function test_laporan_neraca_can_filter_only_accounts_with_balance(): void
    {
        $user = User::factory()->create([
            'is_admin' => true,
        ]);

        $header = Coa::create([
            'kode_akun' => '2000',
            'nama_akun' => 'Kewajiban',
            'tipe' => 'Kewajiban',
            'kategori' => 'Utang',
            'level' => 'Header',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $subHeader = Coa::create([
            'kode_akun' => '2100',
            'nama_akun' => 'Utang Lancar',
            'tipe' => 'Kewajiban',
            'kategori' => 'Utang',
            'level' => 'Sub Header',
            'parent_id' => $header->id,
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $posting = Coa::create([
            'kode_akun' => '2110',
            'nama_akun' => 'Utang Dagang',
            'tipe' => 'Kewajiban',
            'kategori' => 'Utang',
            'level' => 'Akun Posting',
            'parent_id' => $subHeader->id,
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $zeroPosting = Coa::create([
            'kode_akun' => '2120',
            'nama_akun' => 'Utang Lainnya',
            'tipe' => 'Kewajiban',
            'kategori' => 'Utang',
            'level' => 'Akun Posting',
            'parent_id' => $subHeader->id,
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'JU-TEST-002',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Utang',
            'total_debit' => 0,
            'total_kredit' => 500000,
            'referensi' => 'REF-002',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnal->items()->create([
            'coa_id' => $posting->id,
            'deskripsi' => 'Pembelian',
            'debit' => 0,
            'kredit' => 500000,
        ]);

        $response = $this->actingAs($user)
            ->get(route('laporan.neraca', ['tanggal' => now()->toDateString(), 'scope' => 'saldo', 'show_zero' => false]));

        $response->assertOk();
        $response->assertSee('Utang Dagang');
        $response->assertDontSee('Utang Lainnya');
    }

    public function test_laporan_neraca_includes_archived_journal_items_after_year_close(): void
    {
        $user = User::factory()->create();

        $assetHeader = Coa::create([
            'kode_akun' => '1000',
            'nama_akun' => 'Aset',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva',
            'level' => 'Header',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $cash = Coa::create([
            'kode_akun' => '1110',
            'nama_akun' => 'Kas Arsip',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'parent_id' => $assetHeader->id,
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'JU-ARCHIVE-2026',
            'tanggal' => '2026-06-30',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Jurnal yang sudah diarsipkan',
            'total_debit' => 2500000,
            'total_kredit' => 0,
            'referensi' => 'ARCHIVE-2026',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $cash->id,
            'deskripsi' => 'Saldo kas di archive',
            'debit' => 2500000,
            'kredit' => 0,
        ]);

        $report = app(NeracaReportService::class)->build([
            'from_date' => '2026-01-01',
            'to_date' => '2026-12-31',
        ]);

        $this->assertSame(2500000.0, $report['total_asset']);
        $this->assertSame(2500000.0, $report['asset_tree'][0]['subtotal']);
        $this->assertSame(2500000.0, $report['asset_tree'][0]['children'][0]['balance']);
    }

    public function test_laporan_neraca_month_year_period_shows_month_and_year_label(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get(route('laporan.neraca', [
            'period_type' => 'month_year',
            'month' => '07',
            'year' => '2026',
        ]));

        $response->assertOk();
        $response->assertSeeText('Periode: JULI 2026');
    }

    public function test_laporan_neraca_month_year_label_works_when_only_month_and_year_are_sent(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get(route('laporan.neraca', [
            'month' => '07',
            'year' => '2026',
        ]));

        $response->assertOk();
        $response->assertSeeText('Periode: JULI 2026');
    }

    public function test_laporan_neraca_keeps_october_through_december_month_selection(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        foreach ([
            '10' => ['2026-10-01', '2026-10-31', 'OKTOBER'],
            '11' => ['2026-11-01', '2026-11-30', 'NOVEMBER'],
            '12' => ['2026-12-01', '2026-12-31', 'DESEMBER'],
        ] as $month => [$fromDate, $toDate, $label]) {
            $response = $this->actingAs($user)->get(route('laporan.neraca', [
                'period_type' => 'month_year',
                'month' => $month,
                'year' => '2026',
            ]));

            $response->assertOk()
                ->assertViewHas('month', $month)
                ->assertViewHas('from_date', $fromDate)
                ->assertViewHas('to_date', $toDate)
                ->assertSeeText("Periode: {$label} 2026")
                ->assertSee("value=\"{$month}\" selected", false);
        }
    }

    public function test_laporan_neraca_keeps_explicit_date_range_when_month_and_year_are_also_sent(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get(route('laporan.neraca', [
            'period_type' => 'date_range',
            'from_date' => '2026-06-10',
            'to_date' => '2026-07-12',
            'month' => '07',
            'year' => '2026',
        ]));

        $response->assertOk();
        $response->assertViewHas('period_type', 'date_range');
        $response->assertViewHas('from_date', '2026-06-10');
        $response->assertViewHas('to_date', '2026-07-12');
        $response->assertSeeText('Periode: 10-06-2026 s/d 12-07-2026');
        $response->assertSee('value="date_range" selected', false);
    }

    public function test_neraca_excel_export_uses_month_year_period_label(): void
    {
        $report = app(NeracaReportService::class)->build([
            'period_type' => 'month_year',
            'month' => '07',
            'year' => '2026',
        ]);

        $rows = (new NeracaExport($report))->collection();

        $this->assertSame('Periode: JULI 2026', $rows[2][0]);
    }

    public function test_laporan_neraca_stays_balanced_after_profit_is_closed_to_retained_earnings(): void
    {
        $user = User::factory()->create();

        $cash = Coa::create([
            'kode_akun' => '1110',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $revenue = Coa::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Pendapatan Jasa',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $retainedEarnings = Coa::create([
            'kode_akun' => '3201',
            'nama_akun' => 'Laba Ditahan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Laba Ditahan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $currentYearProfit = Coa::create([
            'kode_akun' => '3202',
            'nama_akun' => 'Laba Tahun Berjalan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Laba Tahun Berjalan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $salesJournal = Jurnal::create([
            'no_jurnal' => 'JU-PROFIT-2026',
            'tanggal' => '2026-06-30',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Pendapatan periode',
            'total_debit' => 1000000,
            'total_kredit' => 1000000,
            'referensi' => 'PROFIT-2026',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $salesJournal->id,
            'coa_id' => $cash->id,
            'deskripsi' => 'Kas dari pendapatan',
            'debit' => 1000000,
            'kredit' => 0,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $salesJournal->id,
            'coa_id' => $revenue->id,
            'deskripsi' => 'Pendapatan jasa',
            'debit' => 0,
            'kredit' => 1000000,
        ]);

        $closingJournal = Jurnal::create([
            'no_jurnal' => 'TJ-PROFIT-2026',
            'tanggal' => '2026-12-31',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Jurnal Tutup Otomatis',
            'total_debit' => 2000000,
            'total_kredit' => 2000000,
            'referensi' => 'AUTO-CLOSING',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $closingJournal->id,
            'coa_id' => $revenue->id,
            'deskripsi' => 'Penutupan akun pendapatan',
            'debit' => 1000000,
            'kredit' => 0,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $closingJournal->id,
            'coa_id' => $currentYearProfit->id,
            'deskripsi' => 'Penutupan akun pendapatan ke laba tahun berjalan',
            'debit' => 0,
            'kredit' => 1000000,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $closingJournal->id,
            'coa_id' => $currentYearProfit->id,
            'deskripsi' => 'Penutupan saldo laba ke laba ditahan',
            'debit' => 1000000,
            'kredit' => 0,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $closingJournal->id,
            'coa_id' => $retainedEarnings->id,
            'deskripsi' => 'Penutupan laba tahun berjalan ke laba ditahan',
            'debit' => 0,
            'kredit' => 1000000,
        ]);

        $report = app(NeracaReportService::class)->build([
            'from_date' => '2026-01-01',
            'to_date' => '2026-12-31',
        ]);

        $this->assertSame(0.0, $report['laba_berjalan']);
        $this->assertSame(1000000.0, $report['total_asset']);
        $this->assertSame(1000000.0, $report['total_ekuitas']);
        $this->assertSame(1000000.0, $report['total_pasiva']);
        $this->assertTrue($report['is_balance']);
    }

    public function test_neraca_contra_asset_and_prive_reduce_section_totals(): void
    {
        $user = User::factory()->create();

        $cash = Coa::create([
            'kode_akun' => '1110',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $vehicle = Coa::create([
            'kode_akun' => '1501',
            'nama_akun' => 'Kendaraan',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva Tetap',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $accumDep = Coa::create([
            'kode_akun' => '1502',
            'nama_akun' => 'Akumulasi Penyusutan Kendaraan',
            'tipe' => 'Aset',
            'kategori' => 'Akumulasi Penyusutan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $modal = Coa::create([
            'kode_akun' => '3101',
            'nama_akun' => 'Modal',
            'tipe' => 'Ekuitas',
            'kategori' => 'Modal',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $prive = Coa::create([
            'kode_akun' => '3102',
            'nama_akun' => 'Prive',
            'tipe' => 'Ekuitas',
            'kategori' => 'Prive',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'JU-CONTRA-001',
            'tanggal' => '2026-08-01',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Setup kontra aset dan prive',
            'total_debit' => 17000000,
            'total_kredit' => 17000000,
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        // D Kas 5 + Kendaraan 10 + Prive 2 = 17
        // C Modal 15 + Akumulasi 2 = 17
        // Net aset 5+10-2=13, net ekuitas 15-2=13
        $jurnal->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Kas', 'debit' => 5000000, 'kredit' => 0],
            ['coa_id' => $vehicle->id, 'deskripsi' => 'Kendaraan', 'debit' => 10000000, 'kredit' => 0],
            ['coa_id' => $prive->id, 'deskripsi' => 'Prive', 'debit' => 2000000, 'kredit' => 0],
            ['coa_id' => $modal->id, 'deskripsi' => 'Modal', 'debit' => 0, 'kredit' => 15000000],
            ['coa_id' => $accumDep->id, 'deskripsi' => 'Akumulasi', 'debit' => 0, 'kredit' => 2000000],
        ]);

        $report = app(NeracaReportService::class)->build([
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-31',
        ]);

        $this->assertSame(13000000.0, $report['total_asset']);
        $this->assertSame(13000000.0, $report['total_ekuitas']);
        $this->assertSame(13000000.0, $report['total_pasiva']);
        $this->assertTrue($report['is_balance']);

        $accum = collect($report['asset_tree'])->first(
            fn ($n) => str_contains(strtolower($n['coa']->nama_akun), 'akumulasi penyusutan')
        );
        $priveNode = collect($report['equity_tree'])->first(
            fn ($n) => strtolower($n['coa']->nama_akun) === 'prive'
        );

        $this->assertNotNull($accum);
        $this->assertNotNull($priveNode);
        $this->assertSame(-2000000.0, (float) $accum['balance']);
        $this->assertSame(-2000000.0, (float) $priveNode['balance']);
    }

    public function test_seeded_laba_ditahan_uses_credit_normal_balance(): void
    {
        $this->seed(CoaSeeder::class);

        $this->assertSame('Kredit', Coa::where('nama_akun', 'Laba Ditahan')->value('saldo_normal'));
        $this->assertSame('Kredit', Coa::where('nama_akun', 'Modal')->value('saldo_normal'));
        $this->assertSame('Debit', Coa::where('nama_akun', 'Prive')->value('saldo_normal'));
    }
}
