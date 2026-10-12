<?php

namespace Tests\Feature;

use App\Http\Controllers\AuditSelisihController;
use App\Models\AccountMapping;
use App\Models\Arh;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\User;
use App\Services\JournalQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class AuditSelisihReceivableSuspectTest extends TestCase
{
    use RefreshDatabase;

    public function test_receivable_suspect_finder_points_to_manual_ar_journal(): void
    {
        $user = User::factory()->create([
            'username' => 'mariani',
            'name' => 'mariani',
        ]);

        $bank = Coa::query()->create([
            'kode_akun' => '01010201',
            'nama_akun' => 'Bank BCA',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva Lancar',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $receivable = Coa::query()->create([
            'kode_akun' => '01010301',
            'nama_akun' => 'Piutang Usaha',
            'tipe' => 'Aset',
            'kategori' => 'Aktiva Lancar',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        AccountMapping::query()->create([
            'mapping_key' => 'accounts_receivable',
            'account_id' => $receivable->id,
        ]);

        Arh::create([
            'NOFAKTUR' => 'FJ-TEST-001',
            'TGLFAKTUR' => '2026-08-01',
            'TGLJT' => '2026-08-31',
            'CUSTOMER' => 'CUST01',
            'PIUTANG' => 120000000,
            'BAYAR' => 0,
            'SALDO' => 120000000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SYNC_EKS',
            'NOJURNAL' => 'PJ202608000001',
            'USER' => $user->name,
        ]);

        $sales = Jurnal::query()->create([
            'no_jurnal' => 'PJ202608000001',
            'tanggal' => '2026-08-01',
            'tipe' => 'Penjualan',
            'keterangan' => 'Invoice FJ-TEST-001',
            'total_debit' => 120000000,
            'total_kredit' => 120000000,
            'referensi' => 'FJ-TEST-001',
            'created_by' => $user->id,
            'status' => 'Posted',
            'module_type' => 'Penjualan',
            'is_posted' => true,
            'posted_at' => now(),
        ]);
        JurnalItem::query()->create([
            'jurnal_id' => $sales->id,
            'coa_id' => $receivable->id,
            'debit' => 120000000,
            'kredit' => 0,
        ]);
        JurnalItem::query()->create([
            'jurnal_id' => $sales->id,
            'coa_id' => $bank->id,
            'debit' => 0,
            'kredit' => 120000000,
        ]);

        $manual = Jurnal::query()->create([
            'no_jurnal' => 'JU202608000130',
            'tanggal' => '2026-08-18',
            'tipe' => 'Jurnal Umum',
            'keterangan' => 'KEMBALI PINJ PMB',
            'total_debit' => 120000000,
            'total_kredit' => 120000000,
            'created_by' => $user->id,
            'status' => 'Posted',
            'module_type' => 'Jurnal Umum',
            'is_posted' => true,
            'posted_at' => now(),
        ]);
        JurnalItem::query()->create([
            'jurnal_id' => $manual->id,
            'coa_id' => $bank->id,
            'debit' => 120000000,
            'kredit' => 0,
        ]);
        JurnalItem::query()->create([
            'jurnal_id' => $manual->id,
            'coa_id' => $receivable->id,
            'debit' => 0,
            'kredit' => 120000000,
        ]);

        $controller = new AuditSelisihController(app(JournalQueryService::class));
        $method = new ReflectionMethod(AuditSelisihController::class, 'receivableReconciliationSuspects');
        $method->setAccessible(true);

        $suspects = $method->invoke($controller, '2026-08-19', $receivable->id, 120000000.0);

        $this->assertNotEmpty($suspects);
        $first = $suspects->first();
        $this->assertStringContainsString('JU202608000130', $first['title']);
        $this->assertStringContainsString('mariani', $first['description']);
        $this->assertStringContainsString('KEMBALI PINJ PMB', $first['description']);
        $this->assertSame(120000000.0, (float) $first['amount']);
        $this->assertSame(route('jurnal.show', $manual->id), $first['url']);
        $this->assertSame('Lacak Jurnal Selisih', $first['action']);
    }
}
