<?php

namespace Tests\Feature;

use App\Models\Arh;
use App\Models\Bard;
use App\Models\Barh;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanPiutangKartuFakturTest extends TestCase
{
    use RefreshDatabase;

    public function test_kartu_faktur_shows_invoice_and_payment_lines(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();

        Arh::create([
            'NOFAKTUR' => 'FJ20260003788',
            'TGLFAKTUR' => '2026-07-27',
            'TGLJT' => '2026-08-27',
            'CUSTOMER' => 'SBY-WAHANABERKAH',
            'PIUTANG' => 7582500,
            'BAYAR' => 0,
            'SALDO' => 7582500,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SYNC_EKS',
            'USER' => $user->name,
        ]);

        Barh::create([
            'NOBAYAR' => 'AR260800001',
            'TGLBAYAR' => '2026-08-10',
            'CUSTOMER' => 'SBY-WAHANABERKAH',
            'JUMLAH_BAYAR' => 1000000,
            'METODE_PEMBAYARAN' => 'TRANSFER',
            'STATUS' => 'ACTIVE',
            'USER' => $user->name,
        ]);

        Bard::create([
            'NOBAYAR' => 'AR260800001',
            'NOFAKTUR' => 'FJ20260003788',
            'TGLFAKTUR' => '2026-07-27',
            'SALDO_FAKTUR' => 7582500,
            'NILAI_BAYAR' => 1000000,
            'PPH23' => 0,
            'DISCOUNT' => 0,
            'BANK_ADMIN' => 0,
            'RETUR' => 0,
            'SISA_FAKTUR' => 6582500,
        ]);

        $response = $this->actingAs($user)->get(route('laporan.piutang', [
            'section' => 'kartu',
            'nofaktur' => 'FJ20260003788',
            'tanggal_posisi' => '2026-08-20',
        ]));

        $response->assertOk();
        $response->assertSeeText('FJ20260003788');
        $response->assertSeeText('AR260800001');
        $response->assertSeeText('Edit Pelunasan');
        $response->assertSeeText('Penambahan piutang');
    }

    public function test_kartu_faktur_standard_pdf_streams_successfully(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();

        Arh::create([
            'NOFAKTUR' => 'FJ20260003788',
            'TGLFAKTUR' => '2026-07-27',
            'TGLJT' => '2026-08-27',
            'CUSTOMER' => 'SBY-WAHANABERKAH',
            'PIUTANG' => 7582500,
            'BAYAR' => 0,
            'SALDO' => 7582500,
            'STATUS' => 'OPEN',
            'SOURCE' => 'SYNC_EKS',
            'USER' => $user->name,
        ]);

        $response = $this->actingAs($user)->get(route('laporan.piutang.pdf', [
            'section' => 'kartu',
            'nofaktur' => 'FJ20260003788',
            'tanggal_posisi' => '2026-08-20',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
