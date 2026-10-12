<?php

namespace Tests\Feature;

use App\Models\Arh;
use App\Models\Barh;
use App\Models\Bard;
use App\Models\User;
use Database\Seeders\CoaSeeder;
use Database\Seeders\McustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelunasanPiutangTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test customer invoices endpoint returns formatted dates
     */
    public function test_customer_invoices_returns_formatted_dates(): void
    {
        $this->withoutMiddleware();
        $this->seed(CoaSeeder::class);
        $this->seed(McustomerSeeder::class);
        $user = User::factory()->create();

        // Create test invoice
        $arh = Arh::create([
            'NOFAKTUR' => 'INV-TEST-001',
            'TGLFAKTUR' => now(),
            'TGLJT' => now()->addDays(30),
            'CUSTOMER' => 'CUST001',
            'PIUTANG' => 5000000,
            'BAYAR' => 0,
            'SALDO' => 5000000,
            'STATUS' => 'OPEN',
            'USER' => $user->name,
        ]);

        // Test without authentication first
        $response = $this->getJson(route('pelunasan-piutang.customer-invoices', [], false) . '?customer=CUST001');

        if ($response->status() === 404) {
            $response = $this->getJson('/pelunasan-piutang/customer-invoices?customer=CUST001');
        }

        $response->assertOk()
            ->assertJsonCount(1);

        // Check dates are formatted and not empty
        $data = $response->json();
        $this->assertEquals('INV-TEST-001', $data[0]['NOFAKTUR']);
        $this->assertNotEmpty($data[0]['TGLFAKTUR'], 'TGLFAKTUR should not be empty');
        $this->assertNotEmpty($data[0]['TGLJT'], 'TGLJT should not be empty');
        $this->assertStringContainsString('/', $data[0]['TGLFAKTUR'], 'TGLFAKTUR should be formatted with /');
        $this->assertStringContainsString('/', $data[0]['TGLJT'], 'TGLJT should be formatted with /');
    }

    /**
     * Test create form renders without errors
     */
    public function test_create_form_renders(): void
    {
        $this->withoutMiddleware();

        $response = $this->get('/pelunasan-piutang/create');

        $response->assertStatus(200)
            ->assertSee('Pelunasan Piutang')
            ->assertSee('Customer')
            ->assertSee('Cari Invoice');
    }

    public function test_receipt_pdf_route_returns_a_pdf(): void
    {
        $this->withoutMiddleware();

        $invoice = Arh::create([
            'NOFAKTUR' => 'INV-PDF-001',
            'TGLFAKTUR' => now(),
            'CUSTOMER' => 'CUST-PDF',
            'PIUTANG' => 100000,
            'BAYAR' => 100000,
            'SALDO' => 0,
        ]);
        $payment = Barh::create([
            'NOBAYAR' => 'PAY-PDF-001',
            'TGLBAYAR' => now(),
            'CUSTOMER' => $invoice->CUSTOMER,
            'JUMLAH_BAYAR' => 100000,
            'METODE_PEMBAYARAN' => 'CASH',
            'USER' => 'Test User',
        ]);
        Bard::create([
            'NOBAYAR' => $payment->NOBAYAR,
            'NOFAKTUR' => $invoice->NOFAKTUR,
            'TGLFAKTUR' => $invoice->TGLFAKTUR,
            'SALDO_FAKTUR' => 100000,
            'NILAI_BAYAR' => 100000,
            'SISA_FAKTUR' => 0,
        ]);

        $response = $this->get("/pelunasan-piutang/{$payment->id}/pdf");

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
