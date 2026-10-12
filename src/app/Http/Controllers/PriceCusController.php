<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PriceCus;
use App\Models\PriceCushis;
use App\Models\Mcustomer;
use Illuminate\Http\Request;

class PriceCusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $priceCuses = PriceCus::query()
            ->when($search, function ($query) use ($search) {
                $query->where('KODECUS', 'like', "%{$search}%")
                    ->orWhere('NAMACUS', 'like', "%{$search}%");
            })
            ->orderBy('FROM')
            ->paginate(20)
            ->withQueryString();

        return view('price-customer.index', compact('priceCuses', 'search'));
    }

    public function create()
    {
        return view('price-customer.create');
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

        PriceCus::create($validated);

        PriceCushis::create($validated);

        return redirect()->route('price-customer.index')
            ->with('success', 'Price customer berhasil ditambahkan.');
    }

    public function edit(PriceCus $priceCus)
    {
        return view('price-customer.edit', compact('priceCus'));
    }

    public function update(Request $request, PriceCus $priceCus)
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

        $priceCus->update($validated);

        return redirect()->route('price-customer.index')
            ->with('success', 'Price customer berhasil diperbarui.');
    }

    public function destroy(PriceCus $priceCus)
    {
        $priceCus->delete();

        return redirect()->route('price-customer.index')
            ->with('success', 'Price customer berhasil dihapus.');
    }

    public function getPrice($kodecus)
    {
        $priceCus = PriceCus::where('KODECUS', $kodecus)->get();

        return response()->json($priceCus);
    }
}
