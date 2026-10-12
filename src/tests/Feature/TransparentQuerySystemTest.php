<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\JurnalItemArchive;
use App\Services\JournalQueryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransparentQuerySystemTest extends TestCase
{
    use RefreshDatabase;

    private JournalQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new JournalQueryService();
    }

    /**
     * Test: Smart query tetap membaca gabungan aktif dan arsip untuk data terbaru
     */
    public function test_smart_query_within_active_range_uses_archive_safe_query()
    {
        // Setup data
        $coa = Coa::factory()->create(['tipe' => 'Aset']);
        $recentJurnal = Jurnal::factory()->create([
            'tanggal' => now()->subMonths(6),
            'is_posted' => true,
        ]);
        $recentItem = JurnalItem::factory()->create([
            'jurnal_id' => $recentJurnal->id,
            'coa_id' => $coa->id,
            'debit' => 100,
            'kredit' => 0,
        ]);

        // Test
        $startDate = now()->subMonths(12)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $info = $this->service->getQueryInfo($startDate, $endDate);

        $this->assertFalse($info['is_within_active_range']);
        $this->assertEquals(['jurnal_items', 'jurnal_items_archives'], $info['tables_used']);

        // Verify query returns correct data
        $items = $this->service->getSmartQuery($startDate, $endDate)
            ->where('coa_id', $coa->id)
            ->get();

        $this->assertCount(1, $items);
        $this->assertEquals(100, $items->first()->debit);
    }

    /**
     * Test: Smart query menggunakan UNION untuk data melampaui 2 tahun
     */
    public function test_smart_query_beyond_active_range_uses_union()
    {
        // Setup data
        $coa = Coa::factory()->create(['tipe' => 'Aset']);

        // Jurnal masa lalu (4 tahun lalu)
        $oldJurnal = Jurnal::factory()->create([
            'tanggal' => now()->subYears(4),
            'is_posted' => true,
        ]);

        // Insert ke archive manually (simulate archived data)
        JurnalItemArchive::factory()->create([
            'jurnal_id' => $oldJurnal->id,
            'coa_id' => $coa->id,
            'debit' => 50,
            'kredit' => 0,
        ]);

        // Test
        $startDate = now()->subYears(4)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $info = $this->service->getQueryInfo($startDate, $endDate);

        $this->assertFalse($info['is_within_active_range']);
        $this->assertEquals(['jurnal_items', 'jurnal_items_archives'], $info['tables_used']);
    }

    /**
     * Test: getSumByCoaId mengembalikan debit dan kredit yang benar
     */
    public function test_get_sum_by_coa_id_calculates_correctly()
    {
        // Setup
        $coa = Coa::factory()->create();
        $jurnal = Jurnal::factory()->create([
            'tanggal' => now()->subMonths(1),
            'is_posted' => true,
        ]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa->id,
            'debit' => 100,
            'kredit' => 0,
        ]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa->id,
            'debit' => 0,
            'kredit' => 50,
        ]);

        // Test
        $startDate = now()->subMonths(3)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $sums = $this->service->getSumByCoaId($startDate, $endDate, $coa->id);

        $this->assertEquals(100, $sums['debit']);
        $this->assertEquals(50, $sums['kredit']);
    }

    /**
     * Test: getSumByMultipleCoaIds mengembalikan data untuk semua akun
     */
    public function test_get_sum_by_multiple_coa_ids_returns_all_accounts()
    {
        // Setup
        $coa1 = Coa::factory()->create();
        $coa2 = Coa::factory()->create();
        $coa3 = Coa::factory()->create();
        $coaIds = [$coa1->id, $coa2->id, $coa3->id];

        $jurnal = Jurnal::factory()->create([
            'tanggal' => now()->subMonths(1),
            'is_posted' => true,
        ]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa1->id,
            'debit' => 100,
            'kredit' => 0,
        ]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa2->id,
            'debit' => 0,
            'kredit' => 200,
        ]);

        // coa3 has no items

        // Test
        $startDate = now()->subMonths(3)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $sums = $this->service->getSumByMultipleCoaIds($startDate, $endDate, $coaIds);

        $this->assertArrayHasKey($coa1->id, $sums);
        $this->assertArrayHasKey($coa2->id, $sums);
        $this->assertArrayHasKey($coa3->id, $sums);

        $this->assertEquals(100, $sums[$coa1->id]['debit']);
        $this->assertEquals(0, $sums[$coa1->id]['kredit']);

        $this->assertEquals(0, $sums[$coa2->id]['debit']);
        $this->assertEquals(200, $sums[$coa2->id]['kredit']);

        $this->assertEquals(0, $sums[$coa3->id]['debit']);
        $this->assertEquals(0, $sums[$coa3->id]['kredit']);
    }

    /**
     * Test: getJournalsByCoaIds mengembalikan dengan relationships
     */
    public function test_get_journals_by_coa_ids_includes_relationships()
    {
        // Setup
        $coa1 = Coa::factory()->create(['nama_akun' => 'Kas']);
        $coa2 = Coa::factory()->create(['nama_akun' => 'Bank']);

        $jurnal = Jurnal::factory()->create([
            'tanggal' => now()->subMonths(1),
            'is_posted' => true,
            'keterangan' => 'Test Journal',
        ]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa1->id,
            'deskripsi' => 'Test Item',
        ]);

        // Test
        $startDate = now()->subMonths(3)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $items = $this->service->getJournalsByCoaIds($startDate, $endDate, [$coa1->id, $coa2->id]);

        $this->assertCount(1, $items);
        $item = $items->first();

        // Verify relationships are loaded
        $this->assertNotNull($item->jurnal);
        $this->assertEquals('Test Journal', $item->jurnal->keterangan);

        $this->assertNotNull($item->coa);
        $this->assertEquals('Kas', $item->coa->nama_akun);
    }

    /**
     * Test: byCoaIds scope dengan single ID
     */
    public function test_by_coa_ids_scope_with_single_id()
    {
        // Setup
        $coa1 = Coa::factory()->create();
        $coa2 = Coa::factory()->create();

        $jurnal = Jurnal::factory()->create(['is_posted' => true]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa1->id,
        ]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa2->id,
        ]);

        // Test
        $items = JurnalItem::byCoaIds($coa1->id)->get();

        $this->assertCount(1, $items);
        $this->assertEquals($coa1->id, $items->first()->coa_id);
    }

    /**
     * Test: byCoaIds scope dengan multiple IDs
     */
    public function test_by_coa_ids_scope_with_multiple_ids()
    {
        // Setup
        $coa1 = Coa::factory()->create();
        $coa2 = Coa::factory()->create();
        $coa3 = Coa::factory()->create();

        $jurnal = Jurnal::factory()->create(['is_posted' => true]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa1->id,
        ]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa2->id,
        ]);

        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa3->id,
        ]);

        // Test
        $items = JurnalItem::byCoaIds([$coa1->id, $coa2->id])->get();

        $this->assertCount(2, $items);
        $coaIds = $items->pluck('coa_id')->toArray();
        $this->assertContains($coa1->id, $coaIds);
        $this->assertContains($coa2->id, $coaIds);
    }

    /**
     * Test: withArchived scope menggabungkan data dari kedua tabel
     */
    public function test_with_archived_scope_combines_both_tables()
    {
        // Setup
        $coa = Coa::factory()->create();

        // Active data
        $activeJurnal = Jurnal::factory()->create([
            'tanggal' => now()->subMonths(1),
            'is_posted' => true,
        ]);

        JurnalItem::factory()->create([
            'jurnal_id' => $activeJurnal->id,
            'coa_id' => $coa->id,
            'debit' => 100,
        ]);

        // Archived data
        $archivedJurnal = Jurnal::factory()->create([
            'tanggal' => now()->subYears(3),
            'is_posted' => true,
        ]);

        JurnalItemArchive::factory()->create([
            'jurnal_id' => $archivedJurnal->id,
            'coa_id' => $coa->id,
            'debit' => 50,
        ]);

        // Test
        $startDate = now()->subYears(3)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $items = JurnalItem::withArchived($startDate, $endDate)
            ->where('coa_id', $coa->id)
            ->get();

        // Should get both active and archived
        $this->assertCount(2, $items);
        $totalDebit = $items->sum('debit');
        $this->assertEquals(150, $totalDebit);
    }

    /**
     * Test: Query info menunjukkan info yang benar
     */
    public function test_query_info_returns_correct_information()
    {
        $startDate = '2024-01-01';
        $endDate = now()->format('Y-m-d');

        $info = $this->service->getQueryInfo($startDate, $endDate);

        $this->assertArrayHasKey('start_date', $info);
        $this->assertArrayHasKey('end_date', $info);
        $this->assertArrayHasKey('is_within_active_range', $info);
        $this->assertArrayHasKey('tables_used', $info);
        $this->assertArrayHasKey('active_data_threshold_years', $info);
        $this->assertArrayHasKey('cutoff_date', $info);

        $this->assertEquals($startDate, $info['start_date']);
        $this->assertEquals($endDate, $info['end_date']);
        $this->assertNull($info['active_data_threshold_years']);
    }
}
