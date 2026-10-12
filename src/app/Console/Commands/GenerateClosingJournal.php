<?php

namespace App\Console\Commands;

use App\Models\AccountMapping;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Services\TutupBukuReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateClosingJournal extends Command
{
    protected $signature = 'app:generate-closing-journal
        {--tanggal-awal= : Tanggal awal periode tutup buku}
        {--tanggal-akhir= : Tanggal akhir periode tutup buku}
        {--keterangan= : Keterangan jurnal tutup otomatis}
        {--no-jurnal= : Nomor jurnal manual}
        {--created-by=1 : ID user pembuat jurnal}';

    protected $description = 'Generate dan posting jurnal tutup otomatis berdasarkan laporan tutup buku.';

    public function handle(): int
    {
        $defaultYear = $this->option('tanggal-akhir')
            ? Carbon::parse($this->option('tanggal-akhir'))->year
            : now()->year;
        $tanggalAwal = $this->option('tanggal-awal') ?? Carbon::create($defaultYear, 1, 1)->format('Y-m-d');
        $tanggalAkhir = $this->option('tanggal-akhir') ?? Carbon::create($defaultYear, 12, 31)->format('Y-m-d');
        $keterangan = $this->option('keterangan') ?: 'Jurnal Tutup Otomatis';
        $noJurnal = $this->option('no-jurnal') ?: $this->generateNoJurnal($tanggalAkhir);
        $createdBy = (int) $this->option('created-by');

        $start = Carbon::parse($tanggalAwal);
        $end = Carbon::parse($tanggalAkhir);

        if ($start->year !== $end->year
            || ! $start->isSameDay(Carbon::create($start->year, 1, 1))
            || ! $end->isSameDay(Carbon::create($end->year, 12, 31))) {
            $this->error('Jurnal penutup hanya dapat dibuat untuk periode penuh 1 Januari sampai 31 Desember.');
            return self::FAILURE;
        }

        if (! Carbon::today()->greaterThan($end->copy()->startOfDay())) {
            $this->error('Tahun masih berjalan. Jurnal penutup baru dapat dibuat mulai 1 Januari ' . ($end->year + 1) . '.');
            return self::FAILURE;
        }

        $report = app(TutupBukuReportService::class)->build($tanggalAwal, $tanggalAkhir);

        $currentYearProfitAccount = $report['mappingAccounts']['current_year_profit'] ?? null;
        $retainedEarningsAccount = $report['mappingAccounts']['retained_earnings'] ?? null;

        if (! $currentYearProfitAccount instanceof Coa || ! $retainedEarningsAccount instanceof Coa) {
            $this->error('Akun mapping Laba Tahun Berjalan atau Laba Ditahan belum tersedia.');
            return self::FAILURE;
        }

        foreach ([
            'Laba Tahun Berjalan' => $currentYearProfitAccount,
            'Laba Ditahan' => $retainedEarningsAccount,
        ] as $label => $account) {
            if ($account->tipe !== 'Ekuitas' || strtolower((string) $account->saldo_normal) !== 'kredit') {
                $this->error("Akun mapping {$label} harus bertipe Ekuitas dan saldo normal Kredit.");
                return self::FAILURE;
            }
        }

        DB::beginTransaction();

        try {
            $items = [];

            foreach ($report['pendapatanRows'] as $row) {
                $amount = (float) $row['amount'];
                if (abs($amount) < 1) {
                    continue;
                }

                if ($amount > 0) {
                    $items[] = [
                        'coa_id' => $row['coa']->id,
                        'deskripsi' => 'Penutupan akun pendapatan',
                        'debit' => $amount,
                        'kredit' => 0,
                    ];
                    $items[] = [
                        'coa_id' => $currentYearProfitAccount->id,
                        'deskripsi' => 'Penutupan akun pendapatan ke laba tahun berjalan',
                        'debit' => 0,
                        'kredit' => $amount,
                    ];
                } else {
                    $amount = abs($amount);
                    $items[] = [
                        'coa_id' => $row['coa']->id,
                        'deskripsi' => 'Penutupan saldo debit akun pendapatan',
                        'debit' => 0,
                        'kredit' => $amount,
                    ];
                    $items[] = [
                        'coa_id' => $currentYearProfitAccount->id,
                        'deskripsi' => 'Penutupan saldo debit pendapatan dari laba tahun berjalan',
                        'debit' => $amount,
                        'kredit' => 0,
                    ];
                }
            }

            foreach ($report['bebanRows'] as $row) {
                $amount = (float) $row['amount'];
                if (abs($amount) < 1) {
                    continue;
                }

                if ($amount > 0) {
                    $items[] = [
                        'coa_id' => $row['coa']->id,
                        'deskripsi' => 'Penutupan akun beban',
                        'debit' => 0,
                        'kredit' => $amount,
                    ];
                    $items[] = [
                        'coa_id' => $currentYearProfitAccount->id,
                        'deskripsi' => 'Penutupan akun beban dari laba tahun berjalan',
                        'debit' => $amount,
                        'kredit' => 0,
                    ];
                } else {
                    $amount = abs($amount);
                    $items[] = [
                        'coa_id' => $row['coa']->id,
                        'deskripsi' => 'Penutupan saldo kredit akun beban',
                        'debit' => $amount,
                        'kredit' => 0,
                    ];
                    $items[] = [
                        'coa_id' => $currentYearProfitAccount->id,
                        'deskripsi' => 'Penutupan saldo kredit beban ke laba tahun berjalan',
                        'debit' => 0,
                        'kredit' => $amount,
                    ];
                }
            }

            $labaBersih = (float) $report['labaBersih'];
            if ($labaBersih > 0) {
                $items[] = [
                    'coa_id' => $currentYearProfitAccount->id,
                    'deskripsi' => 'Penutupan saldo laba ke laba ditahan',
                    'debit' => $labaBersih,
                    'kredit' => 0,
                ];
                $items[] = [
                    'coa_id' => $retainedEarningsAccount->id,
                    'deskripsi' => 'Penutupan laba tahun berjalan ke laba ditahan',
                    'debit' => 0,
                    'kredit' => $labaBersih,
                ];
            } elseif ($labaBersih < 0) {
                $lossAmount = abs($labaBersih);
                $items[] = [
                    'coa_id' => $retainedEarningsAccount->id,
                    'deskripsi' => 'Penutupan rugi ke laba ditahan',
                    'debit' => $lossAmount,
                    'kredit' => 0,
                ];
                $items[] = [
                    'coa_id' => $currentYearProfitAccount->id,
                    'deskripsi' => 'Penutupan rugi dari laba tahun berjalan',
                    'debit' => 0,
                    'kredit' => $lossAmount,
                ];
            }

            if (empty($items)) {
                DB::rollBack();
                $this->warn('Tidak ada saldo akun pendapatan atau beban yang ditutup.');
                return self::SUCCESS;
            }

            $totalDebit = collect($items)->sum('debit');
            $totalKredit = collect($items)->sum('kredit');

            if (abs($totalDebit - $totalKredit) >= 1) {
                DB::rollBack();
                $this->error('Jurnal tutup otomatis tidak seimbang. Proses dibatalkan.');
                return self::FAILURE;
            }

            $journal = Jurnal::create([
                'no_jurnal' => $noJurnal,
                'tanggal' => $tanggalAkhir,
                'status' => Jurnal::STATUS_POSTED,
                'keterangan' => $keterangan,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'referensi' => 'AUTO-CLOSING',
                'created_by' => $createdBy,
                'is_posted' => true,
                'posted_at' => now(),
            ]);

            $journal->items()->createMany($items);

            DB::commit();

            $this->info("✅ Jurnal tutup otomatis berhasil dibuat: {$journal->no_jurnal}");
            $this->line("Periode: {$tanggalAwal} s/d {$tanggalAkhir}");
            $this->line("Laba bersih: " . number_format($labaBersih, 2));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal membuat jurnal tutup otomatis: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function generateNoJurnal(string $tanggal): string
    {
        $prefix = 'TJ' . Carbon::parse($tanggal)->format('Ymd');
        $latest = Jurnal::where('no_jurnal', 'like', $prefix . '%')->count();

        return $prefix . str_pad((string) ($latest + 1), 6, '0', STR_PAD_LEFT);
    }
}
