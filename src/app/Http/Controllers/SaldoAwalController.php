<?php

namespace App\Http\Controllers;

use App\Exports\SaldoAwalExport;
use App\Http\Requests\SaldoAwalRequest;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalItem;
use App\Services\AuditLogService;
use App\Services\PeriodLockService;
use App\Services\FastTablePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SaldoAwalController extends Controller
{
    public function __construct(private readonly FastTablePdfService $fastTablePdf) {}

    public function index(Request $request)
    {
        // 'tipe' column may not exist; identify Saldo Awal by no_jurnal prefix 'SA'
        $query = Jurnal::with('createdBy')
            ->where('no_jurnal', 'like', 'SA%');

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

        $saldoAwals = $query->latest()->paginate(15)->withQueryString();

        return view('saldo-awal.index', compact('saldoAwals'));
    }

    public function create(Request $request)
    {
        $latestDraft = null;
        $selectedCoaIds = [];
        $noSaldoAwal = $this->generateNoSaldoAwal();

        if ($request->filled('draft_id')) {
            $latestDraft = Jurnal::with('items')
                ->where('created_by', Auth::id())
                ->where('status', Jurnal::STATUS_DRAFT)
                ->where('no_jurnal', 'like', 'SA%')
                ->where('id', $request->input('draft_id'))
                ->first();

            if ($latestDraft) {
                $selectedCoaIds = $latestDraft->items->pluck('coa_id')->filter()->unique()->toArray();
                $noSaldoAwal = $latestDraft->no_jurnal;
            }
        }

        $coas = Coa::active()
            ->when($selectedCoaIds, function ($query, $selectedCoaIds) {
                $query->posting()->orWhereIn('id', $selectedCoaIds);
            }, function ($query) {
                $query->posting();
            })
            ->orderBy('kode_akun')
            ->get();

        return view('saldo-awal.create', compact('coas', 'noSaldoAwal', 'latestDraft'))->with('isEdit', false);
    }

    public function pdf(Request $request)
    {
        $query = Jurnal::query()->where('no_jurnal', 'like', 'SA%')
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
            'title' => 'LAPORAN SALDO AWAL AKUN', 'subtitles' => ['Dicetak '.now()->format('d/m/Y H:i')],
            'headings' => ['Tanggal','No. Saldo Awal','Keterangan','Debit','Kredit','Status'],
            'columns' => [55,85,249,75,75,25], 'rows' => $data, 'numeric' => [3,4],
            'dot_matrix' => $dotMatrix, 'font_size' => $dotMatrix ? 5.4 : 6.0, 'rows_per_page' => 62,
            'filename' => 'saldo_awal_akun_'.($dotMatrix ? 'dot_matrix_' : 'standard_').now()->format('Ymd_His').'.pdf',
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

        DB::beginTransaction();
        try {
            $data['total_debit'] = array_sum($request->input('debit', []));
            $data['total_kredit'] = array_sum($request->input('kredit', []));

            if (
                $draftId && $jurnal = Jurnal::where('id', $draftId)
                    ->where('created_by', $userId)
                    ->where('no_jurnal', 'like', 'SA%')
                    ->first()
            ) {
                $jurnal->update($data);
            } else {
                $jurnal = Jurnal::create($data);
            }

            $this->saveJurnalItems($jurnal, $request);
            DB::commit();

            app(AuditLogService::class)->record('post', 'saldo-awal', $jurnal, "Menyimpan dan posting saldo awal {$jurnal->no_jurnal}.", [
                'no_jurnal' => $jurnal->no_jurnal,
                'tanggal' => $jurnal->tanggal?->format('Y-m-d'),
            ]);

            return redirect()->route('saldo-awal.index')->with('success', 'Saldo Awal berhasil disimpan dan diposting.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menyimpan Saldo Awal: '.$e->getMessage())->withInput();
        }
    }

    public function show(Jurnal $saldo_awal)
    {
        // 'tipe' might not exist in DB schema; validate using no_jurnal prefix
        if (! str_starts_with($saldo_awal->no_jurnal ?? '', 'SA')) {
            return redirect()->route('saldo-awal.index')->with('error', 'Data tidak ditemukan.');
        }

        $saldo_awal->load(['createdBy'])->loadItemHistory();

        return view('saldo-awal.show', compact('saldo_awal'));
    }

    public function pdfDetail(Request $request, Jurnal $saldo_awal)
    {
        abort_unless(str_starts_with($saldo_awal->no_jurnal ?? '', 'SA'), 404);
        $saldo_awal->loadItemHistory();
        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        $rows = $saldo_awal->items->values()->map(fn ($item, $index) => [
            (string) ($index + 1),
            (string) ($item->coa?->kode_akun ?? '-'),
            (string) ($item->coa?->nama_akun ?? '-'),
            number_format((float) $item->debit, 2, ',', '.'),
            number_format((float) $item->kredit, 2, ',', '.'),
        ])->all();
        $rows[] = [
            '', '', 'TOTAL',
            number_format((float) $saldo_awal->total_debit, 2, ',', '.'),
            number_format((float) $saldo_awal->total_kredit, 2, ',', '.'),
        ];

        return $this->fastTablePdf->stream([
            'title' => 'DETAIL SALDO AWAL AKUN',
            'subtitles' => [
                $saldo_awal->no_jurnal.' | '.($saldo_awal->tanggal?->format('d/m/Y') ?? '-').' | '.strtoupper((string) $saldo_awal->status),
            ],
            'headings' => ['No', 'Kode Akun', 'Nama Akun', 'Debit', 'Kredit'],
            'columns' => [28, 92, 264, 90, 90],
            'rows' => $rows,
            'numeric' => [3, 4],
            'bold_columns' => [2, 3, 4],
            'dot_matrix' => $dotMatrix,
            'font_size' => $dotMatrix ? 5.8 : 6.4,
            'row_height' => 11,
            'rows_per_page' => 57,
            'filename' => 'detail_saldo_awal_'.preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $saldo_awal->no_jurnal).'_'.($dotMatrix ? 'dot_matrix' : 'standard').'.pdf',
        ]);
    }

    public function edit(Jurnal $saldo_awal)
    {
        if (! str_starts_with($saldo_awal->no_jurnal ?? '', 'SA')) {
            return redirect()->route('saldo-awal.index')->with('error', 'Data tidak ditemukan.');
        }

        if ($saldo_awal->status !== Jurnal::STATUS_DRAFT) {
            return redirect()->route('saldo-awal.index')->with('error', 'Hanya draft yang dapat diedit.');
        }

        $selectedCoaIds = $saldo_awal->items->pluck('coa_id')->toArray();

        $coas = Coa::active()
            ->where(function ($query) use ($selectedCoaIds) {
                $query->posting();
                if (! empty($selectedCoaIds)) {
                    $query->orWhereIn('id', $selectedCoaIds);
                }
            })
            ->orderBy('kode_akun')
            ->get();

        $saldo_awal->load('items');
        $latestDraft = $saldo_awal;
        $noSaldoAwal = $saldo_awal->no_jurnal;

        return view('saldo-awal.create', compact('coas', 'noSaldoAwal', 'latestDraft'))->with('isEdit', true);
    }

    public function update(SaldoAwalRequest $request, Jurnal $saldo_awal)
    {
        if (! str_starts_with($saldo_awal->no_jurnal ?? '', 'SA')) {
            return redirect()->route('saldo-awal.index')->with('error', 'Data tidak ditemukan.');
        }

        if ($saldo_awal->status !== Jurnal::STATUS_DRAFT) {
            return redirect()->route('saldo-awal.index')->with('error', 'Hanya draft yang dapat diposting.');
        }

        DB::beginTransaction();
        try {
            $saldo_awal->update([
                'tanggal' => $request->input('tanggal'),
                'keterangan' => $request->input('keterangan'),
                'total_debit' => array_sum($request->input('debit', [])),
                'total_kredit' => array_sum($request->input('kredit', [])),
                'status' => Jurnal::STATUS_POSTED,
                'is_posted' => true,
                'posted_at' => now(),
            ]);

            $this->saveJurnalItems($saldo_awal, $request);
            DB::commit();

            app(AuditLogService::class)->record('post', 'saldo-awal', $saldo_awal, "Memperbarui dan posting saldo awal {$saldo_awal->no_jurnal}.", [
                'no_jurnal' => $saldo_awal->no_jurnal,
                'tanggal' => $saldo_awal->tanggal?->format('Y-m-d'),
            ]);

            return redirect()->route('saldo-awal.index')->with('success', 'Saldo Awal berhasil diposting.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memperbarui Saldo Awal: '.$e->getMessage())->withInput();
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
            'no_jurnal' => $request->input('no_jurnal', $this->generateNoSaldoAwal()),
            'tanggal' => $tanggal,
            'keterangan' => $request->input('keterangan'),
            'created_by' => $userId,
            'status' => Jurnal::STATUS_DRAFT,
            'is_posted' => false,
        ];

        DB::beginTransaction();
        try {
            $data['total_debit'] = array_sum($request->input('debit', []));
            $data['total_kredit'] = array_sum($request->input('kredit', []));

            if (
                $draftId && $jurnal = Jurnal::where('id', $draftId)
                    ->where('created_by', $userId)
                    ->where('no_jurnal', 'like', 'SA%')
                    ->first()
            ) {
                $jurnal->update($data);
            } else {
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
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function post(Jurnal $saldo_awal)
    {
        if (! str_starts_with($saldo_awal->no_jurnal ?? '', 'SA')) {
            return redirect()->route('saldo-awal.index')->with('error', 'Data tidak ditemukan.');
        }

        if ($saldo_awal->status === Jurnal::STATUS_POSTED) {
            return redirect()->route('saldo-awal.index')->with('error', 'Saldo Awal sudah diposting.');
        }

        if (app(PeriodLockService::class)->isLocked($saldo_awal->tanggal)) {
            return redirect()->route('saldo-awal.index')->with('error', app(PeriodLockService::class)->lockedMessage($saldo_awal->tanggal));
        }

        $saldo_awal->update([
            'status' => Jurnal::STATUS_POSTED,
            'is_posted' => true,
            'posted_at' => now(),
        ]);

        app(AuditLogService::class)->record('post', 'saldo-awal', $saldo_awal, "Posting saldo awal {$saldo_awal->no_jurnal}.", [
            'no_jurnal' => $saldo_awal->no_jurnal,
            'tanggal' => $saldo_awal->tanggal?->format('Y-m-d'),
        ]);

        return redirect()->route('saldo-awal.index')->with('success', 'Saldo Awal berhasil diposting.');
    }

    public function reopen(Request $request, Jurnal $saldo_awal)
    {
        if ($request->user()?->role?->name !== 'Administrator') {
            return redirect()->route('saldo-awal.index')->with('error', 'Hanya Administrator yang boleh membuka kembali Saldo Awal.');
        }

        if (! str_starts_with($saldo_awal->no_jurnal ?? '', 'SA')) {
            return redirect()->route('saldo-awal.index')->with('error', 'Data tidak ditemukan.');
        }

        if ($saldo_awal->status !== Jurnal::STATUS_POSTED || ! $saldo_awal->is_posted) {
            return redirect()->route('saldo-awal.index')->with('error', 'Hanya Saldo Awal berstatus Posted yang dapat dibuka kembali.');
        }

        if (app(PeriodLockService::class)->isLocked($saldo_awal->tanggal)) {
            return redirect()->route('saldo-awal.index')->with('error', app(PeriodLockService::class)->lockedMessage($saldo_awal->tanggal));
        }

        if ($saldo_awal->archivedItems()->exists()) {
            return redirect()->route('saldo-awal.index')->with('error', 'Saldo Awal sudah diarsipkan dan tidak dapat dibuka kembali. Reset tutup buku terlebih dahulu.');
        }

        $previousPostedAt = $saldo_awal->posted_at?->toDateTimeString();

        DB::transaction(function () use ($saldo_awal): void {
            $saldo_awal->update([
                'status' => Jurnal::STATUS_DRAFT,
                'is_posted' => false,
                'posted_at' => null,
            ]);
        });

        app(AuditLogService::class)->record('reopen', 'saldo-awal', $saldo_awal, "Membuka kembali saldo awal {$saldo_awal->no_jurnal} menjadi Draft.", [
            'no_jurnal' => $saldo_awal->no_jurnal,
            'tanggal' => $saldo_awal->tanggal?->format('Y-m-d'),
            'previous_status' => Jurnal::STATUS_POSTED,
            'previous_posted_at' => $previousPostedAt,
        ]);

        return redirect()->route('saldo-awal.edit', $saldo_awal)->with('success', 'Saldo Awal berhasil dibuka kembali menjadi Draft. Silakan perbaiki lalu posting ulang.');
    }

    public function destroy(Jurnal $saldo_awal)
    {
        if (! str_starts_with($saldo_awal->no_jurnal ?? '', 'SA')) {
            return redirect()->route('saldo-awal.index')->with('error', 'Data tidak ditemukan.');
        }
        if ($saldo_awal->status !== Jurnal::STATUS_DRAFT || $saldo_awal->is_posted) {
            return redirect()->route('saldo-awal.index')->with('error', 'Hanya Saldo Awal berstatus Draft yang dapat dihapus.');
        }
        if (app(PeriodLockService::class)->isLocked($saldo_awal->tanggal)) {
            return redirect()->route('saldo-awal.index')->with('error', app(PeriodLockService::class)->lockedMessage($saldo_awal->tanggal));
        }

        $number = $saldo_awal->no_jurnal;
        DB::transaction(function () use ($saldo_awal) {
            $saldo_awal->items()->delete();
            $saldo_awal->delete();
        });
        app(AuditLogService::class)->record('delete', 'saldo-awal', null, "Menghapus draft saldo awal {$number}.", ['no_jurnal' => $number]);

        return redirect()->route('saldo-awal.index')->with('success', "Draft {$number} berhasil dihapus.");
    }

    public function export(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

        return Excel::download(
            new SaldoAwalExport($tanggalAwal, $tanggalAkhir),
            'saldo_awal_'.$tanggalAwal.'_'.$tanggalAkhir.'.xlsx'
        );
    }

    private function saveJurnalItems(Jurnal $jurnal, Request $request, bool $deleteExisting = true)
    {
        if ($deleteExisting) {
            $jurnal->items()->delete();
        }

        foreach ($request->input('coa_id', []) as $index => $coaId) {
            $debit = $request->input('debit.'.$index, 0);
            $kredit = $request->input('kredit.'.$index, 0);

            $debit = is_numeric($debit) ? (float) $debit : 0.0;
            $kredit = is_numeric($kredit) ? (float) $kredit : 0.0;

            if (($debit > 0 || $kredit > 0) && $coaId) {
                JurnalItem::create([
                    'jurnal_id' => $jurnal->id,
                    'coa_id' => $coaId,
                    'deskripsi' => $request->input('deskripsi_item.'.$index) ?? '',
                    'debit' => $debit,
                    'kredit' => $kredit,
                ]);
            }
        }
    }

    private function generateNoSaldoAwal()
    {
        $date = now();
        $prefix = 'SA'.$date->format('Ym');
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
