<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiutangFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that create form contains required fields
     */
    public function test_create_form_has_nofaktur_tglfaktur_tgljt_fields(): void
    {
        $this->withoutMiddleware();

        $response = $this->get('/piutang/create');

        $response->assertStatus(200);
        $response->assertSee('name="nofaktur"', false);
        $response->assertSee('name="tglfaktur"', false);
        $response->assertSee('name="tgljt"', false);
        $response->assertSee('name="pelanggan"', false);
        $response->assertSee('name="nominal"', false);
        $response->assertSee('No. Faktur');
        $response->assertSee('Tanggal Faktur');
        $response->assertSee('Tanggal Jatuh Tempo');
    }

    /**
     * Test form validation for new fields
     */
    public function test_store_requires_nofaktur_tglfaktur_tgljt(): void
    {
        $this->withoutMiddleware();

        // Missing required fields
        $response = $this->post('/piutang', [
            'pelanggan' => 'PT Test',
            'nominal' => 1000000,
        ]);

        $response->assertSessionHasErrors(['nofaktur', 'tglfaktur', 'tgljt']);
    }

    /**
     * Test NOFAKTUR must be unique
     */
    public function test_nofaktur_must_be_unique(): void
    {
        $this->withoutMiddleware();
        $user = \App\Models\User::factory()->create();

        // First submission with INV-001
        $this->actingAs($user)->post('/piutang', [
            'nofaktur' => 'INV-001',
            'tglfaktur' => now()->toDateString(),
            'tgljt' => now()->addDays(30)->toDateString(),
            'pelanggan' => 'PT Maju Jaya',
            'nominal' => 1000000,
        ])->assertRedirect();

        // Second submission with same NOFAKTUR should fail
        $response = $this->actingAs($user)->post('/piutang', [
            'nofaktur' => 'INV-001',
            'tglfaktur' => now()->toDateString(),
            'tgljt' => now()->addDays(30)->toDateString(),
            'pelanggan' => 'PT Lain',
            'nominal' => 5000000,
        ]);

        $response->assertSessionHasErrors('nofaktur');
    }

    /**
     * Test TGLJT must be after or equal to TGLFAKTUR
     */
    public function test_tgljt_must_be_after_tglfaktur(): void
    {
        $this->withoutMiddleware();
        $user = \App\Models\User::factory()->create();

        // TGLJT is before TGLFAKTUR
        $response = $this->actingAs($user)->post('/piutang', [
            'nofaktur' => 'INV-002',
            'tglfaktur' => now()->toDateString(),
            'tgljt' => now()->subDays(1)->toDateString(),
            'pelanggan' => 'PT Test',
            'nominal' => 1000000,
        ]);

        $response->assertSessionHasErrors('tgljt');
    }
}
