<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_delete_confirmation_is_rendered_for_all_journal_modules(): void
    {
        $user = User::factory()->create();
        $modules = [
            ['JU202607000001', 'Jurnal Umum', 'jurnal.index'],
            ['JP202607000001', 'Jurnal Umum', 'jurnal-penyesuaian.index'],
            ['SA202607000001', 'Jurnal Umum', 'saldo-awal.index'],
        ];

        foreach ($modules as [$number, $type, $route]) {
            $this->draft($number, $type, $user);
            $this->actingAs($user)->get(route($route))
                ->assertOk()
                ->assertSee("Hapus Draft {$number}?")
                ->assertSee('tindakan ini tidak dapat dibatalkan.');
        }
    }

    public function test_adjustment_and_opening_balance_drafts_can_be_deleted_but_posted_entries_cannot(): void
    {
        $user = User::factory()->create();

        foreach ([
            ['JP202607000001', 'Jurnal Umum', 'jurnal-penyesuaian.destroy'],
            ['SA202607000001', 'Jurnal Umum', 'saldo-awal.destroy'],
        ] as [$number, $type, $route]) {
            $draft = $this->draft($number, $type, $user);
            $this->actingAs($user)->delete(route($route, $draft))
                ->assertSessionHas('success', "Draft {$number} berhasil dihapus.");
            $this->assertDatabaseMissing('jurnals', ['id' => $draft->id]);

            $posted = $this->draft(str_replace('000001', '000002', $number), $type, $user, true);
            $this->actingAs($user)->delete(route($route, $posted))->assertSessionHas('error');
            $this->assertDatabaseHas('jurnals', ['id' => $posted->id]);
        }
    }

    public function test_posted_general_journal_can_be_deleted_with_all_items(): void
    {
        $user = User::factory()->create();
        $jurnal = $this->draft('JU202607000003', 'Jurnal Umum', $user, true);
        $coa = Coa::factory()->create(['level' => 'Akun Posting']);

        $item = JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa->id,
            'debit' => 100000,
            'kredit' => 0,
        ]);
        JurnalItem::factory()->create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $coa->id,
            'debit' => 0,
            'kredit' => 100000,
        ]);

        $this->actingAs($user)->get(route('jurnal.show', $jurnal))
            ->assertOk()
            ->assertSee('Hapus Jurnal')
            ->assertSee(route('jurnal.destroy', $jurnal), false);

        $this->actingAs($user)->delete(route('jurnal.destroy', $jurnal))
            ->assertRedirect(route('jurnal.index'))
            ->assertSessionHas('success', 'Jurnal JU202607000003 berhasil dihapus');

        $this->assertDatabaseMissing('jurnals', ['id' => $jurnal->id]);
        $this->assertDatabaseMissing('jurnal_items', ['id' => $item->id]);
    }

    private function draft(string $number, string $type, User $user, bool $posted = false): Jurnal
    {
        return Jurnal::create([
            'no_jurnal' => $number,
            'tanggal' => '2026-07-11',
            'tipe' => $type,
            'module_type' => $type,
            'status' => $posted ? Jurnal::STATUS_POSTED : Jurnal::STATUS_DRAFT,
            'is_posted' => $posted,
            'total_debit' => 0,
            'total_kredit' => 0,
            'created_by' => $user->id,
        ]);
    }
}
