<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coolroom;
use App\Models\Arh;
use App\Models\Kwitansi;
use App\Models\Mcustomer;
use App\Models\Rekening;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CoolroomInvoiceExport;

class CoolroomGenerateInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $base = Coolroom::query()->whereNotNull('INVOICE')->where('INVOICE', '!=', '');
        $stats = (clone $base)->selectRaw(
            'COUNT(DISTINCT INVOICE) as invoice_count, SUM(GRAND) as grand_total, SUM(BAYAR) as paid_total, SUM(PIUTANG) as receivable_total'
        )->first();

        $invoices = (clone $base)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($match) use ($search) {
                    $match->where('INVOICE', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER_KODE', 'like', "%{$search}%");
                });
            })
            ->select('INVOICE')
            ->selectRaw('MIN(id) as id, MAX(TGLINVOICE) as TGLINVOICE, MAX(CUSTOMER) as CUSTOMER, SUM(GRAND) as GRAND, SUM(BAYAR) as BAYAR, SUM(PIUTANG) as PIUTANG')
            ->groupBy('INVOICE')
            ->orderByDesc('TGLINVOICE')
            ->orderByDesc('INVOICE')
            ->paginate(20)
            ->withQueryString();

        return view('coolroom-invoice.index', compact('invoices', 'search', 'stats'));
    }

    public function create(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = in_array($request->input('status_invoice'), ['belum', 'sudah'], true)
            ? $request->input('status_invoice')
            : 'belum';

        $coolrooms = Coolroom::query()
            ->when($status === 'belum', function ($query) {
                $query->where(function ($match) {
                    $match->whereNull('INVOICE')->orWhere('INVOICE', '');
                });
            })
            ->when($status === 'sudah', function ($query) {
                $query->whereNotNull('INVOICE')->where('INVOICE', '!=', '');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($match) use ($search) {
                    $match->where('NOSJ', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER_KODE', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('coolroom-invoice.generate', compact('coolrooms', 'search', 'status'));
    }

    public function show($noInvoice)
    {
        $details = Coolroom::where('INVOICE', $noInvoice)->orderBy('NOSJ')->get();
        abort_if($details->isEmpty(), 404);
        $invoice = $details->first();
        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();

        return view('coolroom-invoice.show', compact('invoice', 'details', 'arh'));
    }

    public function store(Request $request)
    {
        return $this->prosesInvoicePembayaran($request);
    }

    public function showEditInvoiceJson($noInvoice)
    {
        $row = Coolroom::where('INVOICE', $noInvoice)->first();
        if (! $row) {
            return response()->json(['status' => false, 'message' => 'Invoice tidak ditemukan'], 404);
        }

        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();
        if ($arh && (float) ($arh->BAYAR ?? 0) > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Invoice sudah memiliki pembayaran. Silakan hubungi admin.',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'master' => [
                'invoice' => $row->INVOICE,
                'customer' => $row->CUSTOMER,
                'tgl_invoice' => $row->TGLINVOICE ? $row->TGLINVOICE->format('Y-m-d') : null,
                'tgl_jt' => ($arh->TGLJT ?? $row->TGLJT) ? ($arh->TGLJT ?? $row->TGLJT)->format('Y-m-d') : null,
                'boxing' => (int) ($row->BOXING ?? 0),
                'jumlah' => (float) ($row->JUMLAH ?? 0),
                'unit' => $row->UNIT ?? 'KG',
                'harga' => (float) ($row->HARGA ?? 0),
                'subtotal' => (float) ($row->SUBTOTAL ?? 0),
                'disc' => (float) ($row->DISC ?? 0),
                'dpp' => (float) ($row->DPP ?? 0),
                'ppn' => (float) ($row->PPN ?? 0),
                'total' => (float) ($row->TOTAL ?? 0),
                'grand' => (float) ($row->GRAND ?? 0),
                'bayar' => (float) ($row->BAYAR ?? 0),
                'piutang' => (float) ($row->PIUTANG ?? 0),
                'keterangan' => $row->KETERANGAN ?? '',
            ],
        ]);
    }

    public function getDataInvoiceGen(Request $request)
    {
        $query = Coolroom::query();

        if ($request->status_invoice === 'belum') {
            $query->where(function ($q) {
                $q->whereNull('INVOICE')->orWhere('INVOICE', '');
            });
        }

        if ($request->status_invoice === 'sudah') {
            $query->whereNotNull('INVOICE')->where('INVOICE', '!=', '');
        }

        $query->orderByDesc('id');

        return response()->json([
            'status' => true,
            'data' => $query->get()->map(function ($row) {
                return [
                    'id' => $row->id,
                    'NOSJ' => $row->NOSJ,
                    'TGLSJ' => $row->TGLSJ?->format('Y-m-d'),
                    'CUSTOMER' => $row->CUSTOMER,
                    'JUMLAH' => (float) ($row->JUMLAH ?? 0),
                    'GRAND' => (float) ($row->GRAND ?? 0),
                    'INVOICE' => $row->INVOICE ?? '',
                ];
            }),
        ]);
    }

    public function showInvoiceDetail($nosj)
    {
        $coolroom = Coolroom::where('NOSJ', $nosj)->first();
        if (! $coolroom) {
            return response()->json(['status' => false, 'message' => 'Data coolroom tidak ditemukan'], 404);
        }

        $arh = Arh::where('NOFAKTUR', $coolroom->INVOICE)->first();
        if ($arh && (float) ($arh->BAYAR ?? 0) > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Invoice sudah memiliki pembayaran. Silakan hubungi admin.',
            ], 422);
        }

        $topKredit = 0;
        if ($coolroom->CUSTOMER_KODE) {
            $customer = Mcustomer::where('kode_cus', $coolroom->CUSTOMER_KODE)->first();
            $topKredit = (int) ($customer->TOPKREDIT ?? 0);
        }

        $tglInvoice = $coolroom->TGLINVOICE ? $coolroom->TGLINVOICE : now();
        $tglJatuhTempo = $tglInvoice->copy()->addDays($topKredit);

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $coolroom->id,
                'nosj' => $coolroom->NOSJ,
                'tglsj' => $coolroom->TGLSJ?->format('Y-m-d'),
                'invoice' => $coolroom->INVOICE ?? '',
                'tgl_invoice' => $coolroom->TGLINVOICE?->format('Y-m-d'),
                'customer' => $coolroom->CUSTOMER,
                'customer_kode' => $coolroom->CUSTOMER_KODE,
                'jumlah' => (float) ($coolroom->JUMLAH ?? 0),
                'harga' => (float) ($coolroom->HARGA ?? 0),
                'sub_total' => (float) ($coolroom->SUBTOTAL ?? 0),
                'disc_persen' => (float) ($coolroom->DISC ?? 0),
                'disc_rp' => (float) ($coolroom->NDISC ?? 0),
                'dpp' => (float) ($coolroom->DPP ?? 0),
                'ppn' => (float) ($coolroom->PPN ?? 0),
                'ppn_rp' => (float) ($coolroom->NPPN ?? 0),
                'grand' => (float) ($coolroom->GRAND ?? 0),
                'bayar' => (float) ($coolroom->BAYAR ?? 0),
                'piutang' => (float) ($coolroom->PIUTANG ?? 0),
                'top' => $topKredit,
                'tgl_jt' => $coolroom->TGLJT ? $coolroom->TGLJT->format('Y-m-d') : $tglJatuhTempo->format('Y-m-d'),
                'saldo' => (float) ($arh->SALDO ?? 0),
            ],
        ]);
    }

    public function prosesInvoicePembayaran(Request $request)
    {
        if (is_int($request->input('bayar')) || is_float($request->input('bayar'))) {
            $request->merge(['bayar' => (string) $request->input('bayar')]);
        }

        $validated = $request->validate([
            'invoice' => ['nullable', 'string', 'max:30'],
            'nosj' => ['required_without:invoice', 'nullable', 'string', 'max:30'],
            'bayar' => ['nullable', 'string', 'max:40'],
            'top' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'tgl_jtp' => ['nullable', 'date'],
        ]);

        try {
            $invoice = trim((string) ($validated['invoice'] ?? ''));
            $paymentInput = trim((string) ($validated['bayar'] ?? '0'));
            if (str_contains($paymentInput, '-')) {
                throw new \RuntimeException('Nominal bayar tidak valid');
            }
            // The legacy input uses Indonesian thousands separators; keep the old digit-only parsing.
            $bayar = (int) (preg_replace('/\D+/', '', $paymentInput) ?: 0);
            $top = (int) ($validated['top'] ?? 0);
            $tglJtp = $validated['tgl_jtp'] ?? null;
            $nosj = trim((string) ($validated['nosj'] ?? ''));

            DB::transaction(function () use ($request, &$invoice, $bayar, $top, $tglJtp, $nosj) {
                if (empty($invoice)) {
                    $row = Coolroom::where('NOSJ', $nosj)->lockForUpdate()->first();
                    if (! $row) {
                        throw new \RuntimeException('Data coolroom tidak ditemukan');
                    }
                    if (trim((string) $row->INVOICE) !== '') {
                        throw new \RuntimeException('Surat jalan ini sudah memiliki invoice.');
                    }
                    $invoice = $this->generateInvoiceCoolroom();
                    $row->INVOICE = $invoice;
                    $row->TGLINVOICE = now();
                    $row->STS = 'INVOICE';
                    $row->STATUS = 'INVOICE';
                } else {
                    $row = Coolroom::where('INVOICE', $invoice)->lockForUpdate()->first();
                    if (! $row) {
                        throw new \RuntimeException('Invoice tidak ditemukan');
                    }

                    $arh = Arh::where('NOFAKTUR', $invoice)->first();
                    if ($arh && (float) ($arh->BAYAR ?? 0) > 0) {
                        throw new \RuntimeException('Invoice sudah memiliki pembayaran. Silakan hubungi admin.');
                    }
                }

                $grand = (float) ($row->GRAND ?? 0);
                if ($bayar < 0) {
                    throw new \RuntimeException('Nominal bayar tidak valid');
                }
                if ($bayar > $grand) {
                    throw new \RuntimeException('Bayar tidak boleh lebih besar dari total');
                }

                $piutangBaru = round($grand - $bayar);
                $row->BAYAR = $bayar;
                $row->PIUTANG = $piutangBaru;
                $row->TOP = $top;
                $row->TGLJT = $tglJtp;
                $row->save();

                if ($piutangBaru > 0) {
                    Arh::updateOrCreate(
                        ['NOFAKTUR' => $invoice],
                        [
                            'TGLFAKTUR' => $row->TGLINVOICE ?? now(),
                            'CUSTOMER' => $row->CUSTOMER,
                            'SALES' => $request->user()->name,
                            'AREA' => $request->user()->area_name,
                            'SALDO' => $top,
                            'PIUTANG' => $piutangBaru,
                            'BAYAR' => 0,
                            'DISCOUNT' => $row->NDISC ?? 0,
                            'TGLJT' => $tglJtp,
                            'CABANG' => $row->CABANG ?? '',
                            'KETERANGAN' => 'INVOICE COOLROOM',
                            'USER' => auth()->user()->user_id,
                            'USER_UPDATE' => auth()->user()->user_id,
                            'STATUS' => 'OPEN',
                            'SOURCE' => 'Coolroom',
                            'area_id' => $row->area_id,
                            'area_name' => $row->area_name,
                            'updated_at' => now(),
                        ]
                    );
                } else {
                    Arh::where('NOFAKTUR', $invoice)->delete();
                }
            });

            session()->flash('success', 'Invoice coolroom berhasil diproses.');

            return response()->json([
                'status' => true,
                'message' => 'Invoice coolroom berhasil diproses',
                'invoice' => $invoice,
                'redirect_url' => route('coolroom-invoice.show', $invoice),
            ]);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error('Gagal memproses invoice Coolroom.', ['exception' => $exception]);

            return response()->json([
                'status' => false,
                'message' => 'Invoice gagal diproses. Periksa data lalu coba kembali.',
            ], 500);
        }
    }

    public function indexEdit(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $invoices = Coolroom::query()
            ->whereNotNull('INVOICE')
            ->where('INVOICE', '!=', '')
            ->where('GRAND', '>', 0)
            ->when($request->filled('tanggal_dari'), function ($query) use ($request) {
                $query->whereDate('TGLINVOICE', '>=', $request->input('tanggal_dari'));
            })
            ->when($request->filled('tanggal_sampai'), function ($query) use ($request) {
                $query->whereDate('TGLINVOICE', '<=', $request->input('tanggal_sampai'));
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($match) use ($search) {
                    $match->where('INVOICE', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('TGLINVOICE')
            ->orderByDesc('INVOICE')
            ->paginate(20)
            ->withQueryString();

        return view('coolroom-invoice.edit', compact('invoices', 'search'));
    }

    public function showEditInvoiceCoolroom($noInvoice)
    {
        $invoice = Coolroom::where('INVOICE', $noInvoice)->firstOrFail();
        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();

        if ($arh && (float) ($arh->BAYAR ?? 0) > 0) {
            return redirect()->route('coolroom-invoice-gen.index')
                ->with('error', 'Invoice sudah memiliki pembayaran. Silakan hubungi admin.');
        }

        return view('coolroom-invoice.show-edit', compact('invoice', 'arh'));
    }

    public function updateEditInvoiceCoolroom(Request $request, $noInvoice)
    {
        foreach (['jumlah' => 3, 'harga' => 0, 'disc' => 2, 'ppn' => 2, 'bayar' => 0] as $field => $scale) {
            if ($request->exists($field)) {
                $request->merge([$field => $this->normalizeLocalizedNumber($request->input($field), $scale)]);
            }
        }

        $validated = $request->validate([
            'jumlah' => ['required', 'numeric', 'min:0', 'max:999999999.999'],
            'harga' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'disc' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ppn' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bayar' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'tgl_jt' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($validated, $request, $noInvoice) {
                $coolroom = Coolroom::where('INVOICE', $noInvoice)->lockForUpdate()->firstOrFail();
                $arh = Arh::where('NOFAKTUR', $noInvoice)->lockForUpdate()->first();
                if ($arh && (float) ($arh->BAYAR ?? 0) > 0) {
                    throw new \RuntimeException('Invoice sudah memiliki pembayaran. Silakan hubungi admin.');
                }

                $jumlahScaled = $this->toScaledInteger($validated['jumlah'], 3);
                $harga = (int) $validated['harga'];
                $discScaled = $this->toScaledInteger($validated['disc'] ?? '0', 2);
                $ppnScaled = $this->toScaledInteger($validated['ppn'] ?? '0', 2);
                $boxing = $request->boolean('boxing');

                $maxSubtotalScaled = 999_999_999_999_000;
                if (! $boxing && $harga > 0 && $jumlahScaled > intdiv($maxSubtotalScaled, $harga)) {
                    throw new \RuntimeException('Subtotal melebihi batas maksimum penyimpanan.');
                }
                $subtotalScaled = $boxing ? $harga * 1000 : $jumlahScaled * $harga;
                $subtotal = $subtotalScaled / 1000;
                $ndisc = $this->roundMultiplyDivide($subtotalScaled, $discScaled, 10_000_000);
                $dpp = $this->roundDivide($subtotalScaled - ($ndisc * 1000), 1000);
                $nppn = $this->roundMultiplyDivide($dpp, $ppnScaled, 10_000);
                $grand = $dpp + $nppn;

                if ($grand > 999_999_999_999) {
                    throw new \RuntimeException('Grand total melebihi batas maksimum penyimpanan.');
                }

                $jumlah = $jumlahScaled / 1000;
                $discPersen = $discScaled / 100;
                $ppnPersen = $ppnScaled / 100;
                $bayar = (int) ($validated['bayar'] ?? 0);
                if ($grand < $bayar) {
                    throw new \RuntimeException('Grand tidak boleh lebih kecil dari pembayaran yang sudah diterima');
                }
                $piutang = $grand - $bayar;

                $coolroom->update([
                    'BOXING' => $boxing,
                    'JUMLAH' => $jumlah,
                    'HARGA' => $harga,
                    'SUBTOTAL' => $subtotal,
                    'DISC' => $discPersen,
                    'NDISC' => $ndisc,
                    'DPP' => $dpp,
                    'PPN' => $ppnPersen,
                    'NPPN' => $nppn,
                    'TOTAL' => $dpp,
                    'GRAND' => $grand,
                    'BAYAR' => $bayar,
                    'PIUTANG' => $piutang,
                    'TGLJT' => $validated['tgl_jt'] ?? null,
                    'KETERANGAN' => $validated['keterangan'] ?? $coolroom->KETERANGAN,
                    'USEREDIT' => auth()->user()->user_id,
                ]);

                if ($piutang > 0) {
                    if (! $arh) {
                        Arh::create([
                            'NOFAKTUR' => $noInvoice,
                            'TGLFAKTUR' => $coolroom->TGLINVOICE,
                            'TGLJT' => $validated['tgl_jt'] ?? null,
                            'CUSTOMER' => $coolroom->CUSTOMER,
                            'PIUTANG' => $piutang,
                            'BAYAR' => 0,
                            'KETERANGAN' => $validated['keterangan'] ?? $coolroom->KETERANGAN,
                            'USER' => auth()->user()->user_id,
                            'CABANG' => $coolroom->area_id ?? '',
                            'SOURCE' => 'Coolroom',
                        ]);
                    } else {
                        $arh->update([
                            'TGLJT' => $validated['tgl_jt'] ?? null,
                            'PIUTANG' => $piutang,
                            'KETERANGAN' => $validated['keterangan'] ?? $coolroom->KETERANGAN,
                            'USER_UPDATE' => auth()->user()->user_id,
                            'SOURCE' => 'Coolroom',
                            'STATUS' => 'OPEN',
                        ]);
                    }
                } elseif ($arh) {
                    $arh->delete();
                }

                $kwitansi = Kwitansi::where('FDOK_TRANS', $noInvoice)->first();
                if ($kwitansi) {
                    $kwitansi->update([
                        'FNIL_DOK' => $grand,
                        'TOTAL' => $dpp,
                        'PPN' => $ppnPersen,
                        'DISC' => $discPersen,
                        'NDISC' => $ndisc,
                    ]);
                }
            });
        } catch (\RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', 'Invoice gagal diperbarui. Periksa data lalu coba kembali.');
        }

        return redirect()->route('coolroom-invoice.show', $noInvoice)
            ->with('success', 'Invoice coolroom berhasil diperbarui.');
    }

    public function printInvoiceCoolroom($noInvoice)
    {
        $details = Coolroom::where('INVOICE', $noInvoice)->orderBy('NOSJ')->get();
        abort_if($details->isEmpty(), 404);
        $invoice = $details->first();
        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();
        $customer = Mcustomer::where('CUSTOMER', $invoice->CUSTOMER_KODE)->first();
        $signature = Signature::latest()->first();
        $rekening = Rekening::where('AKTIF', 1)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'coolroom-invoice.pdf.index',
            compact('invoice', 'arh', 'details', 'customer', 'signature', 'rekening')
        );

        return $pdf->stream('invoice-' . $noInvoice . '.pdf', ['stream' => true]);
    }

    public function printInvoiceText($noInvoice)
    {
        $rows = Coolroom::where('INVOICE', $noInvoice)->orderBy('NOSJ')->get();
        abort_if($rows->isEmpty(), 404);

        $master = $rows->first();
        $arh = Arh::where('NOFAKTUR', $noInvoice)->first();
        $customer = Mcustomer::where('CUSTOMER', $master->CUSTOMER_KODE)->first();
        $rekening = Rekening::where('AKTIF', 1)->first();
        $lineWidth = 130;
        $leftMargin = 3;
        $linesPerPage = 30;
        $esc = "\x1B";
        $boldOn = $esc . 'E';
        $boldOff = $esc . 'F';
        $doubleWidthOn = $esc . 'W' . "\x01";
        $doubleWidthOff = $esc . 'W' . "\x00";
        $kepada = strtoupper($customer->NAMACUST ?? '-');
        $up = strtoupper($customer->KONTAK ?? '-');
        $alamat = strtoupper($customer->ALAMAT1 ?? '-');
        $bank = $rekening->BANK ?? '-';
        $norek = $rekening->NOREK ?? '-';
        $namaRek = $rekening->NAMA ?? '-';
        $subtotal = (float) ($master->SUBTOTAL ?? 0);
        $discount = (float) ($master->NDISC ?? 0);
        $dpp = (float) ($master->DPP ?? 0);
        $ppn = (float) ($master->NPPN ?? 0);
        $grand = (float) ($master->GRAND ?? 0);
        $paid = (float) ($master->BAYAR ?? 0);
        $receivable = (float) ($master->PIUTANG ?? 0);
        $lines = [];

        $half = intdiv($lineWidth, 2);
        $titleLeft = 'INVOICE';
        $titleRight = 'PT.LINTAS ANUGERAH SEJATI';
        $lines[] = $boldOn . $doubleWidthOn . $titleLeft . $doubleWidthOff . $boldOff
            . str_repeat(' ', max(0, $half - strlen($titleLeft) * 2))
            . str_repeat(' ', max(0, $half - strlen($titleRight) * 2))
            . $boldOn . $doubleWidthOn . $titleRight . $doubleWidthOff . $boldOff;
        $lines[] = str_repeat('=', $lineWidth);
        $lines[] = sprintf("%-65s%65s", 'NOMOR  : ' . $master->INVOICE, 'TANGGAL : ' . optional($master->TGLINVOICE)->format('d-m-Y'));
        $dueDate = $arh?->TGLJT ?? $master->TGLJT;
        $lines[] = sprintf("%-65s%65s", 'KEPADA : ' . substr($kepada, 0, 55), $dueDate ? 'TGL JT : ' . $dueDate->format('d-m-Y') : '');
        $lines[] = sprintf("%-65s%65s", 'UP     : ' . substr($up, 0, 55), 'CETAK : ' . now()->format('d-m-Y H:i'));

        foreach (explode("\n", wordwrap($alamat, 60, "\n")) as $index => $addressLine) {
            $lines[] = $index === 0 ? 'ALAMAT : ' . $addressLine : '         ' . $addressLine;
        }

        $lines[] = '';
        $lines[] = 'JUMLAH SJ : ' . $rows->count();
        $lines[] = str_repeat('=', $lineWidth);
        $format = "%-4s %-14s %-32s %-8s %-6s %-8s %12s";
        $lines[] = sprintf($format, 'NO', 'SJ', 'KETERANGAN', 'JUMLAH', 'UNIT', 'BOXING', 'TOTAL');
        $lines[] = str_repeat('-', 90);

        foreach ($rows as $index => $row) {
            $quantity = (float) ($row->JUMLAH ?? 0);
            $quantityText = floor($quantity) === $quantity
                ? number_format($quantity, 0, '.', '')
                : rtrim(rtrim(number_format($quantity, 3, '.', ''), '0'), '.');
            $boxingText = (int) ($row->BOXING ?? 0) === 1 ? 'BOX' : '-';
            $lines[] = sprintf($format, $index + 1, substr($row->NOSJ ?? '-', 0, 14), substr($row->KETERANGAN ?? '-', 0, 32), $quantityText, $row->UNIT ?? '', $boxingText, number_format($row->TOTAL ?? 0, 0, ',', '.'));
        }

        $lines[] = str_repeat('-', 90);
        $lines[] = sprintf("%-65s%65s", 'BANK   : ' . $bank, 'SUB TOTAL : ' . number_format($subtotal, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", 'NO REK : ' . $norek, 'DISKON : ' . number_format($discount, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", 'A/N    : ' . $namaRek, 'DPP : ' . number_format($dpp, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", '', 'PPN : ' . number_format($ppn, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", '', 'GRAND TOTAL : ' . number_format($grand, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", '', 'DIBAYAR : ' . number_format($paid, 0, ',', '.'));
        $lines[] = sprintf("%-65s%65s", '', 'SALDO : ' . number_format($receivable, 0, ',', '.'));
        $lines[] = '';
        $lines[] = '';
        $lines[] = str_pad('PENERIMA', $half) . str_pad('MENGETAHUI', $half);
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';
        $lines[] = str_pad('(......................)', $half) . str_pad('PT. LINTAS ANUGERAH SEJATI', $half);
        $lines = array_pad($lines, max(count($lines), $linesPerPage), '');
        $margin = str_repeat(' ', $leftMargin);
        $lines = array_map(fn ($line) => $margin . $line, $lines);

        $text = iconv('UTF-8', 'CP437//TRANSLIT//IGNORE', implode("\r\n", $lines));
        if ($text === false) {
            $text = implode("\r\n", $lines);
        }

        return response()->json(['text' => "\x1B\x0F" . $text . "\x12"]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'status_invoice' => ['required', 'in:belum,sudah'],
            'customer' => ['nullable', 'string', 'max:20'],
        ]);

        $export = new CoolroomInvoiceExport(
            $validated['date_from'],
            $validated['date_to'],
            $validated['status_invoice'],
            $validated['customer'] ?? null
        );

        return Excel::download($export, 'coolroom-' . $validated['status_invoice'] . '.xlsx');
    }

    private function normalizeLocalizedNumber(mixed $value, int $scale): mixed
    {
        $canonicalNumber = is_int($value) || is_float($value);
        if ($value === null || is_int($value) || is_float($value)) {
            $input = is_float($value)
                ? rtrim(rtrim(sprintf('%.12F', $value), '0'), '.')
                : (string) $value;
        } elseif (is_string($value)) {
            $input = preg_replace('/^rp\s*/i', '', trim($value));
            $input = str_replace([" ", "\u{00A0}"], '', $input);
        } else {
            return $value;
        }

        if ($input === '') {
            return null;
        }

        $original = $input;
        $negative = str_starts_with($input, '-');
        if ($negative || str_starts_with($input, '+')) {
            $input = substr($input, 1);
        }

        if ($input === '' || preg_match('/[^0-9.,]/', $input)) {
            return $original;
        }

        $commaPosition = strrpos($input, ',');
        $dotPosition = strrpos($input, '.');
        $fraction = '';

        if ($canonicalNumber) {
            if (substr_count($input, '.') > 1) {
                return $original;
            }
            [$integerPart, $fraction] = array_pad(explode('.', $input, 2), 2, '');
        } elseif ($commaPosition !== false && $dotPosition !== false) {
            $decimalSeparator = $commaPosition > $dotPosition ? ',' : '.';
            $groupingSeparator = $decimalSeparator === ',' ? '.' : ',';
            $decimalPosition = strrpos($input, $decimalSeparator);
            $integerPart = substr($input, 0, $decimalPosition);
            $fraction = substr($input, $decimalPosition + 1);
            $groupingPattern = $groupingSeparator === '.'
                ? '/^\d{1,3}(?:\.\d{3})+$/'
                : '/^\d{1,3}(?:,\d{3})+$/';
            if (str_contains($integerPart, $groupingSeparator) && ! preg_match($groupingPattern, $integerPart)) {
                return $original;
            }
            if (str_contains($integerPart, $decimalSeparator)) {
                return $original;
            }
            $integerPart = str_replace($groupingSeparator, '', $integerPart);
        } elseif ($commaPosition !== false) {
            if (substr_count($input, ',') > 1 && preg_match('/^\d{1,3}(?:,\d{3})+$/', $input)) {
                $integerPart = str_replace(',', '', $input);
            } else {
                $integerPart = str_replace(',', '', substr($input, 0, $commaPosition));
                $fraction = substr($input, $commaPosition + 1);
            }
        } elseif ($dotPosition !== false) {
            if (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $input)) {
                $integerPart = str_replace('.', '', $input);
            } elseif (substr_count($input, '.') > 1) {
                return $original;
            } else {
                $integerPart = str_replace('.', '', substr($input, 0, $dotPosition));
                $fraction = substr($input, $dotPosition + 1);
            }
        } else {
            $integerPart = $input;
        }

        if ($integerPart === '') {
            $integerPart = '0';
        }
        if (! ctype_digit($integerPart) || ($fraction !== '' && ! ctype_digit($fraction)) || strlen($integerPart) > 12) {
            return $original;
        }

        $integerPart = ltrim($integerPart, '0') ?: '0';
        $factor = 10 ** $scale;
        $keptFraction = str_pad(substr($fraction, 0, $scale), $scale, '0');
        $scaledValue = ((int) $integerPart * $factor) + ($scale > 0 ? (int) $keptFraction : 0);

        if (strlen($fraction) > $scale && (int) $fraction[$scale] >= 5) {
            $scaledValue++;
        }

        $integerPart = (string) intdiv($scaledValue, $factor);
        if ($scale === 0) {
            return ($negative ? '-' : '').$integerPart;
        }

        $fractionPart = str_pad((string) ($scaledValue % $factor), $scale, '0', STR_PAD_LEFT);

        return ($negative ? '-' : '').$integerPart.'.'.$fractionPart;
    }

    private function toScaledInteger(string|int|float $value, int $scale): int
    {
        [$integer, $fraction] = array_pad(explode('.', (string) $value, 2), 2, '');

        return ((int) $integer * (10 ** $scale))
            + ($scale > 0 ? (int) str_pad(substr($fraction, 0, $scale), $scale, '0') : 0);
    }

    private function roundMultiplyDivide(int $value, int $factor, int $divisor): int
    {
        $whole = intdiv($value, $divisor);
        $remainder = $value % $divisor;

        return ($whole * $factor) + intdiv(($remainder * $factor) + intdiv($divisor, 2), $divisor);
    }

    private function roundDivide(int $value, int $divisor): int
    {
        return intdiv($value + intdiv($divisor, 2), $divisor);
    }

    private function generateInvoiceCoolroom(): string
    {
        $tahun = now()->format('Y');
        $last = Coolroom::where('INVOICE', 'like', "FCO{$tahun}%")
            ->orderBy('INVOICE', 'desc')
            ->lockForUpdate()
            ->first();

        $lastNo = $last ? (int) substr($last->INVOICE, -6) : 0;

        return 'FCO' . $tahun . str_pad($lastNo + 1, 6, '0', STR_PAD_LEFT);
    }
}
