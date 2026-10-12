<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SinkronisasiPenjualanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('t_penjualan_hosting');
        Schema::dropIfExists('mcustomer');
        Schema::create('mcustomer', function (Blueprint $table) {
            $table->id();
            $table->string('CUSTOMER', 30)->unique();
            $table->string('kode_cus', 30)->nullable()->unique();
            $table->string('NAMACUST', 100)->nullable();
            $table->string('ALAMAT1', 200)->nullable();
            $table->char('SETATUS', 1)->nullable();
            $table->date('TGL_UPDATE')->nullable();
            $table->timestamps();
        });

        Schema::create('t_penjualan_hosting', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->string('customer')->nullable();
            $table->decimal('dpp', 15, 2)->nullable();
            $table->decimal('nilai_ppn', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->string('sumber')->nullable();
            $table->string('jenis_bayar')->nullable();
            $table->string('status_bayar')->nullable();
            $table->dateTime('tgl_kirim')->nullable();
            $table->dateTime('tgl_update')->nullable();
        });

        Schema::dropIfExists('arh');
        Schema::create('arh', function (Blueprint $table) {
            $table->id();
            $table->string('NOFAKTUR')->nullable();
            $table->string('CUSTOMER')->nullable();
        });
    }

    public function test_api_menyimpan_dan_memperbarui_penjualan_berdasarkan_invoice(): void
    {
        $payload = [
            'invoice' => 'INV-001',
            'customer' => 'CUST001',
            'dpp' => 100000,
            'nilai_ppn' => 11000,
            'total' => 111000,
            'sumber' => 'VFP',
            'jenis_bayar' => 'Tunai',
            'status_bayar' => 'Lunas',
            'tgl_kirim' => '2026-07-15 10:00:00',
            'tgl_update' => '2026-07-15 10:05:00',
        ];

        $this->postJson('/api/penjualan/sync', $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('invoice', 'INV-001');

        $payload['customer'] = 'CUST002';

        $this->postJson('/api/penjualan/sync', $payload)->assertOk();

        $this->assertDatabaseCount('t_penjualan_hosting', 1);
        $this->assertDatabaseHas('t_penjualan_hosting', [
            'invoice' => 'INV-001',
            'customer' => 'CUST002',
        ]);
    }

    public function test_api_memakai_customer_sebagai_kode_untuk_mencegah_customer_double_berdasarkan_nama(): void
    {
        $this->postJson('/api/penjualan/sync', [
            'invoice' => 'INV-SEA-001',
            'customer' => 'GSB001',
            'kode_cus' => 'TAMBAHAN-1',
            'namacust' => 'PT. GLOBAL SEAFOOD BALI',
            'dpp' => 100000,
            'nilai_ppn' => 11000,
            'total' => 111000,
            'tgl_kirim' => '2026-07-15 10:00:00',
        ])->assertOk();

        $this->postJson('/api/penjualan/sync', [
            'invoice' => 'INV-SEA-002',
            'customer' => 'GSB001',
            'kode_cus' => 'TAMBAHAN-2',
            'namacust' => 'PT GLOBAL SEAFOOD BALI',
            'dpp' => 200000,
            'nilai_ppn' => 22000,
            'total' => 222000,
            'tgl_kirim' => '2026-07-15 11:00:00',
        ])->assertOk();

        $this->assertDatabaseCount('mcustomer', 1);
        $this->assertDatabaseHas('mcustomer', [
            'CUSTOMER' => 'GSB001',
            'kode_cus' => 'TAMBAHAN-2',
            'NAMACUST' => 'PT GLOBAL SEAFOOD BALI',
        ]);
        $this->assertDatabaseMissing('mcustomer', [
            'CUSTOMER' => 'PT. GLOBAL SEAFOOD BALI',
        ]);
        $this->assertDatabaseHas('t_penjualan_hosting', [
            'invoice' => 'INV-SEA-001',
            'customer' => 'GSB001',
        ]);
        $this->assertDatabaseHas('t_penjualan_hosting', [
            'invoice' => 'INV-SEA-002',
            'customer' => 'GSB001',
        ]);
    }

    public function test_api_mencegah_customer_double_saat_kode_customer_foxpro_kepotong(): void
    {
        $this->postJson('/api/penjualan/sync', [
            'invoice' => 'INV-GSB-001',
            'customer' => 'DPS-GSB/GLOBALSEAFOOD',
            'namacust' => 'PT. GLOBAL SEAFOOD BALI',
            'dpp' => 100000,
            'nilai_ppn' => 11000,
            'total' => 111000,
            'tgl_kirim' => '2026-07-15 10:00:00',
        ])->assertOk();

        $this->postJson('/api/penjualan/sync', [
            'invoice' => 'INV-GSB-002',
            'customer' => 'DPS-GSB/GLOBALSEAFOO',
            'namacust' => 'PT. GLOBAL SEAFOOD BALI ',
            'alamat1' => 'DENPASAR',
            'dpp' => 200000,
            'nilai_ppn' => 22000,
            'total' => 222000,
            'tgl_kirim' => '2026-07-15 11:00:00',
        ])->assertOk();

        $this->assertDatabaseCount('mcustomer', 1);
        $this->assertDatabaseHas('mcustomer', [
            'CUSTOMER' => 'DPS-GSB/GLOBALSEAFOOD',
            'NAMACUST' => 'PT. GLOBAL SEAFOOD BALI',
            'ALAMAT1' => 'DENPASAR',
        ]);
        $this->assertDatabaseHas('t_penjualan_hosting', [
            'invoice' => 'INV-GSB-002',
            'customer' => 'DPS-GSB/GLOBALSEAFOOD',
        ]);
        $this->assertDatabaseMissing('mcustomer', [
            'CUSTOMER' => 'DPS-GSB/GLOBALSEAFOO',
        ]);
    }

    public function test_api_mengganti_kode_customer_kepotong_menjadi_kode_lengkap_saat_data_lengkap_masuk(): void
    {
        $this->postJson('/api/penjualan/sync', [
            'invoice' => 'INV-GSB-003',
            'customer' => 'DPS-GSB/GLOBALSEAFOO',
            'namacust' => 'PT. GLOBAL SEAFOOD BALI',
            'dpp' => 100000,
            'nilai_ppn' => 11000,
            'total' => 111000,
            'tgl_kirim' => '2026-07-15 10:00:00',
        ])->assertOk();

        DB::table('arh')->insert([
            'NOFAKTUR' => 'INV-LAMA',
            'CUSTOMER' => 'DPS-GSB/GLOBALSEAFOO',
        ]);

        $this->postJson('/api/penjualan/sync', [
            'invoice' => 'INV-GSB-004',
            'customer' => 'DPS-GSB/GLOBALSEAFOOD',
            'namacust' => 'PT. GLOBAL SEAFOOD BALI',
            'alamat1' => 'DENPASAR',
            'dpp' => 200000,
            'nilai_ppn' => 22000,
            'total' => 222000,
            'tgl_kirim' => '2026-07-15 11:00:00',
        ])->assertOk();

        $this->assertDatabaseCount('mcustomer', 1);
        $this->assertDatabaseHas('mcustomer', [
            'CUSTOMER' => 'DPS-GSB/GLOBALSEAFOOD',
            'ALAMAT1' => 'DENPASAR',
        ]);
        $this->assertDatabaseHas('t_penjualan_hosting', [
            'invoice' => 'INV-GSB-003',
            'customer' => 'DPS-GSB/GLOBALSEAFOOD',
        ]);
        $this->assertDatabaseHas('arh', [
            'NOFAKTUR' => 'INV-LAMA',
            'CUSTOMER' => 'DPS-GSB/GLOBALSEAFOOD',
        ]);
    }

    public function test_api_menolak_payload_tanpa_invoice(): void
    {
        $this->postJson('/api/penjualan/sync', ['customer' => 'Pelanggan A'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('invoice');
    }
}
