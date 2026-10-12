<?php

namespace App\Http\Controllers;

use App\Models\AccountMapping;
use App\Models\Arh;
use App\Models\Bard;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Services\AuditLogService;
use App\Services\JournalService;
use App\Services\PeriodLockService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PiutangBerjalanController extends Controller
{
    public function __construct(
        private readonly JournalService $journalService,
        private readonly PeriodLockService $periodLockService,
    ) {}

    public function index(Request $request)
    {
        $source = $request->input('source', 'all');
        $piutangs = Arh::query()
            ->where(function ($query): void {
                $query->where('SOURCE', 'PIUTANG_BERJALAN_MANUAL')
                    ->orWhere('SOURCE', 'IMPORT_PENJUALAN')
                    ->orWhere('SOURCE', 'like', 'SYNC_%');
            })
            ->when($source === 'manual', fn ($query) => $query->where('SOURCE', 'PIUTANG_BERJALAN_MANUAL'))
            ->when($source === 'import', fn ($query) => $query->where('SOURCE', 'IMPORT_PENJUALAN'))
            ->when($source === 'sync', fn ($query) => $query->where('SOURCE', 'like', 'SYNC_%'))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.trim((string) $request->input('search')).'%';
                $query->where(fn ($filter) => $filter->where('NOFAKTUR', 'like', $search)->orWhere('CUSTOMER', 'like', $search)->orWhere('KETERANGAN', 'like', $search)->orWhere('NOJURNAL', 'like', $search));
            });
        $totals = (clone $piutangs)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(PIUTANG), 0) as piutang, COALESCE(SUM(BAYAR), 0) as bayar, COALESCE(SUM(SALDO), 0) as saldo')
            ->first();
        $piutangs = $piutangs->orderByDesc('TGLFAKTUR')->orderByDesc('id')->paginate(20)->withQueryString();
        $this->attachPaymentDetails($piutangs->getCollection());

        return view('piutang-berjalan.index', compact('piutangs', 'source', 'totals'));
    }

    public function create()
    {
        return view('piutang-berjalan.create', [
            'receivableAccountId' => AccountMapping::getCoaId('accounts_receivable'),
            'revenueAccountId' => AccountMapping::getCoaId('default_sales_revenue'),
            'outputTaxAccountId' => AccountMapping::getCoaId('output_tax'),
            'accounts' => Coa::query()->where('is_active', true)
                ->where('level', 'Akun Posting')->orderBy('kode_akun')->get(),
        ]);
    }

    public function edit(Arh $piutang)
    {
        try { $this->ensureEditable($piutang); }
        catch (\Throwable $e) { return redirect()->route('piutang-berjalan.index')->with('error', $e->getMessage()); }
        $journal = Jurnal::where('referensi', $piutang->NOFAKTUR)->with('items')->firstOrFail();
        if ($this->periodLockService->isLocked($journal->tanggal)) return redirect()->route('piutang-berjalan.index')->with('error', $this->periodLockService->lockedMessage($journal->tanggal));
        $receivableId = AccountMapping::getCoaId('accounts_receivable');
        $taxId = AccountMapping::getCoaId('output_tax');
        $debitItem = $journal->items->first(fn ($item) => (float) $item->debit > 0);
        $taxItem = $taxId ? $journal->items->firstWhere('coa_id', $taxId) : null;
        $revenueItem = $journal->items->first(fn ($item) => (float) $item->kredit > 0 && (! $taxItem || $item->id !== $taxItem->id));

        return view('piutang-berjalan.create', [
            'piutang' => $piutang, 'journal' => $journal,
            'receivableAccountId' => $debitItem?->coa_id ?? $receivableId,
            'revenueAccountId' => $revenueItem?->coa_id ?? AccountMapping::getCoaId('default_sales_revenue'),
            'outputTaxAccountId' => $taxItem?->coa_id,
            'salesValue' => (float) ($revenueItem?->kredit ?? 0), 'taxValue' => (float) ($taxItem?->kredit ?? 0),
            'accounts' => Coa::query()->where('is_active', true)->where('level', 'Akun Posting')->orderBy('kode_akun')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'no_invoice' => strtoupper(trim((string) $request->input('no_invoice'))),
            'customer' => strtoupper(trim((string) $request->input('customer'))),
        ]);

        $validated = $request->validate([
            'no_invoice' => [
                'required', 'string', 'max:50',
                Rule::unique('arh', 'NOFAKTUR'),
                Rule::unique('jurnals', 'referensi'),
            ],
            'tanggal' => ['required', 'date'],
            'jatuh_tempo' => ['required', 'date', 'after_or_equal:tanggal'],
            'customer' => ['required', 'string', 'max:100', 'exists:mcustomer,CUSTOMER'],
            'coa_piutang_id' => ['required', 'integer', 'exists:coas,id'],
            'coa_pendapatan_id' => ['required', 'integer', 'different:coa_piutang_id', 'exists:coas,id'],
            'nilai_penjualan' => ['required', 'numeric', 'gt:0'],
            'coa_pajak_id' => ['nullable', 'integer', 'different:coa_piutang_id', 'different:coa_pendapatan_id', 'exists:coas,id'],
            'nilai_pajak' => ['nullable', 'numeric', 'min:0', 'required_with:coa_pajak_id'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'no_invoice.unique' => 'Nomor bukti/invoice sudah pernah digunakan.',
            'customer.exists' => 'Pelanggan tidak ditemukan pada master pelanggan.',
        ]);

        if ($this->periodLockService->isLocked($validated['tanggal'])) {
            return back()->withInput()->with('error', $this->periodLockService->lockedMessage($validated['tanggal']));
        }

        $accounts = Coa::query()->whereIn('id', array_filter([
            $validated['coa_piutang_id'], $validated['coa_pendapatan_id'], $validated['coa_pajak_id'] ?? null,
        ]))->get()->keyBy('id');
        foreach ($accounts as $account) {
            if (! $account->is_active || $account->level !== 'Akun Posting') {
                return back()->withInput()->with('error', "Akun {$account->kode_akun} harus aktif dan berupa akun posting.");
            }
        }

        $sales = round((float) $validated['nilai_penjualan'], 2);
        $tax = round((float) ($validated['nilai_pajak'] ?? 0), 2);
        if ($tax > 0 && empty($validated['coa_pajak_id'])) {
            return back()->withInput()->with('error', 'Akun pajak keluaran wajib dipilih ketika nilai pajak diisi.');
        }
        $total = $sales + $tax;

        try {
            DB::transaction(function () use ($validated, $sales, $tax, $total): void {
                $customerLabel = $this->customerLabel($validated['customer']);
                if (Arh::query()->where('NOFAKTUR', $validated['no_invoice'])->lockForUpdate()->exists()
                    || Jurnal::query()->where('referensi', $validated['no_invoice'])->lockForUpdate()->exists()) {
                    throw new \InvalidArgumentException('Nomor bukti/invoice sudah pernah digunakan.');
                }

                $items = [
                    ['coa_id' => $validated['coa_piutang_id'], 'debit' => $total, 'kredit' => 0, 'deskripsi' => 'Piutang '.$customerLabel.' (Invoice: '.$validated['no_invoice'].')'],
                    ['coa_id' => $validated['coa_pendapatan_id'], 'debit' => 0, 'kredit' => $sales, 'deskripsi' => 'Pendapatan '.$customerLabel.' (Invoice: '.$validated['no_invoice'].')'],
                ];
                if ($tax > 0) {
                    $items[] = ['coa_id' => $validated['coa_pajak_id'], 'debit' => 0, 'kredit' => $tax, 'deskripsi' => 'Pajak keluaran '.$customerLabel.' (Invoice: '.$validated['no_invoice'].')'];
                }

                $journal = $this->journalService->createJournal(
                    $this->journalService->generateJournalNumber('PJ', $validated['tanggal']),
                    $validated['tanggal'],
                    'Penjualan kredit '.$validated['no_invoice'].' - '.$customerLabel,
                    $items,
                    $validated['no_invoice'],
                    auth()->id(),
                    'Piutang Berjalan',
                    'Penjualan'
                );

                Arh::query()->create([
                    'NOFAKTUR' => $validated['no_invoice'], 'TGLFAKTUR' => $validated['tanggal'],
                    'TGLJT' => $validated['jatuh_tempo'], 'CUSTOMER' => $validated['customer'],
                    'PIUTANG' => $total, 'BAYAR' => 0, 'RETUR' => 0, 'DISCOUNT' => 0,
                    'SALDO' => $total, 'KETERANGAN' => $validated['keterangan'] ?? null,
                    'STATUS' => 'OPEN', 'SOURCE' => 'PIUTANG_BERJALAN_MANUAL',
                    'NOJURNAL' => $journal->no_jurnal, 'USER' => auth()->user()?->name ?? 'System',
                ]);
            });
        } catch (QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23505'], true)) {
                return back()->withInput()->withErrors(['no_invoice' => 'Nomor bukti/invoice sudah pernah digunakan.']);
            }
            return back()->withInput()->with('error', 'Piutang gagal disimpan: '.$e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Piutang gagal disimpan: '.$e->getMessage());
        }

        return redirect()->route('piutang-berjalan.index')->with('success', "Piutang {$validated['no_invoice']} dan jurnalnya berhasil dibuat.");
    }

    public function update(Request $request, Arh $piutang)
    {
        try { $this->ensureEditable($piutang); }
        catch (\Throwable $e) { return redirect()->route('piutang-berjalan.index')->with('error', $e->getMessage()); }
        $journal = Jurnal::where('referensi', $piutang->NOFAKTUR)->firstOrFail();
        if ($this->periodLockService->isLocked($journal->tanggal)) return redirect()->route('piutang-berjalan.index')->with('error', $this->periodLockService->lockedMessage($journal->tanggal));
        $validated = $this->validateInput($request, $piutang, $journal);
        if ($this->periodLockService->isLocked($validated['tanggal'])) return back()->withInput()->with('error', $this->periodLockService->lockedMessage($validated['tanggal']));

        try {
            DB::transaction(function () use ($piutang, $journal, $validated): void {
                $customerLabel = $this->customerLabel($validated['customer']);
                $ar = Arh::whereKey($piutang->id)->lockForUpdate()->firstOrFail();
                $jurnal = Jurnal::whereKey($journal->id)->lockForUpdate()->firstOrFail();
                if ($this->hasSettlement($ar)) throw new \RuntimeException('Piutang sudah memiliki transaksi pembayaran.');
                $sales = round((float) $validated['nilai_penjualan'], 2); $tax = round((float) ($validated['nilai_pajak'] ?? 0), 2); $total = $sales + $tax;
                $noJurnal = $jurnal->tanggal?->toDateString() === $validated['tanggal'] ? $jurnal->no_jurnal : $this->journalService->generateJournalNumber('PJ', $validated['tanggal']);
                $description = 'Penjualan kredit '.$validated['no_invoice'].' - '.$customerLabel;
                $jurnal->update(['no_jurnal'=>$noJurnal,'tanggal'=>$validated['tanggal'],'keterangan'=>$description,'total_debit'=>$total,'total_kredit'=>$total,'referensi'=>$validated['no_invoice'],'status'=>Jurnal::STATUS_POSTED,'is_posted'=>true,'posted_at'=>now()]);
                $jurnal->items()->delete();
                $jurnal->items()->create(['coa_id'=>$validated['coa_piutang_id'],'debit'=>$total,'kredit'=>0,'deskripsi'=>'Piutang '.$customerLabel.' (Invoice: '.$validated['no_invoice'].')']);
                $jurnal->items()->create(['coa_id'=>$validated['coa_pendapatan_id'],'debit'=>0,'kredit'=>$sales,'deskripsi'=>'Pendapatan '.$customerLabel.' (Invoice: '.$validated['no_invoice'].')']);
                if ($tax > 0) $jurnal->items()->create(['coa_id'=>$validated['coa_pajak_id'],'debit'=>0,'kredit'=>$tax,'deskripsi'=>'Pajak keluaran '.$customerLabel.' (Invoice: '.$validated['no_invoice'].')']);
                $ar->update(['NOFAKTUR'=>$validated['no_invoice'],'TGLFAKTUR'=>$validated['tanggal'],'TGLJT'=>$validated['jatuh_tempo'],'CUSTOMER'=>$validated['customer'],'PIUTANG'=>$total,'SALDO'=>$total,'KETERANGAN'=>$validated['keterangan'] ?? null,'NOJURNAL'=>$noJurnal,'USER_UPDATE'=>auth()->user()?->name ?? 'System']);
                app(AuditLogService::class)->record('update', 'piutang-berjalan', $jurnal, "Mengubah piutang berjalan {$validated['no_invoice']}.", ['ar_id'=>$ar->id]);
            });
            return redirect()->route('piutang-berjalan.index')->with('success', 'Piutang berjalan dan jurnal berhasil diperbarui.');
        } catch (\Throwable $e) { report($e); return back()->withInput()->with('error', 'Gagal memperbarui: '.$e->getMessage()); }
    }

    public function destroy(Arh $piutang)
    {
        try {
            $this->ensureEditable($piutang);
            DB::transaction(function () use ($piutang): void {
                $ar = Arh::whereKey($piutang->id)->lockForUpdate()->firstOrFail();
                if ($this->hasSettlement($ar)) throw new \RuntimeException('Piutang sudah memiliki transaksi pembayaran.');
                $journal = Jurnal::where('referensi', $ar->NOFAKTUR)->lockForUpdate()->firstOrFail();
                if ($this->periodLockService->isLocked($journal->tanggal)) throw new \RuntimeException($this->periodLockService->lockedMessage($journal->tanggal));
                $number = $ar->NOFAKTUR; $snapshot = $ar->toArray();
                $journal->items()->delete(); $journal->delete(); $ar->delete();
                app(AuditLogService::class)->record('delete', 'piutang-berjalan', null, "Menghapus piutang berjalan {$number} beserta jurnalnya.", ['deleted_data'=>$snapshot]);
            });
            return redirect()->route('piutang-berjalan.index')->with('success', 'Piutang berjalan dan jurnal terkait berhasil dihapus.');
        } catch (\Throwable $e) { report($e); return redirect()->route('piutang-berjalan.index')->with('error', 'Gagal menghapus: '.$e->getMessage()); }
    }

    private function validateInput(Request $request, ?Arh $piutang = null, ?Jurnal $journal = null): array
    {
        $request->merge(['no_invoice'=>strtoupper(trim((string)$request->input('no_invoice'))),'customer'=>strtoupper(trim((string)$request->input('customer')))]);
        $validated = $request->validate([
            'no_invoice'=>['required','string','max:50',Rule::unique('arh','NOFAKTUR')->ignore($piutang?->id),Rule::unique('jurnals','referensi')->ignore($journal?->id)],
            'tanggal'=>['required','date'],'jatuh_tempo'=>['required','date','after_or_equal:tanggal'],'customer'=>['required','string','max:100','exists:mcustomer,CUSTOMER'],
            'coa_piutang_id'=>['required','integer','exists:coas,id'],'coa_pendapatan_id'=>['required','integer','different:coa_piutang_id','exists:coas,id'],
            'nilai_penjualan'=>['required','numeric','gt:0'],'coa_pajak_id'=>['nullable','integer','different:coa_piutang_id','different:coa_pendapatan_id','exists:coas,id'],
            'nilai_pajak'=>['nullable','numeric','min:0'],'keterangan'=>['nullable','string','max:1000'],
        ]);
        if ((float)($validated['nilai_pajak'] ?? 0) > 0 && empty($validated['coa_pajak_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['coa_pajak_id' => 'Akun pajak wajib dipilih ketika nilai pajak lebih dari nol.']);
        }
        $accountIds = array_filter([$validated['coa_piutang_id'], $validated['coa_pendapatan_id'], $validated['coa_pajak_id'] ?? null]);
        if (Coa::query()->whereIn('id', $accountIds)->where('is_active', true)->where('level', 'Akun Posting')->count() !== count($accountIds)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['coa_piutang_id' => 'Semua akun harus aktif dan berupa akun posting.']);
        }
        return $validated;
    }

    private function ensureEditable(Arh $piutang): void
    {
        if ($piutang->SOURCE !== 'PIUTANG_BERJALAN_MANUAL') throw new \RuntimeException('Hanya piutang berjalan manual yang dapat diubah.');
        if ($this->periodLockService->isLocked($piutang->TGLFAKTUR)) throw new \RuntimeException($this->periodLockService->lockedMessage($piutang->TGLFAKTUR));
        if ($this->hasSettlement($piutang)) throw new \RuntimeException('Piutang sudah memiliki pembayaran, retur, atau diskon.');
    }

    private function hasSettlement(Arh $piutang): bool
    {
        return (float)$piutang->BAYAR > 0 || (float)$piutang->RETUR > 0 || (float)$piutang->DISCOUNT > 0 || DB::table('bard')->where('NOFAKTUR',$piutang->NOFAKTUR)->exists();
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

    private function customerLabel(string $customerCode): string
    {
        $name = DB::table('mcustomer')->where('CUSTOMER', $customerCode)->value('NAMACUST');
        $name = trim((string) $name);

        return $name !== '' ? $name : $customerCode;
    }
}
