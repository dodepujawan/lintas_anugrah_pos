<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JurnalUmumReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_jurnal_umum_filter_excludes_jurnal_penyesuaian(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $coa = Coa::create([
            'kode_akun' => '1110',
            'nama_akun' => 'Kas',
            'tipe' => 'Aset',
            'kategori' => 'Kas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $jurnalUmum = Jurnal::create([
            'no_jurnal' => 'JU-TEST-001',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Jurnal Umum Test',
            'total_debit' => 100000,
            'total_kredit' => 100000,
            'referensi' => 'REF-001',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnalUmum->items()->create([
            'coa_id' => $coa->id,
            'deskripsi' => 'Uang masuk',
            'debit' => 100000,
            'kredit' => 0,
        ]);

        $jurnalPenyesuaian = Jurnal::create([
            'no_jurnal' => 'JP-TEST-001',
            'tanggal' => now()->toDateString(),
            'status' => Jurnal::STATUS_POSTED,
            'keterangan' => 'Jurnal Penyesuaian Test',
            'total_debit' => 0,
            'total_kredit' => 100000,
            'referensi' => 'REF-002',
            'created_by' => $user->id,
            'is_posted' => true,
        ]);

        $jurnalPenyesuaian->items()->create([
            'coa_id' => $coa->id,
            'deskripsi' => 'Penyesuaian',
            'debit' => 0,
            'kredit' => 100000,
        ]);

        $response = $this->actingAs($user)
            ->get(route('laporan.jurnal_umum', [
                'tanggal_awal' => now()->startOfMonth()->format('Y-m-d'),
                'tanggal_akhir' => now()->endOfMonth()->format('Y-m-d'),
                'jenis' => 'Jurnal Umum',
            ]));

        $response->assertStatus(200);
        $response->assertSee('JU-TEST-001');
        $response->assertDontSee('JP-TEST-001');
    }
}
