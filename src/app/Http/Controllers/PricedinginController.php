<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Mcustomer;
use App\Models\Pricedingin;
use App\Models\Pricedingincus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->with('kendaraan')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('KODEDGN', 'like', "%{$search}%")
                        ->orWhere('KODE', 'like', "%{$search}%")
                        ->orWhere('PERIODE', 'like', "%{$search}%")
                        ->orWhere('PLAT', 'like', "%{$search}%")
                        ->orWhere('JENIS', 'like', "%{$search}%")
                        ->orWhere('ITEM', 'like', "%{$search}%")
                        ->orWhereHas('kendaraan', function ($vehicle) use ($search) {
                            $vehicle->where('NAMA', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('price-rent.index', compact('prices', 'search'));
    }

    public function create()
    {
        $vehicles = Kendaraan::query()
            ->select(['KODE', 'NAMA', 'PLAT', 'JENIS'])
            ->orderBy('KODE')
            ->get();

        return view('price-rent.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'KODE' => 'required|string|max:20',
            'PERIODE' => 'required|string|max:50',
            'HARGA' => 'required|numeric|min:0',
        ]);

        $user = $request->user()?->name ?? 'system';
        $vehicle = Kendaraan::where('KODE', $validated['KODE'])->first();

        if (! $vehicle) {
            return back()->withErrors(['KODE' => 'Kendaraan tidak ditemukan.'])->withInput();
        }

        DB::transaction(function () use ($validated, $user, $vehicle) {
            $kodedgn = $this->nextCode();
            $tanggal = now()->format('Y-m-d');
            $item = trim($vehicle->PLAT.' '.$vehicle->JENIS);
            $kunci = substr($kodedgn.$validated['KODE'].$validated['PERIODE'].$vehicle->PLAT, 0, 100);

            $price = Pricedingin::create([
                'KODEDGN' => $kodedgn,
                'KODE' => $vehicle->KODE,
                'TANGGAL' => $tanggal,
                'PERIODE' => $validated['PERIODE'],
                'PLAT' => $vehicle->PLAT,
                'JENIS' => $vehicle->JENIS,
                'ITEM' => $item,
                'HARGA' => $validated['HARGA'],
                'KUNCI' => $kunci,
                'USER' => $user,
                'USEREDIT' => $user,
            ]);

            $customers = Mcustomer::query()->select('CUSTOMER')->get();
            $now = now();
            $customerPrices = $customers->map(fn ($customer) => [
                'KODECUS' => $customer->CUSTOMER,
                // Legacy contract: customer copy stores the master numeric id.
                'KODEDGN' => $price->id,
                'KODE' => $price->KODE,
                'TANGGAL' => $tanggal,
                'PERIODE' => $price->PERIODE,
                'PLAT' => $price->PLAT,
                'JENIS' => $price->JENIS,
                'ITEM' => $price->ITEM,
                'HARGA' => $price->HARGA,
                'KUNCI' => $price->KUNCI,
                'USER' => $user,
                'USEREDIT' => $user,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            if ($customerPrices) {
                Pricedingincus::insert($customerPrices);
            }
        });

        return redirect()->route('price-rent.index')
            ->with('success', 'Price rent berhasil ditambahkan.');
    }

    public function show(Pricedingin $price)
    {
        return view('price-rent.show', compact('price'));
    }

    public function edit(Pricedingin $price)
    {
        $vehicles = Kendaraan::query()
            ->select(['KODE', 'NAMA', 'PLAT', 'JENIS'])
            ->orderBy('KODE')
            ->get();

        return view('price-rent.edit', compact('price', 'vehicles'));
    }

    public function update(Request $request, Pricedingin $price)
    {
        $validated = $request->validate([
            'KODE' => 'required|string|max:20',
            'PERIODE' => 'required|string|max:50',
            'HARGA' => 'required|numeric|min:0',
        ]);

        $vehicle = Kendaraan::where('KODE', $validated['KODE'])->first();

        if (! $vehicle) {
            return back()->withErrors(['KODE' => 'Kendaraan tidak ditemukan.'])->withInput();
        }

        $price->update([
            'KODE' => $vehicle->KODE,
            'PERIODE' => $validated['PERIODE'],
            'PLAT' => $vehicle->PLAT,
            'JENIS' => $vehicle->JENIS,
            'ITEM' => trim($vehicle->PLAT.' '.$vehicle->JENIS),
            'HARGA' => $validated['HARGA'],
            'KUNCI' => substr($price->KODEDGN.$vehicle->KODE.$validated['PERIODE'].$vehicle->PLAT, 0, 100),
            'USEREDIT' => $request->user()?->name ?? 'system',
        ]);

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

    private function nextCode(): string
    {
        $lastCode = Pricedingin::query()
            ->where('KODEDGN', 'like', 'PRD%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('KODEDGN');

        preg_match('/^PRD(\d+)$/', (string) $lastCode, $matches);
        $number = isset($matches[1]) ? ((int) $matches[1]) + 1 : 1;

        return 'PRD'.str_pad((string) $number, 7, '0', STR_PAD_LEFT);
    }
}
