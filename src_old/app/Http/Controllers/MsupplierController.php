<?php

namespace App\Http\Controllers;

use App\Models\Msupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MsupplierController extends Controller
{
    public function index_supplier(){
        return view('supplier.supplier');
    }

    public function data(){
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
                        <button class="btn btn-sm btn-warning btn-edit-supplier"
                            data-id="'.$row->id.'"
                            title="Edit">
                            <i class="bx bx-edit"></i>
                        </button>

                        <button class="btn btn-sm btn-danger btn-delete-supplier"
                            data-id="'.$row->id.'"
                            title="Delete">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['kategori_label','action'])
            ->make(true);
    }

    public function store(Request $request){
        try {

            DB::beginTransaction();

            $id = $request->supplier_id;

            // =====================================
            // MODE UPDATE
            // =====================================
            if ($id) {

                Msupplier::where('id', $id)->update([
                    'KATEGORI' => $request->kategori_supplier,
                    'NAMA' => $request->nama_supplier,
                    'ALAMAT1' => $request->alamat1_supplier,
                    'ALAMAT2' => $request->alamat2_supplier,
                    'KOTA' => $request->kota_supplier,
                    'TELEPON' => $request->telepon_supplier,
                    'FAX' => $request->fax_supplier,
                    'EMAIL' => $request->email_supplier,
                    'KONTAK' => $request->kontak_supplier,
                    'NOREK' => $request->norek_supplier,
                    'BANK' => $request->bank_supplier,
                    'ATASNAMA' => $request->atasnama_supplier,
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'mode' => 'update',
                    'message' => 'Supplier berhasil diupdate'
                ]);
            }

            // =====================================
            // MODE STORE
            // =====================================
            $kategori = $request->kategori_supplier;

            $last = Msupplier::where('KATEGORI', $kategori)
                ->orderBy('SUPPLIER', 'desc')
                ->lockForUpdate()
                ->first();

            if ($last) {
                $lastNumber = (int) substr($last->SUPPLIER, 3);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $kode = $kategori . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

            Msupplier::create([
                'SUPPLIER' => $kode,
                'KATEGORI' => $kategori,
                'NAMA' => $request->nama_supplier,
                'ALAMAT1' => $request->alamat1_supplier,
                'ALAMAT2' => $request->alamat2_supplier,
                'KOTA' => $request->kota_supplier,
                'TELEPON' => $request->telepon_supplier,
                'FAX' => $request->fax_supplier,
                'EMAIL' => $request->email_supplier,
                'KONTAK' => $request->kontak_supplier,
                'NOREK' => $request->norek_supplier,
                'BANK' => $request->bank_supplier,
                'ATASNAMA' => $request->atasnama_supplier,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'mode' => 'store',
                'message' => 'Supplier berhasil disimpan',
                'kode' => $kode
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show($id){
        $supplier = Msupplier::find($id);

        if(!$supplier){
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $supplier
        ]);
    }

    public function destroy($id){
        try {

            $supplier = Msupplier::find($id);

            if(!$supplier){
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $supplier->delete();

            return response()->json([
                'status' => true,
                'message' => 'Supplier berhasil dihapus'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);

        }
    }
}
