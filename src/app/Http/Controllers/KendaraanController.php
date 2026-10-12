<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KendaraanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $kendaraans = Kendaraan::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('KODE', 'like', "%{$search}%")
                        ->orWhere('NAMA', 'like', "%{$search}%")
                        ->orWhere('PLAT', 'like', "%{$search}%");
                });
            })
            ->orderBy('KODE')
            ->paginate(20)
            ->withQueryString();

        return view('kendaraan.index', compact('kendaraans', 'search'));
    }

    public function create()
    {
        return view('kendaraan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'KODE' => 'required|string|max:20|unique:kendaraans,KODE',
            'NAMA' => 'nullable|string|max:50',
            'PLAT' => 'nullable|string|max:20',
            'FNO_PRK_B' => 'nullable|string|max:15',
            'FNO_PRK_T' => 'nullable|string|max:15',
            'JENIS' => 'nullable|string|max:1',
            'FNO_PRK' => 'nullable|string|max:13',
            'NO_BKK' => 'nullable|string|max:22',
            'KENDARAAN_TYPE' => 'nullable|string|max:20',
        ], [
            'KODE.required' => 'Kode kendaraan wajib diisi.',
            'KODE.unique' => 'Kode kendaraan sudah terdaftar.',
        ]);

        $user = $request->user()->name ?? 'system';
        $validated['USER'] = $user;
        $validated['USEREDIT'] = $user;

        Kendaraan::create($validated);

        return redirect()->route('kendaraan.index')
            ->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function show(Kendaraan $kendaraan)
    {
        return view('kendaraan.show', compact('kendaraan'));
    }

    public function edit(Kendaraan $kendaraan)
    {
        return view('kendaraan.edit', compact('kendaraan'));
    }

    public function update(Request $request, Kendaraan $kendaraan)
    {
        $validated = $request->validate([
            'KODE' => ['required', 'string', 'max:20', 'unique:kendaraans,KODE,' . $kendaraan->id],
            'NAMA' => 'nullable|string|max:50',
            'PLAT' => 'nullable|string|max:20',
            'FNO_PRK_B' => 'nullable|string|max:15',
            'FNO_PRK_T' => 'nullable|string|max:15',
            'JENIS' => 'nullable|string|max:1',
            'FNO_PRK' => 'nullable|string|max:13',
            'NO_BKK' => 'nullable|string|max:22',
            'KENDARAAN_TYPE' => 'nullable|string|max:20',
        ]);

        $validated['USEREDIT'] = $request->user()->name ?? 'system';

        $kendaraan->update($validated);

        return redirect()->route('kendaraan.index')
            ->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Kendaraan $kendaraan)
    {
        $kendaraan->delete();

        return redirect()->route('kendaraan.index')
            ->with('success', 'Kendaraan berhasil dihapus.');
    }

    public function data(Request $request)
    {
        $kendaraans = Kendaraan::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('KODE', 'like', "%{$search}%")
                    ->orWhere('NAMA', 'like', "%{$search}%")
                    ->orWhere('PLAT', 'like', "%{$search}%");
            });

        return DataTables::of($kendaraans)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '<a href="' . route('kendaraan.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a>';
            })
            ->make(true);
    }

    public function kodeCallback()
    {
        $last = Kendaraan::max('KODE');
        return response()->json(['kode' => $last]);
    }

    public function dataModel($user_id = null)
    {
        $driver = \App\Models\Driver::where('user_id', $user_id)->first();
        $kendaraan = null;

        if ($driver) {
            $kendaraan = Kendaraan::where('KODE', $driver->KODE)->first();
        }

        return response()->json([
            'driver' => $driver,
            'kendaraan' => $kendaraan,
        ]);
    }
}
