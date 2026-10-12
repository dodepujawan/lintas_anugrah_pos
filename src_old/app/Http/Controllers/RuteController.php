<?php

namespace App\Http\Controllers;

use App\Models\Rute;
use Illuminate\Http\Request;
use DataTables;

class RuteController extends Controller
{
    public function index()
    {
        return view('rute.index');
    }

    public function getData(){
        $data = Rute::select('id', 'RUTE', 'created_at');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '
                     <div class="d-flex gap-1">
                        <button onclick="pickDataRute('.$row->id.', \''.$row->RUTE.'\')" class="btn btn-primary btn-sm me-1">Pilih</button>
                        <button onclick="editDataRute('.$row->id.')" class="btn btn-warning btn-sm py-0 px-2">Edit</button>
                    </div>
                ';
                return $btn;
            })
            ->editColumn('created_at', function($row){
                return $row->created_at->format('d/m/Y H:i');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'newRute' => 'required|string|max:255|unique:rute,RUTE'
        ]);

        $rute = Rute::create([
            'RUTE' => $request->newRute
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rute berhasil ditambahkan',
            'data' => $rute,
            'id' => $rute->id
        ]);
    }

    public function show($id)
    {
        $rute = Rute::find($id);

        if (!$rute) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $rute
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'newRute' => 'required|string|max:255|unique:rute,RUTE,' . $id
        ]);

        $rute = Rute::find($id);

        if (!$rute) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $rute->update([
            'RUTE' => $request->newRute
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rute berhasil diupdate',
            'data' => $rute,
            'id' => $rute->id
        ]);
    }

    public function destroy($id)
    {
        $rute = Rute::find($id);

        if (!$rute) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $rute->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rute berhasil dihapus'
        ]);
    }
}

// delete rute
// <button onclick="deleteDataRute('.$row->id.')" class="btn btn-danger btn-sm py-0 px-2">Hapus</button>
