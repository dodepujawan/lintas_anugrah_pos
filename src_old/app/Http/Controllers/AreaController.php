<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function getArea()
    {
        $data = Area::orderBy('area')->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'area'   => 'nullable|string',
            'alamat' => 'nullable|string',
        ]);

        Area::create([
            'area'   => $request->area,
            'alamat' => $request->alamat,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Area berhasil disimpan'
        ]);
    }

    public function show($id)
    {
        $data = Area::findOrFail($id);

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $data = Area::findOrFail($id);

        $data->update([
            'area'   => $request->area,
            'alamat' => $request->alamat,
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function destroy($id)
    {
        Area::findOrFail($id)->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
