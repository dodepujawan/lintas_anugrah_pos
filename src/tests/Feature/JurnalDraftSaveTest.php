<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JurnalDraftSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_can_be_saved_and_updated(): void
    {
        $user = User::factory()->create();
        $cash = $this->coa('11000000', 'Kas', 'Aset', 'Debit');
        $capital = $this->coa('31000000', 'Modal', 'Ekuitas', 'Kredit');
        $payload = $this->payload($cash, $capital, 'JU202607000001');

        $response = $this->actingAs($user)->postJson(route('jurnal.draft.save'), $payload)
            ->assertOk()->assertJson(['success' => true]);

        $draftId = $response->json('draft_id');
        $this->actingAs($user)->postJson(route('jurnal.draft.save'), $payload + ['draft_id' => $draftId])
            ->assertOk()->assertJson(['success' => true, 'draft_id' => $draftId]);

        $draft = Jurnal::findOrFail($draftId);
        $this->assertSame(Jurnal::STATUS_DRAFT, $draft->status);
        $this->assertSame('Jurnal Umum', $draft->tipe);
        $this->assertCount(2, $draft->items);
    }

    public function test_draft_gets_a_fresh_number_when_requested_number_is_already_used(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $cash = $this->coa('11000000', 'Kas', 'Aset', 'Debit');
        $capital = $this->coa('31000000', 'Modal', 'Ekuitas', 'Kredit');
        $payload = $this->payload($cash, $capital, 'JU202607000001');

        $this->actingAs($firstUser)->postJson(route('jurnal.draft.save'), $payload)->assertOk();
        $response = $this->actingAs($secondUser)->postJson(route('jurnal.draft.save'), $payload)
            ->assertOk()->assertJson(['success' => true]);

        $this->assertNotSame('JU202607000001', $response->json('no_jurnal'));
        $this->assertSame(2, Jurnal::count());
    }

    private function payload(Coa $debit, Coa $credit, string $number): array
    {
        return ['no_jurnal' => $number, 'tanggal' => '2026-07-11', 'keterangan' => 'Draft test', 'coa_id' => [$debit->id, $credit->id], 'debit' => [100000, 0], 'kredit' => [0, 100000], 'deskripsi_item' => ['Debit', 'Kredit']];
    }

    private function coa(string $code, string $name, string $type, string $normal): Coa
    {
        $coa = Coa::firstOrNew(['kode_akun' => $code]);
        $coa->fill(['nama_akun' => $name, 'tipe' => $type, 'kategori' => null, 'level' => 'Akun Posting', 'saldo_normal' => $normal, 'is_active' => true]);
        $coa->save();

        return $coa;
    }
}
