<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItemArchive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivedJournalDetailActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Coa $cash;
    private Coa $revenue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => true]);
        $this->cash = $this->createCoa('01010101', 'Kas Detail Arsip', 'Aset', 'Kas', 'Debit');
        $this->revenue = $this->createCoa('04010101', 'Pendapatan Detail Arsip', 'Pendapatan', 'Pendapatan', 'Kredit');
    }

    public function test_jurnal_umum_detail_shows_archived_items_after_year_close(): void
    {
        $journal = $this->createArchivedJournal('JU260001', 'Jurnal umum arsip');

        $response = $this->actingAs($this->user)->get(route('jurnal.show', $journal));

        $response->assertOk();
        $response->assertSee('01010101');
        $response->assertSee('Kas Detail Arsip');
        $response->assertSee('04010101');
        $response->assertSee('Pendapatan Detail Arsip');
        $response->assertSee('Jurnal umum arsip');
    }

    public function test_jurnal_penyesuaian_detail_shows_archived_items_after_year_close(): void
    {
        $journal = $this->createArchivedJournal('JP-2026-000001', 'Jurnal penyesuaian arsip');

        $response = $this->actingAs($this->user)->get(route('jurnal-penyesuaian.show', $journal));

        $response->assertOk();
        $response->assertSee('01010101');
        $response->assertSee('Kas Detail Arsip');
        $response->assertSee('04010101');
        $response->assertSee('Pendapatan Detail Arsip');
    }

    public function test_saldo_awal_detail_shows_archived_items_after_year_close(): void
    {
        $journal = $this->createArchivedJournal('SA260001', 'Saldo awal arsip');

        $response = $this->actingAs($this->user)->get(route('saldo-awal.show', $journal));

        $response->assertOk();
        $response->assertSee('01010101');
        $response->assertSee('Kas Detail Arsip');
        $response->assertSee('04010101');
        $response->assertSee('Pendapatan Detail Arsip');
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

    private function createArchivedJournal(string $number, string $description): Jurnal
    {
        $journal = Jurnal::create([
            'no_jurnal' => $number,
            'tanggal' => '2026-12-31',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => $description,
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'referensi' => 'ARCHIVE-DETAIL',
            'created_by' => $this->user->id,
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $journal->id,
            'coa_id' => $this->cash->id,
            'deskripsi' => 'Kas dari arsip',
            'debit' => 1000,
            'kredit' => 0,
        ]);

        JurnalItemArchive::create([
            'jurnal_id' => $journal->id,
            'coa_id' => $this->revenue->id,
            'deskripsi' => 'Pendapatan dari arsip',
            'debit' => 0,
            'kredit' => 1000,
        ]);

        return $journal;
    }
}
