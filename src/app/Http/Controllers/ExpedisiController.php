<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expedisi;
use App\Models\Mcustomer;
use App\Models\Kendaraan;
use App\Models\Driver;
use App\Models\Arh;
use App\Services\JournalService;
use App\Services\AccountMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpedisiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $filterDate = $request->input('date');
        $filterStatus = $request->input('status');

        $expedisis = Expedisi::query()
            ->where('JENIS', 'EKS')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('NOSJ', 'like', "%{$search}%")
                        ->orWhere('INVOICE', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%");
                });
            })
            ->when($filterDate, function ($query, $date) {
                $query->whereDate('TGLMUAT', $date);
            })
            ->when($filterStatus, function ($query, $status) {
                $query->where('STS', $status);
            })
            ->orderByDesc('TGLMUAT')
            ->orderByDesc('NOSJ')
            ->paginate(20)
            ->withQueryString();

        return view('expedisi.index', compact('expedisis', 'search'));
    }

    public function create()
    {
        $customers = Mcustomer::orderBy('CUSTOMER')->get(['CUSTOMER', 'NAMACUST', 'kode_cus']);
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $drivers = Driver::orderBy('KODE')->get(['KODE', 'NAMA']);
        $rutes = \App\Models\Rute::orderBy('FROM')->get(['FROM', 'TO', 'RUTE', 'HARGA']);

        return view('expedisi.create', compact('customers', 'kendaraans', 'drivers', 'rutes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'NOSJ' => 'nullable|string|max:20|unique:expedisis,NOSJ',
            'TGLMUAT' => 'required|date',
            'TGLPO' => 'nullable|date',
            'NOPO' => 'nullable|string|max:20',
            'CUSTOMER_KODE' => 'nullable|string|max:20',
            'CUSTOMER' => 'nullable|string|max:30',
            'KENDARAAN' => 'nullable|string|max:20',
            'DRIVER' => 'nullable|string|max:20',
            'DRIVER2' => 'nullable|string|max:20',
            'JUMLAH' => 'nullable|numeric',
            'HARGA' => 'nullable|numeric',
            'TOTAL' => 'nullable|numeric',
            'PPN' => 'nullable|numeric',
            'GRAND' => 'nullable|numeric',
            'DISC' => 'nullable|numeric',
            'NDISC' => 'nullable|numeric',
            'NDISCAW' => 'nullable|numeric',
            'BAYAR' => 'nullable|numeric',
            'PIUTANG' => 'nullable|numeric',
            'JENIS' => 'required|string|size:3|default:EKS',
            'type' => 'nullable|string|max:1',
            'USER' => 'nullable|string|max:50',
            'USEREDIT' => 'nullable|string|max:50',
            'KETERANGAN' => 'nullable|string|max:500',
            'area_id' => 'nullable|integer',
            'area_name' => 'nullable|string|max:100',
        ], [
            'NOSJ.unique' => 'No Surat Jalan sudah terdaftar.',
            'TGLMUAT.required' => 'Tanggal muat wajib diisi.',
        ]);

        $validated['JENIS'] = 'EKS';
        $validated['USER'] = $request->user()->name ?? 'system';
        $validated['USEREDIT'] = $request->user()->name ?? 'system';

        DB::transaction(function () use ($validated) {
            $expedisi = Expedisi::create($validated);

            $piutang = (float) ($validated['PIUTANG'] ?? 0);
            $bayar = (float) ($validated['BAYAR'] ?? 0);
            $grand = (float) ($validated['GRAND'] ?? 0);

            if ($piutang > 0 || $bayar > 0) {
                $this->createArh($expedisi, $validated);
            }

            if ($grand > 0) {
                $this->createJournal($expedisi, $validated);
            }
        });

        return redirect()
            ->route('expedisi.index')
            ->with('success', 'Ekspedisi berhasil disimpan.');
    }

    public function show(Expedisi $expedisi)
    {
        return view('expedisi.show', compact('expedisi'));
    }

    public function edit(Expedisi $expedisi)
    {
        $customers = Mcustomer::orderBy('CUSTOMER')->get(['CUSTOMER', 'NAMACUST', 'kode_cus']);
        $kendaraans = Kendaraan::orderBy('KODE')->get(['KODE', 'NAMA']);
        $drivers = Driver::orderBy('KODE')->get(['KODE', 'NAMA']);
        $rutes = \App\Models\Rute::orderBy('FROM')->get(['FROM', 'TO', 'RUTE', 'HARGA']);

        return view('expedisi.edit', compact('expedisi', 'customers', 'kendaraans', 'drivers', 'rutes'));
    }

    public function update(Request $request, Expedisi $expedisi)
    {
        $validated = $request->validate([
            'NOSJ' => ['nullable', 'string', 'max:20', Rule::unique('expedisis', 'NOSJ')->ignore($expedisi->id)],
            'TGLMUAT' => 'required|date',
            'TGLPO' => 'nullable|date',
            'NOPO' => 'nullable|string|max:20',
            'CUSTOMER_KODE' => 'nullable|string|max:20',
            'CUSTOMER' => 'nullable|string|max:30',
            'KENDARAAN' => 'nullable|string|max:20',
            'DRIVER' => 'nullable|string|max:20',
            'DRIVER2' => 'nullable|string|max:20',
            'JUMLAH' => 'nullable|numeric',
            'HARGA' => 'nullable|numeric',
            'TOTAL' => 'nullable|numeric',
            'PPN' => 'nullable|numeric',
            'GRAND' => 'nullable|numeric',
            'DISC' => 'nullable|numeric',
            'NDISC' => 'nullable|numeric',
            'NDISCAW' => 'nullable|numeric',
            'BAYAR' => 'nullable|numeric',
            'PIUTANG' => 'nullable|numeric',
            'JENIS' => 'nullable|string|max:3',
            'type' => 'nullable|string|max:1',
            'USEREDIT' => 'nullable|string|max:50',
            'KETERANGAN' => 'nullable|string|max:500',
            'area_id' => 'nullable|integer',
            'area_name' => 'nullable|string|max:100',
        ]);

        $validated['USEREDIT'] = $request->user()->name ?? 'system';

        $expedisi->update($validated);

        if ((float) ($validated['GRAND'] ?? 0) > 0) {
            $this->updateJournal($expedisi, $validated);
        }

        return redirect()
            ->route('expedisi.index')
            ->with('success', 'Ekspedisi berhasil diperbarui.');
    }

    public function destroy(Expedisi $expedisi)
    {
        if ($expedisi->INVOICE) {
            return redirect()
                ->route('expedisi.index')
                ->with('error', 'Ekspedisi yang sudah diinvoice tidak dapat dihapus.');
        }

        $expedisi->delete();

        return redirect()
            ->route('expedisi.index')
            ->with('success', 'Ekspedisi berhasil dihapus.');
    }

    public function getDataCustomer(Request $request)
    {
        $search = $request->input('q');
        $customers = Mcustomer::query()
            ->when($search, fn($q) => $q->where('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('NAMACUST', 'like', "%{$search}%"))
            ->limit(10)
            ->get(['CUSTOMER', 'NAMACUST', 'kode_cus', 'ALAMAT1', 'TELEPON']);

        return response()->json($customers);
    }

    public function getRuteMuat(Request $request, $kodedap = null)
    {
        $rutes = \App\Models\Rute::query()
            ->when($kodedap, fn($q) => $q->where('FROM', $kodedap))
            ->limit(50)
            ->get(['FROM', 'TO', 'RUTE', 'HARGA']);

        return response()->json($rutes);
    }

    public function getKmTerakhir(Request $request, $kendaraan = null)
    {
        $km = Expedisi::query()
            ->where('KENDARAAN', $kendaraan)
            ->orderBy('TGLMUAT', 'desc')
            ->value('JUMLAH');

        return response()->json(['km_terakhir' => $km ?? 0]);
    }

    public function printSuratJalan(Expedisi $expedisi)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('expedisi.pdf.surjal', compact('expedisi'));

        return $pdf->stream('surat-jalan-' . $expedisi->NOSJ . '.pdf', ['stream' => true]);
    }

    public function pdfMuat(Request $request, $nomuat = null)
    {
        $expedisis = Expedisi::where('NOSJ', 'like', "%{$request->input('nosj_filter', '')}%")
            ->whereNotNull('NOSJ')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('expedisi.pdf.muat', compact('expedisis'));

        return $pdf->stream('muat.pdf', ['stream' => true]);
    }

    protected function createArh(Expedisi $expedisi, array $validated): void
    {
        Arh::create([
            'NOFAKTUR' => $validated['INVOICE'] ?? $validated['NOSJ'],
            'TGLFAKTUR' => $validated['TGLINVOICE'] ?? $validated['TGLMUAT'] ?? now(),
            'TGLJT' => $validated['TGLJT'] ?? null,
            'CUSTOMER' => $validated['CUSTOMER'],
            'SALES' => $validated['USER'] ?? null,
            'AREA' => $validated['area_name'] ?? null,
            'DIVISI' => null,
            'PIUTANG' => $validated['PIUTANG'] ?? 0,
            'BAYAR' => $validated['BAYAR'] ?? 0,
            'RETUR' => 0,
            'DISCOUNT' => $validated['NDISCAW'] ?? 0,
            'SALDO' => ($validated['PIUTANG'] ?? 0) - ($validated['BAYAR'] ?? 0),
            'CABANG' => $validated['area_name'] ?? null,
            'KETERANGAN' => $validated['KETERANGAN'] ?? null,
            'STATUS' => 'OPEN',
            'SOURCE' => 'EXPEDITESI',
            'USER' => $validated['USER'] ?? null,
            'USER_UPDATE' => $validated['USEREDIT'] ?? null,
        ]);
    }

    protected function createJournal(Expedisi $expedisi, array $validated): void
    {
        $journalService = app(JournalService::class);
        $noJurnal = $journalService->generateJournalNumber('JURNAL', $validated['TGLMUAT']);
        $grand = (float) ($validated['GRAND'] ?? 0);

        $incomeCoaId = AccountMap::id('income_sales') ?? 4;
        $arCoaId = AccountMap::id('ar_usaha') ?? 11;

        if ($grand > 0) {
            $jurnal = $journalService->createJournal(
                $noJurnal,
                $validated['TGLMUAT'],
                "Penjualan Expedisi {$expedisi->NOSJ}",
                [
                    ['coa_id' => $incomeCoaId, 'debit' => 0, 'kredit' => $grand, 'deskripsi' => 'Penjualan'],
                    ['coa_id' => $arCoaId, 'debit' => $grand, 'kredit' => 0, 'deskripsi' => 'Piutang Usaha'],
                ],
                null,
                null,
                'Penjualan',
                'Penjualan'
            );

            $expedisi->update([
                'NOJURNAL' => $noJurnal,
                'jurnal_id' => $jurnal->id,
            ]);

            if ($expedisi->INVOICE) {
                $expedisi->arh()?->update(['NOJURNAL' => $noJurnal]);
            }
        }
    }

    protected function updateJournal(Expedisi $expedisi, array $validated): void
    {
        // Journal sudah ada — tidak create journal baru untuk update
        // Yang penting: update NOJURNAL reference jika belum ada
        if (! $expedisi->NOJURNAL && (float) ($validated['GRAND'] ?? 0) > 0) {
            $this->createJournal($expedisi, $validated);
        }
    }
}
