<?php

namespace App\Http\Controllers;

use App\Models\Aph;
use App\Models\Arh;
use App\Models\Coa;
use App\Models\DriverReceivable;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Services\JournalQueryService;
use App\Services\LabaRugiReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private readonly JournalQueryService $journalQueryService,
        private readonly LabaRugiReportService $labaRugiReportService,
    ) {}

    public function index()
    {
        $canViewProfitLoss = auth()->user()?->hasMenuPermission('laporan.laba_rugi') ?? false;
        $canViewSalesReport = auth()->user()?->hasMenuPermission('laporan.penjualan', 'view') ?? false;
        $canViewExpenseReport = auth()->user()?->hasMenuPermission('laporan.biaya', 'view') ?? false;
        $canViewReceivableReport = auth()->user()?->hasMenuPermission('laporan.piutang', 'view') ?? false;
        $canViewPayableReport = auth()->user()?->hasMenuPermission('laporan.hutang', 'view') ?? false;
        $canViewDriverReceivableReport = auth()->user()?->hasMenuPermission('laporan.driver-receivables', 'view') ?? false;

        $totalAkun = Coa::count();
        $totalJurnal = Jurnal::count();
        $jurnalPosted = Jurnal::posted()->count();

        $totalAset = $this->hitungSaldoReal('Aset');
        $totalKewajiban = $this->hitungSaldoReal('Kewajiban');
        $totalEkuitas = $this->hitungSaldoReal('Ekuitas');

        $saldoPiutang = 0.0;
        $jumlahPiutangOpen = 0;
        if ($canViewReceivableReport) {
            $piutangTotals = Arh::query()
                ->where('SALDO', '>', 0)
                ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(SALDO),0) as aggregate_saldo')
                ->first();
            $saldoPiutang = (float) ($piutangTotals->aggregate_saldo ?? 0);
            $jumlahPiutangOpen = (int) ($piutangTotals->aggregate_count ?? 0);
        }

        $saldoHutang = 0.0;
        $jumlahHutangOpen = 0;
        if ($canViewPayableReport) {
            $hutangTotals = Aph::query()
                ->where('SALDO', '>', 0)
                ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(SALDO),0) as aggregate_saldo')
                ->first();
            $saldoHutang = (float) ($hutangTotals->aggregate_saldo ?? 0);
            $jumlahHutangOpen = (int) ($hutangTotals->aggregate_count ?? 0);
        }

        $saldoPiutangSopir = 0.0;
        $jumlahPiutangSopirOpen = 0;
        if ($canViewDriverReceivableReport) {
            $driverTotals = DriverReceivable::query()
                ->where('balance', '>', 0)
                ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(balance),0) as aggregate_saldo')
                ->first();
            $saldoPiutangSopir = (float) ($driverTotals->aggregate_saldo ?? 0);
            $jumlahPiutangSopirOpen = (int) ($driverTotals->aggregate_count ?? 0);
        }

        $salesDateFrom = now()->startOfMonth()->toDateString();
        $salesDateTo = now()->toDateString();
        $totalPenjualan = 0.0;
        $jumlahPenjualan = 0;
        if ($canViewSalesReport) {
            $salesQuery = Arh::query()
                ->where(function ($query): void {
                    $query->whereNull('SOURCE')
                        ->orWhere('SOURCE', 'not like', 'SALDO_AWAL_%');
                })
                ->whereDate('TGLFAKTUR', '>=', $salesDateFrom)
                ->whereDate('TGLFAKTUR', '<=', $salesDateTo);
            $salesTotals = (clone $salesQuery)->selectRaw(
                'COUNT(*) as aggregate_count, COALESCE(SUM(PIUTANG),0) as aggregate_piutang'
            )->first();
            $totalPenjualan = (float) ($salesTotals->aggregate_piutang ?? 0);
            $jumlahPenjualan = (int) ($salesTotals->aggregate_count ?? 0);
        }

        $expenseDateFrom = $salesDateFrom;
        $expenseDateTo = $salesDateTo;
        $totalBiayaBulanIni = 0.0;
        $jumlahAkunBiaya = 0;
        if ($canViewExpenseReport) {
            $expenseAccounts = Coa::active()
                ->posting()
                ->where('tipe', 'Beban')
                ->get()
                ->reject(fn (Coa $coa) => $this->isPajakPenghasilanAccount($coa))
                ->values();
            $expenseTotals = $expenseAccounts->isEmpty()
                ? []
                : $this->journalQueryService->getSumByMultipleCoaIdsExcludingClosing(
                    $expenseDateFrom,
                    $expenseDateTo,
                    $expenseAccounts->pluck('id')->all()
                );
            foreach ($expenseAccounts as $account) {
                $debit = (float) ($expenseTotals[$account->id]['debit'] ?? 0);
                $kredit = (float) ($expenseTotals[$account->id]['kredit'] ?? 0);
                $saldo = $debit - $kredit;
                if (abs($debit) > 0.005 || abs($kredit) > 0.005) {
                    $jumlahAkunBiaya++;
                    $totalBiayaBulanIni += $saldo;
                }
            }
        }

        $today = now()->toDateString();
        $jurnalDraftCount = Jurnal::query()->draft()->count();

        $piutangJatuhTempoCount = 0;
        $piutangJatuhTempoSaldo = 0.0;
        if ($canViewReceivableReport) {
            $overdueReceivable = Arh::query()
                ->where('SALDO', '>', 0)
                ->whereNotNull('TGLJT')
                ->whereDate('TGLJT', '<', $today)
                ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(SALDO),0) as aggregate_saldo')
                ->first();
            $piutangJatuhTempoCount = (int) ($overdueReceivable->aggregate_count ?? 0);
            $piutangJatuhTempoSaldo = (float) ($overdueReceivable->aggregate_saldo ?? 0);
        }

        $hutangJatuhTempoCount = 0;
        $hutangJatuhTempoSaldo = 0.0;
        if ($canViewPayableReport) {
            $overduePayable = Aph::query()
                ->where('SALDO', '>', 0)
                ->whereNotNull('TGLJT')
                ->whereDate('TGLJT', '<', $today)
                ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(SALDO),0) as aggregate_saldo')
                ->first();
            $hutangJatuhTempoCount = (int) ($overduePayable->aggregate_count ?? 0);
            $hutangJatuhTempoSaldo = (float) ($overduePayable->aggregate_saldo ?? 0);
        }

        $piutangSopirJatuhTempoCount = 0;
        $piutangSopirJatuhTempoSaldo = 0.0;
        if ($canViewDriverReceivableReport) {
            $overdueDriver = DriverReceivable::query()
                ->where('balance', '>', 0)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', $today)
                ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(balance),0) as aggregate_saldo')
                ->first();
            $piutangSopirJatuhTempoCount = (int) ($overdueDriver->aggregate_count ?? 0);
            $piutangSopirJatuhTempoSaldo = (float) ($overdueDriver->aggregate_saldo ?? 0);
        }

        $asets = Coa::byTipe('Aset')
            ->orderBy('kode_akun')
            ->get();

        $kewajibans = Coa::byTipe('Kewajiban')
            ->orderBy('kode_akun')
            ->get();
        $ekuitas = Coa::byTipe('Ekuitas')
            ->orderBy('kode_akun')
            ->get();

        $pendapatans = $canViewProfitLoss
            ? Coa::byTipe('Pendapatan')->orderBy('kode_akun')->get()
            : collect();
        $bebans = $canViewProfitLoss
            ? Coa::byTipe('Beban')->orderBy('kode_akun')->get()
            : collect();

        // Laba rugi dashboard = bulan berjalan (sama periode penjualan/biaya).
        $profitLossDateFrom = $salesDateFrom;
        $profitLossDateTo = $salesDateTo;
        $totalPendapatan = 0.0;
        $totalBeban = 0.0;
        $totalPajakPenghasilan = 0.0;
        $totalBebanSebelumPajak = 0.0;
        $labaSebelumPajak = 0.0;
        $labaBersih = 0.0;
        if ($canViewProfitLoss) {
            $labaRugiBulanan = $this->labaRugiReportService->build($profitLossDateFrom, $profitLossDateTo);
            $totalPendapatan = (float) $labaRugiBulanan['totalPendapatan'];
            $totalBebanSebelumPajak = (float) $labaRugiBulanan['totalBeban'];
            $totalPajakPenghasilan = (float) $labaRugiBulanan['taksiranPajakPenghasilan'];
            $totalBeban = $totalBebanSebelumPajak + $totalPajakPenghasilan;
            $labaSebelumPajak = (float) $labaRugiBulanan['labaSebelumPajak'];
            $labaBersih = (float) $labaRugiBulanan['labaBersih'];
        }

        // Satu rekap untuk seluruh detail dashboard agar tidak menjalankan
        // dua query SUM pada setiap akun ketika halaman dibuka.
        $saldoAkun = JurnalItem::query()
            ->join('jurnals', 'jurnals.id', '=', 'jurnal_items.jurnal_id')
            ->where('jurnals.is_posted', true)
            ->groupBy('jurnal_items.coa_id')
            ->selectRaw('jurnal_items.coa_id, SUM(jurnal_items.debit) AS debit, SUM(jurnal_items.kredit) AS kredit')
            ->get()
            ->keyBy('coa_id');

        $saldoDashboard = $asets
            ->concat($kewajibans)
            ->concat($ekuitas)
            ->concat($pendapatans)
            ->concat($bebans)
            ->unique('id')
            ->mapWithKeys(function (Coa $akun) use ($saldoAkun) {
                $mutasi = $saldoAkun->get($akun->id);
                $debit = (float) ($mutasi->debit ?? 0);
                $kredit = (float) ($mutasi->kredit ?? 0);
                $saldo = in_array($akun->tipe, ['Kewajiban', 'Ekuitas', 'Pendapatan'], true)
                    ? $kredit - $debit
                    : $debit - $kredit;

                return [$akun->id => $saldo];
            });

        $chartLabels = $asets->pluck('nama_akun')
            ->merge($kewajibans->pluck('nama_akun'));

        $chartAset = $asets->map(fn ($a) => $saldoDashboard->get($a->id, 0));

        $chartKewajiban = $kewajibans->map(fn ($k) => $saldoDashboard->get($k->id, 0));

        $chartData = $chartAset->merge($chartKewajiban);

        $saldoPerTipe = Coa::select('tipe', DB::raw('count(*) as total'))
            ->groupBy('tipe')
            ->get();

        $tipeLabels = $saldoPerTipe->pluck('tipe');
        $tipeCounts = $saldoPerTipe->pluck('total');

        return view('dashboard', [
            'totalAkun' => $totalAkun,
            'totalJurnal' => $totalJurnal,
            'jurnalPosted' => $jurnalPosted,
            'totalAset' => $totalAset,
            'totalKewajiban' => $totalKewajiban,
            'totalEkuitas' => $totalEkuitas,
            'jurnalDraftCount' => $jurnalDraftCount,
            'piutangJatuhTempoCount' => $piutangJatuhTempoCount,
            'piutangJatuhTempoSaldo' => $piutangJatuhTempoSaldo,
            'hutangJatuhTempoCount' => $hutangJatuhTempoCount,
            'hutangJatuhTempoSaldo' => $hutangJatuhTempoSaldo,
            'piutangSopirJatuhTempoCount' => $piutangSopirJatuhTempoCount,
            'piutangSopirJatuhTempoSaldo' => $piutangSopirJatuhTempoSaldo,
            'saldoPerTipe' => $saldoPerTipe,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'chartAset' => $chartAset,
            'chartKewajiban' => $chartKewajiban,
            'tipeLabels' => $tipeLabels,
            'tipeCounts' => $tipeCounts,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'profitLossDateFrom' => $profitLossDateFrom,
            'profitLossDateTo' => $profitLossDateTo,
            'canViewProfitLoss' => $canViewProfitLoss,
            'canViewSalesReport' => $canViewSalesReport,
            'canViewExpenseReport' => $canViewExpenseReport,
            'canViewReceivableReport' => $canViewReceivableReport,
            'canViewPayableReport' => $canViewPayableReport,
            'canViewDriverReceivableReport' => $canViewDriverReceivableReport,
            'totalPenjualan' => $totalPenjualan,
            'jumlahPenjualan' => $jumlahPenjualan,
            'salesDateFrom' => $salesDateFrom,
            'salesDateTo' => $salesDateTo,
            'totalBiayaBulanIni' => $totalBiayaBulanIni,
            'jumlahAkunBiaya' => $jumlahAkunBiaya,
            'expenseDateFrom' => $expenseDateFrom,
            'expenseDateTo' => $expenseDateTo,
            'saldoPiutang' => $saldoPiutang,
            'jumlahPiutangOpen' => $jumlahPiutangOpen,
            'saldoHutang' => $saldoHutang,
            'jumlahHutangOpen' => $jumlahHutangOpen,
            'saldoPiutangSopir' => $saldoPiutangSopir,
            'jumlahPiutangSopirOpen' => $jumlahPiutangSopirOpen,
            'asets' => $asets,
            'kewajibans' => $kewajibans,
            'ekuitas' => $ekuitas,
            'pendapatans' => $pendapatans,
            'bebans' => $bebans,
            'saldoDashboard' => $saldoDashboard,
            'totalPajakPenghasilan' => $totalPajakPenghasilan,
            'totalBebanSebelumPajak' => $totalBebanSebelumPajak,
            'labaSebelumPajak' => $labaSebelumPajak,
            'labaBersih' => $labaBersih,
        ]);
    }

    private function hitungSaldoReal($tipe)
    {
        $coaIds = Coa::byTipe($tipe)->pluck('id');
        $totalDebit = JurnalItem::whereIn('coa_id', $coaIds)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true))
            ->sum('debit');
        $totalKredit = JurnalItem::whereIn('coa_id', $coaIds)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true))
            ->sum('kredit');
        return $tipe == 'Aset' ? $totalDebit - $totalKredit : $totalKredit - $totalDebit;
    }

    private function hitungSaldoAkunReal($coaId, $tipe)
    {
        $totalDebit = JurnalItem::where('coa_id', $coaId)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true))
            ->sum('debit');
        $totalKredit = JurnalItem::where('coa_id', $coaId)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true))
            ->sum('kredit');
        return $tipe == 'Aset' ? $totalDebit - $totalKredit : $totalKredit - $totalDebit;
    }

    private function isPajakPenghasilanAccount(Coa $akun): bool
    {
        $kategori = strtolower(trim((string) $akun->kategori));
        $nama = strtolower(trim((string) $akun->nama_akun));

        return $kategori === 'pajak penghasilan'
            || str_contains($nama, 'pajak penghasilan');
    }
}
