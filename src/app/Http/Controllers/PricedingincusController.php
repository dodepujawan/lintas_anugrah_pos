<?php

namespace App\Http\Controllers;

use App\Models\Mcustomer;
use App\Models\Pricedingin;
use App\Models\Pricedingincus;
use App\Models\Pricedinginhis;
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
        $customers = Mcustomer::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('CUSTOMER', 'like', "%{$search}%")
                        ->orWhere('NAMACUST', 'like', "%{$search}%")
                        ->orWhere('TYPECUST', 'like', "%{$search}%")
                        ->orWhere('TELEPON', 'like', "%{$search}%")
                        ->orWhere('EMAIL', 'like', "%{$search}%");
                });
            })
            ->orderBy('NAMACUST')
            ->paginate(20)
            ->withQueryString();

        return view('price-rent-customer.index', compact('customers', 'search'));
    }

    public function customerPrices(string $kodecus, Request $request)
    {
        $customer = Mcustomer::where('CUSTOMER', $kodecus)->firstOrFail();
        $search = trim((string) $request->input('search'));
        $prices = Pricedingincus::query()
            ->with('kendaraan')
            ->where('KODECUS', $kodecus)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('ITEM', 'like', "%{$search}%")
                        ->orWhere('PERIODE', 'like', "%{$search}%")
                        ->orWhere('PLAT', 'like', "%{$search}%")
                        ->orWhere('JENIS', 'like', "%{$search}%")
                        ->orWhere('KODEDGN', 'like', "%{$search}%");
                });
            })
            ->orderBy('PERIODE')
            ->orderBy('id')
            ->simplePaginate(20)
            ->withQueryString();

        return view('price-rent-customer.customer-prices', compact('customer', 'prices', 'search'));
    }

    public function updateAll(string $kodecus)
    {
        Mcustomer::where('CUSTOMER', $kodecus)->firstOrFail();

        $existingIds = Pricedingincus::where('KODECUS', $kodecus)
            ->pluck('KODEDGN')
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (string) $value)
            ->all();

        $newPrices = Pricedingin::query()
            ->when($existingIds, fn ($query) => $query->whereNotIn('id', $existingIds))
            ->get();

        if ($newPrices->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada harga dingin baru.',
            ]);
        }

        $user = request()->user()?->name ?? 'system';
        $now = now();
        $rows = $newPrices->map(fn ($price) => [
            'KODECUS' => $kodecus,
            'KODEDGN' => $price->id,
            'TANGGAL' => $price->TANGGAL,
            'KODE' => $price->KODE,
            'PERIODE' => $price->PERIODE,
            'PLAT' => $price->PLAT,
            'ITEM' => $price->ITEM,
            'HARGA' => $price->HARGA,
            'JENIS' => $price->JENIS,
            'KUNCI' => $price->KUNCI,
            'USER' => $user,
            'USEREDIT' => $user,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        Pricedingincus::insert($rows);

        return response()->json([
            'success' => true,
            'message' => count($rows).' harga dingin berhasil ditambahkan.',
        ]);
    }

    public function saveRow(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:pricedingincus,id',
            'harga' => 'required|numeric|min:0',
        ]);

        $updated = Pricedingincus::where('id', $validated['id'])
            ->update([
                'HARGA' => $validated['harga'],
                'USEREDIT' => $request->user()?->name ?? 'system',
            ]);

        return response()->json([
            'success' => (bool) $updated,
            'message' => $updated ? 'Harga berhasil diupdate.' : 'Data harga tidak ditemukan.',
        ], $updated ? 200 : 404);
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
            'KODEDGN' => 'nullable|string|max:20',
            'KODE' => 'nullable|string|max:20',
            'TANGGAL' => 'nullable|date',
            'PERIODE' => 'nullable|string|max:50',
            'PLAT' => 'nullable|string|max:50',
            'JENIS' => 'nullable|string|max:50',
            'ITEM' => 'nullable|string|max:100',
            'HARGA' => 'nullable|numeric',
            'KUNCI' => 'nullable|string|max:100',
            'FROM' => 'nullable|string|max:30',
            'TO' => 'nullable|string|max:30',
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
            'KODEDGN' => 'nullable|string|max:20',
            'KODE' => 'nullable|string|max:20',
            'TANGGAL' => 'nullable|date',
            'PERIODE' => 'nullable|string|max:50',
            'PLAT' => 'nullable|string|max:50',
            'JENIS' => 'nullable|string|max:50',
            'ITEM' => 'nullable|string|max:100',
            'HARGA' => 'nullable|numeric',
            'KUNCI' => 'nullable|string|max:100',
            'FROM' => 'nullable|string|max:30',
            'TO' => 'nullable|string|max:30',
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
