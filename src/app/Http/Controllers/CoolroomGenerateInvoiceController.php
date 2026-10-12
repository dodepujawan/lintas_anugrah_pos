<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coolroom;
use App\Models\Arh;
use App\Services\JournalService;
use App\Services\AccountMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Arr;

class CoolroomGenerateInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $invoices = Arh::query()
            ->where('SOURCE', 'Coolroom')
            ->when($search, function ($query) use ($search) {
                $query->where('NOFAKTUR', 'like', "%{$search}%")
                    ->orWhere('CUSTOMER', 'like', "%{$search}%");
            })
            ->orderByDesc('TGLFAKTUR')
            ->paginate(20)
            ->withQueryString();

        return view('coolroom-invoice.index', compact('invoices', 'search'));
    }

    public function show($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Coolroom::where('NOSJ', $invoice->NOFAKTUR)->get();

        return view('coolroom-invoice.show', compact('invoice', 'details'));
    }

    public function store(Request $request)
    {
        $coolrooms = Coolroom::whereIn('id', $request->input('selected', []))->get();

        if ($coolrooms->isEmpty()) {
            return back()->with('error', 'Pilih setidaknya satu data coolroom.');
        }

        DB::transaction(function () use ($coolrooms, $request) {
            $totalGrand = $coolrooms->sum('GRAND');
            $customer = $coolrooms->first()->CUSTOMER;
            $noInvoice = $request->input('INVOICE', now()->format('Ymd') . '-CR-' . rand(1000, 9999));

            $arh = Arh::create([
                'NOFAKTUR' => $noInvoice,
                'TGLFAKTUR' => now(),
                'TGLJT' => $request->input('TGLJT'),
                'CUSTOMER' => $customer,
                'SALES' => $request->user()->name,
                'AREA' => $request->user()->area_name ?? null,
                'PIUTANG' => $totalGrand,
                'BAYAR' => 0,
                'SALDO' => $totalGrand,
                'CABANG' => $request->user()->area_name ?? null,
                'KETERANGAN' => $request->input('KETERANGAN'),
                'STATUS' => 'OPEN',
                'SOURCE' => 'Coolroom',
                'USER' => $request->user()->name,
            ]);

            $coolrooms->each(function ($coolroom) use ($noInvoice) {
                $coolroom->update([
                    'INVOICE' => $noInvoice,
                    'NOJURNAL' => null,
                    'jurnal_id' => null,
                ]);
            });

            $this->createJournal($arh, $totalGrand);
        });

        return redirect()->route('coolroom-invoice.index')
            ->with('success', 'Invoice coolroom berhasil digenerate.');
    }

    public function edit($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();

        return view('coolroom-invoice.edit', compact('invoice'));
    }

    public function update(Request $request, $noInvoice)
    {
        $arh = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();

        $validated = $request->validate([
            'TGLFAKTUR' => 'nullable|date',
            'TGLJT' => 'nullable|date',
            'KETERANGAN' => 'nullable|string',
        ]);

        $arh->update($validated);

        return redirect()->route('coolroom-invoice.index')
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    public function printInvoiceCoolroom($noInvoice)
    {
        $arh = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Coolroom::where('INVOICE', $noInvoice)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('coolroom-invoice.pdf.index', compact('arh', 'details'));

        return $pdf->stream('invoice-' . $noInvoice . '.pdf', ['stream' => true]);
    }

    public function export(Request $request)
    {
        $invoices = Arh::where('SOURCE', 'Coolroom')
            ->when($request->input('date_from'), fn($q) => $q->whereDate('TGLFAKTUR', '>=', $request->input('date_from')))
            ->when($request->input('date_to'), fn($q) => $q->whereDate('TGLFAKTUR', '<=', $request->input('date_to')))
            ->get();

        return Excel::download(new \App\Exports\CoolroomExport($invoices), 'coolroom.xlsx');
    }

    protected function createJournal(Arh $arh, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $journalService = app(JournalService::class);
        $noJurnal = $journalService->generateJournalNumber('JURNAL', $arh->TGLFAKTUR);

        $journal = $journalService->createJournal(
            $noJurnal,
            $arh->TGLFAKTUR->toDateString(),
            "Invoice Coolroom {$arh->NOFAKTUR}",
            [
                ['coa_id' => AccountMap::id('ar_usaha') ?? 11, 'debit' => $amount, 'kredit' => 0, 'deskripsi' => 'Piutang Usaha'],
                ['coa_id' => AccountMap::id('income_sales') ?? 4, 'debit' => 0, 'kredit' => $amount, 'deskripsi' => 'Penjualan'],
            ],
            null,
            null,
            'Penjualan',
            'Penjualan'
        );

        $arh->update(['NOJURNAL' => $noJurnal, 'jurnal_id' => $journal->id]);
    }
}
