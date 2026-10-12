<?php

namespace Tests\Feature;

use App\Models\Barh;
use App\Models\Jurnal;
use App\Models\User;
use App\Services\LedgerSourceLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerSourceLinkServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelunasan_piutang_journal_links_to_payment_edit(): void
    {
        $user = User::factory()->create();

        $journal = Jurnal::query()->create([
            'no_jurnal' => 'AR260519001',
            'tanggal' => '2026-05-19',
            'tipe' => 'Pelunasan Piutang',
            'keterangan' => 'Pelunasan Piutang: JKT-NURIFA/NURHADI - AR260700168',
            'total_debit' => 1182327,
            'total_kredit' => 1182327,
            'referensi' => 'AR260700168',
            'created_by' => $user->id,
            'status' => 'Posted',
            'module_type' => 'Pelunasan Piutang',
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        $payment = Barh::query()->create([
            'NOBAYAR' => 'AR260700168',
            'TGLBAYAR' => '2026-05-19',
            'CUSTOMER' => 'JKT-NURIFA/NURHADI',
            'JUMLAH_BAYAR' => 1182326,
            'METODE_PEMBAYARAN' => 'TRANSFER',
            'NOJURNAL' => 'AR260519001',
            'STATUS' => 'ACTIVE',
            'USER' => $user->name,
        ]);

        $links = app(LedgerSourceLinkService::class)->resolveForJournals(collect([$journal]));

        $this->assertArrayHasKey($journal->id, $links);
        $this->assertSame('Edit Pelunasan', $links[$journal->id]['label']);
        $this->assertSame('AR260700168', $links[$journal->id]['source_number']);
        $this->assertSame(route('pelunasan-piutang.edit', $payment->id), $links[$journal->id]['url']);
    }
}
