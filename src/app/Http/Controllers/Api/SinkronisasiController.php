<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Arh;
use App\Models\Jurnal;
use App\Services\AuditLogService;
use App\Services\JournalService;
use App\Services\PeriodLockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Throwable;

class SinkronisasiController extends Controller
{
    public function __construct(
        private JournalService $journalService,
        private PeriodLockService $periodLockService,
        private AuditLogService $auditLogService,
    ) {}

    public function syncData(Request $request): JsonResponse
    {
        $data = $request->validate([
            'invoice' => ['required', 'string', 'max:30'],
            'customer' => ['nullable', 'string', 'max:30'],
            'kode_cus' => ['nullable', 'string', 'max:50'],
            'namacust' => ['nullable', 'string', 'max:100'],
            'alamat1' => ['nullable', 'string', 'max:150'],
            'dpp' => ['nullable', 'numeric'],
            'nilai_ppn' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'sumber' => ['nullable', 'string', 'max:15'],
            'jenis' => ['nullable', 'string', 'in:EKS,REN,CO,EXSPEDISI'],
            'status_bayar' => ['nullable', 'string', 'max:15'], // kompatibilitas nama field lama
            'status_sinkron' => ['nullable', 'string', 'max:50'],
            // UBAH BAGIAN INI: Gunakan 'nullable' saja agar menerima string/date dari FoxPro
            'tglinvoice' => ['nullable'],
            'tgl_kirim' => ['nullable'],
            'tgl_update' => ['nullable'],
        ]);
        try {
            DB::transaction(function () use ($data) {
                $customerCode = $this->customerCode($data);
                $customerName = $this->customerName($data);

                // 1. Update Master Customer
                if ($customerCode !== '' && Schema::hasTable('mcustomer')) {
                    $customer = $this->findCustomer($customerCode, $customerName);
                    $oldCustomerCode = $customer ? trim((string) $customer->CUSTOMER) : null;
                    $resolvedCustomerCode = $customer
                        ? $this->canonicalCustomerCode($customerCode, $oldCustomerCode)
                        : $customerCode;

                    $values = [
                        'CUSTOMER' => $resolvedCustomerCode,
                        'NAMACUST' => $customerName,
                        'ALAMAT1'  => $data['alamat1'] ?? '',
                        'kode_cus' => $this->optionalCode($data['kode_cus'] ?? null),
                        'updated_at' => now(),
                    ];

                    if ($customer) {
                        DB::table('mcustomer')->where('id', $customer->id)->update($values);
                        if ($oldCustomerCode && $oldCustomerCode !== $resolvedCustomerCode) {
                            $this->replaceCustomerCode($oldCustomerCode, $resolvedCustomerCode);
                        }
                    } else {
                        DB::table('mcustomer')->insert($values + [
                            'SETATUS' => 'A',
                            'TGL_UPDATE' => now()->toDateString(),
                            'created_at' => now(),
                        ]);
                    }

                    $customerCode = $resolvedCustomerCode;
                }

                // 2. Update Penjualan dengan field tambahan
                $values = [
                    'customer' => $customerCode !== '' ? $customerCode : null,
                    'dpp' => $data['dpp'] ?? 0,
                    'nilai_ppn' => $data['nilai_ppn'] ?? 0,
                    'total' => $data['total'] ?? 0,
                    'sumber' => $data['sumber'] ?? '',
                ];
                if (Schema::hasColumn('t_penjualan_hosting', 'tglinvoice')) $values['tglinvoice'] = !empty($data['tglinvoice']) ? Carbon::parse($data['tglinvoice']) : null;
                if (Schema::hasColumn('t_penjualan_hosting', 'tgl_kirim')) $values['tgl_kirim'] = !empty($data['tgl_kirim']) ? Carbon::parse($data['tgl_kirim']) : now();
                if (Schema::hasColumn('t_penjualan_hosting', 'tgl_update')) $values['tgl_update'] = !empty($data['tgl_update']) ? Carbon::parse($data['tgl_update']) : now();
                foreach (['namacust', 'alamat1', 'status_sinkron'] as $column) {
                    if (Schema::hasColumn('t_penjualan_hosting', $column)) $values[$column] = $data[$column] ?? ($column === 'status_sinkron' ? 'SUDAH DIKIRIM' : null);
                }
                $jenis = strtoupper(trim($data['jenis'] ?? $data['status_bayar'] ?? ''));
                if (Schema::hasColumn('t_penjualan_hosting', 'jenis')) {
                    $values['jenis'] = $jenis;
                } elseif (Schema::hasColumn('t_penjualan_hosting', 'status_bayar')) {
                    $values['status_bayar'] = $jenis;
                }
                DB::table('t_penjualan_hosting')->updateOrInsert(
                    ['invoice' => $data['invoice']],
                    $values
                );
            });

            return response()->json(['success' => true, 'invoice' => $data['invoice']], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Data penjualan gagal disinkronkan.'], 500);
        }
    }

    private function customerCode(array $data): string
    {
        return mb_strtoupper(trim((string) ($data['customer'] ?? '')));
    }

    private function customerName(array $data): string
    {
        $name = trim((string) ($data['namacust'] ?? ''));
        if ($name === '') {
            $name = trim((string) ($data['customer'] ?? ''));
        }

        return $name !== '' ? $name : 'Unknown';
    }

    private function findCustomer(string $customerCode, string $customerName): ?object
    {
        $customer = DB::table('mcustomer')
            ->where('CUSTOMER', $customerCode)
            ->lockForUpdate()
            ->first();

        if ($customer || $customerName === '') {
            return $customer;
        }

        $normalizedName = $this->normalizeText($customerName);

        return DB::table('mcustomer')
            ->where('NAMACUST', 'like', mb_substr($customerName, 0, 20).'%')
            ->lockForUpdate()
            ->get()
            ->first(function ($candidate) use ($customerCode, $normalizedName): bool {
                $candidateCode = mb_strtoupper(trim((string) $candidate->CUSTOMER));

                return $this->normalizeText($candidate->NAMACUST) === $normalizedName
                    && ($candidateCode !== '')
                    && (str_starts_with($customerCode, $candidateCode) || str_starts_with($candidateCode, $customerCode));
            });
    }

    private function canonicalCustomerCode(string $incomingCode, string $existingCode): string
    {
        if (str_starts_with($incomingCode, $existingCode) && mb_strlen($incomingCode) > mb_strlen($existingCode)) {
            return $incomingCode;
        }

        return $existingCode;
    }

    private function normalizeText(mixed $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtoupper(trim((string) $value))) ?? '';
    }

    private function replaceCustomerCode(string $oldCode, string $newCode): void
    {
        if (Schema::hasTable('t_penjualan_hosting') && Schema::hasColumn('t_penjualan_hosting', 'customer')) {
            DB::table('t_penjualan_hosting')->where('customer', $oldCode)->update(['customer' => $newCode]);
        }

        if (Schema::hasTable('arh') && Schema::hasColumn('arh', 'CUSTOMER')) {
            DB::table('arh')->where('CUSTOMER', $oldCode)->update(['CUSTOMER' => $newCode]);
        }
    }

    private function optionalCode(mixed $value): ?string
    {
        $code = mb_strtoupper(trim((string) $value));

        return $code !== '' ? $code : null;
    }

    public function rekapan(Request $request): JsonResponse
    {
        $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date'],
        ]);

        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());

        try {
            $penjualanQuery = DB::table('t_penjualan_hosting')
                ->whereBetween('tglinvoice', [$tanggalAwal, $tanggalAkhir]);

            $totalPenjualan = (clone $penjualanQuery)->sum('total');
            $jumlahInvoice = (clone $penjualanQuery)->count();

            $piutangQuery = DB::table('arh')
                ->whereBetween('TGLFAKTUR', [$tanggalAwal, $tanggalAkhir]);

            $totalPiutang = (clone $piutangQuery)->sum('PIUTANG');
            $totalBayar = (clone $piutangQuery)->sum('BAYAR');
            $sisaPiutang = $totalPiutang - $totalBayar;

            $jurnalPosted = DB::table('jurnals')
                ->where('is_posted', true)
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
                ->count();

            $coaCount = DB::table('coas')
                ->where('is_active', true)
                ->count();

            return response()->json([
                'success' => true,
                'periode' => [
                    'tanggal_awal' => $tanggalAwal,
                    'tanggal_akhir' => $tanggalAkhir,
                ],
                'penjualan' => [
                    'jumlah_invoice' => $jumlahInvoice,
                    'total' => $totalPenjualan,
                ],
                'piutang' => [
                    'total' => $totalPiutang,
                    'total_bayar' => $totalBayar,
                    'sisa' => $sisaPiutang,
                ],
                'jurnal_posted' => $jurnalPosted,
                'total_coa_aktif' => $coaCount,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data rekapan.'], 500);
        }
    }

    public function void(Request $request, string $invoice): JsonResponse
    {
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
            return response()->json(['success' => true, 'message' => 'Invoice berhasil di-void dengan jurnal pembalik.']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function revoid(Request $request, string $invoice): JsonResponse
    {
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
            return response()->json(['success' => true, 'message' => 'Revoid berhasil. Piutang dipulihkan dan invoice dapat direjurnal kembali.']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
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
}
