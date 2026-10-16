<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Price as PriceModel;
use App\Models\PriceCus;
use App\Models\PriceCushis;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $prices = PriceModel::query()
            ->when($search, function ($query) use ($search) {
                $query->where('FROM', 'like', "%{$search}%")
                    ->orWhere('TO', 'like', "%{$search}%");
            })
            ->orderBy('FROM')
            ->orderBy('TO')
            ->paginate(20)
            ->withQueryString();

        return view('price.index', compact('prices', 'search'));
    }

    public function create()
    {
        return view('price.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'KODE' => 'nullable|string|max:20|unique:prices,KODE',
            'FROM' => 'nullable|string|max:30',
            'TO' => 'nullable|string|max:30',
            'HARGA' => 'nullable|numeric',
            'TANGGAL' => 'nullable|date',
            'DARI' => 'nullable|numeric',
            'SAMPAI' => 'nullable|numeric',
            'RUTE' => 'nullable|string|max:30',
            'KETERANGAN' => 'nullable|string|max:50',
            'HV' => 'nullable|numeric',
            'HKG' => 'nullable|numeric',
            'HBOK' => 'nullable|numeric',
            'KUNCI' => 'nullable|string|max:100',
            'HG' => 'nullable|numeric',
            'JENIS' => 'nullable|string|max:1',
        ]);

        $user = $request->user()->name ?? 'system';
        $validated['USER'] = $user;
        $validated['USEREDIT'] = $user;

        // Map legacy DARI/SAMPAI to FROM/TO if FROM/TO empty
        if (empty($validated['FROM']) && !empty($request->input('DARI'))) {
            $validated['FROM'] = (string) $request->input('DARI');
        }
        if (empty($validated['TO']) && !empty($request->input('SAMPAI'))) {
            $validated['TO'] = (string) $request->input('SAMPAI');
        }

        PriceModel::create($validated);

        return redirect()->route('price.index')
            ->with('success', 'Price berhasil ditambahkan.');
    }

    public function show(PriceModel $price)
    {
        return view('price.show', compact('price'));
    }

    public function edit(PriceModel $price)
    {
        return view('price.edit', compact('price'));
    }

    public function update(Request $request, PriceModel $price)
    {
        $validated = $request->validate([
            'KODE' => 'nullable|string|max:20|unique:prices,KODE,' . $price->id,
            'FROM' => 'nullable|string|max:30',
            'TO' => 'nullable|string|max:30',
            'HARGA' => 'nullable|numeric',
            'TANGGAL' => 'nullable|date',
            'DARI' => 'nullable|numeric',
            'SAMPAI' => 'nullable|numeric',
            'RUTE' => 'nullable|string|max:30',
            'KETERANGAN' => 'nullable|string|max:50',
            'HV' => 'nullable|numeric',
            'HKG' => 'nullable|numeric',
            'HBOK' => 'nullable|numeric',
            'KUNCI' => 'nullable|string|max:100',
            'HG' => 'nullable|numeric',
            'JENIS' => 'nullable|string|max:1',
        ]);

        $validated['USEREDIT'] = $request->user()->name ?? 'system';

        if (empty($validated['FROM']) && !empty($request->input('DARI'))) {
            $validated['FROM'] = (string) $request->input('DARI');
        }
        if (empty($validated['TO']) && !empty($request->input('SAMPAI'))) {
            $validated['TO'] = (string) $request->input('SAMPAI');
        }

        $price->update($validated);

        return redirect()->route('price.index')
            ->with('success', 'Price berhasil diperbarui.');
    }

    public function destroy(PriceModel $price)
    {
        $price->delete();

        return redirect()->route('price.index')
            ->with('success', 'Price berhasil dihapus.');
    }
}
