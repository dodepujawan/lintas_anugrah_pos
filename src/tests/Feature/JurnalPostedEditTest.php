<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\JurnalItemArchive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JurnalPostedEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_posted_general_journal_can_be_updated_and_have_rows_added(): void
    {
        $user = User::factory()->create();
        $cash = $this->coa('11000000', 'Kas', 'Aset', 'Debit');
        $bank = $this->coa('11010000', 'Bank', 'Aset', 'Debit');
        $capital = $this->coa('31000000', 'Modal', 'Ekuitas', 'Kredit');

        $journal = Jurnal::query()->create([
            'no_jurnal' => 'JU202607000001',
            'tanggal' => '2026-07-10',
            'keterangan' => 'Jurnal awal',
            'total_debit' => 100000,
            'total_kredit' => 100000,
            'referensi' => 'REF-OLD',
            'created_by' => $user->id,
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);
        JurnalItem::query()->create(['jurnal_id' => $journal->id, 'coa_id' => $cash->id, 'deskripsi' => 'Debit lama', 'debit' => 100000, 'kredit' => 0]);
        JurnalItem::query()->create(['jurnal_id' => $journal->id, 'coa_id' => $capital->id, 'deskripsi' => 'Kredit lama', 'debit' => 0, 'kredit' => 100000]);

        $response = $this->actingAs($user)->put(route('jurnal.update', $journal), [
            'draft_id' => $journal->id,
            'no_jurnal' => $journal->no_jurnal,
            'tanggal' => '2026-07-11',
            'keterangan' => 'Jurnal sudah diedit',
            'referensi' => 'REF-NEW',
            'coa_id' => [$cash->id, $bank->id, $capital->id],
            'deskripsi_item' => ['Kas edit', 'Bank tambahan', 'Modal edit'],
            'debit' => [75000, 25000, 0],
            'kredit' => [0, 0, 100000],
        ]);

        $response->assertRedirect(route('jurnal.index'));
        $response->assertSessionHas('success');

        $journal->refresh();
        $this->assertSame('2026-07-11', $journal->tanggal->format('Y-m-d'));
        $this->assertSame('Jurnal sudah diedit', $journal->keterangan);
        $this->assertSame('REF-NEW', $journal->referensi);
        $this->assertEquals(100000, (float) $journal->total_debit);
        $this->assertEquals(100000, (float) $journal->total_kredit);
        $this->assertSame(Jurnal::STATUS_POSTED, $journal->status);
        $this->assertTrue($journal->is_posted);

        $this->assertCount(3, $journal->items()->get());
        $this->assertDatabaseHas('jurnal_items', ['jurnal_id' => $journal->id, 'coa_id' => $bank->id, 'debit' => 25000, 'kredit' => 0]);
        $this->assertDatabaseMissing('jurnal_items', ['jurnal_id' => $journal->id, 'deskripsi' => 'Debit lama']);
    }

    public function test_updating_archived_posted_journal_replaces_archived_rows(): void
    {
        $user = User::factory()->create();
        $cash = $this->coa('11000000', 'Kas', 'Aset', 'Debit');
        $capital = $this->coa('31000000', 'Modal', 'Ekuitas', 'Kredit');

        $journal = Jurnal::query()->create([
            'no_jurnal' => 'JU202607000002',
            'tanggal' => '2026-07-12',
            'keterangan' => 'Jurnal arsip',
            'total_debit' => 50000,
            'total_kredit' => 50000,
            'created_by' => $user->id,
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);
        JurnalItemArchive::query()->create(['jurnal_id' => $journal->id, 'coa_id' => $cash->id, 'deskripsi' => 'Debit arsip lama', 'debit' => 50000, 'kredit' => 0]);
        JurnalItemArchive::query()->create(['jurnal_id' => $journal->id, 'coa_id' => $capital->id, 'deskripsi' => 'Kredit arsip lama', 'debit' => 0, 'kredit' => 50000]);

        $this->actingAs($user)->put(route('jurnal.update', $journal), [
            'draft_id' => $journal->id,
            'no_jurnal' => $journal->no_jurnal,
            'tanggal' => '2026-07-12',
            'keterangan' => 'Jurnal arsip diedit',
            'referensi' => null,
            'coa_id' => [$cash->id, $capital->id],
            'deskripsi_item' => ['Debit baru', 'Kredit baru'],
            'debit' => [75000, 0],
            'kredit' => [0, 75000],
        ])->assertRedirect(route('jurnal.index'));

        $this->assertSame(0, $journal->archivedItems()->count());
        $this->assertCount(2, $journal->items()->get());
        $this->assertDatabaseHas('jurnal_items', ['jurnal_id' => $journal->id, 'deskripsi' => 'Debit baru', 'debit' => 75000]);
        $this->assertDatabaseMissing('jurnal_items_archives', ['jurnal_id' => $journal->id, 'deskripsi' => 'Debit arsip lama']);
    }

    public function test_general_journal_must_be_balanced(): void
    {
        $user = User::factory()->create();
        $cash = $this->coa('11000000', 'Kas', 'Aset', 'Debit');
        $capital = $this->coa('31000000', 'Modal', 'Ekuitas', 'Kredit');

        $response = $this->actingAs($user)->post(route('jurnal.store'), [
            'no_jurnal' => 'JU202607000003',
            'tanggal' => '2026-07-13',
            'keterangan' => 'Jurnal tidak balance ditolak',
            'referensi' => null,
            'coa_id' => [$cash->id, $capital->id],
            'deskripsi_item' => ['Debit sementara', 'Kredit sementara'],
            'debit' => [100000, 0],
            'kredit' => [0, 40000],
        ]);

        $response->assertSessionHasErrors('balance');
        $this->assertDatabaseMissing('jurnals', ['no_jurnal' => 'JU202607000003']);
    }

    public function test_unbalanced_draft_cannot_be_posted_from_post_action(): void
    {
        $user = User::factory()->create();
        $cash = $this->coa('11000000', 'Kas', 'Aset', 'Debit');
        $capital = $this->coa('31000000', 'Modal', 'Ekuitas', 'Kredit');

        $journal = Jurnal::query()->create([
            'no_jurnal' => 'JU202607000004',
            'tanggal' => '2026-07-14',
            'keterangan' => 'Draft tidak balance',
            'total_debit' => 100000,
            'total_kredit' => 40000,
            'created_by' => $user->id,
            'status' => Jurnal::STATUS_DRAFT,
            'is_posted' => false,
        ]);
        JurnalItem::query()->create(['jurnal_id' => $journal->id, 'coa_id' => $cash->id, 'deskripsi' => 'Debit', 'debit' => 100000, 'kredit' => 0]);
        JurnalItem::query()->create(['jurnal_id' => $journal->id, 'coa_id' => $capital->id, 'deskripsi' => 'Kredit', 'debit' => 0, 'kredit' => 40000]);

        $this->actingAs($user)->post(route('jurnal.post', $journal))
            ->assertRedirect(route('jurnal.index'))
            ->assertSessionHas('error');

        $journal->refresh();
        $this->assertSame(Jurnal::STATUS_DRAFT, $journal->status);
        $this->assertFalse($journal->is_posted);
    }

    public function test_posted_general_journal_cannot_be_updated_to_unbalanced_amounts(): void
    {
        $user = User::factory()->create();
        $cash = $this->coa('11000000', 'Kas', 'Aset', 'Debit');
        $capital = $this->coa('31000000', 'Modal', 'Ekuitas', 'Kredit');

        $journal = Jurnal::query()->create([
            'no_jurnal' => 'JU202607000005',
            'tanggal' => '2026-07-15',
            'keterangan' => 'Jurnal balance',
            'total_debit' => 100000,
            'total_kredit' => 100000,
            'created_by' => $user->id,
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        $response = $this->actingAs($user)->put(route('jurnal.update', $journal), [
            'draft_id' => $journal->id,
            'no_jurnal' => $journal->no_jurnal,
            'tanggal' => '2026-07-15',
            'keterangan' => 'Jurnal tidak balance',
            'referensi' => null,
            'coa_id' => [$cash->id, $capital->id],
            'deskripsi_item' => ['Debit', 'Kredit'],
            'debit' => [100000, 0],
            'kredit' => [0, 90000],
        ]);

        $response->assertSessionHasErrors('balance');
        $this->assertDatabaseHas('jurnals', [
            'id' => $journal->id,
            'keterangan' => 'Jurnal balance',
            'total_debit' => 100000,
            'total_kredit' => 100000,
        ]);
    }

    public function test_unbalanced_posted_general_journal_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $cash = $this->coa('11000000', 'Kas', 'Aset', 'Debit');
        $capital = $this->coa('31000000', 'Modal', 'Ekuitas', 'Kredit');

        $journal = Jurnal::query()->create([
            'no_jurnal' => 'JU202607000006',
            'tanggal' => '2026-07-16',
            'keterangan' => 'Posted tidak balance',
            'total_debit' => 100000,
            'total_kredit' => 40000,
            'created_by' => $user->id,
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);
        JurnalItem::query()->create(['jurnal_id' => $journal->id, 'coa_id' => $cash->id, 'deskripsi' => 'Debit', 'debit' => 100000, 'kredit' => 0]);
        JurnalItem::query()->create(['jurnal_id' => $journal->id, 'coa_id' => $capital->id, 'deskripsi' => 'Kredit', 'debit' => 0, 'kredit' => 40000]);

        $this->actingAs($user)->delete(route('jurnal.destroy', $journal))
            ->assertRedirect(route('jurnal.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('jurnals', ['id' => $journal->id]);
        $this->assertSame(2, $journal->items()->count());
    }

    private function coa(string $code, string $name, string $type, string $normal): Coa
    {
        return Coa::query()->updateOrCreate(['kode_akun' => $code], [
            'nama_akun' => $name,
            'tipe' => $type,
            'kategori' => null,
            'level' => 'Akun Posting',
            'saldo_normal' => $normal,
            'is_active' => true,
        ]);
    }
}
