<?php

namespace App\Http\Controllers;

use App\Models\Msupplier;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $suppliers = Msupplier::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($filter) => $filter
                ->where('SUPPLIER', 'like', "%{$search}%")
                ->orWhere('NAMA', 'like', "%{$search}%")
                ->orWhere('KOTA', 'like', "%{$search}%")
                ->orWhere('TELEPON', 'like', "%{$search}%")
            ))
            ->orderBy('SUPPLIER')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create()
    {
        return view('suppliers.form');
    }

    public function store(Request $request)
    {
        $data = $this->normalizeLegacyFields($this->validated($request));

        // The legacy msupplier table has non-null columns without database
        // defaults. Supply the same defaults used by the import process so a
        // supplier can also be created when the optional form fields are blank.
        $data = array_merge([
            'ALAMAT1' => '',
            'ALAMAT2' => '',
            'KOTA' => '',
            'TELEPON' => '',
            'FAX' => '',
            'EMAIL' => '',
            'KONTAK' => '',
            'NOREK' => '',
            'BANK' => '',
            'ATASNAMA' => '',
            'SALDO' => 0,
            'RETURAN' => 0,
            'FTOP' => 0,
            'DISC_REG' => 0,
        ], array_filter($data, static fn ($value) => $value !== null));

        $supplier = Msupplier::create($data);
        app(AuditLogService::class)->record('create', 'msupplier', $supplier, 'Menambah master supplier '.$supplier->SUPPLIER);

        return redirect()->route('suppliers.index')->with('success', 'Master supplier berhasil ditambahkan.');
    }

    public function edit(Msupplier $supplier)
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Msupplier $supplier)
    {
        $supplier->update($this->normalizeLegacyFields($this->validated($request, $supplier)));
        app(AuditLogService::class)->record('update', 'msupplier', $supplier, 'Mengubah master supplier '.$supplier->SUPPLIER);

        return redirect()->route('suppliers.index')->with('success', 'Master supplier berhasil diperbarui.');
    }

    public function destroy(Msupplier $supplier)
    {
        $used = DB::table('aph')->where('SUPPLIER', $supplier->SUPPLIER)->exists()
            || DB::table('baph')->where('SUPPLIER', $supplier->SUPPLIER)->exists();

        if ($used) {
            return back()->with('error', 'Supplier sudah memiliki transaksi hutang/pelunasan dan tidak dapat dihapus.');
        }

        $code = $supplier->SUPPLIER;
        $supplier->delete();

        return back()->with('success', 'Master supplier '.$code.' berhasil dihapus.');
    }

    private function validated(Request $request, ?Msupplier $supplier = null): array
    {
        $request->merge([
            'SUPPLIER' => strtoupper(trim((string) $request->input('SUPPLIER'))),
        ]);

        return $request->validate([
            'SUPPLIER' => ['required', 'string', 'max:6', Rule::unique('msupplier', 'SUPPLIER')->ignore($supplier?->SUPPLIER, 'SUPPLIER')],
            'NAMA' => ['required', 'string', 'max:40'],
            'ALAMAT1' => ['nullable', 'string', 'max:200'],
            'ALAMAT2' => ['nullable', 'string', 'max:200'],
            'KOTA' => ['nullable', 'string', 'max:100'],
            'TELEPON' => ['nullable', 'string', 'max:100'],
            'FAX' => ['nullable', 'string', 'max:100'],
            'EMAIL' => ['nullable', 'email', 'max:100'],
            'KONTAK' => ['nullable', 'string', 'max:100'],
            'NOREK' => ['nullable', 'string', 'max:100'],
            'BANK' => ['nullable', 'string', 'max:100'],
            'ATASNAMA' => ['nullable', 'string', 'max:100'],
            'FTOP' => ['nullable', 'integer', 'min:0'],
            'DISC_REG' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function normalizeLegacyFields(array $data): array
    {
        foreach ([
            'ALAMAT1',
            'ALAMAT2',
            'KOTA',
            'TELEPON',
            'FAX',
            'EMAIL',
            'KONTAK',
            'NOREK',
            'BANK',
            'ATASNAMA',
        ] as $field) {
            if (($data[$field] ?? null) === null) {
                $data[$field] = '';
            }
        }

        foreach (['FTOP', 'DISC_REG'] as $field) {
            if (($data[$field] ?? null) === null) {
                $data[$field] = 0;
            }
        }

        return $data;
    }
}
