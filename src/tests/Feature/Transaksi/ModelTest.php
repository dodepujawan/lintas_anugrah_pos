<?php

namespace Tests\Feature\Transaksi;

use Tests\TestCase;
use App\Models\Expedisi;
use App\Models\Kendaraan;
use App\Models\Price;
use App\Models\Coolroom;
use App\Models\Service;
use App\Models\Area;
use App\Models\Pajak;
use App\Models\Rekening;
use App\Models\Signature;
use App\Models\Kwitansi;
use App\Models\Arh;

class ModelTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** @test */
    public function expedisi_model_bisa_create_dengan_fillable()
    {
        $expedisi = Expedisi::create([
            'NOSJ' => 'SJ001',
            'TGLMUAT' => '2026-10-12',
            'CUSTOMER' => 'C001',
            'CUSTOMER_KODE' => 'C001',
            'JENIS' => 'EKS',
            'type' => 'E',
            'JUMLAH' => 100,
            'HARGA' => 5000,
            'GRAND' => 500000,
            'USER' => 'admin',
            'USEREDIT' => 'admin',
        ]);

        $this->assertDatabaseHas('expedisis', [
            'NOSJ' => 'SJ001',
            'JENIS' => 'EKS',
        ]);
    }

    /** @test */
    public function kendaraan_model_bisa_create()
    {
        $kendaraan = Kendaraan::create([
            'KODE' => 'KEND001',
            'NAMA' => 'TOYOTA Hilux',
            'PLAT' => 'B 1234 CD',
            'FNO_PRK_B' => 'PRKB001',
            'JENIS' => 'E',
        ]);

        $this->assertDatabaseHas('kendaraans', [
            'KODE' => 'KEND001',
            'NAMA' => 'TOYOTA Hilux',
        ]);
    }

    /** @test */
    public function price_model_berhasil_create()
    {
        $price = Price::create([
            'FROM' => 'JAKARTA',
            'TO' => 'BANDUNG',
            'HARGA' => 500000,
        ]);

        $this->assertDatabaseHas('prices', [
            'FROM' => 'JAKARTA',
            'TO' => 'BANDUNG',
            'HARGA' => 500000,
        ]);
    }

    /** @test */
    public function coolroom_model_bisa_create()
    {
        $coolroom = Coolroom::create([
            'TGLMUAT' => '2026-10-12',
            'NOSJ' => 'CR001',
            'CUSTOMER' => 'C001',
            'NAMACUST' => 'Test Customer',
            'GRAND' => 1000000,
        ]);

        $this->assertDatabaseHas('coolrooms', [
            'NOSJ' => 'CR001',
            'CUSTOMER' => 'C001',
        ]);
    }

    /** @test */
    public function area_model_bisa_create()
    {
        $area = Area::create([
            'KODE' => 'A001',
            'NAMA' => 'Jakarta Pusat',
            'CABANG' => '01',
        ]);

        $this->assertDatabaseHas('areas', [
            'KODE' => 'A001',
            'NAMA' => 'Jakarta Pusat',
        ]);
    }

    /** @test */
    public function arh_model_membuat_invoice()
    {
        $arh = Arh::create([
            'NOFAKTUR' => 'INV-001',
            'TGLFAKTUR' => '2026-10-12',
            'CUSTOMER' => 'C001',
            'PIUTANG' => 1000000,
            'BAYAR' => 0,
            'SALDO' => 1000000,
            'STATUS' => 'OPEN',
        ]);

        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => 'INV-001',
            'CUSTOMER' => 'C001',
            'PIUTANG' => 1000000,
        ]);
    }
}
