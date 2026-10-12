<?php

namespace App\Http\Controllers;

use App\Exports\JurnalPenyesuaianExport;
use App\Http\Requests\SaldoAwalRequest;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Models\YearClosing;
use App\Services\AuditLogService;
use App\Services\PeriodLockService;
use App\Services\FastTablePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class JurnalPenyesuaianController extends Controller
{
    public function __construct(private readonly FastTablePdfService $fastTablePdf) {}

    public function index(Request $request)
    {
        $query = Jurnal::with('createdBy')->withCount('archivedItems')
            ->where('no_jurnal', 'like', 'JP%');

        if ($request->filled('status')) {
            if ($request->status === 'posted') {
                $query->where('status', Jurnal::STATUS_POSTED);
            } elseif ($request->status === 'draft') {
                $query->where('status', Jurnal::STATUS_DRAFT);
            }
        }

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_awal);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        $perPage = $request->boolean('print') ? 10000 : 15;
        $jurnals = $query->latest()->paginate($perPage)->withQueryString();

        $lockedYears = YearClosing::query()->whereNotNull('closed_at')->pluck('year')->map(fn ($year) => (int) $year)->all();

        return view('jurnal-penyesuaian.index', compact('jurnals', 'lockedYears'));
    }

    public function create(Request $request)
    {
        $coas = Coa::active()->posting()->orderBy('kode_akun')->get();
        $accountOptions = $coas->map(function ($coa) {
            return [
                'id' => $coa->id,
                'label' => $coa->kode_akun.' - '.$coa->nama_akun,
            ];
        })->toArray();
        $accountOptions = $coas->map(function ($coa) {
            return [
                'id' => $coa->id,
                'label' => $coa->kode_akun.' - '.$coa->nama_akun,
            ];
        })->values()->toArray();

        $latestDraft = null;
        $noJurnal = $this->generateNoJurnal();

        if ($request->filled('draft_id')) {
            $latestDraft = Jurnal::with('items')
                ->where('created_by', Auth::id())
                ->where('status', Jurnal::STATUS_DRAFT)
                ->where('no_jurnal', 'like', 'JP%')
                ->where('id', $request->input('draft_id'))
                ->first();

            if ($latestDraft) {
                $noJurnal = $latestDraft->no_jurnal;
            }
        }

        return view(
            'jurnal-penyesuaian.create',
            compact(
                'coas',
                'accountOptions',
                'noJurnal',
                'latestDraft'
            )
        )->with('isEdit', false);
    }

    public function pdf(Request $request)
    {
        $query = Jurnal::query()->where('no_jurnal', 'like', 'JP%')
            ->when($request->status === 'posted', fn ($q) => $q->where('status', Jurnal::STATUS_POSTED))
            ->when($request->status === 'draft', fn ($q) => $q->where('status', Jurnal::STATUS_DRAFT))
            ->when($request->filled('tanggal_awal'), fn ($q) => $q->whereDate('tanggal', '>=', $request->tanggal_awal))
            ->when($request->filled('tanggal_akhir'), fn ($q) => $q->whereDate('tanggal', '<=', $request->tanggal_akhir));
        $rows = $query->orderBy('tanggal')->orderBy('no_jurnal')->get();
        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        $data = $rows->map(fn ($row) => [
            $row->tanggal?->format('d/m/Y') ?? '-', (string) $row->no_jurnal, (string) ($row->keterangan ?? '-'),
            number_format((float) $row->total_debit, 2, ',', '.'), number_format((float) $row->total_kredit, 2, ',', '.'),
            $row->status === Jurnal::STATUS_POSTED ? 'POSTED' : 'DRAFT',
        ])->all();

        return $this->fastTablePdf->stream([
            'title' => 'LAPORAN JURNAL PENYESUAIAN', 'subtitles' => ['Dicetak '.now()->format('d/m/Y H:i')],
            'headings' => ['Tanggal','No. Jurnal','Keterangan','Debit','Kredit','Status'],
            'columns' => [55,85,249,75,75,25], 'rows' => $data, 'numeric' => [3,4],
            'dot_matrix' => $dotMatrix, 'font_size' => $dotMatrix ? 5.4 : 6.0, 'rows_per_page' => 62,
            'filename' => 'jurnal_penyesuaian_'.($dotMatrix ? 'dot_matrix_' : 'standard_').now()->format('Ymd_His').'.pdf',
        ]);
    }

    public function store(SaldoAwalRequest $request)
    {
        $draftId = $request->input('draft_id');
        $userId = Auth::id() ?? 1;
        $data = [
            'no_jurnal' => $request->input('no_jurnal'),
            'tanggal' => $request->input('tanggal'),
            'keterangan' => $request->input('keterangan'),
            'created_by' => $userId,
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ];

        if (Schema::hasColumn('jurnals', 'jenis_jurnal')) {
            $data['jenis_jurnal'] = 'PENYESUAIAN';
        }

        DB::beginTransaction();
        try {
            $data['total_debit'] = array_sum($request->input('debit', []));
            $data['total_kredit'] = array_sum($request->input('kredit', []));

            $canEditAnyDraft = Auth::user()?->hasMenuPermission('jurnal-penyesuaian', 'edit') ?? false;
            $draftQuery = Jurnal::where('id', $draftId)
                ->where('status', Jurnal::STATUS_DRAFT)
                ->where('no_jurnal', 'like', 'JP%');

            if (! $canEditAnyDraft) {
                $draftQuery->where('created_by', $userId);
            }

            if (
                $draftId && $jurnal = $draftQuery->first()
            ) {
                $jurnal->update($data);
            } else {
                $jurnal = Jurnal::where('created_by', $userId)
                    ->where('status', Jurnal::STATUS_DRAFT)
                    ->where('no_jurnal', $data['no_jurnal'])
                    ->first();

                if ($jurnal) {
                    $jurnal->update($data);
                } else {
                    $jurnal = Jurnal::create($data);
                }
            }

            $this->saveJurnalItems($jurnal, $request);
            DB::commit();

            app(AuditLogService::class)->record('post', 'jurnal-penyesuaian', $jurnal, "Menyimpan dan posting jurnal penyesuaian {$jurnal->no_jurnal}.", [
                'no_jurnal' => $jurnal->no_jurnal,
                'tanggal' => $jurnal->tanggal?->format('Y-m-d'),
            ]);

            return redirect()->route('jurnal-penyesuaian.index')->with('success', 'Jurnal Penyesuaian berhasil disimpan dan diposting.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menyimpan Jurnal Penyesuaian: '.$e->getMessage())->withInput();
        }
    }

    public function show(Jurnal $jurnal_penyesuaian)
    {
        if (! str_starts_with($jurnal_penyesuaian->no_jurnal ?? '', 'JP')) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', 'Data tidak ditemukan.');
        }

        $jurnal_penyesuaian->load(['createdBy'])->loadItemHistory();

        return view('jurnal-penyesuaian.show', compact('jurnal_penyesuaian'));
    }

    public function edit(Jurnal $jurnal_penyesuaian)
    {
        if (! str_starts_with($jurnal_penyesuaian->no_jurnal ?? '', 'JP')) {
            return redirect()->route('jurnal-penyesuaian.index')
                ->with('error', 'Data tidak ditemukan.');
        }

        if (! in_array($jurnal_penyesuaian->status, [Jurnal::STATUS_DRAFT, Jurnal::STATUS_POSTED], true)) {
            return redirect()->route('jurnal-penyesuaian.index')
                ->with('error', 'Jurnal Penyesuaian dengan status ini tidak dapat diedit.');
        }

        if (app(PeriodLockService::class)->isLocked($jurnal_penyesuaian->tanggal)) {
            return redirect()->route('jurnal-penyesuaian.index')
                ->with('error', app(PeriodLockService::class)->lockedMessage($jurnal_penyesuaian->tanggal));
        }

        if ($jurnal_penyesuaian->archivedItems()->exists()) {
            return redirect()->route('jurnal-penyesuaian.index')
                ->with('error', 'Jurnal Penyesuaian yang itemnya sudah diarsipkan tidak dapat diedit.');
        }

        $selectedCoaIds = $jurnal_penyesuaian->items->pluck('coa_id')->toArray();

        $coas = Coa::active()

            ->where(function ($query) use ($selectedCoaIds) {
                $query->posting();

                if (! empty($selectedCoaIds)) {
                    $query->orWhereIn('id', $selectedCoaIds);
                }
            })
            ->orderBy('kode_akun')
            ->get();

        $accountOptions = $coas->map(function ($coa) {
            return [
                'id' => $coa->id,
                'label' => $coa->kode_akun.' - '.$coa->nama_akun,
            ];
        })->toArray();

        $accountOptions = $coas->map(function ($coa) {
            return [
                'id' => $coa->id,
                'label' => $coa->kode_akun.' - '.$coa->nama_akun,
            ];
        })->values()->toArray();

        $jurnal_penyesuaian->load('items');

        $latestDraft = $jurnal_penyesuaian;
        $noJurnal = $jurnal_penyesuaian->no_jurnal;

        return view(
            'jurnal-penyesuaian.create',
            compact('coas', 'accountOptions', 'noJurnal', 'latestDraft')
        )->with([
            'isEdit' => true,
            'isPostedEdit' => $jurnal_penyesuaian->status === Jurnal::STATUS_POSTED,
        ]);
    }

    public function update(SaldoAwalRequest $request, Jurnal $jurnal_penyesuaian)
    {
        if (! str_starts_with($jurnal_penyesuaian->no_jurnal ?? '', 'JP')) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', 'Data tidak ditemukan.');
        }

        if (! in_array($jurnal_penyesuaian->status, [Jurnal::STATUS_DRAFT, Jurnal::STATUS_POSTED], true)) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', 'Jurnal Penyesuaian dengan status ini tidak dapat diperbarui.');
        }

        if (app(PeriodLockService::class)->isLocked($jurnal_penyesuaian->tanggal)) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', app(PeriodLockService::class)->lockedMessage($jurnal_penyesuaian->tanggal));
        }

        if ($jurnal_penyesuaian->archivedItems()->exists()) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', 'Jurnal Penyesuaian yang itemnya sudah diarsipkan tidak dapat diperbarui.');
        }

        DB::beginTransaction();
        try {
            $jurnal_penyesuaian->update([
                'tanggal' => $request->input('tanggal'),
                'keterangan' => $request->input('keterangan'),
                'total_debit' => array_sum($request->input('debit', [])),
                'total_kredit' => array_sum($request->input('kredit', [])),
                'status' => Jurnal::STATUS_POSTED,
                'is_posted' => true,
                'posted_at' => now(),
            ]);

            if (Schema::hasColumn('jurnals', 'jenis_jurnal')) {
                $jurnal_penyesuaian->update(['jenis_jurnal' => 'PENYESUAIAN']);
            }

            $this->saveJurnalItems($jurnal_penyesuaian, $request);
            DB::commit();

            app(AuditLogService::class)->record('update', 'jurnal-penyesuaian', $jurnal_penyesuaian, "Memperbarui jurnal penyesuaian {$jurnal_penyesuaian->no_jurnal}.", [
                'no_jurnal' => $jurnal_penyesuaian->no_jurnal,
                'tanggal' => $jurnal_penyesuaian->tanggal?->format('Y-m-d'),
            ]);

            return redirect()->route('jurnal-penyesuaian.index')->with('success', 'Jurnal Penyesuaian berhasil diperbarui dan tetap berstatus Posted.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memperbarui Jurnal Penyesuaian: '.$e->getMessage())->withInput();
        }
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
            'keterangan' => $request->input('keterangan'),
            'created_by' => $userId,
            'status' => Jurnal::STATUS_DRAFT,
            'is_posted' => false,
        ];

        if (Schema::hasColumn('jurnals', 'jenis_jurnal')) {
            $data['jenis_jurnal'] = 'PENYESUAIAN';
        }

        DB::beginTransaction();
        try {
            $data['total_debit'] = array_sum($request->input('debit', []));
            $data['total_kredit'] = array_sum($request->input('kredit', []));

            $canEditAnyDraft = Auth::user()?->hasMenuPermission('jurnal-penyesuaian', 'edit') ?? false;
            $draftQuery = Jurnal::where('id', $draftId)
                ->where('status', Jurnal::STATUS_DRAFT)
                ->where('no_jurnal', 'like', 'JP%');

            if (! $canEditAnyDraft) {
                $draftQuery->where('created_by', $userId);
            }

            if (
                $draftId && $jurnal = $draftQuery->first()
            ) {
                $jurnal->update($data);
            } else {
                $jurnal = Jurnal::where('created_by', $userId)
                    ->where('status', Jurnal::STATUS_DRAFT)
                    ->where('no_jurnal', $data['no_jurnal'])
                    ->first();

                if ($jurnal) {
                    $jurnal->update($data);
                } else {
                    $jurnal = Jurnal::create($data);
                }
            }

            $this->saveJurnalItems($jurnal, $request);
            DB::commit();

            return response()->json([
                'success' => true,
                'draft_id' => $jurnal->id,
                'no_jurnal' => $jurnal->no_jurnal,
                'draft_saved_at' => $jurnal->updated_at?->format('H:i:s'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function post(Jurnal $jurnal_penyesuaian)
    {
        if (! str_starts_with($jurnal_penyesuaian->no_jurnal ?? '', 'JP')) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', 'Data tidak ditemukan.');
        }

        if ($jurnal_penyesuaian->status === Jurnal::STATUS_POSTED) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', 'Jurnal Penyesuaian sudah diposting.');
        }

        if (app(PeriodLockService::class)->isLocked($jurnal_penyesuaian->tanggal)) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', app(PeriodLockService::class)->lockedMessage($jurnal_penyesuaian->tanggal));
        }

        $jurnal_penyesuaian->update([
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        if (Schema::hasColumn('jurnals', 'jenis_jurnal')) {
            $jurnal_penyesuaian->update(['jenis_jurnal' => 'PENYESUAIAN']);
        }

        app(AuditLogService::class)->record('post', 'jurnal-penyesuaian', $jurnal_penyesuaian, "Posting jurnal penyesuaian {$jurnal_penyesuaian->no_jurnal}.", [
            'no_jurnal' => $jurnal_penyesuaian->no_jurnal,
            'tanggal' => $jurnal_penyesuaian->tanggal?->format('Y-m-d'),
        ]);

        return redirect()->route('jurnal-penyesuaian.index')->with('success', 'Jurnal Penyesuaian berhasil diposting.');
    }

    public function destroy(Jurnal $jurnal_penyesuaian)
    {
        if (! str_starts_with($jurnal_penyesuaian->no_jurnal ?? '', 'JP')) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', 'Data tidak ditemukan.');
        }
        if ($jurnal_penyesuaian->status !== Jurnal::STATUS_DRAFT || $jurnal_penyesuaian->is_posted) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', 'Hanya Jurnal Penyesuaian berstatus Draft yang dapat dihapus.');
        }
        if (app(PeriodLockService::class)->isLocked($jurnal_penyesuaian->tanggal)) {
            return redirect()->route('jurnal-penyesuaian.index')->with('error', app(PeriodLockService::class)->lockedMessage($jurnal_penyesuaian->tanggal));
        }

        $number = $jurnal_penyesuaian->no_jurnal;
        DB::transaction(function () use ($jurnal_penyesuaian) {
            $jurnal_penyesuaian->items()->delete();
            $jurnal_penyesuaian->delete();
        });
        app(AuditLogService::class)->record('delete', 'jurnal-penyesuaian', null, "Menghapus draft jurnal penyesuaian {$number}.", ['no_jurnal' => $number]);

        return redirect()->route('jurnal-penyesuaian.index')->with('success', "Draft {$number} berhasil dihapus.");
    }

    public function export(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

        return Excel::download(
            new JurnalPenyesuaianExport($tanggalAwal, $tanggalAkhir),
            'jurnal_penyesuaian_'.$tanggalAwal.'_'.$tanggalAkhir.'.xlsx'
        );
    }

    private function saveJurnalItems(Jurnal $jurnal, Request $request, bool $deleteExisting = true)
    {
        if ($deleteExisting) {
            $jurnal->items()->delete();
        }

        $coas = $request->input('coa_id', []);
        $debits = $request->input('debit', []);
        $kredits = $request->input('kredit', []);
        $descs = $request->input('deskripsi_item', []);

        foreach ($coas as $index => $coaId) {

            if (! $coaId) {
                continue;
            }

            // 🔥 PAKSA SELALU ANGKA, TIDAK BOLEH NULL
            $debit = isset($debits[$index]) && $debits[$index] !== ''
                ? (float) $debits[$index]
                : 0;

            $kredit = isset($kredits[$index]) && $kredits[$index] !== ''
                ? (float) $kredits[$index]
                : 0;

            $deskripsi = $descs[$index] ?? null;

            // ❗ JANGAN GUNAKAN NULL KE DB
            JurnalItem::create([
                'jurnal_id' => $jurnal->id,
                'coa_id' => $coaId,
                'deskripsi' => $deskripsi,
                'debit' => $debit,
                'kredit' => $kredit,
            ]);
        }
    }

    private function generateNoJurnal()
    {
        $date = now();
        $prefix = 'JP'.$date->format('Ym');
        $last = Jurnal::where('no_jurnal', 'like', $prefix.'%')
            ->orderBy('no_jurnal', 'desc')
            ->first();

        $number = 1;
        if ($last) {
            $number = (int) substr($last->no_jurnal, -6) + 1;
        }

        return $prefix.str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
