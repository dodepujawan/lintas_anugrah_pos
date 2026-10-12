<?php

namespace App\Http\Controllers;

use App\Exports\CustomerDuplicateExport;
use App\Models\Mcustomer;
use App\Services\AuditLogService;
use App\Services\PeriodLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $customers = Mcustomer::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($filter) => $filter
                ->where('CUSTOMER', 'like', "%{$search}%")
                ->orWhere('NAMACUST', 'like', "%{$search}%")
                ->orWhere('KOTA', 'like', "%{$search}%")
                ->orWhere('TELEPON', 'like', "%{$search}%")
            ))
            ->orderBy('CUSTOMER')
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function duplicates(Request $request)
    {
        $filters = $this->duplicateFilters($request);
        $groups = $this->duplicateGroups($filters);

        return view('customers.duplicates', compact('groups', 'filters'));
    }

    public function exportDuplicates(Request $request)
    {
        $groups = $this->duplicateGroups($this->duplicateFilters($request));

        return Excel::download(new CustomerDuplicateExport($groups), 'duplikat-customer-'.now()->format('Ymd-His').'.xlsx');
    }

    private function duplicateFilters(Request $request): array
    {
        return [
            'mode' => $request->string('mode', 'safe')->toString(),
            'code_similarity' => $request->string('code_similarity', 'prefix')->toString(),
            'name' => trim((string) $request->input('name')),
            'customer' => strtoupper(trim((string) $request->input('customer'))),
            'address' => trim((string) $request->input('address')),
            'transaction_status' => $request->string('transaction_status', 'all')->toString(),
        ];
    }

    private function duplicateGroups(array $filters)
    {
        return Mcustomer::query()
            ->when($filters['name'] !== '', fn ($query) => $query->where('NAMACUST', 'like', '%'.$filters['name'].'%'))
            ->when($filters['customer'] !== '', fn ($query) => $query->where('CUSTOMER', 'like', '%'.$filters['customer'].'%'))
            ->when($filters['address'] !== '', fn ($query) => $query->where('ALAMAT1', 'like', '%'.$filters['address'].'%'))
            ->orderBy('NAMACUST')
            ->orderBy('CUSTOMER')
            ->get()
            ->groupBy(fn (Mcustomer $customer): string => $this->groupKey($customer, $filters['mode']))
            ->filter(fn ($customers, string $name): bool => $name !== '' && $customers->count() > 1)
            ->map(function ($customers) use ($filters) {
                $customers = $customers->values();
                $hasMatchingPair = false;

                for ($i = 0; $i < $customers->count(); $i++) {
                    for ($j = $i + 1; $j < $customers->count(); $j++) {
                        if ($this->isDuplicateCandidate($customers[$i], $customers[$j], $filters)) {
                            $hasMatchingPair = true;
                            break 2;
                        }
                    }
                }

                if (! $hasMatchingPair) {
                    return null;
                }

                return [
                    'name' => trim((string) $customers->first()->NAMACUST),
                    'customers' => $customers->map(fn (Mcustomer $customer): array => [
                        'id' => $customer->id,
                        'code' => trim((string) $customer->CUSTOMER),
                        'name' => trim((string) $customer->NAMACUST),
                        'address' => trim((string) $customer->ALAMAT1),
                        'city' => trim((string) $customer->KOTA),
                        'counts' => $this->customerUsageCounts(trim((string) $customer->CUSTOMER)),
                        'transaction_summary' => $this->customerTransactionSummary(trim((string) $customer->CUSTOMER)),
                    ])
                        ->filter(fn (array $row): bool => $this->passesTransactionFilter($row['counts'], $filters['transaction_status']))
                        ->sortByDesc(fn (array $row): int => ($row['address'] !== '' ? 1000 : 0) + mb_strlen($row['code']))
                        ->values(),
                ];
            })
            ->filter(fn ($group): bool => $group && $group['customers']->count() > 1)
            ->values();
    }

    public function merge(Request $request)
    {
        $validated = $request->validate([
            'keep_customer' => ['required', 'string', 'max:30', 'exists:mcustomer,CUSTOMER'],
            'delete_customer' => ['required', 'string', 'max:30', 'different:keep_customer', 'exists:mcustomer,CUSTOMER'],
            'confirm_merge' => ['accepted'],
            'copy_address' => ['nullable', 'boolean'],
        ], [
            'confirm_merge.accepted' => 'Konfirmasi penggabungan wajib dicentang.',
        ]);

        $keepCode = strtoupper(trim($validated['keep_customer']));
        $deleteCode = strtoupper(trim($validated['delete_customer']));

        $keep = Mcustomer::where('CUSTOMER', $keepCode)->firstOrFail();
        $delete = Mcustomer::where('CUSTOMER', $deleteCode)->firstOrFail();

        if ($this->normalizeName($keep->NAMACUST) !== $this->normalizeName($delete->NAMACUST)) {
            return back()->with('error', 'Merge dibatalkan. Nama customer tidak sama.');
        }

        if (! $this->hasSimilarCodePair($keepCode, $deleteCode, 'normal')) {
            return back()->with('error', 'Merge dibatalkan. Kode customer tidak cukup mirip.');
        }

        $lockedMessage = $this->lockedMergeMessage($deleteCode);
        if ($lockedMessage) {
            return back()->with('error', $lockedMessage);
        }

        $counts = $this->customerUsageCounts($deleteCode);
        $copyAddress = (bool) ($validated['copy_address'] ?? false);

        DB::transaction(function () use ($keep, $delete, $keepCode, $deleteCode, $copyAddress): void {
            foreach ($this->customerReferenceTables() as $ref) {
                DB::table($ref['table'])->where($ref['column'], $deleteCode)->update([$ref['column'] => $keepCode]);
            }

            $updates = [];
            foreach (['NAMACUST', 'ALAMAT2', 'KOTA', 'TELEPON', 'FAX', 'EMAIL', 'KONTAK', 'NPWP', 'AREA'] as $field) {
                if (trim((string) $keep->{$field}) === '' && trim((string) $delete->{$field}) !== '') {
                    $updates[$field] = trim((string) $delete->{$field});
                }
            }
            if (
                trim((string) $delete->ALAMAT1) !== ''
                && ($copyAddress || trim((string) $keep->ALAMAT1) === '')
            ) {
                $updates['ALAMAT1'] = trim((string) $delete->ALAMAT1);
            }
            if ($updates) {
                $updates['updated_at'] = now();
                DB::table('mcustomer')->where('id', $keep->id)->update($updates);
            }

            DB::table('mcustomer')->where('id', $delete->id)->delete();
        });

        app(AuditLogService::class)->record('merge', 'mcustomer', $keep, "Merge customer {$deleteCode} ke {$keepCode}.", [
            'from' => $deleteCode,
            'to' => $keepCode,
            'updated_rows' => $counts,
            'copy_address' => $copyAddress,
        ]);

        $redirectFilters = collect($request->input('filters', []))
            ->only(['mode', 'code_similarity', 'name', 'customer', 'address', 'transaction_status'])
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->all();

        return redirect()->route('customers.duplicates', $redirectFilters)->with([
            'success' => "Customer {$deleteCode} berhasil digabung ke {$keepCode}.",
            'merge_report' => [
                'from' => $deleteCode,
                'from_name' => trim((string) $delete->NAMACUST),
                'to' => $keepCode,
                'to_name' => trim((string) $keep->NAMACUST),
                'counts' => $counts,
                'copy_address' => $copyAddress,
                'deleted_at' => now()->format('d/m/Y H:i:s'),
                'user' => auth()->user()?->name ?? 'System',
            ],
        ]);
    }

    public function lookup(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $rows = Mcustomer::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($filter) => $filter
                ->where('CUSTOMER', 'like', "%{$q}%")
                ->orWhere('NAMACUST', 'like', "%{$q}%")
                ->orWhere('ALAMAT1', 'like', "%{$q}%")
            ))
            ->orderBy('CUSTOMER')
            ->limit(30)
            ->get(['CUSTOMER', 'NAMACUST', 'ALAMAT1'])
            ->map(fn (Mcustomer $customer): array => [
                'customer' => trim((string) $customer->CUSTOMER),
                'name' => trim((string) $customer->NAMACUST),
                'address' => trim((string) $customer->ALAMAT1),
            ]);

        return response()->json($rows);
    }

    public function create()
    {
        return view('customers.form');
    }

    public function store(Request $request)
    {
        $customer = Mcustomer::create($this->validated($request));
        app(AuditLogService::class)->record('create', 'mcustomer', $customer, 'Menambah master customer '.$customer->CUSTOMER);

        return redirect()->route('customers.index')->with('success', 'Master customer berhasil ditambahkan.');
    }

    public function edit(Mcustomer $customer)
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Mcustomer $customer)
    {
        $customer->update($this->validated($request, $customer));
        app(AuditLogService::class)->record('update', 'mcustomer', $customer, 'Mengubah master customer '.$customer->CUSTOMER);

        return redirect()->route('customers.index')->with('success', 'Master customer berhasil diperbarui.');
    }

    public function destroy(Mcustomer $customer)
    {
        $used = DB::table('arh')->where('CUSTOMER', $customer->CUSTOMER)->exists()
            || DB::table('barh')->where('CUSTOMER', $customer->CUSTOMER)->exists();

        if ($used) {
            return back()->with('error', 'Customer sudah memiliki transaksi piutang/pelunasan dan tidak dapat dihapus.');
        }

        $code = $customer->CUSTOMER;
        $customer->delete();

        return back()->with('success', 'Master customer '.$code.' berhasil dihapus.');
    }

    private function validated(Request $request, ?Mcustomer $customer = null): array
    {
        $request->merge([
            'CUSTOMER' => strtoupper(trim((string) $request->input('CUSTOMER'))),
            'kode_cus' => strtoupper(trim((string) $request->input('kode_cus'))) ?: null,
        ]);

        return $request->validate([
            'CUSTOMER' => ['required', 'string', 'max:30', Rule::unique('mcustomer', 'CUSTOMER')->ignore($customer?->id)],
            'kode_cus' => ['nullable', 'string', 'max:30', Rule::unique('mcustomer', 'kode_cus')->ignore($customer?->id)],
            'NAMACUST' => ['required', 'string', 'max:100'],
            'ALAMAT1' => ['nullable', 'string', 'max:200'],
            'ALAMAT2' => ['nullable', 'string', 'max:200'],
            'KOTA' => ['nullable', 'string', 'max:100'],
            'TELEPON' => ['nullable', 'string', 'max:100'],
            'FAX' => ['nullable', 'string', 'max:100'],
            'EMAIL' => ['nullable', 'email', 'max:100'],
            'KONTAK' => ['nullable', 'string', 'max:100'],
            'NPWP' => ['nullable', 'string', 'max:100'],
            'AREA' => ['nullable', 'string', 'max:6'],
        ]);
    }

    private function normalizeName(mixed $value): string
    {
        return preg_replace('/\s+/', ' ', strtoupper(trim((string) $value))) ?? '';
    }

    private function normalizeAddress(mixed $value): string
    {
        return preg_replace('/\s+/', ' ', strtoupper(trim((string) $value))) ?? '';
    }

    private function groupKey(Mcustomer $customer, string $mode): string
    {
        if ($mode === 'address_code') {
            return $this->normalizeAddress($customer->ALAMAT1);
        }

        return $this->normalizeName($customer->NAMACUST);
    }

    private function isDuplicateCandidate(Mcustomer $first, Mcustomer $second, array $filters): bool
    {
        $sameName = $this->normalizeName($first->NAMACUST) === $this->normalizeName($second->NAMACUST);
        $similarName = $sameName || levenshtein($this->normalizeName($first->NAMACUST), $this->normalizeName($second->NAMACUST)) <= 3;
        $sameAddress = $this->normalizeAddress($first->ALAMAT1) !== ''
            && $this->normalizeAddress($first->ALAMAT1) === $this->normalizeAddress($second->ALAMAT1);
        $similarCode = $this->hasSimilarCodePair($first->CUSTOMER, $second->CUSTOMER, $filters['code_similarity']);

        return match ($filters['mode']) {
            'same_name' => $sameName,
            'address_code' => $sameAddress && $similarCode,
            'name_code' => $similarName && $similarCode,
            'all_same_name' => $sameName,
            default => $sameName && $similarCode,
        };
    }

    private function hasSimilarCodePair(mixed $first, mixed $second, string $level = 'strict'): bool
    {
        $first = strtoupper(trim((string) $first));
        $second = strtoupper(trim((string) $second));

        if ($first === '' || $second === '' || $first === $second) {
            return false;
        }

        if (str_starts_with($first, $second) || str_starts_with($second, $first)) {
            return true;
        }

        if ($level === 'prefix') {
            return false;
        }

        $maxLength = max(strlen($first), strlen($second));
        if ($maxLength < 8) {
            return false;
        }

        $limit = match ($level) {
            'loose' => 5,
            'normal' => 3,
            default => 2,
        };

        return levenshtein($first, $second) <= $limit;
    }

    private function passesTransactionFilter(array $counts, string $status): bool
    {
        $total = array_sum($counts);

        return match ($status) {
            'with' => $total > 0,
            'without' => $total === 0,
            default => true,
        };
    }

    private function customerUsageCounts(string $customerCode): array
    {
        return collect($this->customerReferenceTables())
            ->mapWithKeys(fn (array $ref): array => [
                $ref['table'] => DB::table($ref['table'])->where($ref['column'], $customerCode)->count(),
            ])
            ->all();
    }

    private function customerTransactionSummary(string $customerCode): array
    {
        $summary = [];

        if (Schema::hasTable('arh') && Schema::hasColumn('arh', 'CUSTOMER')) {
            $query = DB::table('arh')->where('CUSTOMER', $customerCode);
            $count = (clone $query)->count();
            if ($count > 0) {
                $samples = (clone $query)
                    ->orderByDesc(Schema::hasColumn('arh', 'TGLFAKTUR') ? 'TGLFAKTUR' : 'id')
                    ->limit(3)
                    ->pluck('NOFAKTUR')
                    ->filter()
                    ->implode(', ');
                $summary[] = ['label' => 'Piutang', 'count' => $count, 'samples' => $samples];
            }
        }

        if (Schema::hasTable('barh') && Schema::hasColumn('barh', 'CUSTOMER')) {
            $query = DB::table('barh')->where('CUSTOMER', $customerCode);
            $count = (clone $query)->count();
            if ($count > 0) {
                $samples = (clone $query)
                    ->orderByDesc(Schema::hasColumn('barh', 'TGLBAYAR') ? 'TGLBAYAR' : 'id')
                    ->limit(3)
                    ->pluck('NOBAYAR')
                    ->filter()
                    ->implode(', ');
                $summary[] = ['label' => 'Pelunasan', 'count' => $count, 'samples' => $samples];
            }
        }

        if (Schema::hasTable('t_penjualan_hosting') && Schema::hasColumn('t_penjualan_hosting', 'customer')) {
            $query = DB::table('t_penjualan_hosting')->where('customer', $customerCode);
            $count = (clone $query)->count();
            if ($count > 0) {
                $samples = (clone $query)
                    ->orderByDesc(Schema::hasColumn('t_penjualan_hosting', 'tgl_kirim') ? 'tgl_kirim' : 'id')
                    ->limit(3)
                    ->pluck('invoice')
                    ->filter()
                    ->implode(', ');
                $summary[] = ['label' => 'Hosting', 'count' => $count, 'samples' => $samples];
            }
        }

        return $summary;
    }

    private function customerReferenceTables(): array
    {
        return collect([
            ['table' => 'arh', 'column' => 'CUSTOMER'],
            ['table' => 'barh', 'column' => 'CUSTOMER'],
            ['table' => 't_penjualan_hosting', 'column' => 'customer'],
        ])->filter(fn (array $ref): bool => Schema::hasTable($ref['table']) && Schema::hasColumn($ref['table'], $ref['column']))
            ->values()
            ->all();
    }

    private function lockedMergeMessage(string $customerCode): ?string
    {
        $locks = app(PeriodLockService::class);
        $checks = [
            ['table' => 'arh', 'column' => 'CUSTOMER', 'date' => 'TGLFAKTUR', 'label' => 'Piutang'],
            ['table' => 'barh', 'column' => 'CUSTOMER', 'date' => 'TGLBAYAR', 'label' => 'Pelunasan'],
            ['table' => 't_penjualan_hosting', 'column' => 'customer', 'date' => 'tglinvoice', 'label' => 'Sinkron Hosting'],
            ['table' => 't_penjualan_hosting', 'column' => 'customer', 'date' => 'tgl_kirim', 'label' => 'Sinkron Hosting'],
        ];

        foreach ($checks as $check) {
            if (! Schema::hasTable($check['table']) || ! Schema::hasColumn($check['table'], $check['column']) || ! Schema::hasColumn($check['table'], $check['date'])) {
                continue;
            }

            $dates = DB::table($check['table'])
                ->where($check['column'], $customerCode)
                ->whereNotNull($check['date'])
                ->distinct()
                ->pluck($check['date']);

            foreach ($dates as $date) {
                if ($locks->isLocked($date)) {
                    return "Merge dibatalkan. {$check['label']} customer {$customerCode} memiliki transaksi pada periode terkunci. ".$locks->lockedMessage($date);
                }
            }
        }

        return null;
    }
}
