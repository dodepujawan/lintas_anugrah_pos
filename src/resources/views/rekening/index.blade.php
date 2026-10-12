@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Rekening</h1>
            <p class="text-muted small mb-0">Daftar rekening bank</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('rekening.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Bank</th>
                        <th>No. Rekening</th>
                        <th>Nama</th>
                        <th>Aktif</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekenings as $r)
                    <tr>
                        <td>{{ $rekenings->firstItem() + $loop->index }}</td>
                        <td>{{ $r->BANK }}</td>
                        <td class="font-monospace">{{ $r->NOREK }}</td>
                        <td>{{ $r->NAMA }}</td>
                        <td>{{ $r->AKTIF ? 'Ya' : 'Tidak' }}</td>
                        <td class="text-end">
                            <a href="{{ route('rekening.edit', $r) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i> Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data rekening.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $rekenings->links() }}</div>
</div>
@endsection
