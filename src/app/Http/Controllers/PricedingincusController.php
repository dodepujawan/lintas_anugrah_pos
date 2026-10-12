<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pricedingincus;
use App\Models\Pricedinginhis;
use App\Models\Mcustomer;
use Illuminate\Http\Request;

class PricedingincusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $prices = Pricedingincus::query()
            ->when($search, function ($query) use ($search) {
                $query->where('KODECUS', 'like', "%{$search}%")
                    ->orWhere('NAMACUS', 'like', "%{$search}%");
            })
            ->orderBy('FROM')
            ->paginate(20)
            ->withQueryString();

        return view('price-rent-customer.index', compact('prices', 'search'));
    }

    public function create()
    {
        return view('price-rent-customer.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'KODECUS' => 'required|string|max:50',
            'NAMACUS' => 'nullable|string|max:50',
            'FROM' => 'required|string|max:30',
            'TO' => 'required|string|max:30',
            'HARGA' => 'required|numeric',
            'TANGGAL' => 'nullable|string|max:1',
        ]);

        $user = $request->user()->name ?? 'system';
        $validated['USER'] = $user;
        $validated['USEREDIT'] = $user;

        Pricedingincus::create($validated);
        Pricedinginhis::create($validated);

        return redirect()->route('price-rent-customer.index')
            ->with('success', 'Price rent customer berhasil ditambahkan.');
    }

    public function edit(Pricedingincus $pricedingincus)
    {
        return view('price-rent-customer.edit', compact('pricedingincus'));
    }

    public function update(Request $request, Pricedingincus $pricedingincus)
    {
        $validated = $request->validate([
            'KODECUS' => 'required|string|max:50',
            'NAMACUS' => 'nullable|string|max:50',
            'FROM' => 'required|string|max:30',
            'TO' => 'required|string|max:30',
            'HARGA' => 'required|numeric',
            'TANGGAL' => 'nullable|string|max:1',
        ]);

        $validated['USEREDIT'] = $request->user()->name ?? 'system';

        $pricedingincus->update($validated);

        return redirect()->route('price-rent-customer.index')
            ->with('success', 'Price rent customer berhasil diperbarui.');
    }

    public function destroy(Pricedingincus $pricedingincus)
    {
        $pricedingincus->delete();

        return redirect()->route('price-rent-customer.index')
            ->with('success', 'Price rent customer berhasil dihapus.');
    }

    public function getPrice($kodecus)
    {
        $prices = Pricedingincus::where('KODECUS', $kodecus)->get();

        return response()->json($prices);
    }

    public function getPriceModal($kodecus)
    {
        $prices = Pricedingincus::where('KODECUS', $kodecus)->get();

        return response()->json($prices);
    }
}
