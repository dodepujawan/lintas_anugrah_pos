<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coolroom;
use App\Models\Expedisi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.dashboard');
    }

    public function summary(Request $request)
    {
        $dari = $request->tanggal_dari;
        $sampai = $request->tanggal_sampai;

        $eks = Expedisi::where('JENIS', 'EKS')
            ->whereBetween('TGLSJ', [$dari, $sampai])
            ->selectRaw('COUNT(*) total, COALESCE(SUM(GRAND),0) grand')
            ->first();

        $ren = Expedisi::where('JENIS', 'REN')
            ->whereBetween('TGLMUAT', [$dari, $sampai])
            ->selectRaw('COUNT(*) total, COALESCE(SUM(GRAND),0) grand')
            ->first();

        $coolroom = Coolroom::whereBetween('TGLSJ', [$dari, $sampai])
            ->selectRaw('COUNT(*) total, COALESCE(SUM(GRAND),0) grand')
            ->first();

        return response()->json([
            'eks' => $eks,
            'ren' => $ren,
            'coolroom' => $coolroom,
            'total_grand' =>
                $eks->grand +
                $ren->grand +
                $coolroom->grand
        ]);
    }

    public function chart()
    {
        $start = Carbon::now()->subDays(29)->format('Y-m-d');

        $eks = Expedisi::selectRaw("
                DATE(TGLSJ) tanggal,
                SUM(CAST(GRAND AS DECIMAL(15,2))) grand
            ")
            ->where('JENIS', 'EKS')
            ->whereDate('TGLSJ', '>=', $start)
            ->groupBy(DB::raw('DATE(TGLSJ)'))
            ->pluck('grand', 'tanggal')
            ->toArray();

        $ren = Expedisi::selectRaw("
                DATE(TGLMUAT) tanggal,
                SUM(CAST(GRAND AS DECIMAL(15,2))) grand
            ")
            ->where('JENIS', 'REN')
            ->whereDate('TGLMUAT', '>=', $start)
            ->groupBy(DB::raw('DATE(TGLMUAT)'))
            ->pluck('grand', 'tanggal')
            ->toArray();

        $coolroom = Coolroom::selectRaw("
                DATE(TGLSJ) tanggal,
                SUM(CAST(GRAND AS DECIMAL(15,2))) grand
            ")
            ->whereDate('TGLSJ', '>=', $start)
            ->groupBy(DB::raw('DATE(TGLSJ)'))
            ->pluck('grand', 'tanggal')
            ->toArray();

        $labels = [];
        $totals = [];

        for ($i = 29; $i >= 0; $i--) {

            $tanggal = Carbon::now()
                ->subDays($i)
                ->format('Y-m-d');

            $labels[] = Carbon::parse($tanggal)
                ->format('d/m');

            $totals[] =
                ($eks[$tanggal] ?? 0) +
                ($ren[$tanggal] ?? 0) +
                ($coolroom[$tanggal] ?? 0);
        }

        return response()->json([
            'labels' => $labels,
            'data' => $totals
        ]);
    }
}
