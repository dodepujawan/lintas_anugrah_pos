@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Kendaraan</h1>
            <p class="text-muted small mb-0">Daftar kendaraan operasional</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('kendaraan.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari Kode / Nama / Plat..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('kendaraan.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Plat</th>
                        <th>FNO PRK B</th>
                        <th>FNO PRK T</th>
                        <th>Jenis</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kendaraans as $k)
                    <tr>
                        <td>{{ $kendaraans->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $k->KODE }}</td>
                        <td>{{ $k->NAMA }}</td>
                        <td class="font-monospace">{{ $k->PLAT }}</td>
                        <td>{{ $k->FNO_PRK_B }}</td>
                        <td>{{ $k->FNO_PRK_T }}</td>
                        <td>{{ $k->JENIS }}</td>
                        <td class="text-end">
                            <a href="{{ route('kendaraan.edit', $k) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-2">Tidak ada data kendaraan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $kendaraans->links() }}
    </div>
</div>
@endsection
