<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckHostingInvoiceRejournal extends Command
{
    protected $signature = 'hosting:check-rejurnal {invoice}';

    protected $description = 'Cek angka invoice hosting, piutang, dan pelunasan yang dipakai validasi rejurnal.';

    public function handle(): int
    {
        $invoice = (string) $this->argument('invoice');
        $sale = DB::table('t_penjualan_hosting')->where('invoice', $invoice)->first();
        $ar = DB::table('arh')->where('NOFAKTUR', $invoice)->first();
        $active = $this->paymentSummary($invoice, false);
        $void = $this->paymentSummary($invoice, true);
        $detailTotal = $this->summaryTotal($active);
        $headerTotal = $ar ? (float) $ar->BAYAR + (float) $ar->RETUR + (float) $ar->DISCOUNT : 0.0;
        $settled = ((int) ($active->detail_count ?? 0) > 0) ? $detailTotal : $headerTotal;

        $this->info('Invoice: '.$invoice);
        $this->line('');

        $this->table(['Sumber', 'DPP', 'PPN', 'Total', 'Status Jurnal', 'No Jurnal'], [[
            't_penjualan_hosting',
            $this->money($sale->dpp ?? null),
            $this->money($sale->nilai_ppn ?? null),
            $this->money($sale->total ?? null),
            $sale->status_jurnal ?? '-',
            $sale->no_jurnal ?? '-',
        ]]);

        $this->table(['Sumber', 'Piutang', 'Bayar', 'Retur', 'Discount', 'Saldo', 'Status'], [[
            'arh',
            $this->money($ar->PIUTANG ?? null),
            $this->money($ar->BAYAR ?? null),
            $this->money($ar->RETUR ?? null),
            $this->money($ar->DISCOUNT ?? null),
            $this->money($ar->SALDO ?? null),
            $ar->STATUS ?? '-',
        ]]);

        $this->table(['Detail', 'Baris', 'Bayar', 'PPh23', 'Discount', 'Adm Bank', 'Retur', 'Total'], [
            ['Aktif', $active->detail_count ?? 0, $this->money($active->bayar ?? 0), $this->money($active->pph23 ?? 0), $this->money($active->discount ?? 0), $this->money($active->bank_admin ?? 0), $this->money($active->retur ?? 0), $this->money($detailTotal)],
            ['VOID', $void->detail_count ?? 0, $this->money($void->bayar ?? 0), $this->money($void->pph23 ?? 0), $this->money($void->discount ?? 0), $this->money($void->bank_admin ?? 0), $this->money($void->retur ?? 0), $this->money($this->summaryTotal($void))],
        ]);

        $this->line('Nilai settled yang dipakai Rejurnal: '.$this->money($settled));
        $this->line('Total koreksi hosting: '.$this->money($sale->total ?? null));

        if ($sale && (float) $sale->total + 0.01 < $settled) {
            $this->error('Rejurnal akan ditolak karena total koreksi lebih kecil dari settled.');
        } else {
            $this->info('Validasi nilai aman untuk Rejurnal.');
        }

        return self::SUCCESS;
    }

    private function paymentSummary(string $invoice, bool $void): object
    {
        return DB::table('bard')
            ->join('barh', 'barh.NOBAYAR', '=', 'bard.NOBAYAR')
            ->where('bard.NOFAKTUR', $invoice)
            ->where(function ($query) use ($void): void {
                if ($void) {
                    $query->where('barh.STATUS', 'VOID');
                } else {
                    $query->whereNull('barh.STATUS')->orWhere('barh.STATUS', '!=', 'VOID');
                }
            })
            ->selectRaw('COUNT(*) as detail_count, COALESCE(SUM(bard.NILAI_BAYAR),0) as bayar, COALESCE(SUM(bard.PPH23),0) as pph23, COALESCE(SUM(bard.DISCOUNT),0) as discount, COALESCE(SUM(bard.BANK_ADMIN),0) as bank_admin, COALESCE(SUM(bard.RETUR),0) as retur')
            ->first();
    }

    private function summaryTotal(?object $summary): float
    {
        return (float) ($summary->bayar ?? 0)
            + (float) ($summary->pph23 ?? 0)
            + (float) ($summary->discount ?? 0)
            + (float) ($summary->bank_admin ?? 0)
            + (float) ($summary->retur ?? 0);
    }

    private function money(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format((float) $value, 2, ',', '.');
    }
}
