<?php

namespace App\Http\Controllers;

use App\Exports\InvoiceDgnExport;
use App\Models\Arh;
use App\Models\Expedisi;
use App\Models\Kwitansi;
use App\Models\Menu;
use App\Models\Mcustomer;
use App\Models\Pricedingincus;
use App\Models\Rekening;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class RentPendinginGenerateInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function rentInvoiceCandidateQuery()
    {
        return Expedisi::query()
            ->where('JENIS', 'REN')
            ->whereNotNull('NOMUAT')
            ->where('NOMUAT', '!=', '')
            ->orderByDesc('TGLMUAT')
            ->orderByDesc('id');
    }

    public function index(Request $request)
    {
        $status = in_array($request->input('status_inv_gen'), ['belum', 'sudah'], true)
            ? $request->input('status_inv_gen')
            : 'belum';

        $menu = Menu::where('route_name', 'rent-pendingin-invoice.index')->first();
        $canExport = $menu && $request->user()?->hasPermissionForMenu($menu, 'export');
        $customers = $canExport
            ? Mcustomer::query()->orderBy('NAMACUST')->get(['kode_cus', 'NAMACUST', 'CUSTOMER'])
            : collect();

        return view('rent-pendingin-invoice.index', [
            'status' => $status,
            'customers' => $customers,
            'canExport' => (bool) $canExport,
        ]);
    }

    public function getDataInvoiceGen(Request $request)
    {
        $filters = $request->validate([
            'status_inv_gen' => ['nullable', 'in:belum,sudah'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50'],
        ]);
        $status = $filters['status_inv_gen'] ?? 'belum';
        $query = $this->rentInvoiceCandidateQuery();
        if ($status === 'belum') {
            $query->where(fn ($q) => $q->whereNull('INVOICE')->orWhere('INVOICE', ''));
        } else {
            $query->whereNotNull('INVOICE')->where('INVOICE', '!=', '');
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('NOSJ', 'like', "%{$search}%")
                ->orWhere('NOMUAT', 'like', "%{$search}%")
                ->orWhere('INVOICE', 'like', "%{$search}%")
                ->orWhere('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('PESANAN', 'like', "%{$search}%"));
        }

        $perPage = (int) ($filters['per_page'] ?? 10);
        $page = (int) ($filters['page'] ?? 1);
        $total = (clone $query)->count();
        $rows = $query->forPage($page, $perPage)->get();

        return response()->json([
            'data' => $rows->values()->map(fn (Expedisi $row, int $index) => [
                'index' => (($page - 1) * $perPage) + $index + 1,
                'NOSJ' => trim((string) $row->NOSJ),
                'NOMUAT' => trim((string) $row->NOMUAT),
                'TGLMUAT' => $row->TGLMUAT?->format('d/m/Y') ?? '-',
                'INVOICE' => trim((string) $row->INVOICE),
                'TGLINVOICE' => $row->TGLINVOICE?->format('d/m/Y') ?? '-',
                'CUSTOMER' => trim((string) $row->CUSTOMER),
                'PESANAN' => trim((string) $row->PESANAN),
                'KENDARAAN' => trim((string) $row->NAMA_KENDARAAN),
                'GRAND' => (float) $row->GRAND,
                'PIUTANG' => (float) $row->PIUTANG,
                'BAYAR' => (float) $row->BAYAR,
            ]),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'tanggal_dari' => ['required', 'date'],
            'tanggal_sampai' => ['required', 'date', 'after_or_equal:tanggal_dari'],
            'filter_kwt_dgn' => ['required', 'in:belum,sudah'],
            'customer_invoice_ren' => ['nullable', 'string', 'max:20'],
        ]);
        $from = $filters['tanggal_dari'];
        $to = $filters['tanggal_sampai'];
        $status = $filters['filter_kwt_dgn'];
        $customer = $filters['customer_invoice_ren'] ?? null;
        $filename = "laporan_invoice_pendingin_{$status}_{$from}_sd_{$to}.xlsx";

        return Excel::download(new InvoiceDgnExport($from, $to, $status, $customer), $filename);
    }

    public function showInvoiceDetail(string $nosj)
    {
        $row = Expedisi::where('NOSJ', $nosj)->where('JENIS', 'REN')->first();
        if (! $row || blank($row->NOMUAT)) {
            return response()->json(['status' => false, 'message' => 'Surat Jalan Rent Pendingin tidak ditemukan atau belum dimuat.'], 404);
        }

        $arh = filled($row->INVOICE) ? Arh::where('NOFAKTUR', $row->INVOICE)->first() : null;
        if ((float) ($arh->BAYAR ?? 0) > 0) {
            return response()->json(['status' => false, 'message' => 'Invoice sudah memiliki pembayaran. Silakan hubungi admin.'], 422);
        }

        $customer = Mcustomer::where('kode_cus', $row->CUSTOMER_KODE)->first();
        $top = (int) ($arh->SALDO ?? $row->TOP ?? $customer->TOPKREDIT ?? 0);
        $tglInvoice = $row->TGLINVOICE ?: now();
        $tglJatuhTempo = $arh->TGLJT ?? $row->TGLJT
            ?? \Illuminate\Support\Carbon::parse($tglInvoice)->addDays($top)->toDateString();

        return response()->json([
            'status' => true,
            'mode' => filled($row->INVOICE) ? 'edit' : 'create',
            'data' => [
                'invoice' => trim((string) $row->INVOICE),
                'tgl_invoice' => $row->TGLINVOICE?->format('Y-m-d'),
                'customer' => trim((string) $row->CUSTOMER),
                'nomor_muat' => trim((string) $row->NOMUAT),
                'kendaraan' => trim((string) $row->NAMA_KENDARAAN),
                'item' => trim((string) $row->PESANAN),
                'jumlah' => (float) $row->JUMLAH,
                'unit' => trim((string) $row->UNIT),
                'harga' => (float) $row->HARGA,
                'sub_total' => (float) $row->TOTAL,
                'disc_persen' => (float) $row->DISC,
                'disc_rp' => (float) $row->NDISC,
                'd_charge' => (float) $row->DC,
                'ppn' => (float) $row->PPN,
                'grand' => (float) $row->GRAND,
                'nomor_sj' => trim((string) $row->NOSJ),
                'tgl_jt' => \Illuminate\Support\Carbon::parse($tglJatuhTempo)->format('Y-m-d'),
                'top' => $top,
                'bayar' => (float) $row->BAYAR,
                'piutang' => (float) $row->PIUTANG,
                'keterangan' => trim((string) $row->KETERANGAN),
            ],
        ]);
    }

    public function prosesInvoicePembayaran(Request $request)
    {
        $validated = $request->validate([
            'nosj' => ['required', 'string', 'max:20'],
            'invoice' => ['nullable', 'string', 'max:20'],
            'bayar' => ['nullable', 'numeric', 'min:0'],
            'top' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'tgl_jtp' => ['nullable', 'date'],
        ]);

        try {
            $invoice = DB::transaction(function () use ($validated, $request) {
                $row = Expedisi::where('NOSJ', $validated['nosj'])
                    ->where('JENIS', 'REN')->lockForUpdate()->first();
                if (! $row || blank($row->NOMUAT)) {
                    throw new \RuntimeException('SJ tidak ditemukan atau belum masuk Muat. Muat ulang daftar.');
                }

                $invoice = trim((string) ($validated['invoice'] ?? ''));
                if ($invoice === '') {
                    if (filled($row->INVOICE)) {
                        throw new \RuntimeException('SJ sudah memiliki invoice. Muat ulang daftar.');
                    }
                    $invoice = $this->generateInvoiceOnline();
                    $row->INVOICE = $invoice;
                    $row->TGLINVOICE = now();
                    $row->STS = 'INVOICE';
                } else {
                    if ($row->INVOICE !== $invoice || (float) $row->GRAND <= 0) {
                        throw new \RuntimeException('Invoice tidak ditemukan atau sudah berubah. Muat ulang daftar.');
                    }
                    $arh = Arh::where('NOFAKTUR', $invoice)->lockForUpdate()->first();
                    if ((float) ($arh->BAYAR ?? 0) > 0) {
                        throw new \RuntimeException('Invoice sudah memiliki pembayaran. Silakan hubungi admin.');
                    }
                }

                $grand = (float) ($row->GRAND ?? 0);
                $bayar = round((float) ($validated['bayar'] ?? 0));
                if ($bayar > $grand) {
                    throw new \RuntimeException('Bayar tidak boleh lebih besar dari total.');
                }
                $top = (int) ($validated['top'] ?? 0);
                $piutang = round($grand - $bayar);
                $tglJtp = $validated['tgl_jtp'] ?? null;

                $row->BAYAR = $bayar;
                $row->PIUTANG = $piutang;
                $row->TGLJT = $tglJtp;
                $row->save();

                if ($piutang > 0) {
                    Arh::updateOrCreate(
                        ['NOFAKTUR' => $invoice],
                        [
                            'TGLFAKTUR' => $row->TGLINVOICE ?? now(),
                            'CUSTOMER' => $row->CUSTOMER,
                            'SALDO' => $top,
                            'PIUTANG' => $piutang,
                            'DISCOUNT' => $row->NDISC ?? 0,
                            'TGLJT' => $tglJtp,
                            'CABANG' => $row->CABANG ?? '',
                            'KETERANGAN' => 'INVOICE DARI EXPEDISI',
                            'USER' => $request->user()->user_id ?? $request->user()->name,
                            'USER_UPDATE' => $request->user()->user_id ?? $request->user()->name,
                            'updated_at' => now(),
                        ]
                    );
                } else {
                    Arh::where('NOFAKTUR', $invoice)->delete();
                }

                return $invoice;
            });
        } catch (Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('rent-pendingin-invoice.show', $invoice)
            ->with('success', "Invoice {$invoice} berhasil disimpan.");
    }



    public function indexEdit(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $invoices = Expedisi::query()
            ->where('JENIS', 'REN')
            ->whereNotNull('INVOICE')->where('INVOICE', '!=', '')
            ->where('GRAND', '>', 0)
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('TGLINVOICE', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('TGLINVOICE', '<=', $request->input('tanggal_sampai')))
            ->when($search !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('INVOICE', 'like', "%{$search}%")
                ->orWhere('CUSTOMER', 'like', "%{$search}%")))
            ->orderByDesc('TGLINVOICE')->orderByDesc('INVOICE')
            ->paginate(20)->withQueryString();

        return view('rent-pendingin-invoice.edit', compact('invoices', 'search'));
    }

    public function showEditInvoiceJson(string $noInvoice)
    {
        $invoice = Expedisi::where('INVOICE', $noInvoice)
            ->where('JENIS', 'REN')
            ->where('GRAND', '>', 0)
            ->first();
        if (! $invoice) {
            return response()->json(['status' => false, 'message' => 'Invoice tidak ditemukan.'], 404);
        }

        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();
        if ((float) ($arh->BAYAR ?? 0) > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Invoice sudah memiliki pembayaran. Silakan hubungi admin.',
            ], 422);
        }

        $jumlah = (float) ($invoice->JUMLAH ?? 0);
        $harga = (float) ($invoice->HARGA ?? 0);
        $grand = (float) ($invoice->GRAND ?? 0);
        $bayar = (float) ($invoice->BAYAR ?? 0);

        return response()->json([
            'status' => true,
            'master' => [
                'invoice' => trim((string) $invoice->INVOICE),
                'customer_kode' => trim((string) $invoice->CUSTOMER_KODE),
                'customer' => trim((string) $invoice->CUSTOMER),
                'kendaraan' => trim((string) $invoice->NAMA_KENDARAAN),
                'driver' => trim((string) $invoice->DRIVER),
                'nomuat' => trim((string) $invoice->NOMUAT),
                'nosj' => trim((string) $invoice->NOSJ),
                'tgl_invoice' => $invoice->TGLINVOICE?->format('Y-m-d'),
                'tgl_jt' => ($arh?->TGLJT ?? $invoice->TGLJT)?->format('Y-m-d'),
                'item' => trim((string) $invoice->PESANAN),
                'jumlah' => $jumlah,
                'unit' => trim((string) $invoice->UNIT),
                'harga' => $harga,
                'subtotal' => round($jumlah * $harga),
                'disc' => (float) ($invoice->DISC ?? 0),
                'discount_value' => (float) ($invoice->NDISC ?? 0),
                'total' => (float) ($invoice->TOTAL ?? 0),
                'del_charge' => (float) ($invoice->DC ?? 0),
                'ppn' => (float) ($invoice->PPN ?? 0),
                'grand' => $grand,
                'bayar' => $bayar,
                // The legacy edit modal derives receivable from GRAND minus BAYAR.
                'piutang' => $grand - $bayar,
                'keterangan' => trim((string) $invoice->KETERANGAN),
            ],
        ]);
    }

    public function customerRentPrices(string $kodecus, Request $request)
    {
        $customer = Mcustomer::where('CUSTOMER', $kodecus)->first();
        if (! $customer) {
            return response()->json(['message' => 'Customer tidak ditemukan.'], 404);
        }

        $search = trim((string) $request->input('q'));
        $prices = Pricedingincus::query()
            ->leftJoin('kendaraans as k', 'pricedingincus.KODE', '=', 'k.KODE')
            ->where('pricedingincus.KODECUS', $kodecus)
            ->when($search !== '', fn ($query) => $query->where(function ($match) use ($search) {
                $match->where('pricedingincus.ITEM', 'like', "%{$search}%")
                    ->orWhere('pricedingincus.PERIODE', 'like', "%{$search}%")
                    ->orWhere('pricedingincus.PLAT', 'like', "%{$search}%")
                    ->orWhere('pricedingincus.JENIS', 'like', "%{$search}%")
                    ->orWhere('k.NAMA', 'like', "%{$search}%");
            }))
            ->orderBy('pricedingincus.PERIODE')
            ->orderBy('pricedingincus.id')
            ->limit(200)
            ->get([
                'pricedingincus.KODEDGN', 'pricedingincus.KODE', 'pricedingincus.ITEM',
                'pricedingincus.PERIODE', 'pricedingincus.PLAT', 'pricedingincus.JENIS',
                'pricedingincus.HARGA', 'k.NAMA as nama_kendaraan',
            ]);

        return response()->json([
            'customer_kode' => trim((string) $customer->CUSTOMER),
            'customer_nama' => trim((string) $customer->NAMACUST),
            'data' => $prices,
        ]);
    }

    public function showEditInvoiceRen(string $noInvoice)
    {
        $invoice = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'REN')
            ->where('GRAND', '>', 0)->firstOrFail();
        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();
        if ((float) ($arh->BAYAR ?? 0) > 0) {
            return redirect()->route('rent-pendingin-invoice-gen.index')
                ->with('error', 'Invoice sudah memiliki pembayaran. Silakan hubungi admin.');
        }
        $details = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'REN')->get();

        return view('rent-pendingin-invoice.show-edit', compact('invoice', 'arh', 'details'));
    }

    public function updateEditInvoiceRen(Request $request, string $noInvoice)
    {
        $validated = $request->validate([
            'item' => ['nullable', 'string', 'max:100'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'harga' => ['required', 'numeric', 'min:0'],
            'disc' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'del_charge' => ['nullable', 'numeric', 'min:0'],
            'ppn' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bayar' => ['nullable', 'numeric', 'min:0'],
            'tgl_jt' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($validated, $noInvoice, $request) {
                $row = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'REN')
                    ->where('GRAND', '>', 0)->lockForUpdate()->firstOrFail();
                $arh = Arh::where('NOFAKTUR', $noInvoice)->lockForUpdate()->first();
                if ((float) ($arh->BAYAR ?? 0) > 0) {
                    throw new \RuntimeException('Invoice sudah memiliki pembayaran. Silakan hubungi admin.');
                }

                $jumlah = (float) $validated['jumlah'];
                $harga = (float) $validated['harga'];
                $disc = (float) ($validated['disc'] ?? 0);
                $delCharge = (float) ($validated['del_charge'] ?? 0);
                $ppn = (float) ($validated['ppn'] ?? 0);
                $bayar = round((float) ($validated['bayar'] ?? 0));
                $subTotal = round($jumlah * $harga);
                $ndisc = round($subTotal * ($disc / 100));
                $total = round($subTotal - $ndisc);
                $grand = round($total + round($total * ($ppn / 100)) + $delCharge);
                if ($bayar > $grand) {
                    throw new \RuntimeException('Grand tidak boleh lebih kecil dari pembayaran yang sudah diterima.');
                }
                $piutang = round($grand - $bayar);
                $keterangan = $validated['keterangan'] ?? null;

                $row->update([
                    'PESANAN' => $validated['item'] ?? $row->PESANAN,
                    'JUMLAH' => $jumlah,
                    'HARGA' => $harga,
                    'DISC' => $disc,
                    'NDISC' => $ndisc,
                    'DC' => $delCharge,
                    'TOTAL' => $total,
                    'PPN' => $ppn,
                    'GRAND' => $grand,
                    'BAYAR' => $bayar,
                    'PIUTANG' => $piutang,
                    'TGLJT' => $validated['tgl_jt'] ?? null,
                    'KETERANGAN' => $keterangan,
                ]);

                if ($piutang > 0) {
                    if ($arh) {
                        $arh->update([
                            'TGLJT' => $validated['tgl_jt'] ?? null,
                            'PIUTANG' => $piutang,
                            'KETERANGAN' => $keterangan,
                            'USER_UPDATE' => $request->user()->name,
                        ]);
                    } else {
                        Arh::create([
                            'NOFAKTUR' => $noInvoice,
                            'TGLFAKTUR' => $row->TGLINVOICE,
                            'TGLJT' => $validated['tgl_jt'] ?? null,
                            'CUSTOMER' => $row->CUSTOMER,
                            'PIUTANG' => $piutang,
                            'BAYAR' => 0,
                            'KETERANGAN' => $keterangan,
                            'CABANG' => $row->area_id ?? '',
                            'USER' => $request->user()->name,
                        ]);
                    }
                } elseif ($arh) {
                    $arh->delete();
                }

                $kwitansi = Kwitansi::where('FDOK_TRANS', $noInvoice)->first();
                if ($kwitansi) {
                    $kwitansi->update([
                        'TOTAL' => $grand,
                        'PPN' => $ppn,
                        'DISC' => $disc,
                        'NDISC' => $ndisc,
                    ]);
                }
            });
        } catch (\RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => $exception->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Gagal memperbarui invoice Rent Pendingin.', [
                'invoice' => $noInvoice,
                'exception' => $exception,
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice gagal diperbarui. Periksa data lalu coba kembali.',
                ], 500);
            }

            return back()->withInput()->with('error', 'Invoice gagal diperbarui. Periksa data lalu coba kembali.');
        }

        if ($request->expectsJson()) {
            session()->flash('success', 'Invoice Rent Pendingin berhasil diperbarui.');

            return response()->json([
                'status' => true,
                'message' => 'Invoice Rent Pendingin berhasil diperbarui.',
                'redirect_url' => route('rent-pendingin-invoice.show', $noInvoice),
            ]);
        }

        return redirect()->route('rent-pendingin-invoice-gen.edit', $noInvoice)
            ->with('success', 'Invoice Rent Pendingin berhasil diperbarui.');
    }

    public function show(string $noInvoice)
    {
        $details = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'REN')->orderBy('NOSJ')->get();
        abort_if($details->isEmpty(), 404);
        $invoice = $details->firstWhere('GRAND', '>', 0) ?? $details->first();
        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();
        $signature = Signature::where('is_active', true)->first() ?? Signature::orderByDesc('id')->first();
        $rekening = Rekening::where('AKTIF', 1)->first();

        return view('rent-pendingin-invoice.show', compact('invoice', 'arh', 'details', 'signature', 'rekening'));
    }

    public function pdfInvoiceGenerate(string $noInvoice)
    {
        $details = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'REN')->orderBy('NOSJ')->get();
        abort_if($details->isEmpty(), 404);
        $invoice = $details->firstWhere('GRAND', '>', 0) ?? $details->first();
        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();
        $signature = Signature::where('is_active', true)->first() ?? Signature::orderByDesc('id')->first();
        $rekening = Rekening::where('AKTIF', 1)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'rent-pendingin-invoice.pdf.index',
            compact('invoice', 'arh', 'details', 'signature', 'rekening')
        );

        return $pdf->stream('invoice-rent-' . $noInvoice . '.pdf', ['stream' => true]);
    }

    public function printInvoiceText(string $noInvoice)
    {
        $details = Expedisi::where('INVOICE', $noInvoice)->where('JENIS', 'REN')->orderBy('NOSJ')->get();
        abort_if($details->isEmpty(), 404);
        $invoice = $details->firstWhere('GRAND', '>', 0) ?? $details->first();
        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();
        $text = view('rent-pendingin-invoice.print.text', compact('invoice', 'arh', 'details'))->render();
        $text = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', $text) ?: $text;

        return response()->json(['text' => "\x1B\x0F" . $text . "\x12", 'total_pages' => 1]);
    }

    private function generateInvoiceOnline(): string
    {
        $prefix = 'FJO' . now()->format('Y');
        $last = Expedisi::where('INVOICE', 'like', $prefix . '%')
            ->orderByDesc('INVOICE')->lockForUpdate()->value('INVOICE');

        return $prefix . str_pad((string) ((int) substr((string) $last, -6) + 1), 6, '0', STR_PAD_LEFT);
    }
}
