<?php

namespace App\Http\Controllers;

use App\Models\Arh;
use App\Models\Expedisi;
use App\Models\Kwitansi;
use App\Models\Mcustomer;
use App\Models\Rekening;
use App\Models\Signature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExpedisiKwitansiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Daftar invoice expedisi (sumber: expedisis), filter Belum / Sudah kwitansi.
     */
    public function index(Request $request)
    {
        $status = $request->input('status_kwt', 'belum');

        $query = Expedisi::query()
            ->select(['id', 'INVOICE', 'TGLINVOICE', 'CUSTOMER', 'GRAND', 'PIUTANG', 'kwt'])
            ->where('JENIS', 'EKS')
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '!=', '')
            ->where('GRAND', '>', 0);

        if ($status === 'sudah') {
            $query->whereNotNull('kwt')->where('kwt', '!=', '');
        } else {
            $status = 'belum';
            $query->where(function ($q) {
                $q->whereNull('kwt')->orWhere('kwt', '');
            });
        }

        $invoices = $query
            ->orderBy('INVOICE')
            ->orderByDesc('GRAND')
            ->paginate(20)
            ->withQueryString();

        return view('expedisi-kwitansi.index', compact('invoices', 'status'));
    }

    /**
     * Proses kwitansi per invoice (logika src_old): isi expedisis.kwt/TGLKW dan insert kwitansi.
     */
    public function prosesKwitansi(Request $request)
    {
        $validated = $request->validate([
            'invoice' => 'required|string|max:50',
        ]);
        $invoice = $validated['invoice'];

        try {
            $noKwt = DB::transaction(function () use ($invoice) {
                $rows = Expedisi::where('INVOICE', $invoice)->lockForUpdate()->get();

                if ($rows->isEmpty()) {
                    throw new RuntimeException('Data invoice tidak ditemukan');
                }

                if ($rows->contains(fn ($row) => ! empty($row->kwt))) {
                    throw new RuntimeException('Invoice sudah memiliki kwitansi');
                }

                $master = $rows->firstWhere(fn ($r) => (float) $r->GRAND > 0) ?? $rows->first();
                $grand = (float) $master->GRAND;
                $namaCust = Mcustomer::where('kode_cus', $master->CUSTOMER_KODE)->value('NAMACUST')
                    ?? $master->CUSTOMER;

                $noKwt = $this->generateKW();
                $today = now()->toDateString();

                foreach ($rows as $row) {
                    $row->kwt = $noKwt;
                    $row->TGLKW = $today;
                    $row->save();
                }

                Kwitansi::create([
                    'NO_KWT' => $noKwt,
                    'TGL_KWT' => $today,
                    'NOFAKTUR' => $invoice,
                    'SALDO_FAKTUR' => $grand,
                    'NILAI_BAYAR' => $grand,
                    'DISCOUNT' => 0,
                    'RETUR' => 0,
                    'SISA_FAKTUR' => 0,
                    'JENIS_KWT' => 'Expedisi',
                    'CUSTOMER' => $master->CUSTOMER,
                    'NAMACUST' => $namaCust,
                    'KETERANGAN' => 'PENJUALAN EXPEDISI PADA '.$namaCust.', INVOICE : '.$invoice,
                    'user' => auth()->user()->name ?? null,
                ]);

                return $noKwt;
            });
        } catch (RuntimeException $e) {
            return redirect()->route('expedisi-kwitansi.index')
                ->withErrors(['invoice' => $e->getMessage()]);
        }

        return redirect()->route('expedisi-kwitansi.index')
            ->with('success', 'Kwitansi '.$noKwt.' berhasil diproses.')
            ->with('autoPrintKwitansi', $noKwt);
    }

    /**
     * Batalkan kwitansi (logika src_old): reset expedisis.kwt/TGLKW dan hapus kwitansi.
     */
    public function deleteKwitansi(Request $request)
    {
        $validated = $request->validate([
            'kwt' => 'required|string|max:50',
        ]);
        $noKwt = $validated['kwt'];

        try {
            DB::transaction(function () use ($noKwt) {
                $rows = Expedisi::where('kwt', $noKwt)->lockForUpdate()->get();

                if ($rows->isEmpty()) {
                    throw new RuntimeException('Data kwitansi tidak ditemukan');
                }

                foreach ($rows as $row) {
                    $row->kwt = null;
                    $row->TGLKW = null;
                    $row->save();
                }

                Kwitansi::where('NO_KWT', $noKwt)->delete();
            });
        } catch (RuntimeException $e) {
            return redirect()->route('expedisi-kwitansi.index')
                ->withErrors(['kwt' => $e->getMessage()]);
        }

        return redirect()->route('expedisi-kwitansi.index')
            ->with('success', 'Kwitansi '.$noKwt.' berhasil dibatalkan.');
    }

    /**
     * Teks ESC/P untuk direct print ke print bridge Electron (logika src_old printKwitansiText).
     */
    public function printKwitansiText(string $noKwt)
    {
        [$master, $details, , $signature] = $this->loadKwitansiData($noKwt);
        $invoice = $master->INVOICE;

        $LINE_WIDTH = 130;
        $LINES_PER_PAGE = 30;
        $LEFT_MARGIN = 3;

        $ESC = "\x1B";
        $BOLD_ON = $ESC.'E';
        $BOLD_OFF = $ESC.'F';
        $DW_ON = $ESC.'W'."\x01";
        $DW_OFF = $ESC.'W'."\x00";

        $clean = fn ($value) => trim(preg_replace('/[\r\n]+/', ' ', (string) $value));
        $fit = function ($text, $width) use ($clean) {
            $text = $clean($text);

            return strlen($text) > $width ? substr($text, 0, $width) : $text;
        };
        $money = fn ($value) => number_format((float) ($value ?? 0), 0, ',', '.');

        $customer = $clean($master->CUSTOMER ?? '-');
        $grand = (float) ($master->GRAND ?? 0);
        $tanggal = ! empty($master->TGLKW) ? date('d-m-Y', strtotime($master->TGLKW)) : '-';
        $nomorSJ = $details->pluck('NOSJ')->filter(fn ($v) => trim((string) $v) !== '')->unique()->implode(', ');
        $signatureName = $clean($signature->NAMA ?? '');
        $terbilangText = ucwords(terbilang($grand)).' Rupiah';

        $lines = [];

        $judul = 'PT. LINTAS MITRA ANUGERAH SEJATI';
        $lines[] = $BOLD_ON.$DW_ON.str_pad($judul, intdiv($LINE_WIDTH, 2), ' ', STR_PAD_BOTH).$DW_OFF.$BOLD_OFF;
        $lines[] = str_pad('COLD CHAIN DISTRIBUTION & STORAGE', $LINE_WIDTH, ' ', STR_PAD_BOTH);
        $lines[] = str_repeat('=', $LINE_WIDTH);

        $lines[] = sprintf('%-65s%65s', 'Jl. Raya Sempidi No.9 Badung - Bali', 'BizPark Commercial Estate');
        $lines[] = sprintf('%-65s%65s', 'Telp/Fax : (0361) 8947610', 'Jl. Sultan Agung KM 28,5 Bekasi');
        $lines[] = sprintf('%-65s%65s', 'Jl. Bija Taki IV, No.9 Denpasar - Bali', 'www.lintasmitralogistik.com');
        $lines[] = str_repeat('-', $LINE_WIDTH);

        $lines[] = $BOLD_ON.'KWITANSI NO : '.$master->kwt.$BOLD_OFF;
        $lines[] = 'SUDAH TERIMA DARI : '.$fit($customer, 108);
        $lines[] = 'BANYAKNYA UANG    : '.$fit($terbilangText, 108);
        $lines[] = 'UNTUK PEMBAYARAN  :';

        $sjWrapped = wordwrap($nomorSJ !== '' ? $nomorSJ : '-', 118, "\n", true);
        foreach (explode("\n", $sjWrapped) as $i => $row) {
            $lines[] = $i === 0 ? '  SJ : '.$row : '       '.$row;
        }
        $lines[] = '       INVOICE : '.$invoice;

        $lines[] = str_repeat('-', $LINE_WIDTH);

        $lines[] = 'Untuk pembayaran mohon di transfer ke rek resmi';
        $lines[] = $BOLD_ON.'A/n. PT. Lintas Mitra Anugerah Sejati'.$BOLD_OFF;
        $lines[] = 'No.rek BCA 6115352010';

        $cek = 'CEK/GIRO NO. _____________________________';
        $tgl = 'Denpasar '.$tanggal;
        $lines[] = sprintf('%-65s%65s', $cek, $tgl);

        $lines[] = '';
        $lines[] = $BOLD_ON.'JUMLAH'.$BOLD_OFF.str_repeat(' ', 20).'RP. '.$money($grand);

        $lines[] = '';
        $lines[] = sprintf('%-65s%65s', 'PENERIMA', 'MENGETAHUI');
        $lines[] = '';
        $lines[] = '';
        $lines[] = sprintf('%-65s%65s', '(.................................)', $signatureName);

        if (count($lines) < $LINES_PER_PAGE) {
            $lines = array_merge($lines, array_fill(0, $LINES_PER_PAGE - count($lines), ''));
        }

        $margin = str_repeat(' ', $LEFT_MARGIN);
        $lines = array_map(fn ($line) => $margin.$line, $lines);

        $text = implode("\r\n", $lines);

        $converted = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }

        // Condensed mode, sama dengan invoice
        $text = "\x1B\x0F".$text."\x12";

        return response()->json([
            'text' => $text,
            'lines' => count($lines),
            'invoice' => $invoice,
            'kwitansi' => $noKwt,
        ]);
    }

    /**
     * PDF kwitansi (fallback bila print langsung tidak tersedia).
     */
    public function printKwitansi(string $noKwt)
    {
        [$master, $details, $arh, $signature, $rekening] = $this->loadKwitansiData($noKwt);

        $pdf = Pdf::loadView('expedisi-kwitansi.pdf.index', compact('master', 'details', 'arh', 'signature', 'rekening'));

        return $pdf->stream('kwitansi-'.$noKwt.'.pdf');
    }

    /**
     * Data kwitansi dari expedisis (arh hanya dibaca, tidak diubah; sama dengan src_old).
     *
     * @return array{0: Expedisi, 1: Collection, 2: ?Arh, 3: ?Signature, 4: ?Rekening}
     */
    private function loadKwitansiData(string $noKwt): array
    {
        $master = Expedisi::where('kwt', $noKwt)->where('GRAND', '>', 0)->firstOrFail();
        $details = Expedisi::where('kwt', $noKwt)->orderBy('NOSJ')->get();
        $arh = Arh::where('NOFAKTUR', $master->INVOICE)->first();
        $signature = Signature::where('is_active', true)->first() ?? Signature::orderByDesc('id')->first();
        $rekening = Rekening::where('AKTIF', true)->first();

        return [$master, $details, $arh, $signature, $rekening];
    }

    private function generateKW(): string
    {
        $year = now()->format('Y');

        $last = Kwitansi::where('NO_KWT', 'like', 'KW'.$year.'%')
            ->lockForUpdate()
            ->orderByDesc('NO_KWT')
            ->value('NO_KWT');

        if (! $last) {
            return 'KW'.$year.'0000001';
        }

        $number = (int) substr($last, 6) + 1;

        return 'KW'.$year.str_pad((string) $number, 7, '0', STR_PAD_LEFT);
    }
}
