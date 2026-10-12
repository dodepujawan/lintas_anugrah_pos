<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Kendaraan;
use App\Models\Msupplier;
use App\Models\Pricedingin;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $services = Service::query()
            ->when($search, function ($query) use ($search) {
                $query->where('NOSJ', 'like', "%{$search}%")
                    ->orWhere('KENDARAAN', 'like', "%{$search}%");
            })
            ->orderByDesc('TGL_SERVICE')
            ->paginate(20)
            ->withQueryString();

        return view('service.index', compact('services', 'search'));
    }

    public function create()
    {
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $suppliers = Msupplier::orderBy('SUPPLIER')->get(['SUPPLIER', 'NAMA']);

        return view('service.create', compact('kendaraans', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TGL_SERVICE' => 'required|date',
            'KENDARAAN' => 'nullable|string|max:20',
            'NOSJ' => 'nullable|string|max:20',
            'NOBAYAR' => 'nullable|string|max:50',
            'HARGA' => 'nullable|numeric',
            'JUMLAH' => 'nullable|numeric',
            'KETERANGAN' => 'nullable|string|max:500',
            'REK_BIAYA' => 'nullable|string|max:5',
            'tipe' => 'nullable|string|max:1',
        ]);

        $user = $request->user()->name ?? 'system';
        $validated['USER'] = $user;
        $validated['USEREDIT'] = $user;

        Service::create($validated);

        return redirect()->route('service.index')
            ->with('success', 'Service berhasil ditambahkan.');
    }

    public function show(Service $service)
    {
        return view('service.show', compact('service'));
    }

    public function edit(Service $service)
    {
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $suppliers = Msupplier::orderBy('SUPPLIER')->get(['SUPPLIER', 'NAMA']);

        return view('service.edit', compact('service', 'kendaraans', 'suppliers'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'TGL_SERVICE' => 'required|date',
            'KENDARAAN' => 'nullable|string|max:20',
            'NOSJ' => 'nullable|string|max:20',
            'NOBAYAR' => 'nullable|string|max:50',
            'HARGA' => 'nullable|numeric',
            'JUMLAH' => 'nullable|numeric',
            'KETERANGAN' => 'nullable|string|max:500',
            'REK_BIAYA' => 'nullable|string|max:5',
            'tipe' => 'nullable|string|max:1',
        ]);

        $validated['USEREDIT'] = $request->user()->name ?? 'system';

        $service->update($validated);

        return redirect()->route('service.index')
            ->with('success', 'Service berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('service.index')
            ->with('success', 'Service berhasil dihapus.');
    }

    public function dataSupplier()
    {
        $suppliers = Msupplier::orderBy('SUPPLIER')->limit(100)->get(['SUPPLIER', 'NAMA']);

        return response()->json($suppliers);
    }

    public function dataKendaraan()
    {
        $kendaraans = Kendaraan::orderBy('KODE')->limit(100)->get(['KODE', 'NAMA']);

        return response()->json($kendaraans);
    }

    public function ajaxPerkiraan(Request $request)
    {
        $search = $request->input('q');
        $coas = \App\Models\Coa::query()
            ->when($search, fn($q) => $q->where('kode_akun', 'like', "%{$search}%")
                ->orWhere('nama_akun', 'like', "%{$search}%"))
            ->limit(20)
            ->get(['id', 'kode_akun', 'nama_akun']);

        return response()->json($coas);
    }
}
