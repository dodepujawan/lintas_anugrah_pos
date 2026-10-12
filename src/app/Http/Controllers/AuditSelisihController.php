<?php

namespace App\Http\Controllers;

use App\Models\AccountMapping;
use App\Models\Jurnal;
use App\Services\JournalQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditSelisihController extends Controller
{
    public function __construct(private readonly JournalQueryService $journalQueryService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'jenis' => ['nullable', 'in:semua,rekonsiliasi,penjualan,jurnal,sumber,duplikat'],
        ]);

        $tanggalAkhir = Carbon::parse($filters['tanggal_akhir'] ?? $this->latestAuditDate())->format('Y-m-d');
        $tanggalAwal = Carbon::parse($filters['tanggal_awal'] ?? Carbon::parse($tanggalAkhir)->startOfMonth())->format('Y-m-d');
        $jenis = $filters['jenis'] ?? 'semua';

        $checks = collect();
        $issues = collect();

        $this->auditReceivables($tanggalAkhir, $checks, $issues);
        $this->auditSales($tanggalAwal, $tanggalAkhir, $checks, $issues);
        $this->auditPayables($tanggalAkhir, $checks, $issues);
        $this->auditDriverReceivables($tanggalAkhir, $checks, $issues);
        $this->auditJournals($tanggalAwal, $tanggalAkhir, $checks, $issues);
        $this->auditSources($tanggalAwal, $tanggalAkhir, $checks, $issues);
        $this->auditDuplicateReferences($tanggalAwal, $tanggalAkhir, $checks, $issues);

        $filteredIssues = $jenis === 'semua'
            ? $issues
            : $issues->where('category', $jenis)->values();

        $totalSelisih = (float) $filteredIssues->sum(fn (array $issue) => abs((float) ($issue['amount'] ?? 0)));
        $impactWarnings = $this->impactWarnings($filteredIssues, $totalSelisih);

        $summary = [
            'checks' => $checks->count(),
            'ok' => $checks->where('status', 'ok')->count(),
            'warning' => $checks->where('status', 'warning')->count(),
            'issues' => $filteredIssues->count(),
            'difference' => (float) ($checks->firstWhere('key', 'receivable_reconciliation')['difference'] ?? 0),
            'total_selisih' => $totalSelisih,
        ];

        return view('audit-selisih.index', compact(
            'tanggalAwal', 'tanggalAkhir', 'jenis', 'checks', 'filteredIssues', 'summary', 'impactWarnings'
        ));
    }

    private function auditReceivables(string $asOf, Collection $checks, Collection $issues): void
    {
        $receivableCoaId = AccountMapping::getCoaId('accounts_receivable');
        if (! $receivableCoaId) {
            $checks->push($this->check('receivable_reconciliation', 'Rekonsiliasi Piutang', 'warning', null, null, null, 'Mapping akun Piutang Usaha belum tersedia.'));
            $issues->push($this->issue('rekonsiliasi', 'warning', 'Mapping Piutang Usaha belum tersedia', 'Pilih akun accounts_receivable agar buku pembantu dapat dibandingkan dengan buku besar.', null, route('account-mappings.index'), 'Atur Mapping'));
            return;
        }

        $settled = DB::table('bard')->join('barh', 'barh.NOBAYAR', '=', 'bard.NOBAYAR')
            ->where('barh.TGLBAYAR', '<=', $asOf)
            ->where(function ($query) use ($asOf): void {
                $query->where('barh.STATUS', '!=', 'VOID')
                    ->orWhere('barh.VOIDED_AT', '>', Carbon::parse($asOf)->endOfDay());
            })
            ->selectRaw('bard.NOFAKTUR, SUM(bard.NILAI_BAYAR + bard.PPH23 + bard.DISCOUNT + bard.BANK_ADMIN + bard.RETUR) AS settled')
            ->groupBy('bard.NOFAKTUR');

        $subledger = (float) (DB::table('arh')
            ->leftJoinSub($settled, 'paid', fn ($join) => $join->on('paid.NOFAKTUR', '=', 'arh.NOFAKTUR'))
            ->where('arh.TGLFAKTUR', '<=', $asOf)
            ->selectRaw('COALESCE(SUM(GREATEST(arh.PIUTANG - COALESCE(paid.settled, 0), 0)), 0) AS balance')
            ->value('balance') ?? 0);

        $ledger = $this->journalQueryService->getSumByCoaId('1900-01-01', $asOf, $receivableCoaId);
        $ledgerBalance = $ledger['debit'] - $ledger['kredit'];
        $difference = $subledger - $ledgerBalance;
        $status = abs($difference) <= 0.01 ? 'ok' : 'danger';
        $message = $status === 'ok' ? 'Buku pembantu sesuai dengan buku besar.' : ($difference > 0
            ? 'Buku pembantu lebih tinggi; cari invoice yang belum dijurnal atau kredit piutang berlebih.'
            : 'Buku besar lebih tinggi; cari debit piutang berlebih atau pelunasan yang belum dijurnal.');

        $checks->push($this->check('receivable_reconciliation', 'Rekonsiliasi Piutang', $status, $subledger, $ledgerBalance, $difference, $message));
        if ($status !== 'ok') {
            $suspects = $this->receivableReconciliationSuspects($asOf, $receivableCoaId, $difference);
            if ($suspects->isEmpty()) {
                $issues->push($this->issue(
                    'rekonsiliasi',
                    'danger',
                    'Saldo piutang berbeda dengan buku besar',
                    $message,
                    abs($difference),
                    route('laporan.piutang', ['tanggal_posisi' => $asOf]),
                    'Buka Laporan Piutang'
                ));
            } else {
                foreach ($suspects as $suspect) {
                    $issues->push($this->issue(
                        'rekonsiliasi',
                        'danger',
                        $suspect['title'],
                        $suspect['description'],
                        $suspect['amount'],
                        $suspect['url'],
                        $suspect['action']
                    ));
                }

                $explained = (float) $suspects->sum(fn (array $suspect) => abs((float) ($suspect['amount'] ?? 0)));
                $residual = abs($difference) - $explained;
                if ($residual > 0.01) {
                    $issues->push($this->issue(
                        'rekonsiliasi',
                        'warning',
                        'Sisa selisih piutang belum terlacak otomatis',
                        'Total selisih Rp '.number_format(abs($difference), 2, ',', '.').' dikurangi temuan di atas masih tersisa Rp '.number_format($residual, 2, ',', '.').'. Periksa laporan piutang dan buku besar akun Piutang Usaha.',
                        $residual,
                        route('laporan.piutang', ['tanggal_posisi' => $asOf]),
                        'Buka Laporan Piutang'
                    ));
                }
            }
        }
    }

    /**
     * Temukan sumber paling mungkin untuk selisih rekonsiliasi piutang.
     *
     * @return Collection<int, array{title:string,description:string,amount:float,url:string,action:string}>
     */
    private function receivableReconciliationSuspects(string $asOf, int $receivableCoaId, float $difference): Collection
    {
        try {
            return $this->buildReceivableReconciliationSuspects($asOf, $receivableCoaId, $difference);
        } catch (\Throwable $e) {
            report($e);

            return collect([[
                'title' => 'Saldo piutang berbeda dengan buku besar',
                'description' => 'Selisih terdeteksi, tetapi pelacak otomatis gagal dijalankan. Periksa laporan piutang dan jurnal yang memakai akun Piutang Usaha tanpa pelunasan.',
                'amount' => abs($difference),
                'url' => route('laporan.piutang', ['tanggal_posisi' => $asOf]),
                'action' => 'Buka Laporan Piutang',
            ]]);
        }
    }

    /**
     * @return Collection<int, array{title:string,description:string,amount:float,url:string,action:string}>
     */
    private function buildReceivableReconciliationSuspects(string $asOf, int $receivableCoaId, float $difference): Collection
    {
        $suspects = collect();

        $absDifference = abs($difference);
        $lookbackDate = Carbon::parse($asOf)->subYear()->format('Y-m-d');
        $amountFloor = $absDifference > 0.01 ? ($absDifference * 0.5) : 0;

        // Query ringan: agregasi per jurnal dulu, tanpa join AR/barh yang membesarkan hasil.
        $candidateRows = DB::table('jurnal_items')
            ->join('jurnals', 'jurnals.id', '=', 'jurnal_items.jurnal_id')
            ->where('jurnal_items.coa_id', $receivableCoaId)
            ->where('jurnals.tanggal', '<=', $asOf)
            ->where('jurnals.is_posted', true)
            ->where(function ($query) use ($lookbackDate, $amountFloor): void {
                $query->where('jurnals.tanggal', '>=', $lookbackDate);
                if ($amountFloor > 0) {
                    $query->orWhere('jurnal_items.debit', '>=', $amountFloor)
                        ->orWhere('jurnal_items.kredit', '>=', $amountFloor);
                }
            })
            ->groupBy('jurnals.id', 'jurnals.no_jurnal', 'jurnals.tanggal', 'jurnals.keterangan', 'jurnals.referensi', 'jurnals.created_by')
            ->selectRaw('
                jurnals.id,
                jurnals.no_jurnal,
                jurnals.tanggal,
                jurnals.keterangan,
                jurnals.referensi,
                jurnals.created_by,
                COALESCE(SUM(jurnal_items.debit), 0) AS debit,
                COALESCE(SUM(jurnal_items.kredit), 0) AS kredit
            ')
            ->orderByDesc('jurnals.tanggal')
            ->limit(200)
            ->get()
            ->filter(fn ($row) => abs(((float) $row->kredit) - ((float) $row->debit)) > 0.01)
            ->values();

        if ($candidateRows->isNotEmpty()) {
            $journalNumbers = $candidateRows->pluck('no_jurnal')->filter()->unique()->values();

            $linkedToArh = DB::table('arh')
                ->whereIn('NOJURNAL', $journalNumbers)
                ->distinct()
                ->pluck('NOJURNAL')
                ->flip();

            $linkedToBarh = DB::table('barh')
                ->where(function ($query) use ($journalNumbers): void {
                    $query->whereIn('NOJURNAL', $journalNumbers)
                        ->orWhereIn('NOJURNAL_PEMBALIK', $journalNumbers);
                })
                ->get(['NOJURNAL', 'NOJURNAL_PEMBALIK'])
                ->flatMap(fn ($row) => array_filter([$row->NOJURNAL, $row->NOJURNAL_PEMBALIK]))
                ->unique()
                ->flip();

            $userIds = $candidateRows->pluck('created_by')->filter()->unique()->values();
            $users = $userIds->isEmpty()
                ? collect()
                : DB::table('users')->whereIn('id', $userIds)->get(['id', 'username', 'name'])->keyBy('id');

            $manualJournals = $candidateRows
                ->reject(fn ($row) => isset($linkedToArh[$row->no_jurnal]) || isset($linkedToBarh[$row->no_jurnal]))
                ->sortBy(fn ($row) => abs((((float) $row->kredit) - ((float) $row->debit)) - $difference))
                ->values();

            $tolerance = max(0.01, abs($difference) * 0.05);
            $closeMatches = $manualJournals->filter(function ($row) use ($difference, $tolerance) {
                $netCredit = ((float) $row->kredit) - ((float) $row->debit);

                return abs($netCredit - $difference) <= $tolerance;
            });

            $manualJournals = ($closeMatches->isNotEmpty() ? $closeMatches : $manualJournals)->take(5)->values();

            foreach ($manualJournals as $row) {
                $debit = (float) $row->debit;
                $kredit = (float) $row->kredit;
                $netCredit = $kredit - $debit;
                $user = $users->get($row->created_by);
                $userLabel = trim((string) ($user->username ?? $user->name ?? 'tidak diketahui'));
                $keterangan = trim((string) ($row->keterangan ?: '-'));
                $referensi = trim((string) ($row->referensi ?: '-'));
                $arah = $netCredit > 0
                    ? 'mengurangi buku besar lewat kredit Piutang Usaha tanpa pelunasan di buku pembantu'
                    : 'menambah buku besar lewat debit Piutang Usaha tanpa invoice di buku pembantu';

                $suspects->push([
                    'title' => 'Jurnal '.$row->no_jurnal.' memakai Piutang Usaha tanpa tautan AR',
                    'description' => 'Jurnal '.$row->no_jurnal.' tanggal '.Carbon::parse($row->tanggal)->format('d/m/Y')
                        .' oleh '.$userLabel
                        .', keterangan "'.$keterangan.'"'
                        .', referensi '.$referensi
                        .'. Nilai D '.number_format($debit, 2, ',', '.')
                        .' / K '.number_format($kredit, 2, ',', '.')
                        .'. Dugaan: '.$arah.'.',
                    'amount' => abs($netCredit),
                    'url' => route('jurnal.show', $row->id),
                    'action' => 'Lacak Jurnal Selisih',
                ]);
            }
        }

        if ($suspects->isNotEmpty()) {
            return $suspects;
        }

        $missingInvoiceJournal = DB::table('arh')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'arh.NOJURNAL')
                    ->where('jurnals.is_posted', true);
            })
            ->where('arh.TGLFAKTUR', '<=', $asOf)
            ->whereNotNull('arh.NOJURNAL')
            ->where('arh.NOJURNAL', '!=', '')
            ->whereNull('jurnals.id')
            ->select('arh.id', 'arh.NOFAKTUR', 'arh.PIUTANG', 'arh.NOJURNAL', 'arh.SOURCE')
            ->orderByDesc('arh.TGLFAKTUR')
            ->limit(5)
            ->get();

        foreach ($missingInvoiceJournal as $row) {
            $suspects->push([
                'title' => 'Invoice '.$row->NOFAKTUR.' tanpa jurnal posted',
                'description' => 'Invoice '.$row->NOFAKTUR.' (sumber '.($row->SOURCE ?: '-').') memakai nomor jurnal '.($row->NOJURNAL ?: '-').' tetapi jurnal posted tidak ditemukan. Nilai '.number_format((float) $row->PIUTANG, 2, ',', '.').'.',
                'amount' => (float) $row->PIUTANG,
                'url' => route('piutang.show', $row->id),
                'action' => 'Lacak Invoice',
            ]);
        }

        if ($suspects->isNotEmpty()) {
            return $suspects;
        }

        $missingPaymentJournal = DB::table('barh')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'barh.NOJURNAL')
                    ->where('jurnals.is_posted', true);
            })
            ->where('barh.TGLBAYAR', '<=', $asOf)
            ->where(fn ($query) => $query->whereNull('barh.STATUS')->orWhere('barh.STATUS', '!=', 'VOID'))
            ->whereNotNull('barh.NOJURNAL')
            ->where('barh.NOJURNAL', '!=', '')
            ->whereNull('jurnals.id')
            ->select('barh.id', 'barh.NOBAYAR', 'barh.JUMLAH_BAYAR', 'barh.NOJURNAL')
            ->orderByDesc('barh.TGLBAYAR')
            ->limit(5)
            ->get();

        foreach ($missingPaymentJournal as $row) {
            $suspects->push([
                'title' => 'Pelunasan '.$row->NOBAYAR.' tanpa jurnal posted',
                'description' => 'Pelunasan '.$row->NOBAYAR.' memakai nomor jurnal '.($row->NOJURNAL ?: '-').' tetapi jurnal posted tidak ditemukan. Nilai '.number_format((float) $row->JUMLAH_BAYAR, 2, ',', '.').'.',
                'amount' => (float) $row->JUMLAH_BAYAR,
                'url' => route('pelunasan-piutang.show', $row->id),
                'action' => 'Lacak Pelunasan',
            ]);
        }

        if ($suspects->isNotEmpty()) {
            return $suspects;
        }

        $openingBalance = DB::table('arh')
            ->where('TGLFAKTUR', '<=', $asOf)
            ->where('SOURCE', 'like', 'SALDO_AWAL_%')
            ->where(function ($query): void {
                $query->whereNull('NOJURNAL')->orWhere('NOJURNAL', '');
            })
            ->selectRaw('COUNT(*) AS count, COALESCE(SUM(COALESCE(SALDO, PIUTANG, 0)), 0) AS balance')
            ->first();

        if ((float) ($openingBalance->balance ?? 0) > 0.01) {
            $suspects->push([
                'title' => 'Saldo awal piutang tanpa jurnal',
                'description' => 'Ada '.((int) $openingBalance->count).' saldo awal piutang tanpa nomor jurnal, total sekitar '.number_format((float) $openingBalance->balance, 2, ',', '.').'. Ini membuat buku pembantu berbeda bila saldo awal belum dimasukkan ke buku besar.',
                'amount' => (float) $openingBalance->balance,
                'url' => route('piutang.index'),
                'action' => 'Lacak Saldo Awal Piutang',
            ]);
        }

        return $suspects;
    }

    private function latestAuditDate(): string
    {
        $dates = collect([
            DB::table('arh')->max('TGLFAKTUR'),
            DB::table('barh')->max('TGLBAYAR'),
            DB::table('aph')->max('TGLFAKTUR'),
            DB::table('baph')->max('TGLBAYAR'),
            DB::table('driver_receivables')->max('date'),
            DB::table('driver_receivable_payments')->max('date'),
            DB::table('jurnals')->max('tanggal'),
        ])->filter();

        return $dates->isEmpty()
            ? now()->format('Y-m-d')
            : Carbon::parse($dates->max())->format('Y-m-d');
    }

    private function auditPayables(string $asOf, Collection $checks, Collection $issues): void
    {
        $payableCoaId = AccountMapping::getCoaId('accounts_payable');
        if (! $payableCoaId) {
            $checks->push($this->check('payable_reconciliation', 'Rekonsiliasi Hutang', 'warning', null, null, null, 'Mapping akun Hutang Usaha belum tersedia.'));
            $issues->push($this->issue('rekonsiliasi', 'warning', 'Mapping Hutang Usaha belum tersedia', 'Pilih akun accounts_payable agar rincian hutang dapat dibandingkan dengan buku besar.', null, route('account-mappings.index'), 'Atur Mapping'));
            return;
        }

        $settled = DB::table('bapd')->join('baph', 'baph.NOBAYAR', '=', 'bapd.NOBAYAR')
            ->where('baph.TGLBAYAR', '<=', $asOf)
            ->where(function ($query) use ($asOf): void {
                $query->where('baph.STATUS', '!=', 'VOID')
                    ->orWhere('baph.VOIDED_AT', '>', Carbon::parse($asOf)->endOfDay());
            })
            ->selectRaw('bapd.NOFAKTUR, SUM(bapd.NILAI_BAYAR + bapd.DISCOUNT + bapd.RETUR) AS settled')
            ->groupBy('bapd.NOFAKTUR');

        $subledger = (float) (DB::table('aph')
            ->leftJoinSub($settled, 'paid', fn ($join) => $join->on('paid.NOFAKTUR', '=', 'aph.NOFAKTUR'))
            ->where('aph.TGLFAKTUR', '<=', $asOf)
            ->selectRaw('COALESCE(SUM(GREATEST(aph.HUTANG - COALESCE(paid.settled, 0), 0)), 0) AS balance')
            ->value('balance') ?? 0);

        $ledger = $this->journalQueryService->getSumByCoaId('1900-01-01', $asOf, $payableCoaId);
        $ledgerBalance = $ledger['kredit'] - $ledger['debit'];
        $difference = $subledger - $ledgerBalance;
        $status = abs($difference) <= 0.01 ? 'ok' : 'danger';
        $message = $status === 'ok' ? 'Rincian hutang sesuai dengan buku besar.' : ($difference > 0
            ? 'Rincian hutang lebih tinggi; cari faktur hutang yang belum dijurnal atau debit hutang berlebih.'
            : 'Buku besar hutang lebih tinggi; cari kredit hutang berlebih atau pelunasan hutang yang belum dijurnal.');

        $checks->push($this->check('payable_reconciliation', 'Rekonsiliasi Hutang', $status, $subledger, $ledgerBalance, $difference, $message));
        if ($status !== 'ok') {
            $suspect = $this->firstPayableReconciliationSuspect($asOf, $payableCoaId);
            $issues->push($this->issue(
                'rekonsiliasi',
                'danger',
                'Saldo hutang berbeda dengan buku besar',
                $suspect['description'] ?? $message,
                abs($difference),
                $suspect['url'] ?? route('laporan.hutang', ['tanggal_posisi' => $asOf]),
                $suspect['action'] ?? 'Buka Laporan Hutang'
            ));
        }
    }

    private function firstPayableReconciliationSuspect(string $asOf, int $payableCoaId): ?array
    {
        $manualJournal = DB::table('jurnal_items')
            ->join('jurnals', 'jurnals.id', '=', 'jurnal_items.jurnal_id')
            ->leftJoin('aph', 'aph.NOJURNAL', '=', 'jurnals.no_jurnal')
            ->leftJoin('baph', function ($join): void {
                $join->on('baph.NOJURNAL', '=', 'jurnals.no_jurnal')
                    ->orOn('baph.NOJURNAL_PEMBALIK', '=', 'jurnals.no_jurnal');
            })
            ->where('jurnal_items.coa_id', $payableCoaId)
            ->where('jurnals.tanggal', '<=', $asOf)
            ->where('jurnals.is_posted', true)
            ->whereNull('aph.id')
            ->whereNull('baph.id')
            ->select('jurnals.id', 'jurnals.no_jurnal', 'jurnals.tanggal', 'jurnals.referensi', 'jurnal_items.debit', 'jurnal_items.kredit')
            ->orderByDesc('jurnals.tanggal')
            ->first();

        if ($manualJournal) {
            return [
                'description' => 'Jurnal '.$manualJournal->no_jurnal.' memakai akun Hutang Usaha tetapi tidak terhubung ke faktur/pelunasan hutang. Nilai D '.number_format((float) $manualJournal->debit, 2, ',', '.').' / K '.number_format((float) $manualJournal->kredit, 2, ',', '.').'.',
                'url' => route('jurnal.show', $manualJournal->id),
                'action' => 'Lacak Jurnal Selisih',
            ];
        }

        $missingInvoiceJournal = DB::table('aph')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'aph.NOJURNAL')
                    ->where('jurnals.is_posted', true);
            })
            ->where('aph.TGLFAKTUR', '<=', $asOf)
            ->whereNotNull('aph.NOJURNAL')
            ->whereNull('jurnals.id')
            ->select('aph.id', 'aph.NOFAKTUR', 'aph.HUTANG', 'aph.NOJURNAL')
            ->orderByDesc('aph.TGLFAKTUR')
            ->first();

        if ($missingInvoiceJournal) {
            return [
                'description' => 'Faktur hutang '.$missingInvoiceJournal->NOFAKTUR.' memakai nomor jurnal '.($missingInvoiceJournal->NOJURNAL ?: '-').' tetapi jurnal posted tidak ditemukan. Nilai '.number_format((float) $missingInvoiceJournal->HUTANG, 2, ',', '.').'.',
                'url' => route('hutang.show', $missingInvoiceJournal->id),
                'action' => 'Lacak Faktur Hutang',
            ];
        }

        $missingPaymentJournal = DB::table('baph')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'baph.NOJURNAL')
                    ->where('jurnals.is_posted', true);
            })
            ->where('baph.TGLBAYAR', '<=', $asOf)
            ->where(fn ($query) => $query->whereNull('baph.STATUS')->orWhere('baph.STATUS', '!=', 'VOID'))
            ->whereNotNull('baph.NOJURNAL')
            ->whereNull('jurnals.id')
            ->select('baph.id', 'baph.NOBAYAR', 'baph.JUMLAH_BAYAR', 'baph.NOJURNAL')
            ->orderByDesc('baph.TGLBAYAR')
            ->first();

        if ($missingPaymentJournal) {
            return [
                'description' => 'Pelunasan hutang '.$missingPaymentJournal->NOBAYAR.' memakai nomor jurnal '.($missingPaymentJournal->NOJURNAL ?: '-').' tetapi jurnal posted tidak ditemukan. Nilai '.number_format((float) $missingPaymentJournal->JUMLAH_BAYAR, 2, ',', '.').'.',
                'url' => route('pelunasan-hutang.show', $missingPaymentJournal->id),
                'action' => 'Lacak Pelunasan Hutang',
            ];
        }

        $openingBalance = DB::table('aph')
            ->where('TGLFAKTUR', '<=', $asOf)
            ->where('SOURCE', 'SALDO_AWAL_MANUAL')
            ->where(function ($query): void {
                $query->whereNull('NOJURNAL')->orWhere('NOJURNAL', '');
            })
            ->selectRaw('COUNT(*) AS count, COALESCE(SUM(SALDO), 0) AS balance')
            ->first();

        if ((float) ($openingBalance->balance ?? 0) > 0.01) {
            return [
                'description' => 'Ada saldo awal hutang tanpa jurnal sebesar '.number_format((float) $openingBalance->balance, 2, ',', '.').'. Ini membuat buku pembantu berbeda bila saldo awal belum dimasukkan ke buku besar.',
                'url' => route('saldo-awal-hutang.index'),
                'action' => 'Lacak Saldo Awal Hutang',
            ];
        }

        return null;
    }

    private function auditSales(string $start, string $end, Collection $checks, Collection $issues): void
    {
        $receivableCoaId = AccountMapping::getCoaId('accounts_receivable');
        if (! $receivableCoaId) {
            $checks->push($this->check('sales_consistency', 'Audit Penjualan', 'warning', null, null, null, 'Mapping akun Piutang Usaha belum tersedia untuk audit penjualan.'));
            $issues->push($this->issue('penjualan', 'warning', 'Mapping Piutang Usaha belum tersedia', 'Audit penjualan butuh mapping accounts_receivable untuk mencocokkan nilai invoice dengan jurnal.', null, route('account-mappings.index'), 'Atur Mapping'));
            return;
        }

        $journalItemTotals = DB::table('jurnal_items')
            ->selectRaw(
                'jurnal_id,
                SUM(CASE WHEN coa_id = ? THEN debit ELSE 0 END) AS receivable_debit,
                SUM(CASE WHEN coa_id = ? THEN kredit ELSE 0 END) AS receivable_credit,
                SUM(debit) AS total_debit,
                SUM(kredit) AS total_kredit',
                [$receivableCoaId, $receivableCoaId]
            )
            ->groupBy('jurnal_id');

        $auditedTotals = DB::table('arh')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'arh.NOJURNAL')
                    ->where('jurnals.is_posted', true);
            })
            ->leftJoinSub($journalItemTotals, 'totals', fn ($join) => $join->on('totals.jurnal_id', '=', 'jurnals.id'))
            ->whereBetween('arh.TGLFAKTUR', [$start, $end])
            ->selectRaw('
                COUNT(*) AS invoice_count,
                COALESCE(SUM(arh.PIUTANG), 0) AS invoice_total,
                COALESCE(SUM(totals.receivable_debit), 0) AS journal_receivable_total
            ')
            ->first();

        $rows = DB::table('arh')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'arh.NOJURNAL')
                    ->where('jurnals.is_posted', true);
            })
            ->leftJoinSub($journalItemTotals, 'totals', fn ($join) => $join->on('totals.jurnal_id', '=', 'jurnals.id'))
            ->whereBetween('arh.TGLFAKTUR', [$start, $end])
            ->whereNotNull('arh.NOJURNAL')
            ->where(function ($query): void {
                $query->whereNull('jurnals.id')
                    ->orWhereRaw('ABS(COALESCE(totals.receivable_debit, 0) - COALESCE(arh.PIUTANG, 0)) > 0.01')
                    ->orWhereRaw('COALESCE(totals.receivable_credit, 0) > 0.01')
                    ->orWhereRaw('ABS(COALESCE(totals.total_debit, 0) - COALESCE(totals.total_kredit, 0)) > 0.01')
                    ->orWhereColumn('jurnals.referensi', '!=', 'arh.NOFAKTUR');
            })
            ->select(
                'arh.id',
                'arh.NOFAKTUR',
                'arh.NOJURNAL',
                'arh.PIUTANG',
                'jurnals.id AS jurnal_id',
                'jurnals.referensi',
                'totals.receivable_debit',
                'totals.receivable_credit',
                'totals.total_debit',
                'totals.total_kredit'
            )
            ->orderByDesc('arh.TGLFAKTUR')
            ->limit(100)
            ->get();

        $invoiceTotal = (float) ($auditedTotals->invoice_total ?? 0);
        $journalReceivableTotal = (float) ($auditedTotals->journal_receivable_total ?? 0);
        $salesDifferenceTotal = 0.0;

        foreach ($rows as $row) {
            $expected = (float) $row->PIUTANG;
            $actual = (float) ($row->receivable_debit ?? 0);
            $difference = max(
                abs($expected - $actual),
                abs((float) ($row->total_debit ?? 0) - (float) ($row->total_kredit ?? 0)),
                abs((float) ($row->receivable_credit ?? 0))
            );
            $salesDifferenceTotal += $difference;

            $messages = [];
            if (! $row->jurnal_id) {
                $messages[] = 'jurnal tidak ditemukan atau belum posted';
            }
            if ($row->jurnal_id && abs($expected - $actual) > 0.01) {
                $messages[] = 'debit Piutang Usaha '.number_format($actual, 2, ',', '.').' tidak sama dengan invoice '.number_format($expected, 2, ',', '.');
            }
            if ((float) ($row->receivable_credit ?? 0) > 0.01) {
                $messages[] = 'ada kredit pada akun Piutang Usaha di jurnal penjualan';
            }
            if ($row->jurnal_id && abs((float) ($row->total_debit ?? 0) - (float) ($row->total_kredit ?? 0)) > 0.01) {
                $messages[] = 'jurnal penjualan tidak balance';
            }
            if ($row->jurnal_id && (string) $row->referensi !== (string) $row->NOFAKTUR) {
                $messages[] = 'referensi jurnal tidak sama dengan nomor invoice';
            }

            $issues->push($this->issue(
                'penjualan',
                'danger',
                'Invoice penjualan '.$row->NOFAKTUR.' perlu dicek',
                ucfirst(implode('; ', $messages)).'.',
                $difference,
                $row->jurnal_id ? route('jurnal.show', $row->jurnal_id) : route('piutang.show', $row->id),
                $row->jurnal_id ? 'Lacak Jurnal' : 'Lacak Invoice'
            ));
        }

        $duplicateInvoices = DB::table('jurnals')
            ->whereBetween('tanggal', [$start, $end])
            ->where('is_posted', true)
            ->whereNotNull('referensi')
            ->where('referensi', '!=', '')
            ->whereIn('referensi', fn ($query) => $query->select('NOFAKTUR')->from('arh'))
            ->selectRaw('referensi, COUNT(*) AS duplicate_count, MIN(id) AS first_id, SUM(total_debit) AS total')
            ->groupBy('referensi')
            ->havingRaw('COUNT(*) > 1')
            ->limit(100)
            ->get();

        foreach ($duplicateInvoices as $row) {
            $salesDifferenceTotal += (float) $row->total;
            $issues->push($this->issue('penjualan', 'warning', 'Jurnal penjualan ganda untuk invoice '.$row->referensi, $row->duplicate_count.' jurnal posted memakai referensi invoice yang sama.', (float) $row->total, route('jurnal.show', $row->first_id), 'Lacak Jurnal'));
        }

        $count = $rows->count() + $duplicateInvoices->count();
        $auditedCount = (int) ($auditedTotals->invoice_count ?? 0);
        $latestInvoiceDate = DB::table('arh')->max('TGLFAKTUR');
        $emptyPeriodMessage = $latestInvoiceDate
            ? 'Tidak ada invoice penjualan pada periode ini. Invoice penjualan terakhir tercatat tanggal '.Carbon::parse($latestInvoiceDate)->format('d/m/Y').'.'
            : 'Belum ada invoice penjualan yang tercatat.';
        $checks->push($this->check(
            'sales_consistency',
            'Audit Penjualan',
            $count === 0 ? 'ok' : 'danger',
            $invoiceTotal,
            $journalReceivableTotal,
            $salesDifferenceTotal,
            $count === 0
                ? ($auditedCount === 0 ? $emptyPeriodMessage : $auditedCount.' invoice penjualan dalam periode ini sudah konsisten dengan jurnal terkait.')
                : $count.' temuan penjualan perlu diperiksa. Nilai menampilkan total invoice yang diaudit, debit Piutang Usaha jurnal, dan akumulasi selisih/risiko.'
        ));
    }

    private function auditDriverReceivables(string $asOf, Collection $checks, Collection $issues): void
    {
        $driverReceivableCoaId = AccountMapping::getCoaId('driver_receivable');
        if (! $driverReceivableCoaId) {
            $checks->push($this->check('driver_receivable_reconciliation', 'Rekonsiliasi Piutang Sopir', 'warning', null, null, null, 'Mapping akun Piutang Sopir/Karyawan belum tersedia.'));
            $issues->push($this->issue('rekonsiliasi', 'warning', 'Mapping Piutang Sopir belum tersedia', 'Pilih akun driver_receivable agar piutang sopir dapat dibandingkan dengan buku besar.', null, route('account-mappings.index'), 'Atur Mapping'));
            return;
        }

        $paid = DB::table('driver_receivable_payments')
            ->where('date', '<=', $asOf)
            ->where('status', 'POSTED')
            ->selectRaw('driver_receivable_id, SUM(amount) AS paid')
            ->groupBy('driver_receivable_id');

        $subledger = (float) (DB::table('driver_receivables')
            ->leftJoinSub($paid, 'paid', fn ($join) => $join->on('paid.driver_receivable_id', '=', 'driver_receivables.id'))
            ->where('driver_receivables.date', '<=', $asOf)
            ->selectRaw('COALESCE(SUM(GREATEST(driver_receivables.amount - COALESCE(paid.paid, 0), 0)), 0) AS balance')
            ->value('balance') ?? 0);

        $ledger = $this->journalQueryService->getSumByCoaId('1900-01-01', $asOf, $driverReceivableCoaId);
        $ledgerBalance = $ledger['debit'] - $ledger['kredit'];
        $difference = $subledger - $ledgerBalance;
        $status = abs($difference) <= 0.01 ? 'ok' : 'danger';
        $message = $status === 'ok' ? 'Piutang sopir sesuai dengan buku besar.' : ($difference > 0
            ? 'Rincian piutang sopir lebih tinggi; cari piutang sopir yang belum dijurnal atau kredit piutang sopir berlebih.'
            : 'Buku besar piutang sopir lebih tinggi; cari debit piutang sopir berlebih atau pembayaran yang belum dijurnal.');

        $checks->push($this->check('driver_receivable_reconciliation', 'Rekonsiliasi Piutang Sopir', $status, $subledger, $ledgerBalance, $difference, $message));
        if ($status !== 'ok') {
            $suspect = $this->firstDriverReceivableReconciliationSuspect($asOf, $driverReceivableCoaId);
            $issues->push($this->issue(
                'rekonsiliasi',
                'danger',
                'Saldo piutang sopir berbeda dengan buku besar',
                $suspect['description'] ?? $message,
                abs($difference),
                $suspect['url'] ?? route('laporan.driver-receivables', ['as_of' => $asOf]),
                $suspect['action'] ?? 'Buka Laporan Piutang Sopir'
            ));
        }
    }

    private function firstDriverReceivableReconciliationSuspect(string $asOf, int $driverReceivableCoaId): ?array
    {
        $manualJournal = DB::table('jurnal_items')
            ->join('jurnals', 'jurnals.id', '=', 'jurnal_items.jurnal_id')
            ->leftJoin('driver_receivables', 'driver_receivables.journal_id', '=', 'jurnals.id')
            ->leftJoin('driver_receivable_payments', function ($join): void {
                $join->on('driver_receivable_payments.journal_id', '=', 'jurnals.id')
                    ->orOn('driver_receivable_payments.void_journal_id', '=', 'jurnals.id');
            })
            ->where('jurnal_items.coa_id', $driverReceivableCoaId)
            ->where('jurnals.tanggal', '<=', $asOf)
            ->where('jurnals.is_posted', true)
            ->whereNull('driver_receivables.id')
            ->whereNull('driver_receivable_payments.id')
            ->select('jurnals.id', 'jurnals.no_jurnal', 'jurnals.tanggal', 'jurnals.referensi', 'jurnal_items.debit', 'jurnal_items.kredit')
            ->orderByDesc('jurnals.tanggal')
            ->first();

        if ($manualJournal) {
            return [
                'description' => 'Jurnal '.$manualJournal->no_jurnal.' memakai akun Piutang Sopir tetapi tidak terhubung ke transaksi piutang/pembayaran sopir. Nilai D '.number_format((float) $manualJournal->debit, 2, ',', '.').' / K '.number_format((float) $manualJournal->kredit, 2, ',', '.').'.',
                'url' => route('jurnal.show', $manualJournal->id),
                'action' => 'Lacak Jurnal Selisih',
            ];
        }

        $missingReceivableJournal = DB::table('driver_receivables')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.id', '=', 'driver_receivables.journal_id')
                    ->where('jurnals.is_posted', true);
            })
            ->where('driver_receivables.date', '<=', $asOf)
            ->where('driver_receivables.origin', 'TRANSACTION')
            ->whereNull('jurnals.id')
            ->select('driver_receivables.id', 'driver_receivables.number', 'driver_receivables.amount')
            ->orderByDesc('driver_receivables.date')
            ->first();

        if ($missingReceivableJournal) {
            return [
                'description' => 'Piutang sopir '.$missingReceivableJournal->number.' belum memiliki jurnal posted yang valid. Nilai '.number_format((float) $missingReceivableJournal->amount, 2, ',', '.').'.',
                'url' => route('driver-receivables.show', $missingReceivableJournal->id),
                'action' => 'Lacak Piutang Sopir',
            ];
        }

        $missingPaymentJournal = DB::table('driver_receivable_payments')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.id', '=', 'driver_receivable_payments.journal_id')
                    ->where('jurnals.is_posted', true);
            })
            ->where('driver_receivable_payments.date', '<=', $asOf)
            ->where('driver_receivable_payments.status', 'POSTED')
            ->whereNull('jurnals.id')
            ->select('driver_receivable_payments.id', 'driver_receivable_payments.number', 'driver_receivable_payments.amount')
            ->orderByDesc('driver_receivable_payments.date')
            ->first();

        if ($missingPaymentJournal) {
            return [
                'description' => 'Pembayaran piutang sopir '.$missingPaymentJournal->number.' belum memiliki jurnal posted yang valid. Nilai '.number_format((float) $missingPaymentJournal->amount, 2, ',', '.').'.',
                'url' => route('driver-payments.show', $missingPaymentJournal->id),
                'action' => 'Lacak Pembayaran Sopir',
            ];
        }

        $openingBalance = DB::table('driver_receivables')
            ->where('date', '<=', $asOf)
            ->where('origin', 'OPENING')
            ->where('balance', '>', 0.01)
            ->selectRaw('COUNT(*) AS count, COALESCE(SUM(balance), 0) AS balance')
            ->first();

        if ((float) ($openingBalance->balance ?? 0) > 0.01) {
            return [
                'description' => 'Ada saldo awal piutang sopir tanpa jurnal sebesar '.number_format((float) $openingBalance->balance, 2, ',', '.').'. Ini membuat buku pembantu lebih tinggi bila saldo awal belum dimasukkan ke buku besar.',
                'url' => route('driver-opening-receivables.index'),
                'action' => 'Lacak Saldo Awal',
            ];
        }

        return null;
    }

    private function auditJournals(string $start, string $end, Collection $checks, Collection $issues): void
    {
        $itemTotals = DB::table('jurnal_items')
            ->selectRaw('jurnal_id, SUM(debit) AS item_debit, SUM(kredit) AS item_kredit, COUNT(*) AS item_count')
            ->groupBy('jurnal_id');

        $journalTotals = DB::table('jurnals')
            ->leftJoinSub($itemTotals, 'totals', fn ($join) => $join->on('totals.jurnal_id', '=', 'jurnals.id'))
            ->whereBetween('jurnals.tanggal', [$start, $end])
            ->selectRaw('
                COUNT(*) AS journal_count,
                COALESCE(SUM(jurnals.total_debit), 0) AS header_debit,
                COALESCE(SUM(jurnals.total_kredit), 0) AS header_kredit,
                COALESCE(SUM(totals.item_debit), 0) AS detail_debit,
                COALESCE(SUM(totals.item_kredit), 0) AS detail_kredit
            ')
            ->first();

        $rows = DB::table('jurnals')
            ->leftJoinSub($itemTotals, 'totals', fn ($join) => $join->on('totals.jurnal_id', '=', 'jurnals.id'))
            ->whereBetween('jurnals.tanggal', [$start, $end])
            ->where(function ($query): void {
                $query->whereRaw('ABS(COALESCE(jurnals.total_debit, 0) - COALESCE(jurnals.total_kredit, 0)) > 0.01')
                    ->orWhereRaw('ABS(COALESCE(totals.item_debit, 0) - COALESCE(totals.item_kredit, 0)) > 0.01')
                    ->orWhereRaw('ABS(COALESCE(jurnals.total_debit, 0) - COALESCE(totals.item_debit, 0)) > 0.01')
                    ->orWhereRaw('ABS(COALESCE(jurnals.total_kredit, 0) - COALESCE(totals.item_kredit, 0)) > 0.01')
                    ->orWhereRaw('COALESCE(totals.item_count, 0) < 2');
            })
            ->select('jurnals.*', 'totals.item_debit', 'totals.item_kredit', 'totals.item_count')
            ->orderByDesc('jurnals.tanggal')->limit(100)->get();

        $journalDifferenceTotal = max(
            abs((float) ($journalTotals->header_debit ?? 0) - (float) ($journalTotals->header_kredit ?? 0)),
            abs((float) ($journalTotals->detail_debit ?? 0) - (float) ($journalTotals->detail_kredit ?? 0)),
            abs((float) ($journalTotals->header_debit ?? 0) - (float) ($journalTotals->detail_debit ?? 0)),
            abs((float) ($journalTotals->header_kredit ?? 0) - (float) ($journalTotals->detail_kredit ?? 0))
        );

        foreach ($rows as $row) {
            $difference = max(
                abs((float) $row->total_debit - (float) $row->total_kredit),
                abs((float) $row->total_debit - (float) $row->item_debit),
                abs((float) $row->total_kredit - (float) $row->item_kredit)
            );
            $issues->push($this->issue('jurnal', 'danger', 'Jurnal '.$row->no_jurnal.' tidak konsisten',
                'Header: D '.number_format((float) $row->total_debit, 2, ',', '.').' / K '.number_format((float) $row->total_kredit, 2, ',', '.').'; detail: D '.number_format((float) $row->item_debit, 2, ',', '.').' / K '.number_format((float) $row->item_kredit, 2, ',', '.').'.',
                $difference, route('jurnal.show', $row->id), 'Lacak Jurnal'));
        }

        $checks->push($this->check(
            'journal_consistency',
            'Konsistensi Jurnal',
            $rows->isEmpty() ? 'ok' : 'danger',
            (float) ($journalTotals->header_debit ?? 0),
            (float) ($journalTotals->detail_debit ?? 0),
            $journalDifferenceTotal,
            $rows->isEmpty()
                ? (int) ($journalTotals->journal_count ?? 0).' jurnal dalam periode ini sudah konsisten antara header dan detail.'
                : $rows->count().' jurnal perlu diperiksa. Nilai menampilkan total debit header, total debit detail, dan akumulasi selisih konsistensi.'
        ));
    }

    private function auditSources(string $start, string $end, Collection $checks, Collection $issues): void
    {
        $invoiceRows = DB::table('arh')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'arh.NOJURNAL')->where('jurnals.is_posted', true);
            })
            ->whereBetween('arh.TGLFAKTUR', [$start, $end])
            ->whereNull('jurnals.id')
            ->select('arh.id', 'arh.NOFAKTUR', 'arh.TGLFAKTUR', 'arh.PIUTANG', 'arh.NOJURNAL')->limit(100)->get();

        foreach ($invoiceRows as $row) {
            $issues->push($this->issue('sumber', 'danger', 'Invoice '.$row->NOFAKTUR.' tanpa jurnal posted', 'No. jurnal sumber: '.($row->NOJURNAL ?: 'belum ada').'.', (float) $row->PIUTANG, route('penjualan-hosting.index', ['search' => $row->NOFAKTUR]), 'Lacak Invoice'));
        }

        $paymentRows = DB::table('barh')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'barh.NOJURNAL')->where('jurnals.is_posted', true);
            })
            ->whereBetween('barh.TGLBAYAR', [$start, $end])
            ->where(fn ($query) => $query->whereNull('barh.STATUS')->orWhere('barh.STATUS', '!=', 'VOID'))
            ->whereNull('jurnals.id')
            ->select('barh.id', 'barh.NOBAYAR', 'barh.TGLBAYAR', 'barh.JUMLAH_BAYAR', 'barh.NOJURNAL')->limit(100)->get();

        foreach ($paymentRows as $row) {
            $issues->push($this->issue('sumber', 'danger', 'Pelunasan '.$row->NOBAYAR.' tanpa jurnal posted', 'No. jurnal sumber: '.($row->NOJURNAL ?: 'belum ada').'.', (float) $row->JUMLAH_BAYAR, route('pelunasan-piutang.show', $row->id), 'Lacak Pelunasan'));
        }

        $orphanDetails = DB::table('bard')->join('barh', 'barh.NOBAYAR', '=', 'bard.NOBAYAR')
            ->leftJoin('arh', 'arh.NOFAKTUR', '=', 'bard.NOFAKTUR')
            ->whereBetween('barh.TGLBAYAR', [$start, $end])->whereNull('arh.id')
            ->select('barh.id AS payment_id', 'bard.NOBAYAR', 'bard.NOFAKTUR', 'bard.NILAI_BAYAR')->limit(100)->get();
        foreach ($orphanDetails as $row) {
            $issues->push($this->issue('sumber', 'warning', 'Detail pelunasan menunjuk invoice yang tidak ada', $row->NOBAYAR.' → '.$row->NOFAKTUR, (float) $row->NILAI_BAYAR, route('pelunasan-piutang.show', $row->payment_id), 'Lacak Pelunasan'));
        }

        $payableRows = DB::table('aph')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'aph.NOJURNAL')->where('jurnals.is_posted', true);
            })
            ->whereBetween('aph.TGLFAKTUR', [$start, $end])
            ->whereNull('jurnals.id')
            ->select('aph.id', 'aph.NOFAKTUR', 'aph.HUTANG', 'aph.NOJURNAL')->limit(100)->get();

        foreach ($payableRows as $row) {
            $issues->push($this->issue('sumber', 'danger', 'Faktur hutang '.$row->NOFAKTUR.' tanpa jurnal posted', 'No. jurnal sumber: '.($row->NOJURNAL ?: 'belum ada').'.', (float) $row->HUTANG, route('hutang.show', $row->id), 'Lacak Hutang'));
        }

        $payablePaymentRows = DB::table('baph')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.no_jurnal', '=', 'baph.NOJURNAL')->where('jurnals.is_posted', true);
            })
            ->whereBetween('baph.TGLBAYAR', [$start, $end])
            ->where(fn ($query) => $query->whereNull('baph.STATUS')->orWhere('baph.STATUS', '!=', 'VOID'))
            ->whereNull('jurnals.id')
            ->select('baph.id', 'baph.NOBAYAR', 'baph.JUMLAH_BAYAR', 'baph.NOJURNAL')->limit(100)->get();

        foreach ($payablePaymentRows as $row) {
            $issues->push($this->issue('sumber', 'danger', 'Pelunasan hutang '.$row->NOBAYAR.' tanpa jurnal posted', 'No. jurnal sumber: '.($row->NOJURNAL ?: 'belum ada').'.', (float) $row->JUMLAH_BAYAR, route('pelunasan-hutang.show', $row->id), 'Lacak Pelunasan Hutang'));
        }

        $driverReceivableRows = DB::table('driver_receivables')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.id', '=', 'driver_receivables.journal_id')->where('jurnals.is_posted', true);
            })
            ->whereBetween('driver_receivables.date', [$start, $end])
            ->whereNotNull('driver_receivables.journal_id')
            ->whereNull('jurnals.id')
            ->select('driver_receivables.id', 'driver_receivables.number', 'driver_receivables.amount')->limit(100)->get();

        foreach ($driverReceivableRows as $row) {
            $issues->push($this->issue('sumber', 'danger', 'Piutang sopir '.$row->number.' tanpa jurnal posted', 'Jurnal piutang sopir tidak ditemukan atau belum posted.', (float) $row->amount, route('driver-receivables.show', $row->id), 'Lacak Piutang Sopir'));
        }

        $driverPaymentRows = DB::table('driver_receivable_payments')
            ->leftJoin('jurnals', function ($join): void {
                $join->on('jurnals.id', '=', 'driver_receivable_payments.journal_id')->where('jurnals.is_posted', true);
            })
            ->whereBetween('driver_receivable_payments.date', [$start, $end])
            ->where('driver_receivable_payments.status', 'POSTED')
            ->whereNull('jurnals.id')
            ->select('driver_receivable_payments.id', 'driver_receivable_payments.number', 'driver_receivable_payments.amount')->limit(100)->get();

        foreach ($driverPaymentRows as $row) {
            $issues->push($this->issue('sumber', 'danger', 'Pembayaran piutang sopir '.$row->number.' tanpa jurnal posted', 'Jurnal pembayaran piutang sopir tidak ditemukan atau belum posted.', (float) $row->amount, route('driver-payments.show', $row->id), 'Lacak Pembayaran Sopir'));
        }

        $count = $invoiceRows->count() + $paymentRows->count() + $orphanDetails->count()
            + $payableRows->count() + $payablePaymentRows->count()
            + $driverReceivableRows->count() + $driverPaymentRows->count();
        $checks->push($this->check('source_links', 'Keterkaitan Sumber Transaksi', $count === 0 ? 'ok' : 'danger', null, null, null, $count === 0 ? 'Semua sumber transaksi dalam periode terhubung.' : $count.' sumber transaksi perlu dilacak.'));
    }

    private function auditDuplicateReferences(string $start, string $end, Collection $checks, Collection $issues): void
    {
        $rows = Jurnal::query()->whereBetween('tanggal', [$start, $end])->where('is_posted', true)
            ->whereNotNull('referensi')->where('referensi', '!=', '')
            ->selectRaw('referensi, COUNT(*) AS duplicate_count, MIN(id) AS first_id, SUM(total_debit) AS total')
            ->groupBy('referensi')->havingRaw('COUNT(*) > 1')->limit(100)->get();

        foreach ($rows as $row) {
            $issues->push($this->issue('duplikat', 'warning', 'Referensi jurnal ganda: '.$row->referensi, $row->duplicate_count.' jurnal posted memakai referensi yang sama.', (float) $row->total, route('jurnal.show', $row->first_id), 'Lacak Jurnal'));
        }
        $checks->push($this->check('duplicate_reference', 'Referensi Jurnal Ganda', $rows->isEmpty() ? 'ok' : 'warning', null, null, null, $rows->isEmpty() ? 'Tidak ditemukan referensi ganda.' : $rows->count().' referensi ganda ditemukan.'));
    }

    private function check(string $key, string $label, string $status, ?float $expected, ?float $actual, ?float $difference, string $message): array
    {
        return compact('key', 'label', 'status', 'expected', 'actual', 'difference', 'message');
    }

    private function issue(string $category, string $severity, string $title, string $description, ?float $amount, ?string $url, string $action): array
    {
        $cause = $this->inferCause($category, $title, $description);

        return compact('category', 'severity', 'title', 'description', 'cause', 'amount', 'url', 'action');
    }

    private function inferCause(string $category, string $title, string $description): string
    {
        $text = strtolower($title.' '.$description);

        $rules = [
            'mapping' => 'Mapping akun belum lengkap atau belum dipilih.',
            'belum posted' => 'Jurnal sumber belum posted atau belum terbentuk.',
            'tanpa jurnal posted' => 'Transaksi sumber belum memiliki jurnal posted.',
            'jurnal tidak ditemukan' => 'Nomor jurnal tersimpan, tetapi jurnal tidak ditemukan atau belum posted.',
            'debit piutang usaha' => 'Nilai invoice tidak sama dengan debit akun Piutang Usaha di jurnal.',
            'kredit pada akun piutang usaha' => 'Akun Piutang Usaha terisi di sisi kredit pada jurnal penjualan.',
            'tidak balance' => 'Total debit dan kredit jurnal tidak seimbang.',
            'referensi jurnal tidak sama' => 'Referensi jurnal tidak cocok dengan nomor transaksi sumber.',
            'referensi yang sama' => 'Ada lebih dari satu jurnal posted memakai referensi yang sama.',
            'invoice yang tidak ada' => 'Detail pembayaran menunjuk invoice yang tidak ada di data piutang.',
            'buku pembantu lebih tinggi' => 'Ada transaksi di buku pembantu yang belum masuk buku besar, atau kredit akun terkait terlalu besar.',
            'buku besar lebih tinggi' => 'Ada nilai di buku besar yang tidak didukung buku pembantu, atau debit akun terkait terlalu besar.',
            'rincian hutang lebih tinggi' => 'Ada faktur hutang yang belum masuk buku besar, atau debit hutang terlalu besar.',
            'rincian piutang sopir lebih tinggi' => 'Ada piutang sopir yang belum masuk buku besar, atau kredit piutang sopir terlalu besar.',
            'tanpa tautan ar' => 'Jurnal memakai akun Piutang Usaha tetapi tidak terhubung ke invoice/pelunasan piutang.',
            'mengurangi buku besar lewat kredit' => 'Kredit Piutang Usaha di jurnal umum tanpa pelunasan di buku pembantu.',
            'menambah buku besar lewat debit' => 'Debit Piutang Usaha di jurnal umum tanpa invoice di buku pembantu.',
            'saldo awal piutang tanpa jurnal' => 'Saldo awal piutang belum punya jurnal, sehingga buku pembantu dan buku besar bisa berbeda.',
            'sisa selisih piutang' => 'Masih ada sisa selisih setelah temuan otomatis; perlu ditelusuri manual.',
        ];

        foreach ($rules as $needle => $cause) {
            if (str_contains($text, $needle)) {
                return $cause;
            }
        }

        return match ($category) {
            'rekonsiliasi' => 'Saldo buku pembantu dan buku besar tidak sama.',
            'penjualan' => 'Data invoice penjualan tidak konsisten dengan jurnalnya.',
            'jurnal' => 'Header jurnal dan rincian jurnal tidak konsisten.',
            'sumber' => 'Transaksi sumber belum terhubung dengan jurnal posted yang valid.',
            'duplikat' => 'Referensi transaksi tercatat lebih dari sekali di jurnal posted.',
            default => 'Perlu ditelusuri dari data sumber dan jurnal terkait.',
        };
    }

    private function impactWarnings(Collection $issues, float $totalSelisih): array
    {
        if ($issues->isEmpty()) {
            return ['Tidak ada dampak selisih yang terdeteksi pada filter ini.'];
        }

        $warnings = [];
        $categories = $issues->pluck('category')->unique();

        if ($categories->contains('rekonsiliasi')) {
            $warnings[] = 'Saldo piutang di buku besar dan buku pembantu dapat berbeda, sehingga laporan piutang, neraca, dan keputusan penagihan berisiko tidak akurat.';
        }

        if ($categories->contains('penjualan')) {
            $warnings[] = 'Invoice penjualan yang tidak cocok dengan jurnal dapat membuat pendapatan, PPN, piutang, dan laba rugi tersaji keliru.';
        }

        if ($categories->contains('jurnal')) {
            $warnings[] = 'Jurnal tidak konsisten dapat membuat debit dan kredit laporan tidak seimbang serta memengaruhi buku besar akun terkait.';
        }

        if ($categories->contains('sumber')) {
            $warnings[] = 'Transaksi sumber tanpa jurnal posted dapat membuat pendapatan, piutang, kas/bank, atau pelunasan belum tercermin di laporan.';
        }

        if ($categories->contains('duplikat')) {
            $warnings[] = 'Referensi jurnal ganda berisiko mencatat transaksi lebih dari satu kali dan menggandakan nilai laporan.';
        }

        if ($totalSelisih > 0.01) {
            array_unshift($warnings, 'Total nilai/sumber selisih yang perlu ditelusuri: Rp '.number_format($totalSelisih, 2, ',', '.').'.');
        }

        return $warnings;
    }
}
