<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coolroom;
use App\Models\Signature;
use App\Models\Arh;
use App\Models\Kwitansi;
use App\Models\Mcustomer;
use App\Models\Rekening;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CoolroomExport;

use Illuminate\Support\Facades\DB;

class CoolroomGenerateInvoiceController extends Controller
{
    public function index()
    {
        return view('coolroomInvoiceGen.coolroom-invoice-gen');
    }

    public function getDataInvoice(Request $request)
    {
        $query=Coolroom::query();
        // =====================
        // FILTER
        // =====================
        if($request->status_invoice=='belum'){
            $query->where(function($q){
                $q->whereNull('INVOICE')
                    ->orWhere('INVOICE','');
            });
        }
        if($request->status_invoice=='sudah'){
            $query->whereNotNull('INVOICE')
                ->where('INVOICE','!=','');
        }

        $query->orderByDesc('id'); // <-- descending by id

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('TGLSJ',function($row){
                return $row->TGLSJ
                    ? Carbon::parse($row->TGLSJ)
                        ->format('d-m-Y')
                    : '-';
            })
            ->editColumn('JUMLAH',function($row){
                return number_format(
                    $row->JUMLAH ?? 0,
                    3,
                    ',',
                    '.'
                );
            })

            ->editColumn('GRAND',function($row){
                return number_format(
                    $row->GRAND ?? 0,
                    0,
                    ',',
                    '.'
                );
            })
            ->addColumn('action',function($row) use ($request){
                // =====================
                // SUDAH INVOICE
                // =====================
                if($request->status_invoice=='sudah'){
                    return '
                        <button
                            class="btn btn-sm btn-primary btn-edit-invoice-coolroom"
                            data-id="'.$row->id.'"
                            data-nosj="'.$row->NOSJ.'">
                            Edit Invoice
                        </button>
                    ';
                }
                // =====================
                // BELUM INVOICE
                // =====================
                return '
                    <button
                        class="btn btn-sm btn-success btn-proses-invoice-coolroom"
                        data-id="'.$row->id.'"
                        data-nosj="'.$row->NOSJ.'">
                        Buat Invoice
                    </button>
                ';
            })
            ->rawColumns([
                'action'
            ])
            ->make(true);
    }

    public function showInvoiceCoolroom($nosj)
    {
        // =====================
        // AMBIL DATA
        // =====================
        $coolroom=Coolroom::where('NOSJ',$nosj)
            ->first();
        if(!$coolroom){
            return response()->json([
                'status'=>false,
                'message'=>'Data coolroom tidak ditemukan'
            ]);
        }
        // =====================
        // DATA ARH
        // =====================
        $arh=null;
        if(!empty($coolroom->INVOICE)){
            $arh=Arh::where('NOFAKTUR',$coolroom->INVOICE)->first();
            if ($arh) {
                $bayar = (float) ($arh->BAYAR ?? 0);
                if ($bayar > 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invoice sudah memiliki pembayaran. Silakan hubungi admin.'
                    ]);
                }
            }
        }
        // =====================
        // TOP KREDIT CUSTOMER
        // =====================
        $topKredit=0;
        if($coolroom->CUSTOMER_KODE){
            $customer=Mcustomer::where(
                'kode_cus',
                $coolroom->CUSTOMER_KODE
            )->first();
            $topKredit=$customer->TOPKREDIT ?? 0;
        }
        // =====================
        // TGL INVOICE
        // =====================
        $tglInvoice=$coolroom->TGLINVOICE
            ? Carbon::parse($coolroom->TGLINVOICE)
            : now();
        // =====================
        // TGL JATUH TEMPO
        // =====================
        $tglJatuhTempo=$tglInvoice
            ->copy()
            ->addDays((int)$topKredit);
        // =====================
        // RESPONSE
        // =====================
        return response()->json([
            'status'=>true,
            'data'=>[
                // =====================
                // IDENTITAS
                // =====================
                'id'=>$coolroom->id,
                'nosj'=>$coolroom->NOSJ,
                'tglsj'=>$coolroom->TGLSJ,
                'invoice'=>$coolroom->INVOICE ?? '',
                'tgl_invoice'=>$coolroom->TGLINVOICE,
                'customer'=>$coolroom->CUSTOMER,
                'customer_kode'=>$coolroom->CUSTOMER_KODE,
                // =====================
                // NILAI
                // =====================
                'jumlah'=>$coolroom->JUMLAH ?? 0,
                'harga'=>$coolroom->HARGA ?? 0,
                'sub_total'=>$coolroom->SUBTOTAL ?? 0,
                'disc_persen'=>$coolroom->DISC ?? 0,
                'disc_rp'=>$coolroom->NDISC ?? 0,
                'dpp'=>$coolroom->DPP ?? 0,
                'ppn'=>$coolroom->PPN ?? 0,
                'ppn_rp'=>$coolroom->NPPN ?? 0,
                'grand'=>$coolroom->GRAND ?? 0,
                // =====================
                // PEMBAYARAN
                // =====================
                'bayar'=>$coolroom->BAYAR ?? 0,
                'piutang'=>$coolroom->PIUTANG ?? 0,
                // =====================
                // JATUH TEMPO
                // =====================
                'top'=>$topKredit,
                'tgl_jt'=>$coolroom->TGLJT
                    ?? $tglJatuhTempo
                        ->format('Y-m-d'),
                // =====================
                // ARH
                // =====================
                'saldo'=>$arh->SALDO ?? 0
            ]
        ]);
    }

    public function prosesInvoice(Request $request)
    {
        try {
            $invoice=$request->invoice;
            DB::transaction(function () use ($request,&$invoice) {
                // =====================
                // PARSING
                // =====================
                $bayar=(int)preg_replace(
                    '/[^0-9]/',
                    '',
                    $request->bayar
                );
                $top=(int)preg_replace(
                    '/[^0-9]/',
                    '',
                    $request->top
                );
                $tglJtp=$request->tgl_jtp;
                $nosj=$request->nosj;
                // =====================
                // STORE BARU
                // =====================
                if(empty($invoice)){
                    $invoice=$this->generateInvoiceCoolroom();
                    $row=Coolroom::where(
                        'NOSJ',
                        $nosj
                    )
                    ->lockForUpdate()
                    ->first();
                    if(!$row){
                        throw new \Exception(
                            'Data coolroom tidak ditemukan'
                        );
                    }
                    $row->INVOICE=$invoice;
                    $row->TGLINVOICE=now();
                    $row->STS='INVOICE';
                }else{
                    // =====================
                    // UPDATE EXISTING
                    // =====================
                    $row=Coolroom::where(
                        'INVOICE',
                        $invoice
                    )
                    ->lockForUpdate()
                    ->first();
                    if(!$row){
                        throw new \Exception(
                            'Invoice tidak ditemukan'
                        );
                    }
                    // =============================
                    // CEK PEMBAYARAN ARH
                    // =============================
                    $arh = null;
                    if (!empty($row->INVOICE)) {
                        $arh = Arh::where('NOFAKTUR', $row->INVOICE)->first();
                        if ($arh && (float)$arh->BAYAR > 0) {throw new \Exception('Invoice sudah memiliki pembayaran. Silakan hubungi admin.');}
                    }
                }
                // =====================
                // GRAND
                // =====================
                $grand=(int)($row->GRAND ?? 0);
                // =====================
                // VALIDASI
                // =====================
                if($bayar<0){
                    throw new \Exception(
                        'Nominal bayar tidak valid'
                    );
                }
                if($bayar>$grand){
                    throw new \Exception(
                        'Bayar tidak boleh lebih besar dari total'
                    );
                }
                // =====================
                // PIUTANG
                // =====================
                $piutangBaru=$grand-$bayar;
                // =====================
                // UPDATE COOLROOM
                // =====================
                $row->BAYAR=$bayar;
                $row->PIUTANG=$piutangBaru;
                $row->TOP=$top;
                $row->TGLJT=$tglJtp;
                $row->save();
                // =====================
                // HANDLE ARH
                // =====================
                if($piutangBaru>0){
                    Arh::updateOrCreate(
                        [
                            'NOFAKTUR'=>$invoice
                        ],
                        [
                            'TGLFAKTUR'=>$row->TGLINVOICE ?? now(),
                            'CUSTOMER'=>$row->CUSTOMER,
                            'SALDO'=>$top,
                            'PIUTANG'=>$piutangBaru,
                            'DISCOUNT'=>$row->NDISC ?? 0,
                            'TGLJT'=>$tglJtp,
                            'CABANG'=>$row->CABANG ?? '',
                            'KETERANGAN'=>'INVOICE COOLROOM',
                            'USER'=>auth()->user()->user_id,
                            'USER_UPDATE'=>auth()->user()->user_id,
                            'updated_at'=>now()
                        ]
                    );
                }else{
                    // =====================
                    // LUNAS
                    // =====================
                    Arh::where(
                        'NOFAKTUR',
                        $invoice
                    )->delete();
                }
            });
            return response()->json([
                'status'=>true,
                'message'=>'Invoice coolroom berhasil diproses',
                'invoice'=>$invoice
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function pdfGenerate($invoice)
    {
        // =====================
        // MASTER
        // =====================
        $master=Coolroom::where(
            'INVOICE',
            $invoice
        )->firstOrFail();

        // =====================
        // ARH
        // =====================
        $arh=Arh::where(
            'NOFAKTUR',
            $invoice
        )->first();

        // =====================
        // CUSTOMER
        // =====================
        $customer=Mcustomer::where(
            'kode_cus',
            $master->CUSTOMER_KODE
        )->first();

        // =====================
        // SIGNATURE
        // =====================
        $signature=Signature::latest()
            ->first();

        // =====================
        // MPDF
        // =====================
        $tempPath = storage_path('app/mpdf-temp');

        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        $pdf = new \Mpdf\Mpdf([
            'mode'    => 'utf-8',
            'format'  => 'A4',
            'tempDir' => $tempPath,
        ]);

        $html=view(
            'coolroomInvoiceGen.coolroomInvoicePdf',
            compact(
                'master',
                'arh',
                'customer',
                'signature'
            )
        )->render();

        $pdf->WriteHTML($html);

        return response(
            $pdf->Output(
                'INV-'.$invoice.'.pdf',
                'I'
            ),
            200,
            [
                'Content-Type'=>'application/pdf'
            ]
        );
    }

    public function export(Request $request)
    {
        $request->validate([
            'tanggal_dari'   => 'required|date',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari',
            'filter_inv_coolroom' => 'required'
        ]);

        $tanggalDari   = $request->tanggal_dari;
        $tanggalSampai = $request->tanggal_sampai;
        $status        = $request->filter_inv_coolroom;
        $customer      = $request->customer_invoice_col; // select2 customer

        $filename =
            'laporan_coolroom_' .
            $status . '_' .
            $tanggalDari . '_sd_' .
            $tanggalSampai .
            '.xlsx';

        return Excel::download(
            new CoolroomExport(
                $tanggalDari,
                $tanggalSampai,
                $status,
                $customer
            ),
            $filename
        );
    }

    // ##################### EDIT COOLROOM ###############################
    public function indexEdit()
    {
        return view('coolroomInvoiceGen.coolroom-invoice-edit');
    }

    public function tableEditCoolroom(Request $request)
    {
        $query = Coolroom::select(['INVOICE', 'TGLINVOICE', 'CUSTOMER', 'GRAND', 'PIUTANG', 'TGLJT', 'KETERANGAN'])
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '!=', '')
            ->where('GRAND', '>', 0)
            ->orderByDesc('TGLINVOICE')
            ->orderByDesc('INVOICE');

        if ($request->tanggal_dari) {
            $query->whereDate('TGLINVOICE', '>=', $request->tanggal_dari);
        }
        if ($request->tanggal_sampai) {
            $query->whereDate('TGLINVOICE', '<=', $request->tanggal_sampai);
        }

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->search['value'] ?? false) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('INVOICE', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%");
                    });
                }
            })
            ->addIndexColumn()
            ->editColumn('TGLINVOICE', fn($row) => $row->TGLINVOICE ? Carbon::parse($row->TGLINVOICE)->format('d-m-Y') : '-')
            ->editColumn('GRAND', fn($row) => number_format($row->GRAND ?? 0, 0, ',', '.'))
            ->editColumn('PIUTANG', fn($row) => number_format($row->PIUTANG ?? 0, 0, ',', '.'))
            ->addColumn('bayar', fn($row) => number_format(($row->GRAND ?? 0) - ($row->PIUTANG ?? 0), 0, ',', '.'))
            ->addColumn('aksi', fn($row) => '<button class="btn btn-warning btn-sm btn_edit_invoice_coolroom" data-invoice="' . $row->INVOICE . '"><i class="bx bx-edit-alt"></i></button>')
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function showEditInvoiceCoolroom($invoice)
    {
        $row = Coolroom::where('INVOICE', $invoice)->first();
        if (!$row) {
            return response()->json(['status' => false, 'message' => 'Invoice tidak ditemukan']);
        }

        $arh = null;
        if (!empty($row->INVOICE)) {
            $arh = Arh::where('NOFAKTUR', $invoice)->first();
            if ($arh) {
                $bayar = (float) ($arh->BAYAR ?? 0);
                if ($bayar > 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invoice sudah memiliki pembayaran. Silakan hubungi admin.'
                    ]);
                }
            }
        }

        return response()->json([
            'status' => true,
            'master' => [
                'invoice' => $row->INVOICE,
                'customer' => $row->CUSTOMER,
                'tgl_invoice' => $row->TGLINVOICE ? Carbon::parse($row->TGLINVOICE)->format('Y-m-d') : null,
                'tgl_jt' => ($arh->TGLJT ?? $row->TGLJT) ? Carbon::parse($arh->TGLJT ?? $row->TGLJT)->format('Y-m-d') : null,
                'boxing' => $row->BOXING ?? 0,
                'jumlah' => $row->JUMLAH ?? 0,
                'unit' => $row->UNIT ?? 'KG',
                'harga' => $row->HARGA ?? 0,
                'subtotal' => $row->SUBTOTAL ?? 0,
                'disc' => $row->DISC ?? 0,
                'dpp' => $row->DPP ?? 0,
                'ppn' => $row->PPN ?? 0,
                'total' => $row->TOTAL ?? 0,
                'grand' => $row->GRAND ?? 0,
                'bayar' => $row->BAYAR ?? 0,
                'piutang' => $row->PIUTANG ?? 0,
                'keterangan' => $row->KETERANGAN ?? ''
            ]
        ]);
    }

    public function updateEditInvoiceCoolroom(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $invoice = $request->invoice;
                if (!$invoice) {
                    throw new \Exception('Invoice tidak ditemukan');
                }

                $coolroom = Coolroom::where('INVOICE', $invoice)->first();
                if (!$coolroom) {
                    throw new \Exception('Data invoice tidak ditemukan');
                }

                $arh = null;
                if (!empty($coolroom->INVOICE)) {
                    $arh = Arh::where('NOFAKTUR', $coolroom->INVOICE)->first();
                    if ($arh && (float)$arh->BAYAR > 0) {throw new \Exception('Invoice sudah memiliki pembayaran. Silakan hubungi admin.');}
                }

                $jumlah = (float) ($request->jumlah ?? 0);
                $harga = (float) ($request->harga ?? 0);
                $discPersen = (float) ($request->disc ?? 0);
                $ppnPersen = (float) ($request->ppn ?? 0);
                $boxing = $request->boolean('boxing');

                // Hitung subtotal
                $subtotal = $boxing ? $harga : $jumlah * $harga;
                $ndisc = round($subtotal * ($discPersen / 100), 0);
                $dpp = round($subtotal - $ndisc, 0);
                $nppn = round($dpp * ($ppnPersen / 100), 0);
                $grand = round($dpp + $nppn, 0);

                $bayarInput = trim((string) $request->input('bayar', '0'));
                if (str_contains($bayarInput, '-')) {
                    throw new \Exception('Nominal bayar tidak valid');
                }
                $bayarDigits = preg_replace('/\D+/', '', $bayarInput);
                $bayar = (int) ($bayarDigits ?: 0);
                if ($grand < $bayar) {
                    throw new \Exception('Grand tidak boleh lebih kecil dari pembayaran yang sudah diterima');
                }
                $piutang = round($grand - $bayar);

                // Update coolroom
                $coolroom->update([
                    'BOXING'      => $boxing,
                    'JUMLAH'      => $jumlah,
                    'HARGA'       => $harga,
                    'SUBTOTAL'    => $subtotal,
                    'DISC'        => $discPersen,
                    'NDISC'       => $ndisc,
                    'DPP'         => $dpp,
                    'PPN'         => $ppnPersen,
                    'NPPN'        => $nppn,
                    'TOTAL'       => $dpp,
                    'GRAND'       => $grand,
                    'BAYAR'       => $bayar,
                    'PIUTANG'     => $piutang,
                    'TGLJT'       => $request->tgl_jt,
                    'KETERANGAN'  => $request->keterangan,
                    'USEREDIT'    => auth()->user()->user_id
                ]);

                // Handle ARH
                $arh = Arh::where('NOFAKTUR', $invoice)->first();
                if ($piutang > 0) {
                    if (!$arh) {
                        Arh::create([
                            'NOFAKTUR'   => $invoice,
                            'TGLFAKTUR'  => $coolroom->TGLINVOICE,
                            'TGLJT'      => $request->tgl_jt,
                            'CUSTOMER'   => $coolroom->CUSTOMER,
                            'PIUTANG'    => $piutang,
                            'BAYAR'      => 0,
                            // 'SALDO'      => $piutang,
                            'KETERANGAN' => $request->keterangan,
                            'USER'       => auth()->user()->user_id,
                            'CABANG'     => $coolroom->area_id ?? ''
                        ]);
                    } else {
                        $arh->update([
                            'TGLJT'       => $request->tgl_jt,
                            'PIUTANG'     => $piutang,
                            // 'SALDO'       => $piutang,
                            'KETERANGAN'  => $request->keterangan,
                            'USER_UPDATE' => auth()->user()->user_id
                        ]);
                    }
                } else {
                    if ($arh) $arh->delete();
                }

                // Update kwitansi jika ada
                $kwitansi = Kwitansi::where('FDOK_TRANS', $invoice)->first();
                if ($kwitansi) {
                    $kwitansi->update([
                        'FNIL_DOK' => $grand,
                        'TOTAL'    => $dpp,
                        'PPN'      => $ppnPersen,
                        'DISC'     => $discPersen,
                        'NDISC'    => $ndisc
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice coolroom berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function printInvoiceCoolroom($invoiceNo)
    {
        $rows = Coolroom::where('INVOICE', $invoiceNo)->orderBy('NOSJ')->get();
        if ($rows->isEmpty()) {
            abort(404);
        }

        $master = $rows->first();

        // ================================================
        // SETTING PRINT - SAMA SEPERTI EXPEDISI
        // ================================================
        $LINE_WIDTH = 130;
        $LEFT_MARGIN = 3;
        $LINES_PER_PAGE = 30;

        // ================================================
        // ESC/P CODES - SAMA SEPERTI EXPEDISI
        // ================================================
        $ESC = "\x1B";
        $BOLD_ON = $ESC . 'E';
        $BOLD_OFF = $ESC . 'F';
        $DW_ON = $ESC . 'W' . "\x01";
        $DW_OFF = $ESC . 'W' . "\x00";

        // ================================================
        // CUSTOMER
        // ================================================
        $customer = Mcustomer::where('CUSTOMER', $master->CUSTOMER_KODE)->first();
        $kepada = strtoupper($customer->NAMACUST ?? '-');
        $up = strtoupper($customer->KONTAK ?? '-');
        $alamat = strtoupper($customer->ALAMAT1 ?? '-');

        // ================================================
        // REKENING
        // ================================================
        $rekening = Rekening::where('AKTIF', 1)->first();
        $bank = $rekening->BANK ?? '-';
        $norek = $rekening->NOREK ?? '-';
        $namaRek = $rekening->NAMA ?? '-';

        // ================================================
        // TOTAL
        // ================================================
        $subtotal = (float) ($master->SUBTOTAL ?? 0);
        $ndisc = (float) ($master->NDISC ?? 0);
        $dpp = (float) ($master->DPP ?? 0);
        $nppn = (float) ($master->NPPN ?? 0);
        $grand = (float) ($master->GRAND ?? 0);
        $dibayar = (float) ($master->BAYAR ?? 0);
        $saldo = (float) ($master->PIUTANG ?? 0);

        $lines = [];

        // ================================================
        // HEADER - STYLING SAMA SEPERTI EXPEDISI
        // ================================================
        $half = intdiv($LINE_WIDTH, 2);
        $judul1 = 'INVOICE';
        $judul2 = 'PT.LINTAS ANUGERAH SEJATI';
        $leftText = $BOLD_ON . $DW_ON . $judul1 . $DW_OFF . $BOLD_OFF;
        $rightText = $BOLD_ON . $DW_ON . $judul2 . $DW_OFF . $BOLD_OFF;
        $leftPad = str_repeat(' ', max(0, $half - strlen($judul1) * 2));
        $rightPad = str_repeat(' ', max(0, $half - strlen($judul2) * 2));
        $lines[] = $leftText . $leftPad . $rightPad . $rightText;
        $lines[] = str_repeat('=', $LINE_WIDTH);

        // ================================================
        // INFO 2 KOLOM - 130 KARAKTER = 65 + 65
        // ================================================
        $lines[] = sprintf("%-65s%65s", 'NOMOR  : ' . $master->INVOICE, 'TANGGAL : ' . date('d-m-Y', strtotime($master->TGLINVOICE)));
        $lines[] = sprintf("%-65s%65s", 'KEPADA : ' . substr($kepada, 0, 55), !empty($master->TGLJT) ? 'TGL JT : ' . date('d-m-Y', strtotime($master->TGLJT)) : '');
        $lines[] = sprintf("%-65s%65s", 'UP     : ' . substr($up, 0, 55), 'CETAK : ' . now()->format('d-m-Y H:i'));

        // ================================================
        // ALAMAT
        // ================================================
        $alamatWrap = explode("\n", wordwrap($alamat, 60, "\n"));
        foreach ($alamatWrap as $i => $alamatRow) {
            $lines[] = $i === 0 ? 'ALAMAT : ' . $alamatRow : '         ' . $alamatRow;
        }

        // ================================================
        // JUMLAH SJ
        // ================================================
        $lines[] = '';
        $lines[] = 'JUMLAH SJ : ' . $rows->count();
        $lines[] = str_repeat('=', $LINE_WIDTH);

        // ================================================
        // LEBAR KOLOM TABEL COOLROOM - NO(4) SJ(14) KETERANGAN(32) JUMLAH(8) UNIT(6) BOXING(8) TOTAL(12) + 6 spasi = 90
        // ================================================
        $fmt = "%-4s %-14s %-32s %-8s %-6s %-8s %12s";

        // ================================================
        // TABLE HEADER
        // ================================================
        $lines[] = sprintf($fmt, 'NO', 'SJ', 'KETERANGAN', 'JUMLAH', 'UNIT', 'BOXING', 'TOTAL');
        $lines[] = str_repeat('-', 90);

        // ================================================
        // DETAIL
        // ================================================
        $no = 1;
        foreach ($rows as $r) {
            $qty = (float) ($r->JUMLAH ?? 0);
            // Buang .000 kalau bulat, tetap tampilkan pecahan kalau ada
            $qtyText = floor($qty) == $qty ? number_format($qty, 0, '.', '') : rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.');
            // BOXING: 1 = BOX, 0/kosong = -
            $isBoxing = (int) ($r->BOXING ?? 0) === 1;
            $boxingText = $isBoxing ? 'BOX' : '-';
            $lines[] = sprintf($fmt, $no, substr($r->NOSJ ?? '-', 0, 14), substr($r->KETERANGAN ?? '-', 0, 32), $qtyText, $r->UNIT ?? '', $boxingText, number_format($r->TOTAL ?? 0, 0, ',', '.'));
            $no++;
        }

        // ================================================
        // GARIS SEPARATOR
        // ================================================
        $lines[] = str_repeat('-', 90);

        // ================================================
        // REKENING + TOTAL - LEBAR HEADER 130
        // ================================================
        $lines[] = sprintf("%-65s%65s", 'BANK   : ' . $bank, 'SUB TOTAL : ' . number_format($subtotal, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", 'NO REK : ' . $norek, 'DISKON : ' . number_format($ndisc, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", 'A/N    : ' . $namaRek, 'DPP : ' . number_format($dpp, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", '', 'PPN : ' . number_format($nppn, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", '', 'GRAND TOTAL : ' . number_format($grand, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", '', 'DIBAYAR : ' . number_format($dibayar, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", '', 'SALDO : ' . number_format($saldo, 0, ',', '.'));

        // ================================================
        // FOOTER
        // ================================================
        $lines[] = '';
        $lines[] = '';
        $lines[] = str_pad('PENERIMA', $half) . str_pad('MENGETAHUI', $half);
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';
        $lines[] = str_pad('(......................)', $half) . str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', $half);

        // ================================================
        // ISI SISA KERTAS SAMPAI 30 BARIS
        // ================================================
        if (count($lines) < $LINES_PER_PAGE) {
            $lines = array_pad($lines, $LINES_PER_PAGE, '');
        }

        // ================================================
        // TAMBAH MARGIN KIRI
        // ================================================
        $marginStr = str_repeat(' ', $LEFT_MARGIN);
        $lines = array_map(fn($l) => $marginStr . $l, $lines);

        // ================================================
        // OUTPUT - KONVERSI DULU BARU ESC/P WRAPPER
        // ================================================
        $text = implode("\r\n", $lines);
        $text = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', $text);
        if ($text === false) {
            $text = implode("\r\n", $lines);
        }

        // ================================================
        // CONDENSED MODE
        // ================================================
        $text = "\x1B\x0F" . $text . "\x12";

        return response()->json([
            'text' => $text
        ]);
    }

    private function generateInvoiceCoolroom(): string{
        $tahun = now()->format('Y');
        $last = Coolroom::where('INVOICE', 'like', "FCO{$tahun}%")
            ->orderBy('INVOICE', 'desc')
            ->lockForUpdate()
            ->first();
        $lastNo = $last
            ? intval(substr($last->INVOICE, -6))
            : 0;
        return 'FCO' . $tahun . str_pad($lastNo + 1, 6, '0', STR_PAD_LEFT);
    }
}

// ### Versi New 3-9-2026
// public function printInvoiceCoolroom($invoiceNo)
//     {
//         $rows = Coolroom::where('INVOICE', $invoiceNo)->orderBy('NOSJ')->get();
//         if ($rows->isEmpty()) abort(404);

//         $master = $rows->first();

//         // ====== ANGKA YANG PERLU DITES DI KANTOR ======
//         $LINE_WIDTH     = 90;  // lebar karakter per baris (tetep sama kayak sebelumnya)
//         $LEFT_MARGIN    = 3;   // spasi kosong di kiri biar gak kena lubang kertas
//         $LINES_PER_PAGE = 30;  // total baris yg muat di 1 lembar (~14cm), buat isi kertas full
//         // ================================================

//         // CUSTOMER
//         $customer = Mcustomer::where('CUSTOMER', $master->CUSTOMER_KODE)->first();
//         $kepada = strtoupper($customer->NAMACUST ?? '-');
//         $up     = strtoupper($customer->KONTAK ?? '-');
//         $alamat = strtoupper($customer->ALAMAT1 ?? '-');

//         // REKENING
//         $rekening = Rekening::where('AKTIF', 1)->first();
//         $bank    = $rekening->BANK ?? '-';
//         $norek   = $rekening->NOREK ?? '-';
//         $namaRek = $rekening->NAMA ?? '-';

//         // TOTAL
//         $subtotal = (float) ($master->SUBTOTAL ?? 0);
//         $ndisc    = (float) ($master->NDISC ?? 0);
//         $dpp      = (float) ($master->DPP ?? 0);
//         $nppn     = (float) ($master->NPPN ?? 0);
//         $grand    = (float) ($master->GRAND ?? 0);
//         $dibayar  = (float) ($master->BAYAR ?? 0);
//         $saldo    = (float) ($master->PIUTANG ?? 0);

//         $lines = [];

//         // HEADER
//         $lines[] = str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', $LINE_WIDTH, ' ', STR_PAD_BOTH);
//         $lines[] = str_pad('COLD CHAIN DISTRIBUTION & STORAGE', $LINE_WIDTH, ' ', STR_PAD_BOTH);
//         $lines[] = str_pad('Jl. Raya Sempidi No.9 Badung - Bali', $LINE_WIDTH, ' ', STR_PAD_BOTH);
//         $lines[] = str_pad('Telp. (0361) 8947610', $LINE_WIDTH, ' ', STR_PAD_BOTH);
//         $lines[] = str_repeat('=', $LINE_WIDTH);
//         $lines[] = str_pad('INVOICE COOLROOM', $LINE_WIDTH, ' ', STR_PAD_BOTH);
//         $lines[] = str_repeat('=', $LINE_WIDTH);

//         // INFO 2 KOLOM
//         $lines[] = sprintf(
//             "%-45s %-45s",
//             'NOMOR  : ' . $master->INVOICE,
//             'TANGGAL : ' . date('d-m-Y', strtotime($master->TGLINVOICE))
//         );
//         $lines[] = sprintf(
//             "%-45s %-45s",
//             'KEPADA : ' . substr($kepada, 0, 33),
//             !empty($master->TGLJT) ? 'TGL JT : ' . date('d-m-Y', strtotime($master->TGLJT)) : ''
//         );
//         $lines[] = sprintf(
//             "%-45s %-45s",
//             'UP     : ' . substr($up, 0, 33),
//             'CETAK : ' . now()->format('d-m-Y H:i')
//         );

//         // ALAMAT (multiline)
//         $alamatWrap = explode("\n", wordwrap($alamat, 60, "\n"));
//         foreach ($alamatWrap as $i => $alamatRow) {
//             $lines[] = $i === 0 ? 'ALAMAT : ' . $alamatRow : '         ' . $alamatRow;
//         }
//         $lines[] = '';
//         $lines[] = 'JUMLAH SJ : ' . $rows->count();
//         $lines[] = str_repeat('=', $LINE_WIDTH);

//         // ---------- LEBAR KOLOM TABEL ----------
//         // NO(4) SJ(14) KETERANGAN(32) JUMLAH(8) UNIT(6) BOXING(8) TOTAL(12) + 6 spasi = 90
//         $fmt = "%-4s %-14s %-32s %-8s %-6s %-8s %12s";

//         // TABLE HEADER
//         $lines[] = sprintf($fmt, 'NO', 'SJ', 'KETERANGAN', 'JUMLAH', 'UNIT', 'BOXING', 'TOTAL');
//         $lines[] = str_repeat('-', $LINE_WIDTH);

//         // DETAIL
//         $no = 1;
//         foreach ($rows as $r) {
//             $qty = (float) ($r->JUMLAH ?? 0);
//             // Buang desimal .000 kalau nilainya bulat, tapi tetep tampilkan
//             // pecahan kalau memang ada (mis. 5.250 -> "5.25")
//             $qtyText = floor($qty) == $qty
//                 ? number_format($qty, 0, '.', '')
//                 : rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.');

//             // BOXING: 1 = dikemas box, 0/kosong = tidak
//             $isBoxing = (int) ($r->BOXING ?? 0) === 1;
//             $boxingText = $isBoxing ? 'BOX' : '-';

//             $lines[] = sprintf(
//                 $fmt,
//                 $no,
//                 substr($r->NOSJ ?? '-', 0, 14),
//                 substr($r->KETERANGAN ?? '-', 0, 32),
//                 $qtyText,
//                 $r->UNIT ?? '',
//                 $boxingText,
//                 number_format($r->TOTAL ?? 0, 0, ',', '.')
//             );
//             $no++;
//         }
//         $lines[] = str_repeat('-', $LINE_WIDTH);

//         // REKENING + TOTAL
//         $lines[] = sprintf("%-45s %-45s", 'BANK   : ' . $bank, 'SUB TOTAL : ' . number_format($subtotal, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", 'NO REK : ' . $norek, 'DISKON : ' . number_format($ndisc, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", 'A/N    : ' . $namaRek, 'DPP : ' . number_format($dpp, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", '', 'PPN : ' . number_format($nppn, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", '', 'GRAND TOTAL : ' . number_format($grand, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", '', 'DIBAYAR : ' . number_format($dibayar, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", '', 'SALDO : ' . number_format($saldo, 0, ',', '.'));

//         // FOOTER
//         $lines[] = '';
//         $lines[] = '';
//         $lines[] = str_pad('PENERIMA', 45) . str_pad('MENGETAHUI', 45);
//         $lines[] = '';
//         $lines[] = '';
//         $lines[] = '';
//         $lines[] = str_pad('(......................)', 45) . str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', 45);

//         // ====================== ISI SISA KERTAS SAMPAI PENUH ======================
//         if (count($lines) < $LINES_PER_PAGE) {
//             $lines = array_pad($lines, $LINES_PER_PAGE, '');
//         }

//         // ====================== TAMBAH MARGIN KIRI ======================
//         $marginStr = str_repeat(' ', $LEFT_MARGIN);
//         $lines = array_map(fn($l) => $marginStr . $l, $lines);

//         // OUTPUT - Konversi dulu, baru bungkus ESC/P
//         $text = implode("\r\n", $lines);
//         $text = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', $text);
//         if ($text === false) {
//             $text = implode("\r\n", $lines);
//         }

//         // ESC/P wrapper setelah konversi
//         $text = "\x1B\x0F" . $text . "\x12";

//         return response()->json([
//             'text' => $text
//         ]);
//     }

// ### Versi New
// public function printInvoiceCoolroom($invoiceNo){
//         $rows = Coolroom::where('INVOICE', $invoiceNo)->orderBy('NOSJ')->get();
//         if ($rows->isEmpty()) abort(404);

//         $master = $rows->first();

//         // CUSTOMER
//         $customer = Mcustomer::where('CUSTOMER', $master->CUSTOMER_KODE)->first();
//         $kepada = strtoupper($customer->NAMACUST ?? '-');
//         $up     = strtoupper($customer->KONTAK ?? '-');
//         $alamat = strtoupper($customer->ALAMAT1 ?? '-');

//         // REKENING
//         $rekening = Rekening::where('AKTIF', 1)->first();
//         $bank    = $rekening->BANK ?? '-';
//         $norek   = $rekening->NOREK ?? '-';
//         $namaRek = $rekening->NAMA ?? '-';

//         // TOTAL
//         $subtotal = (float) ($master->SUBTOTAL ?? 0);
//         $ndisc    = (float) ($master->NDISC ?? 0);
//         $dpp      = (float) ($master->DPP ?? 0);
//         $nppn     = (float) ($master->NPPN ?? 0);
//         $grand    = (float) ($master->GRAND ?? 0);
//         $dibayar  = (float) ($master->BAYAR ?? 0);
//         $saldo    = (float) ($master->PIUTANG ?? 0);

//         $lines = [];

//         // HEADER
//         $lines[] = str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', 90, ' ', STR_PAD_BOTH);
//         $lines[] = str_pad('COLD CHAIN DISTRIBUTION & STORAGE', 90, ' ', STR_PAD_BOTH);
//         $lines[] = str_pad('Jl. Raya Sempidi No.9 Badung - Bali', 90, ' ', STR_PAD_BOTH);
//         $lines[] = str_pad('Telp. (0361) 8947610', 90, ' ', STR_PAD_BOTH);
//         $lines[] = str_repeat('=', 90);
//         $lines[] = str_pad('INVOICE COOLROOM', 90, ' ', STR_PAD_BOTH);
//         $lines[] = str_repeat('=', 90);

//         // INFO 2 KOLOM (diperlebar jadi 45)
//         $lines[] = sprintf(
//             "%-45s %-45s",
//             'NOMOR  : ' . $master->INVOICE,
//             'TANGGAL : ' . date('d-m-Y', strtotime($master->TGLINVOICE))
//         );
//         $lines[] = sprintf(
//             "%-45s %-45s",
//             'KEPADA : ' . substr($kepada, 0, 33),
//             !empty($master->TGLJT) ? 'TGL JT : ' . date('d-m-Y', strtotime($master->TGLJT)) : ''
//         );
//         $lines[] = sprintf(
//             "%-45s %-45s",
//             'UP     : ' . substr($up, 0, 33),
//             'CETAK : ' . now()->format('d-m-Y H:i')
//         );

//         // ALAMAT (multiline) - wordwrap diperkecil jadi 60
//         $alamatWrap = explode("\n", wordwrap($alamat, 60, "\n"));
//         foreach ($alamatWrap as $i => $alamatRow) {
//             $lines[] = $i === 0 ? 'ALAMAT : ' . $alamatRow : '         ' . $alamatRow;
//         }
//         $lines[] = '';
//         $lines[] = 'JUMLAH SJ : ' . $rows->count();
//         $lines[] = str_repeat('=', 90);

//         // TABLE HEADER (diperlebar)
//         $lines[] = sprintf("%-4s %-16s %-36s %-10s %-8s %12s", 'NO', 'SJ', 'KETERANGAN', 'JUMLAH', 'UNIT', 'TOTAL');
//         $lines[] = str_repeat('-', 90);

//         // DETAIL (diperlebar)
//         $no = 1;
//         foreach ($rows as $r) {
//             $qty = (float) ($r->JUMLAH ?? 0);
//             $qtyText = floor($qty) == $qty ? number_format($qty, 0) : rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.');
//             $lines[] = sprintf(
//                 "%-4s %-16s %-36s %-10s %-8s %12s",
//                 $no,
//                 substr($r->NOSJ ?? '-', 0, 16),
//                 substr($r->KETERANGAN ?? '-', 0, 36),
//                 $qtyText,
//                 $r->UNIT ?? '',
//                 number_format($r->TOTAL ?? 0, 0, ',', '.')
//             );
//             $no++;
//         }
//         $lines[] = str_repeat('-', 90);

//         // REKENING + TOTAL (2 kolom diperlebar)
//         $lines[] = sprintf("%-45s %-45s", 'BANK   : ' . $bank, 'SUB TOTAL : ' . number_format($subtotal, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", 'NO REK : ' . $norek, 'DISKON : ' . number_format($ndisc, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", 'A/N    : ' . $namaRek, 'DPP : ' . number_format($dpp, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", '', 'PPN : ' . number_format($nppn, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", '', 'GRAND TOTAL : ' . number_format($grand, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", '', 'DIBAYAR : ' . number_format($dibayar, 0, ',', '.'));
//         $lines[] = sprintf("%-45s %-45s", '', 'SALDO : ' . number_format($saldo, 0, ',', '.'));

//         // FOOTER
//         $lines[] = '';
//         $lines[] = '';
//         $lines[] = str_pad('PENERIMA', 45) . str_pad('MENGETAHUI', 45);
//         $lines[] = '';
//         $lines[] = '';
//         $lines[] = '';
//         $lines[] = str_pad('(......................)', 45) . str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', 45);

//         // OUTPUT - Konversi dulu, baru bungkus ESC/P
//         $text = implode("\r\n", $lines);
//         $text = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', $text);
//         if ($text === false) {
//             $text = implode("\r\n", $lines);
//         }

//         // ESC/P wrapper setelah konversi
//         $text = "\x1B\x0F" . $text . "\x12";

//         return response()->json([
//             'text' => $text
//         ]);
//     }
// ###Versi Old
// public function printInvoiceCoolroom($invoiceNo){
//         $rows = Coolroom::where('INVOICE', $invoiceNo)->orderBy('NOSJ')->get();
//         if ($rows->isEmpty()) abort(404);

//         $master = $rows->first();

//         // CUSTOMER
//         $customer = Mcustomer::where('CUSTOMER', $master->CUSTOMER_KODE)->first();
//         $kepada = strtoupper($customer->NAMACUST ?? '-');
//         $up     = strtoupper($customer->KONTAK ?? '-');
//         $alamat = strtoupper($customer->ALAMAT1 ?? '-');

//         // REKENING
//         $rekening = Rekening::where('AKTIF', 1)->first();
//         $bank    = $rekening->BANK ?? '-';
//         $norek   = $rekening->NOREK ?? '-';
//         $namaRek = $rekening->NAMA ?? '-';

//         // TOTAL
//         $subtotal = (float) ($master->SUBTOTAL ?? 0);
//         $ndisc    = (float) ($master->NDISC ?? 0);
//         $dpp      = (float) ($master->DPP ?? 0);
//         $nppn     = (float) ($master->NPPN ?? 0);
//         $grand    = (float) ($master->GRAND ?? 0);
//         $dibayar  = (float) ($master->BAYAR ?? 0);
//         $saldo    = (float) ($master->PIUTANG ?? 0);

//         $lines = [];

//         // HEADER
//         $lines[] = str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', 80, ' ', STR_PAD_BOTH);
//         $lines[] = str_pad('COLD CHAIN DISTRIBUTION & STORAGE', 80, ' ', STR_PAD_BOTH);
//         $lines[] = str_pad('Jl. Raya Sempidi No.9 Badung - Bali', 80, ' ', STR_PAD_BOTH);
//         $lines[] = str_pad('Telp. (0361) 8947610', 80, ' ', STR_PAD_BOTH);
//         $lines[] = str_repeat('=', 80);
//         $lines[] = str_pad('INVOICE COOLROOM', 80, ' ', STR_PAD_BOTH);
//         $lines[] = str_repeat('=', 80);

//         // INFO 2 KOLOM
//         $lines[] = sprintf(
//             "%-40s %-40s",
//             'NOMOR  : ' . $master->INVOICE,
//             'TANGGAL : ' . date('d-m-Y', strtotime($master->TGLINVOICE))
//         );
//         $lines[] = sprintf(
//             "%-40s %-40s",
//             'KEPADA : ' . substr($kepada, 0, 28),
//             !empty($master->TGLJT) ? 'TGL JT : ' . date('d-m-Y', strtotime($master->TGLJT)) : ''
//         );
//         $lines[] = sprintf(
//             "%-40s %-40s",
//             'UP     : ' . substr($up, 0, 28),
//             'CETAK : ' . now()->format('d-m-Y H:i')
//         );

//         // ALAMAT (multiline)
//         $alamatWrap = explode("\n", wordwrap($alamat, 65, "\n"));
//         foreach ($alamatWrap as $i => $alamatRow) {
//             $lines[] = $i === 0 ? 'ALAMAT : ' . $alamatRow : '         ' . $alamatRow;
//         }
//         $lines[] = '';
//         $lines[] = 'JUMLAH SJ : ' . $rows->count();
//         $lines[] = str_repeat('=', 80);

//         // TABLE HEADER
//         $lines[] = sprintf("%-4s %-14s %-30s %-10s %-8s %10s", 'NO', 'SJ', 'KETERANGAN', 'JUMLAH', 'UNIT', 'TOTAL');
//         $lines[] = str_repeat('-', 80);

//         // DETAIL
//         $no = 1;
//         foreach ($rows as $r) {
//             $qty = (float) ($r->JUMLAH ?? 0);
//             $qtyText = floor($qty) == $qty ? number_format($qty, 0) : rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.');
//             $lines[] = sprintf(
//                 "%-4s %-14s %-30s %-10s %-8s %10s",
//                 $no,
//                 substr($r->NOSJ ?? '-', 0, 14),
//                 substr($r->KETERANGAN ?? '-', 0, 30),
//                 $qtyText,
//                 $r->UNIT ?? '',
//                 number_format($r->TOTAL ?? 0, 0, ',', '.')
//             );
//             $no++;
//         }
//         $lines[] = str_repeat('-', 80);

//         // REKENING + TOTAL (2 kolom)
//         $lines[] = sprintf("%-40s %-40s", 'BANK   : ' . $bank, 'SUB TOTAL : ' . number_format($subtotal, 0, ',', '.'));
//         $lines[] = sprintf("%-40s %-40s", 'NO REK : ' . $norek, 'DISKON : ' . number_format($ndisc, 0, ',', '.'));
//         $lines[] = sprintf("%-40s %-40s", 'A/N    : ' . $namaRek, 'DPP : ' . number_format($dpp, 0, ',', '.'));
//         $lines[] = sprintf("%-40s %-40s", '', 'PPN : ' . number_format($nppn, 0, ',', '.'));
//         $lines[] = sprintf("%-40s %-40s", '', 'GRAND TOTAL : ' . number_format($grand, 0, ',', '.'));
//         $lines[] = sprintf("%-40s %-40s", '', 'DIBAYAR : ' . number_format($dibayar, 0, ',', '.'));
//         $lines[] = sprintf("%-40s %-40s", '', 'SALDO : ' . number_format($saldo, 0, ',', '.'));

//         // FOOTER
//         $lines[] = '';
//         $lines[] = '';
//         $lines[] = str_pad('PENERIMA', 40) . str_pad('MENGETAHUI', 40);
//         $lines[] = '';
//         $lines[] = '';
//         $lines[] = '';
//         $lines[] = str_pad('(......................)', 40) . str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', 40);

//         // OUTPUT
//         $text = implode("\r\n", $lines);
//         $text = iconv('UTF-8', 'CP437//TRANSLIT', $text);

//         return response()->json(['text' => $text]);
//     }
