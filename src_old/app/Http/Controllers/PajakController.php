<?php

namespace App\Http\Controllers;
use App\Models\Pajak;

use Illuminate\Http\Request;

class PajakController extends Controller
{
    public function get_pajak()
    {
        $pajak = Pajak::latest()->first();

        return response()->json([
            'status' => 'success',
            'data' => $pajak
        ]);
    }

    public function update_pajak(Request $request)
    {
        $request->validate([
            'ppn' => 'required|numeric|min:0'
        ]);

        $pajak = Pajak::create([
            'ppn' => $request->ppn
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pajak berhasil diperbarui.',
            'data' => $pajak
        ]);
    }
}
