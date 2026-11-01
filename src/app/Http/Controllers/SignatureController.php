<?php

namespace App\Http\Controllers;

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

        if (request()->expectsJson()) {
            return response()->json($signature);
        }

        return view('signature.index', compact('signature'));
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

        $response = [
            'success' => true,
            'message' => 'Data penanggung jawab berhasil diperbarui.',
            'data' => $signature,
        ];

        if ($request->expectsJson()) {
            return response()->json($response);
        }

        return redirect()->route('signature.get')->with('success', $response['message']);
    }

    public function getData()
    {
        $signature = Signature::where('is_active', true)->latest('updated_at')->first();

        return response()->json($signature);
    }
}
