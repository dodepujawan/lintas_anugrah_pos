<?php

namespace App\Http\Controllers;

use App\Exports\KwitansiExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class KwitansiHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status_kwt_his' => ['nullable', 'in:belum,sudah'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $status = $filters['status_kwt_his'] ?? 'belum';
        $search = trim((string) $request->input('search'));
        $query = $this->queryForStatus($status);
        $searchableColumns = ['NOKWT', 'INVOICE', 'KETERANGAN'];
        $query->when($search !== '', function ($query) use ($search, $searchableColumns) {
            // Search predicates are grouped so they cannot bypass the status source.
            $query->where(function ($query) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($column, 'like', "%{$search}%");
                }
            });
        });

        $totalNilai = (float) (clone $query)->sum('NILAI');
        $kwitantis = $query->orderByDesc('TGLINVOICE')->paginate(20)->withQueryString();

        return view('kwitansi-history.index', compact('kwitantis', 'search', 'status', 'totalNilai'));
    }

    public function exportKwitansi(Request $request)
    {
        $filters = $request->validate([
            'status_kwt_his' => ['required', 'in:belum,sudah'],
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_dari'],
        ]);
        $status = $filters['status_kwt_his'];
        $tanggalDari = $filters['tanggal_dari'] ?? null;
        $tanggalSampai = $filters['tanggal_sampai'] ?? null;
        $query = $this->queryForStatus($status, $tanggalDari, $tanggalSampai)
            ->orderBy('TGLINVOICE');
        $filename = 'laporan_kwitansi_'.$status;

        if ($tanggalDari || $tanggalSampai) {
            $filename .= '_'.($tanggalDari ?? 'awal').'_sd_'.($tanggalSampai ?? 'akhir');
        }

        return Excel::download(
            new KwitansiExport($query->get()),
            $filename.'.xlsx'
        );
    }

    private function queryForStatus(string $status, ?string $tanggalDari = null, ?string $tanggalSampai = null)
    {
        if ($status === 'belum') {
            $expedisi = DB::table('expedisis')
                ->select([
                    DB::raw("'EKS' as JENIS"),
                    DB::raw("'-' as NOKWT"),
                    'INVOICE',
                    'TGLINVOICE',
                    DB::raw('CUSTOMER as KETERANGAN'),
                    DB::raw('GRAND as NILAI'),
                ])
                ->where('JENIS', 'EKS')
                ->whereNotNull('INVOICE')
                ->where('INVOICE', '<>', '')
                ->where('GRAND', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('kwt')->orWhere('kwt', '');
                });

            $coolroom = DB::table('coolrooms')
                ->select([
                    DB::raw("'COOL' as JENIS"),
                    DB::raw("'-' as NOKWT"),
                    'INVOICE',
                    'TGLINVOICE',
                    DB::raw('CUSTOMER as KETERANGAN'),
                    DB::raw('GRAND as NILAI'),
                ])
                ->whereNotNull('INVOICE')
                ->where('INVOICE', '<>', '')
                ->where('GRAND', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('KWT')->orWhere('KWT', '');
                });

            $union = $expedisi->unionAll($coolroom);
            $query = DB::query()->fromSub($union, 'kwitansi_history')->select([
                'JENIS', 'NOKWT', 'INVOICE', 'TGLINVOICE', 'KETERANGAN', 'NILAI',
            ]);
        } else {
            $kwitansi = DB::table('kwitansi')->select([
                'JENIS',
                'NOKWT',
                DB::raw('FDOK_TRANS as INVOICE'),
                DB::raw('TGL as TGLINVOICE'),
                DB::raw('FKETERANG as KETERANGAN'),
                DB::raw('FNIL_DOK as NILAI'),
            ]);
            $query = DB::query()->fromSub($kwitansi, 'kwitansi_history')->select([
                'JENIS', 'NOKWT', 'INVOICE', 'TGLINVOICE', 'KETERANGAN', 'NILAI',
            ]);
        }

        return $query
            ->when($tanggalDari, fn ($query) => $query->whereDate('TGLINVOICE', '>=', $tanggalDari))
            ->when($tanggalSampai, fn ($query) => $query->whereDate('TGLINVOICE', '<=', $tanggalSampai));
    }
}
