<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Price as PriceModel;
use App\Models\PriceCus;
use App\Models\PriceCushis;
use App\Models\Mcustomer;
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
                $query->where('KETERANGAN', 'like', "%{$search}%")
                    ->orWhere('DARI', 'like', "%{$search}%")
                    ->orWhere('SAMPAI', 'like', "%{$search}%")
                    ->orWhere('RUTE', 'like', "%{$search}%")
                    ->orWhere('FROM', 'like', "%{$search}%")
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

        // Generate KODE otomatis jika kosong
        if (empty($validated['KODE'])) {
            $lastCode = PriceModel::orderBy('id', 'desc')->value('KODE');
            $number = $lastCode ? (int) substr($lastCode, 3) + 1 : 1;
            $validated['KODE'] = 'PRC' . str_pad($number, 7, '0', STR_PAD_LEFT);
        }

        // Set default values
        $validated['HV'] = $validated['HV'] ?? 0;
        $validated['HKG'] = $validated['HKG'] ?? 0;
        $validated['HBOK'] = $validated['HBOK'] ?? 0;
        $validated['HG'] = $validated['HG'] ?? 0;

        // Generate KUNCI
        $validated['KUNCI'] = ($validated['KETERANGAN'] ?? '') .
                              ($validated['JENIS'] ?? '') .
                              ($validated['DARI'] ?? '') .
                              ($validated['SAMPAI'] ?? '');

        $price = PriceModel::create($validated);

        // Sinkronisasi ke tabel pricecus
        $this->syncPriceCus($price);

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

        // Map legacy DARI/SAMPAI to FROM/TO if FROM/TO empty
        if (empty($validated['FROM']) && !empty($request->input('DARI'))) {
            $validated['FROM'] = (string) $request->input('DARI');
        }
        if (empty($validated['TO']) && !empty($request->input('SAMPAI'))) {
            $validated['TO'] = (string) $request->input('SAMPAI');
        }

        // Set default values
        $validated['HV'] = $validated['HV'] ?? 0;
        $validated['HKG'] = $validated['HKG'] ?? 0;
        $validated['HBOK'] = $validated['HBOK'] ?? 0;
        $validated['HG'] = $validated['HG'] ?? 0;

        // Generate KUNCI
        $validated['KUNCI'] = ($validated['KETERANGAN'] ?? '') .
                              ($validated['JENIS'] ?? '') .
                              ($validated['DARI'] ?? '') .
                              ($validated['SAMPAI'] ?? '');

        $price->update($validated);

        // Sinkronisasi ke tabel pricecus
        $this->syncPriceCus($price);

        return redirect()->route('price.index')
            ->with('success', 'Price berhasil diperbarui.');
    }

    /**
     * Sinkronisasi price ke tabel pricecus untuk semua customer
     */
    private function syncPriceCus(PriceModel $price): void
    {
        $customers = Mcustomer::select('CUSTOMER')->get();
        $data = [];
        foreach ($customers as $customer) {
            $data[] = [
                'KODECUS' => $customer->CUSTOMER,
                'KODE' => $price->id,
                'TANGGAL' => $price->TANGGAL,
                'KETERANGAN' => $price->KETERANGAN,
                'DARI' => $price->DARI,
                'SAMPAI' => $price->SAMPAI,
                'RUTE' => $price->RUTE,
                'HARGA' => $price->HARGA,
                'HV' => $price->HV,
                'HKG' => $price->HKG,
                'HBOK' => $price->HBOK,
                'USER' => $price->USER,
                'USEREDIT' => $price->USEREDIT,
                'KUNCI' => $price->KUNCI,
                'HG' => $price->HG,
                'JENIS' => $price->JENIS,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        PriceCus::insert($data);
    }

    public function destroy(PriceModel $price)
    {
        $price->delete();

        return redirect()->route('price.index')
            ->with('success', 'Price berhasil dihapus.');
    }
}
