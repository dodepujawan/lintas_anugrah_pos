<?php

namespace App\Http\Controllers;

use App\Models\Expedisi;
use App\Models\Kwitansi;
use App\Models\Mcustomer;
use App\Models\Signature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RentPendinginKwitansiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $status = in_array($request->input('status_kwt'), ['belum', 'sudah'], true)
            ? $request->input('status_kwt')
            : 'belum';

        $query = Expedisi::query()
            ->select(['id', 'INVOICE', 'TGLINVOICE', 'CUSTOMER', 'GRAND', 'PIUTANG', 'kwt'])
            ->where('JENIS', 'REN')
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '!=', '')
            ->where('GRAND', '>', 0);

        if ($status === 'sudah') {
            $query->whereNotNull('kwt')->where('kwt', '!=', '');
        } else {
            $query->where(function ($q) {
                $q->whereNull('kwt')->orWhere('kwt', '');
            });
        }

        $invoices = $query
            ->orderBy('INVOICE')
            ->orderByDesc('GRAND')
            ->paginate(20)
            ->withQueryString();

        return view('rent-pendingin-kwitansi.index', compact('invoices', 'status'));
    }

    public function store(Request $request)
    {
        return $this->prosesKwitansi($request);
    }

    public function destroy(Request $request)
    {
        return $this->deleteKwitansi($request);
    }

    public function create()
    {
        return redirect()->route('rent-pendingin-kwitansi.index');
    }

    public function show($id)
    {
        return redirect()->route('rent-pendingin-kwitansi.index');
    }

    public function edit($id)
    {
        return redirect()->route('rent-pendingin-kwitansi.index');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('rent-pendingin-kwitansi.index');
    }

    public function prosesKwitansi(Request $request)
    {
        $validated = $request->validate([
            'invoice' => ['required', 'string', 'max:50'],
        ]);

        $invoice = trim((string) $validated['invoice']);

        try {
            $noKwt = DB::transaction(function () use ($invoice) {
                $rows = Expedisi::where('INVOICE', $invoice)
                    ->where('JENIS', 'REN')
                    ->lockForUpdate()
                    ->get();

                if ($rows->isEmpty()) {
                    throw new RuntimeException('Data invoice tidak ditemukan');
                }

                if ($rows->contains(fn ($row) => filled($row->kwt))) {
                    throw new RuntimeException('Invoice sudah memiliki kwitansi');
                }

                $master = $rows->first(fn ($row) => (float) ($row->GRAND ?? 0) > 0) ?? $rows->first();
                if (! $master) {
                    throw new RuntimeException('Master invoice tidak ditemukan');
                }

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
                    'NOKWT' => $noKwt,
                    'TGL' => $today,
                    'FDOK_TRANS' => $invoice,
                    'TGL_TRANS' => $master->TGLINVOICE ?? now(),
                    'CUSTOMER' => $master->CUSTOMER,
                    'NOSJ' => $rows->pluck('NOSJ')->filter(fn ($value) => trim((string) $value) !== '')->implode(','),
                    'FKETERANG' => 'PENYEWAAN MOBIL PENDINGIN PADA '.$namaCust.', INVOICE : '.$invoice,
                    'FNAMA' => $namaCust,
                    'FNIL_DOK' => (float) ($master->GRAND ?? 0),
                    'USERINPUT' => auth()->user()->user_id ?? auth()->id(),
                    'TOTAL' => (float) ($master->TOTAL ?? 0),
                    'PPN' => (float) ($master->PPN ?? 0),
                    'DISC' => (float) ($master->DISC ?? 0),
                    'NDISC' => (float) ($master->NDISC ?? 0),
                    'JENIS' => 'REN',
                ]);

                return $noKwt;
            });
        } catch (RuntimeException $e) {
            return redirect()->route('rent-pendingin-kwitansi.index')
                ->withErrors(['invoice' => $e->getMessage()]);
        }

        return redirect()->route('rent-pendingin-kwitansi.index', ['status_kwt' => 'sudah'])
            ->with('success', 'Kwitansi '.$noKwt.' berhasil diproses.')
            ->with('autoPrintKwitansi', $noKwt);
    }

    public function deleteKwitansi(Request $request)
    {
        $validated = $request->validate([
            'kwt' => ['required', 'string', 'max:50'],
        ]);

        $noKwt = trim((string) $validated['kwt']);

        try {
            DB::transaction(function () use ($noKwt) {
                $rows = Expedisi::where('kwt', $noKwt)
                    ->where('JENIS', 'REN')
                    ->lockForUpdate()
                    ->get();

                if ($rows->isEmpty()) {
                    throw new RuntimeException('Data kwitansi tidak ditemukan');
                }

                foreach ($rows as $row) {
                    $row->kwt = null;
                    $row->TGLKW = null;
                    $row->save();
                }

                Kwitansi::where('NOKWT', $noKwt)->delete();
            });
        } catch (RuntimeException $e) {
            return redirect()->route('rent-pendingin-kwitansi.index')
                ->withErrors(['kwt' => $e->getMessage()]);
        }

        return redirect()->route('rent-pendingin-kwitansi.index', ['status_kwt' => 'belum'])
            ->with('success', 'Kwitansi '.$noKwt.' berhasil dibatalkan.');
    }

    public function printKwitansiText(string $noKwt)
    {
        [$master, $details, $signature] = $this->loadKwitansiData($noKwt);
        $grand = (float) ($master->GRAND ?? 0);
        $tanggal = ! empty($master->TGLKW) ? date('d-m-Y', strtotime($master->TGLKW)) : '-';
        $nomorSj = $details->pluck('NOSJ')->filter(fn ($value) => trim((string) $value) !== '')->unique()->implode(', ');

        $lineWidth = 130;
        $linesPerPage = 30;
        $leftMargin = 3;
        $esc = "\x1B";
        $boldOn = $esc.'E';
        $boldOff = $esc.'F';
        $doubleWidthOn = $esc.'W' . "\x01";
        $doubleWidthOff = $esc.'W' . "\x00";

        $clean = fn ($value) => trim(preg_replace('/[\r\n]+/', ' ', (string) $value));
        $fit = fn ($value, $width) => substr($clean($value), 0, $width);
        $signatureName = $clean($signature->NAMA ?? '');
        $terbilangText = ucwords(terbilang($grand)).' Rupiah';

        $lines = [
            $boldOn.$doubleWidthOn.str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', intdiv($lineWidth, 2), ' ', STR_PAD_BOTH).$doubleWidthOff.$boldOff,
            str_pad('COLD CHAIN DISTRIBUTION & STORAGE', $lineWidth, ' ', STR_PAD_BOTH),
            str_repeat('=', $lineWidth),
            sprintf('%-65s%65s', 'Jl. Raya Sempidi No.9 Badung - Bali', 'BizPark Commercial Estate'),
            sprintf('%-65s%65s', 'Telp/Fax : (0361) 8947610', 'Jl. Sultan Agung KM 28,5 Bekasi'),
            sprintf('%-65s%65s', 'Jl. Bija Taki IV, No.9 Denpasar - Bali', 'www.lintasmitralogistik.com'),
            str_repeat('-', $lineWidth),
            $boldOn.'KWITANSI NO : '.$master->kwt.$boldOff,
            'SUDAH TERIMA DARI : '.$fit($master->CUSTOMER ?? '-', 108),
            'BANYAKNYA UANG    : '.$fit($terbilangText, 108),
            'UNTUK PEMBAYARAN  :',
        ];

        $sjRows = explode("\n", wordwrap($nomorSj !== '' ? $nomorSj : '-', 118, "\n", true));
        foreach ($sjRows as $index => $row) {
            $lines[] = ($index === 0 ? '  SJ : ' : '       ').$row;
        }

        $lines[] = '       INVOICE : '.$master->INVOICE;
        $lines[] = str_repeat('-', $lineWidth);
        $lines[] = 'Untuk pembayaran mohon di transfer ke rek resmi';
        $lines[] = $boldOn.'A/n. PT. Lintas Mitra Anugerah Sejati'.$boldOff;
        $lines[] = 'No.rek BCA 6115352010';
        $lines[] = sprintf('%-65s%65s', 'CEK/GIRO NO. _____________________________', 'Denpasar '.$tanggal);
        $lines[] = '';
        $lines[] = $boldOn.'JUMLAH'.$boldOff.str_repeat(' ', 20).'RP. '.number_format($grand, 0, ',', '.');
        $lines[] = '';
        $lines[] = sprintf('%-65s%65s', 'PENERIMA', 'MENGETAHUI');
        $lines[] = '';
        $lines[] = '';
        $lines[] = sprintf('%-65s%65s', '(.................................)', $signatureName);

        if (count($lines) < $linesPerPage) {
            $lines = array_pad($lines, $linesPerPage, '');
        }

        $margin = str_repeat(' ', $leftMargin);
        $text = implode("\r\n", array_map(fn ($line) => $margin.$line, $lines));
        $converted = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
        $text = "\x1B\x0F".$text."\x12";

        return response()->json([
            'text' => $text,
            'lines' => count($lines),
            'invoice' => $master->INVOICE,
            'kwitansi' => $noKwt,
        ]);
    }

    public function printKwitansi(string $noKwt)
    {
        [$master, $details, $signature] = $this->loadKwitansiData($noKwt);
        $invoice = $master->INVOICE;

        $pdf = Pdf::loadView('rent-pendingin-kwitansi.pdf.index', compact('master', 'details', 'signature', 'invoice', 'noKwt'));

        return $pdf->stream('kwitansi-rent-'.$noKwt.'.pdf', ['stream' => true]);
    }

    protected function loadKwitansiData(string $noKwt): array
    {
        $master = Expedisi::where('kwt', $noKwt)->where('JENIS', 'REN')->where('GRAND', '>', 0)->firstOrFail();
        $details = Expedisi::where('kwt', $noKwt)->where('JENIS', 'REN')->orderBy('NOSJ')->get();
        $signature = Signature::orderByDesc('id')->first();

        return [$master, $details, $signature];
    }

    private function generateKW(): string
    {
        $year = now()->format('Y');
        $last = Kwitansi::where('NOKWT', 'like', 'KW'.$year.'%')
            ->orderByDesc('NOKWT')
            ->value('NOKWT');

        if (! $last) {
            return 'KW'.$year.'0000001';
        }

        $number = (int) substr($last, 7);
        $number++;

        return 'KW'.$year.str_pad((string) $number, 7, '0', STR_PAD_LEFT);
    }
}
