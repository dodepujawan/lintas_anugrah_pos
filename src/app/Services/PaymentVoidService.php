<?php

namespace App\Services;

use App\Models\Aph;
use App\Models\Arh;
use App\Models\Bapd;
use App\Models\Baph;
use App\Models\Bard;
use App\Models\Barh;
use App\Models\Jurnal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaymentVoidService
{
    public function __construct(
        private readonly JournalService $journalService,
        private readonly PeriodLockService $periodLockService,
    ) {}

    public function receivable(Barh $payment, string $reason): void
    {
        $this->void($payment, Bard::class, Arh::class, $reason, 'piutang');
    }

    public function payable(Baph $payment, string $reason): void
    {
        $this->void($payment, Bapd::class, Aph::class, $reason, 'hutang');
    }

    private function void(Model $payment, string $detailClass, string $invoiceClass, string $reason, string $type): void
    {
        if ($payment->STATUS === 'VOID') throw new \RuntimeException('Pelunasan ini sudah dibatalkan.');
        if ($this->periodLockService->isLocked($payment->TGLBAYAR)) throw new \RuntimeException($this->periodLockService->lockedMessage($payment->TGLBAYAR));
        if ($this->periodLockService->isLocked(now())) throw new \RuntimeException($this->periodLockService->lockedMessage(now()));

        DB::transaction(function () use ($payment, $detailClass, $invoiceClass, $reason, $type): void {
            $payment = $payment->newQuery()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();
            if ($payment->STATUS === 'VOID') throw new \RuntimeException('Pelunasan ini sudah dibatalkan.');
            $details = $detailClass::query()->where('NOBAYAR', $payment->NOBAYAR)->get();
            foreach ($details as $detail) {
                $invoice = $invoiceClass::query()->where('NOFAKTUR', $detail->NOFAKTUR)->lockForUpdate()->firstOrFail();
                $paid = (float) $detail->NILAI_BAYAR;
                $pph23 = $type === 'piutang' ? (float) ($detail->PPH23 ?? 0) : 0;
                $bankAdmin = $type === 'piutang' ? (float) ($detail->BANK_ADMIN ?? 0) : 0;
                $settled = $paid + $pph23 + (float) $detail->DISCOUNT + $bankAdmin + (float) $detail->RETUR;
                $invoice->BAYAR = max(0, (float) $invoice->BAYAR - $paid);
                $invoice->SALDO = min((float) ($type === 'piutang' ? $invoice->PIUTANG : $invoice->HUTANG), (float) $invoice->SALDO + $settled);
                $invoice->STATUS = $invoice->SALDO > 0 ? 'OPEN' : 'LUNAS';
                $invoice->TGLLUNAS = $invoice->SALDO > 0 ? null : $invoice->TGLLUNAS;
                $invoice->save();
            }

            $original = Jurnal::query()->where('no_jurnal', $payment->NOJURNAL)->with('items')->lockForUpdate()->first();
            if (! $original || $original->items->isEmpty()) throw new \RuntimeException('Jurnal asli pelunasan tidak ditemukan atau tidak memiliki detail.');
            $items = $original->items->map(fn ($item) => ['coa_id'=>$item->coa_id,'deskripsi'=>'Pembalik: '.$item->deskripsi,'debit'=>(float)$item->kredit,'kredit'=>(float)$item->debit])->all();
            $reversal = $this->journalService->createJournal(
                $this->journalService->generateJournalNumber('RV', now()->format('Y-m-d')),
                now()->format('Y-m-d'),
                'Pembatalan pelunasan '.ucfirst($type).' '.$payment->NOBAYAR.': '.$reason,
                $items, 'REV-'.$payment->NOBAYAR, auth()->id(), 'Pembatalan Pelunasan '.ucfirst($type), 'Jurnal Umum'
            );
            $original->update(['keterangan'=>$original->keterangan.' [DIBATALKAN dengan '.$reversal->no_jurnal.': '.$reason.']']);
            $payment->update(['STATUS'=>'VOID','VOID_REASON'=>$reason,'VOIDED_AT'=>now(),'VOIDED_BY'=>auth()->id(),'NOJURNAL_PEMBALIK'=>$reversal->no_jurnal,'USER_UPDATE'=>auth()->user()?->name ?? 'System']);
            app(AuditLogService::class)->record('void', 'pelunasan-'.$type, $payment, 'Membatalkan pelunasan '.$payment->NOBAYAR.'.', ['reason'=>$reason,'original_journal'=>$payment->NOJURNAL,'reversal_journal'=>$reversal->no_jurnal]);
        });
    }
}
