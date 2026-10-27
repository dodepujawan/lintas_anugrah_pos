<?php

namespace App\Http\Controllers;

use App\Models\Expedisi;
use App\Models\Arh;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ExpedisiInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('expedisi-invoice-gabung.index');
    }

    /** Data utama legacy: satu baris master per muat/SJ. */
    public function getDataMuat(Request $request): JsonResponse
    {
        $query = Expedisi::query()
            ->where('JENIS', 'EKS')
            ->whereNotNull('NOMUAT')
            ->orderByDesc('TGLMUAT');

        $status = $request->input('filter_invoice', 'belum');
        if ($status === 'sudah') {
            $query->whereNotNull('GB')->where('GB', '<>', '')
                ->where('HARGA', '>', 0)->where(function ($q) {
                    $q->whereNull('INVOICE')->orWhere('INVOICE', '');
                });
        } else {
            $query->where(function ($q) {
                $q->whereNull('GB')->orWhere('GB', '');
            })->where(function ($q) {
                $q->whereNull('INVOICE')->orWhere('INVOICE', '');
            });
        }

        $query->when($request->filled('tgl_mulai'), fn ($q) => $q->whereDate('TGLMUAT', '>=', $request->tgl_mulai));
        $query->when($request->filled('tgl_akhir'), fn ($q) => $q->whereDate('TGLMUAT', '<=', $request->tgl_akhir));
        $query->when($request->filled('search_muat'), function ($q) use ($request) {
            $term = trim($request->search_muat);
            $q->where(fn ($sub) => $sub->where('NOMUAT', 'like', "%{$term}%")
                ->orWhere('CUSTOMER', 'like', "%{$term}%")
                ->orWhere('rute', 'like', "%{$term}%")
                ->orWhere('NOSJ', 'like', "%{$term}%"));
        });

        $rows = $query->get([
            'id', 'NOMUAT', 'TGLMUAT', 'CUSTOMER', 'CUSTOMER_KODE', 'PESANAN',
            'GB', 'PESANANGB', 'rute', 'NAMA_KENDARAAN', 'JUMLAH', 'UNIT',
            'HARGA', 'DISC', 'DC', 'GRAND', 'NOSJ', 'INVOICE', 'JENISHRG',
        ])->values()->map(fn (Expedisi $row, int $i) => $this->formatRow($row, $i + 1));

        return response()->json(['data' => $rows]);
    }

    /** SJ lain milik customer yang masih dapat dimasukkan ke grup. */
    public function dataGabung(Request $request): JsonResponse
    {
        $request->validate(['customer_kode' => ['required', 'string', 'max:20']]);
        $query = Expedisi::query()
            ->where('JENIS', 'EKS')->whereNotNull('NOMUAT')
            ->where(function ($q) { $q->whereNull('GB')->orWhere('GB', ''); })
            ->where(function ($q) { $q->whereNull('INVOICE')->orWhere('INVOICE', ''); })
            ->when($request->filled('customer_kode'), fn ($q) => $q->where('CUSTOMER_KODE', $request->customer_kode))
            ->orderBy('TGLMUAT');

        return response()->json(['data' => $query->get([
            'id', 'NOMUAT', 'TGLMUAT', 'NOSJ', 'PESANAN', 'CUSTOMER_KODE', 'CUSTOMER',
            'rute', 'NAMA_KENDARAAN', 'JUMLAH', 'UNIT', 'JENISHRG', 'HARGA', 'DISC',
            'DC', 'TOTAL', 'PPN', 'GRAND',
        ])->values()->map(fn (Expedisi $row, int $i) => $this->formatRow($row, $i + 1, true))]);
    }

    /** Muat yang sudah berada pada GB tertentu, untuk mode edit. */
    public function getExistingGabung(Request $request): JsonResponse
    {
        $gb = trim((string) $request->input('gbExist'));
        $rows = Expedisi::where('GB', $gb)->where('JENIS', 'EKS')->orderBy('NOSJ')->get();

        if ($rows->isEmpty()) {
            return response()->json(['message' => 'Grup gabungan tidak ditemukan.'], 404);
        }

        return response()->json([
            'data' => $rows->values()->map(fn (Expedisi $row, int $i) => $this->formatRow($row, $i + 1, true)),
            'master' => $rows->first(),
        ]);
    }

    public function show(string $noInvoice, Request $request)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'EKS')->get();
        $autoPrintInvoice = $request->boolean('auto_print') ? $noInvoice : session('autoPrintInvoice');

        return view('expedisi-invoice.show', compact('invoice', 'details', 'autoPrintInvoice'));
    }

    public function editGabung(string $noInvoice)
    {
        return redirect()->route('expedisi-invoice.index', ['gb' => $noInvoice]);
    }

    public function storeGabungInvoice(Request $request): JsonResponse
    {
        return $this->saveGroup($request, null);
    }

    public function updateGabungInvoice(Request $request, string $noInvoice): JsonResponse
    {
        return $this->saveGroup($request, $noInvoice);
    }

    private function saveGroup(Request $request, ?string $existingGb): JsonResponse
    {
        try {
            $data = $request->validate([
                'nosj_list' => ['required', 'array', 'min:2'],
                'nosj_list.*' => ['required', 'string', 'distinct'],
                'no_invoice' => ['nullable', 'string', 'max:15'],
                'item' => ['nullable', 'string', 'max:100'],
                'harga' => ['required', 'numeric', 'min:0'],
                'diskon' => ['nullable', 'numeric', 'between:0,100'],
                'dc' => ['nullable', 'numeric', 'min:0'],
                'ppn' => ['nullable', 'numeric', 'min:0'],
                'grand_total' => ['nullable', 'numeric', 'min:0'],
            ]);

            $gb = DB::transaction(function () use ($data, $existingGb, $request) {
                $rows = Expedisi::whereIn('NOSJ', $data['nosj_list'])
                    ->where('JENIS', 'EKS')->lockForUpdate()->orderBy('NOSJ')->get();
                if ($rows->count() !== count(array_unique($data['nosj_list']))) {
                    throw new \RuntimeException('Sebagian Surat Jalan tidak ditemukan atau bukan expedisi.');
                }

                if ($existingGb) {
                    $oldRows = Expedisi::where('GB', $existingGb)
                        ->where('JENIS', 'EKS')->orderBy('id')->lockForUpdate()->get();
                    if ($oldRows->isEmpty()) {
                        throw new \RuntimeException('Grup gabungan tidak ditemukan. Muat ulang halaman.');
                    }
                    if ($oldRows->contains(fn (Expedisi $row) => filled($row->INVOICE))) {
                        throw new \RuntimeException('Grup ini sudah menjadi invoice dan tidak dapat diubah.');
                    }
                    $oldRows->each(function (Expedisi $row) {
                        $row->update([
                            'HARGA' => $row->hargaaw, 'NDISC' => $row->NDISCAW, 'DC' => $row->DCAW,
                            'TOTAL' => 0, 'PPN' => 0, 'GRAND' => 0, 'PIUTANG' => 0,
                            'GB' => null, 'PESANANGB' => null, 'STS' => null,
                        ]);
                    });
                    $gb = $existingGb;
                    // Re-read after rollback so backups come from the original SJ values,
                    // not from the old group's master/detail overrides.
                    $rows = Expedisi::whereIn('NOSJ', $data['nosj_list'])
                        ->where('JENIS', 'EKS')->orderBy('NOSJ')->lockForUpdate()->get();
                } else {
                    $gb = $this->generateGB();
                }

                foreach ($rows as $row) {
                    if ($row->INVOICE || ($row->GB && $row->GB !== $existingGb)) {
                        throw new \RuntimeException("SJ {$row->NOSJ} sudah diproses atau tergabung di grup lain.");
                    }
                }
                $customerCodes = $rows->map(fn (Expedisi $row) => trim((string) $row->CUSTOMER_KODE))->unique();
                if ($customerCodes->count() !== 1 || $customerCodes->first() === '') {
                    throw new \RuntimeException('Kode customer SJ tidak sama atau belum terisi. Periksa data SJ terlebih dahulu.');
                }
                if ($rows->pluck('JENISHRG')->filter()->unique()->count() > 1) {
                    throw new \RuntimeException('Jenis harga/pesanan harus sama untuk satu gabungan.');
                }

                $harga = (float) $data['harga'];
                $diskon = (float) ($data['diskon'] ?? 0);
                $dc = (float) ($data['dc'] ?? 0);
                $ppn = (float) ($data['ppn'] ?? 11);
                $ndisc = round($harga * $diskon / 100);
                $total = max(0, round($harga - $ndisc + $dc));
                $grand = round($total + ($total * $ppn / 100));

                foreach ($rows as $index => $row) {
                    $row->hargaaw = $row->HARGA;
                    $row->NDISCAW = $row->NDISC;
                    $row->DCAW = $row->DC;
                    $row->GB = $gb;
                    $row->PESANANGB = $data['item'] ?? $row->PESANAN;
                    $row->STS = 'GABUNG';
                    if ($index === 0) {
                        $row->HARGA = $harga; $row->DISC = $diskon; $row->NDISC = $ndisc;
                        $row->DC = $dc; $row->TOTAL = $total; $row->PPN = $ppn;
                        $row->GRAND = $grand; $row->PIUTANG = $grand;
                    } else {
                        $row->HARGA = 0; $row->DISC = 0; $row->NDISC = 0; $row->DC = 0;
                        $row->TOTAL = 0; $row->PPN = 0; $row->GRAND = 0; $row->PIUTANG = 0;
                    }
                    $row->USERINV = (string) ($request->user()->user_id ?? $request->user()->id) . '-' . now()->format('d-m-Y H:i:s');
                    $row->save();
                }
                return $gb;
            });

            return response()->json(['status' => true, 'message' => 'Gabung SJ berhasil disimpan.', 'gb' => $gb]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function generateGB(): string
    {
        $date = now()->format('Ymd');
        $last = Expedisi::where('GB', 'like', $date . '%')->lockForUpdate()->orderByDesc('GB')->value('GB');
        return $date . str_pad(((int) substr((string) $last, -4)) + 1, 4, '0', STR_PAD_LEFT);
    }

    private function formatRow(Expedisi $row, int $index, bool $detail = false): array
    {
        return array_merge($row->toArray(), [
            'DT_RowIndex' => $index,
            'TGLMUAT' => $row->TGLMUAT?->format('d-m-Y') ?? '-',
            'JUMLAH' => number_format((float) $row->JUMLAH, 0, ',', '.') . ' ' . ($row->UNIT ?? ''),
            'harga_formatted' => 'Rp ' . number_format((float) $row->HARGA, 0, ',', '.'),
            'dc_formatted' => 'Rp ' . number_format((float) $row->DC, 0, ',', '.'),
            'gtotal_formatted' => 'Rp ' . number_format((float) $row->GRAND, 0, ',', '.'),
            'total_formatted' => 'Rp ' . number_format((float) $row->GRAND, 0, ',', '.'),
            'jumlahreal' => $row->JUMLAH,
        ]);
    }
}
