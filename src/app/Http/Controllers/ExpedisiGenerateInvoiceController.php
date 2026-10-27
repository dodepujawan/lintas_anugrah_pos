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
        return view('expedisi-invoice.generate', [
            'invoicePreview' => $this->nextInvoiceNumber(),
        ]);
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
                'CUSTOMER_KODE', 'NAMA_KENDARAAN', 'JUMLAH', 'UNIT', 'HARGA',
                'TOTAL', 'DISC', 'NDISC', 'DC', 'PPN', 'GRAND', 'TOP',
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
        $groupSummaries = Expedisi::query()
            ->where('JENIS', 'EKS')
            ->whereIn('GB', $rows->pluck('GB')->filter()->unique())
            ->orderBy('NOSJ')
            ->get(['GB', 'NOSJ', 'JUMLAH'])
            ->groupBy('GB')
            ->map(fn ($members) => [
                'SJS' => $members->pluck('NOSJ')->map(fn ($noSj) => trim((string) $noSj))->all(),
                'JUMLAH' => (float) $members->sum('JUMLAH'),
            ]);
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

        $data = $rows->map(function (Expedisi $row) use ($groupSummaries, $creditTerms) {
            $summary = $row->GB ? ($groupSummaries[$row->GB] ?? null) : null;

            return [
                'NOSJ' => trim((string) $row->NOSJ),
                'NOMUAT' => trim((string) $row->NOMUAT),
                'TGLMUAT' => $row->TGLMUAT?->format('d-m-Y') ?? '-',
                'GB' => trim((string) $row->GB),
                'CUSTOMER' => trim((string) $row->CUSTOMER),
                'NAMA_KENDARAAN' => trim((string) $row->NAMA_KENDARAAN),
                'JUMLAH' => $summary['JUMLAH'] ?? (float) $row->JUMLAH,
                'UNIT' => trim((string) $row->UNIT),
                'HARGA' => (float) $row->HARGA,
                'SUBTOTAL' => (float) $row->HARGA,
                'TOTAL' => (float) $row->TOTAL,
                'DISC' => (float) $row->DISC,
                'DISC_AMOUNT' => (float) $row->NDISC,
                'DCHARGE' => (float) $row->DC,
                'DPP' => (float) $row->TOTAL - (float) $row->NDISC,
                'PPN' => (float) $row->PPN,
                'GRAND' => (float) $row->GRAND,
                'GROUP_SIZE' => $row->GB ? (int) $row->GROUP_SIZE : 1,
                'GROUP_SJS' => $summary['SJS'] ?? [trim((string) $row->NOSJ)],
                'TOP' => (int) $row->TOP,
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
            'selected_sj' => ['required', 'string', 'max:20'],
            'TGLFAKTUR' => ['required', 'date'],
            'TGLJT' => ['nullable', 'date'],
            'TOP' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'BAYAR' => ['nullable', 'numeric', 'min:0'],
            'KETERANGAN' => ['nullable', 'string', 'max:500'],
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
                $top = (int) ($validated['TOP'] ?? $customer->TOPKREDIT ?? 0);
                $jatuhTempo = $validated['TGLJT']
                    ?? \Carbon\Carbon::parse($tanggal)->addDays($top)->toDateString();
                $noInvoice = $this->nextInvoiceNumber(lock: true);

                if (Arh::where('NOFAKTUR', $noInvoice)->exists()
                    || Expedisi::where('INVOICE', $noInvoice)->exists()) {
                    throw new \RuntimeException("Nomor invoice {$noInvoice} sudah digunakan.");
                }

                $arh = Arh::create([
                    'NOFAKTUR' => $noInvoice,
                    'TGLFAKTUR' => $tanggal,
                    'TGLJT' => $jatuhTempo,
                    'CUSTOMER' => $master->CUSTOMER,
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
                $expedisis->each(function (Expedisi $expedisi) use ($noInvoice, $tanggal, $jatuhTempo, $master, $totalGrand, $userInvoice, $bayar, $piutang, $top) {
                    $isMaster = $expedisi->is($master);
                    $expedisi->update([
                        'INVOICE' => $noInvoice,
                        'TGLINVOICE' => $tanggal,
                        'TGLJT' => $jatuhTempo,
                        'TOP' => $top,
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
            ->with('success', "Invoice {$noInvoice} berhasil dibuat.")
            ->with('autoPrintInvoice', $noInvoice);
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
        $dari = $request->input('tanggal_dari');
        $sampai = $request->input('tanggal_sampai');
        $invoices = Arh::where('SOURCE', 'Expedisi')
            ->when($dari, fn ($query) => $query->whereDate('TGLFAKTUR', '>=', $dari))
            ->when($sampai, fn ($query) => $query->whereDate('TGLFAKTUR', '<=', $sampai))
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('NOFAKTUR', 'like', "%{$search}%")
                ->orWhere('CUSTOMER', 'like', "%{$search}%")))
            ->orderByDesc('TGLFAKTUR')
            ->paginate(20)
            ->withQueryString();

        return view('expedisi-invoice.edit', compact('invoices', 'search'));
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

    public function showEditInvoiceJson($noInvoice)
    {
        $invoice = Arh::where('NOFAKTUR', $noInvoice)->where('SOURCE', 'Expedisi')->first();
        if (! $invoice) {
            return response()->json(['status' => false, 'message' => 'Invoice tidak ditemukan'], 404);
        }
        try {
            $this->ensureInvoiceEditable($invoice);
        } catch (\RuntimeException $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
        $rows = Expedisi::where('INVOICE', $noInvoice)->orderBy('NOSJ')->get();
        $master = $rows->firstWhere('GRAND', '>', 0) ?? $rows->first();
        if (! $master) {
            return response()->json(['status' => false, 'message' => 'Data master invoice tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => true,
            'master' => [
                'invoice' => $master->INVOICE,
                'customer_kode' => $master->CUSTOMER_KODE,
                'customer' => $master->CUSTOMER,
                'kendaraan' => $master->NAMA_KENDARAAN ?? '',
                'driver' => $master->DRIVER ?? '',
                'nomuat' => $master->NOMUAT ?? '',
                'tgl_invoice' => $master->TGLINVOICE?->format('Y-m-d'),
                'tgl_jt' => $invoice->TGLJT?->format('Y-m-d') ?? $master->TGLJT?->format('Y-m-d'),
                'wilayah' => $master->WILAYAH ?? '',
                'item' => $master->PESANAN ?? '',
                'rute' => $master->rute ?? '',
                'jumlah' => (float) $master->JUMLAH,
                'harga' => (float) $master->HARGA,
                'total' => (float) $master->TOTAL,
                'diskon' => (float) $master->DISC,
                'del_charge' => (float) $master->DC,
                'ppn' => (float) $master->PPN,
                'grand' => (float) $master->GRAND,
                'bayar' => (float) $master->BAYAR,
                'piutang' => (float) ($master->GRAND ?? 0) - (float) ($master->BAYAR ?? 0),
                'keterangan' => $master->KETERANGAN ?? $invoice->KETERANGAN ?? '',
            ],
            'details' => $rows->map(fn ($row) => [
                'nosj' => $row->NOSJ,
                'jumlah' => (float) $row->JUMLAH,
                'hargaaw' => (float) $row->hargaaw,
                'total' => (float) $row->TOTAL,
            ])->values(),
        ]);
    }

    public function updateEditInvoice(Request $request, $noInvoice)
    {
        $validated = $request->validate([
            'TGLFAKTUR' => ['required', 'date'],
            'TGLJT' => ['nullable', 'date', 'after_or_equal:TGLFAKTUR'],
            'KETERANGAN' => ['nullable', 'string', 'max:500'],
            'item' => ['nullable', 'string', 'max:255'],
            'rute' => ['nullable', 'string', 'max:255'],
            'jumlah' => ['nullable', 'numeric', 'min:0'],
            'harga' => ['nullable', 'numeric', 'min:0'],
            'disc' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'del_charge' => ['nullable', 'numeric', 'min:0'],
            'ppn' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bayar' => ['nullable', 'numeric', 'min:0'],
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

                $rows = Expedisi::where('INVOICE', $noInvoice)->lockForUpdate()->get();
                $master = $rows->firstWhere('GRAND', '>', 0) ?? $rows->first();
                if (! $master) {
                    throw new \RuntimeException('Data master invoice tidak ditemukan');
                }

                $hasFinancial = array_key_exists('jumlah', $validated) || array_key_exists('harga', $validated)
                    || array_key_exists('disc', $validated) || array_key_exists('del_charge', $validated)
                    || array_key_exists('ppn', $validated) || array_key_exists('bayar', $validated);

                if ($hasFinancial) {
                    $jumlah = (float) ($validated['jumlah'] ?? $master->JUMLAH ?? 0);
                    $harga = (float) ($validated['harga'] ?? $master->HARGA ?? 0);
                    $discPersen = (float) ($validated['disc'] ?? $master->DISC ?? 0);
                    $delCharge = (float) ($validated['del_charge'] ?? $master->DC ?? 0);
                    $ppnPersen = (float) ($validated['ppn'] ?? $master->PPN ?? 0);
                    $bayar = (float) ($validated['bayar'] ?? $master->BAYAR ?? 0);

                    $subTotal = round($jumlah * $harga);
                    $ndisc = round($subTotal * ($discPersen / 100));
                    $total = round($subTotal - $ndisc);
                    $ppnNominal = round($total * ($ppnPersen / 100));
                    $grand = round($total + $ppnNominal + $delCharge);
                    if ($grand < $bayar) {
                        throw new \RuntimeException('Grand tidak boleh lebih kecil dari pembayaran yang sudah diterima');
                    }
                    $piutang = round($grand - $bayar);

                    $master->update([
                        'PESANAN' => $validated['item'] ?? $master->PESANAN,
                        'rute' => $validated['rute'] ?? $master->rute,
                        'JUMLAH' => $jumlah,
                        'HARGA' => $harga,
                        'DISC' => $discPersen,
                        'NDISC' => $ndisc,
                        'DC' => $delCharge,
                        'TOTAL' => $total,
                        'PPN' => $ppnPersen,
                        'GRAND' => $grand,
                        'BAYAR' => $bayar,
                        'PIUTANG' => $piutang,
                        'TGLINVOICE' => $validated['TGLFAKTUR'],
                        'TGLJT' => $validated['TGLJT'] ?? $master->TGLJT,
                        'KETERANGAN' => $validated['KETERANGAN'],
                        'USEREDIT' => $request->user()->name,
                    ]);

                    $arh = Arh::where('NOFAKTUR', $noInvoice)->first();
                    $arhData = [
                        'TGLFAKTUR' => $validated['TGLFAKTUR'],
                        'TGLJT' => $validated['TGLJT'] ?? $arh?->TGLJT,
                        'CUSTOMER' => $master->CUSTOMER,
                        'PIUTANG' => $piutang,
                        'BAYAR' => $bayar,
                        'SALDO' => $grand,
                        'DISCOUNT' => $ndisc,
                        'KETERANGAN' => $validated['KETERANGAN'],
                        'STATUS' => $piutang > 0 ? 'OPEN' : 'PAID',
                        'TGLLUNAS' => $piutang > 0 ? null : $validated['TGLFAKTUR'],
                    ];
                    if (! $arh) {
                        Arh::create($arhData + ['NOFAKTUR' => $noInvoice, 'SOURCE' => 'Expedisi', 'USER' => $request->user()->name ?? 'SYSTEM']);
                    } else {
                        $arh->update($arhData + ['USER_UPDATE' => $request->user()->name ?? 'SYSTEM']);
                    }

                    // Sinkron kwitansi bila sudah ada (setara src_old: nilai kwitansi mengikuti nilai invoice)
                    $kwitansi = Kwitansi::where('FDOK_TRANS', $noInvoice)->first();
                    if ($kwitansi) {
                        $kwitansi->update([
                            'FNIL_DOK' => $grand,
                            'TOTAL' => $total,
                            'PPN' => $ppnPersen,
                            'DISC' => $discPersen,
                            'NDISC' => $ndisc,
                            'USERINPUT' => $request->user()->name ?? 'SYSTEM',
                        ]);
                    }
                } else {
                    // Jalur lama (metadata only) — tetap dipakai test
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
                }
            });
        } catch (\RuntimeException $exception) {
            if ($request->wantsJson()) {
                return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
            }

            return back()->withInput()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            Log::error('Gagal memperbarui invoice expedisi.', ['invoice' => $noInvoice, 'exception' => $exception]);
            if ($request->wantsJson()) {
                return response()->json(['status' => false, 'message' => 'Invoice gagal diperbarui. Periksa data lalu coba kembali.'], 500);
            }

            return back()->withInput()->with('error', 'Invoice gagal diperbarui. Periksa data invoice lalu coba kembali.');
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'Invoice berhasil diperbarui.']);
        }

        return back()->with('success', 'Invoice berhasil diperbarui. Tidak ada jurnal yang dibuat.');
    }

    private function ensureInvoiceEditable(Arh $invoice): void
    {
        if ((float) $invoice->BAYAR > 0) {
            throw new \RuntimeException('Invoice sudah memiliki pembayaran. Silakan hubungi admin.');
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
        $rows = Expedisi::where('INVOICE', $noInvoice)->orderBy('NOSJ')->get();
        abort_if($rows->isEmpty(), 404);

        $master = $rows->firstWhere('GRAND', '>', 0) ?? $rows->first();
        $customer = Mcustomer::where('CUSTOMER', $master->CUSTOMER_KODE)
            ->orWhere('kode_cus', $master->CUSTOMER_KODE)
            ->first();
        $rekening = \App\Models\Rekening::where('AKTIF', 1)->first();

        $lineWidth = 130;
        $leftMargin = 3;
        $linesPerPage = 30;
        $half = intdiv($lineWidth, 2);
        $esc = "\x1B";
        $boldOn = $esc . 'E';
        $boldOff = $esc . 'F';
        $doubleWidthOn = $esc . 'W' . "\x01";
        $doubleWidthOff = $esc . 'W' . "\x00";
        $kepada = trim(preg_replace('/[\r\n]+/', ' ', strtoupper($customer->NAMACUST ?? '-')));
        $up = trim(preg_replace('/[\r\n]+/', ' ', strtoupper($customer->KONTAK ?? '-')));
        $alamat = trim(preg_replace('/[\r\n]+/', ' ', strtoupper($customer->ALAMAT1 ?? '-')));
        $bank = $rekening->BANK ?? '-';
        $norek = $rekening->NOREK ?? '-';
        $namaRekening = $rekening->NAMA ?? '-';
        $dibayar = (float) ($master->BAYAR ?? 0);
        $saldo = (float) ($master->PIUTANG ?? 0);
        $nomorSj = $rows->pluck('NOSJ')->filter(fn ($value) => trim((string) $value) !== '')->unique()->implode(',');
        $judulKiri = 'INVOICE';
        $judulKanan = 'PT.LINTAS ANUGERAH SEJATI';
        $header = [
            $boldOn . $doubleWidthOn . $judulKiri . $doubleWidthOff . $boldOff
                . str_repeat(' ', max(0, $half - strlen($judulKiri) * 2))
                . str_repeat(' ', max(0, $half - strlen($judulKanan) * 2))
                . $boldOn . $doubleWidthOn . $judulKanan . $doubleWidthOff . $boldOff,
            str_repeat('=', $lineWidth),
            sprintf('%-65s%65s', 'NOMOR    : ' . $master->INVOICE, 'TANGGAL  : ' . ($master->TGLINVOICE ? date('d-m-Y', strtotime($master->TGLINVOICE)) : '-')),
            sprintf('%-65s%65s', 'NOMOR SJ : ' . $nomorSj, $master->TGLJT ? 'TGL JT   : ' . date('d-m-Y', strtotime($master->TGLJT)) : ''),
            sprintf('%-65s%65s', 'KEPADA   : ' . $kepada, 'CETAK    : ' . now()->format('d-m-Y H:i')),
            sprintf('%-65s%65s', 'UP       : ' . $up, ''),
        ];
        foreach (explode("\n", wordwrap($alamat, 56, "\n")) as $index => $alamatRow) {
            $header[] = $index === 0 ? 'ALAMAT   : ' . $alamatRow : str_repeat(' ', 10) . $alamatRow;
        }
        $header[] = '';
        $header[] = 'JUMLAH SJ : ' . $rows->count();
        $header[] = str_repeat('=', $lineWidth);

        $tableHeader = [
            sprintf('%-4s %-48s %13s %14s %8s %18s %10s', 'NO', 'NAMA BARANG / PESANAN', 'JUMLAH UNIT', 'HARGA @', 'DISC %', 'SUB TOTAL', 'PAJAK %'),
            str_repeat('-', $lineWidth),
        ];
        $vehicle = trim((string) ($master->NAMA_KENDARAAN ?? ''));
        $plate = trim((string) ($master->PLAT_NOMOR ?? ''));
        $vehicleText = $vehicle !== '' ? $vehicle : '-';
        if ($plate !== '') $vehicleText .= ' (' . $plate . ')';
        $description = trim((string) ($master->PESANANGB ?? ''));
        if ($description === '') $description = trim((string) ($master->KETERANGAN ?? ''));
        $description = $description !== '' ? $description : '-';
        $quantity = (float) $rows->sum('JUMLAH');
        $quantityText = floor($quantity) == $quantity
            ? number_format($quantity, 0, ',', '.')
            : rtrim(rtrim(number_format($quantity, 3, ',', '.'), '0'), ',');
        $unit = strtoupper(trim((string) ($master->UNIT ?? '')) ?: 'KG');
        $detail = sprintf(
            '%-4s %-48s %13s %14s %8s %18s %10s',
            '1',
            substr($vehicleText . ' - ' . $description, 0, 48),
            $quantityText . ' ' . $unit,
            number_format((float) ($master->HARGA ?? 0), 0, ',', '.'),
            number_format((float) ($master->DISC ?? 0), 2, ',', '.'),
            number_format((float) $rows->sum('TOTAL'), 0, ',', '.'),
            number_format((float) ($master->PPN ?? 0), 2, ',', '.')
        );
        $footer = [
            str_repeat('-', $lineWidth),
            sprintf('%-65s%65s', 'BANK    : ' . $bank, 'SUB TOTAL : ' . number_format((float) $master->GRAND, 0, ',', '.')),
            sprintf('%-65s%65s', 'NO REK  : ' . $norek, 'DIBAYAR   : ' . number_format($dibayar, 0, ',', '.')),
            sprintf('%-65s%65s', 'A/N     : ' . $namaRekening, 'SALDO     : ' . number_format($saldo, 0, ',', '.')),
            '',
            str_pad('PENERIMA', $half) . str_pad('MENGETAHUI', $half),
            '', '', '',
            str_pad('(......................)', $half) . str_pad('PT. LINTAS MITRA ANUGERAH SEJATI', $half),
        ];

        $pages = [];
        $remainingRows = [$detail];
        while ($remainingRows !== []) {
            $overhead = $pages === [] ? count($header) + count($tableHeader) : 2 + count($tableHeader);
            $pages[] = array_splice($remainingRows, 0, max(1, $linesPerPage - $overhead));
        }
        if ($pages === []) $pages[] = [];
        $lastOverhead = count($pages) === 1 ? count($header) + count($tableHeader) : 2 + count($tableHeader);
        $footerNeedsOwnPage = ($linesPerPage - $lastOverhead - count(end($pages))) < count($footer);
        $totalPages = count($pages) + ($footerNeedsOwnPage ? 1 : 0);
        $outputPages = [];
        foreach ($pages as $index => $pageRows) {
            $pageLines = $index === 0
                ? $header
                : [str_pad('INVOICE ' . $master->INVOICE . ' (LANJUTAN) - HAL ' . ($index + 1) . '/' . $totalPages, $lineWidth, ' ', STR_PAD_BOTH), str_repeat('=', $lineWidth)];
            $pageLines = array_merge($pageLines, $tableHeader, $pageRows);
            if ($index === count($pages) - 1 && !$footerNeedsOwnPage) {
                $pageLines = array_merge($pageLines, array_fill(0, max(0, $linesPerPage - count($pageLines) - count($footer)), ''));
                $pageLines = array_merge($pageLines, $footer);
            }
            $outputPages[] = array_map(fn ($line) => str_repeat(' ', $leftMargin) . $line, $pageLines);
        }
        if ($footerNeedsOwnPage) {
            $pageLines = [str_pad('INVOICE ' . $master->INVOICE . ' (LANJUTAN) - HAL ' . $totalPages . '/' . $totalPages, $lineWidth, ' ', STR_PAD_BOTH), str_repeat('=', $lineWidth)];
            $pageLines = array_merge($pageLines, array_fill(0, max(0, $linesPerPage - 2 - count($footer)), ''), $footer);
            $outputPages[] = array_map(fn ($line) => str_repeat(' ', $leftMargin) . $line, $pageLines);
        }

        $pagesText = implode("\r\n\x0C\r\n", array_map(fn ($lines) => implode("\r\n", $lines), $outputPages));
        $text = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', $pagesText);
        if ($text === false) $text = $pagesText;
        $text = "\x1B\x0F" . $text . "\x12";

        return response()->json(['text' => $text, 'total_pages' => $totalPages]);
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

    protected function nextInvoiceNumber(bool $lock = false): string
    {
        $prefix = 'FJO' . now()->format('Y');
        $arhQuery = Arh::where('NOFAKTUR', 'like', $prefix . '%');
        $expedisiQuery = Expedisi::where('INVOICE', 'like', $prefix . '%');
        if ($lock) {
            $arhQuery->lockForUpdate();
            $expedisiQuery->lockForUpdate();
        }
        $latestArh = $arhQuery->orderByDesc('NOFAKTUR')->value('NOFAKTUR');
        $latestExpedisi = $expedisiQuery->orderByDesc('INVOICE')->value('INVOICE');
        $lastNumber = max(
            (int) substr((string) $latestArh, -6),
            (int) substr((string) $latestExpedisi, -6),
        );

        return $prefix . str_pad((string) ($lastNumber + 1), 6, '0', STR_PAD_LEFT);
    }
}
