<?php

namespace Tests\Feature;

use App\Models\Coa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuditCoaSaldoNormalCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reports_suspicious_saldo_normal_without_updating_by_default(): void
    {
        $retainedEarnings = Coa::create([
            'kode_akun' => '3101',
            'nama_akun' => 'Laba Ditahan',
            'tipe' => 'Ekuitas',
            'kategori' => 'Modal',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $exitCode = Artisan::call('app:audit-coa-saldo-normal');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Laba Ditahan', Artisan::output());
        $this->assertSame('Debit', $retainedEarnings->fresh()->saldo_normal);
    }

    public function test_it_fixes_suspicious_saldo_normal_when_requested(): void
    {
        $liability = Coa::create([
            'kode_akun' => '2101',
            'nama_akun' => 'Utang Usaha',
            'tipe' => 'Kewajiban',
            'kategori' => 'Kewajiban Lancar',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Debit',
            'is_active' => true,
        ]);

        $prive = Coa::create([
            'kode_akun' => '3201',
            'nama_akun' => 'Prive Pemilik',
            'tipe' => 'Ekuitas',
            'kategori' => 'Prive',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $exitCode = Artisan::call('app:audit-coa-saldo-normal', ['--fix' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertSame('Kredit', $liability->fresh()->saldo_normal);
        $this->assertSame('Debit', $prive->fresh()->saldo_normal);
    }

    public function test_it_treats_deviden_as_contra_equity(): void
    {
        $deviden = Coa::create([
            'kode_akun' => '3301',
            'nama_akun' => 'Deviden',
            'tipe' => 'Ekuitas',
            'kategori' => 'Ekuitas',
            'level' => 'Akun Posting',
            'saldo_normal' => 'Kredit',
            'is_active' => true,
        ]);

        $exitCode = Artisan::call('app:audit-coa-saldo-normal', ['--fix' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertSame('Debit', $deviden->fresh()->saldo_normal);
    }
}
