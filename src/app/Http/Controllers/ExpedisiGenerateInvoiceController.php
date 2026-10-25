<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expedisi;
use App\Models\Arh;
use App\Models\Mcustomer;
use App\Models\Kwitansi;
use App\Services\PeriodLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExpedisiGenerateInvoiceController extends Controller
{
    public function __construct(private readonly PeriodLockService $periodLocks)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('expedisi-invoice.generate');
    }

    public function create()
    {
        return redirect()->route('expedisi-generate-invoice.index');
    }

    public function getDataInvoiceGen(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50'],
        ]);
        if (($filters['date_from'] ?? null) && ($filters['date_to'] ?? null)
            && $filters['date_to'] < $filters['date_from']) {
            return response()->json(['message' => 'Tanggal sampai tidak boleh sebelum tanggal dari.'], 422);
        }

        $query = Expedisi::query()
            ->where('JENIS', 'EKS')
            ->whereNotNull('NOSJ')->where('NOSJ', '<>', '')
            ->whereNotNull('NOMUAT')->where('NOMUAT', '<>', '')
            ->where('GRAND', '>', 0)
            ->where(fn ($q) => $q->whereNull('INVOICE')->orWhere('INVOICE', ''))
            ->where(function ($q) {
                $q->where(fn ($single) => $single->whereNull('GB')->orWhere('GB', ''))
                    ->orWhere(function ($group) {
                        $group->whereNotNull('GB')->where('GB', '<>', '')
                            ->where('GRAND', '>', 0)
                            ->whereNotExists(function ($invoiced) {
                                $invoiced->selectRaw('1')->from('expedisis as invoice_member')
                                    ->whereColumn('invoice_member.GB', 'expedisis.GB')
                                    ->where('invoice_member.JENIS', 'EKS')
                                    ->whereNotNull('invoice_member.INVOICE')
                                    ->where('invoice_member.INVOICE', '<>', '');
                            })
                            ->whereNotExists(function ($otherMaster) {
                                $otherMaster->selectRaw('1')->from('expedisis as candidate_master')
                                    ->whereColumn('candidate_master.GB', 'expedisis.GB')
                                    ->where('candidate_master.JENIS', 'EKS')
                                    ->where(function ($invoice) {
                                        $invoice->whereNull('candidate_master.INVOICE')->orWhere('candidate_master.INVOICE', '');
                                    })
                                    ->where('candidate_master.GRAND', '>', 0)
                                    ->whereRaw('(candidate_master.GRAND > expedisis.GRAND OR (candidate_master.GRAND = expedisis.GRAND AND candidate_master.id < expedisis.id))');
                            });
                    });
            })
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('TGLMUAT', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('TGLMUAT', '<=', $date))
            ->when(trim((string) ($filters['q'] ?? '')) !== '', function ($q) use ($filters) {
                $search = '%' . trim($filters['q']) . '%';
                $q->where(fn ($match) => $match->where('NOMUAT', 'like', $search)
                    ->orWhere('NOSJ', 'like', $search)
                    ->orWhere('GB', 'like', $search)
                    ->orWhere('CUSTOMER', 'like', $search)
                    ->orWhereExists(function ($members) use ($search) {
                        $members->selectRaw('1')->from('expedisis as group_search')
                            ->whereColumn('group_search.GB', 'expedisis.GB')
                            ->whereNotNull('expedisis.GB')
                            ->where('group_search.JENIS', 'EKS')
                            ->where(fn ($fields) => $fields->where('group_search.NOMUAT', 'like', $search)
                                ->orWhere('group_search.NOSJ', 'like', $search)
                                ->orWhere('group_search.CUSTOMER', 'like', $search));
                    }));
            })
            ->select([
                'id', 'NOMUAT', 'TGLMUAT', 'NOSJ', 'GB', 'CUSTOMER',
                'CUSTOMER_KODE', 'NAMA_KENDARAAN', 'GRAND',
            ])
            ->addSelect([
                'GROUP_SIZE' => DB::table('expedisis as group_members')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('group_members.GB', 'expedisis.GB')
                    ->whereColumn('group_members.JENIS', 'expedisis.JENIS'),
            ]);

        $perPage = (int) ($filters['per_page'] ?? 25);
        $page = (int) ($filters['page'] ?? 1);
        $total = (clone $query)->count();
        $rows = $query->orderByDesc('TGLMUAT')->orderByDesc('id')
            ->forPage($page, $perPage)
            ->get();
        $groupNosjs = Expedisi::query()
            ->where('JENIS', 'EKS')
            ->whereIn('GB', $rows->pluck('GB')->filter()->unique())
            ->orderBy('NOSJ')
            ->get(['GB', 'NOSJ'])
            ->groupBy('GB')
            ->map(fn ($members) => $members->pluck('NOSJ')->map(fn ($noSj) => trim((string) $noSj))->all());
        $customerCodes = $rows->pluck('CUSTOMER_KODE')->map(fn ($code) => trim((string) $code))->filter()->unique();
        $creditTerms = [];
        Mcustomer::whereIn('CUSTOMER', $customerCodes)->get(['CUSTOMER', 'TOPKREDIT'])
            ->each(function (Mcustomer $customer) use (&$creditTerms) {
                $creditTerms[trim((string) $customer->CUSTOMER)] = (int) $customer->TOPKREDIT;
            });
        Mcustomer::whereIn('kode_cus', $customerCodes)->get(['kode_cus', 'TOPKREDIT'])
            ->each(function (Mcustomer $customer) use (&$creditTerms) {
                $code = trim((string) $customer->kode_cus);
                $creditTerms[$code] ??= (int) $customer->TOPKREDIT;
            });

        $data = $rows->map(function (Expedisi $row) use ($groupNosjs, $creditTerms) {
            return [
                'NOSJ' => trim((string) $row->NOSJ),
                'NOMUAT' => trim((string) $row->NOMUAT),
                'TGLMUAT' => $row->TGLMUAT?->format('d-m-Y') ?? '-',
                'GB' => trim((string) $row->GB),
                'CUSTOMER' => trim((string) $row->CUSTOMER),
                'NAMA_KENDARAAN' => trim((string) $row->NAMA_KENDARAAN),
                'GRAND' => (float) $row->GRAND,
                'GROUP_SIZE' => $row->GB ? (int) $row->GROUP_SIZE : 1,
                'GROUP_SJS' => $row->GB ? ($groupNosjs[$row->GB] ?? []) : [trim((string) $row->NOSJ)],
                'TOPKREDIT' => $creditTerms[trim((string) $row->CUSTOMER_KODE)] ?? 0,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    public function customerLookup(Request $request)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim((string) ($filters['q'] ?? ''));

        $customers = Mcustomer::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('NAMACUST', 'like', "%{$search}%")
                ->orWhere('kode_cus', 'like', "%{$search}%")))
            ->orderBy('CUSTOMER')
            ->limit(20)
            ->get(['CUSTOMER', 'NAMACUST'])
            ->map(fn (Mcustomer $customer) => [
                'code' => trim((string) $customer->CUSTOMER),
                'name' => trim((string) $customer->NAMACUST),
            ]);

        return response()->json($customers);
    }

    public function prosesInvoiceStore(Request $request)
    {
        $validated = $request->validate([
            'INVOICE' => ['nullable', 'string', 'max:20', 'unique:arh,NOFAKTUR'],
            'selected_sj' => ['required', 'string', 'max:20'],
            'TGLFAKTUR' => ['required', 'date'],
            'TGLJT' => ['nullable', 'date'],
            'BAYAR' => ['nullable', 'numeric', 'min:0'],
            'KETERANGAN' => ['nullable', 'string', 'max:500'],
            'SALES' => ['nullable', 'string', 'max:50'],
        ]);
        if ($this->periodLocks->isLocked($validated['TGLFAKTUR'])) {
            return back()->withInput()->with('error', $this->periodLocks->lockedMessage($validated['TGLFAKTUR']));
        }
        try {
            $noInvoice = DB::transaction(function () use ($validated, $request) {
                $anchor = Expedisi::where('NOSJ', $validated['selected_sj'])
                    ->where('JENIS', 'EKS')->lockForUpdate()->first();
                if (!$anchor || blank($anchor->NOMUAT)) {
                    throw new \RuntimeException('Surat Jalan tidak ditemukan atau belum masuk Muat. Muat ulang daftar.');
                }

                $expedisis = filled($anchor->GB)
                    ? Expedisi::where('GB', $anchor->GB)->where('JENIS', 'EKS')->orderBy('id')->lockForUpdate()->get()
                    : collect([$anchor]);

                if ($expedisis->isEmpty() || $expedisis->contains(fn ($row) => filled($row->INVOICE))) {
                    throw new \RuntimeException('SJ atau grup GB sudah memiliki invoice. Muat ulang daftar.');
                }

                $customerCodes = $expedisis->pluck('CUSTOMER_KODE')->map(fn ($code) => trim((string) $code))->unique();
                if ($customerCodes->count() > 1 || $customerCodes->first() === '') {
                    throw new \RuntimeException('Customer dalam grup GB tidak sama. Periksa grup sebelum membuat invoice.');
                }

                $master = $expedisis->sort(function ($a, $b) {
                    return ((float) $b->GRAND <=> (float) $a->GRAND) ?: ($a->id <=> $b->id);
                })->first();
                $totalGrand = (float) ($master->GRAND ?? 0);
                if ($totalGrand <= 0) {
                    throw new \RuntimeException('Nilai invoice harus lebih besar dari nol. Periksa nilai Grand pada Surat Jalan.');
                }
                $bayar = round((float) ($validated['BAYAR'] ?? 0), 2);
                if ($bayar > $totalGrand) {
                    throw new \RuntimeException('Pembayaran saat generate tidak boleh melebihi nilai invoice.');
                }
                $piutang = max(0, $totalGrand - $bayar);

                $tanggal = $validated['TGLFAKTUR'];
                if ($this->periodLocks->isLocked($tanggal)) {
                    throw new \RuntimeException($this->periodLocks->lockedMessage($tanggal));
                }
                $customerCode = trim((string) $master->CUSTOMER_KODE);
                $customer = Mcustomer::query()
                    ->where(fn ($query) => $query->where('CUSTOMER', $customerCode)->orWhere('kode_cus', $customerCode))
                    ->orderByRaw('CASE WHEN CUSTOMER = ? THEN 0 ELSE 1 END', [$customerCode])
                    ->first();
                $jatuhTempo = $validated['TGLJT']
                    ?? \Carbon\Carbon::parse($tanggal)->addDays((int) ($customer->TOPKREDIT ?? 0))->toDateString();
                $noInvoice = trim((string) ($validated['INVOICE'] ?? ''));
                if ($noInvoice === '') {
                    $noInvoice = $this->generateInvoiceNumber();
                }

                if (Arh::where('NOFAKTUR', $noInvoice)->exists()
                    || Expedisi::where('INVOICE', $noInvoice)->exists()) {
                    throw new \RuntimeException("Nomor invoice {$noInvoice} sudah digunakan.");
                }

                $arh = Arh::create([
                    'NOFAKTUR' => $noInvoice,
                    'TGLFAKTUR' => $tanggal,
                    'TGLJT' => $jatuhTempo,
                    'CUSTOMER' => $master->CUSTOMER,
                    'SALES' => $validated['SALES'] ?? null,
                    'PIUTANG' => $totalGrand,
                    'BAYAR' => $bayar,
                    'SALDO' => $totalGrand,
                    'DISCOUNT' => $master->NDISC ?? 0,
                    'KETERANGAN' => ($validated['KETERANGAN'] ?? null) ?: 'INVOICE DARI EXPEDISI',
                    'STATUS' => $piutang > 0 ? 'OPEN' : 'PAID',
                    'TGLLUNAS' => $piutang > 0 ? null : $tanggal,
                    'SOURCE' => 'Expedisi',
                    'USER' => $request->user()->name,
                ]);

                $userInvoice = (string) ($request->user()->user_id ?? $request->user()->id)
                    . '-' . now()->format('d-m-Y H:i:s');
                $expedisis->each(function (Expedisi $expedisi) use ($noInvoice, $tanggal, $jatuhTempo, $master, $totalGrand, $userInvoice, $bayar, $piutang) {
                    $isMaster = $expedisi->is($master);
                    $expedisi->update([
                        'INVOICE' => $noInvoice,
                        'TGLINVOICE' => $tanggal,
                        'TGLJT' => $jatuhTempo,
                        'BAYAR' => $isMaster ? $bayar : 0,
                        'PIUTANG' => $isMaster ? $piutang : 0,
                        'STS' => 'INVOICE',
                        'USERINV' => $userInvoice,
                    ]);
                });

                return $noInvoice;
            });
        } catch (\RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            Log::error('Gagal membuat invoice expedisi.', ['exception' => $exception]);

            return back()->withInput()->with('error', 'Invoice gagal disimpan. Periksa data invoice lalu coba kembali.');
        }

        return redirect()->route('expedisi-invoice.show', $noInvoice)
            ->with('success', "Invoice {$noInvoice} berhasil dibuat.");
    }

    public function showInvoiceGabung($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->firstOrFail();
        $details = Expedisi::where('INVOICE', $noInvoice)->get();

        return response()->json(compact('invoice', 'details'));
    }

    public function indexEdit(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $invoices = Arh::where('SOURCE', 'Expedisi')
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('NOFAKTUR', 'like', "%{$search}%")
                ->orWhere('CUSTOMER', 'like', "%{$search}%")))
            ->orderByDesc('TGLFAKTUR')
            ->paginate(20)
            ->withQueryString();

        return view('expedisi-invoice.edit', compact('invoices', 'search'));
    }

    public function tableEdit()
    {
        $search = trim((string) request('search'));
        $invoices = Arh::where('SOURCE', 'Expedisi')
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('NOFAKTUR', 'like', "%{$search}%")
                ->orWhere('CUSTOMER', 'like', "%{$search}%")))
            ->paginate(20)
            ->withQueryString();

        return response()->json($invoices);
    }

    public function showEditInvoice($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->where('SOURCE', 'Expedisi')->firstOrFail();
        try {
            $this->ensureInvoiceEditable($invoice);
        } catch (\RuntimeException $exception) {
            return redirect()->route('expedisi-invoice-gen.index')->with('error', $exception->getMessage());
        }
        $details = Expedisi::where('INVOICE', $noInvoice)->get();

        return view('expedisi-invoice.show-edit', compact('invoice', 'details'));
    }

    public function updateEditInvoice(Request $request, $noInvoice)
    {
        $validated = $request->validate([
            'TGLFAKTUR' => ['required', 'date'],
            'TGLJT' => ['nullable', 'date', 'after_or_equal:TGLFAKTUR'],
            'KETERANGAN' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($validated, $noInvoice, $request) {
                $invoice = Arh::where('NOFAKTUR', $noInvoice)
                    ->where('SOURCE', 'Expedisi')->lockForUpdate()->firstOrFail();
                $this->ensureInvoiceEditable($invoice);
                if ($this->periodLocks->isLocked($validated['TGLFAKTUR'])) {
                    throw new \RuntimeException($this->periodLocks->lockedMessage($validated['TGLFAKTUR']));
                }
                if (blank($validated['KETERANGAN'] ?? null)) {
                    $validated['KETERANGAN'] = 'INVOICE DARI EXPEDISI';
                }

                $invoice->update([
                    'TGLFAKTUR' => $validated['TGLFAKTUR'],
                    'TGLJT' => $validated['TGLJT'] ?? null,
                    'KETERANGAN' => $validated['KETERANGAN'],
                    'USER_UPDATE' => $request->user()->name,
                ]);
                Expedisi::where('INVOICE', $noInvoice)->lockForUpdate()->update([
                    'TGLINVOICE' => $validated['TGLFAKTUR'],
                    'TGLJT' => $validated['TGLJT'] ?? null,
                    'KETERANGAN' => $validated['KETERANGAN'],
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            Log::error('Gagal memperbarui invoice expedisi.', ['invoice' => $noInvoice, 'exception' => $exception]);

            return back()->withInput()->with('error', 'Invoice gagal diperbarui. Periksa data invoice lalu coba kembali.');
        }

        return back()->with('success', 'Invoice berhasil diperbarui. Tidak ada jurnal yang dibuat.');
    }

    private function ensureInvoiceEditable(Arh $invoice): void
    {
        if ((float) $invoice->BAYAR > 0 || $invoice->STATUS === 'PAID'
            || Kwitansi::where('NOFAKTUR', $invoice->NOFAKTUR)->exists()) {
            throw new \RuntimeException('Invoice yang sudah memiliki pembayaran atau kwitansi tidak dapat diedit.');
        }
        if ($this->periodLocks->isLocked($invoice->TGLFAKTUR)) {
            throw new \RuntimeException($this->periodLocks->lockedMessage($invoice->TGLFAKTUR));
        }
        if (filled($invoice->NOJURNAL) || filled($invoice->jurnal_id)) {
            throw new \RuntimeException('Invoice sudah terhubung ke jurnal. Perubahan harus ditangani melalui prosedur jurnal.');
        }
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'tanggal_dari' => ['required', 'date'],
            'tanggal_sampai' => ['required', 'date', 'after_or_equal:tanggal_dari'],
            'filter_inv_gen' => ['required', 'in:belum,sudah'],
            'customer_invoice_exp' => ['nullable', 'string', 'max:30'],
        ]);

        $filename = 'laporan_invoice_expedisi_' . $filters['filter_inv_gen'] . '_'
            . $filters['tanggal_dari'] . '_sd_' . $filters['tanggal_sampai'] . '.xlsx';

        return Excel::download(
            new \App\Exports\ExpedisiInvoiceGenerateExport(
                $filters['tanggal_dari'],
                $filters['tanggal_sampai'],
                $filters['filter_inv_gen'],
                $filters['customer_invoice_exp'] ?? null,
            ),
            $filename,
        );
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

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'FJO' . now()->format('Y');
        $latestArh = Arh::where('NOFAKTUR', 'like', $prefix . '%')
            ->lockForUpdate()->orderByDesc('NOFAKTUR')->value('NOFAKTUR');
        $latestExpedisi = Expedisi::where('INVOICE', 'like', $prefix . '%')
            ->lockForUpdate()->orderByDesc('INVOICE')->value('INVOICE');
        $lastNumber = max(
            (int) substr((string) $latestArh, -6),
            (int) substr((string) $latestExpedisi, -6),
        );

        return $prefix . str_pad((string) ($lastNumber + 1), 6, '0', STR_PAD_LEFT);
    }
}
