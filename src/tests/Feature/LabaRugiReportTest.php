<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\User;
use App\Exports\LabaRugiExport;
use App\Services\LabaRugiReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabaRugiReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_laba_rugi_excludes_pajak_penghasilan_from_total_beban(): void
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

        $revenue = Coa::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Pendapatan Jasa',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $expense = Coa::create([
            'kode_akun' => '5101',
            'nama_akun' => 'Beban Operasional',
            'tipe' => 'Beban',
            'kategori' => 'Beban Operasional',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $tax = Coa::create([
            'kode_akun' => '5201',
            'nama_akun' => 'Pajak Penghasilan',
            'tipe' => 'Beban',
            'kategori' => 'Pajak Penghasilan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'JU-TEST-001',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Transaksi laba rugi',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'REF-001',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnal->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Setoran kas', 'debit' => 1000, 'kredit' => 0],
            ['coa_id' => $revenue->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 1000],
        ]);

        $jurnal2 = Jurnal::create([
            'no_jurnal' => 'JU-TEST-002',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Beban dan pajak',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'REF-002',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnal2->items()->createMany([
            ['coa_id' => $expense->id, 'deskripsi' => 'Beban operasional', 'debit' => 800, 'kredit' => 0],
            ['coa_id' => $tax->id, 'deskripsi' => 'Pajak Penghasilan', 'debit' => 200, 'kredit' => 0],
            ['coa_id' => $cash->id, 'deskripsi' => 'Pembayaran biaya', 'debit' => 0, 'kredit' => 1000],
        ]);

        $tanggalAwal = now()->toDateString();
        $tanggalAkhir = now()->toDateString();

        $report = (new LabaRugiReportService())->build($tanggalAwal, $tanggalAkhir);

        $this->assertSame(1000.0, $report['totalPendapatan']);
        $this->assertSame(800.0, $report['totalBeban']);
        $this->assertSame(200.0, $report['taksiranPajakPenghasilan']);
        $this->assertSame(200.0, $report['labaSebelumPajak']);
        $this->assertSame(0.0, $report['labaBersih']);
    }

    public function test_laporan_laba_rugi_detects_pajak_penghasilan_by_account_name(): void
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

        $revenue = Coa::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Pendapatan Jasa',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $tax = Coa::create([
            'kode_akun' => '5201',
            'nama_akun' => 'Taksiran Pajak Penghasilan',
            'tipe' => 'Beban',
            'kategori' => 'Pajak dan Asuransi',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'JU-TEST-004',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Transaksi pajak penghasilan',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'REF-004',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnal->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Setoran kas', 'debit' => 1000, 'kredit' => 0],
            ['coa_id' => $revenue->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 1000],
        ]);

        $jurnal2 = Jurnal::create([
            'no_jurnal' => 'JU-TEST-005',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Taksiran pajak',
            'total_debit' => 200,
            'total_kredit' => 200,
            'referensi' => 'REF-005',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnal2->items()->createMany([
            ['coa_id' => $tax->id, 'deskripsi' => 'Taksiran pajak', 'debit' => 200, 'kredit' => 0],
            ['coa_id' => $cash->id, 'deskripsi' => 'Pembayaran pajak', 'debit' => 0, 'kredit' => 200],
        ]);

        $report = (new LabaRugiReportService())->build(now()->toDateString(), now()->toDateString());

        $this->assertSame(1000.0, $report['totalPendapatan']);
        $this->assertSame(0.0, $report['totalBeban']);
        $this->assertSame(200.0, $report['taksiranPajakPenghasilan']);
        $this->assertSame(1000.0, $report['labaSebelumPajak']);
        $this->assertSame(800.0, $report['labaBersih']);
    }

    public function test_laporan_laba_rugi_view_shows_correct_section_order(): void
    {
        $user = User::factory()->create();
        $reportService = new LabaRugiReportService();

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

        $tax = Coa::create([
            'kode_akun' => '5201',
            'nama_akun' => 'Pajak Penghasilan',
            'tipe' => 'Beban',
            'kategori' => 'Pajak Penghasilan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $jurnal = Jurnal::create([
            'no_jurnal' => 'JU-TEST-003',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Transaksi laba rugi view',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'REF-003',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnal->items()->createMany([
            ['coa_id' => $pendapatan->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 1000],
            ['coa_id' => $beban->id, 'deskripsi' => 'Beban', 'debit' => 800, 'kredit' => 0],
            ['coa_id' => $tax->id, 'deskripsi' => 'Pajak', 'debit' => 200, 'kredit' => 0],
        ]);

        $response = $this->actingAs($user)
            ->get(route('laporan.laba_rugi', ['tanggal_awal' => now()->toDateString(), 'tanggal_akhir' => now()->toDateString()]));

        $response->assertOk();
        $response->assertSeeText('1,000.00');
        $response->assertSeeText('800.00');
        $response->assertSeeText('200.00');
        $response->assertSeeTextInOrder([
            'PENDAPATAN',
            'Total Pendapatan',
            'BEBAN',
            'Total Beban',
            'LABA SEBELUM PAJAK',
            'Taksiran Pajak Penghasilan',
            'LABA BERSIH',
        ]);
    }

    public function test_laporan_laba_rugi_monthly_period_shows_month_and_year_label(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get(route('laporan.laba_rugi', [
            'mode_periode' => 'bulanan',
            'month' => '07',
            'year' => '2026',
        ]));

        $response->assertOk();
        $response->assertSeeText('Periode: JULI 2026');
    }

    public function test_laporan_laba_rugi_keeps_october_through_december_month_selection(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        foreach ([
            '10' => ['2026-10-01', '2026-10-31', 'OKTOBER'],
            '11' => ['2026-11-01', '2026-11-30', 'NOVEMBER'],
            '12' => ['2026-12-01', '2026-12-31', 'DESEMBER'],
        ] as $month => [$tanggalAwal, $tanggalAkhir, $label]) {
            $response = $this->actingAs($user)->get(route('laporan.laba_rugi', [
                'mode_periode' => 'bulanan',
                'month' => $month,
                'year' => '2026',
            ]));

            $response->assertOk()
                ->assertViewHas('month', $month)
                ->assertViewHas('tanggalAwal', $tanggalAwal)
                ->assertViewHas('tanggalAkhir', $tanggalAkhir)
                ->assertSeeText("Periode: {$label} 2026")
                ->assertSee("value=\"{$month}\" selected", false);
        }
    }

    public function test_laporan_laba_rugi_keeps_explicit_date_range_when_month_and_year_are_also_sent(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->get(route('laporan.laba_rugi', [
            'mode_periode' => 'range',
            'tanggal_awal' => '2026-06-10',
            'tanggal_akhir' => '2026-07-12',
            'month' => '07',
            'year' => '2026',
        ]));

        $response->assertOk();
        $response->assertViewHas('mode_periode', 'range');
        $response->assertViewHas('tanggalAwal', '2026-06-10');
        $response->assertViewHas('tanggalAkhir', '2026-07-12');
        $response->assertSeeText('Periode: 10 June 2026 - 12 July 2026');
        $response->assertSee('value="range" selected', false);
    }

    public function test_laba_rugi_excel_export_uses_month_year_period_label(): void
    {
        $report = (new LabaRugiReportService())->build('2026-07-01', '2026-07-31');
        $report['periodeLabel'] = 'JULI 2026';

        $headings = (new LabaRugiExport($report))->headings();

        $this->assertSame('PT. LINTAS ANUGERAH SEJATI', $headings[0][0]);
        $this->assertSame('Periode: JULI 2026', $headings[2][0]);
    }
}
