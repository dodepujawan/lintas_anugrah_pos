<?php

namespace Tests\Feature;

use App\Models\AccountMapping;
use App\Models\Arh;
use App\Models\Bard;
use App\Models\Barh;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PenjualanHostingRepostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        if (! Schema::hasTable('t_penjualan_hosting')) {
            Schema::create('t_penjualan_hosting', function (Blueprint $table): void {
                $table->id();
                $table->string('invoice')->unique();
                $table->string('customer', 30)->nullable();
                $table->string('namacust')->nullable();
                $table->string('alamat1')->nullable();
                $table->string('jenis')->nullable();
                $table->date('tglinvoice')->nullable();
                $table->date('tgl_kirim')->nullable();
                $table->decimal('dpp', 15, 2)->default(0);
                $table->decimal('nilai_ppn', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->string('status_jurnal', 30)->nullable();
                $table->string('no_jurnal', 50)->nullable();
                $table->unsignedBigInteger('jurnal_id')->nullable();
                $table->timestamp('posted_at')->nullable();
                $table->unsignedBigInteger('posted_by')->nullable();
                $table->text('posting_error')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_repost_allows_partially_paid_invoice_and_recalculates_balance(): void
    {
        $user = User::factory()->create();
        [$receivable, $revenue] = $this->createMappings();
        DB::table('mcustomer')->insert([
            'CUSTOMER' => 'CUST001',
            'NAMACUST' => 'Customer Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $journal = Jurnal::query()->create([
            'no_jurnal' => 'PJ260701001',
            'tanggal' => '2026-07-01',
            'keterangan' => 'Pendapatan lama',
            'referensi' => 'FJ20260003570',
            'total_debit' => 1000000,
            'total_kredit' => 1000000,
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);
        $journal->items()->create(['coa_id' => $receivable->id, 'debit' => 1000000, 'kredit' => 0, 'deskripsi' => 'Piutang lama']);
        $journal->items()->create(['coa_id' => $revenue->id, 'debit' => 0, 'kredit' => 1000000, 'deskripsi' => 'Pendapatan lama']);

        DB::table('t_penjualan_hosting')->insert([
            'invoice' => 'FJ20260003570',
            'customer' => 'CUST001',
            'namacust' => 'Customer Test',
            'jenis' => 'EKS',
            'tglinvoice' => '2026-07-01',
            'tgl_kirim' => '2026-07-01',
            'dpp' => 1200000,
            'nilai_ppn' => 0,
            'total' => 1200000,
            'status_jurnal' => 'POSTED',
            'no_jurnal' => 'PJ260701001',
            'jurnal_id' => $journal->id,
        ]);

        Arh::query()->create([
            'NOFAKTUR' => 'FJ20260003570',
            'TGLFAKTUR' => '2026-07-01',
            'TGLJT' => '2026-07-31',
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 1000000,
            'BAYAR' => 300000,
            'RETUR' => 0,
            'DISCOUNT' => 0,
            'SALDO' => 700000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SYNC_EKS',
            'NOJURNAL' => 'PJ260701001',
            'USER' => $user->name,
        ]);
        Barh::query()->create([
            'NOBAYAR' => 'AR260700001',
            'TGLBAYAR' => '2026-07-05',
            'CUSTOMER' => 'CUST001',
            'JUMLAH_BAYAR' => 300000,
            'METODE_PEMBAYARAN' => 'CASH',
            'USER' => $user->name,
        ]);
        Bard::query()->create([
            'NOBAYAR' => 'AR260700001',
            'NOFAKTUR' => 'FJ20260003570',
            'TGLFAKTUR' => '2026-07-01',
            'SALDO_FAKTUR' => 1000000,
            'NILAI_BAYAR' => 300000,
            'PPH23' => 0,
            'DISCOUNT' => 0,
            'BANK_ADMIN' => 0,
            'RETUR' => 0,
            'SISA_FAKTUR' => 700000,
        ]);

        $this->actingAs($user)
            ->from(route('penjualan-hosting.index'))
            ->post(route('penjualan-hosting.repost', 'FJ20260003570'), ['due_days' => 30])
            ->assertRedirect(route('penjualan-hosting.index'));

        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => 'FJ20260003570',
            'PIUTANG' => 1200000,
            'BAYAR' => 300000,
            'SALDO' => 900000,
            'STATUS' => 'OPEN',
        ]);
        $this->assertDatabaseHas('jurnals', [
            'id' => $journal->id,
            'total_debit' => 1200000,
            'total_kredit' => 1200000,
        ]);
    }

    public function test_repost_rejects_when_corrected_total_is_below_settled_amount(): void
    {
        $user = User::factory()->create();
        [$receivable, $revenue] = $this->createMappings();

        DB::table('mcustomer')->insert([
            'CUSTOMER' => 'CUST001',
            'NAMACUST' => 'Customer Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $journal = Jurnal::query()->create([
            'no_jurnal' => 'PJ260701002',
            'tanggal' => '2026-07-01',
            'keterangan' => 'Pendapatan lama',
            'referensi' => 'FJ-LOW-001',
            'total_debit' => 1000000,
            'total_kredit' => 1000000,
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);
        $journal->items()->create(['coa_id' => $receivable->id, 'debit' => 1000000, 'kredit' => 0, 'deskripsi' => 'Piutang lama']);
        $journal->items()->create(['coa_id' => $revenue->id, 'debit' => 0, 'kredit' => 1000000, 'deskripsi' => 'Pendapatan lama']);

        DB::table('t_penjualan_hosting')->insert([
            'invoice' => 'FJ-LOW-001',
            'customer' => 'CUST001',
            'namacust' => 'Customer Test',
            'jenis' => 'EKS',
            'tglinvoice' => '2026-07-01',
            'tgl_kirim' => '2026-07-01',
            'dpp' => 200000,
            'nilai_ppn' => 0,
            'total' => 200000,
            'status_jurnal' => 'POSTED',
            'no_jurnal' => 'PJ260701002',
            'jurnal_id' => $journal->id,
        ]);
        Arh::query()->create([
            'NOFAKTUR' => 'FJ-LOW-001',
            'TGLFAKTUR' => '2026-07-01',
            'TGLJT' => '2026-07-31',
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 1000000,
            'BAYAR' => 300000,
            'RETUR' => 0,
            'DISCOUNT' => 0,
            'SALDO' => 700000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SYNC_EKS',
            'NOJURNAL' => 'PJ260701002',
            'USER' => $user->name,
        ]);

        $response = $this->actingAs($user)
            ->from(route('penjualan-hosting.index'))
            ->post(route('penjualan-hosting.repost', 'FJ-LOW-001'), ['due_days' => 30]);

        $response->assertRedirect(route('penjualan-hosting.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => 'FJ-LOW-001',
            'PIUTANG' => 1000000,
            'SALDO' => 700000,
        ]);
    }

    public function test_repost_ignores_voided_payment_details(): void
    {
        $user = User::factory()->create();
        [$receivable, $revenue] = $this->createMappings();
        DB::table('mcustomer')->insert([
            'CUSTOMER' => 'CUST001',
            'NAMACUST' => 'Customer Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $journal = Jurnal::query()->create([
            'no_jurnal' => 'PJ260701003',
            'tanggal' => '2026-07-01',
            'keterangan' => 'Pendapatan lama',
            'referensi' => 'FJ-VOID-001',
            'total_debit' => 1000000,
            'total_kredit' => 1000000,
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);
        $journal->items()->create(['coa_id' => $receivable->id, 'debit' => 1000000, 'kredit' => 0, 'deskripsi' => 'Piutang lama']);
        $journal->items()->create(['coa_id' => $revenue->id, 'debit' => 0, 'kredit' => 1000000, 'deskripsi' => 'Pendapatan lama']);

        DB::table('t_penjualan_hosting')->insert([
            'invoice' => 'FJ-VOID-001',
            'customer' => 'CUST001',
            'namacust' => 'Customer Test',
            'jenis' => 'EKS',
            'tglinvoice' => '2026-07-01',
            'tgl_kirim' => '2026-07-01',
            'dpp' => 100000,
            'nilai_ppn' => 0,
            'total' => 100000,
            'status_jurnal' => 'POSTED',
            'no_jurnal' => 'PJ260701003',
            'jurnal_id' => $journal->id,
        ]);
        Arh::query()->create([
            'NOFAKTUR' => 'FJ-VOID-001',
            'TGLFAKTUR' => '2026-07-01',
            'TGLJT' => '2026-07-31',
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 1000000,
            'BAYAR' => 0,
            'RETUR' => 0,
            'DISCOUNT' => 0,
            'SALDO' => 1000000,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SYNC_EKS',
            'NOJURNAL' => 'PJ260701003',
            'USER' => $user->name,
        ]);
        Barh::query()->create([
            'NOBAYAR' => 'AR260700002',
            'TGLBAYAR' => '2026-07-05',
            'CUSTOMER' => 'CUST001',
            'JUMLAH_BAYAR' => 900000,
            'METODE_PEMBAYARAN' => 'CASH',
            'STATUS' => 'VOID',
            'USER' => $user->name,
        ]);
        Bard::query()->create([
            'NOBAYAR' => 'AR260700002',
            'NOFAKTUR' => 'FJ-VOID-001',
            'TGLFAKTUR' => '2026-07-01',
            'SALDO_FAKTUR' => 1000000,
            'NILAI_BAYAR' => 900000,
            'PPH23' => 0,
            'DISCOUNT' => 0,
            'BANK_ADMIN' => 0,
            'RETUR' => 0,
            'SISA_FAKTUR' => 100000,
        ]);

        $this->actingAs($user)
            ->from(route('penjualan-hosting.index'))
            ->post(route('penjualan-hosting.repost', 'FJ-VOID-001'), ['due_days' => 30])
            ->assertRedirect(route('penjualan-hosting.index'));

        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => 'FJ-VOID-001',
            'PIUTANG' => 100000,
            'SALDO' => 100000,
        ]);
    }

    private function createMappings(): array
    {
        $receivable = Coa::query()->create([
            'kode_akun' => '11020000',
            'nama_akun' => 'Piutang Usaha',
            'tipe' => 'Aset',
            'kategori' => 'Piutang',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);
        $revenue = Coa::query()->create([
            'kode_akun' => '41010000',
            'nama_akun' => 'Pendapatan Ekspedisi',
            'tipe' => 'Pendapatan',
            'kategori' => 'Pendapatan',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        AccountMapping::query()->create(['mapping_key' => 'accounts_receivable', 'account_id' => $receivable->id]);
        AccountMapping::query()->create(['mapping_key' => 'sales_revenue_eks', 'account_id' => $revenue->id]);

        return [$receivable, $revenue];
    }
}
