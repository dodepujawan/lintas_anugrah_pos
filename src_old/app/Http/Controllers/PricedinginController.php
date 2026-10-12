<?php

namespace App\Http\Controllers;
use App\Models\Pricedingin;
use App\Models\Kendaraan;
use App\Models\Pricedingincus;
use App\Models\Mcustomer;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PricedinginController extends Controller
{
    public function index()
    {
        return view('priceDingin.priceDingin');
    }

    public function getData(Request $request){
        if ($request->ajax()) {
            $data = DB::table('pricedingin')
                ->leftJoin('kendaraan', 'pricedingin.KODE', '=', 'kendaraan.KODE')
                ->select(
                    'pricedingin.id',
                    'pricedingin.KODEDGN',
                    'pricedingin.KODE',
                    'pricedingin.PERIODE',
                    'pricedingin.PLAT',
                    'pricedingin.JENIS',
                    'pricedingin.ITEM',
                    'pricedingin.HARGA',
                    'pricedingin.KUNCI',
                    // Format tanggal tanpa jam: dd-mm-yyyy
                    DB::raw("DATE_FORMAT(pricedingin.created_at, '%d-%m-%Y') as created_at"),
                    // Ambil nama dari tabel kendaraan
                    'kendaraan.NAMA as nama_kendaraan'
                );
            $data->orderBy('pricedingin.created_at', 'desc');
            return DataTables::of($data)
                ->addIndexColumn()

                ->editColumn('HARGA', function ($row) {
                    return number_format($row->HARGA, 0, ',', '.');
                })

                ->addColumn('action', function ($row) {
                    if (!Auth::check() || Auth::user()->role_old != 'admin') {
                        return '-';
                    }

                    return '
                        <div class="btn-group">
                            <button onclick="editDataDingin('.$row->id.')" class="btn btn-sm btn-warning">
                                <i class="bx bx-edit"></i>
                            </button>
                        </div>
                    ';
                    // <button onclick="deleteDataDingin('.$row->id.')" class="btn btn-sm btn-danger">
                    //     <i class="bx bx-trash"></i>
                    // </button>
                })

                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request){
        $validated = $request->validate([
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
        $lastCode = Pricedingin::orderBy('id', 'desc')->value('KODEDGN');
        $number = $lastCode ? (int) substr($lastCode, 3) + 1 : 1;
        $kodedgn = 'PRD' . str_pad($number, 7, '0', STR_PAD_LEFT);
        $validated['KODEDGN'] = $kodedgn;
        $validated['TANGGAL'] = now()->toDateString();

        // Simpan
        $pricedingin = Pricedingin::create($validated);

        // Simpan Ke Pricdingincus
        $customers = Mcustomer::select('CUSTOMER')->get();

        $data = [];

        foreach ($customers as $customer) {

            $data[] = [
                'KODECUS' => $customer->CUSTOMER,

                // relasi ke master
                'KODEDGN' => $pricedingin->id,
                'TANGGAL' => $pricedingin->TANGGAL,

                // copy data master
                'KODE' => $pricedingin->KODE,
                'PERIODE' => $pricedingin->PERIODE,
                'PLAT' => $pricedingin->PLAT,
                'ITEM' => $pricedingin->ITEM,
                'HARGA' => $pricedingin->HARGA,
                'USER' => $pricedingin->USER,
                'JENIS' => $pricedingin->JENIS,

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Pricedingincus::insert($data);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $pricedingin
        ]);
    }

    public function show($id){
        $data = Pricedingin::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Ambil relasi dari kendaraan berdasarkan KODE
        $kendaraan = Kendaraan::where('KODE', $data->KODE)->first();

        // Ambil nama kendaraan atau kosong jika tidak ada
        $jenis = $kendaraan->NAMA ?? null;

        return response()->json([
            'success' => true,
            'data' => $data,
            'jenis_pricedingin' => $jenis
        ]);
    }

    public function update(Request $request, $id){
        $pricedingin = Pricedingin::findOrFail($id);

        $validated = $request->validate([
            'KODE' => 'required|string|max:50',
            'PERIODE' => 'required|string|max:50',
            'PLAT' => 'required|string|max:20',
            'ITEM' => 'required|string|max:255',
            'HARGA' => 'required|integer|min:0',
        ]);

        $validated['USEREDIT'] = Auth::user()->user_id; // Atau ganti dengan user yang login
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

        $pricedingin->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $pricedingin
        ]);
    }

    public function destroy($id)
    {
        $Prices = Pricedingin::findOrFail($id);
        $Prices->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }
}


// Extra note untuk store be careful ada perbedaan jenis dan nama kendaraan karna mengikuti tampilan form desktop juga databasenya yang desktop
