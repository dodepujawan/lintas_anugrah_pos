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

    public function createMuat()
    {
        return view('expedisi.create_muat');
    }

    public function dataSJTersedia(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $suratJalan = Expedisi::query()
            ->where('JENIS', 'EKS')
            ->where(fn ($query) => $query->whereNull('NOMUAT')->orWhere('NOMUAT', ''))
            ->where(fn ($query) => $query->whereNull('GB')->orWhere('GB', ''))
            ->where(fn ($query) => $query->whereNull('INVOICE')->orWhere('INVOICE', ''))
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('NOSJ', 'like', "%{$search}%")
                ->orWhere('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('rute', 'like', "%{$search}%")))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('tglsj', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('tglsj', '<=', $request->input('date_to')))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        $suratJalan->getCollection()->transform(fn (Expedisi $row) => [
            'id' => $row->id,
            'NOSJ' => trim((string) $row->NOSJ),
            'tglsj' => $row->tglsj?->format('Y-m-d'),
            'CUSTOMER' => trim((string) $row->CUSTOMER),
            'rute' => trim((string) $row->rute),
            'JUMLAH' => (float) $row->JUMLAH,
            'UNIT' => trim((string) $row->UNIT),
            'HARGA' => (float) $row->HARGA,
            'DISC' => (float) $row->DISC,
            'DC' => (float) $row->DC,
            'GRAND' => (float) $row->GRAND,
        ]);

        return response()->json($suratJalan);
    }

    public function showSurjal(Request $request)
    {
        $suratJalan = Expedisi::query()
            ->whereKey($request->input('id'))
            ->where('JENIS', 'EKS')
            ->where(fn ($query) => $query->whereNull('NOMUAT')->orWhere('NOMUAT', ''))
            ->where(fn ($query) => $query->whereNull('GB')->orWhere('GB', ''))
            ->where(fn ($query) => $query->whereNull('INVOICE')->orWhere('INVOICE', ''))
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $suratJalan->id,
                'no_sj' => trim((string) $suratJalan->NOSJ),
                'tgl_sj' => $suratJalan->tglsj?->format('d-m-Y'),
                'customer' => trim((string) $suratJalan->CUSTOMER),
                'rute' => trim((string) $suratJalan->rute),
                'jumlah' => (float) $suratJalan->JUMLAH,
                'unit' => trim((string) $suratJalan->UNIT),
                'jenis' => trim((string) $suratJalan->JENISHRG),
                'harga' => (float) $suratJalan->HARGA,
                'disc' => (float) $suratJalan->DISC,
                'dc' => (float) $suratJalan->DC,
                'ppn' => (float) $suratJalan->PPN,
                'total' => (float) $suratJalan->GRAND,
                'pengirim' => trim((string) $suratJalan->PENGIRIM),
            ],
        ]);
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
                    ->orWhere('NAMA', 'like', "%{$search}%")
                    ->orWhere('PLAT', 'like', "%{$search}%");
            }))
            ->orderBy('KODE')
            ->paginate(10)
            ->withQueryString();

        $vehicles->getCollection()->transform(fn (Kendaraan $vehicle) => [
            'id' => $vehicle->id,
            'code' => $vehicle->KODE,
            'name' => $vehicle->NAMA,
            'plat' => $vehicle->PLAT,
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
            ->when($request->filled('search'), fn ($query) => $query->where('RUTE', 'like', '%'.$request->input('search').'%'))
            ->orderBy('RUTE')
            ->paginate(15, ['id', 'FROM', 'TO', 'RUTE', 'HARGA']);

        return response()->json($rutes);
    }

    public function getKmTerakhir(Request $request, $kendaraan = null)
    {
        $kendaraan ??= $request->input('kendaraan');
        $km = Expedisi::query()
            ->where('KENDARAAN', $kendaraan)
            ->whereNotNull('KM_AKHIR')
            ->orderByDesc('id')
            ->value('KM_AKHIR');

        return response()->json(['km_terakhir' => (float) ($km ?? 0), 'km_akhir' => (float) ($km ?? 0)]);
    }

    public function getDataMuat(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $muatan = Expedisi::query()
            ->where('JENIS', 'EKS')
            ->whereNotNull('NOMUAT')
            ->where('NOMUAT', '!=', '')
            ->where(fn ($query) => $query->whereNull('INVOICE')->orWhere('INVOICE', ''))
            ->where(fn ($query) => $query->whereNull('GB')->orWhere('GB', ''))
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('NOMUAT', 'like', "%{$search}%")
                ->orWhere('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('rute', 'like', "%{$search}%")
                ->orWhere('NOSJ', 'like', "%{$search}%")))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('TGLMUAT', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('TGLMUAT', '<=', $request->input('date_to')))
            ->selectRaw('MIN(id) as id, NOMUAT')
            ->groupBy('NOMUAT')
            ->orderByDesc(DB::raw('MAX(TGLMUAT)'))
            ->paginate(15);

        $muatan->getCollection()->transform(function ($row) {
            $first = Expedisi::query()->where('NOMUAT', $row->NOMUAT)->orderBy('id')->first();

            return [
                'id' => $first?->id,
                'NOMUAT' => trim((string) $row->NOMUAT),
                'TGLMUAT' => $first?->TGLMUAT?->format('Y-m-d'),
                'CUSTOMER' => trim((string) ($first?->CUSTOMER ?? '')),
                'rute' => trim((string) ($first?->rute ?? '')),
                'NOSJ' => trim((string) ($first?->NOSJ ?? '')),
                'JUMLAH' => (float) ($first?->JUMLAH ?? 0),
                'UNIT' => trim((string) ($first?->UNIT ?? '')),
                'HARGA' => (float) ($first?->HARGA ?? 0),
                'DISC' => (float) ($first?->DISC ?? 0),
                'DC' => (float) ($first?->DC ?? 0),
                'GRAND' => (float) ($first?->GRAND ?? 0),
            ];
        });

        return response()->json($muatan);
    }

    public function showMuat(Request $request)
    {
        $nomuat = trim((string) $request->input('nomuat'));
        abort_if($nomuat === '', 422, 'No Muat wajib dipilih.');

        $rows = Expedisi::query()
            ->where('NOMUAT', $nomuat)
            ->where('JENIS', 'EKS')
            ->where(fn ($query) => $query->whereNull('INVOICE')->orWhere('INVOICE', ''))
            ->where(fn ($query) => $query->whereNull('GB')->orWhere('GB', ''))
            ->orderByDesc('id')
            ->get();

        abort_if($rows->isEmpty(), 404, 'Data No Muat tidak ditemukan.');

        return response()->json([
            'success' => true,
            'nomuat' => $nomuat,
            'data' => $rows->map(fn (Expedisi $row) => [
                'id' => $row->id,
                'nomuat' => trim((string) $row->NOMUAT),
                'tglmuat' => $row->TGLMUAT?->format('Y-m-d'),
                'rute' => trim((string) $row->rute),
                'plat_nomor' => trim((string) $row->PLAT_NOMOR),
                'driver1_kode' => trim((string) $row->DRIVER),
                'driver1' => trim((string) $row->NAMA_DRIVER),
                'driver2_kode' => trim((string) $row->DRIVER2),
                'driver2' => trim((string) $row->NAMA_DRIVER2),
                'kendaraan_kode' => trim((string) $row->KENDARAAN),
                'kendaraan' => trim((string) $row->NAMA_KENDARAAN),
                'km_awal' => (float) $row->KM_AWAL,
                'km_akhir' => (float) $row->KM_AKHIR,
                'uang_jalan' => (float) $row->UANG_JALAN,
                'uang_driver_makan' => (float) $row->UANG_DRIVER_MAKAN,
                'uang_lain_lain' => (float) $row->UANG_LAIN_LAIN,
                'pengirim' => trim((string) $row->PENGIRIM),
                'nosj' => trim((string) $row->NOSJ),
                'tgl_sj' => $row->tglsj?->format('d-m-Y'),
                'customer' => trim((string) $row->CUSTOMER),
                'dc' => (float) $row->DC,
                'jumlah' => (float) $row->JUMLAH,
                'unit' => trim((string) $row->UNIT),
                'jenis' => trim((string) $row->JENIS),
                'jenishrg' => trim((string) $row->JENISHRG),
                'harga' => (float) $row->HARGA,
                'disc' => (float) $row->DISC,
                'ppn' => (float) $row->PPN,
                'total' => (float) $row->GRAND,
            ]),
        ]);
    }

    public function storeMuat(Request $request)
    {
        $data = $request->validate([
            'nosj' => ['required', 'array', 'min:1'],
            'nosj.*' => ['required', 'string', 'max:20'],
            'tglmuat' => ['required', 'date'],
            'rute' => ['nullable', 'string', 'max:30'],
            'plat_nomor' => ['nullable', 'string', 'max:30'],
            'driver1_kode' => ['nullable', 'string', 'max:20'],
            'driver1' => ['nullable', 'string', 'max:50'],
            'driver2_kode' => ['nullable', 'string', 'max:20'],
            'driver2' => ['nullable', 'string', 'max:50'],
            'kendaraan_kode' => ['nullable', 'string', 'max:20'],
            'kendaraan' => ['nullable', 'string', 'max:50'],
            'km_awal' => ['nullable', 'numeric', 'min:0'],
            'km_akhir' => ['nullable', 'numeric', 'min:0', 'gte:km_awal'],
            'uang_jalan' => ['nullable', 'numeric', 'min:0'],
            'uang_driver_makan' => ['nullable', 'numeric', 'min:0'],
            'uang_lain_lain' => ['nullable', 'numeric', 'min:0'],
            'pengirim' => ['nullable', 'array'],
            'pengirim.*.nosj' => ['required_with:pengirim', 'string', 'max:20'],
            'pengirim.*.pengirim' => ['nullable', 'string', 'max:100'],
        ]);

        $nomuat = DB::transaction(function () use ($data) {
            $rows = Expedisi::query()->whereIn('NOSJ', $data['nosj'])->lockForUpdate()->get();
            abort_if($rows->count() !== count(array_unique($data['nosj'])), 422, 'Sebagian Surat Jalan tidak ditemukan.');
            abort_if($rows->contains(fn (Expedisi $row) => $row->JENIS !== 'EKS' || trim((string) $row->NOMUAT) !== '' || trim((string) $row->INVOICE) !== '' || trim((string) $row->GB) !== ''), 422, 'Surat Jalan sudah dimuat, tergabung, atau diinvoice. Muat ulang daftar SJ.');

            $nomuat = $this->generateNomuatWithLock();
            $header = $this->muatHeaderValues($data, $nomuat, $this->generateNoJalan());
            Expedisi::query()->whereIn('NOSJ', $data['nosj'])->update($header);
            $this->savePengirim($data['pengirim'] ?? [], $data['nosj']);

            return $nomuat;
        });

        return response()->json(['status' => true, 'message' => 'No Muat berhasil disimpan.', 'nomuat' => $nomuat]);
    }

    public function updateMuat(Request $request, string $nomuat)
    {
        $data = $request->validate([
            'nosj' => ['required', 'array', 'min:1'],
            'nosj.*' => ['required', 'string', 'max:20'],
            'tglmuat' => ['required', 'date'],
            'rute' => ['nullable', 'string', 'max:30'],
            'plat_nomor' => ['nullable', 'string', 'max:30'],
            'driver1_kode' => ['nullable', 'string', 'max:20'],
            'driver1' => ['nullable', 'string', 'max:50'],
            'driver2_kode' => ['nullable', 'string', 'max:20'],
            'driver2' => ['nullable', 'string', 'max:50'],
            'kendaraan_kode' => ['nullable', 'string', 'max:20'],
            'kendaraan' => ['nullable', 'string', 'max:50'],
            'km_awal' => ['nullable', 'numeric', 'min:0'],
            'km_akhir' => ['nullable', 'numeric', 'min:0', 'gte:km_awal'],
            'uang_jalan' => ['nullable', 'numeric', 'min:0'],
            'uang_driver_makan' => ['nullable', 'numeric', 'min:0'],
            'uang_lain_lain' => ['nullable', 'numeric', 'min:0'],
            'pengirim' => ['nullable', 'array'],
            'pengirim.*.nosj' => ['required_with:pengirim', 'string', 'max:20'],
            'pengirim.*.pengirim' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($data, $nomuat) {
            $existingRows = Expedisi::query()->where('NOMUAT', $nomuat)->lockForUpdate()->get();
            abort_if($existingRows->isEmpty(), 404, 'No Muat tidak ditemukan.');
            abort_if($existingRows->contains(fn (Expedisi $row) => trim((string) $row->INVOICE) !== '' || trim((string) $row->GB) !== ''), 422, 'No Muat yang sudah tergabung atau diinvoice tidak dapat diedit.');

            $submittedRows = Expedisi::query()->whereIn('NOSJ', $data['nosj'])->lockForUpdate()->get();
            abort_if($submittedRows->count() !== count(array_unique($data['nosj'])), 422, 'Sebagian Surat Jalan tidak ditemukan.');
            abort_if($submittedRows->contains(fn (Expedisi $row) => $row->JENIS !== 'EKS' || (trim((string) $row->NOMUAT) !== '' && trim((string) $row->NOMUAT) !== $nomuat) || trim((string) $row->INVOICE) !== '' || trim((string) $row->GB) !== ''), 422, 'Surat Jalan yang dipilih sudah terikat ke transaksi lain.');

            $existingNosj = $existingRows->pluck('NOSJ')->map(fn ($value) => trim((string) $value))->all();
            $removedNosj = array_diff($existingNosj, $data['nosj']);
            if ($removedNosj) {
                Expedisi::query()->whereIn('NOSJ', $removedNosj)->update([
                    'NOMUAT' => null, 'TGLMUAT' => null,
                    'rute' => null, 'PLAT_NOMOR' => null, 'DRIVER' => null, 'NAMA_DRIVER' => null,
                    'DRIVER2' => null, 'NAMA_DRIVER2' => null, 'KENDARAAN' => null, 'NAMA_KENDARAAN' => null,
                    'KM_AWAL' => null, 'KM_AKHIR' => null, 'UANG_JALAN' => null,
                    'UANG_DRIVER_MAKAN' => null, 'UANG_LAIN_LAIN' => null,
                    'updated_at' => now(),
                ]);
            }

            Expedisi::query()->whereIn('NOSJ', $data['nosj'])->update($this->muatHeaderValues($data, $nomuat, $existingRows->first()->NOJALAN));
            $this->savePengirim($data['pengirim'] ?? [], $data['nosj']);
        });

        return response()->json(['status' => true, 'message' => 'No Muat berhasil diperbarui.', 'nomuat' => $nomuat]);
    }

    public function destroyMuat(string $nomuat)
    {
        DB::transaction(function () use ($nomuat) {
            $rows = Expedisi::query()->where('NOMUAT', $nomuat)->lockForUpdate()->get();
            abort_if($rows->isEmpty(), 404, 'No Muat tidak ditemukan.');
            abort_if($rows->contains(fn (Expedisi $row) => trim((string) $row->INVOICE) !== '' || trim((string) $row->GB) !== ''), 422, 'Muatan tidak bisa dibatalkan karena sudah tergabung atau sudah invoice.');

            Expedisi::query()->where('NOMUAT', $nomuat)->update([
                'NOMUAT' => null, 'TGLMUAT' => null, 'NOJALAN' => null,
                'rute' => null, 'PLAT_NOMOR' => null, 'DRIVER' => null, 'NAMA_DRIVER' => null,
                'DRIVER2' => null, 'NAMA_DRIVER2' => null, 'KENDARAAN' => null, 'NAMA_KENDARAAN' => null,
                'KM_AWAL' => null, 'KM_AKHIR' => null, 'UANG_JALAN' => null,
                'UANG_DRIVER_MAKAN' => null, 'UANG_LAIN_LAIN' => null, 'PENGIRIM' => null,
                'updated_at' => now(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'No Muat berhasil dibatalkan.']);
    }

    private function muatHeaderValues(array $data, string $nomuat, ?string $nojalan): array
    {
        return [
            'NOMUAT' => $nomuat,
            'TGLMUAT' => $data['tglmuat'],
            'NOJALAN' => $nojalan,
            'rute' => $data['rute'] ?? null,
            'PLAT_NOMOR' => $data['plat_nomor'] ?? null,
            'DRIVER' => $data['driver1_kode'] ?? null,
            'NAMA_DRIVER' => $data['driver1'] ?? null,
            'DRIVER2' => $data['driver2_kode'] ?? null,
            'NAMA_DRIVER2' => $data['driver2'] ?? null,
            'KENDARAAN' => $data['kendaraan_kode'] ?? null,
            'NAMA_KENDARAAN' => $data['kendaraan'] ?? null,
            'KM_AWAL' => $data['km_awal'] ?? null,
            'KM_AKHIR' => $data['km_akhir'] ?? null,
            'UANG_JALAN' => $data['uang_jalan'] ?? 0,
            'UANG_DRIVER_MAKAN' => $data['uang_driver_makan'] ?? 0,
            'UANG_LAIN_LAIN' => $data['uang_lain_lain'] ?? 0,
            'updated_at' => now(),
        ];
    }

    private function savePengirim(array $pengirim, array $nosjList): void
    {
        foreach ($pengirim as $item) {
            if (in_array($item['nosj'] ?? null, $nosjList, true)) {
                Expedisi::query()->where('NOSJ', $item['nosj'])->update([
                    'PENGIRIM' => $item['pengirim'] ?? null,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function generateNomuatWithLock(): string
    {
        $prefix = 'MU'.now()->format('my');
        $last = Expedisi::query()->where('NOMUAT', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('NOMUAT')->value('NOMUAT');
        $next = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function generateNoJalan(): string
    {
        $prefix = 'PJ'.now()->format('my');
        $last = Expedisi::query()->where('NOJALAN', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('NOJALAN')->value('NOJALAN');
        $next = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
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
        $expedisis = Expedisi::query()
            ->when($nomuat, fn ($query) => $query->where('NOMUAT', $nomuat))
            ->when(! $nomuat, fn ($query) => $query->where('NOSJ', 'like', '%'.$request->input('nosj_filter', '').'%')->whereNotNull('NOSJ'))
            ->orderBy('id')
            ->get();

        foreach ($expedisis as $expedisi) {
            $expedisi->PLAT = Kendaraan::query()->where('KODE', $expedisi->KENDARAAN)->value('PLAT');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('expedisi.pdf.muat', compact('expedisis'));

        return $pdf->stream('muat.pdf', ['stream' => true]);
    }

}
