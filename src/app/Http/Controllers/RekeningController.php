<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Rekening;
use Illuminate\Http\Request;

class RekeningController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $rekenings = Rekening::query()
            ->when($search, function ($query) use ($search) {
                $query->where('BANK', 'like', "%{$search}%")
                    ->orWhere('NOREK', 'like', "%{$search}%")
                    ->orWhere('NAMA', 'like', "%{$search}%");
            })
            ->orderBy('BANK')
            ->paginate(20)
            ->withQueryString();

        return view('rekening.index', compact('rekenings', 'search'));
    }

    public function create()
    {
        return view('rekening.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'BANK' => 'required|string|max:40',
            'NOREK' => 'required|string|max:40',
            'NAMA' => 'nullable|string|max:40',
            'AKTIF' => 'nullable|string|max:1',
        ]);

        $user = $request->user()->name ?? 'system';
        $validated['USER'] = $user;

        Rekening::create($validated);

        return redirect()->route('rekening.index')
            ->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function show(Rekening $rekening)
    {
        return view('rekening.show', compact('rekening'));
    }

    public function edit(Rekening $rekening)
    {
        return view('rekening.edit', compact('rekening'));
    }

    public function update(Request $request, Rekening $rekening)
    {
        $validated = $request->validate([
            'BANK' => 'required|string|max:40',
            'NOREK' => 'required|string|max:40',
            'NAMA' => 'nullable|string|max:40',
            'AKTIF' => 'nullable|string|max:1',
        ]);

        $validated['USER'] = $request->user()->name ?? 'system';

        $rekening->update($validated);

        return redirect()->route('rekening.index')
            ->with('success', 'Rekening berhasil diperbarui.');
    }

    public function destroy(Rekening $rekening)
    {
        $rekening->delete();

        return redirect()->route('rekening.index')
            ->with('success', 'Rekening berhasil dihapus.');
    }

    public function pilih(Request $request)
    {
        $rekening = Rekening::find($request->route('id'));

        return response()->json($rekening);
    }
}
