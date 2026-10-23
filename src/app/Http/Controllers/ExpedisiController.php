<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expedisi;
use App\Models\Mcustomer;
use App\Models\Kendaraan;
use App\Models\Driver;
use App\Models\PriceCus;
use App\Models\Pajak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Mpdf\Mpdf;

class ExpedisiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $filterDate = $request->input('date');
        $filterStatus = $request->input('status');

        $expedisis = Expedisi::query()
            ->where('JENIS', 'EKS')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('NOSJ', 'like', "%{$search}%")
                        ->orWhere('INVOICE', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%");
                });
            })
            ->when($filterDate, function ($query, $date) {
                $query->whereDate('tglsj', $date);
            })
            ->when($filterStatus, function ($query, $status) {
                $query->where('STS', $status);
            })
            ->orderByDesc('tglsj')
            ->orderByDesc('NOSJ')
            ->paginate(20)
            ->withQueryString();

        return view('expedisi.index', compact('expedisis', 'search'));
    }

    public function create()
    {
        $customers = Mcustomer::orderBy('CUSTOMER')->get(['CUSTOMER', 'NAMACUST', 'kode_cus']);
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $drivers = Driver::orderBy('KODE')->get(['KODE', 'NAMA']);
        $rutes = \App\Models\Rute::orderBy('FROM')->get(['FROM', 'TO', 'RUTE', 'HARGA']);

        $ppnRate = (float) (Pajak::where('is_active', true)->latest('updated_at')->value('PPN') ?? 0);

        return view('expedisi.create', compact('customers', 'kendaraans', 'drivers', 'rutes', 'ppnRate'));
    }

    public function dataItems(Request $request)
    {
        $customer = trim((string) $request->input('customer'));
        $search = trim((string) $request->input('q'));

        if ($customer === '') {
            return response()->json([]);
        }

        $items = PriceCus::query()
            ->where('KODECUS', $customer)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('KETERANGAN', 'like', "%{$search}%")
                        ->orWhere('RUTE', 'like', "%{$search}%")
                        ->orWhere('DARI', 'like', "%{$search}%")
                        ->orWhere('SAMPAI', 'like', "%{$search}%");
                });
            })
            ->orderBy('KETERANGAN')
            ->paginate(10)
            ->withQueryString();

        return response()->json($items);
    }

    public function dataCustomers(Request $request)
    {
        $search = trim((string) $request->input('q'));

        return response()->json(Mcustomer::query()
            ->when($search, fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('CUSTOMER', 'like', "%{$search}%")
                    ->orWhere('kode_cus', 'like', "%{$search}%")
                    ->orWhere('NAMACUST', 'like', "%{$search}%");
            }))
            ->orderBy('CUSTOMER')
            ->paginate(10)
            ->withQueryString());
    }

    public function dataDrivers(Request $request)
    {
        $search = trim((string) $request->input('q'));
        $drivers = Driver::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhereNull('is_active'))
            ->when($search, fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('KODE', 'like', "%{$search}%")
                    ->orWhere('NAMA', 'like', "%{$search}%");
            }))
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        $drivers->getCollection()->transform(fn (Driver $driver) => [
            'id' => $driver->id,
            'code' => $driver->KODE ?: $driver->code,
            'name' => $driver->display_name,
        ]);

        return response()->json($drivers);
    }

    public function dataVehicles(Request $request)
    {
        $search = trim((string) $request->input('q'));
        $vehicles = Kendaraan::query()
            ->when($search, fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('KODE', 'like', "%{$search}%")
                    ->orWhere('NAMA', 'like', "%{$search}%");
            }))
            ->orderBy('KODE')
            ->paginate(10)
            ->withQueryString();

        $vehicles->getCollection()->transform(fn (Kendaraan $vehicle) => [
            'id' => $vehicle->id,
            'code' => $vehicle->KODE,
            'name' => $vehicle->NAMA,
        ]);

        return response()->json($vehicles);
    }

    public function previewNoSuratJalan()
    {
        return response()->json(['nosj' => $this->nextNoSuratJalan()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->suratJalanRules(), [
            'NOSJ.unique' => 'No Surat Jalan sudah terdaftar.',
            'tglsj.required' => 'Tanggal Surat Jalan wajib diisi.',
            'CUSTOMER_KODE.required' => 'Customer wajib dipilih.',
            'rute.required' => 'Rute wajib diisi.',
            'JUMLAH.required' => 'Jumlah wajib diisi.',
            'HARGA.required' => 'Harga wajib diisi.',
        ]);

        $validated['JENIS'] = 'EKS';
        $validated['STS'] = $validated['STS'] ?? 'INVOICE';
        $validated['SIMPAN'] = $validated['SIMPAN'] ?? 'N';
        $validated['READY'] = $validated['READY'] ?? 'Y';
        $validated['CLOSSING'] = $validated['CLOSSING'] ?? 'N';
        $validated['KETERANGAN'] = $validated['KETERANGAN'] ?? 'EXPEDISI BARU';
        $validated['user'] = $request->user()->name ?? 'SYSTEM';
        $validated['USEREDIT'] = $request->user()->name ?? 'system';
        $validated = $this->calculateSuratJalan($validated, (float) (Pajak::where('is_active', true)->latest('updated_at')->value('PPN') ?? 0));

        $expedisi = DB::transaction(function () use ($validated) {
            $validated['NOSJ'] = $validated['NOSJ'] ?: $this->generateNoSuratJalan();
            return Expedisi::create($validated);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Surat Jalan berhasil disimpan.',
                'data' => $expedisi,
                'print_url' => route('expedisi.print.surjal', $expedisi),
                'edit_url' => route('expedisi.edit', $expedisi),
            ]);
        }

        return redirect()
            ->route('expedisi.index')
            ->with('success', 'Ekspedisi berhasil disimpan.');
    }

    public function show(Expedisi $expedisi)
    {
        return view('expedisi.show', compact('expedisi'));
    }

    public function edit(Expedisi $expedisi)
    {
        $customers = Mcustomer::orderBy('CUSTOMER')->get(['CUSTOMER', 'NAMACUST', 'kode_cus']);
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $drivers = Driver::orderBy('KODE')->get(['KODE', 'NAMA']);
        $rutes = \App\Models\Rute::orderBy('FROM')->get(['FROM', 'TO', 'RUTE', 'HARGA']);

        $ppnRate = (float) (Pajak::where('is_active', true)->latest('updated_at')->value('PPN') ?? 0);

        return view('expedisi.edit', compact('expedisi', 'customers', 'kendaraans', 'drivers', 'rutes', 'ppnRate'));
    }

    public function update(Request $request, Expedisi $expedisi)
    {
        if ($expedisi->INVOICE || $expedisi->GB) {
            return back()->with('error', 'Surat Jalan yang sudah masuk invoice atau GB tidak dapat diedit.');
        }

        $rules = $this->suratJalanRules();
        $rules['NOSJ'] = ['nullable', 'string', 'max:20', Rule::unique('expedisis', 'NOSJ')->ignore($expedisi->id)];
        $validated = $request->validate($rules);

        $validated['USEREDIT'] = $request->user()->name ?? 'system';
        $validated['JENIS'] = 'EKS';
        $validated['KETERANGAN'] = $validated['KETERANGAN'] ?? $expedisi->KETERANGAN ?? 'EXPEDISI BARU';
        $validated = $this->calculateSuratJalan($validated, (float) (Pajak::where('is_active', true)->latest('updated_at')->value('PPN') ?? 0));

        $expedisi->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Surat Jalan berhasil diperbarui.',
                'data' => $expedisi->fresh(),
                'print_url' => route('expedisi.print.surjal', $expedisi),
                'edit_url' => route('expedisi.edit', $expedisi),
            ]);
        }

        return redirect()
            ->route('expedisi.index')
            ->with('success', 'Ekspedisi berhasil diperbarui.');
    }

    public function destroy(Expedisi $expedisi)
    {
        if ($expedisi->INVOICE) {
            return redirect()
                ->route('expedisi.index')
                ->with('error', 'Ekspedisi yang sudah diinvoice tidak dapat dihapus.');
        }

        $expedisi->delete();

        return redirect()
            ->route('expedisi.index')
            ->with('success', 'Ekspedisi berhasil dihapus.');
    }

    public function getDataCustomer(Request $request)
    {
        $search = $request->input('q');
        $customers = Mcustomer::query()
            ->when($search, fn($q) => $q->where('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('NAMACUST', 'like', "%{$search}%"))
            ->limit(10)
            ->get(['CUSTOMER', 'NAMACUST', 'kode_cus', 'ALAMAT1', 'TELEPON']);

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

    public function getKmTerakhir(Request $request, $kendaraan = null)
    {
        $km = Expedisi::query()
            ->where('KENDARAAN', $kendaraan)
            ->orderBy('TGLMUAT', 'desc')
            ->value('JUMLAH');

        return response()->json(['km_terakhir' => $km ?? 0]);
    }

    private function suratJalanRules(): array
    {
        return [
            'NOSJ' => 'nullable|string|max:20|unique:expedisis,NOSJ',
            'tglsj' => 'required|date',
            'CUSTOMER_KODE' => 'required|string|max:20',
            'CUSTOMER' => 'nullable|string|max:30',
            'PESANAN' => 'nullable|string|max:100',
            'JENISHRG' => 'nullable|string|max:1',
            'STS' => 'nullable|string|max:20',
            'SIMPAN' => 'nullable|string|max:1',
            'READY' => 'nullable|string|max:1',
            'CLOSSING' => 'nullable|string|max:1',
            'user' => 'nullable|string|max:50',
            'KENDARAAN' => 'nullable|string|max:20',
            'NAMA_KENDARAAN' => 'nullable|string|max:50',
            'DRIVER' => 'nullable|string|max:20',
            'NAMA_DRIVER' => 'nullable|string|max:50',
            'DRIVER2' => 'nullable|string|max:20',
            'NAMA_DRIVER2' => 'nullable|string|max:50',
            'P_PENERIMA' => 'nullable|string|max:100',
            'P_NAMA' => 'nullable|string|max:50',
            'P_PHONE' => 'nullable|string|max:50',
            'P_ALAMAT' => 'nullable|string|max:100',
            'barang' => 'nullable|string|max:100',
            'penyimpanan' => 'nullable|string|in:F,C,D',
            'koli' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
            'rute' => 'required|string|max:30',
            'JUMLAH' => 'required|numeric|min:0',
            'UNIT' => 'nullable|string|max:10',
            'HARGA' => 'required|numeric|min:0',
            'hargaaw' => 'nullable|numeric|min:0',
            'DISC' => 'nullable|numeric|min:0|max:100',
            'DC' => 'nullable|numeric|min:0',
            'DCAW' => 'nullable|numeric|min:0',
            'PPN' => 'nullable|numeric|min:0|max:100',
            'KETERANGAN' => 'nullable|string|max:500',
            'WILAYAH' => 'required|string|in:denpasar,gianyar',
            'BAYAR' => 'nullable|numeric|min:0',
            'PIUTANG' => 'nullable|numeric|min:0',
            'area_id' => 'nullable|integer',
            'area_name' => 'nullable|string|max:100',
        ];
    }

    private function calculateSuratJalan(array $data, float $ppnRate): array
    {
        $subtotal = round((float) ($data['JUMLAH'] ?? 0) * (float) ($data['HARGA'] ?? 0));
        $discount = round($subtotal * ((float) ($data['DISC'] ?? 0) / 100));
        $total = $subtotal - $discount;
        $ppn = round($total * ($ppnRate / 100));
        $deliveryCharge = (float) ($data['DC'] ?? 0);

        $data['NDISC'] = $discount;
        $data['NDISCAW'] = $discount;
        $data['TOTAL'] = $total;
        $data['GRAND'] = $total + $ppn + $deliveryCharge;
        $data['PPN'] = $ppnRate;
        $data['PIUTANG'] = $data['PIUTANG'] ?? $data['GRAND'];
        $data['UNIT'] = $data['UNIT'] ?: 'KG';
        $data['hargaaw'] = $data['hargaaw'] ?? $data['HARGA'];
        $data['DCAW'] = $data['DCAW'] ?? $data['DC'];

        return $data;
    }

    private function generateNoSuratJalan(): string
    {
        return $this->nextNoSuratJalan();
    }

    private function nextNoSuratJalan(): string
    {
        $prefix = 'SJ'.now()->format('my');
        $last = Expedisi::where('NOSJ', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('NOSJ')
            ->first();

        $next = $last ? ((int) substr($last->NOSJ, -5)) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function printSuratJalan(Expedisi $expedisi)
    {
        $expedisi->PLAT = Kendaraan::query()
            ->where('KODE', $expedisi->KENDARAAN)
            ->value('PLAT');

        $user = auth()->user();
        if ($user->role_old !== 'admin' && $expedisi->user_id !== $user->user_id) {
            abort(403, 'Anda tidak berhak mencetak surat jalan ini');
        }

        $tempPath = storage_path('app/mpdf-temp');
        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'tempDir' => $tempPath,
        ]);

        $html = view('expedisi.pdf.surjal', compact('expedisi'))->render();
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('Surat-Jalan-'.$expedisi->NOSJ.'.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }

    public function pdfMuat(Request $request, $nomuat = null)
    {
        $expedisis = Expedisi::where('NOSJ', 'like', "%{$request->input('nosj_filter', '')}%")
            ->whereNotNull('NOSJ')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('expedisi.pdf.muat', compact('expedisis'));

        return $pdf->stream('muat.pdf', ['stream' => true]);
    }

}
