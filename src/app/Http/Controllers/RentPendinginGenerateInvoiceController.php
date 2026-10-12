<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expedisi;
use App\Models\Arh;
use App\Models\Kwitansi;
use App\Models\Barh;
use App\Services\JournalService;
use App\Services\AccountMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RentPendinginGenerateInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $invoices = Arh::query()
            ->where('SOURCE', 'Rent')
            ->when($search, function ($query) use ($search) {
                $query->where('NOFAKTUR', 'like', "%{$search}%")
                    ->orWhere('CUSTOMER', 'like', "%{$search}%");
            })
            ->orderByDesc('TGLFAKTUR')
            ->paginate(20)
            ->withQueryString();

        return view('rent-pendingin-invoice.index', compact('invoices', 'search'));
    }

    public function showInvoiceDetail($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'REN')->get();

        return response()->json(compact('invoice', 'details'));
    }

    public function prosesInvoicePembayaran(Request $request)
    {
        $validated = $request->validate([
            'NOFAKTUR' => 'required|string|max:50|unique:arh,NOFAKTUR',
            'TGLFAKTUR' => 'required|date',
            'CUSTOMER' => 'required|string|max:30',
            'selected_sj' => 'required|array|min:1',
            'selected_sj.*' => 'exists:expedisis,NOSJ',
        ]);

        $expedisis = Expedisi::whereIn('NOSJ', $validated['selected_sj'])->where('JENIS', 'REN')->get();

        DB::transaction(function () use ($expedisis, $validated) {
            $totalGrand = $expedisis->sum('GRAND');

            Arh::create([
                'NOFAKTUR' => $validated['NOFAKTUR'],
                'TGLFAKTUR' => $validated['TGLFAKTUR'],
                'CUSTOMER' => $validated['CUSTOMER'],
                'SALES' => auth()->user()->name,
                'PIUTANG' => $totalGrand,
                'BAYAR' => 0,
                'SALDO' => $totalGrand,
                'STATUS' => 'OPEN',
                'SOURCE' => 'Rent',
                'USER' => auth()->user()->name,
            ]);

            $expedisis->each(fn($e) => $e->update(['INVOICE' => $validated['NOFAKTUR']]));
        });

        return redirect()->route('rent-pendingin-invoice.index')
            ->with('success', 'Invoice rent pendingin berhasil digenerate.');
    }

    public function prosesKwitansiDelete(Request $request, $noInvoice)
    {
        Arh::where('NOFAKTUR', $noInvoice)->delete();
        Expedisi::where('INVOICE', $noInvoice)->update(['INVOICE' => null]);

        return back()->with('success', 'Invoice berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $invoices = Arh::where('SOURCE', 'Rent')
            ->when($request->input('date_from'), fn($q) => $q->whereDate('TGLFAKTUR', '>=', $request->input('date_from')))
            ->when($request->input('date_to'), fn($q) => $q->whereDate('TGLFAKTUR', '<=', $request->input('date_to')))
            ->get();

        return Excel::download(new \App\Exports\CoolroomExport($invoices), 'rent-pendingin.xlsx');
    }

    public function indexEdit()
    {
        $invoices = Arh::where('SOURCE', 'Rent')->orderByDesc('TGLFAKTUR')->paginate(20);

        return view('rent-pendingin-invoice.edit', compact('invoices'));
    }

    public function tableEditRen()
    {
        $invoices = Arh::where('SOURCE', 'Rent')
            ->when(request('search'), function ($query, $search) {
                $query->where('NOFAKTUR', 'like', "%{$search}%")
                    ->orWhere('CUSTOMER', 'like', "%{$search}%");
            })
            ->paginate(20);

        return response()->json($invoices);
    }

    public function showEditInvoiceRen($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'REN')->get();

        return view('rent-pendingin-invoice.show-edit', compact('invoice', 'details'));
    }

    public function updateEditInvoiceRen(Request $request, $noInvoice)
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

    public function pdfInvoiceGenerate($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'REN')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('rent-pendingin-invoice.pdf.index', compact('invoice', 'details'));

        return $pdf->stream('invoice-rent-' . $noInvoice . '.pdf', ['stream' => true]);
    }
}
