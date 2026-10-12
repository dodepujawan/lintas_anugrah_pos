<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Signature;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function get()
    {
        $signature = Signature::where('is_active', true)->latest('updated_at')->first();

        return response()->json($signature);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'NAMA' => 'required|string|max:100',
            'JABATAN' => 'nullable|string|max:100',
            'TTD' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $signature = Signature::updateOrCreate(
            ['is_active' => true],
            $validated
        );

        return response()->json([
            'success' => true,
            'data' => $signature,
        ]);
    }

    public function getData()
    {
        $signature = Signature::where('is_active', true)->latest('updated_at')->first();

        return response()->json($signature);
    }
}
