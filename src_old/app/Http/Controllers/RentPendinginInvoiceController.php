<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Expedisi;
use App\Models\Rekening;
use App\Models\Signature;
use App\Models\Mcustomer;
use App\Models\Arh;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Exception;

class RentPendinginInvoiceController extends Controller
{
    public function index()
    {
        return view('rentPendingin.rentPendingin-invoice');
    }

    public function getDataMuat(Request $request){
        $expedisi = Expedisi::select([
            'id',
            'NOMUAT',
            'TGLMUAT',
            'CUSTOMER',
            'CUSTOMER_KODE',
            'PESANAN',
            'GB',
            'PESANANGB',
            'rute',
            'NAMA_KENDARAAN',
            'JUMLAH',
            'UNIT',
            'HARGA',
            'DISC',
            'DC',
            'GRAND',
            'NOSJ',
            'INVOICE',
            'JENIS',
        ])
        ->where('JENIS', 'REN')
        ->whereNotNull('NOMUAT')   // 🔒 wajib punya NOMUAT
        ->orderByDesc('id');

        // 🔐 FILTER DRIVER
        if (auth()->user()->role_old === 'driver') {
            $expedisi->where('user_id', auth()->user()->user_id);
        }

        // 📅 FILTER TANGGAL
        if ($request->filled('tgl_mulai')) {
            $expedisi->whereDate('TGLMUAT', '>=', $request->tgl_mulai);
        }

        if ($request->filled('tgl_akhir')) {
            $expedisi->whereDate('TGLMUAT', '<=', $request->tgl_akhir);
        }

        // 🔍 SEARCH
        if ($request->filled('search_muat')) {
            $search = $request->search_muat;
            $expedisi->where(function ($q) use ($search) {
                $q->where('NOMUAT', 'like', "%{$search}%")
                ->orWhere('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('rute', 'like', "%{$search}%")
                ->orWhere('NOSJ', 'like', "%{$search}%")
                ->orWhere('INVOICE', 'like', "%{$search}%");
            });
        }

        // 🧾 FILTER INVOICE (DEFAULT: BELUM)
        $filterInvoice = $request->filter_invoice ?? 'belum';

        // 🧾 FILTER INVOICE (DEFAULT: BELUM)
        $filterInvoice = $request->filter_invoice ?? 'belum';

        if ($filterInvoice === 'belum') {

            // tampilkan yang belum invoice
            $expedisi->where(function ($q) {
                $q->whereNull('INVOICE')
                ->orWhere('INVOICE', '');
            });

        } elseif ($filterInvoice === 'sudah') {

            // tampilkan yang sudah invoice
            // tapi hanya master row (yang punya harga)
            $expedisi->whereNotNull('INVOICE')
                    ->where('INVOICE', '!=', '')
                    ->where('HARGA', '>', 0);
        }

        return DataTables::of($expedisi)
            ->addIndexColumn()
            ->editColumn('TGLMUAT', fn ($r) =>
                $r->TGLMUAT ? date('d-m-Y', strtotime($r->TGLMUAT)) : '-'
            )
            ->editColumn('JUMLAH', fn ($r) =>
                number_format($r->JUMLAH ?? 0, 0, ',', '.') . ' ' . ($r->UNIT ?? '')
            )
            ->addColumn('harga_formatted', fn ($r) =>
                'Rp ' . number_format($r->HARGA ?? 0, 0, ',', '.')
            )
            ->addColumn('dc_formatted', fn ($r) =>
                'Rp ' . number_format($r->DC ?? 0, 0, ',', '.')
            )
            ->addColumn('total_formatted', fn ($r) =>
                'Rp ' . number_format($r->GRAND ?? 0, 0, ',', '.')
            )
            ->addColumn('aksi', function ($r) {
                return '
                    <button class="btn btn-sm btn-primary btn-detail-dgn-inv"
                        data-nomuat="'.$r->NOMUAT.'">
                        Detail
                    </button>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function getDetailByNomuat(Request $request)
    {
        $data = Expedisi::where('NOMUAT', $request->nomuat)
            // ->where(function ($q) {
            //     $q->whereNull('INVOICE')
            //     ->orWhere('INVOICE', '');
            // })
            ->first();

        if (!$data) {
            return response()->json(['status' => false]);
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function storeRentDinginInvoice(Request $request)
    {
        try {

            $invoiceNo = null;

            DB::transaction(function () use ($request, &$invoiceNo) {

                $row = Expedisi::where('NOMUAT', $request->nomuat)
                    ->lockForUpdate()
                    ->first();

                if (!$row) {
                    throw new \Exception('Data tidak ditemukan');
                }

                if ($row->STS === 'INVOICE') {
                    throw new \Exception("NOMUAT {$row->NOMUAT} sudah di-invoice");
                }

                $invoiceNo = $this->generateInvoiceOnline();

                // Backup nilai lama
                $row->HARGAAW = $row->HARGA;
                $row->NDISCAW = $row->NDISC;
                $row->DCAW    = $row->DC;

                // Update jadi invoice
                $row->INVOICE     = $invoiceNo;
                $row->TGLINVOICE  = now();
                $row->STS         = 'INVOICE';

                $row->HARGA   = $request->harga;
                $row->DISC    = $request->diskon;
                $row->NDISC   = $this->calcNominalDiskon($request);
                $row->DC      = $request->dc;
                $row->TOTAL   = $request->total;
                $row->PPN     = $request->ppn;
                $row->GRAND   = $request->grand_total;
                $row->PIUTANG = $request->grand_total;

                $row->USERINV = auth()->user()->user_id . '-' . now()->format('d-m-Y h:i:s A');

                $row->save();

                // Simpan ke ARH
                Arh::create([
                    'NOFAKTUR'   => $invoiceNo,
                    'TGLFAKTUR'  => $row->TGLINVOICE,
                    'CUSTOMER'   => $row->CUSTOMER,
                    'PIUTANG'    => $row->GRAND,
                    'DISCOUNT'   => $row->NDISC,
                    'SALDO'      => 0,
                    'CABANG'     => $row->CABANG ?? '',
                    'KETERANGAN' => 'INVOICE DARI EXPEDISI (NOMUAT)',
                    'USER'       => auth()->user()->user_id,
                ]);
            });

            return response()->json([
                'status'  => true,
                'message' => 'Invoice berhasil disimpan',
                'redirect' => route('expedisiInvoice.pdfInvoice', [
                    'invoiceNo' => $invoiceNo
                ])
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateRentDinginInvoice(Request $request){
        try {
            $invoiceNo = $request->noinvoice;
            DB::transaction(function () use ($request, $invoiceNo) {

                $row = Expedisi::where('INVOICE', $invoiceNo)
                    ->lockForUpdate()
                    ->first();

                if (!$row) {
                    throw new \Exception('Invoice tidak ditemukan');
                }

                // Update nilai invoice
                $row->HARGA   = $request->harga;
                $row->DISC    = $request->diskon;
                $row->NDISC   = $this->calcNominalDiskon($request);
                $row->DC      = $request->dc;
                $row->TOTAL   = $request->total;
                $row->PPN     = $request->ppn;
                $row->GRAND   = $request->grand_total;
                $row->PIUTANG = $request->grand_total;

                $row->USERINV = auth()->user()->user_id . '-' . now()->format('d-m-Y h:i:s A');

                $row->save();

                // Update juga tabel ARH
                Arh::where('NOFAKTUR', $invoiceNo)->update([
                    'PIUTANG'  => $request->grand_total,
                    'DISCOUNT' => $this->calcNominalDiskon($request),
                    'USER'     => auth()->user()->user_id
                ]);

            });

            return response()->json([
                'status' => true,
                'message' => 'Invoice berhasil diupdate',
                'redirect' => route('expedisiInvoice.pdfInvoice', [
                    'invoiceNo' => $invoiceNo
                ])
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateInvoiceOnline(): string{
        $tahun = now()->format('Y');

        $last = Expedisi::where('INVOICE', 'like', "FJO{$tahun}%")
            ->orderBy('INVOICE', 'desc')
            ->lockForUpdate()
            ->first();

        $lastNo = $last
            ? intval(substr($last->INVOICE, -6))
            : 0;

        return 'FJO' . $tahun . str_pad($lastNo + 1, 6, '0', STR_PAD_LEFT);
    }

    private function calcNominalDiskon(Request $request){
        $total  = (float) $request->total;   // total_gabung_exp_inv
        $diskon = (float) $request->diskon;  // persen, contoh: 5

        if ($total <= 0 || $diskon <= 0) {
            return 0;
        }

        return $total * ($diskon / 100);
    }

}
