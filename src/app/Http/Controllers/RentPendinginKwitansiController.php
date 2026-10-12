<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kwitansi;
use App\Models\Arh;
use App\Models\Barh;
use App\Services\JournalService;
use App\Services\AccountMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentPendinginKwitansiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $kwitantis = Kwitansi::query()
            ->where('JENIS_KWT', 'Rent')
            ->when($search, function ($query) use ($search) {
                $query->where('NO_KWT', 'like', "%{$search}%")
                    ->orWhere('NOFAKTUR', 'like', "%{$search}%");
            })
            ->orderByDesc('TGL_KWT')
            ->paginate(20)
            ->withQueryString();

        return view('rent-pendingin-kwitansi.index', compact('kwitantis', 'search'));
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

        DB::transaction(function () use ($arh, $validated) {
            $noKwt = 'KWT-' . now()->format('Ymd') . '-' . rand(1000, 9999);

            $kwitansi = Kwitansi::create([
                'NO_KWT' => $noKwt,
                'TGL_KWT' => $validated['TGL_KWT'],
                'NOFAKTUR' => $validated['NOFAKTUR'],
                'SALDO_FAKTUR' => $arh->SALDO,
                'NILAI_BAYAR' => $bayar,
                'DISCOUNT' => $validated['DISCOUNT'] ?? 0,
                'RETUR' => $validated['RETUR'] ?? 0,
                'JENIS_KWT' => 'Rent',
                'CUSTOMER' => $arh->CUSTOMER,
                'NOBAYAR' => $arh->NOFAKTUR . '-' . $noKwt,
                'KETERANGAN' => $validated['KETERANGAN'],
            ]);

            $arh->increment('BAYAR', $bayar);

            Barh::create([
                'NOBAYAR' => $kwitansi->NOBAYAR,
                'TGLBAYAR' => $validated['TGL_KWT'],
                'CUSTOMER' => $arh->CUSTOMER,
                'JUMLAH_BAYAR' => $bayar,
                'METODE_PEMBAYARAN' => $validated['METODE_PEMBAYARAN'] ?? 'TUNAI',
                'NOFAKTUR' => $validated['NOFAKTUR'],
            ]);

            $this->createPaymentJournal($arh, $kwitansi, $bayar);
        });

        return redirect()->route('rent-pendingin-kwitansi.index')
            ->with('success', 'Kwitansi berhasil diproses.');
    }

    public function deleteKwitansi(Kwitansi $kwitansi)
    {
        $arh = Arh::where('NOFAKTUR', $kwitansi->NOFAKTUR)->first();

        if ($arh) {
            $arh->decrement('BAYAR', $kwitansi->NILAI_BAYAR);
        }

        Barh::where('NOBAYAR', $kwitansi->NOBAYAR)->delete();
        $kwitansi->delete();

        return redirect()->route('rent-pendingin-kwitansi.index')
            ->with('success', 'Kwitansi berhasil dihapus.');
    }

    public function printKwitansi(Kwitansi $kwitansi)
    {
        $arh = Arh::where('NOFAKTUR', $kwitansi->NOFAKTUR)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('rent-pendingin-kwitansi.pdf.index', compact('kwitansi', 'arh'));

        return $pdf->stream('kwitansi-rent-' . $kwitansi->NO_KWT . '.pdf', ['stream' => true]);
    }

    protected function createPaymentJournal(Arh $arh, Kwitansi $kwitansi, float $amount): void
    {
        if ($amount <= 0) return;

        $journalService = app(JournalService::class);
        $noJurnal = $journalService->generateJournalNumber('JURNAL', $kwitansi->TGL_KWT);

        $journal = $journalService->createJournal(
            $noJurnal,
            $kwitansi->TGL_KWT->toDateString(),
            "Pembayaran Rent {$kwitansi->NO_KWT}",
            [
                ['coa_id' => AccountMap::id('cash') ?? 1, 'debit' => $amount, 'kredit' => 0, 'deskripsi' => 'Pembayaran Kas'],
                ['coa_id' => AccountMap::id('ar_usaha') ?? 11, 'debit' => 0, 'kredit' => $amount, 'deskripsi' => 'Penyelesaian Piutang'],
            ],
            null, null, 'Pelunasan Piutang', 'Pelunasan Piutang'
        );

        $kwitansi->update(['NOJURNAL' => $noJurnal, 'jurnal_id' => $journal->id]);
    }
}
