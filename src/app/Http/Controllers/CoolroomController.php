<?php

namespace App\Http\Controllers;

use App\Models\Coolroom;
use App\Models\Mcustomer;
use App\Models\Pajak;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoolroomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $coolrooms = Coolroom::query()
            ->where(function ($query) {
                $query->whereNull('INVOICE')->orWhere('INVOICE', '');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($match) use ($search) {
                    $match->where('NOSJ', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER', 'like', "%{$search}%")
                        ->orWhere('CUSTOMER_KODE', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('TGLSJ')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('coolroom.index', compact('coolrooms', 'search'));
    }

    public function create()
    {
        return view('coolroom.create', ['ppnRate' => $this->taxRate()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTransaction($request);
        $customer = Mcustomer::where('CUSTOMER', $validated['CUSTOMER_KODE'])->firstOrFail();

        $coolroom = DB::transaction(function () use ($request, $validated, $customer) {
            $attributes = $this->transactionAttributes($request, $validated, $customer);
            $attributes['NOSJ'] = $this->generateSjCoolroom();
            $attributes['USERINPUT'] = $this->userCode($request);
            $attributes['user'] = $request->user()->name;
            $attributes['area_id'] = $request->user()->area_id;
            $attributes['area_name'] = $request->user()->area_name;
            $attributes['NOJURNAL'] = null;
            $attributes['jurnal_id'] = null;

            return Coolroom::create($attributes);
        });

        return redirect()->route('coolroom.show', $coolroom)
            ->with('success', 'Transaksi Coolroom berhasil disimpan.');
    }

    public function show(Coolroom $coolroom)
    {
        return view('coolroom.show', compact('coolroom'));
    }

    public function edit(Coolroom $coolroom)
    {
        if ($this->isInvoiced($coolroom)) {
            return redirect()->route('coolroom.index')
                ->with('error', 'Data sudah memiliki invoice dan tidak dapat diedit.');
        }

        return view('coolroom.edit', [
            'coolroom' => $coolroom,
            'ppnRate' => $this->taxRate(),
        ]);
    }

    public function update(Request $request, Coolroom $coolroom)
    {
        $validated = $this->validateTransaction($request);
        $customer = Mcustomer::where('CUSTOMER', $validated['CUSTOMER_KODE'])->firstOrFail();

        $wasInvoiced = DB::transaction(function () use ($request, $validated, $customer, $coolroom) {
            $locked = Coolroom::whereKey($coolroom->getKey())->lockForUpdate()->firstOrFail();
            if ($this->isInvoiced($locked)) {
                return true;
            }

            $attributes = $this->transactionAttributes($request, $validated, $customer);
            $attributes['USEREDIT'] = $this->userCode($request);
            $locked->update($attributes);

            return false;
        });

        if ($wasInvoiced) {
            return redirect()->route('coolroom.index')
                ->with('error', 'Data sudah memiliki invoice dan tidak dapat diedit.');
        }

        return redirect()->route('coolroom.show', $coolroom)
            ->with('success', 'Transaksi Coolroom berhasil diperbarui.');
    }

    public function destroy(Coolroom $coolroom)
    {
        $wasInvoiced = DB::transaction(function () use ($coolroom) {
            $locked = Coolroom::whereKey($coolroom->getKey())->lockForUpdate()->firstOrFail();
            if ($this->isInvoiced($locked)) {
                return true;
            }

            $locked->delete();

            return false;
        });

        if ($wasInvoiced) {
            return redirect()->route('coolroom.index')
                ->with('error', 'Data sudah invoice dan tidak bisa dihapus.');
        }

        return redirect()->route('coolroom.index')
            ->with('success', 'Data Coolroom berhasil dihapus.');
    }

    public function getDataCustomer(Request $request)
    {
        $search = trim((string) $request->input('q'));
        $customers = Mcustomer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($match) use ($search) {
                    $match->where('CUSTOMER', 'like', "%{$search}%")
                        ->orWhere('NAMACUST', 'like', "%{$search}%")
                        ->orWhere('TELEPON', 'like', "%{$search}%");
                });
            })
            ->orderBy('NAMACUST')
            ->paginate(10, ['CUSTOMER', 'NAMACUST', 'TYPECUST', 'TELEPON', 'EMAIL'])
            ->withQueryString();

        return response()->json($customers);
    }

    public function pdf(string $nosj)
    {
        $coolroom = Coolroom::where('NOSJ', $nosj)->firstOrFail();
        $signature = Signature::latest()->first();
        $user = DB::table('users')->where('user_id', $coolroom->USERINPUT)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('coolroom.pdf.index', compact('coolroom', 'signature', 'user'));

        return $pdf->stream('SJ-' . $coolroom->NOSJ . '.pdf');
    }

    private function validateTransaction(Request $request): array
    {
        return $request->validate([
            'TGLSJ' => ['required', 'date'],
            'CUSTOMER_KODE' => ['required', 'string', 'max:20', 'exists:mcustomer,CUSTOMER'],
            'JUMLAH' => ['required', 'numeric', 'min:0'],
            'HARGA' => ['required', 'numeric', 'min:0'],
            'BOXING' => ['nullable', 'boolean'],
            'DISC' => ['required', 'numeric', 'min:0', 'max:100'],
            'PPN' => ['required', 'numeric', 'min:0', 'max:100'],
            'KETERANGAN' => ['nullable', 'string', 'max:2000'],
        ], [
            'CUSTOMER_KODE.exists' => 'Customer tidak ditemukan. Pilih customer dari daftar.',
        ]);
    }

    private function transactionAttributes(Request $request, array $validated, Mcustomer $customer): array
    {
        $jumlah = (float) $validated['JUMLAH'];
        $harga = (int) preg_replace('/[^0-9]/', '', (string) $validated['HARGA']);
        $discountRate = (float) $validated['DISC'];
        $ppnRate = (float) $validated['PPN'];
        $boxing = $request->boolean('BOXING');

        $subtotal = $boxing ? round($harga) : round($jumlah * $harga);
        $discountAmount = round($subtotal * $discountRate / 100);
        $dpp = round($subtotal - $discountAmount);
        $ppnAmount = round($dpp * $ppnRate / 100);
        $grand = round($dpp + $ppnAmount);
        $date = $validated['TGLSJ'];
        $customerName = trim((string) $customer->NAMACUST);

        return [
            'CUSTOMER_KODE' => $customer->CUSTOMER,
            'CUSTOMER' => $customerName,
            'NAMACUST' => $customerName,
            'TGL' => $date,
            'TGLSJ' => $date,
            'TGLMUAT' => $date,
            'UNIT' => 'KG',
            'JUMLAH' => $jumlah,
            'HARGA' => $harga,
            'BOXING' => $boxing,
            'SUBTOTAL' => $subtotal,
            'DISC' => $discountRate,
            'NDISC' => $discountAmount,
            'DPP' => $dpp,
            'PPN' => $ppnRate,
            'NPPN' => $ppnAmount,
            'TOTAL' => $dpp,
            'GRAND' => $grand,
            'KETERANGAN' => $validated['KETERANGAN'] ?? null,
        ];
    }

    private function generateSjCoolroom(): string
    {
        $prefix = 'SC' . now()->format('my');
        $last = Coolroom::where('NOSJ', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('NOSJ')
            ->first();
        $nextNumber = $last ? ((int) substr((string) $last->NOSJ, -5)) + 1 : 1;

        return $prefix . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    private function taxRate(): float
    {
        return (float) (Pajak::where('is_active', true)->latest('updated_at')->value('PPN') ?? 0);
    }

    private function userCode(Request $request): string
    {
        return (string) ($request->user()->user_id ?? $request->user()->name ?? 'system');
    }

    private function isInvoiced(Coolroom $coolroom): bool
    {
        return trim((string) $coolroom->INVOICE) !== '';
    }
}
