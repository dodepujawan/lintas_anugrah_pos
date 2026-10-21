@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceRentIndex">
    <div class="row mb-3" data-debug="transaksiPriceRentHeader">
        <div class="col">
            <h1 class="page-title mb-1">Price Rent</h1>
            <p class="text-muted small mb-0">Master harga sewa pendingin</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('price-rent.create') }}" id="btnTambahPriceRent" data-debug="transaksiPriceRentCreateBtn" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="GET" id="formFilterPriceRent" data-debug="transaksiPriceRentFilter" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari KODEDGN / kendaraan / PLAT / ITEM / PERIODE..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('price-rent.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>KODE</th>
                        <th>Kendaraan</th>
                        <th>ITEM</th>
                        <th>PERIODE</th>
                        <th>PLAT</th>
                        <th>JENIS</th>
                        <th class="text-end">HARGA</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prices as $p)
                    <tr>
                        <td>{{ $prices->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $p->KODE ?? '-' }}</td>
                        <td>{{ $p->kendaraan?->NAMA ?? '-' }}</td>
                        <td>{{ $p->ITEM ?? '-' }}</td>
                        <td>{{ $p->PERIODE ?? '-' }}</td>
                        <td class="font-monospace">{{ $p->PLAT ?? '-' }}</td>
                        <td>{{ $p->JENIS ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $p->HARGA, 0, ',', '.') }}</td>
                        <td>{{ $p->TANGGAL ? \Carbon\Carbon::parse($p->TANGGAL)->format('d/m/Y') : '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('price-rent.edit', $p) }}" id="btnEditPriceRent_{{ $p->id }}" data-debug="transaksiPriceRentEditBtn_{{ $p->id }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $prices->links() }}</div>
</div>
@endsection
