<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Arh;
use App\Models\Barh;
use App\Models\Kwitansi;
use App\Services\AccountMap;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoolroomKwitansiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $kwitantis = Kwitansi::query()
            ->where('JENIS', 'COO')
            ->when($search, function ($query) use ($search) {
                $query->where('NOKWT', 'like', "%{$search}%")
                    ->orWhere('FDOK_TRANS', 'like', "%{$search}%");
            })
            ->orderByDesc('TGL')
            ->paginate(20)
            ->withQueryString();

        return view('coolroom-kwitansi.index', compact('kwitantis', 'search'));
    }

    public function prosesKwitansi(Request $request)
    {
        $validated = $request->validate([
            'NOFAKTUR' => 'required|string|max:50',
            'TGL_KWT' => 'required|date',
            'NILAI_BAYAR' => 'required|numeric|min:0',
            'DISCOUNT' => 'nullable|numeric|min:0',
            'RETUR' => 'nullable|numeric|min:0',
            'METODE_PEMBAYARAN' => 'nullable|string|max:50',
            'KETERANGAN' => 'nullable|string|max:200',
        ]);

        $arh = Arh::where('NOFAKTUR', $validated['NOFAKTUR'])->firstOrFail();
        $sisa = (float) $arh->SALDO - (float) $arh->BAYAR;
        $bayar = min((float) $validated['NILAI_BAYAR'], $sisa);

        DB::transaction(function () use ($arh, $validated, $bayar) {
            $noKwt = 'KWT-' . now()->format('Ymd') . '-' . rand(1000, 9999);

            $kwitansi = Kwitansi::create([
                'NOKWT' => $noKwt,
                'TGL' => $validated['TGL_KWT'],
                'FDOK_TRANS' => $validated['NOFAKTUR'],
                'TGL_TRANS' => $arh->TGLFAKTUR,
                'CUSTOMER' => $arh->CUSTOMER,
                'NOSJ' => $arh->NOFAKTUR,
                'FKETERANG' => $validated['KETERANGAN'] ?? 'Pembayaran Coolroom',
                'FNAMA' => $arh->CUSTOMER,
                'FNIL_DOK' => (float) $arh->SALDO,
                'TOTAL' => (float) $arh->PIUTANG,
                'PPN' => 0,
                'DISC' => (float) ($validated['DISCOUNT'] ?? 0),
                'NDISC' => (float) ($validated['RETUR'] ?? 0),
                'USERINPUT' => auth()->user()->name ?? 'system',
                'JENIS' => 'COO',
            ]);

            $arh->increment('BAYAR', $bayar);
            $arh->touch();

            $this->createPaymentJournal($arh, $kwitansi, $bayar);
        });

        return redirect()->route('coolroom-kwitansi.index')
            ->with('success', 'Kwitansi coolroom berhasil diproses.');
    }

    public function deleteKwitansi(Kwitansi $kwitansi)
    {
        DB::transaction(function () use ($kwitansi) {
            $arh = Arh::where('NOFAKTUR', $kwitansi->FDOK_TRANS)->first();

            if ($arh) {
                $arh->decrement('BAYAR', $kwitansi->FNIL_DOK);
            }

            $kwitansi->delete();
        });

        return redirect()->route('coolroom-kwitansi.index')
            ->with('success', 'Kwitansi berhasil dihapus.');
    }

    public function printKwitansi(Kwitansi $kwitansi)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('coolroom-kwitansi.pdf.index', compact('kwitansi'));

        return $pdf->stream('kwitansi-' . $kwitansi->NOKWT . '.pdf', ['stream' => true]);
    }

    protected function createPaymentJournal(Arh $arh, Kwitansi $kwitansi, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $journalService = app(JournalService::class);
        $noJurnal = $journalService->generateJournalNumber('JURNAL', $kwitansi->TGL);

        $journal = $journalService->createJournal(
            $noJurnal,
            $kwitansi->TGL->toDateString(),
            "Pembayaran Coolroom {$kwitansi->NOKWT}",
            [
                ['coa_id' => AccountMap::id('cash') ?? 1, 'debit' => $amount, 'kredit' => 0, 'deskripsi' => 'Pembayaran Kas'],
                ['coa_id' => AccountMap::id('ar_usaha') ?? 11, 'debit' => 0, 'kredit' => $amount, 'deskripsi' => 'Penyelesaian Piutang'],
            ],
            null,
            null,
            'Pelunasan Piutang',
            'Pelunasan Piutang'
        );

        $kwitansi->update(['NOJURNAL' => $noJurnal, 'jurnal_id' => $journal->id]);
    }
}
