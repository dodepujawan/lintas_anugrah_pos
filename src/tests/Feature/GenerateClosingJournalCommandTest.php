<?php

namespace Tests\Feature;

use App\Models\AccountMapping;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GenerateClosingJournalCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_and_posts_closing_journal_from_tutup_buku_report(): void
    {
        $user = User::factory()->create();

        $pendapatan = Coa::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Pendapatan Jasa',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $beban = Coa::create([
            'kode_akun' => '5101',
            'nama_akun' => 'Beban Operasional',
            'tipe' => 'Beban',
            'kategori' => 'Beban Operasional',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $retainedEarnings = Coa::create([
            'kode_akun' => '3201',
            'nama_akun' => 'Laba Ditahan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Laba Ditahan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $currentYearProfit = Coa::create([
            'kode_akun' => '3202',
            'nama_akun' => 'Laba Tahun Berjalan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Laba Tahun Berjalan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        AccountMapping::create(['mapping_key' => 'retained_earnings', 'account_id' => $retainedEarnings->id]);
        AccountMapping::create(['mapping_key' => 'current_year_profit', 'account_id' => $currentYearProfit->id]);

        $tanggal = now()->toDateString();

        Jurnal::create([
            'no_jurnal' => 'JU-TB-001',
            'tanggal' => $tanggal,
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Pendapatan dan beban periode',
            'total_debit' => 1200,
            'total_kredit' => 1200,
            'referensi' => 'REF-TB-001',
            'created_by' => $user->id,
            'is_posted' => true,
        ])->items()->createMany([
            ['coa_id' => $pendapatan->id, 'deskripsi' => 'Pendapatan', 'debit' => 0, 'kredit' => 1000],
            ['coa_id' => $beban->id, 'deskripsi' => 'Beban operasional', 'debit' => 200, 'kredit' => 0],
        ]);

        $exitCode = Artisan::call('app:generate-closing-journal', [
            '--tanggal-awal' => $tanggal,
            '--tanggal-akhir' => $tanggal,
            '--keterangan' => 'Jurnal tutup otomatis',
        ]);

        $this->assertSame(0, $exitCode);

        $closingJournal = Jurnal::where('keterangan', 'Jurnal tutup otomatis')->latest()->first();
        $this->assertNotNull($closingJournal);
        $this->assertTrue($closingJournal->is_posted);
        $this->assertSame(Jurnal::STATUS_POSTED, $closingJournal->status);

        $totalDebit = (float) $closingJournal->items()->sum('debit');
        $totalCredit = (float) $closingJournal->items()->sum('kredit');
        $currentYearProfitDebit = (float) $closingJournal->items()->where('coa_id', $currentYearProfit->id)->sum('debit');
        $currentYearProfitCredit = (float) $closingJournal->items()->where('coa_id', $currentYearProfit->id)->sum('kredit');

        $this->assertSame(1000.0, (float) $closingJournal->items()->where('coa_id', $pendapatan->id)->first()->debit);
        $this->assertSame(200.0, (float) $closingJournal->items()->where('coa_id', $beban->id)->first()->kredit);
        $this->assertSame(1000.0, $currentYearProfitCredit);
        $this->assertSame(1000.0, $currentYearProfitDebit);
        $this->assertSame(800.0, (float) $closingJournal->items()->where('coa_id', $retainedEarnings->id)->first()->kredit);
        $this->assertSame($totalCredit, $totalDebit);
    }
}
