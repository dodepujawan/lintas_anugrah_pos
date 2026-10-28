<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expedisi;
use App\Models\Mcustomer;
use App\Models\Kendaraan;
use App\Models\Driver;
use App\Models\Pricedingincus;
use Illuminate\Http\Request;

class RentPendinginController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $rents = Expedisi::query()
            ->where('JENIS', 'REN')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('NOSJ', 'like', "%{$search}%")
                        ->orWhere('INVOICE', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('TGLMUAT')
            ->paginate(20)
            ->withQueryString();

        return view('rent-pendingin.index', compact('rents', 'search'));
    }

    public function create()
    {
        $customers = Mcustomer::orderBy('CUSTOMER')->get(['CUSTOMER', 'NAMACUST']);
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $drivers = Driver::orderBy('KODE')->get(['KODE', 'NAMA']);

        return view('rent-pendingin.create', compact('customers', 'kendaraans', 'drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TGLMUAT' => 'nullable|date',
            'NOSJ' => 'nullable|string|max:20',
            'NOMUAT' => 'nullable|string|max:20',
            'CUSTOMER' => 'nullable|string|max:100',
            'KENDARAAN' => 'nullable|string|max:20',
            'DRIVER' => 'nullable|string|max:20',
            'JUMLAH' => 'nullable|numeric|min:0',
            'UNIT' => 'nullable|string|max:10',
            'HARGA' => 'nullable|numeric|min:0',
            'DISC' => 'nullable|numeric|min:0|max:100',
            'DC' => 'nullable|numeric|min:0',
            'GRAND' => 'nullable|numeric|min:0',
            'rute' => 'nullable|string|max:30',
            'PESANAN' => 'nullable|string|max:100',
            'STS' => 'nullable|string|max:20',
            'JENIS' => 'nullable|string|max:3',
        ]);

        $validated['JENIS'] = $validated['JENIS'] ?? 'REN';
        $validated['NOSJ'] = $validated['NOSJ'] ?: $this->generateNoSuratJalan();
        $validated['USER'] = $request->user()?->name ?? 'system';
        $validated['USEREDIT'] = $request->user()?->name ?? 'system';
        $validated['READY'] = 'Y';
        $validated['CLOSSING'] = 'N';
        $validated['catatan'] = $validated['catatan'] ?? 'RENT PENDINGIN';

        Expedisi::create($validated);

        return redirect()->route('rent-pendingin.index')
            ->with('success', 'Surat jalan rent pendingin berhasil disimpan.');
    }

    public function edit(Expedisi $rentPendingin)
    {
        $customers = Mcustomer::orderBy('CUSTOMER')->get(['CUSTOMER', 'NAMACUST']);
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $drivers = Driver::orderBy('KODE')->get(['KODE', 'NAMA']);

        return view('rent-pendingin.edit', compact('rentPendingin', 'customers', 'kendaraans', 'drivers'));
    }

    public function update(Request $request, Expedisi $rentPendingin)
    {
        $validated = $request->validate([
            'TGLMUAT' => 'nullable|date',
            'NOSJ' => 'nullable|string|max:20',
            'NOMUAT' => 'nullable|string|max:20',
            'CUSTOMER' => 'nullable|string|max:100',
            'KENDARAAN' => 'nullable|string|max:20',
            'DRIVER' => 'nullable|string|max:20',
            'JUMLAH' => 'nullable|numeric|min:0',
            'UNIT' => 'nullable|string|max:10',
            'HARGA' => 'nullable|numeric|min:0',
            'DISC' => 'nullable|numeric|min:0|max:100',
            'DC' => 'nullable|numeric|min:0',
            'GRAND' => 'nullable|numeric|min:0',
            'rute' => 'nullable|string|max:30',
            'PESANAN' => 'nullable|string|max:100',
            'STS' => 'nullable|string|max:20',
            'JENIS' => 'nullable|string|max:3',
        ]);

        $validated['USEREDIT'] = $request->user()?->name ?? 'system';
        $validated['JENIS'] = $validated['JENIS'] ?? $rentPendingin->JENIS ?? 'REN';

        $rentPendingin->update($validated);

        return redirect()->route('rent-pendingin.index')
            ->with('success', 'Surat jalan rent pendingin berhasil diupdate.');
    }

    public function destroy(Expedisi $rentPendingin)
    {
        $rentPendingin->delete();

        return redirect()->route('rent-pendingin.index')
            ->with('success', 'Surat jalan rent pendingin berhasil dihapus.');
    }

    public function printSurjal(string $nosj)
    {
        $expedisi = Expedisi::where('NOSJ', $nosj)->where('JENIS', 'REN')->firstOrFail();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('rent-pendingin.pdf.surjal', compact('expedisi'));

        return $pdf->stream('surat-jalan-rent-'.$nosj.'.pdf', ['Attachment' => false]);
    }

    public function dataSurjal(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $dateFrom = $request->input('tgl_mulai');
        $dateTo = $request->input('tgl_akhir');

        $rows = Expedisi::query()
            ->where('JENIS', 'REN')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('NOSJ', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%")
                        ->orWhere('PESANAN', 'like', "%{$search}%");
                });
            })
            ->when($dateFrom, fn ($query) => $query->whereDate('tglsj', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('tglsj', '<=', $dateTo))
            ->orderByDesc('tglsj')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'data' => $rows->items(),
            'current_page' => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
        ]);
    }

    public function dataMuat(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $dateFrom = $request->input('tgl_mulai');
        $dateTo = $request->input('tgl_akhir');

        $rows = Expedisi::query()
            ->where('JENIS', 'REN')
            ->whereNotNull('NOMUAT')
            ->where('NOMUAT', '!=', '')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('NOMUAT', 'like', "%{$search}%")
                        ->orWhere('NOSJ', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%")
                        ->orWhere('PESANAN', 'like', "%{$search}%");
                });
            })
            ->when($dateFrom, fn ($query) => $query->whereDate('TGLMUAT', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('TGLMUAT', '<=', $dateTo))
            ->orderByDesc('TGLMUAT')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'data' => $rows->items(),
            'current_page' => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
        ]);
    }

    public function showSurjal(string $nosj)
    {
        $row = Expedisi::query()
            ->where('NOSJ', $nosj)
            ->where('JENIS', 'REN')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $row->id,
                'NOSJ' => $row->NOSJ,
                'tglsj' => $row->tglsj?->format('Y-m-d'),
                'TGLMUAT' => $row->TGLMUAT?->format('Y-m-d'),
                'NOMUAT' => $row->NOMUAT,
                'WILAYAH' => $row->WILAYAH,
                'CUSTOMER_KODE' => $row->CUSTOMER_KODE,
                'CUSTOMER' => $row->CUSTOMER,
                'P_NAMA' => $row->P_NAMA,
                'P_PHONE' => $row->P_PHONE,
                'P_ALAMAT' => $row->P_ALAMAT,
                'PESANAN' => $row->PESANAN,
                'DRIVER' => $row->DRIVER,
                'NAMA_DRIVER' => $row->NAMA_DRIVER,
                'KENDARAAN' => $row->KENDARAAN,
                'NAMA_KENDARAAN' => $row->NAMA_KENDARAAN,
                'JUMLAH' => (float) ($row->JUMLAH ?? 0),
                'HARGA' => (float) ($row->HARGA ?? 0),
                'DISC' => (float) ($row->DISC ?? 0),
                'PPN' => (float) ($row->PPN ?? 0),
                'TOTAL' => (float) ($row->TOTAL ?? 0),
                'GRAND' => (float) ($row->GRAND ?? 0),
                'catatan' => $row->catatan,
            ],
        ]);
    }

    public function showMuat(string $nomuat)
    {
        $row = Expedisi::query()
            ->where('NOMUAT', $nomuat)
            ->where('JENIS', 'REN')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $row->id,
                'NOSJ' => $row->NOSJ,
                'tglsj' => $row->tglsj?->format('Y-m-d'),
                'TGLMUAT' => $row->TGLMUAT?->format('Y-m-d'),
                'NOMUAT' => $row->NOMUAT,
                'WILAYAH' => $row->WILAYAH,
                'CUSTOMER_KODE' => $row->CUSTOMER_KODE,
                'CUSTOMER' => $row->CUSTOMER,
                'P_NAMA' => $row->P_NAMA,
                'P_PHONE' => $row->P_PHONE,
                'P_ALAMAT' => $row->P_ALAMAT,
                'PESANAN' => $row->PESANAN,
                'DRIVER' => $row->DRIVER,
                'NAMA_DRIVER' => $row->NAMA_DRIVER,
                'KENDARAAN' => $row->KENDARAAN,
                'NAMA_KENDARAAN' => $row->NAMA_KENDARAAN,
                'JUMLAH' => (float) ($row->JUMLAH ?? 0),
                'HARGA' => (float) ($row->HARGA ?? 0),
                'DISC' => (float) ($row->DISC ?? 0),
                'PPN' => (float) ($row->PPN ?? 0),
                'TOTAL' => (float) ($row->TOTAL ?? 0),
                'GRAND' => (float) ($row->GRAND ?? 0),
                'catatan' => $row->catatan,
            ],
        ]);
    }

    private function generateNoSuratJalan(): string
    {
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $prefix = 'SJ'.now()->format('my');
            $last = Expedisi::where('NOSJ', 'like', $prefix.'%')
                ->orderByDesc('NOSJ')
                ->lockForUpdate()
                ->first();

            $number = 1;
            if ($last && preg_match('/(\d+)$/', $last->NOSJ, $matches)) {
                $number = (int) $matches[1] + 1;
            }

            return $prefix.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
        });
    }

    public function getDataCustomer(Request $request)
    {
        $search = $request->input('q');
        $customers = Mcustomer::query()
            ->when($search, fn($q) => $q->where('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('NAMACUST', 'like', "%{$search}%"))
            ->limit(10)
            ->get(['CUSTOMER', 'NAMACUST', 'ALAMAT1', 'TELEPON']);

        return response()->json($customers);
    }

    public function getRuteMuat(Request $request, $kodedap = null)
    {
        $rutes = \App\Models\Rute::query()
            ->when($kodedap, fn($q) => $q->where('FROM', $kodedap))
            ->limit(50)
            ->get(['FROM', 'TO', 'RUTE', 'HARGA']);

        return response()->json($rutes);
    }

    protected function getKmTerakhir($kendaraan)
    {
        return Expedisi::where('KENDARAAN', $kendaraan)->where('JENIS', 'REN')
            ->orderBy('TGLMUAT', 'desc')
            ->value('JUMLAH');
    }

    public function priceItems(string $kodecus, Request $request)
    {
        $customer = Mcustomer::where('CUSTOMER', $kodecus)->firstOrFail();
        $search = trim((string) $request->input('q'));

        $items = Pricedingincus::query()
            ->where('KODECUS', $kodecus)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ITEM', 'like', "%{$search}%")
                        ->orWhere('KODE', 'like', "%{$search}%")
                        ->orWhere('PERIODE', 'like', "%{$search}%")
                        ->orWhere('PLAT', 'like', "%{$search}%")
                        ->orWhere('JENIS', 'like', "%{$search}%");
                });
            })
            ->orderBy('PERIODE')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'customer_kode' => $customer->CUSTOMER,
            'customer_nama' => trim((string) $customer->NAMACUST),
            'data' => $items->items(),
            'current_page' => $items->currentPage(),
            'last_page' => $items->lastPage(),
        ]);
    }
}
