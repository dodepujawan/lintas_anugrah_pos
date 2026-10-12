<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expedisi;
use App\Models\Coolroom;
use App\Models\Arh;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function expedisi(Request $request)
    {
        if ($request->bearerToken() !== env('LEDGER_API_TOKEN')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'data' => Expedisi::all()
        ]);
    }

    public function coolrooms(Request $request)
    {
        if ($request->bearerToken() !== env('LEDGER_API_TOKEN')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'data' => Coolroom::all()
        ]);
    }

    public function arh(Request $request)
    {
        if ($request->bearerToken() !== env('LEDGER_API_TOKEN')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'data' => Arh::all()
        ]);
    }
}
