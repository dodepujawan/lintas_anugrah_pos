<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Signature;

class SignatureController extends Controller
{
    public function get_signature()
    {
        $signature = Signature::latest()->first();

        return response()->json([
            'status' => 'success',
            'data' => $signature
        ]);
    }

    public function update_signature(Request $request)
    {
        $request->validate([
            'signature' => 'required'
        ]);

        $signature = Signature::create([
            'nama' => $request->signature
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Signature berhasil diperbarui.',
            'data' => $signature
        ]);
    }
}
