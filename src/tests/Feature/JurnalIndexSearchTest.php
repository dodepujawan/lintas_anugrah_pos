<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JurnalIndexSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_journal_index_can_search_journal_and_item_content(): void
    {
        $user = User::factory()->create();
        $cash = Coa::factory()->create([
            'kode_akun' => '110100',
            'nama_akun' => 'Kas Besar Dicari',
            'level' => 'Akun Posting',
        ]);
        $bank = Coa::factory()->create([
            'kode_akun' => '110200',
            'nama_akun' => 'Bank Lain',
            'level' => 'Akun Posting',
        ]);

        $matched = Jurnal::factory()->create([
            'no_jurnal' => 'JU202607000101',
            'tanggal' => '2026-07-11',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Transaksi masuk khusus',
            'referensi' => 'REF-DICARI',
            'is_posted' => true,
            'created_by' => $user->id,
        ]);
        $matched->items()->create([
            'coa_id' => $cash->id,
            'deskripsi' => 'Baris kas dicari',
            'debit' => 100000,
            'kredit' => 0,
        ]);

        Jurnal::factory()->create([
            'no_jurnal' => 'JU202607000102',
            'tanggal' => '2026-07-11',
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Transaksi lain',
            'referensi' => 'REF-LAIN',
            'is_posted' => true,
            'created_by' => $user->id,
        ])->items()->create([
            'coa_id' => $bank->id,
            'deskripsi' => 'Baris bank',
            'debit' => 0,
            'kredit' => 100000,
        ]);

        $this->actingAs($user)->get(route('jurnal.index', ['search' => 'dicari']))
            ->assertOk()
            ->assertSee('JU202607000101')
            ->assertDontSee('JU202607000102')
            ->assertSee('value="dicari"', false);
    }
}
