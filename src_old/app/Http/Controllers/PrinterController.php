<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Printer;
use Illuminate\Support\Facades\DB;

class PrinterController extends Controller
{
    public function list()
    {
        return response()->json([
            'status' => true
        ]);
    }

    public function current()
    {
        $printer = DB::table('printers')
           ->where('user_id', auth()->user()->user_id)
            ->first();

        return response()->json([
            'printer' => $printer?->printer_name
        ]);
    }

    public function save(Request $request)
    {
        DB::table('printers')->updateOrInsert(
            ['user_id' => auth()->user()->user_id],
            ['printer_name' => $request->printer]
        );

        return response()->json([
            'status' => true,
            'message' => 'Printer berhasil disimpan'
        ]);
    }
}
