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
use App\Models\Kwitansi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Exception;

class ExpedisiKwitansiController extends Controller
{
    public function index()
    {
        return view('expedisiKwitansi.expedisi-kwitansi');
    }

    public function getDataKwitansi(Request $request)
    {
        $query = Expedisi::select([
                'INVOICE',
                'TGLINVOICE',
                'CUSTOMER',
                'GRAND',
                'PIUTANG',
                'kwt'
            ])
            ->where('JENIS', 'EKS')
            // hanya yang sudah invoice
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '!=', '')
            // hanya master GB / single SJ
            ->where('GRAND', '>', 0)
            // invoice sama berdempetan
            ->orderBy('INVOICE')
            // master di atas
            ->orderByDesc('GRAND');
        // ==========================================
        // FILTER STATUS KWITANSI
        // ==========================================
        if ($request->status_kwt == 'belum') {
            $query->where(function ($q) {
                $q->whereNull('kwt')
                    ->orWhere('kwt', '');
            });
        }
        if ($request->status_kwt == 'sudah') {
            $query->whereNotNull('kwt')
                ->where('kwt', '!=', '');
        }
        return DataTables::of($query)
            ->addIndexColumn()
            // ==========================================
            // FORMAT TANGGAL
            // ==========================================
            ->editColumn('TGLINVOICE', function ($row) {
                return $row->TGLINVOICE
                    ? \Carbon\Carbon::parse($row->TGLINVOICE)->format('d-m-Y') : '-';
            })
            // ==========================================
            // FORMAT GRAND
            // ==========================================
            ->editColumn('GRAND', function ($row) {
                return number_format(
                    $row->GRAND ?? 0,
                    0,
                    ',',
                    '.'
                );
            })
            // ==========================================
            // FORMAT PIUTANG
            // ==========================================
            ->editColumn('PIUTANG', function ($row) {
                return number_format(
                    $row->PIUTANG ?? 0,
                    0,
                    ',',
                    '.'
                );
            })
            // ==========================================
            // ACTION
            // ==========================================
            ->addColumn('action', function ($row) use ($request) {
                // ==============================
                // BELUM KWITANSI
                // ==============================
                if ($request->status_kwt == 'belum') {
                    return '
                        <button
                            class="btn btn-sm btn-primary btn-proses-kwt"
                            data-invoice="'.$row->INVOICE.'">
                            Proses
                        </button>
                    ';
                }
                // ==============================
                // SUDAH KWITANSI
                // ==============================
                return '
                    <div class="d-flex flex-wrap gap-1">
                        <button class="btn btn-sm btn-primary btn-print-kwt" data-kwitansi="'.$row->kwt.'">Print</button>
                        <button class="btn btn-sm btn-success btn-cetak-kwt" data-kwitansi="'.$row->kwt.'">Pdf</button>
                        <button class="btn btn-sm btn-danger btn-delete-kwt" data-kwitansi="'.$row->kwt.'">Delete</button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function prosesKwitansi(Request $request)
    {
        try {
            $invoice = $request->invoice;
            $kwt = DB::transaction(function () use ($invoice) {
                // =====================================
                // VALIDASI
                // =====================================
                if (!$invoice) {
                    throw new \Exception(
                        'Invoice tidak ditemukan'
                    );
                }
                // =====================================
                // AMBIL DATA EXPEDISI
                // =====================================
                $rows = Expedisi::where('INVOICE', $invoice)
                    ->lockForUpdate()
                    ->get();
                if ($rows->isEmpty()) {

                    throw new \Exception(
                        'Data invoice tidak ditemukan'
                    );

                }
                // =====================================
                // CEK SUDAH KWITANSI?
                // =====================================
                $alreadyKwt = $rows->first(function ($row) {
                    return !empty($row->kwt);
                });
                if ($alreadyKwt) {
                    throw new \Exception(
                        'Invoice sudah memiliki kwitansi'
                    );
                }
                // =====================================
                // GENERATE NOMOR KWITANSI
                // =====================================
                $kwt = $this->generateKW();
                // =====================================
                // MASTER
                // =====================================
                $master = $rows->firstWhere('GRAND', '>', 0)
                        ?? $rows->first();
                if (!$master) {
                    throw new \Exception(
                        'Master invoice tidak ditemukan'
                    );
                }
                // =====================================
                // AMBIL NAMA CUSTOMER
                // =====================================
                $mcustomer = Mcustomer::where(
                    'KODE_CUS',
                    $master->CUSTOMER_KODE
                )->first();
                $namaCust = $mcustomer->NAMACUST
                    ?? $master->CUSTOMER;
                // =====================================
                // UPDATE EXPEDISI
                // =====================================
                foreach ($rows as $row) {
                    $row->kwt   = $kwt;
                    $row->TGLKW = now();
                    $row->save();
                }
                // =====================================
                // INSERT KWITANSI
                // =====================================
                Kwitansi::create([
                    'NOKWT' => $kwt,
                    'TGL' => now(),
                    'FDOK_TRANS' => $invoice,
                    'TGL_TRANS' => $master->TGLINVOICE,
                    'CUSTOMER' => $master->CUSTOMER,
                    'NOSJ' => $rows
                        ->pluck('NOSJ')
                        ->implode(','),
                    'FKETERANG' =>
                        'PENJUALAN EXPEDISI PADA '
                        .$namaCust
                        .', INVOICE : '
                        .$invoice,
                    'FNAMA' => $namaCust,
                    'FNIL_DOK' => $master->GRAND ?? 0,
                    'USERINPUT' => auth()->user()->user_id,
                    'TOTAL' => $master->TOTAL ?? 0,
                    'PPN' => $master->PPN ?? 0,
                    'DISC' => $master->DISC ?? 0,
                    'NDISC' => $master->NDISC ?? 0,
                    'JENIS' => $master->JENIS ?? 'EKS',
                ]);
                return $kwt;
            });
            return response()->json([
                'status'  => true,
                'message' => 'Kwitansi berhasil diproses',
                'nokwt'   => $kwt,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteKwitansi(Request $request)
    {
        try {
            $kwt = $request->kwt;
            DB::transaction(function () use ($kwt) {
                // =====================================
                // VALIDASI
                // =====================================
                if (!$kwt) {
                    throw new \Exception(
                        'Nomor kwitansi tidak ditemukan'
                    );
                }
                // =====================================
                // AMBIL DATA
                // =====================================
                $rows = Expedisi::where('kwt', $kwt)
                    ->lockForUpdate()
                    ->get();
                if ($rows->isEmpty()) {
                    throw new \Exception(
                        'Data kwitansi tidak ditemukan'
                    );
                }

                // =====================================
                // REVERSE KWITANSI EXPEDISI
                // =====================================
                foreach ($rows as $row) {
                    $row->kwt   = null;
                    // reset tanggal kwitansi
                    $row->TGLKW = null;
                    $row->save();
                }

                // =====================================
                // DELETE TABEL KWITANSI
                // =====================================
                Kwitansi::where(
                    'NOKWT',
                    $kwt
                )->delete();
            });

            return response()->json([
                'status'  => true,
                'message' => 'Kwitansi berhasil dibatalkan'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function printKwitansiText($kwitansi)
    {
        // ==========================================
        // AMBIL DATA KWITANSI
        // ==========================================
        $master = Expedisi::where('kwt', $kwitansi)->where('GRAND', '>', 0)->firstOrFail();
        $details = Expedisi::where('kwt', $kwitansi)->orderBy('NOSJ')->get();
        $invoice = $master->INVOICE;

        // Sama seperti versi PDF
        $arh = Arh::where('NOFAKTUR', $invoice)->first();
        $signature = Signature::orderByDesc('id')->first();

        // ==========================================
        // SETTING PRINTER
        // ==========================================
        $LINE_WIDTH = 130;
        $LINES_PER_PAGE = 30;
        $LEFT_MARGIN = 3;
        $half = intdiv($LINE_WIDTH, 2);

        // ==========================================
        // ESC/P
        // ==========================================
        $ESC = "\x1B";
        $BOLD_ON = $ESC . 'E';
        $BOLD_OFF = $ESC . 'F';
        $DW_ON = $ESC . 'W' . "\x01";
        $DW_OFF = $ESC . 'W' . "\x00";

        // ==========================================
        // HELPER
        // ==========================================
        $clean = function ($value) {
            return trim(preg_replace('/[\r\n]+/', ' ', (string) $value));
        };

        $fit = function ($text, $width) use ($clean) {
            $text = $clean($text);
            if (strlen($text) > $width) {
                return substr($text, 0, $width);
            }
            return $text;
        };

        $money = function ($value) {
            return number_format((float) ($value ?? 0), 0, ',', '.');
        };

        // ==========================================
        // DATA
        // ==========================================
        $customer = $clean($master->CUSTOMER ?? '-');
        $grand = (float) ($master->GRAND ?? 0);
        $tanggal = !empty($master->TGLKW) ? date('d-m-Y', strtotime($master->TGLKW)) : '-';
        $nomorSJ = $details->pluck('NOSJ')->filter(fn($v) => trim((string) $v) !== '')->unique()->implode(', ');
        $signatureName = $clean($signature->nama ?? '');

        // ==========================================
        // TERBILANG
        // ==========================================
        $terbilangText = ucwords(terbilang($grand)) . ' Rupiah';

        // ==========================================
        // ARRAY BARIS
        // ==========================================
        $lines = [];

        // ==========================================
        // HEADER
        // ==========================================
        $judul = 'PT. LINTAS MITRA ANUGERAH SEJATI';
        $lines[] = $BOLD_ON . $DW_ON . str_pad($judul, intdiv($LINE_WIDTH, 2), ' ', STR_PAD_BOTH) . $DW_OFF . $BOLD_OFF;
        $lines[] = str_pad('COLD CHAIN DISTRIBUTION & STORAGE', $LINE_WIDTH, ' ', STR_PAD_BOTH);
        $lines[] = str_repeat('=', $LINE_WIDTH);

        // ==========================================
        // ALAMAT HEADER
        // ==========================================
        $lines[] = sprintf("%-65s%65s", 'Jl. Raya Sempidi No.9 Badung - Bali', 'BizPark Commercial Estate');
        $lines[] = sprintf("%-65s%65s", 'Telp/Fax : (0361) 8947610', 'Jl. Sultan Agung KM 28,5 Bekasi');
        $lines[] = sprintf("%-65s%65s", 'Jl. Bija Taki IV, No.9 Denpasar - Bali', 'www.lintasmitralogistik.com');
        $lines[] = str_repeat('-', $LINE_WIDTH);

        // ==========================================
        // NOMOR KWITANSI
        // ==========================================
        $lines[] = $BOLD_ON . 'KWITANSI NO : ' . $master->kwt . $BOLD_OFF;

        // ==========================================
        // SUDAH TERIMA DARI
        // ==========================================
        $lines[] = 'SUDAH TERIMA DARI : ' . $fit($customer, 108);

        // ==========================================
        // BANYAKNYA UANG
        // ==========================================
        $lines[] = 'BANYAKNYA UANG    : ' . $fit($terbilangText, 108);

        // ==========================================
        // UNTUK PEMBAYARAN
        // ==========================================
        $lines[] = 'UNTUK PEMBAYARAN  :';

        // Nomor SJ bisa banyak
        $sjPrefix = 'SJ : ';
        $sjWrapped = wordwrap($nomorSJ !== '' ? $nomorSJ : '-', 118, "\n", true);
        $sjRows = explode("\n", $sjWrapped);
        foreach ($sjRows as $i => $row) {
            if ($i === 0) {
                $lines[] = '  ' . $sjPrefix . $row;
            } else {
                $lines[] = '       ' . $row;
            }
        }

        $lines[] = '       INVOICE : ' . $invoice;

        // ==========================================
        // PEMISAH
        // ==========================================
        $lines[] = str_repeat('-', $LINE_WIDTH);

        // ==========================================
        // PEMBAYARAN
        // ==========================================
        $lines[] = 'Untuk pembayaran mohon di transfer ke rek resmi';
        $lines[] = $BOLD_ON . 'A/n. PT. Lintas Mitra Anugerah Sejati' . $BOLD_OFF;
        $lines[] = 'No.rek BCA 6115352010';

        // ==========================================
        // CEK / GIRO + TANGGAL
        // ==========================================
        $cek = 'CEK/GIRO NO. _____________________________';
        $tgl = 'Denpasar ' . $tanggal;
        $lines[] = sprintf("%-65s%65s", $cek, $tgl);

        // ==========================================
        // JUMLAH
        // ==========================================
        $lines[] = '';
        $lines[] = $BOLD_ON . 'JUMLAH' . $BOLD_OFF . str_repeat(' ', 20) . 'RP. ' . $money($grand);

        // ==========================================
        // TANDA TANGAN
        // ==========================================
        $lines[] = '';
        $lines[] = sprintf("%-65s%65s", 'PENERIMA', 'MENGETAHUI');
        $lines[] = '';
        $lines[] = '';
        $lines[] = sprintf("%-65s%65s", '(.................................)', $signatureName);

        // ==========================================
        // PADDING SAMPAI TEPAT 30 BARIS
        // ==========================================
        if (count($lines) < $LINES_PER_PAGE) {
            $padding = $LINES_PER_PAGE - count($lines);
            $lines = array_merge($lines, array_fill(0, $padding, ''));
        }

        // Kalau ternyata melebihi 30 baris, jangan dipotong diam-diam.
        if (count($lines) > $LINES_PER_PAGE) {
            // Untuk sementara biarkan semua baris.
            // Nanti kita bisa tuning layout kalau kasus nomor SJ sangat panjang.
        }

        // ==========================================
        // LEFT MARGIN
        // ==========================================
        $margin = str_repeat(' ', $LEFT_MARGIN);
        $lines = array_map(fn($line) => $margin . $line, $lines);

        // ==========================================
        // GABUNG TEXT
        // ==========================================
        $text = implode("\r\n", $lines);

        // ==========================================
        // CP437
        // ==========================================
        $converted = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }

        // ==========================================
        // CONDENSED MODE - SAMA DENGAN INVOICE YANG SUDAH PAS
        // ==========================================
        $text = "\x1B\x0F" . $text . "\x12";

        // ==========================================
        // RETURN KE JAVASCRIPT
        // ==========================================
        return response()->json([
            'text' => $text,
            'lines' => count($lines),
            'invoice' => $invoice,
            'kwitansi' => $kwitansi,
        ]);
    }

    public function pdfInvoiceKwitansi($kwitansi){
        $master = Expedisi::where('kwt', $kwitansi)
            ->where('GRAND', '>', 0)
            ->firstOrFail();
        $details = Expedisi::where('kwt', $kwitansi)
            ->orderBy('NOSJ')
            ->get();
        $invoice = $master->INVOICE;
        $arh = Arh::where('NOFAKTUR', $invoice)
            ->first();

        $signature = Signature::orderByDesc('id')->first();

        $html = view('expedisiKwitansi.expedisi-kwitansi-pdf', compact('master','details','arh','signature'))->render();

        $tempPath = storage_path('app/mpdf-temp');

        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 20,
            'margin_bottom' => 15,
            'margin_left' => 15,
            'margin_right' => 15,
            'tempDir' => $tempPath,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('Invoice-'.$invoice.'.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }

    private function generateKW()
    {
        $year = now()->format('Y');

        $last = Kwitansi::where('NOKWT', 'like', 'KW'.$year.'%')
            ->lockForUpdate()
            ->orderByDesc('NOKWT')
            ->value('NOKWT');

        if (!$last) {
            return 'KW' . $year . '0000001';
        }

        $number = (int) substr($last, 7);
        $number++;

        return 'KW' . $year . str_pad($number, 7, '0', STR_PAD_LEFT);
    }

    // public function prosesKwitansiDelete(Request $request){
    //     try {

    //         DB::transaction(function () use ($request) {

    //             $invoice = $request->invoice;

    //             $expedisi = Expedisi::where('INVOICE', $invoice)->first();

    //             if (!$expedisi) {
    //                 throw new \Exception('Invoice tidak ditemukan');
    //             }

    //             // $grand = $expedisi->GRAND;

    //             // ===============================
    //             // Reverse ARH
    //             // ===============================
    //             // Arh::where('NOFAKTUR', $invoice)
    //             //     ->update([
    //             //         'BAYAR'       => 0,
    //             //         'SALDO'       => 0,
    //             //         'PIUTANG'     => $grand, // kembali seperti awal
    //             //         'TGLJT'       => null,
    //             //         'USER_UPDATE' => auth()->user()->user_id,
    //             //         'updated_at'  => now()
    //             //     ]);

    //             // ===============================
    //             // Reverse EXPEDISI
    //             // ===============================
    //             Expedisi::where('INVOICE', $invoice)
    //                 ->update([
    //                     'kwt'   => null,
    //                     'TGLKW' => null
    //                 ]);
    //         });

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Kwitansi berhasil dihapus / direverse'
    //         ]);

    //     } catch (\Throwable $e) {

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
