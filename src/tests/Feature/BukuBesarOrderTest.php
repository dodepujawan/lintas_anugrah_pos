<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\User;
use App\Services\JournalQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BukuBesarOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_buku_besar_items_sorted_by_journal_date_ascending(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $cash = Coa::create([
            'kode_akun' => '1110',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
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

        // Jurnal tanggal 20 dibuat lebih dulu, tanggal 10 belakangan,
        // supaya urutan created_at berlawanan dengan tanggal jurnal.
        $later = Jurnal::create([
            'no_jurnal' => 'JU-LATER',
            'tanggal' => '2026-08-20',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Transaksi tanggal 20',
            'total_debit' => 1000,
            'total_kredit' => 1000,
            'created_by' => $user->id,
            'is_posted' => true,
        ]);
        $later->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Kas 20', 'debit' => 1000, 'kredit' => 0],
            ['coa_id' => $modal->id, 'deskripsi' => 'Modal 20', 'debit' => 0, 'kredit' => 1000],
        ]);

        $earlier = Jurnal::create([
            'no_jurnal' => 'JU-EARLIER',
            'tanggal' => '2026-08-10',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Transaksi tanggal 10',
            'total_debit' => 2000,
            'total_kredit' => 2000,
            'created_by' => $user->id,
            'is_posted' => true,
        ]);
        $earlier->items()->createMany([
            ['coa_id' => $cash->id, 'deskripsi' => 'Kas 10', 'debit' => 2000, 'kredit' => 0],
            ['coa_id' => $modal->id, 'deskripsi' => 'Modal 10', 'debit' => 0, 'kredit' => 2000],
        ]);
        $items = app(JournalQueryService::class)
            ->getLedgerItemsForCoa('2026-08-01', '2026-08-31', (int) $cash->id);

        $this->assertCount(2, $items);
        $this->assertSame('JU-EARLIER', $items[0]->jurnal->no_jurnal);
        $this->assertSame('JU-LATER', $items[1]->jurnal->no_jurnal);
        $this->assertTrue($items[0]->jurnal->tanggal->lte($items[1]->jurnal->tanggal));

        $response = $this->actingAs($user)->get(route('laporan.buku_besar', [
            'coa_id' => $cash->id,
            'tanggal_awal' => '2026-08-01',
            'tanggal_akhir' => '2026-08-31',
        ]));

        $response->assertOk();
        $content = $response->getContent();
        $posEarlier = strpos($content, 'JU-EARLIER');
        $posLater = strpos($content, 'JU-LATER');
        $this->assertNotFalse($posEarlier);
        $this->assertNotFalse($posLater);
        $this->assertLessThan($posLater, $posEarlier);
        $response->assertSee('Saldo Awal per 01/08/2026');
        $response->assertSee('Saldo Akhir per 31/08/2026');
    }
}
