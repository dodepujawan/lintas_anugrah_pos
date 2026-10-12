<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function get(): JsonResponse
    {
        $areas = Area::where('is_active', true)
            ->orderBy('KODE')
            ->get(['KODE', 'NAMA', 'CABANG']);

        return response()->json($areas);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'KODE' => 'required|string|max:6|unique:areas,KODE',
            'NAMA' => 'required|string|max:100',
            'CABANG' => 'nullable|string|max:2',
            'KETERANGAN' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $area = Area::create($validated);

        return response()->json([
            'success' => true,
            'data' => $area,
        ]);
    }

    public function update(Request $request, Area $area): JsonResponse
    {
        $validated = $request->validate([
            'KODE' => ['required', 'string', 'max:6', 'unique:areas,KODE,' . $area->id],
            'NAMA' => 'required|string|max:100',
            'CABANG' => 'nullable|string|max:2',
            'KETERANGAN' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $area->update($validated);

        return response()->json([
            'success' => true,
            'data' => $area,
        ]);
    }

    public function destroy(Area $area): JsonResponse
    {
        $area->delete();

        return response()->json([
            'success' => true,
            'message' => 'Area berhasil dihapus.',
        ]);
    }
}
