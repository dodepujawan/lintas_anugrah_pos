<?php

namespace App\Http\Controllers;

use App\Models\Mcustomer;
use App\Models\Price;
use App\Models\PriceCus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceCusController extends Controller
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
                $query->where('CUSTOMER', 'like', "%{$search}%")
                    ->orWhere('NAMACUST', 'like', "%{$search}%");
            })
            ->orderBy('CUSTOMER')
            ->paginate(20)
            ->withQueryString();

        return view('price-customer.index', compact('customers', 'search'));
    }

    /**
     * Tampilkan daftar price untuk customer tertentu
     */
    public function customerPrices($kodecus, Request $request)
    {
        $customer = Mcustomer::where('CUSTOMER', $kodecus)->firstOrFail();
        $search = trim((string) $request->input('search'));
        $priceCuses = PriceCus::query()
            ->where('KODECUS', $kodecus)
            ->when($search, function ($query) use ($search) {
                $query->where('KETERANGAN', 'like', "%{$search}%")
                    ->orWhere('DARI', 'like', "%{$search}%")
                    ->orWhere('SAMPAI', 'like', "%{$search}%")
                    ->orWhere('RUTE', 'like', "%{$search}%");
            })
            ->orderBy('DARI')
            ->orderBy('SAMPAI')
            ->orderBy('id')
            ->simplePaginate(20)
            ->withQueryString();

        return view('price-customer.customer-prices', compact('customer', 'priceCuses', 'search'));
    }

    /**
     * Sync new prices from the general price table into pricecus for a customer.
     */
    public function updateAll(Request $request, $kodecus)
    {
        // Legacy rows may store either prices.id or prices.KODE in pricecus.KODE.
        // Check both values so Update All does not create duplicate rows.
        $existingKeys = PriceCus::where('KODECUS', $kodecus)
            ->pluck('KODE')
            ->filter(fn ($kode) => $kode !== null && $kode !== '')
            ->map(fn ($kode) => (string) $kode)
            ->unique()
            ->values()
            ->all();

        $newPrices = Price::query()
            ->when($existingKeys, function ($query) use ($existingKeys) {
                $query->whereNotIn('id', $existingKeys)
                    ->whereNotIn('KODE', $existingKeys);
            })
            ->get();

        if ($newPrices->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada harga baru',
            ]);
        }

        $insertData = [];

        foreach ($newPrices as $price) {
            $insertData[] = [
                'KODECUS' => $kodecus,
                // New rows use the stable business key; legacy id-based rows
                // remain supported by the duplicate check above.
                'KODE' => $price->KODE,
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

        PriceCus::insert($insertData);

        return response()->json([
            'success' => true,
            'message' => count($insertData).' harga baru berhasil ditambahkan',
        ]);
    }

    /**
     * Save a single price row (inline edit).
     */
    public function saveRow(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:pricecus,id',
            'harga' => 'required|numeric',
        ]);

        $updated = PriceCus::where('id', $request->id)->update([
            'HARGA' => $request->harga,
            'USEREDIT' => $request->user()->name ?? 'System',
        ]);

        if (! $updated) {
            return response()->json([
                'success' => false,
                'message' => 'Data harga customer tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Harga berhasil diupdate',
        ]);
    }

    public function create(Request $request)
    {
        $kodecus = $request->input('kodecus');
        $customer = $kodecus ? Mcustomer::where('CUSTOMER', $kodecus)->first() : null;

        return view('price-customer.create', compact('customer'));
    }

    public function store(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate([
            'KODECUS' => 'required|string|max:50',
            'NAMACUS' => 'nullable|string|max:50',
            'FROM' => 'nullable|string|max:30',
            'TO' => 'nullable|string|max:30',
            'HARGA' => 'required|numeric',
            'TANGGAL' => 'nullable|date',
            'DARI' => 'required|numeric',
            'SAMPAI' => 'required|numeric',
            'RUTE' => 'required|string|max:30',
            'KETERANGAN' => 'required|string|max:50',
            'HV' => 'nullable|numeric',
            'HKG' => 'nullable|numeric',
            'HBOK' => 'nullable|numeric',
            'KUNCI' => 'nullable|string|max:100',
            'HG' => 'nullable|numeric',
            'JENIS' => 'required|string|max:1',
        ]);

        $user = $request->user()?->name ?? 'system';
        $tanggal = now()->format('Y-m-d');

        // 2. Siapkan nilai default
        $hv = $validated['HV'] ?? 0;
        $hkg = $validated['HKG'] ?? 0;
        $hbok = $validated['HBOK'] ?? 0;
        $hg = $validated['HG'] ?? 0;

        // 3. Generate KUNCI
        $kunci = ($validated['KETERANGAN'] ?? '')
            .($validated['JENIS'] ?? '')
            .($validated['DARI'] ?? '')
            .($validated['SAMPAI'] ?? '');

        // 4. Simpan master dan harga customer dalam satu transaksi
        $priceCus = DB::transaction(function () use (
            $validated,
            $user,
            $tanggal,
            $hv,
            $hkg,
            $hbok,
            $hg,
            $kunci
        ) {
            // Cari kode master terakhir.
            // Format contoh: PRC0000001
            $lastCode = Price::where('KODE', 'LIKE', 'PRC%')
                ->orderByDesc('KODE')
                ->lockForUpdate()
                ->value('KODE');

            $number = $lastCode
                ? ((int) substr($lastCode, 3)) + 1
                : 1;

            $kode = 'PRC'.str_pad(
                $number,
                7,
                '0',
                STR_PAD_LEFT
            );

            // 5. Simpan master ke tabel prices terlebih dahulu
            $price = Price::create([
                'KODE' => $kode,
                'HARGA' => $validated['HARGA'],
                'TANGGAL' => $tanggal,
                'DARI' => $validated['DARI'],
                'SAMPAI' => $validated['SAMPAI'],
                'RUTE' => $validated['RUTE'],
                'KETERANGAN' => $validated['KETERANGAN'],
                'HV' => $hv,
                'HKG' => $hkg,
                'HBOK' => $hbok,
                'KUNCI' => $kunci,
                'HG' => $hg,
                'JENIS' => $validated['JENIS'],
                'USER' => $user,
                'USEREDIT' => $user,
            ]);

            // 6. Simpan harga customer ke tabel pricecus
            // KODE harus sama dengan KODE master di prices
            return PriceCus::create([
                'KODECUS' => $validated['KODECUS'],
                'KODE' => $price->KODE,
                'NAMACUS' => $validated['NAMACUS'] ?? null,
                'FROM' => $validated['FROM'] ?? null,
                'TO' => $validated['TO'] ?? null,
                'HARGA' => $validated['HARGA'],
                'TANGGAL' => $tanggal,
                'DARI' => $validated['DARI'],
                'SAMPAI' => $validated['SAMPAI'],
                'RUTE' => $validated['RUTE'],
                'KETERANGAN' => $validated['KETERANGAN'],
                'HV' => $hv,
                'HKG' => $hkg,
                'HBOK' => $hbok,
                'KUNCI' => $kunci,
                'HG' => $hg,
                'JENIS' => $validated['JENIS'],
                'USER' => $user,
                'USEREDIT' => $user,
            ]);
        });

        // 7. Kembali ke halaman harga customer
        return redirect()
            ->route(
                'price-customer.customer-prices',
                $priceCus->KODECUS
            )
            ->with(
                'success',
                'Master harga dan harga customer berhasil disimpan.'
            );
    }

    public function edit(PriceCus $priceCus)
    {
        return view('price-customer.edit', compact('priceCus'));
    }

    public function update(Request $request, PriceCus $priceCus)
    {
        $validated = $request->validate([
            'KODECUS' => 'required|string|max:50',
            'KODE' => 'nullable|string|max:50',
            'NAMACUS' => 'nullable|string|max:50',
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
        if (empty($validated['FROM']) && ! empty($request->input('DARI'))) {
            $validated['FROM'] = (string) $request->input('DARI');
        }
        if (empty($validated['TO']) && ! empty($request->input('SAMPAI'))) {
            $validated['TO'] = (string) $request->input('SAMPAI');
        }

        // Set default values
        $validated['HV'] = $validated['HV'] ?? 0;
        $validated['HKG'] = $validated['HKG'] ?? 0;
        $validated['HBOK'] = $validated['HBOK'] ?? 0;
        $validated['HG'] = $validated['HG'] ?? 0;

        // Generate KUNCI
        $validated['KUNCI'] = ($validated['KETERANGAN'] ?? '').
                              ($validated['JENIS'] ?? '').
                              ($validated['DARI'] ?? '').
                              ($validated['SAMPAI'] ?? '');

        $priceCus->update($validated);

        return redirect()->route('price-customer.customer-prices', $priceCus->KODECUS)
            ->with('success', 'Price customer berhasil diperbarui.');
    }

    public function destroy(PriceCus $priceCus)
    {
        $kodecus = $priceCus->KODECUS;
        $keterangan = trim((string) $priceCus->KETERANGAN);

        $isUsed = \DB::table('expedisis')
            ->where('CUSTOMER_KODE', $kodecus)
            ->whereRaw('TRIM(PESANAN) = ?', [$keterangan])
            ->exists();

        if ($isUsed) {
            return redirect()
                ->route('price-customer.customer-prices', $kodecus)
                ->with(
                    'error',
                    'Price customer tidak bisa dihapus karena sudah digunakan pada data ekspedisi.'
                );
        }

        $priceCus->delete();

        return redirect()
            ->route('price-customer.customer-prices', $kodecus)
            ->with('success', 'Price customer berhasil dihapus.');
    }

    public function getPrice($kodecus)
    {
        $priceCus = PriceCus::where('KODECUS', $kodecus)->get();

        return response()->json($priceCus);
    }
}
