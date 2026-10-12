<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\KwitansiExport;
use Maatwebsite\Excel\Facades\Excel;

class KwitansiHistoryController extends Controller
{
    public function index()
    {
        return view('kwitansi.kwitansi');
    }

    public function getBelumKwitansi(){
        $expedisi = DB::table('expedisi')
            ->select([
                DB::raw("'EKS' as JENIS"),
                DB::raw("'-' as NOKWT"),
                'INVOICE',
                'TGLINVOICE',
                DB::raw("CUSTOMER as KETERANGAN"),
                DB::raw("GRAND as NILAI"),
                'GRAND',
                'PIUTANG',
                'kwt',
            ])
            ->where('JENIS', 'EKS')
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '<>', '')
            ->where('GRAND', '>', 0)
            ->where(function ($q) {
                $q->whereNull('kwt')
                ->orWhere('kwt', '');
            });

        $coolroom = DB::table('coolrooms')
            ->select([
                DB::raw("'COOL' as JENIS"),
                DB::raw("'-' as NOKWT"),
                'INVOICE',
                'TGLINVOICE',
                DB::raw("CUSTOMER as KETERANGAN"),
                DB::raw("GRAND as NILAI"),
                'GRAND',
                'PIUTANG',
                'kwt',
            ])
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '<>', '')
            ->where('GRAND', '>', 0)
            ->where(function ($q) {
                $q->whereNull('kwt')
                ->orWhere('kwt', '');
            });

        $union = $expedisi->unionAll($coolroom);

        return DB::query()->fromSub($union, 'kwitansi');
    }

    public function getSudahKwitansi()
    {
        return DB::table('kwitansi')
            ->select([
                'NOKWT',
                'FDOK_TRANS as INVOICE',
                'TGL as TGLINVOICE',
                'FKETERANG as KETERANGAN',
                'FNIL_DOK as NILAI',
                DB::raw("0 as GRAND"),
                DB::raw("0 as PIUTANG"),
                'JENIS',
            ]);
    }

    public function getDataKwitansi(Request $request)
    {
        if ($request->status_kwt == 'belum') {
            $query = $this->getBelumKwitansi();
        } else {
            $query = $this->getSudahKwitansi();
        }

        return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('TGLINVOICE', function ($row) {
                return $row->TGLINVOICE
                    ? \Carbon\Carbon::parse($row->TGLINVOICE)->format('d-m-Y')
                    : '-';
            })

            ->editColumn('NILAI', function ($row) {
                return number_format($row->NILAI, 0, ',', '.');
            })

            ->make(true);
    }

    public function exportKwitansi(Request $request)
    {
        $request->validate([
            'status_kwt_his' => 'required|in:belum,sudah',
            'tanggal_dari' => 'nullable|date',
            'tanggal_sampai' => 'nullable|date|after_or_equal:tanggal_dari',
        ]);

        $status = $request->status_kwt_his;
        $tanggalDari = $request->tanggal_dari;
        $tanggalSampai = $request->tanggal_sampai;

        // Buat nama file
        $filename = 'laporan_kwitansi_' .
            $status .
            ($tanggalDari ? '_' . $tanggalDari . '_sd_' . $tanggalSampai : '') .
            '.xlsx';

        return Excel::download(
            new KwitansiExport($status, $tanggalDari, $tanggalSampai),
            $filename
        );
    }
}
