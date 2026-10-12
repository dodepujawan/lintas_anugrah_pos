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

class ExpedisiInvoiceController extends Controller
{
    public function index()
    {
        return view('expedisi.expedisi-invoice');
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
        ->where('JENIS', 'EKS')
        ->whereNotNull('NOMUAT')   // 🔒 wajib punya NOMUAT
        // ->orderByDesc('id');
        ->orderBy('TGLMUAT', 'desc');

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
                ->orWhere('NOSJ', 'like', "%{$search}%");
            });
        }

        // 🧾 FILTER GABUNG (DEFAULT: BELUM)
        $filterInvoice = $request->filter_invoice ?? 'belum';

        if ($filterInvoice === 'belum') {
            // tampilkan yang belum invoice
            $expedisi->where(function ($q) {
                // non GB
                $q->where(function ($sub) {
                    $sub->whereNull('GB')
                        ->orWhere('GB', '');
                });
            })
            ->where(function ($q) {
                // invoice masih kosong
                $q->whereNull('INVOICE')
                    ->orWhere('INVOICE', '');
            });
        } elseif ($filterInvoice === 'sudah') {

            // tampilkan yang sudah invoice
            // tapi hanya master row (yang punya harga)
            $expedisi->whereNotNull('GB')
            ->where('GB', '!=', '')
            ->where('HARGA', '>', 0)
            ->where(function ($q) {
                $q->whereNull('INVOICE')
                ->orWhere('INVOICE', '');
            });
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
            ->make(true);
    }

    public function dataGabung(Request $request){
        $data = Expedisi::select([
            'id',
            'NOMUAT',
            'TGLMUAT',
            'PESANAN',
            'CUSTOMER_KODE',
            'CUSTOMER',
            'rute',
            'NAMA_KENDARAAN',
            'JUMLAH',
            'JUMLAH as jumlahreal',
            'UNIT',
            'JENISHRG',
            'HARGA',
            'DISC',
            'DC',
            'PPN',
            'TOTAL',
            'GRAND',
            'NOSJ',
        ])
        ->where('CUSTOMER_KODE', $request->customer_kode)
        ->where('JENIS', 'EKS')
        ->whereNotNull('NOMUAT')
        ->where(function ($q) {
            $q->whereNull('GB')
            ->orWhere('GB', '');
        })
        ->where(function ($q) {
            $q->whereNull('INVOICE')
            ->orWhere('INVOICE', '');
        })
        ->orderBy('TGLMUAT');

        return DataTables::of($data)
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
            ->addColumn('gtotal_formatted', fn ($r) =>
                'Rp ' . number_format($r->GRAND ?? 0, 0, ',', '.')
            )
            ->make(true);
    }

    public function getExistingGabung(Request $request){
        $gb = $request->gbExist;

        $rows = Expedisi::where('GB', $gb)
            ->orderBy('NOSJ')
            ->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'data' => [],
                'master' => null
            ]);
        }

        // 🔥 karena belum invoice → tidak ada "master harga"
        // jadi kita ambil baris pertama saja sebagai referensi
        $master = $rows->first();

        $data = $rows->map(function($r,$i){

            return [
                'DT_RowIndex' => $i + 1,
                'NOMUAT' => $r->NOMUAT,
                'TGLMUAT' => $r->TGLMUAT ? date('d-m-Y', strtotime($r->TGLMUAT)) : '-',
                'NOSJ' => $r->NOSJ,
                'PESANAN' => $r->PESANAN,
                'rute' => $r->rute,
                'JENISHRG' => $r->JENISHRG,
                'NAMA_KENDARAAN' => $r->NAMA_KENDARAAN,
                'JUMLAH' => number_format($r->JUMLAH ?? 0,0,',','.').' '.$r->UNIT,
                'jumlahreal' => $r->JUMLAH,

                // ❗ ini kemungkinan 0 semua (karena belum invoice)
                'harga_formatted' => 'Rp '.number_format($r->HARGA ?? 0,0,',','.'),
                'gtotal_formatted' => 'Rp '.number_format($r->GRAND ?? 0,0,',','.'),

                'HARGA' => $r->HARGA,
                'DISC' => $r->DISC,
                'DC' => $r->DC,
                'TOTAL' => $r->TOTAL,
                'PPN' => $r->PPN
            ];

        });

        return response()->json([
            'data' => $data,
            'master' => $master
        ]);
    }

    public function storeGabungInvoice(Request $request){
        try {

            $gb = null;

            DB::transaction(function () use ($request, &$gb) {

                $rows = Expedisi::whereIn('NOSJ', $request->nosj_list)
                    ->lockForUpdate()
                    ->orderBy('NOSJ')
                    ->get();

                // ❌ Tidak ada data
                if ($rows->isEmpty()) {
                    throw new Exception('Data SJ tidak ditemukan');
                }

                // ❌ Minimal 2 SJ
                if ($rows->count() < 2) {
                    throw new Exception('Gabung minimal 2 SJ');
                }

                // ❌ Validasi
                foreach ($rows as $row) {

                    if ($row->STS === 'INVOICE') {
                        throw new Exception("SJ {$row->NOSJ} sudah di-invoice");
                    }

                    // 🔥 optional tapi sangat disarankan
                    if ($row->GB) {
                        throw new Exception("SJ {$row->NOSJ} sudah tergabung di GB {$row->GB}");
                    }
                }

                // ✅ Generate GB
                $gb = $this->generateGB();

                $isFirst = true;

                foreach ($rows as $row) {

                    // backup nilai lama
                    $row->HARGAAW  = $row->HARGA;
                    $row->NDISCAW  = $row->NDISC;
                    $row->DCAW     = $row->DC;

                    // assign GB
                    $row->GB        = $gb;
                    $row->PESANANGB = $request->item;
                    $row->STS       = 'GABUNG';

                    if ($isFirst) {

                        // 🔥 MASTER (nilai dari admin)
                        $row->HARGA   = $request->harga;
                        $row->DISC    = $request->diskon;
                        $row->NDISC   = $this->calcNominalDiskon($request);
                        $row->DC      = $request->dc;
                        $row->TOTAL   = $request->total;
                        $row->PPN     = $request->ppn;
                        $row->GRAND   = $request->grand_total;
                        $row->PIUTANG = $request->grand_total;

                        $isFirst = false;

                    } else {

                        // 🔹 DETAIL
                        $row->HARGA   = 0;
                        $row->DISC    = 0;
                        $row->NDISC   = 0;
                        $row->DC      = 0;
                        $row->TOTAL   = 0;
                        $row->PPN     = 0;
                        $row->GRAND   = 0;
                        $row->PIUTANG = 0;
                    }

                    $row->USERINV = auth()->user()->user_id . '-' . now()->format('d-m-Y h:i:s A');

                    $row->save();
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'Gabung SJ berhasil disimpan',
                'gb'      => $gb
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateGabungInvoice(Request $request){
        try {

            $gb = $request->no_invoice; // tetap pakai ini kalau legacy

            DB::transaction(function () use ($request, $gb) {

                if (!$gb) {
                    throw new Exception('GB tidak ditemukan');
                }

                // =============================
                // 1. rollback semua isi lama
                // =============================
                $oldRows = Expedisi::where('GB', $gb)
                    ->lockForUpdate()
                    ->get();

                foreach ($oldRows as $row) {

                    // restore nilai awal
                    $row->HARGA = $row->HARGAAW;
                    $row->NDISC = $row->NDISCAW;
                    $row->DC    = $row->DCAW;

                    $row->TOTAL   = 0;
                    $row->PPN     = 0;
                    $row->GRAND   = 0;
                    $row->PIUTANG = 0;

                    $row->GB = null;
                    $row->PESANANGB = null;
                    $row->STS = null;

                    $row->save();
                }

                // =============================
                // 2. ambil SJ baru
                // =============================
                $rows = Expedisi::whereIn('NOSJ', $request->nosj_list)
                    ->lockForUpdate()
                    ->orderBy('NOSJ')
                    ->get();

                if ($rows->isEmpty()) {
                    throw new Exception('Data SJ tidak ditemukan');
                }

                if ($rows->count() < 2) {
                    throw new Exception('Gabung minimal 2 SJ');
                }

                $isFirst = true;

                foreach ($rows as $row) {

                    if ($row->STS === 'INVOICE') {
                        throw new Exception("SJ {$row->NOSJ} sudah di-invoice");
                    }

                    if ($row->GB && $row->GB !== $gb) {
                        throw new Exception("SJ {$row->NOSJ} sudah ada di GB lain");
                    }

                    // backup ulang
                    $row->HARGAAW  = $row->HARGA;
                    $row->NDISCAW  = $row->NDISC;
                    $row->DCAW     = $row->DC;

                    $row->GB        = $gb;
                    $row->PESANANGB = $request->item;
                    $row->STS       = 'GABUNG';

                    if ($isFirst) {

                        // 🔥 MASTER
                        $row->HARGA   = $request->harga;
                        $row->DISC    = $request->diskon;
                        $row->NDISC   = $this->calcNominalDiskon($request);
                        $row->DC      = $request->dc;
                        $row->TOTAL   = $request->total;
                        $row->PPN     = $request->ppn;
                        $row->GRAND   = $request->grand_total;
                        $row->PIUTANG = $request->grand_total;

                        $isFirst = false;

                    } else {

                        // 🔹 DETAIL
                        $row->HARGA   = 0;
                        $row->DISC    = 0;
                        $row->NDISC   = 0;
                        $row->DC      = 0;
                        $row->TOTAL   = 0;
                        $row->PPN     = 0;
                        $row->GRAND   = 0;
                        $row->PIUTANG = 0;
                    }

                    $row->USERINV = auth()->user()->user_id . '-' . now()->format('d-m-Y h:i:s A');

                    $row->save();
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Gabung berhasil diupdate',
                'gb' => $gb
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateGB(): string{
        $date = now()->format('Ymd');

        $last = Expedisi::whereNotNull('GB')
            ->orderBy('GB', 'desc')
            ->lockForUpdate()
            ->first();

        $lastNo = $last
            ? intval(substr($last->GB, -4))
            : 0;

        return $date . str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
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

// #### Fungsi Print Yang Lama
// public function printInvoiceText($invoiceNo)
// {
//     $rows = Expedisi::where('INVOICE', $invoiceNo)->orderBy('NOSJ')->get();
//     if ($rows->isEmpty()) abort(404);

//     $master = $rows->first();

//     // Customer
//     $customer = Mcustomer::where('CUSTOMER', $master->CUSTOMER_KODE)->first();
//     $kepada = strtoupper($customer->NAMACUST ?? '-');
//     $up     = strtoupper($customer->KONTAK ?? '-');
//     $alamat = strtoupper($customer->ALAMAT1 ?? '-');

//     // Rekening
//     $rekening = Rekening::where('AKTIF', 1)->first();
//     $bank    = $rekening->BANK ?? '-';
//     $norek   = $rekening->NOREK ?? '-';
//     $namaRek = $rekening->NAMA ?? '-';

//     // Total
//     $subtotal = (float) ($master->GRAND ?? 0);
//     $dibayar  = (float) ($master->BAYAR ?? 0);
//     $saldo    = (float) ($master->PIUTANG ?? 0);

//     $lines = [];

//     // HEADER (invoice title + company name)
//     $lines[] = str_pad('INVOICE', 40) . str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', 40, ' ', STR_PAD_LEFT);
//     $lines[] = str_repeat('=', 80);
//     $lines[] = 'NOMOR     : ' . $master->INVOICE;
//     $lines[] = 'TANGGAL   : ' . date('d-m-Y', strtotime($master->TGLINVOICE));
//     $lines[] = 'TGL JT    : ' . date('d-m-Y', strtotime($master->TGLJT));
//     $lines[] = 'TGL CETAK : ' . now()->format('d-m-Y H:i');
//     $lines[] = '';
//     $lines[] = 'NOMOR SJ  : ' . $rows->pluck('NOSJ')->implode(', ');
//     $lines[] = '';
//     $lines[] = 'KEPADA    : ' . $kepada;
//     $lines[] = 'UP        : ' . $up;
//     $lines[] = 'ALAMAT    : ' . $alamat;
//     $lines[] = str_repeat('=', 80);

//     // TABLE HEADER
//     $lines[] = sprintf("%-3s %-12s %-32s %-10s %15s", 'NO', 'SJ', 'NAMA BARANG', 'JUMLAH', 'SUB TOTAL');
//     $lines[] = str_repeat('-', 80);

//     // DETAIL
//     $no = 1;
//     foreach ($rows as $r) {
//         $namaBarang = trim($r->PESANANGB) !== '' ? $r->PESANANGB : $r->PESANAN;
//         $qty = (float) $r->JUMLAH;
//         $qtyText = floor($qty) == $qty ? number_format($qty, 0) : rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.');

//         $lines[] = sprintf(
//             "%-3s %-12s %-32s %-10s %15s",
//             $no,
//             $r->NOSJ,
//             substr($namaBarang, 0, 32),
//             $qtyText . ' KG',
//             number_format($r->TOTAL, 0, ',', '.')
//         );
//         $no++;
//     }

//     $lines[] = str_repeat('-', 80);

//     // PEMBAYARAN
//     $lines[] = '';
//     $lines[] = 'Untuk pembayaran mohon transfer ke rekening resmi :';
//     $lines[] = '';
//     $lines[] = 'Bank   : ' . $bank;
//     $lines[] = 'No Rek : ' . $norek;
//     $lines[] = 'A/N    : ' . $namaRek;
//     $lines[] = '';

//     // TOTAL (kanan bawah)
//     $lines[] = str_pad('SUB TOTAL : ' . number_format($subtotal, 0, ',', '.'), 80, ' ', STR_PAD_LEFT);
//     $lines[] = str_pad('DIBAYAR   : ' . number_format($dibayar, 0, ',', '.'), 80, ' ', STR_PAD_LEFT);
//     $lines[] = str_pad('SALDO     : ' . number_format($saldo, 0, ',', '.'), 80, ' ', STR_PAD_LEFT);

//     // FOOTER
//     $footer = [];
//     $footer[] = '';
//     $footer[] = '';
//     $footer[] = str_pad('PENERIMA', 40) . str_pad('MENGETAHUI', 40);
//     $footer[] = '';
//     $footer[] = '';
//     $footer[] = '';
//     $footer[] = '';
//     $footer[] = str_pad('(......................)', 40) . str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', 40);

//     // FIX HEIGHT (disesuaikan dengan jumlah item)
//     $pageHeight = 60;
//     while (count($lines) < ($pageHeight - count($footer))) {
//         $lines[] = '';
//     }
//     $lines = array_merge($lines, $footer);

//     // OUTPUT
//     $text = implode("\r\n", $lines);
//     $text = iconv('UTF-8', 'CP437//TRANSLIT', $text);

//     return response()->json(['text' => $text]);
// }
