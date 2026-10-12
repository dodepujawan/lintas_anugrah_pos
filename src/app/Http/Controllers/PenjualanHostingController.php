<?php

namespace App\Http\Controllers;

use App\Exports\PenjualanHostingIssueExport;
use App\Exports\PenjualanHostingPostedExport;
use App\Models\AccountMapping;
use App\Models\Arh;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Services\AuditLogService;
use App\Services\JournalService;
use App\Services\PeriodLockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class PenjualanHostingController extends Controller
{
    private const SOURCES = [
        'EKS' => ['label' => 'Pendapatan Ekspedisi', 'mapping' => 'sales_revenue_eks'],
        'REN' => ['label' => 'Pendapatan Sewa Unit Armada', 'mapping' => 'sales_revenue_ren'],
        'CO' => ['label' => 'Pendapatan Sewa Cold Room', 'mapping' => 'sales_revenue_cold_room'],
    ];

    public function __construct(
        private JournalService $journalService,
        private PeriodLockService $periodLockService,
        private AuditLogService $auditLogService,
    ) {}

    public function index(Request $request)
    {
        $query = DB::table('t_penjualan_hosting')->orderByDesc('tgl_kirim')->orderByDesc('invoice');

        if ($request->filled('search')) {
            $search = '%'.trim($request->string('search')).'%';
            $query->where(fn ($q) => $q->where('invoice', 'like', $search)->orWhere('customer', 'like', $search)->orWhere('namacust', 'like', $search));
        }
        if ($request->filled('sumber')) {
            $sourceFilter = strtoupper($request->string('sumber')->toString());
            $query->whereIn('jenis', $sourceFilter === 'EKS' ? ['EKS', 'EXSPEDISI'] : [$sourceFilter]);
        }

        $status = $request->string('status', 'pending')->toString();
        if (in_array($status, ['pending', 'problems', 'ready'], true)) $query->where(fn ($q) => $q->whereNull('status_jurnal')->orWhereNotIn('status_jurnal', ['POSTED', 'VOID']));
        elseif ($status !== 'all') $query->where('status_jurnal', strtoupper($status));

        $dateField = $request->string('date_field', 'invoice')->toString();
        $dateColumn = $dateField === 'posted' ? 'posted_at' : 'tglinvoice';
        if (! Schema::hasColumn('t_penjualan_hosting', $dateColumn)) {
            $dateColumn = 'tgl_kirim';
        }
        if ($request->filled('tanggal_awal')) {
            $query->whereDate($dateColumn, '>=', $request->date('tanggal_awal')->toDateString());
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate($dateColumn, '<=', $request->date('tanggal_akhir')->toDateString());
        }

        $sales = $query->get();
        $voidInvoices = $sales->where('status_jurnal', 'VOID')->pluck('invoice')->filter();
        if ($voidInvoices->isNotEmpty()) {
            $voidJournalIds = Jurnal::query()->whereIn('referensi', $voidInvoices->map(fn ($invoice) => 'VOID-'.$invoice))
                ->pluck('id', 'referensi');
            $sales->each(function ($sale) use ($voidJournalIds): void {
                if (($sale->status_jurnal ?? '') === 'VOID' && empty($sale->void_journal_id)) {
                    $sale->void_journal_id = $voidJournalIds->get('VOID-'.$sale->invoice);
                }
            });
        }
        [$mappingIds, $validAccountIds] = $this->mappingReadiness();
        $sales->each(function ($sale) use ($mappingIds, $validAccountIds): void {
            $sale->validation_issues = $this->validationIssues($sale, $mappingIds, $validAccountIds);
            $sale->invoice_date_display = $this->invoiceDateDisplay($sale);
        });
        if ($status === 'problems') {
            $sales = $sales->filter(fn ($sale) => count($sale->validation_issues) > 0)->values();
        } elseif ($status === 'ready') {
            $sales = $sales->filter(fn ($sale) => count($sale->validation_issues) === 0)->values();
        }
        $problemCount = $sales->filter(fn ($sale) => count($sale->validation_issues) > 0)->count();
        $totals = [
            'count' => $sales->count(),
            'dpp' => $sales->sum(fn ($sale) => (float) $sale->dpp),
            'ppn' => $sales->sum(fn ($sale) => (float) $sale->nilai_ppn),
            'total' => $sales->sum(fn ($sale) => (float) $sale->total),
        ];
        $mappingKeys = array_merge(['accounts_receivable', 'output_tax'], array_column(self::SOURCES, 'mapping'));
        $accounts = Coa::query()->whereIn('id', $mappingIds)->get()->keyBy('id');
        $mappingStatus = collect($mappingKeys)->mapWithKeys(fn ($key) => [$key => $accounts->get($mappingIds[$key] ?? null)]);

        return view('penjualan-hosting.index', ['sales' => $sales, 'sources' => self::SOURCES, 'mappingStatus' => $mappingStatus, 'totals' => $totals, 'problemCount' => $problemCount, 'dateField' => $dateField]);
    }

    public function exportPosted(Request $request)
    {
        $query = DB::table('t_penjualan_hosting')
            ->where('status_jurnal', 'POSTED')
            ->orderByDesc('tgl_kirim')
            ->orderByDesc('invoice');

        if ($request->filled('search')) {
            $search = '%'.trim($request->string('search')).'%';
            $query->where(fn ($q) => $q->where('invoice', 'like', $search)->orWhere('customer', 'like', $search)->orWhere('namacust', 'like', $search));
        }
        if ($request->filled('sumber')) {
            $source = strtoupper($request->string('sumber')->toString());
            $query->whereIn('jenis', $source === 'EKS' ? ['EKS', 'EXSPEDISI'] : [$source]);
        }

        $dateField = $request->string('date_field', 'invoice')->toString();
        $dateColumn = $dateField === 'posted' ? 'posted_at' : 'tglinvoice';
        if (! Schema::hasColumn('t_penjualan_hosting', $dateColumn)) {
            $dateColumn = 'tgl_kirim';
        }
        if ($request->filled('tanggal_awal')) {
            $query->whereDate($dateColumn, '>=', $request->date('tanggal_awal')->toDateString());
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate($dateColumn, '<=', $request->date('tanggal_akhir')->toDateString());
        }

        return Excel::download(
            new PenjualanHostingPostedExport($query->get(), [
                'tanggal_awal' => $request->input('tanggal_awal'),
                'tanggal_akhir' => $request->input('tanggal_akhir'),
                'date_field' => $dateField,
            ]),
            'penjualan-hosting-posted-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    public function exportProblems(Request $request)
    {
        $query = DB::table('t_penjualan_hosting')
            ->where(fn ($q) => $q->whereNull('status_jurnal')->orWhereNotIn('status_jurnal', ['POSTED', 'VOID']))
            ->orderByDesc('tgl_kirim')->orderByDesc('invoice');
        if ($request->filled('search')) {
            $search = '%'.trim($request->string('search')).'%';
            $query->where(fn ($q) => $q->where('invoice', 'like', $search)->orWhere('customer', 'like', $search)->orWhere('namacust', 'like', $search));
        }
        if ($request->filled('sumber')) {
            $source = strtoupper($request->string('sumber')->toString());
            $query->whereIn('jenis', $source === 'EKS' ? ['EKS', 'EXSPEDISI'] : [$source]);
        }
        $dateField = $request->string('date_field', 'invoice')->toString();
        $dateColumn = $dateField === 'posted' ? 'posted_at' : 'tglinvoice';
        if (! Schema::hasColumn('t_penjualan_hosting', $dateColumn)) {
            $dateColumn = 'tgl_kirim';
        }
        if ($request->filled('tanggal_awal')) {
            $query->whereDate($dateColumn, '>=', $request->date('tanggal_awal')->toDateString());
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate($dateColumn, '<=', $request->date('tanggal_akhir')->toDateString());
        }
        [$mappingIds, $validAccountIds] = $this->mappingReadiness();
        $rows = $query->get()->map(function ($sale) use ($mappingIds, $validAccountIds) {
            $sale->validation_issues = $this->validationIssues($sale, $mappingIds, $validAccountIds);
            return $sale;
        })->filter(fn ($sale) => count($sale->validation_issues) > 0)->values();

        return Excel::download(new PenjualanHostingIssueExport($rows), 'penjualan-hosting-bermasalah-'.now()->format('Ymd-His').'.xlsx');
    }

    public function post(Request $request)
    {
        $validated = $request->validate([
            'invoices' => ['required', 'array', 'max:100'],
            'invoices.*' => ['required', 'string', 'max:30', 'distinct'],
            'due_days' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $rows = DB::table('t_penjualan_hosting')->whereIn('invoice', $validated['invoices'])->get();
        $required = ['accounts_receivable'];
        foreach ($rows as $row) {
            $source = $this->canonicalSource($row->jenis ?? $row->sumber);
            if (isset(self::SOURCES[$source])) $required[] = self::SOURCES[$source]['mapping'];
            if ((float) $row->nilai_ppn > 0) $required[] = 'output_tax';
        }
        $required = array_unique($required);
        $mappingIds = AccountMapping::query()->whereIn('mapping_key', $required)->pluck('account_id', 'mapping_key');
        $validAccountIds = Coa::active()->posting()->whereIn('id', $mappingIds)->pluck('id')->all();
        $missing = collect($required)->filter(fn ($key) => ! isset($mappingIds[$key]) || ! in_array((int) $mappingIds[$key], $validAccountIds, true));
        if ($missing->isNotEmpty()) {
            $labels = AccountMapping::mappingKeys();
            return back()->with('error', 'Proses dibatalkan. Isi mapping akun: '.$missing->map(fn ($key) => $labels[$key] ?? $key)->join(', ').'.');
        }

        $success = 0; $skipped = 0; $errors = [];
        foreach ($validated['invoices'] as $invoice) {
            try {
                DB::transaction(function () use ($invoice, $validated, $mappingIds, &$success): void {
                    $sale = DB::table('t_penjualan_hosting')->where('invoice', $invoice)->lockForUpdate()->first();
                    if (! $sale) throw new \RuntimeException('Invoice tidak ditemukan.');
                    if ($sale->status_jurnal === 'POSTED' || $sale->no_jurnal || Jurnal::where('referensi', $invoice)->exists()) {
                        throw new \DomainException('Invoice sudah pernah diproses.');
                    }

                    // Pulihkan piutang yatim dari proses lama: AR sudah terbentuk,
                    // tetapi jurnal transaksi tidak pernah tersimpan. Hanya aman
                    // dihapus bila berasal dari sinkronisasi dan belum dibayar.
                    $existingAr = Arh::where('NOFAKTUR', $invoice)->lockForUpdate()->first();
                    if ($existingAr) {
                        $hasPayment = DB::table('bard')->where('NOFAKTUR', $invoice)->exists();
                        $isUnpaidSync = str_starts_with((string) $existingAr->SOURCE, 'SYNC_')
                            && (float) $existingAr->BAYAR === 0.0
                            && (float) $existingAr->RETUR === 0.0
                            && (float) $existingAr->DISCOUNT === 0.0
                            && ! $hasPayment;
                        if (! $isUnpaidSync) {
                            throw new \RuntimeException('Piutang invoice sudah ada dan memiliki transaksi terkait; perlu diperiksa manual.');
                        }
                        $existingAr->delete();
                    }

                    $validAccountIds = Coa::active()->posting()->whereIn('id', $mappingIds)->pluck('id')->all();
                    $issues = $this->validationIssues($sale, $mappingIds, $validAccountIds);
                    if ($issues) throw new \RuntimeException('Belum siap diproses: '.implode('; ', $issues));

                    $source = $this->canonicalSource($sale->jenis ?? $sale->sumber);
                    if (! isset(self::SOURCES[$source])) throw new \RuntimeException("Sumber {$source} tidak dikenali (harus EKS, REN, atau CO).");
                    if (! $sale->customer) throw new \RuntimeException('Kode customer kosong.');

                    // Pengaman untuk data sinkronisasi lama: buat master customer
                    // saat posting bila API sebelumnya belum sempat membuatnya.
                    if (Schema::hasTable('mcustomer') && ! DB::table('mcustomer')->where('CUSTOMER', $sale->customer)->lockForUpdate()->exists()) {
                        DB::table('mcustomer')->insert([
                            'CUSTOMER' => $sale->customer,
                            'NAMACUST' => $sale->namacust ?: 'Unknown',
                            'ALAMAT1' => $sale->alamat1 ?: '',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $date = Carbon::parse($sale->tglinvoice ?: $sale->tgl_kirim)->toDateString();
                    if ($this->periodLockService->isLocked($date)) throw new \RuntimeException($this->periodLockService->lockedMessage($date));
                    $dpp = round((float) $sale->dpp, 2); $ppn = round((float) $sale->nilai_ppn, 2); $total = round((float) $sale->total, 2);
                    if ($dpp < 0 || $ppn < 0 || $total <= 0) throw new \RuntimeException('Nilai DPP, PPN, atau total tidak valid.');
                    if (abs(($dpp + $ppn) - $total) > 0.02) throw new \RuntimeException('Total tidak sama dengan DPP + PPN.');

                    $noJurnal = $this->journalService->generateJournalNumber('PJ', $date);
                    $customerLabel = $this->customerLabel($sale);
                    $description = self::SOURCES[$source]['label'].' '.$customerLabel.' - '.$invoice;
                    $items = [[
                        'coa_id' => (int) $mappingIds['accounts_receivable'], 'deskripsi' => 'Piutang '.$customerLabel.' (Invoice: '.$invoice.')',
                        'debit' => $total, 'kredit' => 0,
                    ]];
                    if ($dpp > 0) $items[] = ['coa_id' => (int) $mappingIds[self::SOURCES[$source]['mapping']], 'deskripsi' => $description, 'debit' => 0, 'kredit' => $dpp];
                    if ($ppn > 0) $items[] = ['coa_id' => (int) $mappingIds['output_tax'], 'deskripsi' => 'PPN Keluaran '.$customerLabel.' (Invoice: '.$invoice.')', 'debit' => 0, 'kredit' => $ppn];

                    $journal = $this->journalService->createJournal($noJurnal, $date, $description, $items, $invoice, auth()->id(), 'Sinkronisasi Penjualan', 'Penjualan');
                    $ar = Arh::create([
                        'NOFAKTUR' => $invoice, 'TGLFAKTUR' => $date,
                        'TGLJT' => Carbon::parse($date)->addDays((int) $validated['due_days'])->toDateString(),
                        'CUSTOMER' => $sale->customer, 'PIUTANG' => $total, 'BAYAR' => 0, 'RETUR' => 0,
                        'DISCOUNT' => 0, 'SALDO' => $total, 'KETERANGAN' => $description,
                        'STATUS' => 'OPEN', 'SOURCE' => 'SYNC_'.$source, 'NOJURNAL' => $noJurnal, 'USER' => auth()->user()?->name,
                    ]);
                    DB::table('t_penjualan_hosting')->where('invoice', $invoice)->update([
                        'status_jurnal' => 'POSTED', 'no_jurnal' => $noJurnal, 'jurnal_id' => $journal->id,
                        'namacust' => $customerLabel,
                        'posted_at' => now(), 'posted_by' => auth()->id(), 'posting_error' => null,
                    ]);
                    $this->auditLogService->record('post', 'penjualan-hosting', $journal, "Posting invoice {$invoice} ke jurnal dan AR.", ['invoice' => $invoice, 'source' => $source, 'ar_id' => $ar->id]);
                    $success++;
                });
            } catch (\DomainException $e) {
                $skipped++;
            } catch (Throwable $e) {
                $errors[] = $invoice.': '.$e->getMessage();
                DB::table('t_penjualan_hosting')->where('invoice', $invoice)->where(fn ($q) => $q->whereNull('status_jurnal')->orWhereNotIn('status_jurnal', ['POSTED', 'VOID']))->update(['status_jurnal' => 'GAGAL', 'posting_error' => Str::limit($e->getMessage(), 1000)]);
                report($e);
            }
        }

        $message = "{$success} invoice berhasil diposting".($skipped ? ", {$skipped} dilewati karena sudah diproses" : '').'.';
        return redirect()->route('penjualan-hosting.index')->with($errors ? 'error' : 'success', $errors ? $message.' Gagal: '.implode(' | ', array_slice($errors, 0, 5)) : $message);
    }

    public function repost(Request $request, string $invoice)
    {
        $validated = $request->validate(['due_days' => ['required', 'integer', 'min:0', 'max:365']]);

        try {
            DB::transaction(function () use ($invoice, $validated): void {
                $sale = DB::table('t_penjualan_hosting')->where('invoice', $invoice)->lockForUpdate()->first();
                if (! $sale) throw new \RuntimeException('Invoice hosting tidak ditemukan.');
                if ($sale->status_jurnal === 'VOID') throw new \RuntimeException('Invoice sudah di-void dan tidak dapat direjurnal.');

                $journal = Jurnal::query()
                    ->where(fn ($query) => $query->where('id', $sale->jurnal_id)->orWhere('referensi', $invoice))
                    ->lockForUpdate()->first();
                $ar = Arh::where('NOFAKTUR', $invoice)->lockForUpdate()->first();
                if (! $journal || ! $ar) throw new \RuntimeException('Jurnal atau piutang lama tidak lengkap; koreksi otomatis dibatalkan.');
                if ($journal->status === Jurnal::STATUS_VOID) throw new \RuntimeException('Jurnal lama sudah Void dan tidak dapat direjurnal.');

                $date = Carbon::parse($sale->tglinvoice ?: $sale->tgl_kirim)->toDateString();
                if ($this->periodLockService->isLocked($journal->tanggal) || $this->periodLockService->isLocked($date)) {
                    throw new \RuntimeException('Periode jurnal lama atau tanggal koreksi sudah terkunci.');
                }

                [$mappingIds, $validAccountIds] = $this->mappingReadiness();
                $issues = $this->validationIssues($sale, $mappingIds, $validAccountIds);
                if ($issues) throw new \RuntimeException('Data koreksi belum valid: '.implode('; ', $issues));

                $source = $this->canonicalSource($sale->jenis ?? $sale->sumber);
                $dpp = round((float) $sale->dpp, 2);
                $ppn = round((float) $sale->nilai_ppn, 2);
                $total = round((float) $sale->total, 2);
                $settled = $this->settledReceivableAmount($invoice, $ar);
                if ($total + 0.01 < $settled) {
                    throw new \RuntimeException(
                        'Total koreksi '.number_format($total, 2, ',', '.').' lebih kecil dari nilai yang sudah dibayar/dipotong '.number_format($settled, 2, ',', '.').'. Batalkan atau edit transaksi pelunasan terkait terlebih dahulu.'
                    );
                }
                if (Schema::hasTable('mcustomer') && ! DB::table('mcustomer')->where('CUSTOMER', $sale->customer)->lockForUpdate()->exists()) {
                    DB::table('mcustomer')->insert([
                        'CUSTOMER' => $sale->customer, 'NAMACUST' => $sale->namacust ?: 'Unknown',
                        'ALAMAT1' => $sale->alamat1 ?: '', 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }

                $customerLabel = $this->customerLabel($sale);
                $description = self::SOURCES[$source]['label'].' '.$customerLabel.' - '.$invoice;
                $items = [[
                    'coa_id' => (int) $mappingIds['accounts_receivable'],
                    'deskripsi' => 'Piutang '.$customerLabel.' (Invoice: '.$invoice.')', 'debit' => $total, 'kredit' => 0,
                ]];
                if ($dpp > 0) $items[] = ['coa_id' => (int) $mappingIds[self::SOURCES[$source]['mapping']], 'deskripsi' => $description, 'debit' => 0, 'kredit' => $dpp];
                if ($ppn > 0) $items[] = ['coa_id' => (int) $mappingIds['output_tax'], 'deskripsi' => 'PPN Keluaran '.$customerLabel.' (Invoice: '.$invoice.')', 'debit' => 0, 'kredit' => $ppn];

                $noJurnal = $journal->tanggal?->toDateString() === $date
                    ? $journal->no_jurnal
                    : $this->journalService->generateJournalNumber('PJ', $date);
                $journal->update([
                    'no_jurnal' => $noJurnal, 'tanggal' => $date, 'keterangan' => $description,
                    'total_debit' => $total, 'total_kredit' => $total, 'referensi' => $invoice,
                    'status' => Jurnal::STATUS_POSTED, 'is_posted' => true, 'posted_at' => now(),
                    'module_type' => 'Sinkronisasi Penjualan', 'tipe' => 'Penjualan',
                ]);
                $journal->items()->delete();
                foreach ($items as $item) $journal->items()->create($item);

                $ar->update([
                    'TGLFAKTUR' => $date,
                    'TGLJT' => Carbon::parse($date)->addDays((int) $validated['due_days'])->toDateString(),
                    'CUSTOMER' => $sale->customer, 'PIUTANG' => $total,
                    'SALDO' => max(0, $total - $settled),
                    'KETERANGAN' => $description, 'STATUS' => $total - $settled <= 0.01 ? 'LUNAS' : 'OPEN', 'SOURCE' => 'SYNC_'.$source,
                    'NOJURNAL' => $noJurnal, 'USER_UPDATE' => auth()->user()?->name,
                ]);
                DB::table('t_penjualan_hosting')->where('invoice', $invoice)->update([
                    'status_jurnal' => 'POSTED', 'no_jurnal' => $noJurnal, 'jurnal_id' => $journal->id,
                    'namacust' => $customerLabel,
                    'posted_at' => now(), 'posted_by' => auth()->id(), 'posting_error' => null,
                ]);
                $this->auditLogService->record('repost', 'penjualan-hosting', $journal, "Rejurnal invoice {$invoice} setelah koreksi sumber.", [
                    'invoice' => $invoice, 'source' => $source, 'ar_id' => $ar->id,
                ]);
            });

            return back()->with('success', "Invoice {$invoice} berhasil direjurnal sesuai data koreksi terbaru.");
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', "Rejurnal {$invoice} gagal: ".$e->getMessage());
        }
    }

    public function void(Request $request, string $invoice)
    {
        abort_unless(auth()->user()?->hasMenuPermission('penjualan-hosting', 'void'), 403, 'Anda tidak memiliki hak Void Sinkronisasi Penjualan.');
        $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);
        $reason = trim((string) $request->input('reason', 'Pembatalan dari sumber penjualan'));
        if ($reason === '') $reason = 'Pembatalan dari sumber penjualan';

        try {
            DB::transaction(function () use ($invoice, $reason): void {
                $sale = DB::table('t_penjualan_hosting')->where('invoice', $invoice)->lockForUpdate()->first();
                if (! $sale || $sale->status_jurnal !== 'POSTED' || ! $sale->no_jurnal) {
                    throw new \RuntimeException('Invoice belum berstatus Posted atau jurnal asal tidak ditemukan.');
                }
                if (($sale->status_jurnal ?? '') === 'VOID') throw new \RuntimeException('Invoice sudah di-void.');
                $original = Jurnal::where('no_jurnal', $sale->no_jurnal)->with('items')->lockForUpdate()->first();
                $ar = Arh::where('NOFAKTUR', $invoice)->lockForUpdate()->first();
                if (! $original || $original->items->isEmpty() || ! $ar) throw new \RuntimeException('Jurnal atau piutang invoice tidak lengkap.');
                if (! $original->is_posted || $original->status === Jurnal::STATUS_VOID || $ar->NOJURNAL !== $original->no_jurnal || ! str_starts_with((string) $ar->SOURCE, 'SYNC_')) throw new \RuntimeException('Jurnal/piutang asal tidak sesuai.');
                $this->ensureUnpaidForVoid($invoice, $ar);
                if ($this->periodLockService->isLocked(now()) || $this->periodLockService->isLocked($original->tanggal)) throw new \RuntimeException('Periode transaksi terkunci.');
                $items = $original->items->map(fn ($item) => ['coa_id' => $item->coa_id, 'deskripsi' => 'Pembalik: '.$item->deskripsi, 'debit' => (float) $item->kredit, 'kredit' => (float) $item->debit])->all();
                $voidReferensi = 'VOID-'.$invoice;
                $existingVoid = Jurnal::where('referensi', $voidReferensi)->where('is_posted', true)->first();
                if ($existingVoid) {
                    $reversal = $existingVoid;
                } else {
                    $reversal = $this->journalService->createJournal($this->journalService->generateJournalNumber('RV', now()->toDateString()), now()->toDateString(), 'Void penjualan '.$invoice.': '.$reason, $items, $voidReferensi, auth()->id(), 'Void Penjualan', 'Jurnal Umum');
                }
                $original->update(['keterangan' => $original->keterangan.' [VOID dengan '.$reversal->no_jurnal.': '.$reason.']']);
                $ar->update(['STATUS' => 'VOID', 'SALDO' => 0, 'USER_UPDATE' => auth()->user()?->name]);
                DB::table('t_penjualan_hosting')->where('invoice', $invoice)->update(['status_jurnal' => 'VOID', 'void_journal_id' => $reversal->id, 'voided_at' => now(), 'voided_by' => auth()->id()]);
                $this->auditLogService->record('void', 'penjualan-hosting', $original, "Void invoice {$invoice} dengan jurnal pembalik.", ['reason' => $reason, 'reversal_journal' => $reversal->no_jurnal]);
            });
            return redirect()->route('penjualan-hosting.index', ['status' => 'VOID', 'search' => $invoice])
                ->with('success', 'Invoice berhasil di-void dengan jurnal pembalik.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function revoid(Request $request, string $invoice)
    {
        abort_unless(auth()->user()?->hasMenuPermission('penjualan-hosting', 'void'), 403, 'Anda tidak memiliki hak Revoid Sinkronisasi Penjualan.');
        try {
            DB::transaction(function () use ($invoice): void {
                $sale = DB::table('t_penjualan_hosting')->where('invoice', $invoice)->lockForUpdate()->first();
                if (! $sale || $sale->status_jurnal !== 'VOID' || empty($sale->void_journal_id)) {
                    throw new \RuntimeException('Invoice harus berstatus VOID dengan jurnal pembalik yang lengkap.');
                }
                $ar = Arh::where('NOFAKTUR', $invoice)->lockForUpdate()->first();
                $original = Jurnal::where('no_jurnal', $sale->no_jurnal)->lockForUpdate()->first();
                $voidJournal = Jurnal::with('items')->whereKey($sale->void_journal_id)->lockForUpdate()->first();
                if (! $ar || ! $original || ! $voidJournal || $voidJournal->items->isEmpty()
                    || ! $voidJournal->is_posted || $voidJournal->referensi !== 'VOID-'.$invoice
                    || $ar->STATUS !== 'VOID' || $ar->NOJURNAL !== $original->no_jurnal
                    || ! str_starts_with((string) $ar->SOURCE, 'SYNC_')) {
                    throw new \RuntimeException('Jurnal atau piutang asal tidak sesuai untuk Revoid.');
                }
                $this->ensureUnpaidForVoid($invoice, $ar);
                foreach ([$original->tanggal, $voidJournal->tanggal, now()] as $date) {
                    if ($this->periodLockService->isLocked($date)) throw new \RuntimeException('Periode transaksi terkunci.');
                }
                $items = $voidJournal->items->map(fn ($item) => [
                    'coa_id' => $item->coa_id, 'deskripsi' => 'Revoid: '.$item->deskripsi,
                    'debit' => (float) $item->kredit, 'kredit' => (float) $item->debit,
                ])->all();
                $revoidReferensi = 'REVOID-'.$invoice;
                $existingRevoid = Jurnal::where('referensi', $revoidReferensi)->where('is_posted', true)->first();
                if ($existingRevoid) {
                    $journal = $existingRevoid;
                } else {
                    $journal = $this->journalService->createJournal(
                        $this->journalService->generateJournalNumber('RV', now()->toDateString()),
                        now()->toDateString(), 'Revoid penjualan '.$invoice, $items,
                        $revoidReferensi, auth()->id(), 'Revoid Penjualan', 'Jurnal Umum'
                    );
                }
                $ar->update(['STATUS' => 'OPEN', 'SALDO' => $ar->PIUTANG, 'TGLLUNAS' => null, 'USER_UPDATE' => auth()->user()?->name]);
                DB::table('t_penjualan_hosting')->where('invoice', $invoice)->update([
                    'status_jurnal' => 'POSTED', 'void_journal_id' => null, 'voided_at' => null, 'voided_by' => null,
                ]);
                $this->auditLogService->record('revoid', 'penjualan-hosting', $journal, 'Mengaktifkan kembali invoice '.$invoice, [
                    'invoice' => $invoice, 'void_journal_id' => $voidJournal->id, 'revoid_journal_id' => $journal->id,
                ]);
            });
            return redirect()->route('penjualan-hosting.index', ['status' => 'POSTED', 'search' => $invoice])
                ->with('success', 'Revoid berhasil. Piutang dipulihkan dan invoice dapat direjurnal kembali.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }

    private function ensureUnpaidForVoid(string $invoice, Arh $ar): void
    {
        $hasPayment = DB::table('bard')->join('barh', 'barh.NOBAYAR', '=', 'bard.NOBAYAR')
            ->where('bard.NOFAKTUR', $invoice)
            ->where(fn ($query) => $query->whereNull('barh.STATUS')->orWhere('barh.STATUS', '!=', 'VOID'))->exists();
        if ($hasPayment || (float) $ar->BAYAR != 0 || (float) $ar->RETUR != 0 || (float) $ar->DISCOUNT != 0) {
            throw new \RuntimeException('Void/Revoid hanya dapat dilakukan jika invoice belum dibayar atau dipotong. Batalkan pelunasan terkait terlebih dahulu.');
        }
    }

    private function canonicalSource(?string $source): string
    {
        $source = strtoupper(trim((string) $source));
        return $source === 'EXSPEDISI' ? 'EKS' : $source;
    }

    private function mappingReadiness(): array
    {
        $keys = array_merge(['accounts_receivable', 'output_tax'], array_column(self::SOURCES, 'mapping'));
        $mappingIds = AccountMapping::query()->whereIn('mapping_key', $keys)->pluck('account_id', 'mapping_key');
        $validAccountIds = Coa::active()->posting()->whereIn('id', $mappingIds)->pluck('id')->all();

        return [$mappingIds, $validAccountIds];
    }

    private function validationIssues(object $sale, $mappingIds, array $validAccountIds): array
    {
        $issues = [];
        $source = $this->canonicalSource($sale->jenis ?? $sale->sumber ?? null);
        $date = $sale->tglinvoice ?? $sale->tgl_kirim ?? null;
        $dpp = round((float) ($sale->dpp ?? 0), 2);
        $ppn = round((float) ($sale->nilai_ppn ?? 0), 2);
        $total = round((float) ($sale->total ?? 0), 2);

        if (! $date) $issues[] = 'Tanggal invoice kosong';
        else try { Carbon::parse($date); } catch (Throwable) { $issues[] = 'Tanggal invoice tidak valid'; }
        if (empty($sale->customer)) $issues[] = 'Kode customer kosong';
        if (! isset(self::SOURCES[$source])) $issues[] = 'Jenis/sumber harus EKS, REN, atau CO';
        if ($dpp < 0 || $ppn < 0 || $total <= 0) $issues[] = 'Nilai DPP, PPN, atau total tidak valid';
        elseif (abs(($dpp + $ppn) - $total) > 0.02) $issues[] = 'Total tidak sama dengan DPP + PPN';

        $requiredMappings = ['accounts_receivable'];
        if (isset(self::SOURCES[$source])) $requiredMappings[] = self::SOURCES[$source]['mapping'];
        if ($ppn > 0) $requiredMappings[] = 'output_tax';
        foreach ($requiredMappings as $key) {
            if (! isset($mappingIds[$key]) || ! in_array((int) $mappingIds[$key], $validAccountIds, true)) {
                $issues[] = 'Mapping akun '.(AccountMapping::mappingKeys()[$key] ?? $key).' belum valid';
            }
        }

        return array_values(array_unique($issues));
    }

    private function invoiceDateDisplay(object $sale): string
    {
        $date = $sale->tglinvoice ?? $sale->tgl_kirim ?? null;
        if (! $date) return '-';
        try { return Carbon::parse($date)->format('d/m/Y'); }
        catch (Throwable) { return (string) $date; }
    }

    private function customerLabel(object $sale): string
    {
        $name = trim((string) ($sale->namacust ?? ''));
        if ($name !== '') {
            return $name;
        }

        $code = trim((string) ($sale->customer ?? ''));
        if ($code !== '' && Schema::hasTable('mcustomer')) {
            $masterName = DB::table('mcustomer')->where('CUSTOMER', $code)->value('NAMACUST');
            $masterName = trim((string) $masterName);
            if ($masterName !== '') {
                return $masterName;
            }
        }

        return $code !== '' ? $code : 'Unknown';
    }

    private function settledReceivableAmount(string $invoice, Arh $ar): float
    {
        $details = DB::table('bard')
            ->join('barh', 'barh.NOBAYAR', '=', 'bard.NOBAYAR')
            ->where('bard.NOFAKTUR', $invoice)
            ->where(fn ($query) => $query->whereNull('barh.STATUS')->orWhere('barh.STATUS', '!=', 'VOID'))
            ->selectRaw(
                'COUNT(*) as detail_count, COALESCE(SUM(bard.NILAI_BAYAR), 0) as paid, COALESCE(SUM(bard.PPH23), 0) as pph23, COALESCE(SUM(bard.DISCOUNT), 0) as discount, COALESCE(SUM(bard.BANK_ADMIN), 0) as bank_admin, COALESCE(SUM(bard.RETUR), 0) as retur'
            )
            ->first();

        $detailTotal = (float) ($details->paid ?? 0)
            + (float) ($details->pph23 ?? 0)
            + (float) ($details->discount ?? 0)
            + (float) ($details->bank_admin ?? 0)
            + (float) ($details->retur ?? 0);

        $headerTotal = (float) $ar->BAYAR + (float) $ar->RETUR + (float) $ar->DISCOUNT;

        return round(((int) ($details->detail_count ?? 0) > 0) ? $detailTotal : $headerTotal, 2);
    }
}
