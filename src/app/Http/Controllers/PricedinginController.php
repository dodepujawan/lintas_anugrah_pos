<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pricedingin;
use App\Models\Pricedingincus;
use Illuminate\Http\Request;

class PricedinginController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $prices = Pricedingin::query()
            ->when($search, function ($query) use ($search) {
                $query->where('FROM', 'like', "%{$search}%")
                    ->orWhere('TO', 'like', "%{$search}%");
            })
            ->orderBy('FROM')
            ->paginate(20)
            ->withQueryString();

        return view('price-rent.index', compact('prices', 'search'));
    }

    public function create()
    {
        return view('price-rent.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'FROM' => 'required|string|max:30',
            'TO' => 'required|string|max:30',
            'HARGA' => 'required|numeric',
            'TANGGAL' => 'nullable|string|max:1',
        ]);

        $user = $request->user()->name ?? 'system';
        $validated['USER'] = $user;
        $validated['USEREDIT'] = $user;

        Pricedingin::create($validated);

        return redirect()->route('price-rent.index')
            ->with('success', 'Price rent berhasil ditambahkan.');
    }

    public function show(Pricedingin $price)
    {
        return view('price-rent.show', compact('price'));
    }

    public function edit(Pricedingin $price)
    {
        return view('price-rent.edit', compact('price'));
    }

    public function update(Request $request, Pricedingin $price)
    {
        $validated = $request->validate([
            'FROM' => 'required|string|max:30',
            'TO' => 'required|string|max:30',
            'HARGA' => 'required|numeric',
            'TANGGAL' => 'nullable|string|max:1',
        ]);

        $validated['USEREDIT'] = $request->user()->name ?? 'system';

        $price->update($validated);

        return redirect()->route('price-rent.index')
            ->with('success', 'Price rent berhasil diperbarui.');
    }

    public function destroy(Pricedingin $price)
    {
        $price->delete();

        return redirect()->route('price-rent.index')
            ->with('success', 'Price rent berhasil dihapus.');
    }

    public function getData()
    {
        $prices = Pricedingin::all();
        return response()->json($prices);
    }
}
