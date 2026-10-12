<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coolroom;
use App\Models\Mcustomer;
use App\Models\Kendaraan;
use App\Models\Driver;
use App\Models\Arh;
use App\Services\JournalService;
use App\Services\AccountMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoolroomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $coolrooms = Coolroom::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('NOSJ', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('TGLMUAT')
            ->paginate(20)
            ->withQueryString();

        return view('coolroom.index', compact('coolrooms', 'search'));
    }

    public function create()
    {
        $customers = Mcustomer::orderBy('CUSTOMER')->get(['CUSTOMER', 'NAMACUST']);
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $drivers = Driver::orderBy('KODE')->get(['KODE', 'NAMA']);

        return view('coolroom.create', compact('customers', 'kendaraans', 'drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TGLMUAT' => 'required|date',
            'NOSJ' => 'nullable|string|max:20',
            'NOMUAT' => 'nullable|string|max:20',
            'CUSTOMER' => 'nullable|string|max:30',
            'NAMACUST' => 'nullable|string|max:100',
            'KENDARAAN' => 'nullable|string|max:20',
            'NAMA_KENDARAAN' => 'nullable|string|max:50',
            'DRIVER' => 'nullable|string|max:20',
            'NAMA_DRIVER' => 'nullable|string|max:50',
            'HARGA' => 'nullable|numeric',
            'JUMLAH' => 'nullable|numeric',
            'DISC' => 'nullable|numeric',
            'NDISC' => 'nullable|numeric',
            'NDISCAW' => 'nullable|numeric',
            'TOTAL' => 'nullable|numeric',
            'PPN' => 'nullable|numeric',
            'GRAND' => 'nullable|numeric',
            'SJ' => 'nullable|string|max:30',
            'STATUS' => 'nullable|string|max:20',
            'KETERANGAN' => 'nullable|string|max:500',
        ]);

        $user = $request->user()->name ?? 'system';
        $validated['user'] = $user;
        $validated['USEREDIT'] = $user;
        $validated['NOJURNAL'] = null;
        $validated['jurnal_id'] = null;

        Coolroom::create($validated);

        return redirect()->route('coolroom.index')
            ->with('success', 'Coolroom berhasil ditambahkan.');
    }

    public function show(Coolroom $coolroom)
    {
        return view('coolroom.show', compact('coolroom'));
    }

    public function edit(Coolroom $coolroom)
    {
        $customers = Mcustomer::orderBy('CUSTOMER')->get(['CUSTOMER', 'NAMACUST']);
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $drivers = Driver::orderBy('KODE')->get(['KODE', 'NAMA']);

        return view('coolroom.edit', compact('coolroom', 'customers', 'kendaraans', 'drivers'));
    }

    public function update(Request $request, Coolroom $coolroom)
    {
        $validated = $request->validate([
            'TGLMUAT' => 'required|date',
            'NOSJ' => 'nullable|string|max:20',
            'NOMUAT' => 'nullable|string|max:20',
            'CUSTOMER' => 'nullable|string|max:30',
            'NAMACUST' => 'nullable|string|max:100',
            'KENDARAAN' => 'nullable|string|max:20',
            'NAMA_KENDARAAN' => 'nullable|string|max:50',
            'DRIVER' => 'nullable|string|max:20',
            'NAMA_DRIVER' => 'nullable|string|max:50',
            'HARGA' => 'nullable|numeric',
            'JUMLAH' => 'nullable|numeric',
            'DISC' => 'nullable|numeric',
            'NDISC' => 'nullable|numeric',
            'NDISCAW' => 'nullable|numeric',
            'TOTAL' => 'nullable|numeric',
            'PPN' => 'nullable|numeric',
            'GRAND' => 'nullable|numeric',
            'SJ' => 'nullable|string|max:30',
            'STATUS' => 'nullable|string|max:20',
            'KETERANGAN' => 'nullable|string|max:500',
        ]);

        $validated['USEREDIT'] = $request->user()->name ?? 'system';

        $coolroom->update($validated);

        return redirect()->route('coolroom.index')
            ->with('success', 'Coolroom berhasil diperbarui.');
    }

    public function destroy(Coolroom $coolroom)
    {
        $coolroom->delete();

        return redirect()->route('coolroom.index')
            ->with('success', 'Coolroom berhasil dihapus.');
    }

    public function getDataCustomer(Request $request)
    {
        $search = $request->input('q');
        $customers = Mcustomer::query()
            ->when($search, function ($query) use ($search) {
                $query->where('CUSTOMER', 'like', "%{$search}%")
                    ->orWhere('NAMACUST', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['CUSTOMER', 'NAMACUST', 'ALAMAT1']);

        return response()->json($customers);
    }
}
