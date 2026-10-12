<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckPermission;
use App\Models\Coa;
use App\Models\Driver;
use App\Models\DriverReceivable;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverReceivableStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_receivable_store_creates_journal_with_valid_tipe(): void
    {
        $this->withoutMiddleware(CheckPermission::class);

        $user = User::factory()->create();
        $receivableCoa = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Debit']);
        $counterCoa = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Kredit']);
        $driver = Driver::create(['code' => 'DRV001', 'name' => 'Sopir Test', 'is_active' => true]);

        $response = $this->actingAs($user)->post(route('driver-receivables.store'), [
            'number' => 'PS-TEST-001',
            'date' => '2026-07-21',
            'due_date' => '2026-08-21',
            'driver_id' => $driver->id,
            'type' => 'KAS_BON',
            'vehicle_number' => 'DD1234AA',
            'reference' => 'REF-001',
            'amount' => 1000000,
            'description' => 'Kas bon sopir',
            'receivable_coa_id' => $receivableCoa->id,
            'counter_coa_id' => $counterCoa->id,
        ]);

        $response->assertRedirect(route('driver-receivables.index'));

        $receivable = DriverReceivable::where('number', 'PS-TEST-001')->firstOrFail();
        $journal = Jurnal::findOrFail($receivable->journal_id);

        $this->assertEquals('Piutang Sopir', $journal->module_type);
        $this->assertEquals('Jurnal Umum', $journal->tipe);
        $this->assertEquals(1000000, (float) $receivable->amount);
        $this->assertEquals(1000000, (float) $receivable->balance);
    }

    public function test_driver_receivable_destroy_deletes_receivable_before_journal(): void
    {
        $this->withoutMiddleware(CheckPermission::class);

        $user = User::factory()->create();
        $receivableCoa = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Debit']);
        $counterCoa = Coa::factory()->create(['level' => 'Akun Posting', 'saldo_normal' => 'Kredit']);
        $driver = Driver::create(['code' => 'DRV002', 'name' => 'Sopir Hapus', 'is_active' => true]);

        $this->actingAs($user)->post(route('driver-receivables.store'), [
            'number' => 'PS-DELETE-001',
            'date' => '2026-07-21',
            'due_date' => '2026-08-21',
            'driver_id' => $driver->id,
            'type' => 'KAS_BON',
            'amount' => 750000,
            'description' => 'Kas bon sopir untuk dihapus',
            'receivable_coa_id' => $receivableCoa->id,
            'counter_coa_id' => $counterCoa->id,
        ])->assertRedirect(route('driver-receivables.index'));

        $receivable = DriverReceivable::where('number', 'PS-DELETE-001')->firstOrFail();
        $journalId = $receivable->journal_id;
        $this->assertSame(2, Jurnal::findOrFail($journalId)->items()->count());

        $this->actingAs($user)
            ->delete(route('driver-receivables.destroy', $receivable))
            ->assertRedirect()
            ->assertSessionHas('success', 'Piutang sopir dan jurnal berhasil dihapus.');

        $this->assertDatabaseMissing('driver_receivables', ['id' => $receivable->id]);
        $this->assertDatabaseMissing('jurnals', ['id' => $journalId]);
        $this->assertDatabaseMissing('jurnal_items', ['jurnal_id' => $journalId]);
    }
}
