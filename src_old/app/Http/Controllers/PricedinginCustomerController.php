<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Pricedingincus;
use App\Models\Pricedinginhis;
use App\Models\Pricedingin;
use App\Models\Mcustomer;
use App\Models\Kendaraan;

class PricedinginCustomerController extends Controller
{
    public function index(){
        return view('priceDinginCustomer.priceDinginCustomer');
    }

    public function getData(){
        $customers = Mcustomer::select(['id', 'CUSTOMER', 'kode_cus', 'NAMACUST', 'TYPECUST', 'TELEPON', 'EMAIL', 'created_at']);

        return DataTables::of($customers)
            ->addIndexColumn()
            ->addColumn('action', function($customer) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-btn-customer-price-dingin" data-id="'.$customer->CUSTOMER.'" data-bs-toggle="tooltip" title="View">
                            <i class="bx bx-show"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getPrice(Request $request, $kodecus)
    {
        $customer = DB::table('mcustomer')
            ->where('CUSTOMER', $kodecus)
            ->first();

        $customerData = [
            'customer_kode' => $customer->CUSTOMER ?? null,
            'customer_nama' => $customer->NAMACUST ?? null,
            'jenis_usaha'   => $customer->TYPECUST ?? null,
            'alamat'        => $customer->ALAMAT1 ?? null,
            'pemilik_nama'  => $customer->nama_p ?? null,
        ];

        if (!$customer) {
            return DataTables::of(collect([]))
                ->with($customerData)
                ->make(true);
        }

        // $pricedingincus = DB::table('pricedingincus as pc')
        //     ->leftJoin('kendaraan as k', 'pc.KODE', '=', 'k.KODE')
        //     ->select('pc.*', 'k.NAMA as nama_kendaraan')
        //     ->where('pc.KODECUS', $kodecus)
        //     ->get();

        return DataTables::of(DB::table('pricedingincus as pc')
            ->leftJoin('kendaraan as k', 'pc.KODE', '=', 'k.KODE')
            ->select('pc.*', 'k.NAMA as nama_kendaraan')
            ->where('pc.KODECUS', $kodecus))
            ->addIndexColumn()

            ->with($customerData)

            ->addColumn('harga_html', function ($row) {
                return '<span class="editable-price-dingin"
                    contenteditable="true"
                    data-original="'.$row->HARGA.'"
                    data-kode="'.$row->KODEDGN.'"
                    data-kodecus="'.$row->KODECUS.'"
                    style="background:#fff7d1; padding:6px; border-radius:4px;">
                    '.$row->HARGA.'
                </span>';
            })

            ->addColumn('action', function ($row) {
                return '<button class="btn btn-success btn-sm save-price-dingin"
                    data-id="'.$row->id.'"
                    data-kode="'.$row->KODEDGN.'"
                    data-original="'.$row->HARGA.'">
                    <i class="bx bx-save"></i>
                </button>';
            })

            ->rawColumns(['harga_html', 'action'])
            ->make(true);
    }

    public function updateHargaDinginCustomer(Request $request)
    {
        $kodecus = $request->kodecus;
        $existingKode = Pricedingincus::where('KODECUS', $kodecus)
            ->pluck('KODEDGN')
            ->toArray();
        $newPrices = Pricedingin::whereNotIn('id', $existingKode)
            ->get();
        if ($newPrices->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada harga dingin baru'
            ]);
        }
        $insertData = [];
        foreach ($newPrices as $price) {
            $insertData[] = [
                'KODECUS' => $kodecus,
                // relasi ke master
                'KODEDGN' => $price->id,
                'TANGGAL' => $price->TANGGAL,
                // copy data master
                'KODE' => $price->KODE,
                'PERIODE' => $price->PERIODE,
                'PLAT' => $price->PLAT,
                'ITEM' => $price->ITEM,
                'HARGA' => $price->HARGA,
                'JENIS' => $price->JENIS,
                'USER' => $price->USER,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Pricedingincus::insert($insertData);
        return response()->json([
            'success' => true,
            'message' => count($insertData) . ' harga dingin baru berhasil ditambahkan'
        ]);
    }

    public function saveCustomerRow(Request $request)
    {
        $request->validate([
            'kode' => 'required',
            'harga' => 'required|numeric',
            'kodecus' => 'required',
        ]);

        $updated = Pricedingincus::where('KODEDGN', $request->kode)
            ->where('KODECUS', $request->kodecus)
            ->update([
                'HARGA' => $request->harga,
                'USEREDIT' => Auth::check()
                    ? Auth::user()->user_id
                    : 'System',
            ]);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Data harga customer tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Harga berhasil diupdate'
        ]);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'KODECUS' => 'required|string|max:50',
            'KODE' => 'required|string|max:50',
            'PERIODE' => 'required|string|max:50',
            'PLAT' => 'required|string|max:20',
            'ITEM' => 'required|string|max:255',
            'HARGA' => 'required|integer|min:0',
            'USER' => 'required|string|max:100',
        ]);

        // Ambil data kendaraan
        $kode = $request->input('KODE');
        $kendaraan = Kendaraan::where('KODE', $kode)->first();

        // Kalau kendaraan ada, tambahkan kolom jenis ke validated
        if ($kendaraan) {
            $validated['JENIS'] = $kendaraan->JENIS;
        } else {
            // Optional: kalau mau error kalau kendaraan tidak ditemukan
            return response()->json([
                'success' => false,
                'message' => 'Kendaraan tidak ditemukan',
            ], 404);
        }

        // Generate KODE
        $lastCode = Pricedingincus::orderBy('id', 'desc')->value('KODEDGN');
        $number = $lastCode ? (int) substr($lastCode, 4) + 1 : 1;
        $kodedgn = 'PRDK' . str_pad($number, 7, '0', STR_PAD_LEFT);
        $validated['KODEDGN'] = $kodedgn;

        // Simpan
        $pricedingin = Pricedingincus::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $pricedingin
        ]);
    }

    public function getPriceModal(Request $request, $kodecus)
{
    $customer = DB::table('mcustomer')
        ->where('CUSTOMER', $kodecus)
        ->first();

    $customerData = [
        'customer_kode' => $customer->CUSTOMER ?? null,
        'customer_nama' => $customer->NAMACUST ?? null,
        'jenis_usaha'   => $customer->TYPECUST ?? null,
        'alamat'        => $customer->ALAMAT1 ?? null,
        'pemilik_nama'  => $customer->nama_p ?? null,
    ];

    if (!$customer) {
        return DataTables::of(collect([]))
            ->with($customerData)
            ->make(true);
    }

    return DataTables::of(
        DB::table('pricedingincus as pc')
            ->leftJoin('kendaraan as k', 'pc.KODE', '=', 'k.KODE')
            ->select('pc.*', 'k.NAMA as nama_kendaraan')
            ->where('pc.KODECUS', $kodecus)
    )
        ->addIndexColumn()

        ->with($customerData)

        ->addColumn('harga_html', function ($row) {
            return '<span
                data-original="'.$row->HARGA.'"
                data-kode="'.$row->KODEDGN.'"
                data-kodecus="'.$row->KODECUS.'"
                style="padding:6px; border-radius:4px;">
                '.$row->HARGA.'
            </span>';
        })

        ->addColumn('action', function ($row) {
            return '<button class="btn btn-success btn-sm pick-price-dgn"
                data-id="'.$row->id.'"
                data-kode="'.$row->KODEDGN.'"
                data-kodembl="'.$row->KODE.'"
                data-original="'.$row->HARGA.'">
                <i class="bx bx-check"></i>
            </button>';
        })

        ->rawColumns(['harga_html', 'action'])
        ->make(true);
}

}

// Get Data Customer Price Lama
// public function getPrice(Request $request, $kodecus){
//         // Ambil data customer
//         $customer = DB::table('mcustomer as c')
//             ->where('c.CUSTOMER', $kodecus)
//             ->first();

//         // Siapkan metadata customer
//         $customerData = [
//             'customer_kode' => $customer->CUSTOMER ?? null,
//             'customer_nama' => $customer->NAMACUST ?? null,
//             'jenis_usaha'   => $customer->TYPECUST ?? null,
//             'alamat'        => $customer->ALAMAT1 ?? null,
//             'pemilik_nama'  => $customer->nama_p ?? null,
//         ];

//         // Jika customer tidak ditemukan → return kosong tapi metadata tetap ada
//         if (!$customer) {
//             return DataTables::of(collect([]))
//                 ->with($customerData)
//                 ->make(true);
//         }

//         $customerCreatedAt = $customer->created_at;
//         $final = collect();


//         // ============================================================
//         // 1️⃣ PRIORITAS pricedingincus (punya KODECUS asli)
//         // ============================================================

//         $pricedingincus = DB::table('pricedingincus as pc')
//             ->leftJoin('kendaraan as k', 'pc.KODE', '=', 'k.KODE')
//             ->select('pc.*', 'k.NAMA as nama_kendaraan')
//             ->where('pc.KODECUS', $kodecus)
//             ->get();

//         foreach ($pricedingincus as $pc) {
//             $pc->source = 'pricedingincus';
//             // pc sudah punya KODECUS bawaan → aman
//             $final->push($pc);
//         }

//         $usedCodes = $pricedingincus->pluck('KODE')->toArray();



//         // ============================================================
//         // 2️⃣ PRIORITAS PRICEDINGINHIS (HARUS ditambah KODECUS)
//         // ============================================================

//         $allCodes = DB::table('pricedingin')->pluck('KODE')->unique();

//         foreach ($allCodes as $kd) {

//             if (in_array($kd, $usedCodes)) continue;

//             $his = DB::table('pricedinginhis as ph')
//                 ->leftJoin('kendaraan as k', 'ph.KODE', '=', 'k.KODE')
//                 ->select('ph.*', 'k.NAMA as nama_kendaraan')
//                 ->where('ph.KODE', $kd)
//                 ->where('ph.created_at', '>=', $customerCreatedAt)
//                 ->orderBy('ph.created_at', 'asc')
//                 ->first();

//             if ($his) {
//                 $his->source = 'pricedinginhis';
//                 $his->KODECUS = $kodecus; // FIX WAJIB
//                 $final->push($his);
//                 $usedCodes[] = $kd;
//             }
//         }



//         // ============================================================
//         // 3️⃣ DEFAULT PRICES (HARUS ditambah KODECUS)
//         // ============================================================

//         foreach ($allCodes as $kd) {

//             if (in_array($kd, $usedCodes)) continue;

//             $default = DB::table('pricedingin as p')
//                 ->leftJoin('kendaraan as k', 'p.KODE', '=', 'k.KODE')
//                 ->select('p.*', 'k.NAMA as nama_kendaraan')
//                 ->where('p.KODE', $kd)
//                 ->first();

//             if ($default) {
//                 $default->source = 'pricedingin';
//                 $default->KODECUS = $kodecus; // FIX WAJIB
//                 $final->push($default);
//                 $usedCodes[] = $kd;
//             }
//         }



//         // ============================================================
//         // RETURN KE DATATABLES
//         // ============================================================

//         return DataTables::of($final)
//             ->addIndexColumn()
//             ->with($customerData)
//             ->addColumn('harga_html', function ($row) {
//                 return '<span class="editable-price-dingin"
//                     contenteditable="true"
//                     data-original="'.$row->HARGA.'"
//                     data-kode="'.$row->KODEDGN.'"
//                     data-kodecus="'.$row->KODECUS.'"
//                     style="background:#fff7d1; padding:6px; border-radius:4px;">
//                     '.$row->HARGA.'</span>';
//             })
//             ->addColumn('action', function ($row) {
//                 return '<button class="btn btn-success btn-sm save-price-dingin"
//                     data-id="'.$row->id.'"
//                     data-kode="'.$row->KODEDGN.'"
//                     data-source="'.$row->source.'"
//                     data-original="'.$row->HARGA.'">
//                     <i class="bx bx-save"></i></button>';
//             })
//             ->rawColumns(['harga_html', 'action'])
//             ->make(true);
//     }

// ########### Update Row #################
// public function saveCustomerRow(Request $request){
//         $request->validate([
//             'kode' => 'required|string',
//             'harga' => 'required|numeric',
//             'kodecus' => 'required|string',
//         ]);

//         $kode = $request->kode;
//         $hargaBaru = $request->harga;
//         $kodecus = $request->kodecus;

//         // 1. Cek apakah kode sudah ada di pricecus
//         $existing = Pricedingincus::where('KODEDGN', $kode)
//                             ->where('KODECUS', $kodecus)
//                             ->first();

//         if ($existing) {
//             // -------------------------
//             //   UPDATE HARGA SAJA
//             // -------------------------
//             $existing->update([
//                 'HARGA' => $hargaBaru,
//                 'USEREDIT' => Auth::check() ? Auth::user()->user_id : 'System',
//             ]);

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Harga berhasil diupdate untuk customer ini'
//             ]);
//         }

//         // -------------------------
//         //   INSERT BARU
//         // -------------------------

//         // Ambil data dari harga umum
//         $price = Pricedingin::where('KODEDGN', $kode)->first();

//         if (!$price) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Kode harga tidak ditemukan di tabel Prices'
//             ]);
//         }

//         // Buat insert ke pricecus
//         Pricedingincus::create([
//             'KODECUS' => $kodecus,
//             'KODEDGN' => $price->KODEDGN,
//             'KODE' => $price->KODE,
//             'PERIODE' => $price->PERIODE,
//             'PLAT' => $price->PLAT,
//             'JENIS' => $price->JENIS,
//             'ITEM' => $price->ITEM,

//             // Harga khusus customer
//             'HARGA' => $hargaBaru,


//             'USER' => Auth::check() ? Auth::user()->user_id : 'System',
//             'USEREDIT' => Auth::check() ? Auth::user()->user_id : 'System',
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Harga khusus customer berhasil ditambahkan'
//         ]);
//     }

// ################### MODAL GET DATA HARGA CUS ###############################
// public function getPriceModal(Request $request, $kodecus){
//         // Ambil data customer
//         $customer = DB::table('mcustomer as c')
//             ->where('c.CUSTOMER', $kodecus)
//             ->first();

//         // Siapkan metadata customer
//         $customerData = [
//             'customer_kode' => $customer->CUSTOMER ?? null,
//             'customer_nama' => $customer->NAMACUST ?? null,
//             'jenis_usaha'   => $customer->TYPECUST ?? null,
//             'alamat'        => $customer->ALAMAT1 ?? null,
//             'pemilik_nama'  => $customer->nama_p ?? null,
//         ];

//         // Jika customer tidak ditemukan → return kosong tapi metadata tetap ada
//         if (!$customer) {
//             return DataTables::of(collect([]))
//                 ->with($customerData)
//                 ->make(true);
//         }

//         $customerCreatedAt = $customer->created_at;
//         $final = collect();


//         // ============================================================
//         // 1️⃣ PRIORITAS pricedingincus (punya KODECUS asli)
//         // ============================================================

//         $pricedingincus = DB::table('pricedingincus as pc')
//             ->leftJoin('kendaraan as k', 'pc.KODE', '=', 'k.KODE')
//             ->select('pc.*', 'k.NAMA as nama_kendaraan')
//             ->where('pc.KODECUS', $kodecus)
//             ->get();

//         foreach ($pricedingincus as $pc) {
//             $pc->source = 'pricedingincus';
//             // pc sudah punya KODECUS bawaan → aman
//             $final->push($pc);
//         }

//         $usedCodes = $pricedingincus->pluck('KODE')->toArray();



//         // ============================================================
//         // 2️⃣ PRIORITAS PRICEDINGINHIS (HARUS ditambah KODECUS)
//         // ============================================================

//         $allCodes = DB::table('pricedingin')->pluck('KODE')->unique();

//         foreach ($allCodes as $kd) {

//             if (in_array($kd, $usedCodes)) continue;

//             $his = DB::table('pricedinginhis as ph')
//                 ->leftJoin('kendaraan as k', 'ph.KODE', '=', 'k.KODE')
//                 ->select('ph.*', 'k.NAMA as nama_kendaraan')
//                 ->where('ph.KODE', $kd)
//                 ->where('ph.created_at', '>=', $customerCreatedAt)
//                 ->orderBy('ph.created_at', 'asc')
//                 ->first();

//             if ($his) {
//                 $his->source = 'pricedinginhis';
//                 $his->KODECUS = $kodecus; // FIX WAJIB
//                 $final->push($his);
//                 $usedCodes[] = $kd;
//             }
//         }



//         // ============================================================
//         // 3️⃣ DEFAULT PRICES (HARUS ditambah KODECUS)
//         // ============================================================

//         foreach ($allCodes as $kd) {

//             if (in_array($kd, $usedCodes)) continue;

//             $default = DB::table('pricedingin as p')
//                 ->leftJoin('kendaraan as k', 'p.KODE', '=', 'k.KODE')
//                 ->select('p.*', 'k.NAMA as nama_kendaraan')
//                 ->where('p.KODE', $kd)
//                 ->first();

//             if ($default) {
//                 $default->source = 'pricedingin';
//                 $default->KODECUS = $kodecus; // FIX WAJIB
//                 $final->push($default);
//                 $usedCodes[] = $kd;
//             }
//         }



//         // ============================================================
//         // RETURN KE DATATABLES
//         // ============================================================

//         return DataTables::of($final)
//             ->addIndexColumn()
//             ->with($customerData)
//             ->addColumn('harga_html', function ($row) {
//                 return '<span
//                     data-original="'.$row->HARGA.'"
//                     data-kode="'.$row->KODEDGN.'"
//                     data-kodecus="'.$row->KODECUS.'"
//                     style="padding:6px; border-radius:4px;">
//                     '.$row->HARGA.'</span>';
//             })
//             ->addColumn('action', function ($row) {
//                 return '<button class="btn btn-success btn-sm pick-price-dgn"
//                     data-id="'.$row->id.'"
//                     data-kode="'.$row->KODEDGN.'"
//                     data-kodembl="'.$row->KODE.'"
//                     data-source="'.$row->source.'"
//                     data-original="'.$row->HARGA.'">
//                     <i class="bx bx-check"></i></button>';
//             })
//             ->rawColumns(['harga_html', 'action'])
//             ->make(true);
//     }
