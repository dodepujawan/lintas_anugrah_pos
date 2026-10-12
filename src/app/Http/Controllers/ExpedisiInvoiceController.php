<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expedisi;
use App\Models\Arh;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpedisiInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $expedisis = Expedisi::query()
            ->where('JENIS', 'EKS')
            ->whereNotNull('NOSJ')
            ->when($search, function ($query) use ($search) {
                $query->where('NOSJ', 'like', "%{$search}%")
                    ->orWhere('CUSTOMER', 'like', "%{$search}%");
            })
            ->orderByDesc('TGLMUAT')
            ->paginate(15)
            ->withQueryString();

        return view('expedisi-invoice-gabung.index', compact('expedisis', 'search'));
    }

    public function getDataMuat()
    {
        $expedisis = Expedisi::where('JENIS', 'EKS')
            ->whereNotNull('NOSJ')
            ->whereNull('INVOICE')
            ->get(['NOSJ', 'CUSTOMER', 'JUMLAH', 'HARGA', 'TOTAL', 'DISC', 'NDISCAW', 'GRAND', 'TGLMUAT']);

        return response()->json($expedisis);
    }

    public function dataGabung()
    {
        $data = Expedisi::where('JENIS', 'EKS')
            ->whereNull('INVOICE')
            ->groupBy('CUSTOMER')
            ->selectRaw('CUSTOMER, SUM(GRAND) as TOTAL_GRAND, SUM(DISC) as TOTAL_DISC, COUNT(*) as COUNT_SJ')
            ->get();

        return response()->json($data);
    }

    public function getExistingGabung()
    {
        $invoices = Arh::where('SOURCE', 'Expedisi')
            ->where('STATUS', 'OPEN')
            ->get(['NOFAKTUR', 'CUSTOMER', 'SALDO', 'BAYAR', 'SISA']);

        return response()->json($invoices);
    }

    public function storeGabungInvoice(Request $request)
    {
        $validated = $request->validate([
            'NOFAKTUR' => 'required|string|max:50|unique:arh,NOFAKTUR',
            'TGLFAKTUR' => 'required|date',
            'TGLJT' => 'nullable|date',
            'CUSTOMER' => 'required|string|max:30',
            'selected_sj' => 'required|array|min:1',
            'selected_sj.*' => 'exists:expedisis,NOSJ',
            'KETERANGAN' => 'nullable|string|max:500',
        ]);

        $expedisis = Expedisi::whereIn('NOSJ', $validated['selected_sj'])->where('JENIS', 'EKS')->get();

        DB::transaction(function () use ($expedisis, $validated) {
            $totalGrand = $expedisis->sum('GRAND');

            $arh = Arh::create([
                'NOFAKTUR' => $validated['NOFAKTUR'],
                'TGLFAKTUR' => $validated['TGLFAKTUR'],
                'TGLJT' => $validated['TGLJT'],
                'CUSTOMER' => $validated['CUSTOMER'],
                'SALES' => auth()->user()->name ?? null,
                'AREA' => auth()->user()->area_name ?? null,
                'PIUTANG' => $totalGrand,
                'BAYAR' => 0,
                'SALDO' => $totalGrand,
                'DISCOUNT' => $expedisis->sum('NDISCAW'),
                'KETERANGAN' => $validated['KETERANGAN'],
                'STATUS' => 'OPEN',
                'SOURCE' => 'Expedisi',
                'USER' => auth()->user()->name ?? 'system',
            ]);

            $expedisis->each(fn($e) => $e->update(['INVOICE' => $validated['NOFAKTUR']]));
        });

        return redirect()->route('expedisi-invoice-gabung.index')
            ->with('success', 'Gabung invoice berhasil.');
    }

    public function updateGabungInvoice(Request $request, $noInvoice)
    {
        $arh = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();

        $arh->update([
            'TGLFAKTUR' => $request->input('TGLFAKTUR'),
            'TGLJT' => $request->input('TGLJT'),
            'CUSTOMER' => $request->input('CUSTOMER'),
            'KETERANGAN' => $request->input('KETERANGAN'),
        ]);

        return redirect()->route('expedisi-invoice-gabung.index')
            ->with('success', 'Invoice berhasil diperbarui.');
    }
}
