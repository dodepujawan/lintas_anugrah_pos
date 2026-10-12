<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\JurnalRequest;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\YearClosing;
use App\Services\AuditLogService;
use App\Services\PeriodLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JurnalController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->format('Y-m-01'));
        $tanggalAkhir = $request->input('tanggal_akhir', now()->format('Y-m-d'));

        $query = Jurnal::with('createdBy')
            ->where('no_jurnal', 'like', 'JU%');

        $query->whereDate('tanggal', '>=', $tanggalAwal)
            ->whereDate('tanggal', '<=', $tanggalAkhir);
        if ($request->filled('status')) {
            if ($request->status === 'posted') {
                $query->posted();
            } elseif ($request->status === 'draft') {
                $query->draft();
            } elseif ($request->status === 'void') {
                $query->void();
            }
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($query) use ($search) {
                $query->where('no_jurnal', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhere('referensi', 'like', "%{$search}%")
                    ->orWhereHas('createdBy', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($query) use ($search) {
                        $query->where('deskripsi', 'like', "%{$search}%")
                            ->orWhereHas('coa', function ($query) use ($search) {
                                $query->where('kode_akun', 'like', "%{$search}%")
                                    ->orWhere('nama_akun', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $jurnals = $query->orderByDesc('tanggal')
            ->orderByDesc('no_jurnal')
            ->paginate(15)
            ->withQueryString();
        $lockedYears = YearClosing::query()->whereNotNull('closed_at')->pluck('year')->map(fn ($year) => (int) $year)->all();

        return view('jurnal.index', compact('jurnals', 'lockedYears', 'tanggalAwal', 'tanggalAkhir'));
    }

    public function create(Request $request)
    {
        $noJurnal = $this->generateNoJurnal();

        $coas = Coa::active()
            ->posting()
            ->orderBy('kode_akun')
            ->get();

        return view('jurnal.create', compact('coas', 'noJurnal'));
    }

    public function store(JurnalRequest $request)
    {
        $totalDebit = array_sum($request->debit);
        $totalKredit = array_sum($request->kredit);
        $draftId = $request->input('draft_id');

        try {
            DB::beginTransaction();

            if ($draftId && $jurnal = Jurnal::where('id', $draftId)->where('created_by', Auth::id())->where('status', Jurnal::STATUS_DRAFT)->first()) {
                $jurnal->update([
                    'no_jurnal' => $request->no_jurnal,
                    'tanggal' => $request->tanggal,
                    'keterangan' => $request->keterangan,
                    'total_debit' => $totalDebit,
                    'total_kredit' => $totalKredit,
                    'referensi' => $request->referensi,
                    'status' => Jurnal::STATUS_POSTED,
                    'is_posted' => true,
                    'posted_at' => now(),
                ]);
            } else {
                $jurnal = Jurnal::create([
                    'no_jurnal' => $request->no_jurnal,
                    'tanggal' => $request->tanggal,
                    'keterangan' => $request->keterangan,
                    'total_debit' => $totalDebit,
                    'total_kredit' => $totalKredit,
                    'referensi' => $request->referensi,
                    'created_by' => Auth::id() ?? 1,
                    'status' => Jurnal::STATUS_POSTED,
                    'is_posted' => true,
                    'posted_at' => now(),
                ]);
            }

            $this->saveJurnalItems($jurnal, $request);

            DB::commit();
            app(AuditLogService::class)->record('post', 'jurnal', $jurnal, "Menyimpan dan posting jurnal {$jurnal->no_jurnal}.", [
                'no_jurnal' => $jurnal->no_jurnal,
                'tanggal' => $jurnal->tanggal?->format('Y-m-d'),
                'total_debit' => (float) $jurnal->total_debit,
                'total_kredit' => (float) $jurnal->total_kredit,
            ]);

            return redirect()->to($this->highlightIndexUrl($jurnal))
                ->with('success', 'Jurnal berhasil disimpan dan diposting. Baris jurnal yang baru disimpan sudah ditandai di daftar.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal membuat jurnal: '.$e->getMessage())->withInput();
        }
    }

    public function show(Request $request, Jurnal $jurnal)
    {
        $jurnal->load(['createdBy'])->loadItemHistory();
        $viewOnly = $request->input('from') === 'laporan-penjualan';
        $canEdit = ! $viewOnly
            && $jurnal->status !== Jurnal::STATUS_VOID
            && ! app(PeriodLockService::class)->isLocked($jurnal->tanggal);
        $printMode = $request->boolean('print');
        $backUrl = $this->highlightIndexUrl($jurnal);
        $backLabel = 'Kembali';

        if ($request->input('from') === 'hutang' && $request->filled('hutang_id')) {
            $backUrl = route('hutang.show', $request->integer('hutang_id'));
            $backLabel = 'Kembali ke Hutang';
        }
        if ($request->input('from') === 'piutang' && $request->filled('piutang_id')) {
            $params = ['piutang' => $request->integer('piutang_id')];
            if ($request->input('piutang_from') === 'piutang-berjalan') {
                $params['from'] = 'piutang-berjalan';
            }
            $backUrl = route('piutang.show', $params);
            $backLabel = 'Kembali ke Detail Piutang';
        }
        if ($request->input('from') === 'pelunasan-piutang' && $request->filled('pelunasan_piutang_id')) {
            $backUrl = route('pelunasan-piutang.show', $request->integer('pelunasan_piutang_id'));
            $backLabel = 'Kembali ke Pelunasan Piutang';
        }
        if ($request->input('from') === 'pelunasan-hutang' && $request->filled('pelunasan_hutang_id')) {
            $backUrl = route('pelunasan-hutang.show', $request->integer('pelunasan_hutang_id'));
            $backLabel = 'Kembali ke Pelunasan Hutang';
        }
        if ($request->input('from') === 'driver-receivable' && $request->filled('driver_receivable_id')) {
            $backUrl = route('driver-receivables.show', $request->integer('driver_receivable_id'));
            $backLabel = 'Kembali ke Piutang Sopir';
        }
        if ($request->input('from') === 'driver-payment' && $request->filled('driver_payment_id')) {
            $backUrl = route('driver-payments.show', $request->integer('driver_payment_id'));
            $backLabel = 'Kembali ke Pelunasan Piutang Sopir';
        }
        if ($viewOnly) {
            $backUrl = route('laporan.penjualan');
            $backLabel = 'Kembali ke Laporan Penjualan';
        }

        return view('jurnal.show', compact('jurnal', 'canEdit', 'viewOnly', 'printMode', 'backUrl', 'backLabel'));
    }

    public function receiptPdf(Request $request, Jurnal $jurnal)
    {
        $jurnal->load(['createdBy', 'items.coa']);
        $paper = $request->input('paper') === 'full' ? 'full' : 'half';

        $pdf = Pdf::loadView('jurnal.receipt_pdf', compact('jurnal', 'paper'));
        $paper === 'full'
            ? $pdf->setPaper('letter', 'portrait')
            : $pdf->setPaper([0, 0, 612, 396], 'portrait');

        return $pdf->stream('bukti_jurnal_'.$jurnal->no_jurnal.'.pdf');
    }

    public function edit(Jurnal $jurnal)
    {
        if ($jurnal->status === Jurnal::STATUS_VOID) {
            return redirect()->route('jurnal.index')->with('error', 'Jurnal Void tidak dapat diedit.');
        }
        if (app(PeriodLockService::class)->isLocked($jurnal->tanggal)) {
            return redirect()->route('jurnal.index')->with('error', app(PeriodLockService::class)->lockedMessage($jurnal->tanggal));
        }

        $selectedCoaIds = $jurnal->items->pluck('coa_id')->toArray();
        $coas = Coa::active()
            ->where(function ($query) use ($selectedCoaIds) {
                $query->posting();
                if (! empty($selectedCoaIds)) {
                    $query->orWhereIn('id', $selectedCoaIds);
                }
            })
            ->orderBy('kode_akun')
            ->get();

        $jurnal->load('items.coa');

        $backToIndexUrl = $this->highlightIndexUrl($jurnal);

        return view('jurnal.edit', compact('jurnal', 'coas', 'backToIndexUrl'));
    }

    public function update(JurnalRequest $request, Jurnal $jurnal)
    {
        if ($jurnal->status === Jurnal::STATUS_VOID) {
            return redirect()->route('jurnal.index')->with('error', 'Jurnal Void tidak dapat diedit.');
        }
        if (app(PeriodLockService::class)->isLocked($jurnal->tanggal)) {
            return redirect()->route('jurnal.index')->with('error', app(PeriodLockService::class)->lockedMessage($jurnal->tanggal));
        }

        $totalDebit = array_sum($request->debit);
        $totalKredit = array_sum($request->kredit);

        try {
            DB::beginTransaction();

            $jurnal->update([
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'referensi' => $request->referensi,
                'status' => Jurnal::STATUS_POSTED,
                'is_posted' => true,
                'posted_at' => now(),
            ]);

            $this->saveJurnalItems($jurnal, $request);

            DB::commit();
            app(AuditLogService::class)->record('post', 'jurnal', $jurnal, "Memperbarui dan posting jurnal {$jurnal->no_jurnal}.", [
                'no_jurnal' => $jurnal->no_jurnal,
                'tanggal' => $jurnal->tanggal?->format('Y-m-d'),
                'total_debit' => (float) $jurnal->total_debit,
                'total_kredit' => (float) $jurnal->total_kredit,
            ]);

            return redirect()->to($this->highlightIndexUrl($jurnal))
                ->with('success', 'Jurnal berhasil disimpan dan diposting. Baris jurnal yang baru disimpan sudah ditandai di daftar.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memperbarui jurnal: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Jurnal $jurnal)
    {
        if (app(PeriodLockService::class)->isLocked($jurnal->tanggal)) {
            return redirect()->route('jurnal.index')->with('error', app(PeriodLockService::class)->lockedMessage($jurnal->tanggal));
        }

        if ($jurnal->status === Jurnal::STATUS_VOID) {
            return redirect()->route('jurnal.index')->with('error', 'Jurnal Void tidak dapat dihapus.');
        }

        if ($jurnal->status === Jurnal::STATUS_POSTED && $message = $this->journalBalanceError($jurnal)) {
            return redirect()->route('jurnal.index')->with('error', $message.' Jurnal posted harus diperbaiki atau divoid sebelum dihapus.');
        }

        try {
            DB::beginTransaction();
            $number = $jurnal->no_jurnal;
            $journalId = $jurnal->id;
            $journalDate = $jurnal->tanggal?->format('Y-m-d');
            $wasPosted = $jurnal->is_posted;

            $jurnal->items()->delete();
            $jurnal->delete();
            DB::commit();
            app(AuditLogService::class)->record('delete', 'jurnal', null, "Menghapus jurnal {$number}.", [
                'jurnal_id' => $journalId,
                'no_jurnal' => $number,
                'tanggal' => $journalDate,
                'was_posted' => $wasPosted,
            ]);

            return redirect()->route('jurnal.index')->with('success', "Jurnal {$number} berhasil dihapus");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menghapus jurnal');
        }
    }

    public function post(Jurnal $jurnal)
    {
        if (app(PeriodLockService::class)->isLocked($jurnal->tanggal)) {
            return redirect()->route('jurnal.index')->with('error', app(PeriodLockService::class)->lockedMessage($jurnal->tanggal));
        }

        if ($jurnal->status === Jurnal::STATUS_POSTED) {
            return redirect()->route('jurnal.index')->with('error', 'Jurnal sudah diposting');
        }

        if ($message = $this->journalBalanceError($jurnal)) {
            return redirect()->route('jurnal.index')->with('error', $message);
        }

        $jurnal->update([
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
            'total_debit' => $jurnal->items()->sum('debit'),
            'total_kredit' => $jurnal->items()->sum('kredit'),
        ]);

        app(AuditLogService::class)->record('post', 'jurnal', $jurnal, "Posting jurnal {$jurnal->no_jurnal}.", [
            'no_jurnal' => $jurnal->no_jurnal,
            'tanggal' => $jurnal->tanggal?->format('Y-m-d'),
        ]);

        return redirect()->route('jurnal.index')->with('success', 'Jurnal berhasil diposting');
    }

    public function void(Jurnal $jurnal)
    {
        if (app(PeriodLockService::class)->isLocked($jurnal->tanggal)) {
            return redirect()->route('jurnal.index')->with('error', app(PeriodLockService::class)->lockedMessage($jurnal->tanggal));
        }

        if ($jurnal->status === Jurnal::STATUS_VOID) {
            return redirect()->route('jurnal.index')->with('error', 'Jurnal sudah di-void');
        }

        $jurnal->update([
            'status' => Jurnal::STATUS_VOID,
            'is_posted' => false,
        ]);

        app(AuditLogService::class)->record('void', 'jurnal', $jurnal, "Void jurnal {$jurnal->no_jurnal}.", [
            'no_jurnal' => $jurnal->no_jurnal,
            'tanggal' => $jurnal->tanggal?->format('Y-m-d'),
        ]);

        return redirect()->route('jurnal.index')->with('success', 'Jurnal berhasil di-void');
    }

    public function saveDraft(Request $request)
    {
        $draftId = $request->input('draft_id');
        $userId = Auth::id() ?? 1;
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        if (app(PeriodLockService::class)->isLocked($tanggal)) {
            return response()->json([
                'success' => false,
                'message' => app(PeriodLockService::class)->lockedMessage($tanggal),
            ], 422);
        }

        $data = [
            'no_jurnal' => $request->input('no_jurnal', $this->generateNoJurnal()),
            'tanggal' => $tanggal,
            'tipe' => 'Jurnal Umum',
            'module_type' => 'Jurnal Umum',
            'keterangan' => $request->input('keterangan'),
            'referensi' => $request->input('referensi'),
            'created_by' => $userId,
            'status' => Jurnal::STATUS_DRAFT,
            'is_posted' => false,
        ];

        DB::beginTransaction();
        try {
            $totalDebit = array_sum($request->input('debit', []));
            $totalKredit = array_sum($request->input('kredit', []));
            $data['total_debit'] = $totalDebit;
            $data['total_kredit'] = $totalKredit;

            if ($draftId && $jurnal = Jurnal::where('id', $draftId)
                ->where('created_by', $userId)
                ->where('status', Jurnal::STATUS_DRAFT)
                ->first()) {
                // Nomor jurnal Draft yang sudah ada tidak boleh diganti oleh
                // nilai stale dari tab/form lama.
                $data['no_jurnal'] = $jurnal->no_jurnal;
                $jurnal->update($data);
            } else {
                if (Jurnal::where('no_jurnal', $data['no_jurnal'])->exists()) {
                    $data['no_jurnal'] = $this->generateNoJurnal();
                }
                $jurnal = Jurnal::create($data);
            }

            $this->saveJurnalItems($jurnal, $request);

            DB::commit();

            return response()->json([
                'success' => true,
                'draft_id' => $jurnal->id,
                'no_jurnal' => $jurnal->no_jurnal,
                'draft_saved_at' => $jurnal->updated_at?->format('H:i:s'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menyimpan draft jurnal umum.', [
                'user_id' => $userId,
                'draft_id' => $draftId,
                'no_jurnal' => $data['no_jurnal'] ?? null,
                'exception' => $e,
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function saveJurnalItems(Jurnal $jurnal, Request $request, bool $deleteExisting = true)
    {
        if ($deleteExisting) {
            $jurnal->items()->delete();
            $jurnal->archivedItems()->delete();
        }

        foreach ($request->input('coa_id', []) as $index => $coaId) {

            $debit = (float) ($request->input("debit.$index") ?? 0);
            $kredit = (float) ($request->input("kredit.$index") ?? 0);

            if (! $coaId) {
                continue;
            }

            if ($debit == 0 && $kredit == 0) {
                continue;
            }

            JurnalItem::create([
                'jurnal_id' => $jurnal->id,
                'coa_id' => $coaId,
                'deskripsi' => $request->input("deskripsi_item.$index"),
                'debit' => $debit,
                'kredit' => $kredit,
            ]);
        }
    }

    private function journalBalanceError(Jurnal $jurnal): ?string
    {
        $items = $jurnal->items()->get(['debit', 'kredit']);
        $validRows = $items->filter(fn ($item) => (float) $item->debit > 0 || (float) $item->kredit > 0)->count();
        $totalDebit = (float) $items->sum('debit');
        $totalKredit = (float) $items->sum('kredit');

        if ($validRows < 2) {
            return 'Minimal dua baris jurnal harus diisi sebelum diposting.';
        }

        if (abs($totalDebit - $totalKredit) > 0.01) {
            return 'Total Debit dan Kredit harus sama. Selisih: '.number_format(abs($totalDebit - $totalKredit), 2, ',', '.').'.';
        }

        return null;
    }

    private function highlightIndexUrl(Jurnal $jurnal): string
    {
        $tanggalAwal = $jurnal->tanggal?->format('Y-m-01') ?? now()->format('Y-m-01');
        $tanggalAkhir = $jurnal->tanggal?->format('Y-m-t') ?? now()->format('Y-m-d');
        $perPage = 15;

        $rowsBefore = Jurnal::query()
            ->where('no_jurnal', 'like', 'JU%')
            ->whereDate('tanggal', '>=', $tanggalAwal)
            ->whereDate('tanggal', '<=', $tanggalAkhir)
            ->where(function ($query) use ($jurnal) {
                $query->whereDate('tanggal', '>', $jurnal->tanggal)
                    ->orWhere(function ($sameDate) use ($jurnal) {
                        $sameDate->whereDate('tanggal', $jurnal->tanggal)
                            ->where('no_jurnal', '>', $jurnal->no_jurnal);
                    });
            })
            ->count();

        $params = [
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'highlight' => $jurnal->id,
        ];

        $page = intdiv($rowsBefore, $perPage) + 1;
        if ($page > 1) {
            $params['page'] = $page;
        }

        return route('jurnal.index', $params).'#jurnal-'.$jurnal->id;
    }

    private function generateNoJurnal()
    {
        $date = now();
        $prefix = 'JU'.$date->format('Ym');
        $lastJurnal = Jurnal::where('no_jurnal', 'like', $prefix.'%')
            ->orderBy('no_jurnal', 'desc')
            ->first();

        if ($lastJurnal) {
            $lastNumber = (int) substr($lastJurnal->no_jurnal, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
