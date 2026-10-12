<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Driver;
use App\Models\DriverReceivable;
use App\Models\DriverReceivablePayment;
use App\Models\Jurnal;
use App\Models\User;
use App\Http\Middleware\CheckPermission;
use App\Services\JournalService;
use App\Services\PaymentFormTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverPaymentEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_payment_store_creates_posted_journal(): void
    {
        $this->withoutMiddleware(CheckPermission::class);

        $user = User::factory()->create();
        $cash = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Debit']);
        $receivableCoa = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Debit']);
        $driver = Driver::create(['code' => 'DRV001', 'name' => 'Sopir Test', 'is_active' => true]);
        $receivable = DriverReceivable::create([
            'number' => 'PS-STORE-001',
            'date' => '2026-07-01',
            'due_date' => '2026-07-31',
            'driver_id' => $driver->id,
            'type' => 'KAS_BON',
            'amount' => 100000,
            'paid' => 0,
            'balance' => 100000,
            'status' => 'OPEN',
            'receivable_coa_id' => $receivableCoa->id,
            'counter_coa_id' => $cash->id,
            'created_by' => $user->id,
        ]);

        $payload = [
            'driver_receivable_id' => $receivable->id,
            'date' => '2026-07-02',
            'method' => 'CASH',
            'amount' => 60000,
            'payment_coa_id' => $cash->id,
            'reference' => 'PAY-001',
            'description' => 'Bayar kas bon sopir',
            '_payment_token' => 'payment-token-1',
        ];

        $response = $this->actingAs($user)
            ->withSession(['driver_payment_form_tokens' => ['payment-token-1']])
            ->post(route('driver-payments.store'), $payload);

        $response->assertRedirect(route('driver-payments.index'));

        $payment = DriverReceivablePayment::firstOrFail();
        $journal = Jurnal::findOrFail($payment->journal_id);

        $this->assertSame('POSTED', $payment->status);
        $this->assertSame('Pelunasan Piutang Sopir', $journal->module_type);
        $this->assertSame('Pelunasan Piutang', $journal->tipe);
        $this->assertEquals(60000, (float) $journal->total_debit);
        $this->assertEquals(60000, (float) $journal->total_kredit);
        $this->assertCount(2, $journal->items);

        $receivable->refresh();
        $this->assertEquals(60000, (float) $receivable->paid);
        $this->assertEquals(40000, (float) $receivable->balance);
    }

    public function test_driver_payment_store_ignores_duplicate_form_submission(): void
    {
        $this->withoutMiddleware(CheckPermission::class);

        $user = User::factory()->create();
        $cash = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Debit']);
        $receivableCoa = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Debit']);
        $driver = Driver::create(['code' => 'DRV002', 'name' => 'Sopir Double', 'is_active' => true]);
        $receivable = DriverReceivable::create([
            'number' => 'PS-DOUBLE-001',
            'date' => '2026-07-01',
            'due_date' => '2026-07-31',
            'driver_id' => $driver->id,
            'type' => 'KAS_BON',
            'amount' => 100000,
            'paid' => 0,
            'balance' => 100000,
            'status' => 'OPEN',
            'receivable_coa_id' => $receivableCoa->id,
            'counter_coa_id' => $cash->id,
            'created_by' => $user->id,
        ]);
        $payload = [
            'driver_receivable_id' => $receivable->id,
            'date' => '2026-07-02',
            'method' => 'CASH',
            'amount' => 40000,
            'payment_coa_id' => $cash->id,
            'reference' => 'PAY-DOUBLE',
            'description' => 'Klik simpan dua kali',
            '_payment_token' => 'payment-token-double',
        ];

        $token = app(PaymentFormTokenService::class)
            ->issue('driver_payment_form_tokens', $user->id);
        $payload['_payment_token'] = $token;

        $this->actingAs($user)
            ->post(route('driver-payments.store'), $payload)
            ->assertRedirect(route('driver-payments.index'))
            ->assertSessionHas('success', 'Pelunasan dan jurnal berhasil disimpan.');

        $this->actingAs($user)
            ->post(route('driver-payments.store'), $payload)
            ->assertRedirect(route('driver-payments.index'))
            ->assertSessionHas('success', 'Pelunasan dan jurnal berhasil disimpan.');

        $this->assertSame(1, DriverReceivablePayment::count());
        $this->assertSame(1, Jurnal::where('module_type', 'Pelunasan Piutang Sopir')->count());
        $receivable->refresh();
        $this->assertEquals(40000, (float) $receivable->paid);
        $this->assertEquals(60000, (float) $receivable->balance);
    }

    public function test_driver_payment_edit_recalculates_receivable_balance_and_updates_journal(): void
    {
        $this->withoutMiddleware(CheckPermission::class);

        $user = User::factory()->create();
        $cash = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Debit']);
        $receivableCoa = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Debit']);
        $driver = Driver::create(['code' => 'DRV001', 'name' => 'Sopir Test', 'is_active' => true]);
        $receivable = DriverReceivable::create([
            'number' => 'PS-001',
            'date' => '2026-07-01',
            'due_date' => '2026-07-31',
            'driver_id' => $driver->id,
            'type' => 'OPERASIONAL',
            'amount' => 100000,
            'paid' => 40000,
            'balance' => 60000,
            'status' => 'OPEN',
            'receivable_coa_id' => $receivableCoa->id,
            'counter_coa_id' => $cash->id,
            'created_by' => $user->id,
        ]);

        $journal = app(JournalService::class)->createJournal(
            'BPS260701001',
            '2026-07-01',
            'Pelunasan piutang sopir PS-001',
            [
                ['coa_id' => $cash->id, 'debit' => 40000, 'kredit' => 0, 'deskripsi' => 'Penerimaan BPS260701001'],
                ['coa_id' => $receivableCoa->id, 'debit' => 0, 'kredit' => 40000, 'deskripsi' => 'Pelunasan PS-001'],
            ],
            'BPS260701001',
            $user->id,
            'Pelunasan Piutang Sopir',
            'Pelunasan Piutang'
        );
        $payment = DriverReceivablePayment::create([
            'number' => 'BPS260701001',
            'driver_receivable_id' => $receivable->id,
            'date' => '2026-07-01',
            'method' => 'CASH',
            'amount' => 40000,
            'payment_coa_id' => $cash->id,
            'journal_id' => $journal->id,
            'status' => 'POSTED',
            'created_by' => $user->id,
        ]);
        $response = $this->actingAs($user)->put(route('driver-payments.update', $payment), [
            'driver_receivable_id' => $receivable->id,
            'date' => '2026-07-02',
            'method' => 'CASH',
            'amount' => 70000,
            'payment_coa_id' => $cash->id,
            'reference' => 'EDIT-001',
            'description' => 'Edit pembayaran',
        ]);

        $response->assertRedirect(route('driver-payments.index'));

        $receivable->refresh();
        $payment->refresh();
        $journal->refresh();

        $this->assertEquals(70000, (float) $payment->amount);
        $this->assertEquals(70000, (float) $receivable->paid);
        $this->assertEquals(30000, (float) $receivable->balance);
        $this->assertEquals(70000, (float) $journal->total_debit);
        $this->assertEquals(70000, (float) $journal->total_kredit);
        $this->assertEquals('2026-07-02', $journal->tanggal->toDateString());
    }
}
