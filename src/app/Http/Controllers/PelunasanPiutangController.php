<?php

namespace App\Http\Controllers;

use App\Models\Arh;
use App\Models\Barh;
use App\Models\Bard;
use App\Models\AccountMapping;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\YearClosing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Services\JournalService;
use App\Services\PaymentFormTokenService;
use App\Services\PaymentVoidService;
use App\Services\PeriodLockService;
use App\Exports\ReceivablePaymentReceiptExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;


class PelunasanPiutangController extends Controller
{
    protected JournalService $journalService;

    private const PAYMENT_TOKEN_SCOPE = 'receivable_payment_form_tokens';

    public function __construct(
        JournalService $journalService,
        private readonly PaymentVoidService $paymentVoidService,
        private readonly PeriodLockService $periodLockService,
        private readonly PaymentFormTokenService $paymentFormTokenService,
    )
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $customer = trim((string) $request->input('customer'));

        $data = Barh::query()
            ->leftJoin('mcustomer', 'mcustomer.CUSTOMER', '=', 'barh.CUSTOMER')
            ->leftJoin('jurnals as reversal_journals', 'reversal_journals.no_jurnal', '=', 'barh.NOJURNAL_PEMBALIK')
            ->select('barh.*', 'mcustomer.NAMACUST as CUSTOMER_NAME')
            ->addSelect('reversal_journals.id as REVERSAL_JOURNAL_ID')
            ->when($customer !== '', fn ($query) => $query->where('barh.CUSTOMER', $customer))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($filter) use ($search) {
                    $filter->where('barh.NOBAYAR', 'like', "%{$search}%")
                        ->orWhere('barh.NO_REFERENSI', 'like', "%{$search}%")
                        ->orWhere('barh.NOJURNAL_PEMBALIK', 'like', "%{$search}%")
                        ->orWhereExists(function ($detail) use ($search) {
                            $detail->selectRaw('1')
                                ->from('bard')
                                ->whereColumn('bard.NOBAYAR', 'barh.NOBAYAR')
                                ->where('bard.NOFAKTUR', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('barh.created_at')
            ->paginate(20)
            ->withQueryString();
        $closedYears = YearClosing::query()->whereNotNull('closed_at')->pluck('year')->map(fn ($year) => (string) $year)->all();

        return view('pelunasan-piutang.index', compact('data', 'closedYears', 'search', 'customer'));
    }

    public function create()
    {
        $paymentAccounts = $this->paymentAccounts();
        $pphAccounts = $this->adjustmentAccounts('Aset');
        $discountAccounts = $this->adjustmentAccounts('Beban');
        $bankAdminAccounts = $discountAccounts;
        $defaultTransferAccountId = AccountMapping::getCoaId('receivable_transfer_bank') ?? $this->coaIdByCode('01010201');
        $defaultCashAccountId = AccountMapping::getCoaId('cash_account');
        $defaultBankAccountId = AccountMapping::getCoaId('bank_account');
        $defaultPph23AccountId = AccountMapping::getCoaId('receivable_pph23') ?? $this->coaIdByCode('01010502');
        $defaultDiscountAccountId = AccountMapping::getCoaId('receivable_payment_loss') ?? $this->coaIdByCode('05040103');
        $defaultBankAdminAccountId = AccountMapping::getCoaId('receivable_bank_admin') ?? $this->coaIdByCode('05040102');
        $paymentToken = $this->paymentFormTokenService->issue(self::PAYMENT_TOKEN_SCOPE);

        return view('pelunasan-piutang.create', compact(
            'paymentAccounts',
            'pphAccounts',
            'discountAccounts',
            'bankAdminAccounts',
            'defaultTransferAccountId',
            'defaultCashAccountId',
            'defaultBankAccountId',
            'defaultPph23AccountId',
            'defaultDiscountAccountId',
            'defaultBankAdminAccountId',
            'paymentToken',
        ));
    }
    public function store(Request $request)
    {
        $tokenConsumed = false;
        $paymentToken = (string) $request->input('_payment_token');

        $request->merge([
            'customer' => $this->normalizeCustomerCode($request->input('customer')),
        ]);

        $validator = Validator::make($request->all(), [
            'tglbayar' => 'required|date',
            'customer' => 'required|string|max:30|exists:mcustomer,CUSTOMER',
            'jenis_bayar' => 'required|string|in:CASH,TRANSFER,GIRO,CEK,QRIS',
            'payment_coa_id' => 'nullable|integer|exists:coas,id',
            'pph23_coa_id' => 'nullable|integer|exists:coas,id',
            'discount_coa_id' => 'nullable|integer|exists:coas,id',
            'bank_admin_coa_id' => 'nullable|integer|exists:coas,id',
            'no_referensi' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.nofaktur' => 'required|string|max:50',
            'details.*.bayar' => 'required|numeric|min:0',
            'details.*.pph23' => 'nullable|numeric|min:0',
            'details.*.discount' => 'nullable|numeric|min:0',
            'details.*.bank_admin' => 'nullable|numeric|min:0',
        ], [
            'customer.exists' => 'Customer tidak ditemukan. Pilih customer dari daftar pencarian.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($this->periodLockService->isLocked($request->tglbayar)) {
            return response()->json([
                'success' => false,
                'message' => $this->periodLockService->lockedMessage($request->tglbayar),
            ], 422);
        }

        if (! $this->paymentFormTokenService->consume(self::PAYMENT_TOKEN_SCOPE, $paymentToken)) {
            $existing = $this->paymentFormTokenService->findSuccess(self::PAYMENT_TOKEN_SCOPE, $paymentToken);
            if ($existing) {
                return response()->json($existing);
            }

            return response()->json([
                'success' => false,
                'message' => 'Token formulir tidak valid atau sudah dipakai. Muat ulang halaman input, lalu cek daftar pelunasan sebelum input ulang.',
                'reload' => true,
            ], 409);
        }
        $tokenConsumed = true;

        DB::beginTransaction();

        try {
            $details = collect($request->details)
                ->filter(fn ($row) => (float) ($row['bayar'] ?? 0) + (float) ($row['pph23'] ?? 0) + (float) ($row['discount'] ?? 0) + (float) ($row['bank_admin'] ?? 0) > 0)
                ->values();

            if ($details->isEmpty()) {
                throw new \InvalidArgumentException('Belum ada pembayaran yang valid.');
            }

            $totalBayar = 0;
            $totalPph23 = $details->sum(fn ($row) => (float) ($row['pph23'] ?? 0));
            $totalDiscount = $details->sum(fn ($row) => (float) ($row['discount'] ?? 0));
            $totalBankAdmin = $details->sum(fn ($row) => (float) ($row['bank_admin'] ?? 0));

            if ($totalPph23 > 0 && ! $request->filled('pph23_coa_id')) {
                throw new \InvalidArgumentException('Pilih akun PPh Pasal 23 karena terdapat nilai PPh 23.');
            }

            if ($totalDiscount > 0 && ! $request->filled('discount_coa_id')) {
                throw new \InvalidArgumentException('Pilih akun Kerugian Pembayaran Piutang karena terdapat nilai potongan.');
            }

            if ($totalBankAdmin > 0 && ! $request->filled('bank_admin_coa_id')) {
                throw new \InvalidArgumentException('Pilih akun beban administrasi bank karena terdapat nilai administrasi bank.');
            }

            if ($details->sum(fn ($row) => (float) ($row['bayar'] ?? 0)) <= 0) {
                throw new \InvalidArgumentException('Uang yang diterima harus lebih dari nol.');
            }

            $nobayar = $this->generateNoBayar(); // This is for BARH NOBAYAR, not journal number

            $header = Barh::create([
                'NOBAYAR'             => $nobayar,
                'TGLBAYAR'            => $request->tglbayar,
                'CUSTOMER'            => $request->customer,
                'METODE_PEMBAYARAN'   => $request->jenis_bayar,
                'PAYMENT_COA_ID'      => $request->filled('payment_coa_id') ? (int) $request->payment_coa_id : null,
                'PPH23_COA_ID'        => $request->filled('pph23_coa_id') ? (int) $request->pph23_coa_id : null,
                'DISCOUNT_COA_ID'     => $request->filled('discount_coa_id') ? (int) $request->discount_coa_id : null,
                'BANK_ADMIN_COA_ID'   => $request->filled('bank_admin_coa_id') ? (int) $request->bank_admin_coa_id : null,
                'NO_REFERENSI'        => $request->no_referensi,
                'JUMLAH_BAYAR'        => 0,
                'CABANG'              => '01',
                'KETERANGAN'          => $request->keterangan,
                'USER'                => auth()->user()->name ?? 'SYSTEM',
                // NOJURNAL will be set after journal creation
                'NOJURNAL'            => null,
            ]);

            foreach ($details as $row) {
                $invoice = Arh::where(
                    'NOFAKTUR',
                    $row['nofaktur']
                )->lockForUpdate()->first();

                if (!$invoice) {
                    throw new \InvalidArgumentException("Faktur {$row['nofaktur']} tidak ditemukan.");
                }

                if ($this->normalizeCustomerCode($invoice->CUSTOMER) !== $request->customer) {
                    throw new \InvalidArgumentException("Faktur {$row['nofaktur']} milik customer {$invoice->CUSTOMER}, bukan {$request->customer}. Pilih customer sesuai faktur lalu klik Cari Faktur ulang.");
                }

                $nilaiBayar = (float) $row['bayar'];
                $nilaiPph23 = (float) ($row['pph23'] ?? 0);
                $nilaiDiscount = (float) ($row['discount'] ?? 0);
                $nilaiBankAdmin = (float) ($row['bank_admin'] ?? 0);
                $saldoFaktur = (float) $invoice->SALDO;
                $nilaiPelunasan = $nilaiBayar + $nilaiPph23 + $nilaiDiscount + $nilaiBankAdmin;

                if ($nilaiPelunasan > $saldoFaktur) {
                    throw new \InvalidArgumentException("Total bayar dan potongan faktur {$invoice->NOFAKTUR} melebihi sisa piutang.");
                }

                $sisa = $saldoFaktur - $nilaiPelunasan;
                $totalBayar += $nilaiBayar;

                Bard::create([
                    'NOBAYAR'       => $nobayar,
                    'NOFAKTUR'      => $invoice->NOFAKTUR,
                    'TGLFAKTUR'     => $invoice->TGLFAKTUR,
                    'SALDO_FAKTUR'  => $saldoFaktur,
                    'NILAI_BAYAR'   => $nilaiBayar,
                    'PPH23'         => $nilaiPph23,
                    'DISCOUNT'      => $nilaiDiscount,
                    'BANK_ADMIN'    => $nilaiBankAdmin,
                    'RETUR'         => 0,
                    'SISA_FAKTUR'   => $sisa,
                ]);

                $invoice->BAYAR =
                    $invoice->BAYAR + $nilaiBayar;

                $invoice->SALDO =
                    $sisa;

                if ($invoice->SALDO <= 0) {

                    $invoice->SALDO = 0;

                    if (Schema::hasColumn('arh', 'STATUS')) {
                        $invoice->STATUS = 'LUNAS';
                    }

                    if (Schema::hasColumn('arh', 'TGLLUNAS')) {
                        $invoice->TGLLUNAS =
                            $request->tglbayar;
                    }
                }

                $invoice->save();
            }

            $header->JUMLAH_BAYAR = $totalBayar;
            $header->save();

            // --- Generate Journal Entry ---
            $accountsReceivableCoaId = AccountMapping::getCoaId('accounts_receivable');
            $cashBankCoaId = $this->resolvePaymentCoaId($request);
            $customerLabel = $this->customerLabel($request->customer);

            if (!$accountsReceivableCoaId || !$cashBankCoaId) {
                throw new \Exception("COA mapping for Accounts Receivable or Cash/Bank not found.");
            }

            $journalItems = [];
            // Debit Cash/Bank
            $journalItems[] = [
                'coa_id' => $cashBankCoaId,
                'deskripsi' => 'Penerimaan kas dari ' . $customerLabel . ' (No. Bayar: ' . $nobayar . ')',
                'debit' => $totalBayar,
                'kredit' => 0,
            ];
            if ($totalPph23 > 0) {
                $journalItems[] = [
                    'coa_id' => (int) $request->pph23_coa_id,
                    'deskripsi' => 'PPh Pasal 23 dipotong customer ' . $customerLabel . ' (No. Bayar: ' . $nobayar . ')',
                    'debit' => $totalPph23,
                    'kredit' => 0,
                ];
            }
            if ($totalDiscount > 0) {
                $journalItems[] = [
                    'coa_id' => (int) $request->discount_coa_id,
                    'deskripsi' => 'Kerugian pembayaran piutang ' . $customerLabel . ' (No. Bayar: ' . $nobayar . ')',
                    'debit' => $totalDiscount,
                    'kredit' => 0,
                ];
            }
            if ($totalBankAdmin > 0) {
                $journalItems[] = [
                    'coa_id' => (int) $request->bank_admin_coa_id,
                    'deskripsi' => 'Beban administrasi bank pelunasan piutang ' . $customerLabel . ' (No. Bayar: ' . $nobayar . ')',
                    'debit' => $totalBankAdmin,
                    'kredit' => 0,
                ];
            }
            // Kredit Accounts Receivable
            $journalItems[] = [
                'coa_id' => $accountsReceivableCoaId,
                'deskripsi' => 'Pelunasan piutang dari ' . $customerLabel . ' (No. Bayar: ' . $nobayar . ')',
                'debit' => 0,
                'kredit' => $totalBayar + $totalPph23 + $totalDiscount + $totalBankAdmin,
            ];

            $journal = $this->journalService->createJournal(
                $this->journalService->generateJournalNumber('AR', $request->tglbayar), // Use AR for Accounts Receivable payment
                $request->tglbayar,
                'Pelunasan Piutang: ' . $customerLabel . ' - ' . $nobayar,
                $journalItems,
                $nobayar, // Reference to the payment number
                auth()->user()->id ?? null,
                'Pelunasan Piutang',
                'Pelunasan Piutang'
            );

            // Link the journal to BARH
            $header->PAYMENT_COA_ID = $cashBankCoaId;
            $header->NOJURNAL = $journal->no_jurnal;
            $header->save();

            DB::commit();

            $payload = [
                'success' => true,
                'message' => 'Pelunasan berhasil disimpan dan dijurnal.',
                'nobayar' => $nobayar,
                'nojurnal' => $journal->no_jurnal,
                'receipt_url' => route('pelunasan-piutang.show', $header->id),
            ];

            $this->paymentFormTokenService->rememberSuccess(self::PAYMENT_TOKEN_SCOPE, $paymentToken, $payload);

            return response()->json($payload);
        } catch (\Exception $e) {

            DB::rollBack();
            if ($tokenConsumed) {
                $this->paymentFormTokenService->restore(self::PAYMENT_TOKEN_SCOPE, $paymentToken);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateNoBayar()
    {
        $prefix = 'AR' . now()->format('ym');

        $last = Barh::where(
            'NOBAYAR',
            'like',
            $prefix . '%'
        )
            ->orderBy('NOBAYAR', 'desc')
            ->first();

        if ($last) {

            $urut =
                (int) substr($last->NOBAYAR, -5) + 1;
        } else {

            $urut = 1;
        }

        return $prefix .
            str_pad($urut, 5, '0', STR_PAD_LEFT);
    }


    public function searchCustomer(Request $request)
    {
        $q = trim((string) $request->q);
        $isModal = $request->boolean('modal');

        if (! $isModal && strlen($q) < 2) {
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
            ->when(! $isModal, fn ($query) => $query->limit(20))
            ->get();

        return response()->json(
            $rows->map(function ($row) {
                $customer = trim((string) $row->CUSTOMER);
                $name = trim((string) $row->NAMACUST);

                return [
                    'customer' => $customer,
                    'nama' => $name,
                    'label' => $customer . ' - ' . $name,
                ];
            })
        );
    }

    public function customerInvoices(Request $request)
    {
        $customer = $request->customer;

        $rows = Arh::where('CUSTOMER', $customer)
            ->where('SALDO', '>', 0)
            ->orderBy('TGLFAKTUR')
            ->get()
            ->map(function ($row) {

                return [
                    'NOFAKTUR' => $row->NOFAKTUR,
                    'TGLFAKTUR' => $row->TGLFAKTUR
                        ? $row->TGLFAKTUR->format('d/m/Y')
                        : '',

                    'TGLJT' => $row->TGLJT
                        ? $row->TGLJT->format('d/m/Y')
                        : '',

                    'SALDO' => $row->SALDO,
                ];
            });

        return response()->json($rows);
    }

    public function show($id)
    {
        $barh = Barh::query()
            ->leftJoin('mcustomer', 'mcustomer.CUSTOMER', '=', 'barh.CUSTOMER')
            ->select('barh.*', 'mcustomer.NAMACUST as CUSTOMER_NAME')
            ->where('barh.id', $id)
            ->firstOrFail();
        $bard = Bard::where('NOBAYAR', $barh->NOBAYAR)->get();
        $isLocked = $this->periodLockService->isLocked($barh->TGLBAYAR);
        $journal = $barh->NOJURNAL ? Jurnal::query()->where('no_jurnal', $barh->NOJURNAL)->with('items.coa')->first() : null;
        $reversalJournal = $barh->NOJURNAL_PEMBALIK ? Jurnal::query()->where('no_jurnal', $barh->NOJURNAL_PEMBALIK)->first() : null;
        $receivableCoaId = AccountMapping::getCoaId('accounts_receivable');
        $settled = (float) $barh->JUMLAH_BAYAR + (float) $bard->sum('PPH23') + (float) $bard->sum('DISCOUNT') + (float) $bard->sum('BANK_ADMIN') + (float) $bard->sum('RETUR');
        $receivableCredit = $journal && $receivableCoaId ? (float) $journal->items->where('coa_id', $receivableCoaId)->sum('kredit') : 0.0;
        $journalChecks = [
            'exists' => (bool) $journal,
            'balanced' => $journal ? abs((float) $journal->total_debit - (float) $journal->total_kredit) <= 0.01 : false,
            'reference_matches' => $journal ? (string) $journal->referensi === (string) $barh->NOBAYAR : false,
            'amount_matches' => $journal && $receivableCoaId ? abs($receivableCredit - $settled) <= 0.01 : false,
            'mapping_exists' => (bool) $receivableCoaId,
        ];
        $journalValid = $journalChecks['exists'] && $journalChecks['balanced'] && $journalChecks['reference_matches'] && $journalChecks['amount_matches'];

        $printMode = request()->boolean('print');
        return view('pelunasan-piutang.show', compact('barh', 'bard', 'isLocked', 'printMode', 'journal', 'reversalJournal', 'journalChecks', 'journalValid'));
    }

    public function exportReceipt($id)
    {
        $barh = Barh::query()->leftJoin('mcustomer','mcustomer.CUSTOMER','=','barh.CUSTOMER')
            ->select('barh.*','mcustomer.NAMACUST as CUSTOMER_NAME')->where('barh.id',$id)->firstOrFail();
        $bard = Bard::where('NOBAYAR',$barh->NOBAYAR)->orderBy('NOFAKTUR')->get();
        return Excel::download(new ReceivablePaymentReceiptExport($barh,$bard),'bukti_pelunasan_'.$barh->NOBAYAR.'.xlsx');
    }

    public function receiptPdf(Request $request, $id)
    {
        $barh = Barh::query()
            ->leftJoin('mcustomer', 'mcustomer.CUSTOMER', '=', 'barh.CUSTOMER')
            ->select('barh.*', 'mcustomer.NAMACUST as CUSTOMER_NAME')
            ->where('barh.id', $id)
            ->firstOrFail();
        $bard = Bard::where('NOBAYAR', $barh->NOBAYAR)
            ->orderBy('NOFAKTUR')
            ->get([
                'NOFAKTUR',
                'TGLFAKTUR',
                'SALDO_FAKTUR',
                'NILAI_BAYAR',
                'PPH23',
                'DISCOUNT',
                'BANK_ADMIN',
                'RETUR',
                'SISA_FAKTUR',
            ]);
        $pdf = Pdf::loadView('pelunasan-piutang.receipt_pdf', compact('barh', 'bard'))
            ->setPaper('letter', 'portrait')
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->stream('bukti_pelunasan_'.$barh->NOBAYAR.'.pdf');
    }

    public function edit($id)
    {
        $barh = Barh::findOrFail($id);
        abort_if($barh->STATUS === 'VOID', 422, 'Pelunasan yang sudah dibatalkan tidak dapat diedit.');
        if ($this->periodLockService->isLocked($barh->TGLBAYAR)) {
            return redirect()->route('pelunasan-piutang.show', $barh)
                ->with('error', $this->periodLockService->lockedMessage($barh->TGLBAYAR));
        }
        $bard = Bard::where('NOBAYAR', $barh->NOBAYAR)->get();
        $paymentAccounts = $this->paymentAccounts();
        $pphAccounts = $this->adjustmentAccounts('Aset');
        $discountAccounts = $this->adjustmentAccounts('Beban');
        $bankAdminAccounts = $discountAccounts;
        $customerName = DB::table('mcustomer')->where('CUSTOMER', $barh->CUSTOMER)->value('NAMACUST');

        return view('pelunasan-piutang.edit', compact(
            'barh', 'bard', 'paymentAccounts', 'pphAccounts', 'discountAccounts', 'bankAdminAccounts', 'customerName'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'customer' => $this->normalizeCustomerCode($request->input('customer')),
        ]);

        $validator = Validator::make($request->all(), [
            'tglbayar' => 'required|date',
            'customer' => 'required|string|max:30|exists:mcustomer,CUSTOMER',
            'jenis_bayar' => 'required|string|in:CASH,TRANSFER,GIRO,CEK,QRIS',
            'payment_coa_id' => 'nullable|integer|exists:coas,id',
            'pph23_coa_id' => 'nullable|integer|exists:coas,id',
            'discount_coa_id' => 'nullable|integer|exists:coas,id',
            'bank_admin_coa_id' => 'nullable|integer|exists:coas,id',
            'no_referensi' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.nofaktur' => 'required|string|max:50',
            'details.*.bayar' => 'required|numeric|min:0',
            'details.*.pph23' => 'nullable|numeric|min:0',
            'details.*.discount' => 'nullable|numeric|min:0',
            'details.*.bank_admin' => 'nullable|numeric|min:0',
        ], [
            'customer.exists' => 'Customer tidak ditemukan. Pilih customer dari daftar pencarian.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $barh = Barh::findOrFail($id);
        if ($barh->STATUS === 'VOID') return redirect()->route('pelunasan-piutang.show', $barh)->with('error', 'Pelunasan yang sudah dibatalkan tidak dapat diedit.');
        if ($this->periodLockService->isLocked($barh->TGLBAYAR)) {
            $message = $this->periodLockService->lockedMessage($barh->TGLBAYAR);
            return $request->expectsJson() ? response()->json(['success'=>false,'message'=>$message], 422) : redirect()->route('pelunasan-piutang.show', $barh)->with('error', $message);
        }
        if ($this->periodLockService->isLocked($request->tglbayar)) {
            $message = $this->periodLockService->lockedMessage($request->tglbayar);
            return $request->expectsJson() ? response()->json(['success'=>false,'message'=>$message], 422) : redirect()->route('pelunasan-piutang.show', $barh)->with('error', $message);
        }

        DB::beginTransaction();

        try {
            $details = collect($request->details)
                ->filter(fn ($row) => (float) ($row['bayar'] ?? 0) + (float) ($row['pph23'] ?? 0) + (float) ($row['discount'] ?? 0) + (float) ($row['bank_admin'] ?? 0) > 0)
                ->values();

            if ($details->isEmpty()) {
                throw new \InvalidArgumentException('Belum ada pembayaran yang valid.');
            }

            $totalPph23 = $details->sum(fn ($row) => (float) ($row['pph23'] ?? 0));
            $totalDiscount = $details->sum(fn ($row) => (float) ($row['discount'] ?? 0));
            $totalBankAdmin = $details->sum(fn ($row) => (float) ($row['bank_admin'] ?? 0));
            if ($details->sum(fn ($row) => (float) ($row['bayar'] ?? 0)) <= 0) throw new \InvalidArgumentException('Uang yang diterima harus lebih dari nol.');
            if ($totalPph23 > 0 && ! $request->filled('pph23_coa_id')) throw new \InvalidArgumentException('Pilih akun PPh Pasal 23.');
            if ($totalDiscount > 0 && ! $request->filled('discount_coa_id')) throw new \InvalidArgumentException('Pilih akun Kerugian Pembayaran Piutang.');
            if ($totalBankAdmin > 0 && ! $request->filled('bank_admin_coa_id')) throw new \InvalidArgumentException('Pilih akun Beban Administrasi Bank.');

            // Revert previous changes to ARH
            $oldBard = Bard::where('NOBAYAR', $barh->NOBAYAR)->get();
            foreach ($oldBard as $detail) {
                $invoice = Arh::where('NOFAKTUR', $detail->NOFAKTUR)->lockForUpdate()->first();
                if ($invoice) {
                    $invoice->BAYAR = $invoice->BAYAR - $detail->NILAI_BAYAR;
                    $oldSettled = (float) $detail->NILAI_BAYAR + (float) $detail->PPH23
                        + (float) $detail->DISCOUNT + (float) $detail->BANK_ADMIN + (float) $detail->RETUR;
                    $invoice->SALDO = min((float) $invoice->PIUTANG, (float) $invoice->SALDO + $oldSettled);
                    if (Schema::hasColumn('arh', 'STATUS')) {
                        $invoice->STATUS = $invoice->SALDO > 0 ? 'OPEN' : 'LUNAS';
                    }
                    if (Schema::hasColumn('arh', 'TGLLUNAS')) {
                        $invoice->TGLLUNAS = $invoice->SALDO > 0 ? null : $request->tglbayar;
                    }
                    $invoice->save();
                }
            }

            // Delete old details
            Bard::where('NOBAYAR', $barh->NOBAYAR)->delete();

            // Update header
            $barh->update([
                'TGLBAYAR' => $request->tglbayar,
                'CUSTOMER' => $request->customer,
                'METODE_PEMBAYARAN' => $request->jenis_bayar,
                'PAYMENT_COA_ID' => $request->filled('payment_coa_id') ? (int) $request->payment_coa_id : null,
                'PPH23_COA_ID' => $request->filled('pph23_coa_id') ? (int) $request->pph23_coa_id : null,
                'DISCOUNT_COA_ID' => $request->filled('discount_coa_id') ? (int) $request->discount_coa_id : null,
                'BANK_ADMIN_COA_ID' => $request->filled('bank_admin_coa_id') ? (int) $request->bank_admin_coa_id : null,
                'NO_REFERENSI' => $request->no_referensi,
                'JUMLAH_BAYAR' => 0,
                'KETERANGAN' => $request->keterangan,
                'USER_UPDATE' => auth()->user()->name ?? 'SYSTEM',
            ]);

            $totalBayar = 0;

            // Insert new details
            foreach ($details as $row) {
                $invoice = Arh::where('NOFAKTUR', $row['nofaktur'])->lockForUpdate()->first();
                if (!$invoice) {
                    throw new \InvalidArgumentException("Faktur {$row['nofaktur']} tidak ditemukan.");
                }

                if ($this->normalizeCustomerCode($invoice->CUSTOMER) !== $request->customer) {
                    throw new \InvalidArgumentException("Faktur {$row['nofaktur']} milik customer {$invoice->CUSTOMER}, bukan {$request->customer}. Pilih customer sesuai faktur lalu klik Cari Faktur ulang.");
                }

                $nilaiBayar = (float) $row['bayar'];
                $nilaiPph23 = (float) ($row['pph23'] ?? 0);
                $nilaiDiscount = (float) ($row['discount'] ?? 0);
                $nilaiBankAdmin = (float) ($row['bank_admin'] ?? 0);
                $saldoFaktur = (float) $invoice->SALDO;
                $nilaiPelunasan = $nilaiBayar + $nilaiPph23 + $nilaiDiscount + $nilaiBankAdmin;

                if ($nilaiPelunasan > $saldoFaktur) {
                    throw new \InvalidArgumentException("Total pembayaran faktur {$invoice->NOFAKTUR} melebihi sisa piutang.");
                }

                $sisa = $saldoFaktur - $nilaiPelunasan;
                $totalBayar += $nilaiBayar;

                Bard::create([
                    'NOBAYAR' => $barh->NOBAYAR,
                    'NOFAKTUR' => $invoice->NOFAKTUR,
                    'TGLFAKTUR' => $invoice->TGLFAKTUR,
                    'SALDO_FAKTUR' => $saldoFaktur,
                    'NILAI_BAYAR' => $nilaiBayar,
                    'PPH23' => $nilaiPph23,
                    'DISCOUNT' => $nilaiDiscount,
                    'BANK_ADMIN' => $nilaiBankAdmin,
                    'RETUR' => 0,
                    'SISA_FAKTUR' => $sisa,
                ]);

                $invoice->BAYAR = $invoice->BAYAR + $nilaiBayar;
                $invoice->SALDO = $sisa;

                if ($invoice->SALDO <= 0) {
                    $invoice->SALDO = 0;
                    if (Schema::hasColumn('arh', 'STATUS')) {
                        $invoice->STATUS = 'LUNAS';
                    }
                    if (Schema::hasColumn('arh', 'TGLLUNAS')) {
                        $invoice->TGLLUNAS = $request->tglbayar;
                    }
                }

                $invoice->save();
            }

            if ($barh->NOJURNAL) {
                Jurnal::where('no_jurnal', $barh->NOJURNAL)->delete();
            }

            $accountsReceivableCoaId = AccountMapping::getCoaId('accounts_receivable');
            $cashBankCoaId = $this->resolvePaymentCoaId($request);
            $customerLabel = $this->customerLabel($request->customer);

            if (!$accountsReceivableCoaId || !$cashBankCoaId) {
                throw new \Exception("COA mapping for Accounts Receivable or Cash/Bank not found.");
            }

            $journalItems = [[
                'coa_id' => $cashBankCoaId, 'deskripsi' => 'Penerimaan kas dari '.$customerLabel.' (No. Bayar: '.$barh->NOBAYAR.')',
                'debit' => $totalBayar, 'kredit' => 0,
            ]];
            if ($totalPph23 > 0) $journalItems[] = ['coa_id'=>(int)$request->pph23_coa_id,'deskripsi'=>'PPh Pasal 23 dipotong customer '.$customerLabel,'debit'=>$totalPph23,'kredit'=>0];
            if ($totalDiscount > 0) $journalItems[] = ['coa_id'=>(int)$request->discount_coa_id,'deskripsi'=>'Kerugian pembayaran piutang '.$customerLabel,'debit'=>$totalDiscount,'kredit'=>0];
            if ($totalBankAdmin > 0) $journalItems[] = ['coa_id'=>(int)$request->bank_admin_coa_id,'deskripsi'=>'Beban administrasi bank pelunasan piutang '.$customerLabel,'debit'=>$totalBankAdmin,'kredit'=>0];
            $journalItems[] = ['coa_id'=>$accountsReceivableCoaId,'deskripsi'=>'Pelunasan piutang dari '.$customerLabel.' (No. Bayar: '.$barh->NOBAYAR.')','debit'=>0,'kredit'=>$totalBayar+$totalPph23+$totalDiscount+$totalBankAdmin];

            $journal = $this->journalService->createJournal(
                $this->journalService->generateJournalNumber('AR', $request->tglbayar),
                $request->tglbayar,
                'Pelunasan Piutang: ' . $customerLabel . ' - ' . $barh->NOBAYAR,
                $journalItems,
                $barh->NOBAYAR,
                auth()->user()->id ?? null,
                'Pelunasan Piutang',
                'Pelunasan Piutang'
            );

            $barh->JUMLAH_BAYAR = $totalBayar;
            $barh->PAYMENT_COA_ID = $cashBankCoaId;
            $barh->NOJURNAL = $journal->no_jurnal;
            $barh->save();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pelunasan berhasil diupdate dan jurnal diperbarui.',
                    'redirect' => route('pelunasan-piutang.show', $barh->id),
                    'nojurnal' => $journal->no_jurnal,
                ]);
            }

            return redirect()->route('pelunasan-piutang.show', $barh->id)
                ->with('success', 'Pelunasan berhasil diupdate dan jurnal diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function void(Request $request, $id)
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:1000']]);
        try {
            $payment = Barh::findOrFail($id);
            $this->paymentVoidService->receivable($payment, $validated['reason']);
            return redirect()->route('pelunasan-piutang.show', $payment)->with('success', 'Pelunasan piutang dibatalkan dan jurnal pembalik berhasil dibuat.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Pembatalan gagal: '.$e->getMessage());
        }
    }

    private function paymentAccounts()
    {
        return Coa::query()
            ->where('is_active', true)
            ->where('level', 'Akun Posting')
            ->orderBy('kode_akun')
            ->get(['id', 'kode_akun', 'nama_akun']);
    }

    private function adjustmentAccounts(string $type)
    {
        return Coa::query()
            ->where('tipe', $type)
            ->where('is_active', true)
            ->orderBy('kode_akun')
            ->get(['id', 'kode_akun', 'nama_akun']);
    }

    private function coaIdByCode(string $code): ?int
    {
        $id = Coa::query()->where('kode_akun', $code)->value('id');

        return $id ? (int) $id : null;
    }

    private function resolvePaymentCoaId(Request $request): ?int
    {
        if ($request->filled('payment_coa_id')) {
            return (int) $request->payment_coa_id;
        }

        if ($request->jenis_bayar === 'CASH') {
            return AccountMapping::getCoaId('cash_account');
        }

        if ($request->jenis_bayar === 'TRANSFER') {
            return AccountMapping::getCoaId('receivable_transfer_bank')
                ?? $this->coaIdByCode('01010201')
                ?? AccountMapping::getCoaId('bank_account');
        }

        return AccountMapping::getCoaId('bank_account');
    }

    private function customerLabel(string $customerCode): string
    {
        $name = DB::table('mcustomer')->where('CUSTOMER', $customerCode)->value('NAMACUST');
        $name = trim((string) $name);

        return $name !== '' ? $name : $customerCode;
    }

    private function normalizeCustomerCode(mixed $customerCode): string
    {
        return strtoupper(trim((string) $customerCode));
    }

}
