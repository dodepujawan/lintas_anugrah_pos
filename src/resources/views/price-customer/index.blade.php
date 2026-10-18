@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceCustomerIndex">
    <div class="row mb-3" data-debug="transaksiPriceCustomerHeader">
        <div class="col">
            <h1 class="page-title mb-1">Price Customer</h1>
            <p class="text-muted small mb-0">Master harga per customer</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('price-customer.create') }}" id="btnTambahPriceCus" data-debug="transaksiPriceCustomerCreateBtn" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <form method="GET" id="formFilterPriceCus" data-debug="transaksiPriceCustomerFilter" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari KODECUS / KODE / Nama..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('price-customer.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>KODECUS</th>
                        <th>KODE</th>
                        <th>NAMACUS</th>
                        <th>DARI</th>
                        <th>SAMPAI</th>
                        <th>RUTE</th>
                        <th class="text-end">HARGA</th>
                        <th>JENIS</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($priceCuses as $p)
                    <tr>
                        <td>{{ $priceCuses->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $p->KODECUS }}</td>
                        <td>{{ $p->KODE ?? '-' }}</td>
                        <td>{{ $p->NAMACUS ?? '-' }}</td>
                        <td>{{ $p->DARI ?? '-' }}</td>
                        <td>{{ $p->SAMPAI ?? '-' }}</td>
                        <td>{{ $p->RUTE ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $p->HARGA, 0, ',', '.') }}</td>
                        <td>{{ $p->JENIS == 1 ? 'Eceran' : ($p->JENIS == 2 ? 'Boking' : '-') }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('price-customer.edit', $p) }}" id="btnEditPriceCus_{{ $p->id }}" data-debug="transaksiPriceCustomerEditBtn_{{ $p->id }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form class="d-inline" method="POST" action="{{ route('price-customer.destroy', $p) }}" onsubmit="return confirm('Hapus price customer {{ $p->KODECUS }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
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
    <div class="mt-3">{{ $priceCuses->links() }}</div>
</div>
@endsection
