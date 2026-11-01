<?php

namespace App\Http\Controllers;

use App\Models\Pajak;
use Illuminate\Http\Request;

class PajakController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $pajak = Pajak::latest('updated_at')->first();

        return response()->json($pajak);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'PPN' => 'required|numeric|min:0|max:100',
            'DISC1' => 'nullable|numeric|min:0|max:100',
            'DISC2' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $pajak = Pajak::updateOrCreate(
            ['is_active' => true],
            $validated
        );

        $response = [
            'success' => true,
            'message' => 'Pengaturan pajak berhasil diperbarui.',
            'data' => $pajak,
        ];

        if ($request->expectsJson()) {
            return response()->json($response);
        }

        return redirect()->route('pajak.get')->with('success', $response['message']);
    }

    public function get(Request $request)
    {
        $pajak = Pajak::where('is_active', true)->latest('updated_at')->first();

        if ($request->expectsJson()) {
            return response()->json($pajak);
        }

        return view('pajak.index', compact('pajak'));
    }
}
