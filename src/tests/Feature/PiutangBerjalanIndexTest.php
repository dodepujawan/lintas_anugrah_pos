<?php

namespace Tests\Feature;

use App\Models\Arh;
use App\Models\AccountMapping;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiutangBerjalanIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_manual_import_and_synced_receivables(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();

        $this->receivable('MAN-001', 'PIUTANG_BERJALAN_MANUAL');
        $this->receivable('IMP-001', 'IMPORT_PENJUALAN');
        $this->receivable('SYN-001', 'SYNC_EKS');
        $this->receivable('OLD-001', 'SALDO_AWAL_IMPORT');

        $response = $this->actingAs($user)->get(route('piutang-berjalan.index'));

        $response->assertOk();
        $response->assertSee('MAN-001');
        $response->assertSee('IMP-001');
        $response->assertSee('SYN-001');
        $response->assertDontSee('OLD-001');
        $response->assertSee('Manual');
        $response->assertSee('Import');
        $response->assertSee('Sinkron');
    }

    public function test_index_can_filter_by_source(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();

        $this->receivable('MAN-001', 'PIUTANG_BERJALAN_MANUAL');
        $this->receivable('IMP-001', 'IMPORT_PENJUALAN');
        $this->receivable('SYN-001', 'SYNC_EKS');

        $this->actingAs($user)->get(route('piutang-berjalan.index', ['source' => 'manual']))
            ->assertOk()
            ->assertSee('MAN-001')
            ->assertDontSee('IMP-001')
            ->assertDontSee('SYN-001');

        $this->actingAs($user)->get(route('piutang-berjalan.index', ['source' => 'import']))
            ->assertOk()
            ->assertSee('IMP-001')
            ->assertDontSee('MAN-001')
            ->assertDontSee('SYN-001');

        $this->actingAs($user)->get(route('piutang-berjalan.index', ['source' => 'sync']))
            ->assertOk()
            ->assertSee('SYN-001')
            ->assertSee('Detail')
            ->assertDontSee('MAN-001')
            ->assertDontSee('IMP-001')
            ->assertDontSee('Edit')
            ->assertDontSee('Hapus');
    }

    public function test_index_shows_totals_for_filtered_rows(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();

        $this->receivable('MAN-001', 'PIUTANG_BERJALAN_MANUAL');
        $this->receivable('SYN-001', 'SYNC_EKS');

        $response = $this->actingAs($user)->get(route('piutang-berjalan.index', ['source' => 'sync']));

        $response->assertOk();
        $response->assertSee('Jumlah Faktur');
        $response->assertSee('Total Piutang');
        $response->assertSee('Total Bayar');
        $response->assertSee('Total Saldo');
        $response->assertSee('100.000,00');
    }

    public function test_synced_receivable_detail_shows_journal_badge_and_contextual_journal_link(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $receivable = Coa::query()->create([
            'kode_akun' => '11020000',
            'nama_akun' => 'Piutang Usaha',
            'tipe' => 'Aset',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);
        $revenue = Coa::query()->create([
            'kode_akun' => '41010000',
            'nama_akun' => 'Pendapatan',
            'tipe' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);
        AccountMapping::query()->create(['mapping_key' => 'accounts_receivable', 'account_id' => $receivable->id]);
        $journal = Jurnal::query()->create([
            'no_jurnal' => 'PJ260701001',
            'tanggal' => '2026-07-01',
            'keterangan' => 'Sinkron invoice',
            'referensi' => 'SYN-001',
            'total_debit' => 100000,
            'total_kredit' => 100000,
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);
        $journal->items()->create(['coa_id' => $receivable->id, 'debit' => 100000, 'kredit' => 0, 'deskripsi' => 'Piutang']);
        $journal->items()->create(['coa_id' => $revenue->id, 'debit' => 0, 'kredit' => 100000, 'deskripsi' => 'Pendapatan']);
        $this->receivable('SYN-001', 'SYNC_EKS');
        $piutang = Arh::query()->where('NOFAKTUR', 'SYN-001')->firstOrFail();

        $response = $this->actingAs($user)->get(route('piutang.show', ['piutang' => $piutang, 'from' => 'piutang-berjalan']));

        $response->assertOk();
        $response->assertSee('Jurnal Valid');
        $response->assertSee('PJ260701001');
        $response->assertSee('from=piutang', false);
        $response->assertSee('piutang_from=piutang-berjalan', false);
        $response->assertSee('Kembali ke Piutang Berjalan');
    }

    private function receivable(string $invoice, string $source): void
    {
        Arh::query()->create([
            'NOFAKTUR' => $invoice,
            'TGLFAKTUR' => '2026-07-01',
            'TGLJT' => '2026-07-31',
            'CUSTOMER' => 'CUST-001',
            'PIUTANG' => 100000,
            'BAYAR' => 0,
            'RETUR' => 0,
            'DISCOUNT' => 0,
            'SALDO' => 100000,
            'KETERANGAN' => 'Test',
            'STATUS' => 'OPEN',
            'SOURCE' => $source,
            'NOJURNAL' => $source === 'SALDO_AWAL_IMPORT' ? null : 'PJ260701001',
            'USER' => 'Test',
        ]);
    }
}
