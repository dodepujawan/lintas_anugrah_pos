<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expedisi;
use App\Models\Mcustomer;
use App\Models\Kendaraan;
use App\Models\Driver;
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

    public function storeSurjal(Request $request)
    {
        $validated = $request->validate([
            'TGLMUAT' => 'nullable|date',
            'NOSJ' => 'nullable|string|max:20',
            'CUSTOMER' => 'nullable|string|max:30',
            'KENDARAAN' => 'nullable|string|max:20',
            'DRIVER' => 'nullable|string|max:20',
            'JUMLAH' => 'nullable|numeric',
            'HARGA' => 'nullable|numeric',
            'GRAND' => 'nullable|numeric',
        ]);

        $validated['JENIS'] = 'REN';
        $validated['USER'] = $request->user()->name;
        $validated['USEREDIT'] = $request->user()->name;

        Expedisi::create($validated);

        return response()->json(['success' => true, 'message' => 'SJ berhasil disimpan.']);
    }

    public function storeMuat(Request $request)
    {
        $validated = $request->validate([
            'NOMUAT' => 'nullable|string|max:20',
            'JUMLAH' => 'nullable|numeric',
            'HARGA' => 'nullable|numeric',
            'TOTAL' => 'nullable|numeric',
        ]);

        return response()->json(['success' => true, 'message' => 'Muat berhasil disimpan.']);
    }

    public function updateSurjal(Request $request, $nosj)
    {
        $expedisi = Expedisi::where('NOSJ', $nosj)->where('JENIS', 'REN')->firstOrFail();

        $validated = $request->validate([
            'TGLMUAT' => 'nullable|date',
            'CUSTOMER' => 'nullable|string|max:30',
            'KENDARAAN' => 'nullable|string|max:20',
            'DRIVER' => 'nullable|string|max:20',
            'JUMLAH' => 'nullable|numeric',
            'HARGA' => 'nullable|numeric',
            'GRAND' => 'nullable|numeric',
        ]);

        $expedisi->update($validated);

        return response()->json(['success' => true, 'message' => 'SJ berhasil diupdate.']);
    }

    public function updateMuat(Request $request, $nomuat)
    {
        return response()->json(['success' => true, 'message' => 'Muat berhasil diupdate.']);
    }

    public function showSurjal($nosj)
    {
        $expedisi = Expedisi::where('NOSJ', $nosj)->where('JENIS', 'REN')->firstOrFail();

        return response()->json($expedisi);
    }

    public function showMuat($nomuat)
    {
        $expedisi = Expedisi::where('NOMUAT', $nomuat)->where('JENIS', 'REN')->firstOrFail();

        return response()->json($expedisi);
    }

    public function destroySurjal($nosj)
    {
        Expedisi::where('NOSJ', $nosj)->where('JENIS', 'REN')->delete();

        return response()->json(['success' => true, 'message' => 'SJ berhasil dihapus.']);
    }

    public function destroyMuat($nomuat)
    {
        return response()->json(['success' => true, 'message' => 'Muat berhasil dihapus.']);
    }

    public function printSurjal(Request $request)
    {
        $expedisi = Expedisi::where('NOSJ', $request->input('nosj'))->where('JENIS', 'REN')->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('rent-pendingin.pdf.surjal', compact('expedisi'));

        return $pdf->stream('surat-jalan-rent.pdf', ['stream' => true]);
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
}
