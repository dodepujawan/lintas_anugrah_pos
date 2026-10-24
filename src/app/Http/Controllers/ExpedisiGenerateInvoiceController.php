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
            ->where(function ($query) {
                $query->whereNull('INVOICE')->orWhere('INVOICE', '');
            })
            ->whereNotNull('NOSJ')
            ->get(['NOSJ', 'CUSTOMER', 'JUMLAH', 'HARGA', 'TOTAL', 'DISC', 'NDISCAW', 'GRAND', 'TGLMUAT']);

        return response()->json($expedisis);
    }

    public function prosesInvoiceStore(Request $request)
    {
        $validated = $request->validate([
            'INVOICE' => ['nullable', 'string', 'max:20', 'unique:arh,NOFAKTUR'],
            'selected_sj' => ['required', 'array', 'min:1'],
            'selected_sj.*' => ['required', 'string'],
            'TGLFAKTUR' => ['nullable', 'date'],
            'TGLJT' => ['nullable', 'date'],
            'KETERANGAN' => ['nullable', 'string', 'max:500'],
            'SALES' => ['nullable', 'string', 'max:50'],
        ]);
        $noInvoice = $validated['INVOICE'] ?? now()->format('Ymd') . '-EKS-' . random_int(1000, 9999);

        DB::transaction(function () use ($validated, $noInvoice, $request) {
            $picked = Expedisi::whereIn('NOSJ', $validated['selected_sj'])
                ->where('JENIS', 'EKS')->lockForUpdate()->get();
            if ($picked->count() !== count(array_unique($validated['selected_sj']))) {
                throw new \RuntimeException('Surat Jalan tidak ditemukan.');
            }
            $group = $picked->first()->GB;
            $expedisis = $group
                ? Expedisi::where('GB', $group)->where('JENIS', 'EKS')->lockForUpdate()->get()
                : $picked;
            if ($expedisis->isEmpty() || $expedisis->contains(fn ($row) => filled($row->INVOICE))) {
                throw new \RuntimeException('Surat Jalan sudah memiliki invoice atau tidak tersedia.');
            }
            $master = $expedisis->sortByDesc('GRAND')->first();
            $totalGrand = (float) $master->GRAND;
            $customer = $master->CUSTOMER;
            $tanggal = $validated['TGLFAKTUR'] ?? now()->toDateString();

            $arh = Arh::create([
                'NOFAKTUR' => $noInvoice,
                'TGLFAKTUR' => $tanggal,
                'TGLJT' => $validated['TGLJT'] ?? null,
                'CUSTOMER' => $customer,
                'SALES' => $validated['SALES'] ?? null,
                'PIUTANG' => $totalGrand,
                'BAYAR' => 0,
                'SALDO' => $totalGrand,
                'DISCOUNT' => $master->NDISC,
                'KETERANGAN' => $validated['KETERANGAN'] ?? null,
                'STATUS' => 'OPEN',
                'SOURCE' => 'Expedisi',
                'USER' => $request->user()->name,
            ]);

            $expedisis->each(function ($expedisi) use ($noInvoice, $validated) {
                $expedisi->update([
                    'INVOICE' => $noInvoice,
                    'TGLINVOICE' => now()->toDateString(),
                    'TGLJT' => $validated['TGLJT'] ?? null,
                    'STS' => 'INVOICE',
                ]);
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
