<?php

namespace App\Http\Controllers;
use App\Models\Rekening;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class RekeningController extends Controller
{
    public function index()
    {
        return view('rekening.rekening');
    }

    public function data()
    {
        return response()->json(
            Rekening::orderByDesc('id')->get()
        );
    }

    public function store(Request $request)
    {
        Rekening::create([
            'BANK'  => $request->bank,
            'NOREK' => $request->norek,
            'NAMA'  => $request->nama,
            'USER'  => auth()->user()->user_id ?? 'SYSTEM',
            'AKTIF' => 0
        ]);

        return response()->json(['status' => true]);
    }

    public function pilih($id)
    {
        DB::transaction(function () use ($id) {

            Rekening::query()->update(['AKTIF' => 0]);

            Rekening::where('id', $id)->update([
                'AKTIF' => 1
            ]);
        });

        return response()->json(['status' => true]);
    }
}
