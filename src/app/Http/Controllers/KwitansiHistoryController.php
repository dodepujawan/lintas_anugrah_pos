<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Arh;
use App\Models\Kwitansi;
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
        $jenis = $this->normalizeJenis($request->input('jenis'));

        $kwitantis = Kwitansi::query()
            ->when($search, function ($query) use ($search) {
                $query->where('NOKWT', 'like', "%{$search}%")
                    ->orWhere('FDOK_TRANS', 'like', "%{$search}%")
                    ->orWhere('CUSTOMER', 'like', "%{$search}%");
            })
            ->when($jenis, function ($query) use ($jenis) {
                $query->where('JENIS', $jenis);
            })
            ->when($request->input('date_from'), function ($query) use ($request) {
                $query->whereDate('TGL', '>=', $request->input('date_from'));
            })
            ->when($request->input('date_to'), function ($query) use ($request) {
                $query->whereDate('TGL', '<=', $request->input('date_to'));
            })
            ->orderByDesc('TGL')
            ->paginate(20)
            ->withQueryString();

        $totalBayar = (float) Kwitansi::when($jenis, function ($query) use ($jenis) {
            $query->where('JENIS', $jenis);
        })->sum('FNIL_DOK');

        return view('kwitansi-history.index', compact('kwitantis', 'search', 'totalBayar'));
    }

    public function exportKwitansi(Request $request)
    {
        $jenis = $this->normalizeJenis($request->input('jenis'));

        $kwitantis = Kwitansi::query()
            ->when($jenis, function ($query) use ($jenis) {
                $query->where('JENIS', $jenis);
            })
            ->when($request->input('date_from'), function ($query) use ($request) {
                $query->whereDate('TGL', '>=', $request->input('date_from'));
            })
            ->when($request->input('date_to'), function ($query) use ($request) {
                $query->whereDate('TGL', '<=', $request->input('date_to'));
            })
            ->orderBy('TGL')
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

    protected function normalizeJenis(?string $jenis): ?string
    {
        if ($jenis === null || $jenis === '') {
            return null;
        }

        return match ($jenis) {
            'Expedisi' => 'EKS',
            'Rent' => 'REN',
            'Coolroom' => 'COO',
            default => strtoupper((string) $jenis),
        };
    }
}
