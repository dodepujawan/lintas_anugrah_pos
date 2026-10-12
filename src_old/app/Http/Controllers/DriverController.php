<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public function index()
    {
        return view('driver.driver');
    }

    public function data()
    {
        $driver = Driver::select('*');

        return DataTables::of($driver)
            ->addIndexColumn()
            ->addColumn('action', function($driver) {
                return '
                <div class="btn-group">
                    <button class="btn btn-sm btn-warning edit edit-driver" data-id="'.$driver->id.'"><i class="bx bx-edit"></i></button>
                    <button class="btn btn-sm btn-danger delete delete-driver" data-id="'.$driver->id.'"><i class="bx bx-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request){
        $request->validate([
            // unique harus pakai kolom DB (UPPERCASE)
            'kode'        => 'required|unique:driver,KODE',
            'nama'        => 'required|max:100',
            'alamat'      => 'required|max:100',
            'phone'       => 'required|min:5',
            'mulai_kerja' => 'required|date',
        ]);

        try {
            // Generate kode driver
            $kode = $this->driver_kode_store();

            // Mapping request (lowercase) -> DB (UPPERCASE)
            $data = [
                'KODE'   => $kode,
                'NAMA'   => $request->nama,
                'ALAMAT' => $request->alamat,
                'PHONE'  => $request->phone,
                // nama field beda: mulai_kerja -> MULAI
                'MULAI'  => $request->mulai_kerja,
            ];

            $driver = Driver::create($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data driver berhasil disimpan',
                'data'    => $driver
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $driver = Driver::findOrFail($id);
        return response()->json($driver);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode' => 'required|unique:driver,KODE,'.$id,
            'nama' => 'required|max:100',
            'alamat' => 'required|max:200', // Biasanya alamat lebih panjang
            'phone' => 'required|min:5|numeric', // numeric bukan number
            'mulai_kerja' => 'required|date',
        ]);

        $driver = Driver::findOrFail($id);
        $data = [
                'NAMA'   => $request->nama,
                'ALAMAT' => $request->alamat,
                'PHONE'  => $request->phone,
                // nama field beda: mulai_kerja -> MULAI
                'MULAI'  => $request->mulai_kerja,
            ];
        $driver->update($data);

        return response()->json(['success' => 'Data berhasil diupdate!']);
    }

    public function destroy($id)
    {
        Driver::destroy($id);
        return response()->json(['success' => 'Data berhasil dihapus!']);
    }

    // Fungsi Calback id_user
    public function driver_kode(){
        $lastKode = Driver::orderByRaw('CAST(KODE AS UNSIGNED) DESC')
            ->value('KODE');

        $next = ((int) $lastKode) + 1;

        $newKode = (string) $next;

        return response()->json([
            'kode' => $newKode
        ]);
    }

    private function driver_kode_store(){
        $lastKode = Driver::lockForUpdate()
            ->orderByRaw('CAST(KODE AS UNSIGNED) DESC')
            ->value('KODE');

        $next = ((int) $lastKode) + 1;

        // padding hanya untuk < 10
        return (string) $next;
    }

    public function dataModal()
    {
        $driver = Driver::select('*');

        return DataTables::of($driver)
            ->addIndexColumn()
            ->addColumn('action', function($driver) {
                return '
                <div class="btn-group">
                    <button class="btn btn-sm btn-warning edit pickDriverModal" data-id="'.$driver->KODE.'"><i class="bx bx-check"></i></button>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function dataDriver($user_id)
    {
        $driver = DB::table('driver')
        ->where('user_id', $user_id)
        ->first();

        if (!$driver) {
            return response()->json(['success' => false], 404);
        }

        return response()->json([
            'success' => true,
            'kode' => $driver->KODE,
            'nama' => $driver->NAMA
        ]);
    }
}

// public function driver_kode() {
    //     $role = 'DRV'; // Default prefix untuk customer

    //     $lastUser = DB::table('driver')
    //         ->where('KODE', 'LIKE', $role . '%')
    //         ->orderBy('KODE', 'desc')
    //         ->first();

    //     if ($lastUser) {
    //         // Ambil angka dari kode terakhir, contoh: CST000001 -> 000001 -> 1
    //         $lastNumber = (int) substr($lastUser->KODE, strlen($role));
    //         $newNumber = $lastNumber + 1;
    //     } else {
    //         $newNumber = 1;
    //     }

    //     // Format: CST + 6 digit angka (contoh: CST000001, CST000002, dst)
    //     $newKode = $role . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

    //     return response()->json(['kode' => $newKode]);
    // }

    // Fungsi Calback private
    // private function driver_kode_store() {
    //     $role = 'DRV';

    //     DB::beginTransaction();

    //     try {
    //         $lastUser = DB::table('driver')
    //             ->where('KODE', 'LIKE', $role . '%')
    //             ->lockForUpdate() // 🔒 Lock table untuk prevent race condition
    //             ->orderBy('KODE', 'desc')
    //             ->first();

    //         if ($lastUser) {
    //             $lastNumber = (int) substr($lastUser->KODE, strlen($role));
    //             $newNumber = $lastNumber + 1;
    //         } else {
    //             $newNumber = 1;
    //         }

    //         $newKode = $role . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

    //         DB::commit();
    //         return $newKode;

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }
