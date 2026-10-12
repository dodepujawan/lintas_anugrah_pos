<?php

namespace App\Http\Controllers;

use App\Exports\OpeningReceivableExport;
use App\Models\AccountMapping;
use App\Models\Arh;
use App\Models\Bard;
use App\Models\Jurnal;
use App\Services\AuditLogService;
use App\Services\PeriodLockService;
use App\Services\FastTablePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class PiutangController extends Controller
{
    public function __construct(private readonly FastTablePdfService $fastTablePdf) {}

    public function index(Request $request)
    {
        $query = $this->openingReceivableQuery($request);
        $totals = (clone $query)->selectRaw('COUNT(*) as count, COALESCE(SUM(PIUTANG), 0) as receivable, COALESCE(SUM(BAYAR), 0) as paid, COALESCE(SUM(SALDO), 0) as balance')->first();
        $printMode = $request->boolean('print');
        $piutangs = $printMode
            ? (clone $query)->orderBy('TGLFAKTUR')->orderBy('NOFAKTUR')->get()
            : (clone $query)->orderByDesc('id')->paginate(15)->withQueryString();

        $visiblePiutangs = $printMode ? $piutangs : $piutangs->getCollection();
        $this->attachCustomerNames($visiblePiutangs);
        $this->attachPaymentDetails($visiblePiutangs);

        return view('piutang.index', compact('piutangs', 'totals', 'printMode'));
    }

    public function export(Request $request)
    {
        $rows = $this->openingReceivableQuery($request)->orderBy('TGLFAKTUR')->orderBy('NOFAKTUR')->get();
        $this->attachCustomerNames($rows);

        return Excel::download(new OpeningReceivableExport(
            $rows,
            trim((string) $request->input('search')),
            trim((string) $request->input('customer')),
        ), 'saldo-awal-piutang-'.now()->format('Ymd-His').'.xlsx');
    }

    public function pdf(Request $request)
    {
        $rows = $this->openingReceivableQuery($request)->orderBy('TGLFAKTUR')->orderBy('NOFAKTUR')->get();
        $this->attachCustomerNames($rows);
        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        $data = $rows->map(fn ($row) => [
            $row->TGLFAKTUR?->format('d/m/Y') ?? '-', $row->TGLJT?->format('d/m/Y') ?? '-',
            (string) $row->NOFAKTUR, trim((string) $row->CUSTOMER.' - '.(string) $row->CUSTOMER_NAME, ' -'),
            number_format((float) $row->PIUTANG, 2, ',', '.'), number_format((float) $row->BAYAR, 2, ',', '.'),
            number_format((float) $row->SALDO, 2, ',', '.'), (float) $row->SALDO <= 0 ? 'LUNAS' : 'OPEN',
        ])->all();

        return $this->fastTablePdf->stream([
            'title' => 'SALDO AWAL PIUTANG', 'subtitles' => ['Dicetak '.now()->format('d/m/Y H:i')],
            'headings' => ['Tanggal','J. Tempo','Faktur','Customer','Piutang','Bayar','Sisa','Status'],
            'columns' => [48,48,72,158,72,62,72,32], 'rows' => $data, 'numeric' => [4,5,6],
            'dot_matrix' => $dotMatrix, 'font_size' => $dotMatrix ? 5.3 : 5.8, 'rows_per_page' => 62,
            'filename' => 'saldo_awal_piutang_'.($dotMatrix ? 'dot_matrix_' : 'standard_').now()->format('Ymd_His').'.pdf',
        ]);
    }

    public function create()
    {
        return view('piutang.create');
    }

    public function edit(Arh $piutang)
    {
        if (! $this->isOpeningBalance($piutang)) {
            return redirect()->route('piutang.index')->with('error', 'Hanya saldo awal piutang manual atau import yang dapat diedit.');
        }
        if (app(PeriodLockService::class)->isLocked($piutang->TGLFAKTUR)) {
            return redirect()->route('piutang.index')->with('error', app(PeriodLockService::class)->lockedMessage($piutang->TGLFAKTUR));
        }
        if ($this->hasSettlement($piutang)) {
            return redirect()->route('piutang.index')->with('error', 'Saldo awal sudah memiliki pembayaran, retur, atau diskon sehingga tidak dapat diedit.');
        }

        return view('piutang.create', compact('piutang'));
    }

    public function update(Request $request, Arh $piutang)
    {
        if (! $this->isOpeningBalance($piutang)) {
            return redirect()->route('piutang.index')->with('error', 'Hanya saldo awal piutang manual atau import yang dapat diedit.');
        }
        if (app(PeriodLockService::class)->isLocked($piutang->TGLFAKTUR)) {
            return redirect()->route('piutang.index')->with('error', app(PeriodLockService::class)->lockedMessage($piutang->TGLFAKTUR));
        }
        if ($this->hasSettlement($piutang)) {
            return redirect()->route('piutang.index')->with('error', 'Edit dibatalkan karena saldo awal sudah memiliki transaksi pelunasan.');
        }

        $request->merge([
            'nofaktur' => strtoupper(trim((string) $request->input('nofaktur'))),
            'pelanggan' => strtoupper(trim((string) $request->input('pelanggan'))),
        ]);
        $validated = $request->validate([
            'nofaktur' => ['required', 'string', 'max:50', Rule::unique('arh', 'NOFAKTUR')->ignore($piutang->id)],
            'tglfaktur' => ['required', 'date'],
            'tgljt' => ['required', 'date', 'after_or_equal:tglfaktur'],
            'pelanggan' => ['required', 'string', 'max:30', Rule::exists('mcustomer', 'CUSTOMER')],
            'nominal' => ['required', 'numeric', 'gt:0'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ], ['pelanggan.exists' => 'Customer tidak ditemukan. Pilih customer dari daftar pencarian.']);
        if (app(PeriodLockService::class)->isLocked($validated['tglfaktur'])) {
            return back()->withErrors(['tglfaktur' => app(PeriodLockService::class)->lockedMessage($validated['tglfaktur'])])->withInput();
        }

        try {
            DB::transaction(function () use ($piutang, $validated): void {
                $locked = Arh::whereKey($piutang->id)->lockForUpdate()->firstOrFail();
                if ($this->hasSettlement($locked)) throw new \RuntimeException('Saldo awal sudah memiliki transaksi pelunasan.');
                $before = $locked->only(['NOFAKTUR','TGLFAKTUR','TGLJT','CUSTOMER','PIUTANG','SALDO','KETERANGAN']);
                $locked->update([
                    'NOFAKTUR' => $validated['nofaktur'], 'TGLFAKTUR' => $validated['tglfaktur'],
                    'TGLJT' => $validated['tgljt'], 'CUSTOMER' => $validated['pelanggan'],
                    'PIUTANG' => $validated['nominal'], 'SALDO' => $validated['nominal'],
                    'KETERANGAN' => $validated['deskripsi'] ?? null, 'STATUS' => 'OPEN',
                    'USER_UPDATE' => auth()->user()?->name ?? 'System',
                ]);
                app(AuditLogService::class)->record('update', 'piutang', $locked, "Mengubah saldo awal piutang {$locked->NOFAKTUR}.", ['before' => $before]);
            });
            return redirect()->route('piutang.index')->with('success', 'Saldo awal piutang berhasil diperbarui.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal memperbarui saldo awal piutang: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Arh $piutang)
    {
        if (! $this->isOpeningBalance($piutang)) {
            return redirect()->route('piutang.index')->with('error', 'Hanya saldo awal piutang manual atau import yang dapat dihapus.');
        }
        if (app(PeriodLockService::class)->isLocked($piutang->TGLFAKTUR)) {
            return redirect()->route('piutang.index')->with('error', app(PeriodLockService::class)->lockedMessage($piutang->TGLFAKTUR));
        }
        if ($this->hasSettlement($piutang)) {
            return redirect()->route('piutang.index')->with('error', 'Saldo awal tidak dapat dihapus karena sudah memiliki pembayaran, retur, atau diskon di aplikasi.');
        }

        try {
            DB::transaction(function () use ($piutang): void {
                $locked = Arh::whereKey($piutang->id)->lockForUpdate()->firstOrFail();
                if (! $this->isOpeningBalance($locked) || $this->hasSettlement($locked)) {
                    throw new \RuntimeException('Data berubah atau sudah memiliki transaksi terkait.');
                }
                $snapshot = $locked->toArray();
                $invoice = $locked->NOFAKTUR;
                $locked->delete();
                app(AuditLogService::class)->record('delete', 'piutang', null, "Menghapus saldo awal piutang {$invoice}.", ['deleted_data' => $snapshot]);
            });

            return redirect()->route('piutang.index')->with('success', "Saldo awal piutang {$piutang->NOFAKTUR} berhasil dihapus.");
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('piutang.index')->with('error', 'Gagal menghapus saldo awal piutang: '.$e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->merge([
            'pelanggan' => strtoupper(trim((string) $request->input('pelanggan'))),
        ]);

        $validator = Validator::make($request->all(), [
            'nofaktur' => 'required|string|max:50|unique:arh,NOFAKTUR',
            'tglfaktur' => 'required|date',
            'tgljt' => 'required|date|after_or_equal:tglfaktur',
            'pelanggan' => 'required|string|max:30|exists:mcustomer,CUSTOMER',
            'nominal' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ], [
            'pelanggan.exists' => 'Customer tidak ditemukan. Pilih customer dari daftar pencarian.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();

            // Create ARH (Accounts Receivable Header)
            $arh = Arh::create([
                'NOFAKTUR' => $request->nofaktur,
                'TGLFAKTUR' => $request->tglfaktur,
                'TGLJT' => $request->tgljt,
                'CUSTOMER' => $request->pelanggan,
                'PIUTANG' => $request->nominal,
                'SALDO' => $request->nominal,
                'BAYAR' => 0,
                'KETERANGAN' => $request->deskripsi,
                'STATUS' => 'OPEN',
                'SOURCE' => 'SALDO_AWAL_MANUAL',
                'NOJURNAL' => null,
                'USER' => $user->name ?? 'System',
            ]);

            DB::commit();

            return redirect()->route('piutang.index')->with('success', 'Saldo awal piutang berhasil ditambahkan tanpa jurnal baru.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menyimpan piutang: '.$e->getMessage())->withInput();
        }
    }

    public function searchPelanggan(Request $request)
    {
        $q = trim((string) $request->q);
        $forModal = $request->boolean('modal');

        if (! $forModal && strlen($q) < 2) {
            return response()->json([]);
        }

        $rows = DB::table('mcustomer')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($search) use ($q) {
                    $search->where('CUSTOMER', 'like', "%{$q}%")
                        ->orWhere('NAMACUST', 'like', "%{$q}%");
                });
            })
            ->orderBy('CUSTOMER')
            ->limit($forModal ? 100 : 20)
            ->get();

        return response()->json(
            $rows->map(function ($row) {
                $customer = trim((string) $row->CUSTOMER);
                $name = trim((string) $row->NAMACUST);

                return [
                    'value' => $customer,
                    'customer' => $customer,
                    'nama' => $name,
                    'label' => $customer.' - '.$name,
                ];
            })
        );
    }

    public function show(Request $request, $piutang)
    {
        $piutang = Arh::query()->findOrFail($piutang);
        $this->attachCustomerNames(collect([$piutang]));
        $this->attachPaymentDetails(collect([$piutang]));
        $backUrl = route('piutang.index');
        $backLabel = 'Kembali';
        $fromPiutangBerjalan = $request->input('from') === 'piutang-berjalan';

        if ($fromPiutangBerjalan) {
            $backUrl = route('piutang-berjalan.index');
            $backLabel = 'Kembali ke Piutang Berjalan';
        }
        $journal = $piutang->NOJURNAL
            ? Jurnal::query()->where('no_jurnal', $piutang->NOJURNAL)->with('items.coa')->first()
            : null;
        $receivableCoaId = AccountMapping::getCoaId('accounts_receivable');
        $receivableDebit = $journal && $receivableCoaId
            ? (float) $journal->items->where('coa_id', $receivableCoaId)->sum('debit')
            : 0.0;
        $journalChecks = [
            'exists' => (bool) $journal,
            'balanced' => $journal ? abs((float) $journal->total_debit - (float) $journal->total_kredit) <= 0.01 : false,
            'reference_matches' => $journal ? (string) $journal->referensi === (string) $piutang->NOFAKTUR : false,
            'receivable_matches' => $journal && $receivableCoaId ? abs($receivableDebit - (float) $piutang->PIUTANG) <= 0.01 : false,
            'receivable_mapping_exists' => (bool) $receivableCoaId,
        ];
        $journalValid = $journalChecks['exists']
            && $journalChecks['balanced']
            && $journalChecks['reference_matches']
            && $journalChecks['receivable_matches'];

        return view('piutang.show', compact('piutang', 'backUrl', 'backLabel', 'fromPiutangBerjalan', 'journal', 'journalChecks', 'journalValid'));
    }

    private function openingReceivableQuery(Request $request)
    {
        return Arh::query()
            ->where('SOURCE', 'like', 'SALDO_AWAL_%')
            ->when($request->filled('customer'), fn ($query) => $query->where('CUSTOMER', trim((string) $request->input('customer'))))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.trim((string) $request->input('search')).'%';
                $query->where(function ($filter) use ($search): void {
                    $filter->where('NOFAKTUR', 'like', $search)
                        ->orWhere('CUSTOMER', 'like', $search)
                        ->orWhere('KETERANGAN', 'like', $search)
                        ->orWhereExists(function ($customer) use ($search): void {
                            $customer->selectRaw('1')->from('mcustomer')
                                ->whereColumn('mcustomer.CUSTOMER', 'arh.CUSTOMER')
                                ->where('mcustomer.NAMACUST', 'like', $search);
                        });
                });
            });
    }

    private function attachCustomerNames($piutangs): void
    {
        $customerCodes = $piutangs
            ->pluck('CUSTOMER')
            ->filter()
            ->map(fn ($code) => trim((string) $code))
            ->unique()
            ->values();

        $customerNames = DB::table('mcustomer')
            ->whereIn('CUSTOMER', $customerCodes)
            ->get(['CUSTOMER', 'NAMACUST'])
            ->mapWithKeys(fn ($customer) => [
                strtoupper(trim((string) $customer->CUSTOMER)) => trim((string) $customer->NAMACUST),
            ]);

        $piutangs->each(function (Arh $piutang) use ($customerNames): void {
            $key = strtoupper(trim((string) $piutang->CUSTOMER));
            $piutang->setAttribute('CUSTOMER_NAME', $customerNames->get($key));
        });
    }

    private function attachPaymentDetails($piutangs): void
    {
        $invoiceNumbers = $piutangs
            ->pluck('NOFAKTUR')
            ->filter()
            ->map(fn ($invoice) => trim((string) $invoice))
            ->unique()
            ->values();

        if ($invoiceNumbers->isEmpty()) {
            return;
        }

        $paymentDetails = Bard::query()
            ->with(['header.paymentCoa'])
            ->whereIn('NOFAKTUR', $invoiceNumbers)
            ->whereHas('header', fn ($query) => $query->where(function ($status) {
                $status->whereNull('STATUS')->orWhere('STATUS', '!=', 'VOID');
            }))
            ->orderBy('NOBAYAR')
            ->get()
            ->groupBy(fn (Bard $detail) => trim((string) $detail->NOFAKTUR));

        $piutangs->each(function (Arh $piutang) use ($paymentDetails): void {
            $details = $paymentDetails->get(trim((string) $piutang->NOFAKTUR), collect());
            $piutang->setRelation('paymentDetails', $details);
            $piutang->setAttribute('PAYMENT_DETAIL_COUNT', $details->count());
            $piutang->setAttribute('PAYMENT_SETTLED_TOTAL', $details->sum(fn (Bard $detail) =>
                (float) $detail->NILAI_BAYAR
                + (float) $detail->PPH23
                + (float) $detail->DISCOUNT
                + (float) $detail->BANK_ADMIN
                + (float) $detail->RETUR
            ));
        });
    }

    private function hasSettlement(Arh $piutang): bool
    {
        return (float) $piutang->BAYAR > 0
            || (float) $piutang->RETUR > 0
            || (float) $piutang->DISCOUNT > 0
            || DB::table('bard')->where('NOFAKTUR', $piutang->NOFAKTUR)->exists();
    }

    private function isOpeningBalance(Arh $piutang): bool
    {
        return str_starts_with((string) $piutang->SOURCE, 'SALDO_AWAL_');
    }
}
