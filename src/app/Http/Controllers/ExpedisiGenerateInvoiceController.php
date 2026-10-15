<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expedisi;
use App\Models\Arh;
use App\Models\Barh;
use App\Models\Bard;
use App\Services\JournalService;
use App\Services\AccountMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExpedisiGenerateInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $invoices = Arh::query()
            ->where('SOURCE', 'Expedisi')
            ->when($search, function ($query) use ($search) {
                $query->where('NOFAKTUR', 'like', "%{$search}%")
                    ->orWhere('CUSTOMER', 'like', "%{$search}%");
            })
            ->orderByDesc('TGLFAKTUR')
            ->paginate(20)
            ->withQueryString();

        return view('expedisi-invoice.index', compact('invoices', 'search'));
    }

    public function getDataInvoiceGen()
    {
        $expedisis = Expedisi::where('JENIS', 'EKS')
            ->whereNull('INVOICE')
            ->whereNotNull('NOSJ')
            ->get(['NOSJ', 'CUSTOMER', 'JUMLAH', 'HARGA', 'TOTAL', 'DISC', 'NDISCAW', 'GRAND', 'TGLMUAT']);

        return response()->json($expedisis);
    }

    public function prosesInvoiceStore(Request $request)
    {
        $noInvoice = $request->input('INVOICE') ?? now()->format('Ymd') . '-EKS-' . rand(1000, 9999);
        $nosjList = $request->input('selected_sj', []);

        $expedisis = Expedisi::whereIn('NOSJ', $nosjList)->where('JENIS', 'EKS')->get();

        if ($expedisis->isEmpty()) {
            return back()->with('error', 'Pilih minimal satu surat jalan.');
        }

        DB::transaction(function () use ($expedisis, $noInvoice, $request) {
            $totalGrand = $expedisis->sum('GRAND');
            $customer = $expedisis->first()->CUSTOMER;

            $arh = Arh::create([
                'NOFAKTUR' => $noInvoice,
                'TGLFAKTUR' => $request->input('TGLFAKTUR') ?? now(),
                'TGLJT' => $request->input('TGLJT'),
                'CUSTOMER' => $customer,
                'SALES' => $request->input('SALES'),
                'PIUTANG' => $totalGrand,
                'BAYAR' => 0,
                'SALDO' => $totalGrand,
                'KETERANGAN' => $request->input('KETERANGAN'),
                'STATUS' => 'OPEN',
                'SOURCE' => 'Expedisi',
                'USER' => $request->user()->name,
            ]);

            $expedisis->each(function ($expedisi) use ($noInvoice) {
                $expedisi->update(['INVOICE' => $noInvoice]);
            });

            $this->createJournal($arh, $totalGrand);
        });

        return redirect()->route('expedisi-invoice.index')
            ->with('success', 'Invoice berhasil digenerate.');
    }

    public function showInvoiceGabung($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Expedisi::where('INVOICE', $noInvoice)->get();

        return response()->json(compact('invoice', 'details'));
    }

    public function indexEdit()
    {
        $invoices = Arh::where('SOURCE', 'Expedisi')->orderByDesc('TGLFAKTUR')->paginate(20);

        return view('expedisi-invoice.edit', compact('invoices'));
    }

    public function tableEdit()
    {
        $invoices = Arh::where('SOURCE', 'Expedisi')
            ->when(request('search'), function ($query, $search) {
                $query->where('NOFAKTUR', 'like', "%{$search}%")
                    ->orWhere('CUSTOMER', 'like', "%{$search}%");
            })
            ->paginate(20);

        return response()->json($invoices);
    }

    public function showEditInvoice($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Expedisi::where('INVOICE', $noInvoice)->get();

        return view('expedisi-invoice.show-edit', compact('invoice', 'details'));
    }

    public function updateEditInvoice(Request $request, $noInvoice)
    {
        $arh = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();

        $arh->update([
            'TGLFAKTUR' => $request->input('TGLFAKTUR'),
            'TGLJT' => $request->input('TGLJT'),
            'KETERANGAN' => $request->input('KETERANGAN'),
            'CUSTOMER' => $request->input('CUSTOMER'),
        ]);

        return back()->with('success', 'Invoice berhasil diperbarui.');
    }

    public function export(Request $request)
    {
        $invoices = Arh::where('SOURCE', 'Expedisi')
            ->when($request->input('date_from'), fn($q) => $q->whereDate('TGLFAKTUR', '>=', $request->input('date_from')))
            ->when($request->input('date_to'), fn($q) => $q->whereDate('TGLFAKTUR', '<=', $request->input('date_to')))
            ->get();

        return Excel::download(new \App\Exports\InvoiceExport($invoices), 'invoice.xlsx');
    }

    public function printInvoiceText($noInvoice)
    {
        $arh = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Expedisi::where('INVOICE', $noInvoice)->get();
        $signature = \App\Models\Signature::where('is_active', true)->first();
        $rekening = \App\Models\Rekening::where('AKTIF', true)->first();

        return response()->view('expedisi-invoice.print.text', compact('arh', 'details', 'signature', 'rekening'))
            ->header('Content-Type', 'text/plain');
    }

    public function pdfGabungInvoice($noInvoice)
    {
        $arh = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Expedisi::where('INVOICE', $noInvoice)->get();
        $signature = \App\Models\Signature::where('is_active', true)->first();
        $rekening = \App\Models\Rekening::where('AKTIF', true)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('expedisi-invoice.pdf.index', compact('arh', 'details', 'signature', 'rekening'));

        return $pdf->stream('invoice-' . $noInvoice . '.pdf', ['stream' => true]);
    }

    protected function createJournal(Arh $arh, float $amount): void
    {
        if ($amount <= 0) return;

        $journalService = app(JournalService::class);
        $noJurnal = $journalService->generateJournalNumber('JURNAL', $arh->TGLFAKTUR);

        $journal = $journalService->createJournal(
            $noJurnal,
            $arh->TGLFAKTUR->toDateString(),
            "Invoice Expedisi {$arh->NOFAKTUR}",
            [
                ['coa_id' => AccountMap::id('ar_usaha') ?? 11, 'debit' => $amount, 'kredit' => 0, 'deskripsi' => 'Piutang Usaha'],
                ['coa_id' => AccountMap::id('income_sales') ?? 4, 'debit' => 0, 'kredit' => $amount, 'deskripsi' => 'Penjualan'],
            ],
            null, null, 'Penjualan', 'Penjualan'
        );

        $arh->update(['NOJURNAL' => $noJurnal, 'jurnal_id' => $journal->id]);
    }
}
