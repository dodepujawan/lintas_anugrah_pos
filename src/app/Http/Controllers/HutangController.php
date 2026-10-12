<?php

namespace App\Http\Controllers;

use App\Exports\OpeningPayableExport;
use App\Models\AccountMapping;
use App\Models\Aph;
use App\Models\Jurnal;
use App\Models\Coa;
use App\Models\Msupplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use App\Services\JournalService;
use App\Services\PeriodLockService;
use App\Services\AuditLogService;
use App\Services\FastTablePdfService;
use Maatwebsite\Excel\Facades\Excel;

class HutangController extends Controller
{
    public function __construct(private JournalService $journalService, private PeriodLockService $periodLockService, private FastTablePdfService $fastTablePdf)
    {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $supplier = trim((string) $request->input('supplier'));
        $hutangs = Aph::with('supplierMaster')
            ->where('SOURCE', 'HUTANG_BERJALAN_MANUAL')
            ->when($supplier !== '', fn ($query) => $query->where('SUPPLIER', $supplier))
            ->when($search !== '', function ($query) use ($search): void {
                $keyword = "%{$search}%";
                $query->where(function ($filter) use ($keyword): void {
                    $filter->where('NOFAKTUR', 'like', $keyword)
                        ->orWhere('SUPPLIER', 'like', $keyword)
                        ->orWhere('KETERANGAN', 'like', $keyword)
                        ->orWhereHas('supplierMaster', fn ($supplier) => $supplier->where('NAMA', 'like', $keyword));
                });
            });
        $totals = (clone $hutangs)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(HUTANG), 0) as hutang, COALESCE(SUM(BAYAR), 0) as bayar, COALESCE(SUM(SALDO), 0) as saldo')
            ->first();
        $hutangs = $hutangs
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('hutang.index', compact('hutangs', 'totals'));
    }

    public function create(): View
    {
        return view('hutang.create', [
            'payableAccountId' => AccountMapping::getCoaId('accounts_payable'),
            'inputTaxAccountId' => AccountMapping::getCoaId('input_tax'),
            'accounts' => Coa::query()->where('is_active', true)
                ->where('level', 'Akun Posting')->orderBy('kode_akun')->get(),
            'expenseAccounts' => Coa::query()->where('is_active', true)
                ->where('level', 'Akun Posting')->where('tipe', 'Beban')->orderBy('kode_akun')->get(),
        ]);
    }

    public function createSaldoAwal(): View
    {
        return view('hutang.saldo-awal');
    }

    public function openingIndex(Request $request): View
    {
        $query = $this->openingPayableQuery($request)->with('supplierMaster');
        $totals = (clone $query)->selectRaw('COUNT(*) as count, COALESCE(SUM(HUTANG), 0) as payable, COALESCE(SUM(BAYAR), 0) as paid, COALESCE(SUM(SALDO), 0) as balance')->first();
        $printMode = $request->boolean('print');
        $hutangs = $printMode
            ? (clone $query)->orderBy('TGLFAKTUR')->orderBy('NOFAKTUR')->get()
            : (clone $query)->latest('id')->paginate(15)->withQueryString();

        return view('hutang.opening-index', compact('hutangs', 'totals', 'printMode'));
    }

    public function exportSaldoAwal(Request $request)
    {
        $rows = $this->openingPayableQuery($request)->with('supplierMaster')->orderBy('TGLFAKTUR')->orderBy('NOFAKTUR')->get();

        return Excel::download(new OpeningPayableExport(
            $rows,
            trim((string) $request->input('search')),
            trim((string) $request->input('supplier')),
        ), 'saldo-awal-hutang-'.now()->format('Ymd-His').'.xlsx');
    }

    public function pdfSaldoAwal(Request $request)
    {
        $rows = $this->openingPayableQuery($request)->with('supplierMaster')->orderBy('TGLFAKTUR')->orderBy('NOFAKTUR')->get();
        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        $data = $rows->map(fn ($row) => [
            $row->TGLFAKTUR?->format('d/m/Y') ?? '-', $row->TGLJT?->format('d/m/Y') ?? '-',
            (string) $row->NOFAKTUR, (string) $row->supplier_display,
            number_format((float) $row->HUTANG, 2, ',', '.'), number_format((float) $row->BAYAR, 2, ',', '.'),
            number_format((float) $row->SALDO, 2, ',', '.'), (float) $row->SALDO <= 0 ? 'LUNAS' : 'OPEN',
        ])->all();

        return $this->fastTablePdf->stream([
            'title' => 'SALDO AWAL HUTANG', 'subtitles' => ['Dicetak '.now()->format('d/m/Y H:i')],
            'headings' => ['Tanggal','J. Tempo','Faktur','Supplier','Hutang','Bayar','Sisa','Status'],
            'columns' => [48,48,72,158,72,62,72,32], 'rows' => $data, 'numeric' => [4,5,6],
            'dot_matrix' => $dotMatrix, 'font_size' => $dotMatrix ? 5.3 : 5.8, 'rows_per_page' => 62,
            'filename' => 'saldo_awal_hutang_'.($dotMatrix ? 'dot_matrix_' : 'standard_').now()->format('Ymd_His').'.pdf',
        ]);
    }

    public function editSaldoAwal(Aph $hutang): View|RedirectResponse
    {
        if (! $this->isOpeningBalance($hutang)) return redirect()->route('saldo-awal-hutang.index')->with('error', 'Hanya saldo awal hutang yang dapat diedit melalui aksi ini.');
        if ($this->periodLockService->isLocked($hutang->TGLFAKTUR)) return redirect()->route('saldo-awal-hutang.index')->with('error', $this->periodLockService->lockedMessage($hutang->TGLFAKTUR));
        if ($this->hasSettlement($hutang)) return redirect()->route('saldo-awal-hutang.index')->with('error', 'Saldo awal sudah memiliki pembayaran, retur, atau diskon.');

        return view('hutang.saldo-awal', compact('hutang'));
    }

    public function storeSaldoAwal(Request $request): RedirectResponse
    {
        $request->merge([
            'nofaktur' => strtoupper(trim((string) $request->nofaktur)),
            'supplier' => strtoupper(trim((string) $request->supplier)),
        ]);
        $validated = $request->validate([
            'nofaktur' => ['required', 'string', 'max:50', Rule::unique('aph', 'NOFAKTUR'), Rule::unique('jurnals', 'referensi')],
            'tglfaktur' => ['required', 'date'],
            'tgljt' => ['nullable', 'date', 'after_or_equal:tglfaktur'],
            'supplier' => $this->supplierValidationRules(),
            'nominal' => ['required', 'numeric', 'gt:0'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ], ['nofaktur.unique' => 'Nomor bukti/faktur sudah pernah digunakan.']);
        if ($this->periodLockService->isLocked($validated['tglfaktur'])) return back()->withInput()->with('error', $this->periodLockService->lockedMessage($validated['tglfaktur']));

        try {
            DB::transaction(function () use ($validated): void {
                Aph::query()->create([
                    'NOFAKTUR' => $validated['nofaktur'], 'TGLFAKTUR' => $validated['tglfaktur'],
                    'TGLJT' => $validated['tgljt'] ?? null, 'SUPPLIER' => $validated['supplier'],
                    'HUTANG' => $validated['nominal'], 'BAYAR' => 0, 'RETUR' => 0, 'DISCOUNT' => 0,
                    'SALDO' => $validated['nominal'], 'KETERANGAN' => $validated['deskripsi'] ?? null,
                    'STATUS' => 'OPEN', 'SOURCE' => 'SALDO_AWAL_MANUAL', 'NOJURNAL' => null,
                    'USER' => auth()->user()?->name ?? 'System',
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Saldo awal hutang gagal disimpan: '.$e->getMessage());
        }

        return redirect()->route('saldo-awal-hutang.index')->with('success', 'Saldo awal hutang berhasil disimpan tanpa jurnal.');
    }

    public function updateSaldoAwal(Request $request, Aph $hutang): RedirectResponse
    {
        if (! $this->isOpeningBalance($hutang)) return redirect()->route('saldo-awal-hutang.index')->with('error', 'Hanya saldo awal hutang yang dapat diedit.');
        if ($this->periodLockService->isLocked($hutang->TGLFAKTUR)) return redirect()->route('saldo-awal-hutang.index')->with('error', $this->periodLockService->lockedMessage($hutang->TGLFAKTUR));
        if ($this->hasSettlement($hutang)) return redirect()->route('saldo-awal-hutang.index')->with('error', 'Edit dibatalkan karena saldo awal sudah memiliki transaksi terkait.');
        $request->merge(['nofaktur'=>strtoupper(trim((string)$request->nofaktur)),'supplier'=>strtoupper(trim((string)$request->supplier))]);
        $validated = $request->validate([
            'nofaktur'=>['required','string','max:50',Rule::unique('aph','NOFAKTUR')->ignore($hutang->id),Rule::unique('jurnals','referensi')],
            'tglfaktur'=>['required','date'],'tgljt'=>['nullable','date','after_or_equal:tglfaktur'],'supplier'=>$this->supplierValidationRules(),
            'nominal'=>['required','numeric','gt:0'],'deskripsi'=>['nullable','string','max:1000'],
        ]);
        if ($this->periodLockService->isLocked($validated['tglfaktur'])) return back()->withInput()->with('error', $this->periodLockService->lockedMessage($validated['tglfaktur']));

        try {
            DB::transaction(function () use ($hutang, $validated): void {
                $locked = Aph::whereKey($hutang->id)->lockForUpdate()->firstOrFail();
                if (! $this->isOpeningBalance($locked) || $this->hasSettlement($locked)) throw new \RuntimeException('Data berubah atau sudah memiliki transaksi terkait.');
                $before = $locked->only(['NOFAKTUR','TGLFAKTUR','TGLJT','SUPPLIER','HUTANG','SALDO','KETERANGAN']);
                $locked->update(['NOFAKTUR'=>$validated['nofaktur'],'TGLFAKTUR'=>$validated['tglfaktur'],'TGLJT'=>$validated['tgljt'] ?? null,'SUPPLIER'=>$validated['supplier'],'HUTANG'=>$validated['nominal'],'SALDO'=>$validated['nominal'],'KETERANGAN'=>$validated['deskripsi'] ?? null,'STATUS'=>'OPEN','USER_UPDATE'=>auth()->user()?->name ?? 'System']);
                app(AuditLogService::class)->record('update','saldo-awal-hutang',$locked,"Mengubah saldo awal hutang {$locked->NOFAKTUR}.",['before'=>$before]);
            });
            return redirect()->route('saldo-awal-hutang.index')->with('success','Saldo awal hutang berhasil diperbarui tanpa jurnal.');
        } catch (\Throwable $e) { report($e); return back()->withInput()->with('error','Gagal memperbarui saldo awal hutang: '.$e->getMessage()); }
    }

    public function destroySaldoAwal(Aph $hutang): RedirectResponse
    {
        if (! $this->isOpeningBalance($hutang)) return redirect()->route('saldo-awal-hutang.index')->with('error','Hanya saldo awal hutang yang dapat dihapus.');
        if ($this->periodLockService->isLocked($hutang->TGLFAKTUR)) return redirect()->route('saldo-awal-hutang.index')->with('error',$this->periodLockService->lockedMessage($hutang->TGLFAKTUR));
        if ($this->hasSettlement($hutang)) return redirect()->route('saldo-awal-hutang.index')->with('error','Saldo awal tidak dapat dihapus karena sudah memiliki pembayaran, retur, atau diskon.');
        try {
            DB::transaction(function () use ($hutang): void {
                $locked = Aph::whereKey($hutang->id)->lockForUpdate()->firstOrFail();
                if (! $this->isOpeningBalance($locked) || $this->hasSettlement($locked)) throw new \RuntimeException('Data berubah atau sudah memiliki transaksi terkait.');
                $snapshot=$locked->toArray(); $number=$locked->NOFAKTUR; $locked->delete();
                app(AuditLogService::class)->record('delete','saldo-awal-hutang',null,"Menghapus saldo awal hutang {$number}.",['deleted_data'=>$snapshot]);
            });
            return redirect()->route('saldo-awal-hutang.index')->with('success','Saldo awal hutang berhasil dihapus tanpa mengubah jurnal.');
        } catch (\Throwable $e) { report($e); return redirect()->route('saldo-awal-hutang.index')->with('error','Gagal menghapus saldo awal hutang: '.$e->getMessage()); }
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'nofaktur' => strtoupper(trim((string) $request->nofaktur)),
            'supplier' => strtoupper(trim((string) $request->supplier)),
        ]);
        $validator = Validator::make($request->all(), [
            'nofaktur' => ['required', 'string', 'max:50', Rule::unique('aph', 'NOFAKTUR'), Rule::unique('jurnals', 'referensi')],
            'tglfaktur' => 'required|date',
            'tgljt' => 'nullable|date|after_or_equal:tglfaktur',
            'supplier' => $this->supplierValidationRules(),
            'coa_hutang_id' => ['required', 'integer', 'exists:coas,id'],
            'coa_beban_id' => ['required', 'integer', 'different:coa_hutang_id', Rule::exists('coas', 'id')->where('tipe', 'Beban')],
            'nilai_dpp' => ['required', 'numeric', 'gt:0'],
            'coa_pajak_id' => ['nullable', 'integer', 'different:coa_hutang_id', 'different:coa_beban_id', 'exists:coas,id'],
            'nilai_pajak' => ['nullable', 'numeric', 'min:0'],
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($this->periodLockService->isLocked($request->tglfaktur)) {
            return back()->withInput()->with('error', $this->periodLockService->lockedMessage($request->tglfaktur));
        }

        $dpp = round((float) $request->nilai_dpp, 2);
        $pajak = round((float) ($request->nilai_pajak ?? 0), 2);
        if ($pajak > 0 && ! $request->filled('coa_pajak_id')) {
            return back()->withInput()->with('error', 'Akun Pajak Masukan wajib dipilih ketika nilai pajak diisi.');
        }
        $nominal = $dpp + $pajak;

        $selectedAccounts = Coa::query()->whereIn('id', array_filter([
            $request->coa_hutang_id, $request->coa_beban_id, $request->coa_pajak_id,
        ]))->get();
        foreach ($selectedAccounts as $account) {
            if (! $account->is_active || $account->level !== 'Akun Posting') {
                return back()->withInput()->with('error', "Akun {$account->kode_akun} harus aktif dan berupa akun posting.");
            }
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();
            $supplierLabel = $this->supplierLabel($request->supplier);

            $hutang = Aph::create([
                'NOFAKTUR' => $request->nofaktur,
                'TGLFAKTUR' => $request->tglfaktur,
                'TGLJT' => $request->tgljt,
                'SUPPLIER' => $request->supplier,
                'HUTANG' => $nominal,
                'BAYAR' => 0,
                'SALDO' => $nominal,
                'KETERANGAN' => $request->deskripsi,
                'STATUS' => 'OPEN',
                'SOURCE' => 'HUTANG_BERJALAN_MANUAL',
                'USER' => $user->name ?? 'System',
            ]);

            $accountsPayableCoaId = (int) $request->coa_hutang_id;
            $purchaseExpenseCoaId = (int) $request->coa_beban_id;

            if (! $accountsPayableCoaId || ! $purchaseExpenseCoaId) {
                throw new \RuntimeException('Akun hutang dan akun beban wajib dipilih.');
            }

            $journalItems = [[
                'coa_id' => $purchaseExpenseCoaId,
                'deskripsi' => 'Pembelian dari ' . $supplierLabel . ' (Faktur: ' . $request->nofaktur . ')',
                'debit' => $dpp, 'kredit' => 0,
            ]];
            if ($pajak > 0) {
                $journalItems[] = ['coa_id' => (int) $request->coa_pajak_id, 'deskripsi' => 'Pajak masukan faktur '.$request->nofaktur, 'debit' => $pajak, 'kredit' => 0];
            }
            $journalItems[] = ['coa_id' => $accountsPayableCoaId, 'deskripsi' => 'Hutang usaha kepada '.$supplierLabel.' (Faktur: '.$request->nofaktur.')', 'debit' => 0, 'kredit' => $nominal];

            $journal = $this->journalService->createJournal(
                $this->journalService->generateJournalNumber('JU', $request->tglfaktur),
                $request->tglfaktur,
                'Pencatatan Hutang: ' . $supplierLabel . ' - ' . $request->nofaktur,
                $journalItems,
                $request->nofaktur,
                $user->id ?? null,
                'Pembelian',
                'Pembelian'
            );

            $hutang->NOJURNAL = $journal->no_jurnal;
            $hutang->save();

            DB::commit();

            return redirect()->route('hutang.index')->with('success', 'Hutang berhasil disimpan dan dijurnal.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menyimpan hutang: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Aph $hutang): View
    {
        $hutang->load('supplierMaster', 'details.header');
        $journal = $hutang->NOJURNAL
            ? Jurnal::query()->where('no_jurnal', $hutang->NOJURNAL)->with('items.coa')->first()
            : null;
        $payableCoaId = AccountMapping::getCoaId('accounts_payable');
        $payableCredit = $journal && $payableCoaId
            ? (float) $journal->items->where('coa_id', $payableCoaId)->sum('kredit')
            : 0.0;
        $journalChecks = [
            'exists' => (bool) $journal,
            'balanced' => $journal ? abs((float) $journal->total_debit - (float) $journal->total_kredit) <= 0.01 : false,
            'reference_matches' => $journal ? (string) $journal->referensi === (string) $hutang->NOFAKTUR : false,
            'payable_matches' => $journal && $payableCoaId ? abs($payableCredit - (float) $hutang->HUTANG) <= 0.01 : false,
            'payable_mapping_exists' => (bool) $payableCoaId,
        ];
        $journalValid = $journalChecks['exists']
            && $journalChecks['balanced']
            && $journalChecks['reference_matches']
            && $journalChecks['payable_matches'];

        return view('hutang.show', compact('hutang', 'journal', 'journalChecks', 'journalValid'));
    }

    public function edit(Aph $hutang): View|RedirectResponse
    {
        if ($hutang->SOURCE !== 'HUTANG_BERJALAN_MANUAL') return redirect()->route('hutang.index')->with('error', 'Hanya hutang berjalan manual yang dapat diedit melalui aksi ini.');
        if ($this->periodLockService->isLocked($hutang->TGLFAKTUR)) return redirect()->route('hutang.index')->with('error', $this->periodLockService->lockedMessage($hutang->TGLFAKTUR));
        if ($this->hasSettlement($hutang)) return redirect()->route('hutang.index')->with('error', 'Hutang sudah memiliki pembayaran, retur, atau diskon.');
        $journal = $hutang->NOJURNAL
            ? Jurnal::query()->where('no_jurnal', $hutang->NOJURNAL)->with('items')->first()
            : null;
        if (! $journal) return redirect()->route('hutang.index')->with('error', 'Jurnal terkait tidak ditemukan. Edit dibatalkan untuk menjaga konsistensi data.');
        if ($this->periodLockService->isLocked($journal->tanggal)) return redirect()->route('hutang.index')->with('error', $this->periodLockService->lockedMessage($journal->tanggal));
        $inputTaxId = AccountMapping::getCoaId('input_tax');
        $taxItem = $inputTaxId ? $journal->items->firstWhere('coa_id', $inputTaxId) : null;
        $expenseItem = $journal->items->first(fn ($item) => (float)$item->debit > 0 && (! $taxItem || $item->id !== $taxItem->id));

        return view('hutang.edit', [
            'hutang' => $hutang,
            'accounts' => Coa::query()->where('is_active', true)
                ->where('level', 'Akun Posting')->orderBy('kode_akun')->get(),
            'expenseAccounts' => Coa::query()->where('is_active', true)
                ->where('level', 'Akun Posting')->where('tipe', 'Beban')->orderBy('kode_akun')->get(),
            'expenseAccountId' => $expenseItem?->coa_id,
            'payableAccountId' => $journal?->items->first(fn ($item) => (float) $item->kredit > 0)?->coa_id
                ?? AccountMapping::getCoaId('accounts_payable'),
            'inputTaxAccountId' => $taxItem?->coa_id,
            'dppValue' => (float)($expenseItem?->debit ?? 0),
            'taxValue' => (float)($taxItem?->debit ?? 0),
        ]);
    }

    public function update(Request $request, Aph $hutang): RedirectResponse
    {
        $request->merge([
            'nofaktur' => strtoupper(trim((string) $request->nofaktur)),
            'supplier' => strtoupper(trim((string) $request->supplier)),
        ]);

        if ($hutang->SOURCE !== 'HUTANG_BERJALAN_MANUAL') {
            return redirect()->route('hutang.index')->with('error', 'Hanya hutang berjalan manual yang dapat diedit.');
        }
        if ($this->periodLockService->isLocked($hutang->TGLFAKTUR)) {
            return redirect()->route('hutang.index')->with('error', $this->periodLockService->lockedMessage($hutang->TGLFAKTUR));
        }
        if ($this->hasSettlement($hutang)) {
            return redirect()->route('hutang.show', $hutang)
                ->with('error', 'Hutang yang sudah memiliki pembayaran, retur, atau diskon tidak dapat diubah.');
        }

        $existingJournal = $hutang->NOJURNAL ? Jurnal::where('no_jurnal', $hutang->NOJURNAL)->first() : null;
        if (! $existingJournal) return redirect()->route('hutang.show', $hutang)->with('error', 'Jurnal terkait tidak ditemukan.');

        $validator = Validator::make($request->all(), [
            'nofaktur' => [
                'required',
                'string',
                'max:50',
                Rule::unique('aph', 'NOFAKTUR')->ignore($hutang->id),
                Rule::unique('jurnals', 'referensi')->ignore($existingJournal->id),
            ],
            'tglfaktur' => 'required|date',
            'tgljt' => 'nullable|date|after_or_equal:tglfaktur',
            'supplier' => $this->supplierValidationRules(),
            'nilai_dpp' => 'required|numeric|min:0.01',
            'coa_hutang_id' => ['required', 'integer', 'exists:coas,id'],
            'coa_beban_id' => ['required', 'integer', 'different:coa_hutang_id', Rule::exists('coas', 'id')->where('tipe', 'Beban')],
            'coa_pajak_id' => ['nullable','integer','different:coa_hutang_id','different:coa_beban_id','exists:coas,id'],
            'nilai_pajak' => ['nullable','numeric','min:0'],
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if ($this->periodLockService->isLocked($request->tglfaktur)) return back()->withInput()->with('error', $this->periodLockService->lockedMessage($request->tglfaktur));

        $dpp = round((float)$request->nilai_dpp, 2); $tax = round((float)($request->nilai_pajak ?? 0), 2);
        if ($tax > 0 && ! $request->filled('coa_pajak_id')) return back()->withInput()->with('error','Akun Pajak Masukan wajib dipilih ketika nilai pajak lebih dari nol.');
        $accountIds = array_filter([$request->coa_hutang_id,$request->coa_beban_id,$request->coa_pajak_id]);
        $selectedAccounts = Coa::query()->whereIn('id',$accountIds)->where('is_active',true)->where('level','Akun Posting')->count();
        if ($selectedAccounts !== count($accountIds)) return back()->withInput()->with('error', 'Semua akun harus aktif serta berupa akun posting.');

        DB::beginTransaction();

        try {
            $hutang = Aph::whereKey($hutang->id)->lockForUpdate()->firstOrFail();
            if ($this->hasSettlement($hutang)) throw new \RuntimeException('Hutang sudah memiliki transaksi terkait.');
            $nominal = $dpp + $tax;
            $supplierLabel = $this->supplierLabel($request->supplier);

            $hutang->fill([
                'NOFAKTUR' => $request->nofaktur,
                'TGLFAKTUR' => $request->tglfaktur,
                'TGLJT' => $request->tgljt,
                'SUPPLIER' => $request->supplier,
                'HUTANG' => $nominal,
                'SALDO' => $nominal,
                'KETERANGAN' => $request->deskripsi,
                'STATUS' => 'OPEN',
                'USER_UPDATE' => auth()->user()->name ?? 'System',
            ]);
            $hutang->save();

            $accountsPayableCoaId = (int) $request->coa_hutang_id;
            $purchaseExpenseCoaId = (int) $request->coa_beban_id;

            if (! $accountsPayableCoaId || ! $purchaseExpenseCoaId) {
                throw new \RuntimeException('Akun hutang dan akun beban wajib dipilih.');
            }

            $journalItems = [
                [
                    'coa_id' => $purchaseExpenseCoaId,
                    'deskripsi' => 'Pembelian dari ' . $supplierLabel . ' (Faktur: ' . $request->nofaktur . ')',
                    'debit' => $dpp,
                    'kredit' => 0,
                ],
            ];
            if ($tax > 0) $journalItems[] = ['coa_id'=>(int)$request->coa_pajak_id,'deskripsi'=>'Pajak masukan faktur '.$request->nofaktur,'debit'=>$tax,'kredit'=>0];
            $journalItems[] = [
                    'coa_id' => $accountsPayableCoaId,
                    'deskripsi' => 'Hutang usaha kepada ' . $supplierLabel . ' (Faktur: ' . $request->nofaktur . ')',
                    'debit' => 0,
                    'kredit' => $nominal,
                ];

            $journal = Jurnal::whereKey($existingJournal->id)->lockForUpdate()->firstOrFail();

            if ($journal) {
                $oldNumber = $journal->no_jurnal;
                $newNumber = $journal->tanggal?->toDateString() === $request->tglfaktur ? $oldNumber : $this->journalService->generateJournalNumber('JU', $request->tglfaktur);
                $journal->items()->delete();
                $journal->update([
                    'no_jurnal' => $newNumber,
                    'tanggal' => $request->tglfaktur,
                    'keterangan' => 'Pencatatan Hutang: ' . $supplierLabel . ' - ' . $request->nofaktur,
                    'referensi' => $request->nofaktur,
                    'total_debit' => $nominal,
                    'total_kredit' => $nominal,
                ]);
                $journal->items()->createMany($journalItems);
                $hutang->NOJURNAL = $newNumber;
                $hutang->save();
            }

            DB::commit();
            app(AuditLogService::class)->record('update','hutang',$journal,"Mengubah hutang berjalan {$hutang->NOFAKTUR}.",['hutang_id'=>$hutang->id]);

            return redirect()->route('hutang.show', $hutang)->with('success', 'Hutang berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memperbarui hutang: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Aph $hutang): RedirectResponse
    {
        if ($hutang->SOURCE !== 'HUTANG_BERJALAN_MANUAL') return redirect()->route('hutang.index')->with('error','Hanya hutang berjalan manual yang dapat dihapus melalui aksi ini.');
        if ($this->periodLockService->isLocked($hutang->TGLFAKTUR)) return redirect()->route('hutang.index')->with('error',$this->periodLockService->lockedMessage($hutang->TGLFAKTUR));
        if ($this->hasSettlement($hutang)) {
            return redirect()->route('hutang.show', $hutang)
                ->with('error', 'Hutang yang sudah memiliki pembayaran, retur, atau diskon tidak dapat dihapus.');
        }

        DB::beginTransaction();

        try {
            $hutang = Aph::whereKey($hutang->id)->lockForUpdate()->firstOrFail();
            if ($this->hasSettlement($hutang)) throw new \RuntimeException('Hutang sudah memiliki transaksi terkait.');
            $journal = $hutang->NOJURNAL ? Jurnal::where('no_jurnal', $hutang->NOJURNAL)->lockForUpdate()->first() : null;
            if (! $journal) throw new \RuntimeException('Jurnal terkait tidak ditemukan.');
            if ($this->periodLockService->isLocked($journal->tanggal)) throw new \RuntimeException($this->periodLockService->lockedMessage($journal->tanggal));
            $snapshot=$hutang->toArray(); $number=$hutang->NOFAKTUR;
            $journal->items()->delete(); $journal->delete(); $hutang->delete();

            DB::commit();
            app(AuditLogService::class)->record('delete','hutang',null,"Menghapus hutang berjalan {$number} beserta jurnalnya.",['deleted_data'=>$snapshot]);

            return redirect()->route('hutang.index')->with('success', 'Hutang berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menghapus hutang: ' . $e->getMessage());
        }
    }

    public function searchSupplier(Request $request)
    {
        $q = trim((string) $request->q);
        $forModal = $request->boolean('modal');

        if (! $forModal && strlen($q) < 2) {
            return response()->json([]);
        }

        $limit = $forModal ? 100 : 20;

        $rows = Aph::query()
            ->select('SUPPLIER')
            ->when($q !== '', fn ($query) => $query->where('SUPPLIER', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('SUPPLIER')
            ->limit($limit)
            ->get();

        if (! Schema::hasTable('msupplier')) {
            return response()->json($rows->map(fn ($row) => [
                'value' => $row->SUPPLIER,
                'label' => $row->SUPPLIER,
            ]));
        }

        $rows = Msupplier::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($search) use ($q) {
                    $search->where('SUPPLIER', 'like', "%{$q}%")
                        ->orWhere('NAMA', 'like', "%{$q}%");
                });
            })
            ->orderBy('SUPPLIER')
            ->limit($limit)
            ->get(['SUPPLIER', 'NAMA']);

        return response()->json($rows->map(function (Msupplier $row) {
            $supplier = trim((string) $row->SUPPLIER);
            $name = trim((string) $row->NAMA);

            return [
                'value' => $supplier,
                'label' => $supplier.' - '.($name ?: 'Tanpa Nama'),
                'supplier' => $supplier,
                'nama' => $name,
            ];
        }));
    }

    private function supplierValidationRules(): array
    {
        $rules = ['required', 'string', 'max:100'];

        if (Schema::hasTable('msupplier')) {
            $rules[] = Rule::exists('msupplier', 'SUPPLIER');
        }

        return $rules;
    }

    private function openingPayableQuery(Request $request)
    {
        return Aph::query()
            ->where('SOURCE', 'like', 'SALDO_AWAL_%')
            ->when($request->filled('supplier'), fn ($query) => $query->where('SUPPLIER', trim((string) $request->input('supplier'))))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.trim((string) $request->input('search')).'%';
                $query->where(function ($filter) use ($search): void {
                    $filter->where('NOFAKTUR', 'like', $search)->orWhere('SUPPLIER', 'like', $search)->orWhere('KETERANGAN', 'like', $search)
                        ->orWhereHas('supplierMaster', fn ($supplier) => $supplier->where('NAMA', 'like', $search));
                });
            });
    }

    private function supplierLabel(string $supplierCode): string
    {
        if (! Schema::hasTable('msupplier')) {
            return $supplierCode;
        }

        $supplier = Msupplier::find($supplierCode);

        return $supplier?->display_name ?? $supplierCode;
    }

    private function isOpeningBalance(Aph $hutang): bool
    {
        return str_starts_with((string)$hutang->SOURCE, 'SALDO_AWAL_');
    }

    private function hasSettlement(Aph $hutang): bool
    {
        return (float)$hutang->BAYAR > 0 || (float)$hutang->RETUR > 0 || (float)$hutang->DISCOUNT > 0 || $hutang->details()->exists();
    }
}
