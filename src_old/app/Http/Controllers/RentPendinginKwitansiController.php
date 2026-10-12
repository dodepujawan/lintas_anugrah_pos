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

class RentPendinginKwitansiController extends Controller
{
     public function index()
    {
        return view('rentPendinginKwitansi.rentPendingin-kwt');
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
            ->where('JENIS', 'REN')
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
                            class="btn btn-sm btn-primary btn-proses-kwt-dgn"
                            data-invoice="'.$row->INVOICE.'">
                            Proses
                        </button>
                    ';
                }
                // ==============================
                // SUDAH KWITANSI
                // ==============================
                return '
                    <div class="d-flex gap-1">
                        <button
                            class="btn btn-sm btn-danger btn-delete-kwt-dgn"
                            data-kwitansi="'.$row->kwt.'">
                            Delete
                        </button>
                        <button
                            class="btn btn-sm btn-primary btn-print-kwt-dgn"
                            data-kwitansi="'.$row->kwt.'">
                            Print
                        </button>
                        <button
                            class="btn btn-sm btn-success btn-cetak-kwt-dgn"
                            data-kwitansi="'.$row->kwt.'">
                            PDF
                        </button>
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
                    ->where('JENIS', 'REN')
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
                        'PENYEWAAN MOBIL PENDINGIN PADA '
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
                    'JENIS' => 'REN',
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
                    ->where('JENIS', 'REN')
                    ->lockForUpdate()
                    ->get();
                if ($rows->isEmpty()) {
                    throw new \Exception(
                        'Data kwitansi tidak ditemukan'
                    );
                }
                // =====================================
                // REVERSE KWITANSI
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
        $master = Expedisi::where('kwt', $kwitansi)
            ->where('JENIS', 'REN')
            ->where('GRAND', '>', 0)
            ->firstOrFail();
        $details = Expedisi::where('kwt', $kwitansi)
            ->where('JENIS', 'REN')
            ->orderBy('NOSJ')
            ->get();
        $signature = Signature::orderByDesc('id')->first();

        $lineWidth = 130;
        $linesPerPage = 30;
        $leftMargin = 3;
        $esc = "\x1B";
        $boldOn = $esc . 'E';
        $boldOff = $esc . 'F';
        $doubleWidthOn = $esc . 'W' . "\x01";
        $doubleWidthOff = $esc . 'W' . "\x00";
        $clean = fn($value) => trim(preg_replace('/[\r\n]+/', ' ', (string) $value));
        $fit = function ($value, $width) use ($clean) {
            return substr($clean($value), 0, $width);
        };

        $grand = (float) ($master->GRAND ?? 0);
        $tanggal = !empty($master->TGLKW) ? date('d-m-Y', strtotime($master->TGLKW)) : '-';
        $nomorSj = $details->pluck('NOSJ')
            ->filter(fn($value) => trim((string) $value) !== '')
            ->unique()
            ->implode(', ');
        $signatureName = $clean($signature->nama ?? '');
        $terbilangText = ucwords(terbilang($grand)) . ' Rupiah';

        $lines = [
            $boldOn . $doubleWidthOn . str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', intdiv($lineWidth, 2), ' ', STR_PAD_BOTH) . $doubleWidthOff . $boldOff,
            str_pad('COLD CHAIN DISTRIBUTION & STORAGE', $lineWidth, ' ', STR_PAD_BOTH),
            str_repeat('=', $lineWidth),
            sprintf('%-65s%65s', 'Jl. Raya Sempidi No.9 Badung - Bali', 'BizPark Commercial Estate'),
            sprintf('%-65s%65s', 'Telp/Fax : (0361) 8947610', 'Jl. Sultan Agung KM 28,5 Bekasi'),
            sprintf('%-65s%65s', 'Jl. Bija Taki IV, No.9 Denpasar - Bali', 'www.lintasmitralogistik.com'),
            str_repeat('-', $lineWidth),
            $boldOn . 'KWITANSI NO : ' . $master->kwt . $boldOff,
            'SUDAH TERIMA DARI : ' . $fit($master->CUSTOMER ?? '-', 108),
            'BANYAKNYA UANG    : ' . $fit($terbilangText, 108),
            'UNTUK PEMBAYARAN  :',
        ];

        $sjRows = explode("\n", wordwrap($nomorSj !== '' ? $nomorSj : '-', 118, "\n", true));
        foreach ($sjRows as $index => $row) {
            $lines[] = ($index === 0 ? '  SJ : ' : '       ') . $row;
        }

        $lines[] = '       INVOICE : ' . $master->INVOICE;
        $lines[] = str_repeat('-', $lineWidth);
        $lines[] = 'Untuk pembayaran mohon di transfer ke rek resmi';
        $lines[] = $boldOn . 'A/n. PT. Lintas Mitra Anugerah Sejati' . $boldOff;
        $lines[] = 'No.rek BCA 6115352010';
        $lines[] = sprintf('%-65s%65s', 'CEK/GIRO NO. _____________________________', 'Denpasar ' . $tanggal);
        $lines[] = '';
        $lines[] = $boldOn . 'JUMLAH' . $boldOff . str_repeat(' ', 20) . 'RP. ' . number_format($grand, 0, ',', '.');
        $lines[] = '';
        $lines[] = sprintf('%-65s%65s', 'PENERIMA', 'MENGETAHUI');
        $lines[] = '';
        $lines[] = '';
        $lines[] = sprintf('%-65s%65s', '(.................................)', $signatureName);

        if (count($lines) < $linesPerPage) {
            $lines = array_pad($lines, $linesPerPage, '');
        }

        $margin = str_repeat(' ', $leftMargin);
        $text = implode("\r\n", array_map(fn($line) => $margin . $line, $lines));
        $converted = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
        $text = "\x1B\x0F" . $text . "\x12";

        return response()->json([
            'text' => $text,
            'lines' => count($lines),
            'invoice' => $master->INVOICE,
            'kwitansi' => $kwitansi,
        ]);
    }

    public function pdfInvoiceKwitansi($kwitansi){
        $master = Expedisi::where('kwt', $kwitansi)
            ->where('JENIS', 'REN')
            ->where('GRAND', '>', 0)
            ->firstOrFail();

        $details = Expedisi::where('kwt', $kwitansi)
            ->where('JENIS', 'REN')
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
}
