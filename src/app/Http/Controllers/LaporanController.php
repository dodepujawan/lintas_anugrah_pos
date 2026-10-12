<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem; // <-- SUDAH DIPERBAIKI (Sebelumnya JournalItem)
use App\Models\YearClosing;
use App\Services\AccountMap;
use App\Services\AuditLogService;
use App\Services\JournalQueryService;
use App\Services\LabaRugiReportService;
use App\Services\NeracaReportService;
use App\Services\PeriodLockService;
use App\Services\TutupBukuReportService;
use App\Services\FastTablePdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BukuBesarExport;
use App\Services\LedgerSourceLinkService;
use App\Exports\CoaExport;
use App\Exports\JurnalUmumExport;
use App\Exports\LabaRugiExport;
use App\Exports\NeracaExport;
use App\Exports\NeracaSaldoExport;
use App\Exports\ArusKasExport;
use App\Exports\BiayaExport;
use App\Exports\TutupBukuExport;
use App\Exports\PerubahanEkuitasExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function __construct(
        private readonly NeracaReportService $neracaReportService,
        private readonly JournalQueryService $journalQueryService,
        private readonly LabaRugiReportService $labaRugiReportService,
        private readonly TutupBukuReportService $tutupBukuReportService,
        private readonly FastTablePdfService $fastTablePdf,
    ) {}

    public function index()
    {
        return view('laporan.index');
    }

    public function neraca(Request $request)
    {
        $periodType = $request->filled('period_type')
            ? $request->string('period_type')->toString()
            : ($request->filled('month') && $request->filled('year') ? 'month_year' : 'date_range');
        $month = $request->month ?? now()->format('m');
        $year = $request->year ?? now()->format('Y');

        if ($periodType === 'month_year') {
            $periodType = 'month_year';
            $fromDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
            $toDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        } else {
            $fromDate = $request->from_date ?? now()->format('Y-m-d');
            $toDate = $request->to_date ?? now()->format('Y-m-d');
        }

        $report = $this->neracaReportService->build($request->all() + [
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);

        return view('laporan.neraca', array_merge($report, [
            'tanggal' => $report['from_date'],
            'period_type' => $periodType,
            'month' => $month,
            'year' => $year,
            'periodLockStatus' => app(PeriodLockService::class)->statusLabel($toDate),
        ]));
    }

    public function exportNeracaPdf(Request $request)
    {
        $report = $this->neracaReportService->build($request->all() + [
            'tanggal' => $request->tanggal ?? now()->toDateString(),
        ]);

        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        $pdf = Pdf::loadView('laporan.neraca_pdf', array_merge($report, [
            'tanggal' => $report['from_date'],
            'period_type' => $report['period_type'],
            'month' => $report['month'],
            'year' => $report['year'],
            'periodLockStatus' => app(PeriodLockService::class)->statusLabel($report['to_date']),
            'dotMatrix' => $dotMatrix,
        ]))
            ->setPaper($dotMatrix ? [0, 0, 612, 936] : 'letter', 'portrait')
            ->setOption('isRemoteEnabled', false);

        return $pdf->stream('neraca_' . $report['from_date'] . '.pdf');
    }

    public function labaRugi(Request $request)
    {
        $modePeriode = $request->filled('mode_periode')
            ? $request->string('mode_periode')->toString()
            : ($request->filled('tanggal_awal') || $request->filled('tanggal_akhir') ? 'range' : 'bulanan');

        if ($modePeriode === 'tahunan') {
            $year = $request->year ?? now()->format('Y');
            $tanggalAwal = Carbon::createFromDate($year, 1, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($year, 12, 31)->format('Y-m-d');
        } elseif ($modePeriode === 'bulanan') {
            $month = $request->month ?? now()->format('m');
            $year = $request->year ?? now()->format('Y');
            $tanggalAwal = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        } else {
            // range
            $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
            $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        }

        $report = $this->labaRugiReportService->build($tanggalAwal, $tanggalAkhir);
        $periodeLabel = $this->labaRugiPeriodLabel($modePeriode, $tanggalAwal, $tanggalAkhir, $month ?? null, $year ?? null);

        return view('laporan.laba_rugi', array_merge($report, [
            'mode_periode' => $modePeriode,
            'month' => $month ?? null,
            'year' => $year ?? null,
            'periodeLabel' => $periodeLabel,
            'periodLockStatus' => app(PeriodLockService::class)->statusLabel($tanggalAkhir),
        ]));
    }

    public function bukuBesar(Request $request)
    {
        $coas = Coa::active()->posting()->orderBy('kode_akun')->get();
        $selectedCoa = null;
        $items = collect();
        $sourceLinks = [];
        $totalDebit = 0;
        $totalKredit = 0;
        $openingBalance = 0;
        $endingBalance = 0;
        $queryInfo = null;
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $periodLockStatus = app(PeriodLockService::class)->statusLabel($tanggalAkhir);
        $periodLockStatus = app(PeriodLockService::class)->statusLabel($tanggalAkhir);

        if ($request->filled('coa_id')) {
            $selectedCoa = Coa::active()->posting()->findOrFail($request->coa_id);

            // Gunakan JournalQueryService untuk smart query selection
            $queryInfo = $this->journalQueryService->getQueryInfo($tanggalAwal, $tanggalAkhir);

            $items = $this->journalQueryService->getLedgerItemsForCoa(
                $tanggalAwal,
                $tanggalAkhir,
                (int) $selectedCoa->id
            );

            $sourceLinks = app(LedgerSourceLinkService::class)->resolveForJournals(
                $items->map(fn ($item) => $item->jurnal)->filter()
            );

            $totalDebit = $items->sum('debit');
            $totalKredit = $items->sum('kredit');
            $openingBalance = $this->calculateLedgerOpeningBalance($selectedCoa, $tanggalAwal);
            $endingBalance = $items->reduce(function ($balance, $item) use ($selectedCoa) {
                if (in_array($selectedCoa->tipe, ['Aset', 'Beban'])) {
                    return $balance + $item->debit - $item->kredit;
                }

                return $balance + $item->kredit - $item->debit;
            }, $openingBalance);
        }

        return view('laporan.buku_besar', compact(
            'coas',
            'selectedCoa',
            'items',
            'sourceLinks',
            'totalDebit',
            'totalKredit',
            'openingBalance',
            'endingBalance',
            'queryInfo',
            'tanggalAwal',
            'tanggalAkhir',
            'periodLockStatus'
        ));
    }

    public function biaya(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $search = trim((string) $request->input('search', ''));
        $selectedCoaIds = collect($request->input('coa_ids', []))
            ->when($request->filled('coa_id'), fn ($ids) => $ids->push($request->integer('coa_id')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $coasQuery = Coa::active()
            ->posting()
            ->where('tipe', 'Beban')
            ->orderBy('kode_akun');

        if ($search !== '') {
            $coasQuery->where(function ($query) use ($search): void {
                $query->where('kode_akun', 'like', "%{$search}%")
                    ->orWhere('nama_akun', 'like', "%{$search}%");
            });
        }

        $coas = $coasQuery->get();
        $totalsByCoa = $coas->isEmpty()
            ? []
            : $this->journalQueryService->getSumByMultipleCoaIdsExcludingClosing($tanggalAwal, $tanggalAkhir, $coas->pluck('id')->all());

        $rows = $coas
            ->map(function (Coa $coa) use ($totalsByCoa) {
                $debit = (float) ($totalsByCoa[$coa->id]['debit'] ?? 0);
                $kredit = (float) ($totalsByCoa[$coa->id]['kredit'] ?? 0);

                return [
                    'coa' => $coa,
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'saldo' => $debit - $kredit,
                    'has_transactions' => abs($debit) > 0.005 || abs($kredit) > 0.005,
                ];
            })
            ->filter(fn (array $row) => $request->boolean('show_zero') || $row['has_transactions'])
            ->sortByDesc(fn (array $row) => abs($row['saldo']))
            ->values();

        $selectedCoas = $selectedCoaIds->isEmpty()
            ? collect()
            : Coa::active()
                ->posting()
                ->where('tipe', 'Beban')
                ->whereIn('id', $selectedCoaIds)
                ->orderBy('kode_akun')
                ->get();

        $detailItemsByCoa = $selectedCoas->mapWithKeys(function (Coa $coa) use ($tanggalAwal, $tanggalAkhir): array {
            return [
                $coa->id => $this->journalQueryService->getSmartQueryExcludingClosing($tanggalAwal, $tanggalAkhir)
                    ->where('coa_id', $coa->id)
                    ->with('jurnal', 'coa')
                    ->orderBy('created_at')
                    ->get(),
            ];
        });

        $taxRows = $rows->filter(fn (array $row) => $this->isPajakPenghasilanAccount($row['coa']));
        $operationalRows = $rows->reject(fn (array $row) => $this->isPajakPenghasilanAccount($row['coa']));
        $totalDebit = $operationalRows->sum('debit');
        $totalKredit = $operationalRows->sum('kredit');
        $totalBiaya = $operationalRows->sum('saldo');
        $totalPajakPenghasilan = $taxRows->sum('saldo');
        $totalSemuaBiaya = $rows->sum('saldo');
        $periodLockStatus = app(PeriodLockService::class)->statusLabel($tanggalAkhir);

        return view('laporan.biaya', compact(
            'tanggalAwal',
            'tanggalAkhir',
            'search',
            'coas',
            'rows',
            'selectedCoaIds',
            'selectedCoas',
            'detailItemsByCoa',
            'totalDebit',
            'totalKredit',
            'totalBiaya',
            'totalPajakPenghasilan',
            'totalSemuaBiaya',
            'periodLockStatus'
        ));
    }

    public function exportBukuBesarPdf(Request $request)
    {
        $request->validate([
            'coa_id' => ['required', 'integer'],
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
        ]);

        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $selectedCoa = Coa::active()->posting()->findOrFail($request->integer('coa_id'));
        $items = $this->journalQueryService->getLedgerItemsForCoa(
            $tanggalAwal,
            $tanggalAkhir,
            (int) $selectedCoa->id
        );

        $totalDebit = $items->sum('debit');
        $totalKredit = $items->sum('kredit');
        $openingBalance = $this->calculateLedgerOpeningBalance($selectedCoa, $tanggalAwal);
        $endingBalance = $items->reduce(function ($balance, $item) use ($selectedCoa) {
            return in_array($selectedCoa->tipe, ['Aset', 'Beban'], true)
                ? $balance + $item->debit - $item->kredit
                : $balance + $item->kredit - $item->debit;
        }, $openingBalance);

        $safeAccountCode = preg_replace('/[^A-Za-z0-9_-]+/', '_', $selectedCoa->kode_akun);

        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        $runningBalance = $openingBalance;
        $rows = [[
            date('d/m/Y', strtotime($tanggalAwal)),
            '-',
            'Saldo Awal per '.date('d/m/Y', strtotime($tanggalAwal)),
            '',
            '',
            number_format($runningBalance, 2, ',', '.'),
        ]];
        $transactionRows = $items->map(function ($item) use ($selectedCoa, &$runningBalance): array {
            $runningBalance += in_array($selectedCoa->tipe, ['Aset', 'Beban'], true)
                ? (float) $item->debit - (float) $item->kredit
                : (float) $item->kredit - (float) $item->debit;

            $deskripsi = trim((string) ($item->deskripsi ?: ($item->jurnal->keterangan ?? '')));
            if ($deskripsi === '') {
                $deskripsi = '-';
            }

            return [
                $item->jurnal->tanggal->format('d/m/Y'),
                (string) $item->jurnal->no_jurnal,
                $deskripsi,
                number_format((float) $item->debit, 2, ',', '.'),
                number_format((float) $item->kredit, 2, ',', '.'),
                number_format($runningBalance, 2, ',', '.'),
            ];
        })->all();
        $rows = array_merge($rows, $transactionRows);

        if ($transactionRows === []) {
            $rows[] = ['', '', 'Tidak ada transaksi pada periode ini', '', '', ''];
        }

        $rows[] = [
            '',
            '',
            'Total Debit / Kredit periode',
            number_format($totalDebit, 2, ',', '.'),
            number_format($totalKredit, 2, ',', '.'),
            '',
        ];
        $rows[] = [
            date('d/m/Y', strtotime($tanggalAkhir)),
            '-',
            'Saldo Akhir per '.date('d/m/Y', strtotime($tanggalAkhir)),
            '',
            '',
            number_format($endingBalance, 2, ',', '.'),
        ];

        return $this->fastTablePdf->stream([
            'title' => 'LAPORAN BUKU BESAR',
            'subtitles' => [
                'Periode '.date('d/m/Y', strtotime($tanggalAwal)).' s/d '.date('d/m/Y', strtotime($tanggalAkhir)),
                'Akun: '.$selectedCoa->kode_akun.' - '.$selectedCoa->nama_akun,
                'Urutan: tanggal jurnal terkecil ke terbesar',
            ],
            'headings' => ['Tanggal', 'No. Jurnal', 'Deskripsi', 'Debit', 'Kredit', 'Saldo'],
            'columns' => [50, 85, 219, 70, 70, 70],
            'rows' => $rows,
            'numeric' => [3, 4, 5],
            'bold_columns' => [5],
            'wrap_columns' => [2],
            'max_wrap_lines' => 4,
            'dot_matrix' => $dotMatrix,
            'font_size' => 8.5,
            'row_height' => 14.0,
            'filename' => "buku_besar_{$safeAccountCode}_{$tanggalAwal}_{$tanggalAkhir}.pdf",
        ]);
    }

    private function calculateLedgerOpeningBalance(Coa $coa, string $tanggalAwal): float
    {
        $previousDate = Carbon::parse($tanggalAwal)->subDay()->format('Y-m-d');
        $totals = $this->journalQueryService->getSumByCoaId('1900-01-01', $previousDate, $coa->id);

        return in_array($coa->tipe, ['Aset', 'Beban'], true)
            ? $totals['debit'] - $totals['kredit']
            : $totals['kredit'] - $totals['debit'];
    }

    public function streamReportPdf(Request $request, string $report)
    {
        $reports = [
            'jurnal-umum' => ['method' => 'jurnalUmum', 'paper' => 'landscape'],
            'coa' => ['method' => 'coa', 'paper' => 'portrait'],
            'neraca-saldo' => ['method' => 'neracaSaldo', 'paper' => 'landscape'],
            'arus-kas' => ['method' => 'arusKas', 'paper' => 'portrait'],
            'tutup-buku' => ['method' => 'tutupBuku', 'paper' => 'portrait'],
            'perubahan-ekuitas' => ['method' => 'perubahanEkuitas', 'paper' => 'portrait'],
            'laba-rugi' => ['method' => 'labaRugi', 'paper' => 'portrait'],
        ];

        abort_unless(isset($reports[$report]), 404);
        $configuration = $reports[$report];
        // PDF must contain the complete filtered report, while the browser view
        // remains paginated to avoid loading every journal into memory.
        $request->attributes->set('_report_pdf_export', true);
        $view = $this->{$configuration['method']}($request);

        $dotMatrix = $request->input('print_format') === 'dot_matrix';

        if ($report === 'jurnal-umum') {
            $data = $view->getData();
            $rows = [];
            foreach ($data['jurnals'] as $jurnal) {
                $rows[] = [
                    $jurnal->tanggal->format('d/m/Y'),
                    (string) $jurnal->no_jurnal,
                    (string) ($jurnal->keterangan ?? '-'),
                    number_format((float) $jurnal->total_debit, 2, ',', '.'),
                    number_format((float) $jurnal->total_kredit, 2, ',', '.'),
                ];
                foreach ($jurnal->items as $item) {
                    $rows[] = [
                        '',
                        (string) ($item->coa?->kode_akun ?? '-'),
                        '  '.(string) ($item->coa?->nama_akun ?? '').' - '.(string) ($item->deskripsi ?? '-'),
                        number_format((float) $item->debit, 2, ',', '.'),
                        number_format((float) $item->kredit, 2, ',', '.'),
                    ];
                }
            }

            return $this->fastTablePdf->stream([
                'title' => 'LAPORAN JURNAL UMUM',
                'subtitles' => ['Periode '.date('d/m/Y', strtotime($data['tanggalAwal'])).' - '.date('d/m/Y', strtotime($data['tanggalAkhir']))],
                'headings' => ['Tanggal', 'No. Jurnal / Akun', 'Deskripsi', 'Debit', 'Kredit'],
                'columns' => [55, 100, 269, 70, 70],
                'rows' => $rows,
                'numeric' => [3, 4],
                'dot_matrix' => $dotMatrix,
                'font_size' => 6.0,
                'rows_per_page' => 61,
                'filename' => 'jurnal_umum_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd_His').'.pdf',
            ]);
        }

        if ($report === 'neraca-saldo') {
            $data = $view->getData();
            $rows = collect($data['rows'])->map(fn ($row) => [
                (string) ($row['coa']->kode_akun ?? '-'),
                (string) ($row['coa']->nama_akun ?? '-'),
                number_format((float) ($row['debit'] ?? 0), 2, ',', '.'),
                number_format((float) ($row['kredit'] ?? 0), 2, ',', '.'),
            ])->all();
            $rows[] = [
                '', 'TOTAL',
                number_format((float) $data['totalDebit'], 2, ',', '.'),
                number_format((float) $data['totalKredit'], 2, ',', '.'),
            ];

            return $this->fastTablePdf->stream([
                'title' => 'LAPORAN NERACA SALDO',
                'subtitles' => [(string) $data['periodeLabel']],
                'headings' => ['Kode Akun', 'Nama Akun', 'Saldo Debit', 'Saldo Kredit'],
                'columns' => [85, 269, 105, 105],
                'rows' => $rows,
                'numeric' => [2, 3],
                'bold_columns' => [2, 3],
                'dot_matrix' => $dotMatrix,
                'font_size' => $dotMatrix ? 5.6 : 6.2,
                'rows_per_page' => 62,
                'filename' => 'neraca_saldo_'.($dotMatrix ? 'dot_matrix_' : 'standard_').now()->format('Ymd_His').'.pdf',
            ]);
        }

        return Pdf::loadView($view->name(), $view->getData())
            ->setPaper([0, 0, 612, 936], $dotMatrix ? 'portrait' : $configuration['paper'])
            ->setOption('defaultMediaType', 'print')
            ->setOption('isRemoteEnabled', false)
            ->stream($report.'_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd').'.pdf');
    }

    public function jurnalUmum(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $jenis = $request->jenis ?? null;
        $search = trim((string) $request->input('search', ''));

        // available jenis list for the filter dropdown (only if column exists)
        if (Schema::hasColumn('jurnals', 'tipe')) {
            $jenisList = Jurnal::select('tipe')->distinct()->orderBy('tipe')->pluck('tipe');
            // ensure certain jurnal types are always available in the dropdown
            $required = collect([
                'Jurnal Umum',
                'Jurnal Penyesuaian',
                'Saldo Awal',
                'Saldo Awal Hutang',
                'Pelunasan Hutang',
                'Penambahan Piutang Sopir',
                'Pelunasan Piutang Sopir',
                'Pembatalan Pelunasan Piutang',
                'Pembatalan Pelunasan Hutang',
            ]);
            $jenisList = $jenisList->merge($required)->unique()->values();
            // fallback to known enum values when table has no records yet
            if ($jenisList->isEmpty()) {
                $jenisList = collect([
                    'Penerimaan Kas',
                    'Pengeluaran Kas',
                    'Jurnal Umum',
                    'Penjualan',
                    'Pembelian',
                    'Pelunasan Piutang',
                    'Pelunasan Hutang',
                    'Penambahan Piutang Sopir',
                    'Pelunasan Piutang Sopir',
                    'Pembatalan Pelunasan Piutang',
                    'Pembatalan Pelunasan Hutang',
                    'Jurnal Penyesuaian',
                    'Saldo Awal',
                    'Saldo Awal Hutang',
                ]);
            }
        } else {
            $jenisList = collect(['Jurnal Umum', 'Jurnal Penyesuaian', 'Saldo Awal', 'Saldo Awal Hutang']);
        }

        $jurnalQuery = Jurnal::with(['items.coa', 'archivedItems.coa', 'createdBy'])
            ->where('is_posted', true)
            ->whereDate('tanggal', '>=', $tanggalAwal)
            ->whereDate('tanggal', '<=', $tanggalAkhir)
            ->when($jenis, function ($q) use ($jenis) {
                // special handling for some jurnal groups that may be identified
                // by tipe, module_type or no_jurnal prefix
                if ($jenis === 'Jurnal Penyesuaian') {
                    return $q->where(function ($sub) {
                        $sub->where('tipe', 'Jurnal Penyesuaian')
                            ->orWhere('module_type', 'Jurnal Penyesuaian')
                            ->orWhere('no_jurnal', 'like', 'JP%');
                    });
                }

                if ($jenis === 'Saldo Awal' || $jenis === 'Jurnal Saldo Awal') {
                    return $q->where(function ($sub) {
                        $sub->where('tipe', 'Saldo Awal')
                            ->orWhere('module_type', 'Saldo Awal')
                            ->orWhere('no_jurnal', 'like', 'SA%');
                    });
                }

                if ($jenis === 'Saldo Awal Hutang') {
                    return $q->where(function ($sub) {
                        $sub->where('tipe', 'Saldo Awal Hutang')
                            ->orWhere('module_type', 'Saldo Awal Hutang')
                            ->orWhere('keterangan', 'like', '%Saldo Awal Hutang%')
                            ->orWhere('keterangan', 'like', '%saldo awal hutang%')
                            ->orWhere('no_jurnal', 'like', 'SAH%');
                    });
                }

                if ($jenis === 'Pelunasan Hutang') {
                    return $q->where(function ($sub) {
                        $sub->where('module_type', 'Pelunasan Hutang')
                            ->orWhere(function ($journal) {
                                $journal->where('no_jurnal', 'like', 'AP%')
                                    ->where('keterangan', 'like', '%Pelunasan Hutang%');
                            });
                    });
                }

                if ($jenis === 'Penambahan Piutang Sopir') {
                    return $q->where(function ($sub) {
                        $sub->where('module_type', 'Piutang Sopir')
                            ->orWhere(function ($journal) {
                                $journal->where('no_jurnal', 'like', 'PS%')
                                    ->where('keterangan', 'like', '%Piutang sopir%');
                            });
                    });
                }

                if ($jenis === 'Pelunasan Piutang Sopir') {
                    return $q->where('module_type', 'Pelunasan Piutang Sopir');
                }

                if (in_array($jenis, ['Pembatalan Pelunasan Piutang', 'Pembatalan Pelunasan Hutang'], true)) {
                    return $q->where('module_type', $jenis);
                }

                if ($jenis === 'Jurnal Umum') {
                    return $q->where('tipe', 'Jurnal Umum')
                        ->where('no_jurnal', 'not like', 'JP%')
                        ->where('no_jurnal', 'not like', 'SA%');
                }

                return $q->where('tipe', $jenis);
            })
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($sub) use ($like): void {
                    $sub->where('no_jurnal', 'like', $like)
                        ->orWhere('referensi', 'like', $like)
                        ->orWhere('keterangan', 'like', $like)
                        ->orWhere('tipe', 'like', $like)
                        ->orWhere('module_type', 'like', $like)
                        ->orWhereHas('items.coa', function ($item) use ($like): void {
                            $item->where('kode_akun', 'like', $like)
                                ->orWhere('nama_akun', 'like', $like);
                        })
                        ->orWhereHas('items', fn ($item) => $item->where('deskripsi', 'like', $like))
                        ->orWhereHas('archivedItems.coa', function ($item) use ($like): void {
                            $item->where('kode_akun', 'like', $like)
                                ->orWhere('nama_akun', 'like', $like);
                        })
                        ->orWhereHas('archivedItems', fn ($item) => $item->where('deskripsi', 'like', $like));
                });
            })
            ->where(function ($query): void {
                $query->whereHas('items.coa', fn($q) => $q->posting())
                    ->orWhereHas('archivedItems.coa', fn($q) => $q->posting());
            })
            ->orderBy('tanggal')
            ->orderBy('no_jurnal');

        $totals = (clone $jurnalQuery)
            ->reorder()
            ->selectRaw('COALESCE(SUM(total_debit), 0) AS total_debit_report, COALESCE(SUM(total_kredit), 0) AS total_kredit_report')
            ->first();
        $totalDebit = (float) ($totals?->total_debit_report ?? 0);
        $totalKredit = (float) ($totals?->total_kredit_report ?? 0);

        if ($request->attributes->getBoolean('_report_pdf_export')) {
            $jurnals = $jurnalQuery->get()
                ->map(fn (Jurnal $jurnal) => $this->attachCombinedJournalItems($jurnal));
        } else {
            $jurnals = $jurnalQuery->paginate(50)->withQueryString();
            $jurnals->setCollection(
                $jurnals->getCollection()
                    ->map(fn (Jurnal $jurnal) => $this->attachCombinedJournalItems($jurnal))
            );
        }
        $periodLockStatus = app(PeriodLockService::class)->statusLabel($tanggalAkhir);

        return view('laporan.jurnal_umum', compact('jurnals', 'tanggalAwal', 'tanggalAkhir', 'totalDebit', 'totalKredit', 'jenisList', 'jenis', 'search', 'periodLockStatus'));
    }

    // EXPORT EXCEL
    public function exportNeraca(Request $request)
    {
        $report = $this->neracaReportService->build($request->all() + [
            'tanggal' => $request->tanggal ?? now()->toDateString(),
        ]);
        $report['periodLockStatus'] = app(PeriodLockService::class)->statusLabel($report['to_date']);

        return Excel::download(new NeracaExport($report), 'neraca_' . $report['from_date'] . '.xlsx');
    }

    public function exportLabaRugi(Request $request)
    {
        $modePeriode = $request->mode_periode ?? ($request->filled('month') && $request->filled('year') ? 'bulanan' : 'range');

        if ($modePeriode === 'tahunan') {
            $year = $request->year ?? now()->format('Y');
            $tanggalAwal = Carbon::createFromDate($year, 1, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($year, 12, 31)->format('Y-m-d');
        } elseif ($modePeriode === 'bulanan') {
            $month = $request->month ?? now()->format('m');
            $year = $request->year ?? now()->format('Y');
            $tanggalAwal = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        } else {
            $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
            $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        }

        $report = $this->labaRugiReportService->build($tanggalAwal, $tanggalAkhir);
        $report['mode_periode'] = $modePeriode;
        $report['month'] = $month ?? null;
        $report['year'] = $year ?? null;
        $report['periodeLabel'] = $this->labaRugiPeriodLabel($modePeriode, $tanggalAwal, $tanggalAkhir, $month ?? null, $year ?? null);
        $report['periodLockStatus'] = app(PeriodLockService::class)->statusLabel($tanggalAkhir);

        return Excel::download(
            new LabaRugiExport($report),
            'laba_rugi_' . $tanggalAwal . '_' . $tanggalAkhir . '.xlsx'
        );
    }

    public function exportBukuBesar(Request $request)
    {
        $coaId = $request->coa_id;
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

        return Excel::download(
            new BukuBesarExport($coaId, $tanggalAwal, $tanggalAkhir),
            'buku_besar_' . ($coaId ?? 'all') . '_' . $tanggalAwal . '_' . $tanggalAkhir . '.xlsx'
        );
    }

    public function exportBiaya(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $search = trim((string) $request->input('search', ''));
        $coaIds = collect($request->input('coa_ids', []))
            ->when($request->filled('coa_id'), fn ($ids) => $ids->push($request->integer('coa_id')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return Excel::download(
            new BiayaExport($tanggalAwal, $tanggalAkhir, $search, $coaIds, $request->boolean('show_zero')),
            'laporan_biaya_' . $tanggalAwal . '_' . $tanggalAkhir . '.xlsx'
        );
    }

    public function exportJurnalUmum(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $jenis = $request->jenis ?? null;
        $search = trim((string) $request->input('search', ''));
        return Excel::download(
            new JurnalUmumExport($tanggalAwal, $tanggalAkhir, $jenis, app(PeriodLockService::class)->statusLabel($tanggalAkhir), $search),
            'jurnal_umum_' . ($jenis ? str_replace(' ', '_', $jenis) . '_' : '') . $tanggalAwal . '_' . $tanggalAkhir . '.xlsx'
        );
    }

    public function coa(Request $request)
    {
        $levelList = ['Header', 'Sub Header', 'Kelompok Akun', 'Akun Posting'];
        $tipeList = Coa::select('tipe')->distinct()->orderBy('tipe')->pluck('tipe');

        $query = Coa::active();
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $coas = $query->orderBy('kode_akun')->get();

        return view('laporan.coa', compact('coas', 'levelList', 'tipeList'));
    }

    public function exportCoa(Request $request)
    {
        return Excel::download(
            new CoaExport($request->tipe, $request->level),
            'coa_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function neracaSaldo(Request $request)
    {
        $modePeriode = $request->filled('mode_periode')
            ? $request->string('mode_periode')->toString()
            : ($request->filled('tanggal_awal') || $request->filled('tanggal_akhir') ? 'range' : 'bulanan');
        $month = $request->month ?? now()->format('m');
        $year = $request->year ?? now()->format('Y');

        if ($modePeriode === 'bulanan') {
            $tanggalAwal = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        } else {
            $modePeriode = 'range';
            $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
            $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        }

        $coas = Coa::active()->posting()->orderBy('kode_akun')->get();
        $rows = collect();
        $totalDebit = 0;
        $totalKredit = 0;

        // Neraca saldo menampilkan saldo akhir kumulatif per tanggal posisi,
        // bukan jumlah mutasi debit/kredit di dalam rentang filter.
        $totalsByCoa = $coas->isEmpty()
            ? []
            : $this->journalQueryService->getSumByMultipleCoaIds('1900-01-01', $tanggalAkhir, $coas->pluck('id')->all());

        foreach ($coas as $coa) {
            $mutasiDebit = (float) ($totalsByCoa[$coa->id]['debit'] ?? 0);
            $mutasiKredit = (float) ($totalsByCoa[$coa->id]['kredit'] ?? 0);
            $saldoBersih = $mutasiDebit - $mutasiKredit;
            $debit = $saldoBersih > 0.005 ? $saldoBersih : 0;
            $kredit = $saldoBersih < -0.005 ? abs($saldoBersih) : 0;

            $rows->push([
                'coa' => $coa,
                'debit' => $debit,
                'kredit' => $kredit,
            ]);
            $totalDebit += $debit;
            $totalKredit += $kredit;
        }

        $isBalanced = abs($totalDebit - $totalKredit) < 1;
        $periodeLabel = 'Posisi per ' . Carbon::parse($tanggalAkhir)->translatedFormat('d F Y');
        $periodLockStatus = app(PeriodLockService::class)->statusLabel($tanggalAkhir);

        return view('laporan.neraca_saldo', compact(
            'tanggalAwal',
            'tanggalAkhir',
            'rows',
            'totalDebit',
            'totalKredit',
            'isBalanced',
            'modePeriode',
            'month',
            'year',
            'periodeLabel',
            'periodLockStatus'
        ));
    }

    private function attachCombinedJournalItems(Jurnal $jurnal): Jurnal
    {
        $items = $jurnal->items
            ->concat($jurnal->archivedItems)
            ->filter(fn($item) => $item->coa?->level === 'Akun Posting')
            ->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $jurnal->setRelation('items', $items);

        return $jurnal;
    }

    public function exportNeracaSaldo(Request $request)
    {
        $modePeriode = $request->filled('mode_periode')
            ? $request->string('mode_periode')->toString()
            : ($request->filled('tanggal_awal') || $request->filled('tanggal_akhir') ? 'range' : 'bulanan');

        if ($modePeriode === 'bulanan') {
            $month = $request->month ?? now()->format('m');
            $year = $request->year ?? now()->format('Y');
            $tanggalAwal = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        } else {
            $modePeriode = 'range';
            $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
            $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        }

        $periodeLabel = $this->labaRugiPeriodLabel($modePeriode, $tanggalAwal, $tanggalAkhir, $month ?? null, $year ?? null);

        return Excel::download(
            new NeracaSaldoExport(
                $tanggalAwal,
                $tanggalAkhir,
                app(PeriodLockService::class)->statusLabel($tanggalAkhir),
                $periodeLabel
            ),
            'neraca_saldo_' . $tanggalAwal . '_' . $tanggalAkhir . '.xlsx'
        );
    }

    public function arusKas(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $periodLockStatus = app(PeriodLockService::class)->statusLabel($tanggalAkhir);

        $cashAccounts = Coa::active()->posting()->where('tipe', 'Aset')
            ->where(function ($query) {
                $query->where('nama_akun', 'like', '%Kas%')
                    ->orWhere('nama_akun', 'like', '%Bank%')
                    ->orWhere('kategori', 'like', '%Kas%')
                    ->orWhere('kategori', 'like', '%Bank%');
            })
            ->orderBy('kode_akun')
            ->get();

        if ($cashAccounts->isEmpty()) {
            $cashAccounts = Coa::active()->posting()->byTipe('Aset')->orderBy('kode_akun')->get();
        }

        $rows = collect();
        $totalInflow = 0;
        $totalOutflow = 0;

        $totalsByCoa = $cashAccounts->isEmpty()
            ? []
            : $this->journalQueryService->getSumByMultipleCoaIds($tanggalAwal, $tanggalAkhir, $cashAccounts->pluck('id')->all());

        foreach ($cashAccounts as $coa) {
            $debit = $totalsByCoa[$coa->id]['debit'] ?? 0;
            $kredit = $totalsByCoa[$coa->id]['kredit'] ?? 0;

            $inflow = $debit;
            $outflow = $kredit;
            $net = $debit - $kredit;

            $rows->push([
                'coa' => $coa,
                'inflow' => $inflow,
                'outflow' => $outflow,
                'net' => $net,
            ]);

            $totalInflow += $inflow;
            $totalOutflow += $outflow;
        }

        return view('laporan.arus_kas', compact(
            'tanggalAwal',
            'tanggalAkhir',
            'rows',
            'totalInflow',
            'totalOutflow',
            'periodLockStatus'
        ));
    }

    public function exportArusKas(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        return Excel::download(
            new ArusKasExport($tanggalAwal, $tanggalAkhir, app(PeriodLockService::class)->statusLabel($tanggalAkhir)),
            'arus_kas_' . $tanggalAwal . '_' . $tanggalAkhir . '.xlsx'
        );
    }

    public function tutupBuku(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir, $year] = $this->resolveTutupBukuPeriod($request);

        $report = $this->tutupBukuReportService->build($tanggalAwal, $tanggalAkhir);
        $yearClosing = YearClosing::where('year', $year)->first();

        return view('laporan.tutup_buku', array_merge($report, [
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'year' => $year,
            'yearClosing' => $yearClosing,
            'isYearClosed' => (bool) ($yearClosing?->closed_at),
            'isFullYearPeriod' => $this->isFullCalendarYearPeriod($tanggalAwal, $tanggalAkhir),
            'hasPeriodEnded' => $this->hasClosingPeriodEnded($tanggalAkhir),
            'hasClosingJournal' => $this->hasClosingJournal($year),
            'hasUnpostedJournals' => $this->hasUnpostedJournals($year),
            'periodLockStatus' => app(PeriodLockService::class)->statusLabel($tanggalAkhir),
        ]));
    }

    public function exportTutupBuku(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir] = $this->resolveTutupBukuPeriod($request);
        $report = $this->tutupBukuReportService->build($tanggalAwal, $tanggalAkhir);
        $report['periodLockStatus'] = app(PeriodLockService::class)->statusLabel($tanggalAkhir);

        return Excel::download(
            new TutupBukuExport($report),
            'tutup_buku_' . $tanggalAwal . '_' . $tanggalAkhir . '.xlsx'
        );
    }

    public function perubahanEkuitas(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfYear()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

        $report = $this->tutupBukuReportService->build($tanggalAwal, $tanggalAkhir);
        $saldoAwal = $report['saldoAwalLabaDitahan'] ?? 0;
        $labaBersih = $report['labaBersih'] ?? 0;
        $koreksi = 0;
        $saldoAkhir = $saldoAwal + $labaBersih + $koreksi;
        $periodLockStatus = app(PeriodLockService::class)->statusLabel($tanggalAkhir);

        return view('laporan.perubahan_ekuitas', compact(
            'tanggalAwal',
            'tanggalAkhir',
            'saldoAwal',
            'labaBersih',
            'koreksi',
            'saldoAkhir',
            'periodLockStatus'
        ));
    }

    public function exportPerubahanEkuitas(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfYear()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $report = $this->tutupBukuReportService->build($tanggalAwal, $tanggalAkhir);

        return Excel::download(
            new PerubahanEkuitasExport([
                'tanggalAwal' => $tanggalAwal,
                'tanggalAkhir' => $tanggalAkhir,
                'saldoAwal' => $report['saldoAwalLabaDitahan'] ?? 0,
                'labaBersih' => $report['labaBersih'] ?? 0,
                'koreksi' => 0,
                'saldoAkhir' => ($report['saldoAwalLabaDitahan'] ?? 0) + ($report['labaBersih'] ?? 0),
                'periodLockStatus' => app(PeriodLockService::class)->statusLabel($tanggalAkhir),
            ]),
            'perubahan_ekuitas_' . $tanggalAwal . '_' . $tanggalAkhir . '.xlsx'
        );
    }

    public function generateClosingJournal(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir, $year] = $this->resolveTutupBukuPeriod($request);

        if (! $this->isFullCalendarYearPeriod($tanggalAwal, $tanggalAkhir)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Jurnal penutup hanya boleh dibuat untuk periode satu tahun penuh: 1 Januari sampai 31 Desember.');
        }

        if (! $this->hasClosingPeriodEnded($tanggalAkhir)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', "Tahun {$year} masih berjalan. Jurnal penutup baru dapat dibuat mulai 1 Januari " . ($year + 1) . '.');
        }

        if ($this->hasUnpostedJournals($year)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', "Masih ada jurnal draft/belum posted di tahun {$year}. Posting atau void dulu sebelum tutup buku.");
        }

        if ($this->hasClosingJournal($year)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', "Jurnal penutup tahun {$year} sudah ada. Sistem mencegah pembuatan duplikat.");
        }

        $exitCode = Artisan::call('app:generate-closing-journal', [
            '--tanggal-awal' => $tanggalAwal,
            '--tanggal-akhir' => $tanggalAkhir,
            '--keterangan' => 'Jurnal Tutup Otomatis dari UI',
            '--created-by' => auth()->id() ?? 1,
        ]);

        if ($exitCode !== 0) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Gagal membuat jurnal penutup otomatis.');
        }

        app(AuditLogService::class)->record('generate-closing-journal', 'tutup-buku', null, "Generate jurnal penutup tahun {$year}.", [
            'year' => $year,
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
        ]);

        return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
            ->with('success', 'Jurnal penutup otomatis berhasil dibuat dan diposting.');
    }

    public function markYearClosed(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir, $year] = $this->resolveTutupBukuPeriod($request);

        if (! $this->isFullCalendarYearPeriod($tanggalAwal, $tanggalAkhir)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Tahun hanya boleh ditandai tutup untuk periode 1 Januari sampai 31 Desember.');
        }

        if (! $this->hasClosingPeriodEnded($tanggalAkhir)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', "Tahun {$year} masih berjalan dan baru dapat ditandai tutup mulai 1 Januari " . ($year + 1) . '.');
        }

        if (! $this->hasClosingJournal($year) && $this->periodHasProfitLossActivity($tanggalAwal, $tanggalAkhir)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Buat jurnal penutup terlebih dahulu sebelum menandai tahun sebagai tutup.');
        }

        if (! $this->closingJournalsAreBalanced($year)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Jurnal penutup tahun ini belum balance. Proses ditahan.');
        }

        $exitCode = Artisan::call('app:mark-year-closed', ['year' => $year]);

        if ($exitCode !== 0) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Gagal menandai tahun sebagai sudah ditutup.');
        }

        $yearClosing = YearClosing::where('year', $year)->first();
        app(AuditLogService::class)->record('mark-closed', 'tutup-buku', $yearClosing, "Menandai tahun {$year} sebagai tutup.", [
            'year' => $year,
        ]);

        return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
            ->with('success', "Tahun {$year} berhasil ditandai sebagai tutup.");
    }

    public function archiveJournal(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir, $year] = $this->resolveTutupBukuPeriod($request);

        if (! $this->isFullCalendarYearPeriod($tanggalAwal, $tanggalAkhir)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Arsip jurnal tutup tahun hanya boleh untuk periode 1 Januari sampai 31 Desember.');
        }

        if (! $this->closingJournalsAreBalanced($year)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Jurnal penutup tahun ini belum balance. Arsip dibatalkan.');
        }

        $exitCode = Artisan::call('app:archive-journal', ['year' => $year]);

        if ($exitCode !== 0) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Gagal mengarsipkan jurnal untuk tahun ini.');
        }

        app(AuditLogService::class)->record('archive', 'tutup-buku', null, "Mengarsipkan jurnal tahun {$year}.", [
            'year' => $year,
        ]);

        return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
            ->with('success', "Jurnal tahun {$year} berhasil diarsipkan.");
    }

    public function closeYear(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir, $year] = $this->resolveTutupBukuPeriod($request);

        if (! $this->isFullCalendarYearPeriod($tanggalAwal, $tanggalAkhir)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Selesaikan Tutup Tahun hanya boleh untuk periode satu tahun penuh: 1 Januari sampai 31 Desember.');
        }

        if (! $this->hasClosingPeriodEnded($tanggalAkhir)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', "Tahun {$year} masih berjalan. Proses tutup tahun baru dapat dilakukan mulai 1 Januari " . ($year + 1) . '.');
        }

        if (YearClosing::where('year', $year)->whereNotNull('closed_at')->exists()) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', "Tahun {$year} sudah ditutup. Sistem mencegah proses ulang.");
        }

        if ($this->hasUnpostedJournals($year)) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', "Masih ada jurnal draft/belum posted di tahun {$year}. Posting atau void dulu sebelum tutup buku.");
        }

        DB::beginTransaction();

        try {
            if (! $this->hasClosingJournal($year)) {
                $generateExitCode = Artisan::call('app:generate-closing-journal', [
                    '--tanggal-awal' => $tanggalAwal,
                    '--tanggal-akhir' => $tanggalAkhir,
                    '--keterangan' => 'Jurnal Tutup Otomatis dari UI',
                    '--created-by' => auth()->id() ?? 1,
                ]);

                if ($generateExitCode !== 0) {
                    throw new \RuntimeException('Gagal membuat jurnal penutup otomatis.');
                }
            }

            if (! $this->closingJournalsAreBalanced($year)) {
                throw new \RuntimeException('Jurnal penutup tahun ini belum balance. Proses ditahan.');
            }

            // Validasi sebelum status tahun dan penyimpanan jurnal diubah.
            $neraca = $this->neracaReportService->build([
                'from_date' => $tanggalAwal,
                'to_date' => $tanggalAkhir,
            ]);

            if (! $neraca['is_balance']) {
                throw new \RuntimeException('Neraca akhir tahun belum balance. Tutup tahun dibatalkan dan tidak ada data yang diarsipkan.');
            }

            if (Artisan::call('app:mark-year-closed', ['year' => $year]) !== 0) {
                throw new \RuntimeException('Gagal menandai tahun sebagai tutup.');
            }

            if (Artisan::call('app:archive-journal', ['year' => $year]) !== 0) {
                throw new \RuntimeException('Gagal mengarsipkan jurnal. Seluruh proses dibatalkan.');
            }

            // Pastikan pemindahan ke arsip tidak mengubah hasil laporan.
            $neracaSetelahArsip = $this->neracaReportService->build([
                'from_date' => $tanggalAwal,
                'to_date' => $tanggalAkhir,
            ]);

            if (! $neracaSetelahArsip['is_balance']) {
                throw new \RuntimeException('Validasi Neraca setelah arsip gagal. Seluruh proses dibatalkan.');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', $e->getMessage());
        }

        $yearClosing = YearClosing::where('year', $year)->first();
        app(AuditLogService::class)->record('close-year', 'tutup-buku', $yearClosing, "Menyelesaikan tutup tahun {$year}.", [
            'year' => $year,
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
        ]);

        return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
            ->with('success', "Proses tutup tahun {$year} selesai. Jurnal penutup dibuat, tahun ditandai tutup, jurnal diarsipkan, dan Neraca akhir tahun balance.");
    }

    public function removeClosingJournal(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir, $year] = $this->resolveTutupBukuPeriod($request);

        if (YearClosing::where('year', $year)->whereNotNull('closed_at')->exists()) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', "Tahun {$year} masih berstatus tutup. Reset Tutup Tahun terlebih dahulu sebelum menghapus jurnal penutup.");
        }

        $closingJournals = Jurnal::where(function ($query): void {
            $query->where('keterangan', 'like', '%Jurnal Tutup%')
                ->orWhere('referensi', 'AUTO-CLOSING')
                ->orWhere('no_jurnal', 'like', 'TJ%');
        })
            ->whereYear('tanggal', $year)
            ->get();

        if ($closingJournals->isEmpty()) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('warning', "Tidak ada jurnal penutup ditemukan untuk tahun {$year}.");
        }

        foreach ($closingJournals as $journal) {
            DB::table('jurnal_items_archives')->where('jurnal_id', $journal->id)->delete();
            $journal->items()->delete();
            $journal->delete();
        }

        app(AuditLogService::class)->record('remove-closing-journal', 'tutup-buku', null, "Menghapus jurnal penutup tahun {$year}.", [
            'year' => $year,
            'count' => $closingJournals->count(),
        ]);

        return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
            ->with('success', "Jurnal penutup untuk tahun {$year} berhasil dihapus.");
    }

    public function resetYearClose(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir, $year] = $this->resolveTutupBukuPeriod($request);

        $journalIds = Jurnal::whereYear('tanggal', $year)->pluck('id');
        $archivedItems = DB::table('jurnal_items_archives')
            ->whereIn('jurnal_id', $journalIds)
            ->get();

        if ($archivedItems->isNotEmpty() && JurnalItem::whereIn('jurnal_id', $journalIds)->exists()) {
            return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
                ->with('error', 'Reset dibatalkan karena ditemukan campuran jurnal aktif dan arsip. Hubungi administrator untuk pemeriksaan data.');
        }

        DB::transaction(function () use ($year, $archivedItems): void {
            foreach ($archivedItems->chunk(500) as $chunk) {
                JurnalItem::insert($chunk->map(fn($item) => [
                    'jurnal_id' => $item->jurnal_id,
                    'coa_id' => $item->coa_id,
                    'deskripsi' => $item->deskripsi,
                    'debit' => $item->debit,
                    'kredit' => $item->kredit,
                    'created_at' => $item->created_at,
                    'updated_at' => now(),
                ])->all());
            }

            if ($archivedItems->isNotEmpty()) {
                DB::table('jurnal_items_archives')
                    ->whereIn('id', $archivedItems->pluck('id'))
                    ->delete();
            }

            YearClosing::where('year', $year)->delete();
        });

        app(AuditLogService::class)->record('reset-year-close', 'tutup-buku', null, "Reset catatan tutup tahun {$year}.", [
            'year' => $year,
        ]);

        return $this->redirectTutupBuku($tanggalAwal, $tanggalAkhir)
            ->with('success', "Tutup tahun {$year} berhasil di-reset dan jurnal arsip telah dikembalikan untuk revisi.");
    }

    private function resolveTutupBukuPeriod(Request $request): array
    {
        // Tampilkan tahun berjalan sebagai preview. Pengaman hasClosingPeriodEnded()
        // tetap mencegah generate/finalisasi sebelum 1 Januari tahun berikutnya.
        $defaultClosingYear = now()->year;

        $year = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->year
            : (int) ($request->year ?? $defaultClosingYear);

        $tanggalAwal = $request->filled('tanggal_awal')
            ? Carbon::parse($request->tanggal_awal)->format('Y-m-d')
            : Carbon::create($year, 1, 1)->format('Y-m-d');

        $tanggalAkhir = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->format('Y-m-d')
            : Carbon::create($year, 12, 31)->format('Y-m-d');

        return [$tanggalAwal, $tanggalAkhir, Carbon::parse($tanggalAkhir)->year];
    }

    private function labaRugiPeriodLabel(string $modePeriode, string $tanggalAwal, string $tanggalAkhir, ?string $month, ?string $year): string
    {
        $monthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        if ($modePeriode === 'bulanan') {
            $periodMonth = str_pad((string) ($month ?? Carbon::parse($tanggalAwal)->format('m')), 2, '0', STR_PAD_LEFT);
            $periodYear = (string) ($year ?? Carbon::parse($tanggalAwal)->format('Y'));

            return strtoupper(($monthNames[$periodMonth] ?? Carbon::parse($tanggalAwal)->format('F')) . ' ' . $periodYear);
        }

        if ($modePeriode === 'tahunan') {
            return 'TAHUN ' . ($year ?? Carbon::parse($tanggalAwal)->format('Y'));
        }

        return date('d F Y', strtotime($tanggalAwal)) . ' - ' . date('d F Y', strtotime($tanggalAkhir));
    }

    private function isFullCalendarYearPeriod(string $tanggalAwal, string $tanggalAkhir): bool
    {
        $start = Carbon::parse($tanggalAwal);
        $end = Carbon::parse($tanggalAkhir);

        return $start->year === $end->year
            && $start->isSameDay(Carbon::create($start->year, 1, 1))
            && $end->isSameDay(Carbon::create($end->year, 12, 31));
    }

    private function hasClosingPeriodEnded(string $tanggalAkhir): bool
    {
        return Carbon::today()->greaterThan(Carbon::parse($tanggalAkhir)->startOfDay());
    }

    private function redirectTutupBuku(string $tanggalAwal, string $tanggalAkhir)
    {
        return redirect()->route('laporan.tutup_buku', [
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
        ]);
    }

    private function getClosingJournals(int $year)
    {
        return Jurnal::query()
            ->whereYear('tanggal', $year)
            ->where(function ($query): void {
                $query->where('referensi', 'AUTO-CLOSING')
                    ->orWhere('keterangan', 'like', '%Jurnal Tutup%')
                    ->orWhere('no_jurnal', 'like', 'TJ%');
            })
            ->get();
    }

    private function hasClosingJournal(int $year): bool
    {
        return $this->getClosingJournals($year)->isNotEmpty();
    }

    private function closingJournalsAreBalanced(int $year): bool
    {
        $journals = $this->getClosingJournals($year);

        if ($journals->isEmpty()) {
            return true;
        }

        foreach ($journals as $journal) {
            $totalDebit = 0;
            $totalKredit = 0;

            foreach (['jurnal_items', 'jurnal_items_archives'] as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $totals = DB::table($table)
                    ->where('jurnal_id', $journal->id)
                    ->selectRaw('COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(kredit), 0) as kredit')
                    ->first();

                $totalDebit += (float) $totals->debit;
                $totalKredit += (float) $totals->kredit;
            }

            if (abs($totalDebit - $totalKredit) >= 1) {
                return false;
            }
        }

        return true;
    }

    private function hasUnpostedJournals(int $year): bool
    {
        return Jurnal::query()
            ->whereYear('tanggal', $year)
            ->where(function ($query): void {
                $query->where('is_posted', false)
                    ->orWhere('status', '!=', Jurnal::STATUS_POSTED);
            })
            ->exists();
    }

    private function periodHasProfitLossActivity(string $tanggalAwal, string $tanggalAkhir): bool
    {
        $report = $this->tutupBukuReportService->build($tanggalAwal, $tanggalAkhir);

        return abs((float) $report['totalPendapatan']) >= 1
            || abs((float) $report['totalBeban']) >= 1;
    }

    private function buildBalanceSheetTree($coas, $sampaiTanggal, array $allowedTypes, $parentId = null): array
    {
        $nodes = $coas->filter(function ($coa) use ($parentId, $allowedTypes) {
            if ($parentId === null) {
                return is_null($coa->parent_id) && in_array($coa->tipe, $allowedTypes, true);
            }

            return (int) $coa->parent_id === (int) $parentId;
        })->sortBy('kode_akun')->values();

        return $nodes->map(function ($coa) use ($coas, $sampaiTanggal, $allowedTypes) {
            $children = $this->buildBalanceSheetTree($coas, $sampaiTanggal, $allowedTypes, $coa->id);
            $balance = $this->hitungSaldoAkunNeraca($coa, $sampaiTanggal);
            $subtotal = $balance + collect($children)->sum(fn($child) => $child['subtotal']);

            return [
                'coa' => $coa,
                'balance' => $balance,
                'subtotal' => $subtotal,
                'children' => $children,
            ];
        })->all();
    }

    private function hitungTotalLabaRugi($tipe, $sampaiTanggal)
    {
        $coaIds = Coa::active()->posting()->byTipe($tipe)->pluck('id');
        $totalDebit = JurnalItem::whereIn('coa_id', $coaIds)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereDate('tanggal', '<=', $sampaiTanggal))
            ->sum('debit');
        $totalKredit = JurnalItem::whereIn('coa_id', $coaIds)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereDate('tanggal', '<=', $sampaiTanggal))
            ->sum('kredit');
        return $tipe == 'Pendapatan' ? $totalKredit - $totalDebit : $totalDebit - $totalKredit;
    }

    private function hitungSaldoAkunNeraca($coa, $sampaiTanggal)
    {
        $totalDebit = JurnalItem::where('coa_id', $coa->id)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereDate('tanggal', '<=', $sampaiTanggal))
            ->sum('debit');
        $totalKredit = JurnalItem::where('coa_id', $coa->id)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereDate('tanggal', '<=', $sampaiTanggal))
            ->sum('kredit');

        return strtolower((string) $coa->saldo_normal) === 'kredit'
            ? $totalKredit - $totalDebit
            : $totalDebit - $totalKredit;
    }

    private function hitungSaldoAkunLaba($coaId, $tipe, $tanggalAwal, $tanggalAkhir)
    {
        $totalDebit = JurnalItem::where('coa_id', $coaId)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]))
            ->sum('debit');
        $totalKredit = JurnalItem::where('coa_id', $coaId)
            ->whereHas('jurnal', fn($q) => $q->where('is_posted', true)
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]))
            ->sum('kredit');
        return $tipe == 'Pendapatan' ? $totalKredit - $totalDebit : $totalDebit - $totalKredit;
    }

    private function isPajakPenghasilanAccount(Coa $coa): bool
    {
        $category = strtolower(trim((string) $coa->kategori));
        $name = strtolower(trim((string) $coa->nama_akun));

        return $category === 'pajak penghasilan'
            || str_contains($name, 'pajak penghasilan');
    }
}
