@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Coolroom</h1>
            <p class="text-muted small mb-0">Daftar transaksi coolroom</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('coolroom.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari NOSJ / Customer..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('coolroom.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NOSJ</th>
                        <th>Tgl Muat</th>
                        <th>Customer</th>
                        <th>Kendaraan</th>
                        <th class="text-end">Grand</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coolrooms as $c)
                    <tr>
                        <td>{{ $coolrooms->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $c->NOSJ }}</td>
                        <td>{{ $c->TGLMUAT?->format('d/m/Y') }}</td>
                        <td>{{ $c->CUSTOMER }}</td>
                        <td>{{ $c->NAMA_KENDARAAN ?? $c->KENDARAAN }}</td>
                        <td class="text-end">{{ number_format((float) $c->GRAND, 0, ',', '.') }}</td>
                        <td>{{ $c->STATUS ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('coolroom.edit', $c) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data coolroom.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $coolrooms->links() }}</div>
</div>
@endsection
