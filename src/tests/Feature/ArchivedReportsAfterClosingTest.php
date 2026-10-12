<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItemArchive;
use App\Models\User;
use App\Services\JournalQueryService;
use App\Services\LabaRugiReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivedReportsAfterClosingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Coa $cash;
    private Coa $revenue;
    private Coa $expense;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => true]);
        $this->cash = $this->createCoa('01010101', 'Kas Arsip', 'Aset', 'Kas', 'Debit');
        $this->revenue = $this->createCoa('04010101', 'Pendapatan Arsip', 'Pendapatan', 'Pendapatan Operasional', 'Kredit');
        $this->expense = $this->createCoa('05010101', 'Beban Arsip', 'Beban', 'Beban Operasional', 'Debit');

        $this->createArchivedPostedJournal('JU-ARCH-001', [
            ['coa_id' => $this->cash->id, 'deskripsi' => 'Kas dari arsip', 'debit' => 1000, 'kredit' => 0],
            ['coa_id' => $this->revenue->id, 'deskripsi' => 'Pendapatan dari arsip', 'debit' => 0, 'kredit' => 1000],
        ]);

        $this->createArchivedPostedJournal('JU-ARCH-002', [
            ['coa_id' => $this->expense->id, 'deskripsi' => 'Beban dari arsip', 'debit' => 400, 'kredit' => 0],
            ['coa_id' => $this->cash->id, 'deskripsi' => 'Kas keluar dari arsip', 'debit' => 0, 'kredit' => 400],
        ]);
    }

    public function test_laba_rugi_reads_archived_journal_items_after_year_close(): void
    {
        $report = (new LabaRugiReportService())->build('2026-01-01', '2026-12-31');

        $this->assertSame(1000.0, $report['totalPendapatan']);
        $this->assertSame(400.0, $report['totalBeban']);
        $this->assertSame(600.0, $report['labaBersih']);
    }

    public function test_laba_rugi_ignores_archived_closing_journal_for_closed_year(): void
    {
        $closingJournal = Jurnal::create([
            'no_jurnal' => 'TJ-20261231-0001',
            'tanggal' => '2026-12-31',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Jurnal Tutup Otomatis',
            'total_debit' => 1400,
            'total_kredit' => 1400,
            'referensi' => 'AUTO-CLOSING',
            'created_by' => $this->user->id,
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $closingJournal->id,
            'coa_id' => $this->revenue->id,
            'deskripsi' => 'Penutupan akun pendapatan',
            'debit' => 1000,
            'kredit' => 0,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $closingJournal->id,
            'coa_id' => $this->expense->id,
            'deskripsi' => 'Penutupan akun beban',
            'debit' => 0,
            'kredit' => 400,
        ]);

        $report = (new LabaRugiReportService())->build('2026-01-01', '2026-12-31');

        $this->assertSame(1000.0, $report['totalPendapatan']);
        $this->assertSame(400.0, $report['totalBeban']);
        $this->assertSame(600.0, $report['labaBersih']);
    }

    public function test_laba_rugi_includes_archived_journals_with_empty_description(): void
    {
        $journal = Jurnal::create([
            'no_jurnal' => 'JU-ARCH-NULL-KET',
            'tanggal' => '2026-07-06',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => null,
            'total_debit' => 250,
            'total_kredit' => 250,
            'referensi' => null,
            'created_by' => $this->user->id,
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $journal->id,
            'coa_id' => $this->revenue->id,
            'deskripsi' => 'Pendapatan arsip tanpa keterangan jurnal',
            'debit' => 0,
            'kredit' => 250,
        ]);

        $report = (new LabaRugiReportService())->build('2026-01-01', '2026-12-31');

        $this->assertSame(1250.0, $report['totalPendapatan']);
        $this->assertSame(850.0, $report['labaBersih']);
    }

    public function test_laba_rugi_page_can_show_closed_year_with_yearly_mode(): void
    {
        $response = $this->actingAs($this->user)->get(route('laporan.laba_rugi', [
            'mode_periode' => 'tahunan',
            'year' => '2026',
        ]));

        $response->assertOk();
        $response->assertSee('2026-01-01', false);
        $response->assertSee('2026-12-31', false);
        $response->assertSee('Pendapatan Arsip');
        $response->assertSee('1,000.00');
    }

    public function test_neraca_saldo_page_reads_archived_journal_items_after_year_close(): void
    {
        $totals = app(JournalQueryService::class)->getSumByCoaId('2026-01-01', '2026-12-31', $this->revenue->id);
        $this->assertSame(1000.0, $totals['kredit']);

        $response = $this->actingAs($this->user)->get(route('laporan.neraca_saldo', [
            'tanggal_awal' => '2026-01-01',
            'tanggal_akhir' => '2026-12-31',
        ]));

        $response->assertOk();
        $response->assertSee('Pendapatan Arsip');
        $response->assertSee('1,000.00');
    }

    public function test_buku_besar_page_reads_archived_journal_items_after_year_close(): void
    {
        $items = app(JournalQueryService::class)
            ->getSmartQuery('2026-01-01', '2026-12-31')
            ->where('coa_id', $this->revenue->id)
            ->get();
        $this->assertCount(1, $items);

        $response = $this->actingAs($this->user)->get(route('laporan.buku_besar', [
            'coa_id' => $this->revenue->id,
            'tanggal_awal' => '2026-01-01',
            'tanggal_akhir' => '2026-12-31',
        ]));

        $response->assertOk();
        $response->assertSee('JU-ARCH-001');
        $response->assertSee('Pendapatan dari arsip');
    }

    public function test_jurnal_umum_page_reads_archived_journal_items_after_year_close(): void
    {
        $this->assertSame(2, Jurnal::whereHas('archivedItems.coa', fn($query) => $query->posting())->count());

        $response = $this->actingAs($this->user)->get(route('laporan.jurnal_umum', [
            'tanggal_awal' => '2026-01-01',
            'tanggal_akhir' => '2026-12-31',
        ]));

        $response->assertOk();
        $response->assertSee('JU-ARCH-001');
        $response->assertSee('Pendapatan Arsip');
    }

    public function test_arus_kas_page_reads_archived_journal_items_after_year_close(): void
    {
        $response = $this->actingAs($this->user)->get(route('laporan.arus_kas', [
            'tanggal_awal' => '2026-01-01',
            'tanggal_akhir' => '2026-12-31',
        ]));

        $response->assertOk();
        $response->assertSee('Kas Arsip');
        $response->assertSee('1,000.00');
    }

    private function createCoa(string $code, string $name, string $type, string $category, string $normalBalance): Coa
    {
        return Coa::create([
            'kode_akun' => $code,
            'nama_akun' => $name,
            'tipe' => $type,
            'kategori' => $category,
            'level' => 'Akun Posting',
            'saldo_normal' => $normalBalance,
            'is_active' => true,
        ]);
    }

    private function createArchivedPostedJournal(string $number, array $items): Jurnal
    {
        $journal = Jurnal::create([
            'no_jurnal' => $number,
            'tanggal' => '2026-12-31',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Jurnal arsip test',
            'total_debit' => collect($items)->sum('debit'),
            'total_kredit' => collect($items)->sum('kredit'),
            'referensi' => 'ARCHIVE-TEST',
            'created_by' => $this->user->id,
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        foreach ($items as $item) {
            JurnalItemArchive::create($item + ['jurnal_id' => $journal->id]);
        }

        return $journal;
    }
}
