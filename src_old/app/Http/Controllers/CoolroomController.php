<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Coolroom;
use App\Models\Signature;
use App\Models\Arh;
use App\Models\Kwitansi;
use App\Models\Mcustomer;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class CoolroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('coolroom.coolroom');
    }

    public function getData(Request $request)
    {
        $query = Coolroom::query()
        ->where(function ($q) {
            $q->whereNull('INVOICE')
            ->orWhere('INVOICE', '');
        })
        ->orderByDesc('TGLSJ');
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('TGLSJ', function ($row) {
                return $row->TGLSJ
                    ? \Carbon\Carbon::parse($row->TGLSJ)
                        ->format('d-m-Y')
                    : '-';
            })
            ->editColumn('JUMLAH', function ($row) {
                return number_format(
                    $row->JUMLAH ?? 0,
                    3,
                    ',',
                    '.'
                );
            })
            ->editColumn('HARGA', function ($row) {
                return number_format(
                    $row->HARGA ?? 0,
                    0,
                    ',',
                    '.'
                );
            })
            ->editColumn('GRAND', function ($row) {
                return number_format(
                    $row->GRAND ?? 0,
                    0,
                    ',',
                    '.'
                );
            })
            ->addColumn('action', function ($row) {
                return '
                    <button
                        class="btn btn-sm btn-primary btn-edit-coolroom"
                        data-id="'.$row->id.'">
                        Edit
                    </button>
                    <button
                        class="btn btn-sm btn-danger btn-delete-coolroom"
                        data-id="'.$row->id.'">
                        Hapus
                    </button>
                ';
            })
            ->rawColumns([
                'action'
            ])
            ->make(true);
    }

    public function getDataCustomer(){
        $customers = Mcustomer::select(['id', 'kode_cus','CUSTOMER', 'NAMACUST', 'TYPECUST', 'TELEPON', 'EMAIL', 'created_at']);

        return DataTables::of($customers)
            ->addIndexColumn()
            ->addColumn('action', function($customer) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-btn-customer-coolroom" data-id="'.$customer->CUSTOMER.'" data-name="'.$customer->NAMACUST.'" data-customer="'.$customer->CUSTOMER.'" data-bs-toggle="tooltip" title="View">
                            <i class="bx bx-check"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store transaksi coolroom.
     */
    public function store(Request $request)
    {
        try {
            $nosj=null;
            DB::transaction(function () use ($request,&$nosj) {
                $jumlah = (float) $request->jumlah;
                $harga = (int) preg_replace('/[^0-9]/', '', $request->harga);
                $discPersen = (float) $request->disc;
                $ppnPersen = (float) $request->ppn;
                $boxing = $request->boolean('boxing');
                // SUBTOTAL
                if ($boxing) {
                    $subtotal = round($harga);
                } else {
                    $subtotal = round($jumlah * $harga);
                }
                // DISC
                $ndisc = round($subtotal * $discPersen / 100);
                // DPP
                $dpp = round($subtotal - $ndisc);
                // PPN
                $nppn = round($dpp * $ppnPersen / 100);
                // GRAND
                $grand = round($dpp + $nppn);
                // GENERATE SJ
                $nosj=$this->generateSjCoolroom();
                // STORE
                Coolroom::create([
                    'NOSJ'=>$nosj,
                    'TGLSJ'=>$request->tglsj,
                    'CUSTOMER_KODE'=>$request->customer_kode,
                    'CUSTOMER'=>$request->customer,
                    'JUMLAH'=>$jumlah,
                    'UNIT'=>'KG',
                    'HARGA'=>$harga,
                    'BOXING'=>$boxing,
                    'SUBTOTAL'=>$subtotal,
                    'DISC'=>$discPersen,
                    'NDISC'=>$ndisc,
                    'DPP'=>$dpp,
                    'PPN'=>$ppnPersen,
                    'NPPN'=>$nppn,
                    'TOTAL'=>$dpp,
                    'GRAND'=>$grand,
                    'KETERANGAN'=>$request->keterangan,
                    'USERINPUT'=>auth()->user()->user_id,
                    // AREA
                    'area_id' => auth()->user()->area_id,
                    'area_name' => auth()->user()->area_name,
                    // 'CABANG'=>auth()->user()->cabang ?? null
                ]);
            });
            return response()->json([
                'status'=>true,
                'message'=>'Transaksi coolroom berhasil disimpan',
                'nosj'=>$nosj
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function edit($id)
    {
        $coolroom=Coolroom::findOrFail($id);
        return response()->json([
            'id'=>$coolroom->id,
            'NOSJ'=>$coolroom->NOSJ,
            'TGLSJ'=>$coolroom->TGLSJ ? Carbon::parse($coolroom->TGLSJ)->format('Y-m-d'): null,
            'CUSTOMER'=>$coolroom->CUSTOMER,
            'CUSTOMER_KODE'=>$coolroom->CUSTOMER_KODE,
            'JUMLAH'=>$coolroom->JUMLAH,
            'HARGA'=>$coolroom->HARGA,
            'BOXING'=>$coolroom->BOXING,
            'DISC'=>$coolroom->DISC,
            'PPN'=>$coolroom->PPN,
            'KETERANGAN'=>$coolroom->KETERANGAN,
            'SUBTOTAL'=>$coolroom->SUBTOTAL,
            'NDISC'=>$coolroom->NDISC,
            'DPP'=>$coolroom->DPP,
            'NPPN'=>$coolroom->NPPN,
            'GRAND'=>$coolroom->GRAND
        ]);
    }

    public function update(Request $request,$id)
    {
        try {
            $nosj=null;
            DB::transaction(function () use ($request,$id,&$nosj) {
                $coolroom=Coolroom::findOrFail($id);
                // ======================
                // VALIDASI SUDAH INVOICE / GB
                // ======================
                if (!empty($coolroom->INVOICE)) {
                    throw new \Exception(
                        'Data sudah memiliki invoice dan tidak dapat diedit.'
                    );
                }

                $jumlah = (float) $request->jumlah;
                $harga = (int) preg_replace('/[^0-9]/', '', $request->harga);
                $discPersen = (float) $request->disc;
                $ppnPersen = (float) $request->ppn;
                $boxing = $request->boolean('boxing');
                // =====================
                // SUBTOTAL
                // =====================
                if ($boxing) {
                    $subtotal = round($harga);
                } else {
                    $subtotal = round($jumlah * $harga);
                }
                // =====================
                // DISC
                // =====================
                $ndisc = round($subtotal * $discPersen / 100);
                // =====================
                // DPP
                // =====================
                $dpp = round($subtotal - $ndisc);
                // =====================
                // PPN
                // =====================
                $nppn = round($dpp * $ppnPersen / 100);
                // =====================
                // GRAND
                // =====================
                $grand = round($dpp + $nppn);
                // =====================
                // UPDATE
                // =====================
                $coolroom->update([
                    'TGLSJ'=>$request->tglsj,
                    'CUSTOMER_KODE'=>$request->customer_kode,
                    'CUSTOMER'=>$request->customer,
                    'JUMLAH'=>$jumlah,
                    'HARGA'=>$harga,
                    'BOXING'=>$boxing,
                    'SUBTOTAL'=>$subtotal,
                    'DISC'=>$discPersen,
                    'NDISC'=>$ndisc,
                    'DPP'=>$dpp,
                    'PPN'=>$ppnPersen,
                    'NPPN'=>$nppn,
                    'TOTAL'=>$dpp,
                    'GRAND'=>$grand,
                    'KETERANGAN'=>$request->keterangan,
                    'USEREDIT'=>auth()->user()->user_id
                ]);
                 $nosj=$coolroom->NOSJ;
            });
            return response()->json([
                'status'=>true,
                'message'=>'Transaksi coolroom berhasil diupdate',
                'nosj'=>$nosj
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()
            ],500);

        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $coolroom=Coolroom::findOrFail($id);
                // =====================
                // VALIDASI
                // =====================
                if(!empty($coolroom->INVOICE)){
                    throw new \Exception(
                        'Data sudah invoice dan tidak bisa dihapus'
                    );
                }
                // =====================
                // DELETE
                // =====================
                $coolroom->delete();

            });
            return response()->json([
                'status'=>true,
                'message'=>'Data coolroom berhasil dihapus'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function pdf($nosj)
    {
        $coolroom=Coolroom::where('NOSJ',$nosj)->firstOrFail();
        $signature=Signature::latest()->first();
        $user=DB::table('users')
            ->where('user_id',$coolroom->USERINPUT)
            ->first();

        $tempPath = storage_path('app/mpdf-temp');

        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        $pdf = new \Mpdf\Mpdf([
            'mode'    => 'utf-8',
            'format'  => 'A4',
            'tempDir' => $tempPath,
        ]);
        $html=view(
            'coolroom.coolroom-pdf',
            compact(
                'coolroom',
                'signature',
                'user'
            )
        )->render();
        $pdf->WriteHTML($html);
        return response(
            $pdf->Output('SJ-'.$coolroom->NOSJ.'.pdf','I'),
            200,
            [
                'Content-Type'=>'application/pdf'
            ]
        );
    }

    private function generateSjCoolroom(){
        $bulan=now()->format('m');
        $tahun=now()->format('y');
        $prefix='SC'.$bulan.$tahun;
        $last=Coolroom::where('NOSJ','like',$prefix.'%')
            ->lockForUpdate()
            ->orderBy('NOSJ','desc')
            ->first();
        if($last){
            $lastNumber=(int)substr($last->NOSJ,-5);
            $nextNumber=$lastNumber+1;
        }else{
            $nextNumber=1;
        }
        $number=str_pad($nextNumber,5,'0',STR_PAD_LEFT);
        return $prefix.$number;
    }
}
