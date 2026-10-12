<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Msupplier;
use App\Models\Kendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function index(){
        return view('service.service');
    }

    public function data(){
        $data = DB::table('service')
            ->orderBy('id','desc'); // terbaru dulu

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('action', function($row){

                $edit = '<button class="btn btn-sm btn-warning edit-service" data-id="'.$row->id.'">Edit</button>';

                $delete = '<button class="btn btn-sm btn-danger delete-service" data-id="'.$row->id.'">Delete</button>';

                return $edit.' '.$delete;

            })

            ->rawColumns(['action'])

            ->make(true);
    }

    public function ajaxPerkiraan(Request $request){
        $search = $request->search;
        $data = DB::table('msklas')
            ->select('FNO_PRK', 'FNM_PRK')
            ->where('FTINGKAT', 3)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('FNO_PRK', 'like', "%{$search}%")
                    ->orWhere('FNM_PRK', 'like', "%{$search}%");
                });
            })
            ->orderBy('FNO_PRK')
            ->limit(20)
            ->get();
        return response()->json($data);
    }

    public function store(Request $request){
        try {
            DB::beginTransaction();

            $id = $request->id_service;
            $no_bukti = null;

            $data = [
                'NO_REF'        => $request->no_faktur_service,
                'TGL_SERVIS'    => $request->tgl_document_service,
                'KODE_MOBIL'    => $request->kendaraan_service,
                'KODE_SUPPLIER' => $request->supplier,
                // FNO_PRK_B ini merujuk ke kendaraan
                'FNO_PRK_B'     => $request->fno_prk_b_service,
                'KETERANGAN'    => $request->keterangan_service,
                'NILAI_SERVIS'  => $request->nilai_servis,
                'USER_INPUT'    => auth()->user()->user_id ?? 'admin',
                'TGL_TEMPO'     => $request->tgl_jatuh_tempo_service,
                'NO_JURNAL'     => $request->no_jurnal_service,
                // FRO_PRK ini merujuk ke msklas
                'FNO_PRK'     => $request->akun_hutang,
                'FNM_PRK'     => $request->akun_hutang_nama,
            ];

            if ($id) {

                // =====================
                // UPDATE
                // =====================
                $service = DB::table('service')->where('id', $id)->first();
                // =====================
                // CEK APH
                // =====================
                $aph = DB::table('aph')
                    ->where('NOFAKTUR', $service->NO_SERVICE)
                    ->first();
                if ($aph && (float)$aph->BAYAR > 0) {
                    throw new \Exception(
                        'Hutang sudah memiliki pembayaran. Silakan hubungi admin.'
                    );
                }

                DB::table('service')
                    ->where('id', $id)
                    ->update(array_merge($data, [
                        'USER_EDIT' => auth()->user()->user_id ?? 'admin',
                        'updated_at' => now()
                    ]));

                $noFaktur = $service->NO_SERVICE ?? $request->no_faktur_service;

                $message = 'Data service berhasil diupdate';

            } else {

                // =====================
                // INSERT
                // =====================
                $no_bukti = $this->generateNoService($request->supplier);

                DB::table('service')->insert(array_merge($data, [
                    'NO_SERVICE' => $no_bukti,
                    'TGL_TRANSAKSI' => now(),
                    'created_at' => now()
                ]));

                $noFaktur = $no_bukti;

                $message = 'Data service berhasil disimpan';
            }

            // =====================
            // SYNC KE APH (HUTANG)
            // =====================
            $this->syncAph($request, $noFaktur);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $message,
                'kode' => $noFaktur
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show($id){
        $data = Service::leftJoin('kendaraan', 'kendaraan.KODE', '=', 'service.KODE_MOBIL')
            ->leftJoin('msupplier', 'msupplier.SUPPLIER', '=', 'service.KODE_SUPPLIER')
            ->where('service.id', $id)
            ->select(
                'service.*',

                'kendaraan.NAMA as kendaraan_nama',
                'kendaraan.PLAT as kendaraan_plat',

                'msupplier.NAMA as supplier_nama'
            )
            ->firstOrFail();

        return response()->json([

            'id_service' => $data->id,

            'supplier' => $data->KODE_SUPPLIER,
            'supplier_nama' => $data->supplier_nama,

            'kendaraan_service' => $data->KODE_MOBIL,
            'kendaraan_service_nama' => $data->kendaraan_nama,
            'kendaraan_service_plat' => $data->kendaraan_plat,

            'fno_prk_id' => $data->FNO_PRK,
            'fno_prk_nama' => $data->FNM_PRK,

            'fno_prk_b_service' => $data->FNO_PRK_B,

            'nilai_servis' => $data->NILAI_SERVIS,
            'keterangan' => $data->KETERANGAN,

            'no_faktur' => $data->NO_SERVICE,
            'tgl_document' => $data->TGL_SERVIS,

            'tgl_jatuh_tempo' => $data->TGL_TEMPO,
            'no_jurnal' => $data->NO_JURNAL
        ]);
    }

    public function dataSupplierModal(){
        $query = Msupplier::select([
            'id',
            'SUPPLIER',
            'KATEGORI',
            'NAMA',
            'KOTA',
            'TELEPON',
            'EMAIL'
        ]);

        return DataTables::of($query)

            ->addColumn('kategori_label', function ($row) {

                if ($row->KATEGORI == 'SP') {
                    return '<span class="badge bg-primary">Supplier</span>';
                }

                if ($row->KATEGORI == 'LS') {
                    return '<span class="badge bg-warning">Leasing</span>';
                }

                return '-';
            })

            ->addColumn('action', function ($row) {

                return '
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-sm btn-warning btn-pick-supplier-service"
                            data-id="'.$row->id.'" data-supplier="'.$row->SUPPLIER.'" data-nama="'.$row->NAMA.'"
                            title="Edit">
                            <i class="bx bx-check"></i>
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['kategori_label','action'])
            ->make(true);
    }

    public function dataKendaraanModel()
    {
        $kendaraan = Kendaraan::select('*')
        ->orderBy('id', 'desc');;

        return DataTables::of($kendaraan)
            ->addIndexColumn()
            ->addColumn('action', function($kendaraan) {
                return '
                <div class="btn-group">
                    <button class="btn btn-sm btn-primary edit pickKendaraanServiceModel" data-id="'.$kendaraan->id.'" data-kode="'.$kendaraan->KODE.'" data-nama="'.$kendaraan->NAMA.'" data-fno="'.$kendaraan->FNO_PRK_B.'" data-plat="'.$kendaraan->PLAT.'" id="pickKendaraanService"><i class="bx bx-check"></i></button>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function destroy($id){
        try {
            DB::beginTransaction();
            // ambil data dulu
            $service = DB::table('service')->where('id', $id)->first();
            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
            // hapus APH dulu (hutang)
            DB::table('aph')
                ->where('NOFAKTUR', $service->NO_SERVICE)
                ->delete();
            // hapus service
            DB::table('service')
                ->where('id', $id)
                ->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data service & hutang berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function generateNoService($supplier){
        $prefix = $supplier;

        $last = DB::table('service')
            ->where('KODE_SUPPLIER', $supplier)
            ->where('NO_SERVICE', 'like', $prefix . '-%')
            ->lockForUpdate() // 🔥 INI KUNCINYA
            ->orderBy('NO_SERVICE', 'desc')
            ->first();

        if ($last) {
            preg_match('/(\d+)$/', $last->NO_SERVICE, $matches);
            $nextNumber = (int)$matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        $runningNumber = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return $prefix . '-' . $runningNumber;
    }

    private function syncAph($request, $noFaktur){
        $aph = DB::table('aph')->where('NOFAKTUR', $noFaktur)->first();
        if ($aph && (float)$aph->BAYAR > 0) {
            throw new \Exception('Hutang sudah memiliki pembayaran. Tidak dapat diubah.');
        }

        // hitung saldo (basic dulu)
        $hutang   = (float) ($request->nilai_servis ?? 0);
        $retur    = $aph->RETUR ?? 0;
        $discount = $aph->DISCOUNT ?? 0;
        $bayar    = $aph->BAYAR ?? 0;

        $saldo = $hutang - $bayar - $retur - $discount;

        DB::table('aph')->updateOrInsert(
            ['NOFAKTUR' => $noFaktur],
            [
                'TGLFAKTUR' => $request->tgl_document_service,
                'TGLJT'     => $request->tgl_jatuh_tempo_service,
                'SUPPLIER'  => $request->supplier,

                'HUTANG'   => $hutang,
                'UM'       => 0,
                'RETUR'    => $retur,
                'DISCOUNT' => $discount,
                'BAYAR'    => $bayar,
                'SALDO'    => $saldo,

                'KETERANGAN' => $request->keterangan_service,
                'AUTO'       => 1,

                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
