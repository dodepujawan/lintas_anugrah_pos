@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Rent Pendingin</h1>
            <p class="text-muted small mb-0">Daftar sewa mobil pendingin</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('rent-pendingin.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah SJ
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari NOSJ / Customer..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('rent-pendingin.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Invoice</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rents as $r)
                    <tr>
                        <td>{{ $rents->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $r->NOSJ }}</td>
                        <td>{{ $r->TGLMUAT?->format('d/m/Y') }}</td>
                        <td>{{ $r->CUSTOMER }}</td>
                        <td>{{ $r->NAMA_KENDARAAN ?? $r->KENDARAAN }}</td>
                        <td class="text-end">{{ number_format((float) $r->GRAND, 0, ',', '.') }}</td>
                        <td class="font-monospace">{{ $r->INVOICE ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('rent-pendingin.print.surjal', [], $r->NOSJ) }}" class="btn btn-sm btn-outline-primary" title="Print SJ">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data rent pendingin.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $rents->links() }}</div>
</div>
@endsection
