<?php

namespace App\Services;

use App\Models\Arh;
use App\Models\Aph;
use App\Models\Barh;
use App\Models\Baph;
use App\Models\DriverReceivable;
use App\Models\DriverReceivablePayment;
use App\Models\Jurnal;
use Illuminate\Support\Collection;

class LedgerSourceLinkService
{
    /**
     * Resolve source transaction links for ledger journals.
     *
     * @param  Collection<int, Jurnal|object>  $jurnals
     * @return array<int, array{url:string,label:string,source_number:?string,source_type:string}>
     */
    public function resolveForJournals(Collection $jurnals): array
    {
        $jurnals = $jurnals->filter(fn ($jurnal) => filled($jurnal->id ?? null) && filled($jurnal->no_jurnal ?? null))->values();
        if ($jurnals->isEmpty()) {
            return [];
        }

        $journalIds = $jurnals->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();
        $journalNumbers = $jurnals->pluck('no_jurnal')->unique()->values();

        $barhByNo = $this->indexBarh($journalNumbers);
        $baphByNo = $this->indexBaph($journalNumbers);
        $arhByNo = Arh::query()->whereIn('NOJURNAL', $journalNumbers)->get(['id', 'NOFAKTUR', 'NOJURNAL', 'SOURCE'])->keyBy('NOJURNAL');
        $aphByNo = Aph::query()->whereIn('NOJURNAL', $journalNumbers)->get(['id', 'NOFAKTUR', 'NOJURNAL', 'SOURCE'])->keyBy('NOJURNAL');
        $driverReceivables = DriverReceivable::query()->whereIn('journal_id', $journalIds)->get(['id', 'number', 'journal_id', 'origin'])->keyBy('journal_id');
        $driverPayments = $this->indexDriverPayments($journalIds);

        $links = [];
        foreach ($jurnals as $jurnal) {
            $journalId = (int) $jurnal->id;
            $noJurnal = (string) $jurnal->no_jurnal;
            $module = (string) ($jurnal->module_type ?? '');
            $tipe = (string) ($jurnal->tipe ?? '');

            if (isset($barhByNo[$noJurnal])) {
                $payment = $barhByNo[$noJurnal];
                $isVoid = strtoupper((string) ($payment->STATUS ?? '')) === 'VOID';
                $links[$journalId] = [
                    'url' => $isVoid
                        ? route('pelunasan-piutang.show', $payment->id)
                        : route('pelunasan-piutang.edit', $payment->id),
                    'label' => $isVoid ? 'Tampil Pelunasan' : 'Edit Pelunasan',
                    'source_number' => (string) $payment->NOBAYAR,
                    'source_type' => 'Pelunasan Piutang',
                ];
                continue;
            }

            if (isset($baphByNo[$noJurnal])) {
                $payment = $baphByNo[$noJurnal];
                $isVoid = strtoupper((string) ($payment->STATUS ?? '')) === 'VOID';
                $links[$journalId] = [
                    'url' => $isVoid
                        ? route('pelunasan-hutang.show', $payment->id)
                        : route('pelunasan-hutang.edit', $payment->id),
                    'label' => $isVoid ? 'Tampil Pelunasan' : 'Edit Pelunasan',
                    'source_number' => (string) $payment->NOBAYAR,
                    'source_type' => 'Pelunasan Hutang',
                ];
                continue;
            }

            if (isset($driverPayments[$journalId])) {
                $payment = $driverPayments[$journalId];
                $links[$journalId] = [
                    'url' => route('driver-payments.edit', $payment->id),
                    'label' => 'Edit Bayar Sopir',
                    'source_number' => (string) $payment->number,
                    'source_type' => 'Pelunasan Piutang Sopir',
                ];
                continue;
            }

            if (isset($driverReceivables[$journalId])) {
                $row = $driverReceivables[$journalId];
                $links[$journalId] = [
                    'url' => $row->origin === 'OPENING'
                        ? route('driver-opening-receivables.edit', $row->id)
                        : route('driver-receivables.edit', $row->id),
                    'label' => $row->origin === 'OPENING' ? 'Edit Saldo Awal Sopir' : 'Edit Piutang Sopir',
                    'source_number' => (string) $row->number,
                    'source_type' => 'Piutang Sopir',
                ];
                continue;
            }

            if (isset($arhByNo[$noJurnal])) {
                $invoice = $arhByNo[$noJurnal];
                $source = (string) ($invoice->SOURCE ?? '');
                if (str_starts_with($source, 'SALDO_AWAL_')) {
                    $links[$journalId] = [
                        'url' => route('piutang.edit', $invoice->id),
                        'label' => 'Edit Saldo Awal Piutang',
                        'source_number' => (string) $invoice->NOFAKTUR,
                        'source_type' => 'Saldo Awal Piutang',
                    ];
                } elseif (str_starts_with($source, 'SYNC_')) {
                    $links[$journalId] = [
                        'url' => route('penjualan-hosting.index', ['search' => $invoice->NOFAKTUR]),
                        'label' => 'Tampil Penjualan',
                        'source_number' => (string) $invoice->NOFAKTUR,
                        'source_type' => 'Penjualan',
                    ];
                } else {
                    $links[$journalId] = [
                        'url' => route('piutang-berjalan.edit', $invoice->id),
                        'label' => 'Edit Piutang',
                        'source_number' => (string) $invoice->NOFAKTUR,
                        'source_type' => 'Piutang Berjalan',
                    ];
                }
                continue;
            }

            if (isset($aphByNo[$noJurnal])) {
                $invoice = $aphByNo[$noJurnal];
                $source = (string) ($invoice->SOURCE ?? '');
                $links[$journalId] = [
                    'url' => str_starts_with($source, 'SALDO_AWAL_')
                        ? route('saldo-awal-hutang.edit', $invoice->id)
                        : route('hutang.edit', $invoice->id),
                    'label' => str_starts_with($source, 'SALDO_AWAL_') ? 'Edit Saldo Awal Hutang' : 'Edit Hutang',
                    'source_number' => (string) $invoice->NOFAKTUR,
                    'source_type' => 'Hutang',
                ];
                continue;
            }

            if ($module === 'Saldo Awal' || $tipe === 'Saldo Awal') {
                $links[$journalId] = [
                    'url' => route('saldo-awal.show', $journalId),
                    'label' => 'Tampil Saldo Awal',
                    'source_number' => $noJurnal,
                    'source_type' => 'Saldo Awal',
                ];
                continue;
            }

            if ($module === 'Jurnal Penyesuaian' || $tipe === 'Jurnal Penyesuaian') {
                $links[$journalId] = [
                    'url' => route('jurnal-penyesuaian.show', $journalId),
                    'label' => 'Tampil Penyesuaian',
                    'source_number' => $noJurnal,
                    'source_type' => 'Jurnal Penyesuaian',
                ];
                continue;
            }

            $links[$journalId] = [
                'url' => route('jurnal.show', $journalId),
                'label' => 'Tampil Jurnal',
                'source_number' => $noJurnal,
                'source_type' => $module !== '' ? $module : 'Jurnal',
            ];
        }

        return $links;
    }

    /**
     * @param  Collection<int, string>  $journalNumbers
     * @return array<string, Barh>
     */
    private function indexBarh(Collection $journalNumbers): array
    {
        $rows = Barh::query()
            ->where(function ($query) use ($journalNumbers): void {
                $query->whereIn('NOJURNAL', $journalNumbers)
                    ->orWhereIn('NOJURNAL_PEMBALIK', $journalNumbers);
            })
            ->get(['id', 'NOBAYAR', 'NOJURNAL', 'NOJURNAL_PEMBALIK', 'STATUS']);

        $indexed = [];
        foreach ($rows as $row) {
            if (filled($row->NOJURNAL)) {
                $indexed[(string) $row->NOJURNAL] = $row;
            }
            if (filled($row->NOJURNAL_PEMBALIK)) {
                $indexed[(string) $row->NOJURNAL_PEMBALIK] = $row;
            }
        }

        return $indexed;
    }

    /**
     * @param  Collection<int, string>  $journalNumbers
     * @return array<string, Baph>
     */
    private function indexBaph(Collection $journalNumbers): array
    {
        $rows = Baph::query()
            ->where(function ($query) use ($journalNumbers): void {
                $query->whereIn('NOJURNAL', $journalNumbers)
                    ->orWhereIn('NOJURNAL_PEMBALIK', $journalNumbers);
            })
            ->get(['id', 'NOBAYAR', 'NOJURNAL', 'NOJURNAL_PEMBALIK', 'STATUS']);

        $indexed = [];
        foreach ($rows as $row) {
            if (filled($row->NOJURNAL)) {
                $indexed[(string) $row->NOJURNAL] = $row;
            }
            if (filled($row->NOJURNAL_PEMBALIK)) {
                $indexed[(string) $row->NOJURNAL_PEMBALIK] = $row;
            }
        }

        return $indexed;
    }

    /**
     * @param  Collection<int, int>  $journalIds
     * @return array<int, DriverReceivablePayment>
     */
    private function indexDriverPayments(Collection $journalIds): array
    {
        $rows = DriverReceivablePayment::query()
            ->where(function ($query) use ($journalIds): void {
                $query->whereIn('journal_id', $journalIds)
                    ->orWhereIn('void_journal_id', $journalIds);
            })
            ->get(['id', 'number', 'journal_id', 'void_journal_id']);

        $indexed = [];
        foreach ($rows as $row) {
            if ($row->journal_id) {
                $indexed[(int) $row->journal_id] = $row;
            }
            if ($row->void_journal_id) {
                $indexed[(int) $row->void_journal_id] = $row;
            }
        }

        return $indexed;
    }
}
