<?php

namespace App\Http\Controllers;

use App\Models\Coolroom;
use App\Models\Kwitansi;
use App\Models\Signature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoolroomKwitansiController extends Controller
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

        $query = Coolroom::query()
            ->select(['id', 'INVOICE', 'TGLINVOICE', 'CUSTOMER', 'GRAND', 'PIUTANG', 'KWT'])
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '!=', '')
            ->where('GRAND', '>', 0);

        if ($status === 'sudah') {
            $query->whereNotNull('KWT')->where('KWT', '!=', '');
        } else {
            $query->where(function ($builder) {
                $builder->whereNull('KWT')->orWhere('KWT', '');
            });
        }

        $invoices = $query
            ->orderBy('INVOICE')
            ->orderByDesc('GRAND')
            ->paginate(20)
            ->withQueryString();

        return view('coolroom-kwitansi.index', compact('invoices', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice' => ['required', 'string', 'max:30'],
        ]);
        $invoice = trim($validated['invoice']);

        try {
            $noKwt = DB::transaction(function () use ($invoice) {
                $rows = Coolroom::where('INVOICE', $invoice)
                    ->lockForUpdate()
                    ->get();

                if ($rows->isEmpty()) {
                    throw new RuntimeException('Data invoice tidak ditemukan');
                }

                if ($rows->contains(fn ($row) => filled($row->KWT))) {
                    throw new RuntimeException('Invoice sudah memiliki kwitansi');
                }

                $master = $rows->first(fn ($row) => (float) ($row->GRAND ?? 0) > 0)
                    ?? $rows->first();

                if (! $master || (float) ($master->GRAND ?? 0) <= 0) {
                    throw new RuntimeException('Master invoice tidak ditemukan');
                }

                $noKwt = $this->generateKW();
                $today = now()->toDateString();

                foreach ($rows as $row) {
                    $row->KWT = $noKwt;
                    $row->TGLKW = $today;
                    $row->save();
                }

                Kwitansi::create([
                    'NOKWT' => $noKwt,
                    'TGL' => $today,
                    'FDOK_TRANS' => $invoice,
                    'TGL_TRANS' => $master->TGLINVOICE,
                    'CUSTOMER' => $master->CUSTOMER,
                    'NOSJ' => $rows->pluck('NOSJ')->implode(','),
                    'FKETERANG' => 'SEWA RUANG DINGIN PADA '.$master->CUSTOMER.', INVOICE : '.$invoice,
                    'FNAMA' => $master->CUSTOMER,
                    'FNIL_DOK' => (float) ($master->GRAND ?? 0),
                    'USERINPUT' => auth()->user()->user_id ?? auth()->id(),
                    'TOTAL' => (float) ($master->TOTAL ?? 0),
                    'PPN' => (float) ($master->PPN ?? 0),
                    'DISC' => (float) ($master->DISC ?? 0),
                    'NDISC' => (float) ($master->NDISC ?? 0),
                    'JENIS' => 'COL',
                ]);

                return $noKwt;
            });
        } catch (RuntimeException $exception) {
            return redirect()->route('coolroom-kwitansi.index')
                ->withErrors(['invoice' => $exception->getMessage()]);
        }

        return redirect()->route('coolroom-kwitansi.index', ['status_kwt' => 'sudah'])
            ->with('success', 'Kwitansi '.$noKwt.' berhasil diproses.')
            ->with('autoPrintKwitansi', $noKwt);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'kwt' => ['required', 'string', 'max:30'],
        ]);
        $noKwt = trim($validated['kwt']);

        try {
            DB::transaction(function () use ($noKwt) {
                $rows = Coolroom::where('KWT', $noKwt)
                    ->lockForUpdate()
                    ->get();

                if ($rows->isEmpty()) {
                    throw new RuntimeException('Data kwitansi tidak ditemukan');
                }

                foreach ($rows as $row) {
                    $row->KWT = null;
                    $row->TGLKW = null;
                    $row->save();
                }

                Kwitansi::where('NOKWT', $noKwt)
                    ->where('JENIS', 'COL')
                    ->delete();
            });
        } catch (RuntimeException $exception) {
            return redirect()->route('coolroom-kwitansi.index')
                ->withErrors(['kwt' => $exception->getMessage()]);
        }

        return redirect()->route('coolroom-kwitansi.index', ['status_kwt' => 'belum'])
            ->with('success', 'Kwitansi '.$noKwt.' berhasil dibatalkan.');
    }

    public function printKwitansiText(string $noKwt)
    {
        [$master, $details, $signature] = $this->loadKwitansiData($noKwt);

        $lineWidth = 130;
        $linesPerPage = 30;
        $leftMargin = 3;
        $esc = "\x1B";
        $boldOn = $esc.'E';
        $boldOff = $esc.'F';
        $doubleWidthOn = $esc.'W'."\x01";
        $doubleWidthOff = $esc.'W'."\x00";
        $clean = fn ($value) => trim(preg_replace('/[\r\n]+/', ' ', (string) $value));
        $fit = fn ($value, $width) => substr($clean($value), 0, $width);

        $grand = (float) ($master->GRAND ?? 0);
        $tanggal = $master->TGLKW ? $master->TGLKW->format('d-m-Y') : '-';
        $nomorSj = $details->pluck('NOSJ')
            ->filter(fn ($value) => trim((string) $value) !== '')
            ->unique()
            ->implode(', ');
        $signatureName = $clean($signature->NAMA ?? $signature->nama ?? '');

        $lines = [
            $boldOn.$doubleWidthOn.str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', intdiv($lineWidth, 2), ' ', STR_PAD_BOTH).$doubleWidthOff.$boldOff,
            str_pad('COLD CHAIN DISTRIBUTION & STORAGE', $lineWidth, ' ', STR_PAD_BOTH),
            str_repeat('=', $lineWidth),
            sprintf('%-65s%65s', 'Jl. Raya Sempidi No.9 Badung - Bali', 'BizPark Commercial Estate'),
            sprintf('%-65s%65s', 'Telp/Fax : (0361) 8947610', 'Jl. Sultan Agung KM 28,5 Bekasi'),
            sprintf('%-65s%65s', 'Jl. Bija Taki IV, No.9 Denpasar - Bali', 'www.lintasmitralogistik.com'),
            str_repeat('-', $lineWidth),
            $boldOn.'KWITANSI NO : '.$master->KWT.$boldOff,
            'SUDAH TERIMA DARI : '.$fit($master->CUSTOMER ?? '-', 108),
            'BANYAKNYA UANG    : '.$fit(ucwords(terbilang($grand)).' Rupiah', 108),
            'UNTUK PEMBAYARAN  : SEWA RUANG DINGIN',
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

        $text = implode("\r\n", array_map(fn ($line) => str_repeat(' ', $leftMargin).$line, $lines));
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

        $pdf = Pdf::loadView('coolroom-kwitansi.pdf.index', compact('master', 'details', 'signature', 'invoice', 'noKwt'));

        return $pdf->stream('kwitansi-coolroom-'.$noKwt.'.pdf', ['stream' => true]);
    }

    protected function loadKwitansiData(string $noKwt): array
    {
        $master = Coolroom::where('KWT', $noKwt)
            ->where('GRAND', '>', 0)
            ->firstOrFail();
        $details = Coolroom::where('KWT', $noKwt)->orderBy('NOSJ')->get();
        $signature = Signature::orderByDesc('id')->first();

        return [$master, $details, $signature];
    }

    private function generateKW(): string
    {
        $year = now()->format('Y');
        $last = Kwitansi::where('NOKWT', 'like', 'KW'.$year.'%')
            ->lockForUpdate()
            ->orderByDesc('NOKWT')
            ->value('NOKWT');

        if (! $last) {
            return 'KW'.$year.'0000001';
        }

        $number = (int) substr($last, 7) + 1;

        return 'KW'.$year.str_pad((string) $number, 7, '0', STR_PAD_LEFT);
    }
}
