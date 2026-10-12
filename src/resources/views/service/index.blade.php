@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Service</h1>
            <p class="text-muted small mb-0">Daftar riwayat service kendaraan</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('service.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari Kendaraan / NOSJ..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('service.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tgl Service</th>
                        <th>Kendaraan</th>
                        <th>NOSJ</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $s)
                    <tr>
                        <td>{{ $services->firstItem() + $loop->index }}</td>
                        <td>{{ $s->TGL_SERVICE?->format('d/m/Y') }}</td>
                        <td>{{ $s->NAMA_KENDARAAN ?? $s->KENDARAAN }}</td>
                        <td class="font-monospace">{{ $s->NOSJ }}</td>
                        <td class="text-end">{{ number_format((float) $s->HARGA, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $s->JUMLAH, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a href="{{ route('service.edit', $s) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data service.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $services->links() }}</div>
</div>
@endsection
