<?php

namespace App\Http\Controllers;

use App\Exports\CoaExport;
use App\Models\Coa;
use Database\Seeders\CoaSeeder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CoaController extends Controller
{
    private function normalizeKodeAkun(string $kodeAkun): string
    {
        $digits = preg_replace('/\D/', '', $kodeAkun);

        if (strlen($digits) === 5) {
            return $digits . '000';
        }

        return str_pad($digits, 8, '0', STR_PAD_LEFT);
    }

    private function tipeList(): array
    {
        return ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban'];
    }

    private function kategoriList(?Coa $coa = null): array
    {
        $categories = [
            'Aktiva Lancar',
            'Aktiva Tetap',
            'Aset',
            'Kas',
            'Bank',
            'Piutang',
            'Ekuitas',
            'Kewajiban Lancar',
            'Kewajiban Jangka Panjang',
            'Modal',
            'Prive',
            'Pendapatan',
            'Pendapatan Operasional',
            'Pendapatan Non Operasional',
            'Pendapatan Lainnya',
            'Beban ',
            'Beban Administrasi',
            'Beban Operasional',
            'Beban Penyusutan',
            'Beban Keuangan',
            'Beban Non Operasional',
            'Pajak dan Asuransi',
            'Pajak Dibayar Dimuka',
            'Beban Lain-lain',
            'Akumulasi Penyusutan',
        ];


        if ($coa?->kategori) {
            $categories[] = $coa->kategori;
        }

        return array_values(array_unique(array_filter($categories)));
    }

    private function levelList(): array
    {
        return ['Header', 'Sub Header', 'Kelompok Akun', 'Akun Posting'];
    }

    private function normalizeSaldoNormalValue(mixed $value): string
    {
        $normalized = trim((string) $value);

        if (strtolower($normalized) === 'kredit') {
            return 'Kredit';
        }

        if (strtolower($normalized) === 'debit') {
            return 'Debit';
        }

        if (is_numeric($value)) {
            return ((float) $value > 0) ? 'Kredit' : 'Debit';
        }

        return 'Debit';
    }

    private function getParentOptions(?Coa $exclude = null): array
    {
        $query = Coa::orderBy('kode_akun');

        if ($exclude) {
            $query->where('id', '!=', $exclude->id);
        }

        $coas = $query->get();

        return [
            'Header' => $coas->where('level', 'Header'),
            'Sub Header' => $coas->where('level', 'Sub Header'),
            'Kelompok Akun' => $coas->where('level', 'Kelompok Akun'),
        ];
    }

    public function index(Request $request)
    {
        if (Coa::count() === 0) {
            Artisan::call('db:seed', ['--class' => CoaSeeder::class]);
        }

        $query = $request->input('q');

        $coas = Coa::with('parent')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($item) use ($query) {
                    $item->where('kode_akun', 'like', "%{$query}%")
                        ->orWhere('nama_akun', 'like', "%{$query}%")
                        ->orWhere('tipe', 'like', "%{$query}%")
                        ->orWhere('kategori', 'like', "%{$query}%")
                        ->orWhere('level', 'like', "%{$query}%");
                });
            })
            ->orderBy('kode_akun')
            ->get();

        $coas->each(function (Coa $coa) {
            $coa->setRelation('children', collect());
        });

        $coasById = $coas->keyBy('id');
        $rootCoas = collect();

        foreach ($coas as $coa) {
            if ($coa->parent_id && $coasById->has($coa->parent_id)) {
                $coasById->get($coa->parent_id)->children->push($coa);
            } else {
                $rootCoas->push($coa);
            }
        }

        $treeRows = collect();
        $this->flattenCoaTree($rootCoas, $treeRows);

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $treeRows->slice(($page - 1) * $perPage, $perPage)->values();

        $coas = new LengthAwarePaginator(
            $currentPageItems,
            $treeRows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('coa.index', compact('coas', 'query'));
    }

    public function export(Request $request)
    {
        return Excel::download(new CoaExport(), 'coa_tree_' . now()->format('Ymd_His') . '.xlsx');
    }

    private function flattenCoaTree($items, $output, int $depth = 0): void
    {
        foreach ($items as $item) {
            $item->depth = $depth;
            $output->push($item);

            if ($item->children->isNotEmpty()) {
                $this->flattenCoaTree($item->children, $output, $depth + 1);
            }
        }
    }

    public function create()
    {
        $tipeList = $this->tipeList();
        $kategoriList = $this->kategoriList();
        $levelList = $this->levelList();
        $parentOptions = $this->getParentOptions();

        return view('coa.create', compact('tipeList', 'kategoriList', 'levelList', 'parentOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'kode_akun',
            'nama_akun',
            'tipe',
            'kategori',
            'level',
            'parent_id',
            'saldo_normal',
            'deskripsi',
            'is_active',
        ]);

        $data['kode_akun'] = $this->normalizeKodeAkun($data['kode_akun'] ?? '');
        $data['parent_id'] = ! empty($data['parent_id']) ? (int) $data['parent_id'] : null;
        $data['saldo_normal'] = $this->normalizeSaldoNormalValue($data['saldo_normal'] ?? '');
        $data['is_active'] = isset($data['is_active']) ? true : true;

        $validator = Validator::make($data, [
            'kode_akun' => ['required', 'string', 'size:8', 'regex:/^[0-9]{8}$/', 'unique:coas,kode_akun'],
            'nama_akun' => ['required', 'string', 'max:100'],
            'tipe' => ['required', Rule::in($this->tipeList())],
            'kategori' => ['nullable', 'string'],
            'level' => ['required', Rule::in($this->levelList())],
            'parent_id' => ['nullable', 'exists:coas,id'],
            'saldo_normal' => ['required', 'string', Rule::in(['Debit', 'Kredit'])],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validator->after(function ($validator) use ($data) {
            $this->validateParentAndLevel($validator, $data);
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $coa = Coa::create($data);
        $returnPage = max(1, (int) $request->input('return_page', 1));
        $returnQuery = (string) $request->input('return_q', '');

        $redirectParams = ['page' => $returnPage, 'focus' => $coa->id];
        if ($returnQuery !== '') {
            $redirectParams['q'] = $returnQuery;
        }

        return redirect()->route('coa.index', $redirectParams)
            ->with('success', 'Akun berhasil ditambahkan');
    }

    public function show(Coa $coa)
    {
        $coa->load(['parent', 'children', 'jurnalItems.jurnal']);
        $jurnalItems = $coa->jurnalItems()->with('jurnal')->latest()->take(20)->get();

        return view('coa.show', compact('coa', 'jurnalItems'));
    }

    public function edit(Coa $coa)
    {
        $tipeList = $this->tipeList();
        $kategoriList = $this->kategoriList($coa);
        $levelList = $this->levelList();
        $parentOptions = $this->getParentOptions($coa);

        return view('coa.edit', compact('coa', 'tipeList', 'kategoriList', 'levelList', 'parentOptions'));
    }

    public function update(Request $request, Coa $coa)
    {
        $data = $request->only([
            'kode_akun',
            'nama_akun',
            'tipe',
            'kategori',
            'level',
            'parent_id',
            'saldo_normal',
            'deskripsi',
            'is_active',
        ]);

        $data['kode_akun'] = $this->normalizeKodeAkun($data['kode_akun'] ?? '');
        $data['parent_id'] = ! empty($data['parent_id']) ? (int) $data['parent_id'] : null;
        $data['saldo_normal'] = $this->normalizeSaldoNormalValue($data['saldo_normal'] ?? '');
        $data['is_active'] = isset($data['is_active']) ? true : false;

        $validator = Validator::make($data, [
            'kode_akun' => ['required', 'string', 'size:8', 'regex:/^[0-9]{8}$/', Rule::unique('coas', 'kode_akun')->ignore($coa->id)],
            'nama_akun' => ['required', 'string', 'max:100'],
            'tipe' => ['required', Rule::in($this->tipeList())],
            'kategori' => ['nullable', 'string'],
            'level' => ['required', Rule::in($this->levelList())],
            'parent_id' => ['nullable', 'exists:coas,id'],
            'saldo_normal' => ['required', 'string', Rule::in(['Debit', 'Kredit'])],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validator->after(function ($validator) use ($data, $coa) {
            $this->validateParentAndLevel($validator, $data, $coa);
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $coa->update($data);
        $returnPage = max(1, (int) $request->input('return_page', 1));
        $returnQuery = (string) $request->input('return_q', '');

        $redirectParams = ['page' => $returnPage, 'focus' => $coa->id];
        if ($returnQuery !== '') {
            $redirectParams['q'] = $returnQuery;
        }

        return redirect()->route('coa.index', $redirectParams)
            ->with('success', 'Akun berhasil diperbarui');
    }

    public function destroy(Coa $coa)
    {
        if ($coa->children()->exists()) {
            return redirect()->route('coa.index')->with('error', 'Akun tidak dapat dihapus karena memiliki sub-akun');
        }

        if ($coa->jurnalItems()->exists()) {
            return redirect()->route('coa.index')->with('error', 'Akun tidak dapat dihapus karena sudah memiliki transaksi');
        }

        $coa->delete();

        return redirect()->route('coa.index')->with('success', 'Akun berhasil dihapus');
    }

    private function validateParentAndLevel($validator, array $data, ?Coa $coa = null): void
    {
        $parentId = $data['parent_id'] ?? null;
        $level = $data['level'] ?? null;

        if ($level === 'Header') {
            if (!empty($parentId)) {
                $validator->errors()->add('parent_id', 'Header tidak boleh memiliki parent.');
            }

            return;
        }

        if (empty($parentId)) {
            $validator->errors()->add('parent_id', 'Parent akun harus dipilih untuk level ini.');
            return;
        }

        $parent = Coa::find($parentId);

        if (!$parent) {
            return;
        }

        if ($coa && $parent->id === $coa->id) {
            $validator->errors()->add('parent_id', 'Parent akun tidak boleh sama dengan akun itu sendiri.');
            return;
        }

        $allowedParents = $this->allowedParentLevels($level);

        if (!in_array($parent->level, $allowedParents, true)) {
            $validator->errors()->add('parent_id', sprintf('Parent untuk level %s harus berada pada level %s.', $level, implode(' atau ', $allowedParents)));
            return;
        }

        if ($parent->tipe !== $data['tipe']) {
            $validator->errors()->add('parent_id', 'Parent akun harus memiliki tipe akun yang sama.');
            return;
        }

        if ($coa && $this->isAncestor($coa, $parent)) {
            $validator->errors()->add('parent_id', 'Parent akun tidak boleh merupakan akun turunannya sendiri.');
        }
    }

    private function allowedParentLevels(string $level): array
    {
        return match ($level) {
            'Sub Header' => ['Header'],
            'Kelompok Akun' => ['Sub Header'],
            'Akun Posting' => ['Kelompok Akun'],
            default => [],
        };
    }

    private function isAncestor(Coa $coa, Coa $possibleDescendant): bool
    {
        $current = $possibleDescendant;

        while ($current) {
            if ($current->parent_id === $coa->id) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }
}
