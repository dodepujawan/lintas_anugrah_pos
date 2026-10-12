<?php

namespace App\Http\Controllers;

use App\Models\{AccountMapping, Coa, DriverReceivable, DriverReceivablePayment, Jurnal};
use App\Services\{AuditLogService, JournalService, PaymentFormTokenService, PeriodLockService};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DriverPaymentController extends Controller
{
    private const PAYMENT_TOKEN_SCOPE = 'driver_payment_form_tokens';

    public function __construct(
        private JournalService $journals,
        private PeriodLockService $locks,
        private PaymentFormTokenService $paymentFormTokens,
    ) {}

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        return view('driver-payments.index', [
            'rows' => DriverReceivablePayment::with(['receivable.driver', 'journal'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($sub) use ($search) {
                        $sub->where('number', 'like', "%{$search}%")
                            ->orWhere('method', 'like', "%{$search}%")
                            ->orWhere('reference', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('receivable', function ($receivable) use ($search) {
                                $receivable->where('number', 'like', "%{$search}%")
                                    ->orWhereHas('driver', function ($driver) use ($search) {
                                        $driver->where('code', 'like', "%{$search}%")
                                            ->orWhere('name', 'like', "%{$search}%");
                                    });
                            });
                    });
                })
                ->latest('date')
                ->paginate(20)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('driver-payments.form', [
            'receivables' => DriverReceivable::with('driver')->where('balance', '>', 0)->orderBy('due_date')->get(),
            'accounts' => Coa::active()->posting()->orderBy('kode_akun')->get(),
            'cashId' => AccountMapping::getCoaId('cash_account'),
            'paymentToken' => $this->paymentFormTokens->issue(self::PAYMENT_TOKEN_SCOPE),
        ]);
    }

    public function show(DriverReceivablePayment $driver_payment)
    {
        $payment = $driver_payment->load(['receivable.driver', 'journal.items.coa']);
        $journal = $payment->journal;
        $receivableCoaId = $payment->receivable?->receivable_coa_id;
        $receivableCredit = $journal && $receivableCoaId ? (float) $journal->items->where('coa_id', $receivableCoaId)->sum('kredit') : 0.0;
        $journalChecks = [
            'exists' => (bool) $journal,
            'balanced' => $journal ? abs((float) $journal->total_debit - (float) $journal->total_kredit) <= 0.01 : false,
            'reference_matches' => $journal ? (string) $journal->referensi === (string) $payment->number : false,
            'amount_matches' => $journal && $receivableCoaId ? abs($receivableCredit - (float) $payment->amount) <= 0.01 : false,
            'mapping_exists' => (bool) $receivableCoaId,
        ];
        $journalValid = $journalChecks['exists'] && $journalChecks['balanced'] && $journalChecks['reference_matches'] && $journalChecks['amount_matches'];

        return view('driver-payments.show', compact('payment', 'journal', 'journalChecks', 'journalValid'));
    }

    public function edit(DriverReceivablePayment $driver_payment)
    {
        $this->ensureEditable($driver_payment);
        return view('driver-payments.form', [
            'payment' => $driver_payment,
            'receivables' => DriverReceivable::with('driver')
                ->where(fn ($query) => $query->where('balance', '>', 0)->orWhere('id', $driver_payment->driver_receivable_id))
                ->orderBy('due_date')->get(),
            'accounts' => Coa::active()->posting()->orderBy('kode_akun')->get(),
            'cashId' => AccountMapping::getCoaId('cash_account'),
        ]);
    }

    public function receiptPdf(DriverReceivablePayment $driver_payment)
    {
        $payment = $driver_payment->load(['receivable.driver', 'journal.items.coa']);

        $pdf = Pdf::loadView('driver-payments.receipt_pdf', compact('payment'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('bukti_pelunasan_piutang_sopir_'.$payment->number.'.pdf');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'driver_receivable_id' => ['required', 'exists:driver_receivables,id'],
            'date' => ['required', 'date'],
            'method' => ['required', Rule::in(['CASH', 'TRANSFER', 'POTONG_GAJI', 'POTONG_UANG_JALAN'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_coa_id' => ['required', 'exists:coas,id'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($this->locks->isLocked($data['date'])) return back()->withInput()->with('error', $this->locks->lockedMessage($data['date']));

        $paymentToken = (string) $request->input('_payment_token');
        if (! $this->paymentFormTokens->consume(self::PAYMENT_TOKEN_SCOPE, $paymentToken)) {
            $existing = $this->paymentFormTokens->findSuccess(self::PAYMENT_TOKEN_SCOPE, $paymentToken);
            if ($existing) {
                return redirect()
                    ->route($existing['redirect_route'] ?? 'driver-payments.index')
                    ->with('success', $existing['message'] ?? 'Pelunasan dan jurnal berhasil disimpan.');
            }

            return redirect()
                ->route('driver-payments.index')
                ->with('error', 'Token formulir tidak valid atau sudah dipakai. Muat ulang halaman input, lalu cek daftar pelunasan sebelum input ulang.');
        }

        try {
            DB::transaction(function () use ($data) {
                $receivable = DriverReceivable::with('driver')->whereKey($data['driver_receivable_id'])->lockForUpdate()->firstOrFail();
                if ((float) $data['amount'] > (float) $receivable->balance) throw new \RuntimeException('Pembayaran melebihi saldo piutang.');
                $number = $this->generatePaymentNumber($data['date']);
                $driverLabel = $this->driverLabel($receivable);
                $journal = $this->journals->createJournal(
                    $this->journals->generateJournalNumber('BPS', $data['date']), $data['date'],
                    'Pelunasan piutang sopir '.$driverLabel.' - '.$receivable->number,
                    [
                        ['coa_id' => $data['payment_coa_id'], 'debit' => $data['amount'], 'kredit' => 0, 'deskripsi' => 'Penerimaan '.$driverLabel.' (No. Bayar: '.$number.')'],
                        ['coa_id' => $receivable->receivable_coa_id, 'debit' => 0, 'kredit' => $data['amount'], 'deskripsi' => 'Pelunasan piutang sopir '.$driverLabel.' (No: '.$receivable->number.')'],
                    ],
                    $number, auth()->id(), 'Pelunasan Piutang Sopir', 'Pelunasan Piutang'
                );
                $payment = DriverReceivablePayment::create($data + ['number' => $number, 'journal_id' => $journal->id, 'status' => 'POSTED', 'created_by' => auth()->id()]);
                $receivable->paid = (float) $receivable->paid + (float) $data['amount'];
                $receivable->balance = max(0, (float) $receivable->amount - (float) $receivable->paid);
                $receivable->status = $receivable->balance <= 0 ? 'LUNAS' : 'OPEN';
                $receivable->save();
                app(AuditLogService::class)->record('post', 'driver-payments', $payment, 'Pelunasan piutang sopir '.$receivable->number);
            });
        } catch (\Throwable $e) {
            $this->paymentFormTokens->restore(self::PAYMENT_TOKEN_SCOPE, $paymentToken);

            return back()->withInput()->with('error', $e->getMessage());
        }

        $this->paymentFormTokens->rememberSuccess(self::PAYMENT_TOKEN_SCOPE, $paymentToken, [
            'success' => true,
            'message' => 'Pelunasan dan jurnal berhasil disimpan.',
            'redirect_route' => 'driver-payments.index',
        ]);

        return redirect()->route('driver-payments.index')->with('success', 'Pelunasan dan jurnal berhasil disimpan.');
    }

    public function update(Request $request, DriverReceivablePayment $driver_payment)
    {
        $this->ensureEditable($driver_payment);
        $data = $request->validate([
            'driver_receivable_id' => ['required', 'exists:driver_receivables,id'],
            'date' => ['required', 'date'],
            'method' => ['required', Rule::in(['CASH', 'TRANSFER', 'POTONG_GAJI', 'POTONG_UANG_JALAN'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_coa_id' => ['required', 'exists:coas,id'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($this->locks->isLocked($data['date'])) return back()->withInput()->with('error', $this->locks->lockedMessage($data['date']));

        try {
            DB::transaction(function () use ($data, $driver_payment) {
                $payment = DriverReceivablePayment::whereKey($driver_payment->id)->lockForUpdate()->firstOrFail();
                $this->ensureEditable($payment);
                $ids = collect([$payment->driver_receivable_id, (int) $data['driver_receivable_id']])->unique()->sort()->values();
                $receivables = DriverReceivable::with('driver')->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $oldReceivable = $receivables->get($payment->driver_receivable_id);
                $newReceivable = $receivables->get((int) $data['driver_receivable_id']);
                if (! $oldReceivable || ! $newReceivable) throw new \RuntimeException('Data piutang sopir tidak ditemukan.');

                $oldPaymentAmount = (float) $payment->amount;
                $newPaymentAmount = (float) $data['amount'];
                $sameReceivable = (int) $oldReceivable->id === (int) $newReceivable->id;
                $available = (float) $newReceivable->balance + ($sameReceivable ? $oldPaymentAmount : 0);
                if ($newPaymentAmount > $available) throw new \RuntimeException('Nilai pembayaran melebihi saldo piutang yang tersedia.');

                $oldReceivable->paid = max(0, (float) $oldReceivable->paid - $oldPaymentAmount);
                $oldReceivable->balance = max(0, (float) $oldReceivable->amount - (float) $oldReceivable->paid);
                $oldReceivable->status = $oldReceivable->balance <= 0 ? 'LUNAS' : 'OPEN';
                $oldReceivable->save();

                if (! $sameReceivable) $newReceivable->refresh();
                $newReceivable->paid = (float) $newReceivable->paid + $newPaymentAmount;
                $newReceivable->balance = max(0, (float) $newReceivable->amount - (float) $newReceivable->paid);
                $newReceivable->status = $newReceivable->balance <= 0 ? 'LUNAS' : 'OPEN';
                $newReceivable->save();

                $journal = Jurnal::whereKey($payment->journal_id)->lockForUpdate()->firstOrFail();
                $journalNumber = $journal->tanggal?->toDateString() === $data['date']
                    ? $journal->no_jurnal : $this->journals->generateJournalNumber('BPS', $data['date']);
                $driverLabel = $this->driverLabel($newReceivable);
                $journal->update(['no_jurnal' => $journalNumber, 'tanggal' => $data['date'],
                    'keterangan' => 'Pelunasan piutang sopir '.$driverLabel.' - '.$newReceivable->number, 'referensi' => $payment->number,
                    'total_debit' => $newPaymentAmount, 'total_kredit' => $newPaymentAmount]);
                $journal->items()->delete();
                $journal->items()->createMany([
                    ['coa_id' => $data['payment_coa_id'], 'debit' => $newPaymentAmount, 'kredit' => 0, 'deskripsi' => 'Penerimaan '.$driverLabel.' (No. Bayar: '.$payment->number.')'],
                    ['coa_id' => $newReceivable->receivable_coa_id, 'debit' => 0, 'kredit' => $newPaymentAmount, 'deskripsi' => 'Pelunasan piutang sopir '.$driverLabel.' (No: '.$newReceivable->number.')'],
                ]);
                $payment->update($data);
                app(AuditLogService::class)->record('update', 'driver-payments', $payment, 'Mengubah pelunasan '.$payment->number);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
        return redirect()->route('driver-payments.index')->with('success', 'Pelunasan dan jurnal berhasil diperbarui.');
    }

    public function void(DriverReceivablePayment $driver_payment)
    {
        if ($driver_payment->status === 'VOID') return back()->with('error', 'Pembayaran sudah Void.');
        if ($this->locks->isLocked($driver_payment->date)) return back()->with('error', $this->locks->lockedMessage($driver_payment->date));
        DB::transaction(function () use ($driver_payment) {
            $payment = DriverReceivablePayment::whereKey($driver_payment->id)->lockForUpdate()->firstOrFail();
            $receivable = DriverReceivable::with('driver')->whereKey($payment->driver_receivable_id)->lockForUpdate()->firstOrFail();
            $driverLabel = $this->driverLabel($receivable);
            $journal = $this->journals->createJournal(
                $this->journals->generateJournalNumber('RPS', $payment->date), $payment->date,
                'Pembalik pelunasan piutang sopir '.$driverLabel.' - '.$payment->number,
                [
                    ['coa_id' => $receivable->receivable_coa_id, 'debit' => $payment->amount, 'kredit' => 0, 'deskripsi' => 'Pembalik piutang sopir '.$driverLabel],
                    ['coa_id' => $payment->payment_coa_id, 'debit' => 0, 'kredit' => $payment->amount, 'deskripsi' => 'Pembalik penerimaan '.$driverLabel],
                ],
                $payment->number.'-VOID', auth()->id(), 'Pelunasan Piutang Sopir', 'Pelunasan Piutang'
            );
            $payment->update(['status' => 'VOID', 'voided_at' => now(), 'void_journal_id' => $journal->id]);
            $receivable->paid = max(0, (float) $receivable->paid - (float) $payment->amount);
            $receivable->balance = (float) $receivable->amount - (float) $receivable->paid;
            $receivable->status = 'OPEN';
            $receivable->save();
            app(AuditLogService::class)->record('void', 'driver-payments', $payment, 'Membatalkan pelunasan piutang sopir '.$receivable->number);
        });
        return back()->with('success', 'Pembayaran berhasil di-void dengan jurnal pembalik.');
    }

    private function ensureEditable(DriverReceivablePayment $payment): void
    {
        $status = strtoupper(trim((string) $payment->status));
        abort_if($status !== 'POSTED', 422, 'Hanya pembayaran berstatus Posted yang dapat diedit. Status saat ini: '.($payment->status ?: '-'));
        abort_if($payment->void_journal_id, 422, 'Pembayaran yang sudah dibatalkan tidak dapat diedit.');
        abort_if($this->locks->isLocked($payment->date), 422, $this->locks->lockedMessage($payment->date));
    }

    private function generatePaymentNumber(string $date): string
    {
        $prefix = 'BPS'.\Carbon\Carbon::parse($date)->format('ymd');
        $latest = DriverReceivablePayment::where('number', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('number')->value('number');
        $sequence = $latest ? ((int) substr($latest, -3)) + 1 : 1;
        return $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function driverLabel(DriverReceivable $receivable): string
    {
        $driver = $receivable->driver;
        $label = $driver ? trim(($driver->code ?? '').' - '.($driver->name ?? ''), ' -') : '';

        return $label !== '' ? $label : 'Sopir #'.$receivable->driver_id;
    }
}
