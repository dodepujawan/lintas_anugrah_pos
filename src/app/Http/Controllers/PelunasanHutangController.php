<?php

namespace App\Http\Controllers;

use App\Models\AccountMapping;
use App\Models\Aph;
use App\Models\Bapd;
use App\Models\Baph;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\Msupplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use App\Services\AuditLogService;
use App\Services\JournalService;
use App\Services\PaymentVoidService;
use App\Services\PeriodLockService;

class PelunasanHutangController extends Controller
{
    public function __construct(private JournalService $journalService, private readonly PaymentVoidService $paymentVoidService, private readonly PeriodLockService $periodLockService)
    {
    }

    public function index(): View
    {
        $data = Baph::with('paymentCoa', 'supplierMaster', 'journal')->latest()->paginate(20);

        return view('pelunasan-hutang.index', compact('data'));
    }

    public function create(): View
    {
        $paymentAccounts = $this->paymentAccounts();
        $paymentToken = $this->newPaymentToken('payable_payment_form_tokens');

        return view('pelunasan-hutang.create', compact('paymentAccounts', 'paymentToken'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'supplier' => strtoupper(trim((string) $request->input('supplier'))),
        ]);

        $validator = Validator::make($request->all(), $this->paymentValidationRules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }
        if (! $this->consumePaymentToken((string) $request->input('_payment_token'), 'payable_payment_form_tokens')) {
            return response()->json([
                'success' => false,
                'message' => 'Pelunasan ini sudah diproses. Cek daftar pelunasan sebelum input ulang.',
            ], 409);
        }

        DB::beginTransaction();

        try {
            $details = collect($request->details)
                ->filter(fn ($row) => (float) ($row['bayar'] ?? 0) > 0)
                ->values();

            if ($details->isEmpty()) {
                throw new \InvalidArgumentException('Belum ada pembayaran yang valid.');
            }

            $nobayar = $this->generateNoBayar();
            $totalBayar = 0;
            $supplierLabel = $this->supplierLabel($request->supplier);

            $header = Baph::create([
                'NOBAYAR' => $nobayar,
                'TGLBAYAR' => $request->tglbayar,
                'SUPPLIER' => $request->supplier,
                'JUMLAH_BAYAR' => 0,
                'METODE_PEMBAYARAN' => $request->jenis_bayar,
                'PAYMENT_COA_ID' => $request->filled('payment_coa_id') ? (int) $request->payment_coa_id : null,
                'NO_REFERENSI' => $request->no_referensi,
                'KETERANGAN' => $request->keterangan,
                'CABANG' => '01',
                'USER' => auth()->user()->name ?? 'SYSTEM',
            ]);

            foreach ($details as $row) {
                $invoice = Aph::where('NOFAKTUR', $row['nofaktur'])->lockForUpdate()->first();

                if (! $invoice) {
                    throw new \InvalidArgumentException("Faktur {$row['nofaktur']} tidak ditemukan.");
                }

                if (trim((string) $invoice->SUPPLIER) !== $request->supplier) {
                    throw new \InvalidArgumentException("Faktur {$row['nofaktur']} bukan milik supplier {$supplierLabel}.");
                }

                $nilaiBayar = (float) $row['bayar'];
                $saldoFaktur = (float) $invoice->SALDO;

                if ($nilaiBayar > $saldoFaktur) {
                    throw new \InvalidArgumentException("Pembayaran faktur {$invoice->NOFAKTUR} melebihi sisa hutang.");
                }

                $sisa = $saldoFaktur - $nilaiBayar;
                $totalBayar += $nilaiBayar;

                Bapd::create([
                    'NOBAYAR' => $nobayar,
                    'NOFAKTUR' => $invoice->NOFAKTUR,
                    'TGLFAKTUR' => $invoice->TGLFAKTUR,
                    'SALDO_FAKTUR' => $saldoFaktur,
                    'NILAI_BAYAR' => $nilaiBayar,
                    'DISCOUNT' => 0,
                    'RETUR' => 0,
                    'SISA_FAKTUR' => $sisa,
                ]);

                $invoice->BAYAR = $invoice->BAYAR + $nilaiBayar;
                $invoice->SALDO = $sisa;

                if ($invoice->SALDO <= 0) {
                    $invoice->SALDO = 0;
                    if (Schema::hasColumn('aph', 'STATUS')) {
                        $invoice->STATUS = 'LUNAS';
                    }
                    if (Schema::hasColumn('aph', 'TGLLUNAS')) {
                        $invoice->TGLLUNAS = $request->tglbayar;
                    }
                }

                $invoice->save();
            }

            $accountsPayableCoaId = AccountMapping::getCoaId('accounts_payable');
            $paymentCoaId = $this->resolvePaymentCoaId($request);

            if (! $accountsPayableCoaId || ! $paymentCoaId) {
                throw new \RuntimeException('Mapping akun hutang atau kas/bank belum tersedia.');
            }

            $journal = $this->journalService->createJournal(
                $this->journalService->generateJournalNumber('AP', $request->tglbayar),
                $request->tglbayar,
                'Pelunasan Hutang: ' . $supplierLabel . ' - ' . $nobayar,
                [
                    [
                        'coa_id' => $accountsPayableCoaId,
                        'deskripsi' => 'Pelunasan hutang kepada ' . $supplierLabel . ' (No. Bayar: ' . $nobayar . ')',
                        'debit' => $totalBayar,
                        'kredit' => 0,
                    ],
                    [
                        'coa_id' => $paymentCoaId,
                        'deskripsi' => 'Pembayaran hutang kepada ' . $supplierLabel . ' (No. Bayar: ' . $nobayar . ')',
                        'debit' => 0,
                        'kredit' => $totalBayar,
                    ],
                ],
                $nobayar,
                auth()->id(),
                'Pelunasan Hutang',
                'Pengeluaran Kas'
            );

            $header->JUMLAH_BAYAR = $totalBayar;
            $header->PAYMENT_COA_ID = $paymentCoaId;
            $header->NOJURNAL = $journal->no_jurnal;
            $header->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pelunasan hutang berhasil disimpan dan dijurnal.',
                'nobayar' => $nobayar,
                'nojurnal' => $journal->no_jurnal,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function supplierInvoices(Request $request)
    {
        $rows = Aph::where('SUPPLIER', $request->supplier)
            ->where('SALDO', '>', 0)
            ->orderBy('TGLFAKTUR')
            ->get()
            ->map(fn ($row) => [
                'NOFAKTUR' => $row->NOFAKTUR,
                'TGLFAKTUR' => $row->TGLFAKTUR ? $row->TGLFAKTUR->format('d/m/Y') : '',
                'TGLJT' => $row->TGLJT ? $row->TGLJT->format('d/m/Y') : '',
                'SALDO' => $row->SALDO,
            ]);

        return response()->json($rows);
    }

    public function edit($id): View
    {
        $payment = Baph::with(['details.invoice', 'supplierMaster'])->findOrFail($id);
        $this->ensureEditable($payment);

        $paymentAccounts = $this->paymentAccounts();
        $editInvoices = $payment->details->map(function (Bapd $detail) {
            $invoice = $detail->invoice;
            $saldo = (float) ($invoice?->SALDO ?? 0) + (float) $detail->NILAI_BAYAR;

            return [
                'NOFAKTUR' => $detail->NOFAKTUR,
                'TGLFAKTUR' => $detail->TGLFAKTUR?->format('d/m/Y') ?? '',
                'TGLJT' => $invoice?->TGLJT?->format('d/m/Y') ?? '',
                'SALDO' => $saldo,
                'BAYAR' => (float) $detail->NILAI_BAYAR,
            ];
        })->values();

        return view('pelunasan-hutang.create', compact('payment', 'paymentAccounts', 'editInvoices'));
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'supplier' => strtoupper(trim((string) $request->input('supplier'))),
        ]);

        $validator = Validator::make($request->all(), $this->paymentValidationRules());
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        try {
            DB::transaction(function () use ($request, $id) {
                $payment = Baph::query()->whereKey($id)->lockForUpdate()->firstOrFail();
                $this->ensureEditable($payment);

                if ($this->periodLockService->isLocked($payment->TGLBAYAR) || $this->periodLockService->isLocked($request->tglbayar)) {
                    throw new \RuntimeException($this->periodLockService->lockedMessage($request->tglbayar));
                }

                $oldDetails = Bapd::query()->where('NOBAYAR', $payment->NOBAYAR)->get();
                foreach ($oldDetails as $detail) {
                    $invoice = Aph::query()->where('NOFAKTUR', $detail->NOFAKTUR)->lockForUpdate()->firstOrFail();
                    $invoice->BAYAR = max(0, (float) $invoice->BAYAR - (float) $detail->NILAI_BAYAR);
                    $invoice->SALDO = min((float) $invoice->HUTANG, (float) $invoice->SALDO + (float) $detail->NILAI_BAYAR);
                    if (Schema::hasColumn('aph', 'STATUS')) $invoice->STATUS = $invoice->SALDO <= 0 ? 'LUNAS' : 'OPEN';
                    if (Schema::hasColumn('aph', 'TGLLUNAS') && $invoice->SALDO > 0) $invoice->TGLLUNAS = null;
                    $invoice->save();
                }

                Bapd::query()->where('NOBAYAR', $payment->NOBAYAR)->delete();

                $details = collect($request->details)->filter(fn ($row) => (float) ($row['bayar'] ?? 0) > 0)->values();
                if ($details->isEmpty()) throw new \InvalidArgumentException('Belum ada pembayaran yang valid.');

                $totalBayar = 0;
                foreach ($details as $row) {
                    $invoice = Aph::query()->where('NOFAKTUR', $row['nofaktur'])->lockForUpdate()->firstOrFail();
                    if (trim((string) $invoice->SUPPLIER) !== $request->supplier) {
                        throw new \InvalidArgumentException("Faktur {$row['nofaktur']} bukan milik supplier ".$this->supplierLabel($request->supplier).'.');
                    }
                    $nilaiBayar = (float) $row['bayar'];
                    if ($nilaiBayar > (float) $invoice->SALDO) throw new \InvalidArgumentException("Pembayaran faktur {$invoice->NOFAKTUR} melebihi sisa hutang.");
                    $sisa = (float) $invoice->SALDO - $nilaiBayar;
                    $totalBayar += $nilaiBayar;

                    Bapd::create(['NOBAYAR' => $payment->NOBAYAR, 'NOFAKTUR' => $invoice->NOFAKTUR, 'TGLFAKTUR' => $invoice->TGLFAKTUR, 'SALDO_FAKTUR' => $invoice->SALDO, 'NILAI_BAYAR' => $nilaiBayar, 'DISCOUNT' => 0, 'RETUR' => 0, 'SISA_FAKTUR' => $sisa]);

                    $invoice->BAYAR = (float) $invoice->BAYAR + $nilaiBayar;
                    $invoice->SALDO = max(0, $sisa);
                    if (Schema::hasColumn('aph', 'STATUS')) $invoice->STATUS = $invoice->SALDO <= 0 ? 'LUNAS' : 'OPEN';
                    if (Schema::hasColumn('aph', 'TGLLUNAS')) $invoice->TGLLUNAS = $invoice->SALDO <= 0 ? $request->tglbayar : null;
                    $invoice->save();
                }

                $paymentCoaId = $this->resolvePaymentCoaId($request);
                $accountsPayableCoaId = AccountMapping::getCoaId('accounts_payable');
                if (! $accountsPayableCoaId || ! $paymentCoaId) throw new \RuntimeException('Mapping akun hutang atau kas/bank belum tersedia.');

                $journal = Jurnal::query()->where('no_jurnal', $payment->NOJURNAL)->lockForUpdate()->firstOrFail();
                $journalNumber = $journal->tanggal?->toDateString() === $request->tglbayar
                    ? $journal->no_jurnal
                    : $this->journalService->generateJournalNumber('AP', $request->tglbayar);
                $supplierLabel = $this->supplierLabel($request->supplier);
                $journal->update(['no_jurnal' => $journalNumber, 'tanggal' => $request->tglbayar, 'keterangan' => 'Pelunasan Hutang: '.$supplierLabel.' - '.$payment->NOBAYAR, 'referensi' => $payment->NOBAYAR, 'total_debit' => $totalBayar, 'total_kredit' => $totalBayar]);
                $journal->items()->delete();
                $journal->items()->createMany([
                    ['coa_id' => $accountsPayableCoaId, 'deskripsi' => 'Pelunasan hutang kepada '.$supplierLabel.' (No. Bayar: '.$payment->NOBAYAR.')', 'debit' => $totalBayar, 'kredit' => 0],
                    ['coa_id' => $paymentCoaId, 'deskripsi' => 'Pembayaran hutang kepada '.$supplierLabel.' (No. Bayar: '.$payment->NOBAYAR.')', 'debit' => 0, 'kredit' => $totalBayar],
                ]);

                $payment->update(['TGLBAYAR' => $request->tglbayar, 'SUPPLIER' => $request->supplier, 'JUMLAH_BAYAR' => $totalBayar, 'METODE_PEMBAYARAN' => $request->jenis_bayar, 'PAYMENT_COA_ID' => $paymentCoaId, 'NO_REFERENSI' => $request->no_referensi, 'KETERANGAN' => $request->keterangan, 'NOJURNAL' => $journalNumber, 'USER_UPDATE' => auth()->user()?->name ?? 'System']);
            });

            return response()->json(['success' => true, 'message' => 'Pelunasan hutang berhasil diperbarui.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function searchSupplier(Request $request)
    {
        $q = trim((string) $request->q);

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        if (! Schema::hasTable('msupplier')) {
            $rows = Aph::query()
                ->select('SUPPLIER')
                ->where('SUPPLIER', 'like', "%{$q}%")
                ->distinct()
                ->orderBy('SUPPLIER')
                ->limit(20)
                ->get();

            return response()->json($rows->map(fn ($row) => [
                'supplier' => $row->SUPPLIER,
                'label' => $row->SUPPLIER,
            ]));
        }

        $rows = Msupplier::query()
            ->where('SUPPLIER', 'like', "%{$q}%")
            ->orWhere('NAMA', 'like', "%{$q}%")
            ->orderBy('SUPPLIER')
            ->limit(20)
            ->get(['SUPPLIER', 'NAMA']);

        return response()->json($rows->map(function (Msupplier $row) {
            $supplier = trim((string) $row->SUPPLIER);
            $name = trim((string) $row->NAMA);

            return [
                'supplier' => $supplier,
                'value' => $supplier,
                'nama' => $name,
                'label' => $supplier.' - '.($name ?: 'Tanpa Nama'),
            ];
        }));
    }

    public function show($id): View
    {
        $baph = Baph::with('paymentCoa', 'supplierMaster')->findOrFail($id);
        $bapd = $baph->details()->with('invoice')->orderBy('TGLFAKTUR')->get();
        $journal = $baph->NOJURNAL ? Jurnal::query()->where('no_jurnal', $baph->NOJURNAL)->with('items.coa')->first() : null;
        $payableCoaId = AccountMapping::getCoaId('accounts_payable');
        $payableDebit = $journal && $payableCoaId ? (float) $journal->items->where('coa_id', $payableCoaId)->sum('debit') : 0.0;
        $journalChecks = [
            'exists' => (bool) $journal,
            'balanced' => $journal ? abs((float) $journal->total_debit - (float) $journal->total_kredit) <= 0.01 : false,
            'reference_matches' => $journal ? (string) $journal->referensi === (string) $baph->NOBAYAR : false,
            'amount_matches' => $journal && $payableCoaId ? abs($payableDebit - (float) $baph->JUMLAH_BAYAR) <= 0.01 : false,
            'mapping_exists' => (bool) $payableCoaId,
        ];
        $journalValid = $journalChecks['exists'] && $journalChecks['balanced'] && $journalChecks['reference_matches'] && $journalChecks['amount_matches'];

        return view('pelunasan-hutang.show', compact('baph', 'bapd', 'journal', 'journalChecks', 'journalValid'));
    }

    public function destroy($id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id): void {
                $payment = Baph::query()->whereKey($id)->lockForUpdate()->firstOrFail();
                $this->ensureEditable($payment);

                $details = Bapd::query()->where('NOBAYAR', $payment->NOBAYAR)->get();
                foreach ($details as $detail) {
                    $invoice = Aph::query()->where('NOFAKTUR', $detail->NOFAKTUR)->lockForUpdate()->firstOrFail();
                    $invoice->BAYAR = max(0, (float) $invoice->BAYAR - (float) $detail->NILAI_BAYAR);
                    $invoice->SALDO = min((float) $invoice->HUTANG, (float) $invoice->SALDO + (float) $detail->NILAI_BAYAR);
                    if (Schema::hasColumn('aph', 'STATUS')) $invoice->STATUS = $invoice->SALDO <= 0 ? 'LUNAS' : 'OPEN';
                    if (Schema::hasColumn('aph', 'TGLLUNAS') && $invoice->SALDO > 0) $invoice->TGLLUNAS = null;
                    $invoice->save();
                }

                Bapd::query()->where('NOBAYAR', $payment->NOBAYAR)->delete();
                if ($payment->NOJURNAL) {
                    $journal = Jurnal::query()->where('no_jurnal', $payment->NOJURNAL)->with('items')->lockForUpdate()->first();
                    if ($journal) {
                        $journal->items()->delete();
                        $journal->delete();
                    }
                }
                app(AuditLogService::class)->record('delete', 'pelunasan-hutang', $payment, 'Menghapus pelunasan hutang '.$payment->NOBAYAR.'.', [
                    'nobayar' => $payment->NOBAYAR,
                    'supplier' => $payment->SUPPLIER,
                    'tanggal' => $payment->TGLBAYAR?->toDateString(),
                    'jumlah_bayar' => (float) $payment->JUMLAH_BAYAR,
                    'nojurnal' => $payment->NOJURNAL,
                    'details' => $details->map(fn (Bapd $detail) => [
                        'nofaktur' => $detail->NOFAKTUR,
                        'nilai_bayar' => (float) $detail->NILAI_BAYAR,
                    ])->values()->all(),
                ]);
                $payment->delete();
            });

            return redirect()->route('pelunasan-hutang.index')->with('success', 'Pelunasan hutang berhasil dihapus dan saldo faktur dikembalikan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Hapus pelunasan gagal: '.$e->getMessage());
        }
    }

    public function void(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:1000']]);
        try {
            $payment = Baph::findOrFail($id);
            $this->paymentVoidService->payable($payment, $validated['reason']);
            return redirect()->route('pelunasan-hutang.show', $payment)->with('success', 'Pelunasan hutang dibatalkan dan jurnal pembalik berhasil dibuat.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Pembatalan gagal: '.$e->getMessage());
        }
    }

    private function generateNoBayar(): string
    {
        $prefix = 'AP' . now()->format('ym');
        $last = Baph::where('NOBAYAR', 'like', $prefix . '%')
            ->orderBy('NOBAYAR', 'desc')
            ->first();

        $urut = $last ? ((int) substr($last->NOBAYAR, -5)) + 1 : 1;

        return $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);
    }

    private function paymentAccounts()
    {
        return Coa::query()
            ->where('tipe', 'Aset')
            ->where(function ($query) {
                $query->where('nama_akun', 'like', '%Kas%')
                    ->orWhere('nama_akun', 'like', '%Bank%');
            })
            ->orderBy('kode_akun')
            ->get(['id', 'kode_akun', 'nama_akun']);
    }

    private function resolvePaymentCoaId(Request $request): ?int
    {
        if ($request->filled('payment_coa_id')) {
            return (int) $request->payment_coa_id;
        }

        return $request->jenis_bayar === 'CASH'
            ? AccountMapping::getCoaId('cash_account')
            : AccountMapping::getCoaId('bank_account');
    }

    private function supplierValidationRules(): array
    {
        $rules = ['required', 'string', 'max:100'];

        if (Schema::hasTable('msupplier')) {
            $rules[] = Rule::exists('msupplier', 'SUPPLIER');
        }

        return $rules;
    }

    private function paymentValidationRules(): array
    {
        return [
            'tglbayar' => 'required|date',
            'supplier' => $this->supplierValidationRules(),
            'jenis_bayar' => 'required|string|in:CASH,TRANSFER,GIRO,CEK,QRIS',
            'payment_coa_id' => 'nullable|integer|exists:coas,id',
            'no_referensi' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.nofaktur' => 'required|string|max:50',
            'details.*.bayar' => 'required|numeric|min:0.01',
        ];
    }

    private function ensureEditable(Baph $payment): void
    {
        abort_if($payment->STATUS === 'VOID', 422, 'Pelunasan yang sudah dibatalkan tidak dapat diedit.');
        abort_if($this->periodLockService->isLocked($payment->TGLBAYAR), 422, $this->periodLockService->lockedMessage($payment->TGLBAYAR));
    }

    private function supplierLabel(string $supplierCode): string
    {
        if (! Schema::hasTable('msupplier')) {
            return $supplierCode;
        }

        $supplier = Msupplier::find($supplierCode);

        return $supplier?->display_name ?? $supplierCode;
    }

    private function newPaymentToken(string $sessionKey): string
    {
        $token = (string) Str::uuid();
        $tokens = session($sessionKey, []);
        $tokens[] = $token;
        session([$sessionKey => array_slice(array_unique($tokens), -10)]);

        return $token;
    }

    private function consumePaymentToken(string $token, string $sessionKey): bool
    {
        if ($token === '') {
            return false;
        }

        $tokens = session($sessionKey, []);
        $index = array_search($token, $tokens, true);

        if ($index === false) {
            return false;
        }

        unset($tokens[$index]);
        session([$sessionKey => array_values($tokens)]);

        return true;
    }
}
