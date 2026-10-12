<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Pricecus;
use App\Models\Pricecushis;
use App\Models\Prices;
use App\Models\Mcustomer;

class PricesCustomerController extends Controller
{
    public function index(){
        return view('priceCustomer.priceCustomer');
    }

    public function getData(){
        $customers = Mcustomer::select(['id', 'CUSTOMER', 'kode_cus', 'NAMACUST', 'TYPECUST', 'TELEPON', 'EMAIL', 'created_at']);

        return DataTables::of($customers)
            ->addIndexColumn()
            ->addColumn('action', function($customer) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-btn-customer-price" id="show_price_cus" data-id="'.$customer->CUSTOMER.'" data-bs-toggle="tooltip" title="View">
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

        $pricecus = DB::table('pricecus as pc')
            ->select('pc.*', 'pc.RUTE as nama_rute')
            ->where('pc.KODECUS', $kodecus)
            ->get();

        return DataTables::of($pricecus)
            ->addIndexColumn()

            ->with($customerData)

            ->addColumn('jenis_text', function ($row) {
                return $row->JENIS == 1 ? 'Eceran' : 'Booking';
            })

            ->addColumn('harga_html', function ($row) {
                return '<span class="editable-price"
                    contenteditable="true"
                    data-original="'.$row->HARGA.'"
                    data-kode="'.$row->KODE.'"
                    data-kodecus="'.$row->KODECUS.'"
                    style="background:#fff7d1; padding:6px; border-radius:4px;">
                    '.$row->HARGA.'
                </span>';
            })

            ->addColumn('aksi', function ($row) {
                return '<button class="btn btn-success btn-sm save-price"
                    data-id="'.$row->id.'"
                    data-kode="'.$row->KODE.'"
                    data-original="'.$row->HARGA.'">
                    <i class="bx bx-save"></i>
                </button>';
            })

            ->rawColumns(['harga_html', 'aksi'])
            ->make(true);
    }

    public function updateHargaRuteCustomer(Request $request)
    {
        $kodecus = $request->kodecus;

        $existingKode = Pricecus::where('KODECUS', $kodecus)
            ->pluck('KODE')
            ->toArray();

        $newPrices = Prices::whereNotIn('id', $existingKode)
            ->get();

        if ($newPrices->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada harga baru'
            ]);
        }

        $insertData = [];

        foreach ($newPrices as $price) {
            $insertData[] = [
                'KODECUS'   => $kodecus,
                'KODE'      => $price->id,
                'TANGGAL'   => $price->TANGGAL,
                'KETERANGAN'=> $price->KETERANGAN,
                'DARI'      => $price->DARI,
                'SAMPAI'    => $price->SAMPAI,
                'RUTE'      => $price->RUTE,
                'HARGA'     => $price->HARGA,
                'HV'        => $price->HV,
                'HKG'       => $price->HKG,
                'HBOK'      => $price->HBOK,
                'USER'      => $price->USER,
                'USEREDIT'  => $price->USEREDIT,
                'KUNCI'     => $price->KUNCI,
                'HG'        => $price->HG,
                'JENIS'     => $price->JENIS,
                'created_at'=> now(),
                'updated_at'=> now(),
            ];
        }

        Pricecus::insert($insertData);

        return response()->json([
            'success' => true,
            'message' => count($insertData).' harga baru berhasil ditambahkan'
        ]);
    }

    public function saveCustomerRow(Request $request)
    {
        $request->validate([
            'kode' => 'required',
            'harga' => 'required|numeric',
            'kodecus' => 'required',
        ]);

        $updated = Pricecus::where('KODE', $request->kode)
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

    // ############################ gak diakai lagi karna fungsi simpan harga baru sudah gak dipakai #############
    public function store(Request $request)
    {
        $request->validate([
            'kode_val_pricecus' => 'required|string|max:50',
            'keterangan_pricecus' => 'required|string|max:50',
            'dari_pricecus' => 'required|numeric',
            'sampai_pricecus' => 'required|numeric',
            'rute_pricecus' => 'required|string|max:30',
            'harga_pricecus' => 'required|numeric',
            'jenis_valcus' => 'required|string|max:1',
            'rute_val_pricecus' => 'required'
        ]);

        //  Generate Kode
        $lastCode = Pricecus::where('KODE', 'LIKE', 'PRCK%')
                    ->orderBy('KODE', 'desc')
                    ->value('KODE');
        $number = $lastCode ? (int) substr($lastCode, 4) + 1 : 1;
        $kode = 'PRCK' . str_pad($number, 7, '0', STR_PAD_LEFT);


        $Prices = Pricecus::create([
            'KODECUS' =>  $request->kode_val_pricecus,
            'KODE' => $kode,
            'KETERANGAN' => $request->keterangan_pricecus,
            'DARI' => $request->dari_pricecus,
            'SAMPAI' => $request->sampai_pricecus,
            'RUTE' => $request->rute_pricecus,
            'HARGA' => $request->harga_pricecus,
            'HV' => 0,
            'HKG' => 0,
            'HBOK' => 0,
            'JENIS' => $request->jenis_valcus,
            'USER' => Auth::check() ? Auth::user()->user_id : 'System',
            'USEREDIT' => Auth::check() ? Auth::user()->user_id : 'System',
            'KUNCI' => $request->keterangan_pricecus . $request->jenis_valcus . $request->dari_pricecus . $request->sampai_pricecus,
            'HG' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $Prices
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
            DB::table('pricecus as pc')
                ->select('pc.*', 'pc.RUTE as nama_rute')
                ->where('pc.KODECUS', $kodecus)
        )
            ->addIndexColumn()

            ->with($customerData)

            ->addColumn('jenis_text', function ($row) {
                return $row->JENIS == 1 ? 'Eceran' : 'Booking';
            })

            ->addColumn('harga_html', function ($row) {
                return '<span
                    data-original="'.$row->HARGA.'"
                    data-kode="'.$row->KODE.'"
                    data-kodecus="'.$row->KODECUS.'"
                    style="padding:6px; border-radius:4px;">
                    '.$row->HARGA.'
                </span>';
            })

            ->addColumn('aksi', function ($row) {
                return '<button class="btn btn-success btn-sm pick-price-exp"
                    data-id="'.$row->id.'"
                    data-kode="'.$row->KODE.'"
                    data-jenis="'.$row->JENIS.'"
                    data-original="'.$row->HARGA.'">
                    <i class="bx bx-check"></i>
                </button>';
            })

            ->rawColumns(['harga_html', 'aksi'])
            ->make(true);
    }

}

// ############ get price yang lama ##################
// public function getPrice(Request $request, $kodecus){
    //     // Ambil data customer
    //     $customer = DB::table('mcustomer as c')
    //         ->where('c.CUSTOMER', $kodecus)
    //         ->first();

    //     // Siapkan metadata customer
    //     $customerData = [
    //         'customer_kode' => $customer->CUSTOMER ?? null,
    //         'customer_nama' => $customer->NAMACUST ?? null,
    //         'jenis_usaha'   => $customer->TYPECUST ?? null,
    //         'alamat'        => $customer->ALAMAT1 ?? null,
    //         'pemilik_nama'  => $customer->nama_p ?? null,
    //     ];

    //     // Jika customer tidak ditemukan → return kosong tapi metadata tetap ada
    //     if (!$customer) {
    //         return DataTables::of(collect([]))
    //             ->with($customerData)
    //             ->make(true);
    //     }

    //     $customerCreatedAt = $customer->created_at;
    //     $final = collect();


    //     // ============================================================
    //     // 1️⃣ PRIORITAS PRICECUS (punya KODECUS asli)
    //     // ============================================================

    //     $pricecus = DB::table('pricecus as pc')
    //         ->select('pc.*', 'pc.RUTE as nama_rute')
    //         ->where('pc.KODECUS', $kodecus)
    //         ->get();

    //     foreach ($pricecus as $pc) {
    //         $pc->source = 'pricecus';
    //         // pc sudah punya KODECUS bawaan → aman
    //         $final->push($pc);
    //     }

    //     $usedCodes = $pricecus->pluck('KODE')->toArray();



    //     // ============================================================
    //     // 2️⃣ PRIORITAS PRICECUSHIS (HARUS ditambah KODECUS)
    //     // ============================================================

    //     $allCodes = DB::table('prices')->pluck('KODE')->unique();

    //     foreach ($allCodes as $kd) {

    //         if (in_array($kd, $usedCodes)) continue;

    //         $his = DB::table('pricecushis as ph')
    //             ->select('ph.*', 'ph.RUTE as nama_rute')
    //             ->where('ph.KODE', $kd)
    //             ->where('ph.created_at', '>=', $customerCreatedAt)
    //             ->orderBy('ph.created_at', 'asc')
    //             ->first();

    //         if ($his) {
    //             $his->source = 'pricecushis';
    //             $his->KODECUS = $kodecus; // FIX WAJIB
    //             $final->push($his);
    //             $usedCodes[] = $kd;
    //         }
    //     }



    //     // ============================================================
    //     // 3️⃣ DEFAULT PRICES (HARUS ditambah KODECUS)
    //     // ============================================================

    //     foreach ($allCodes as $kd) {

    //         if (in_array($kd, $usedCodes)) continue;

    //         $default = DB::table('prices as p')
    //             ->select('p.*', 'p.RUTE as nama_rute')
    //             ->where('p.KODE', $kd)
    //             ->first();

    //         if ($default) {
    //             $default->source = 'prices';
    //             $default->KODECUS = $kodecus; // FIX WAJIB
    //             $final->push($default);
    //             $usedCodes[] = $kd;
    //         }
    //     }



    //     // ============================================================
    //     // RETURN KE DATATABLES
    //     // ============================================================

    //     return DataTables::of($final)
    //         ->addIndexColumn()
    //         ->with($customerData)
    //         ->addColumn('jenis_text', function ($row) {
    //             return $row->JENIS == 1 ? 'Eceran' : 'Booking';
    //         })
    //         ->addColumn('harga_html', function ($row) {
    //             return '<span class="editable-price"
    //                 contenteditable="true"
    //                 data-original="'.$row->HARGA.'"
    //                 data-kode="'.$row->KODE.'"
    //                 data-kodecus="'.$row->KODECUS.'"
    //                 style="background:#fff7d1; padding:6px; border-radius:4px;">
    //                 '.$row->HARGA.'</span>';
    //         })
    //         ->addColumn('aksi', function ($row) {
    //             return '<button class="btn btn-success btn-sm save-price"
    //                 data-id="'.$row->id.'"
    //                 data-kode="'.$row->KODE.'"
    //                 data-source="'.$row->source.'"
    //                 data-original="'.$row->HARGA.'">
    //                 <i class="bx bx-save"></i></button>';
    //         })
    //         ->rawColumns(['harga_html', 'aksi'])
    //         ->make(true);
    // }

    // ############# FUNGSI UPDATE ####################
    // public function saveCustomerRow(Request $request){
    //     $request->validate([
    //         'kode' => 'required|string',
    //         'harga' => 'required|numeric',
    //         'kodecus' => 'required|string',
    //     ]);

    //     $kode = $request->kode;
    //     $hargaBaru = $request->harga;
    //     $kodecus = $request->kodecus;

    //     // 1. Cek apakah kode sudah ada di pricecus
    //     $existing = Pricecus::where('KODE', $kode)
    //                         ->where('KODECUS', $kodecus)
    //                         ->first();

    //     if ($existing) {
    //         // -------------------------
    //         //   UPDATE HARGA SAJA
    //         // -------------------------
    //         $existing->update([
    //             'HARGA' => $hargaBaru,
    //             'USEREDIT' => Auth::check() ? Auth::user()->user_id : 'System',
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Harga berhasil diupdate untuk customer ini'
    //         ]);
    //     }

    //     // -------------------------
    //     //   INSERT BARU
    //     // -------------------------

    //     // Ambil data dari harga umum
    //     $price = Prices::where('KODE', $kode)->first();

    //     if (!$price) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Kode harga tidak ditemukan di tabel Prices'
    //         ]);
    //     }

    //     // Buat insert ke pricecus
    //     Pricecus::create([
    //         'KODECUS' => $kodecus,
    //         'KODE' => $price->KODE,
    //         'KETERANGAN' => $price->KETERANGAN,
    //         'DARI' => $price->DARI,
    //         'SAMPAI' => $price->SAMPAI,
    //         'RUTE' => $price->RUTE,

    //         // Harga khusus customer
    //         'HARGA' => $hargaBaru,

    //         'HV' => $price->HV,
    //         'HKG' => $price->HKG,
    //         'HBOK' => $price->HBOK,
    //         'JENIS' => $price->JENIS,
    //         'KUNCI' => $price->KUNCI,
    //         'HG' => $price->HG,

    //         'USER' => Auth::check() ? Auth::user()->user_id : 'System',
    //         'USEREDIT' => Auth::check() ? Auth::user()->user_id : 'System',
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Harga khusus customer berhasil ditambahkan'
    //     ]);
    // }

    // ################ Untuk Modal Suurat Jalan ####################
    // public function getPriceModal(Request $request, $kodecus){
    //     // Ambil data customer
    //     $customer = DB::table('mcustomer as c')
    //         ->where('c.CUSTOMER', $kodecus)
    //         ->first();

    //     // Siapkan metadata customer
    //     $customerData = [
    //         'customer_kode' => $customer->CUSTOMER ?? null,
    //         'customer_nama' => $customer->NAMACUST ?? null,
    //         'jenis_usaha'   => $customer->TYPECUST ?? null,
    //         'alamat'        => $customer->ALAMAT1 ?? null,
    //         'pemilik_nama'  => $customer->nama_p ?? null,
    //     ];

    //     // Jika customer tidak ditemukan → return kosong tapi metadata tetap ada
    //     if (!$customer) {
    //         return DataTables::of(collect([]))
    //             ->with($customerData)
    //             ->make(true);
    //     }

    //     $customerCreatedAt = $customer->created_at;
    //     $final = collect();


    //     // ============================================================
    //     // 1️⃣ PRIORITAS PRICECUS (punya KODECUS asli)
    //     // ============================================================

    //     $pricecus = DB::table('pricecus as pc')
    //         ->select('pc.*', 'pc.RUTE as nama_rute')
    //         ->where('pc.KODECUS', $kodecus)
    //         ->get();

    //     foreach ($pricecus as $pc) {
    //         $pc->source = 'pricecus';
    //         // pc sudah punya KODECUS bawaan → aman
    //         $final->push($pc);
    //     }

    //     $usedCodes = $pricecus->pluck('KODE')->toArray();



    //     // ============================================================
    //     // 2️⃣ PRIORITAS PRICECUSHIS (HARUS ditambah KODECUS)
    //     // ============================================================

    //     $allCodes = DB::table('prices')->pluck('KODE')->unique();

    //     foreach ($allCodes as $kd) {

    //         if (in_array($kd, $usedCodes)) continue;

    //         $his = DB::table('pricecushis as ph')
    //             ->select('ph.*', 'ph.RUTE as nama_rute')
    //             ->where('ph.KODE', $kd)
    //             ->where('ph.created_at', '>=', $customerCreatedAt)
    //             ->orderBy('ph.created_at', 'asc')
    //             ->first();

    //         if ($his) {
    //             $his->source = 'pricecushis';
    //             $his->KODECUS = $kodecus; // FIX WAJIB
    //             $final->push($his);
    //             $usedCodes[] = $kd;
    //         }
    //     }



    //     // ============================================================
    //     // 3️⃣ DEFAULT PRICES (HARUS ditambah KODECUS)
    //     // ============================================================

    //     foreach ($allCodes as $kd) {

    //         if (in_array($kd, $usedCodes)) continue;

    //         $default = DB::table('prices as p')
    //             ->select('p.*', 'p.RUTE as nama_rute')
    //             ->where('p.KODE', $kd)
    //             ->first();

    //         if ($default) {
    //             $default->source = 'prices';
    //             $default->KODECUS = $kodecus; // FIX WAJIB
    //             $final->push($default);
    //             $usedCodes[] = $kd;
    //         }
    //     }



    //     // ============================================================
    //     // RETURN KE DATATABLES
    //     // ============================================================

    //     return DataTables::of($final)
    //         ->addIndexColumn()
    //         ->with($customerData)
    //         ->addColumn('jenis_text', function ($row) {
    //             return $row->JENIS == 1 ? 'Eceran' : 'Booking';
    //         })
    //         ->addColumn('harga_html', function ($row) {
    //             return '<span
    //                 data-original="'.$row->HARGA.'"
    //                 data-kode="'.$row->KODE.'"
    //                 data-kodecus="'.$row->KODECUS.'"
    //                 style="padding:6px; border-radius:4px;">
    //                 '.$row->HARGA.'</span>';
    //         })
    //         ->addColumn('aksi', function ($row) {
    //             return '<button class="btn btn-success btn-sm pick-price-exp"
    //                 data-id="'.$row->id.'"
    //                 data-kode="'.$row->KODE.'"
    //                 data-source="'.$row->source.'"
    //                 data-jenis="'.$row->JENIS.'"
    //                 data-original="'.$row->HARGA.'">
    //                 <i class="bx bx-check"></i></button>';
    //         })
    //         ->rawColumns(['harga_html', 'aksi'])
    //         ->make(true);
    // }
