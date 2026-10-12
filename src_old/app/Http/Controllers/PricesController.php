<?php

namespace App\Http\Controllers;
use App\Models\Prices;
use App\Models\Pricecushis;
use App\Models\Pricecus;
use App\Models\Mcustomer;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PricesController extends Controller
{
    public function index()
    {
        return view('prices.prices');
    }

    public function getData(Request $request){
        if ($request->ajax()) {

            $data = DB::table('prices')
                ->select(
                    'prices.id',
                    'prices.KETERANGAN',
                    'prices.DARI',
                    'prices.SAMPAI',
                    'prices.RUTE',
                    'prices.HARGA',
                    'prices.JENIS',
                    'prices.created_at'
                );

            return DataTables::of($data)
                ->addIndexColumn()

                ->editColumn('HARGA', function ($row) {
                    return number_format($row->HARGA, 0, ',', '.');
                })

                ->editColumn('JENIS', function ($row) {
                    return $row->JENIS == 1 ? 'Eceran' : 'Booking';
                })

                ->addColumn('action', function ($row) {
                    if (!Auth::check() || Auth::user()->role_old != 'admin') {
                        return '-';
                    }

                    return '
                        <div class="btn-group">
                            <button onclick="editData('.$row->id.')" class="btn btn-sm btn-warning">
                                <i class="bx bx-edit"></i>
                            </button>

                        </div>
                    ';
                    // <button onclick="deleteData('.$row->id.')" class="btn btn-sm btn-danger">
                    //      <i class="bx bx-trash"></i>
                    // </button>
                })

                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'keterangan_price' => 'required|string|max:50',
            'dari_price' => 'required|numeric',
            'sampai_price' => 'required|numeric',
            'rute_price' => 'required|string|max:30',
            'harga_price' => 'required|numeric',
            'jenis_val' => 'required|string|max:1',
            'rute_price' => 'required'
        ]);

        // Generate KODE
        $lastCode = Prices::orderBy('id', 'desc')->value('KODE');
        $number = $lastCode ? (int) substr($lastCode, 3) + 1 : 1;
        $kode = 'PRC' . str_pad($number, 7, '0', STR_PAD_LEFT);

        $Prices = Prices::create([
            'KODE' => $kode,
            'TANGGAL' => now()->toDateString(),
            'KETERANGAN' => $request->keterangan_price,
            'DARI' => $request->dari_price,
            'SAMPAI' => $request->sampai_price,
            'RUTE' => $request->rute_price,
            'HARGA' => $request->harga_price,
            'HV' => 0,
            'HKG' => 0,
            'HBOK' => 0,
            'JENIS' => $request->jenis_val,
            'USER' => Auth::check() ? Auth::user()->user_id : 'System',
            'USEREDIT' => Auth::check() ? Auth::user()->user_id : 'System',
            'KUNCI' => $request->keterangan_price . $request->jenis_val . $request->dari_price . $request->sampai_price,
            'HG' => 0,
        ]);

        $customers = Mcustomer::select('CUSTOMER')->get();
        $data = [];
        foreach ($customers as $customer) {
            $data[] = [
                'KODECUS' => $customer->CUSTOMER,
                'KODE' => $Prices->id,
                'TANGGAL' => $Prices->TANGGAL,
                'KETERANGAN' => $Prices->KETERANGAN,
                'DARI' => $Prices->DARI,
                'SAMPAI' => $Prices->SAMPAI,
                'RUTE' => $Prices->RUTE,
                'HARGA' => $Prices->HARGA,
                'HV' => $Prices->HV,
                'HKG' => $Prices->HKG,
                'HBOK' => $Prices->HBOK,
                'USER' => $Prices->USER,
                'USEREDIT' => $Prices->USEREDIT,
                'KUNCI' => $Prices->KUNCI,
                'HG' => $Prices->HG,
                'JENIS' => $Prices->JENIS,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Pricecus::insert($data);
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $Prices
        ]);
    }

    public function show($id){
        $data = DB::table('prices')
            // ->leftJoin('rute', 'prices.RUTE', '=', 'rute.RUTE')
            ->select(
                'prices.id',
                'prices.KETERANGAN',
                'prices.DARI',
                'prices.SAMPAI',
                'prices.RUTE as rute_id',
                // 'rute.RUTE as rute_nama',
                'prices.HARGA',
                'prices.JENIS'
            )
            ->where('prices.id', $id)
            ->first();

        return response()->json([
            "id"         => $data->id,
            "keterangan" => $data->KETERANGAN,
            "dari"       => $data->DARI,
            "sampai"     => $data->SAMPAI,
            "rute_id"    => $data->rute_id,
            // "rute_nama"  => $data->rute_nama,
            "harga"      => $data->HARGA,
            "jenis"      => $data->JENIS
        ]);
    }

    public function update(Request $request, $id){
        $request->validate([
            'keterangan_price' => 'required|string|max:50',
            'dari_price' => 'required|numeric',
            'sampai_price' => 'required|numeric',
            'rute_price' => 'required|string|max:30',
            'harga_price' => 'required|numeric',
            'jenis_val' => 'required|string|max:1',
            'rute_val_price' => 'required'
        ]);

        // AMBIL DATA LAMA
        $old = Prices::findOrFail($id);

        /**
         * 2️⃣ UPDATE DATA BARU DI TABLE PRICES
         */
        $old->update([
            'KETERANGAN' => $request->keterangan_price,
            'DARI' => $request->dari_price,
            'SAMPAI' => $request->sampai_price,
            'RUTE' => $request->rute_price,
            'HARGA' => $request->harga_price,
            'HV' => 0,
            'HKG' => 0,
            'HBOK' => 0,
            'JENIS' => $request->jenis_val,
            'USEREDIT' => Auth::check() ? Auth::user()->user_id : 'System',
            'KUNCI' => $request->keterangan_price . $request->jenis_val . $request->dari_price . $request->sampai_price,
            'HG' => 0,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $old
        ]);
    }

    public function destroy($id)
    {
        $Prices = Prices::findOrFail($id);
        $Prices->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
