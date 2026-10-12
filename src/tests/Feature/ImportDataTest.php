<?php

namespace Tests\Feature;

use App\Exports\ImportTemplateExport;
use App\Models\Arh;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\User;
use App\Services\ImportDataService;
use App\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_import_creates_receivable_and_posted_journal(): void
    {
        $user = User::factory()->create();
        $this->coa('11020000', 'Piutang', 'Aset', 'Debit');
        $this->coa('41010000', 'Penjualan', 'Pendapatan', 'Kredit');
        $this->coa('21020000', 'Pajak Keluaran', 'Kewajiban', 'Kredit');

        $count = app(ImportDataService::class)->import('penjualan', $this->template('penjualan'), '2026-07-01', $user);

        $this->assertSame(1, $count);
        $invoice = Arh::where('NOFAKTUR', 'INV-0001')->firstOrFail();
        $this->assertSame('IMPORT_PENJUALAN', $invoice->SOURCE);
        $this->assertNotNull($invoice->NOJURNAL);
        $journal = Jurnal::where('no_jurnal', $invoice->NOJURNAL)->firstOrFail();
        $this->assertEquals(1110000, $journal->total_debit);
        $this->assertEquals(1110000, $journal->total_kredit);
    }

    public function test_opening_receivable_import_does_not_create_another_journal(): void
    {
        $user = User::factory()->create();
        $receivable = $this->coa('11020000', 'Piutang', 'Aset', 'Debit');
        $equity = $this->coa('31000000', 'Modal Saldo Awal', 'Ekuitas', 'Kredit');
        app(JournalService::class)->createJournal('SA260630001', '2026-06-30', 'Saldo awal', [
            ['coa_id' => $receivable->id, 'deskripsi' => 'Piutang', 'debit' => 2500000, 'kredit' => 0],
            ['coa_id' => $equity->id, 'deskripsi' => 'Modal', 'debit' => 0, 'kredit' => 2500000],
        ], null, $user->id, 'Saldo Awal', 'Jurnal Umum');

        $count = app(ImportDataService::class)->import('saldo_piutang', $this->template('saldo_piutang'), '2026-07-01', $user);

        $this->assertSame(1, $count);
        $invoice = Arh::where('NOFAKTUR', 'INV-LAMA-001')->firstOrFail();
        $this->assertSame('SALDO_AWAL_IMPORT', $invoice->SOURCE);
        $this->assertNull($invoice->NOJURNAL);
        $this->assertSame(1, Jurnal::count());
    }

    private function template(string $type): UploadedFile
    {
        return UploadedFile::fake()->createWithContent("{$type}.xlsx", Excel::raw(new ImportTemplateExport($type), \Maatwebsite\Excel\Excel::XLSX));
    }

    private function coa(string $code, string $name, string $type, string $normal): Coa
    {
        return Coa::create(['kode_akun' => $code, 'nama_akun' => $name, 'tipe' => $type, 'kategori' => null, 'level' => 'Akun Posting', 'saldo_normal' => $normal, 'is_active' => true]);
    }
}
