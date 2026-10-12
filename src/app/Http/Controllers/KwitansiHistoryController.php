<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kwitansi;
use App\Models\Arh;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KwitansiHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $kwitantis = Kwitansi::query()
            ->when($search, function ($query) use ($search) {
                $query->where('NO_KWT', 'like', "%{$search}%")
                    ->orWhere('NOFAKTUR', 'like', "%{$search}%")
                    ->orWhere('CUSTOMER', 'like', "%{$search}%");
            })
            ->when($request->input('jenis'), function ($query) use ($request) {
                $query->where('JENIS_KWT', $request->input('jenis'));
            })
            ->when($request->input('date_from'), function ($query) use ($request) {
                $query->whereDate('TGL_KWT', '>=', $request->input('date_from'));
            })
            ->when($request->input('date_to'), function ($query) use ($request) {
                $query->whereDate('TGL_KWT', '<=', $request->input('date_to'));
            })
            ->orderByDesc('TGL_KWT')
            ->paginate(20)
            ->withQueryString();

        $totalBayar = (float) Kwitansi::when($request->input('jenis'), function ($query) use ($request) {
            $query->where('JENIS_KWT', $request->input('jenis'));
        })->sum('NILAI_BAYAR');

        return view('kwitansi-history.index', compact('kwitantis', 'search', 'totalBayar'));
    }

    public function exportKwitansi(Request $request)
    {
        $kwitantis = Kwitansi::query()
            ->when($request->input('jenis'), function ($query) use ($request) {
                $query->where('JENIS_KWT', $request->input('jenis'));
            })
            ->when($request->input('date_from'), function ($query) use ($request) {
                $query->whereDate('TGL_KWT', '>=', $request->input('date_from'));
            })
            ->when($request->input('date_to'), function ($query) use ($request) {
                $query->whereDate('TGL_KWT', '<=', $request->input('date_to'));
            })
            ->orderBy('TGL_KWT')
            ->get();

        return Excel::download(new \App\Exports\KwitansiExport($kwitantis), 'kwitansi-history.xlsx');
    }

    public function getDataKwitansi(Request $request)
    {
        $invoices = Arh::query()
            ->where('STATUS', 'OPEN')
            ->when($request->input('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('NOFAKTUR', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%");
                });
            })
            ->limit(20)
            ->get(['NOFAKTUR', 'CUSTOMER', 'SALDO', 'BAYAR']);

        return response()->json($invoices);
    }
}
