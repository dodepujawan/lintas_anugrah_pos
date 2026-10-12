<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

        return response()->json([
            'success' => true,
            'data' => $pajak,
        ]);
    }

    public function get()
    {
        $pajak = Pajak::where('is_active', true)->latest('updated_at')->first();

        return response()->json($pajak);
    }
}
