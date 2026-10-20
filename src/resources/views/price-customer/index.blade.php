@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceCustomerIndex">
    <div class="row mb-3" data-debug="transaksiPriceCustomerHeader">
        <div class="col">
            <h1 class="page-title mb-1">Price Customer</h1>
            <p class="text-muted small mb-0">Daftar customer untuk atur harga per customer</p>
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
                   placeholder="Cari Kode / Nama Customer..." value="{{ request('search') }}">
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
                        <th>Kode Customer</th>
                        <th>Nama Customer</th>
                        <th>Jenis Usaha</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                    <tr>
                        <td>{{ $customers->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $c->CUSTOMER }}</td>
                        <td>{{ $c->NAMACUST ?? '-' }}</td>
                        <td>{{ $c->TYPECUST ?? '-' }}</td>
                        <td>{{ $c->TELEPON ?? '-' }}</td>
                        <td>{{ $c->EMAIL ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('price-customer.customer-prices', trim($c->CUSTOMER)) }}" id="btnViewPriceCus_{{ $c->CUSTOMER }}" data-debug="transaksiPriceCustomerView_{{ $c->CUSTOMER }}" class="btn btn-sm btn-outline-primary" title="Lihat Price">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data customer.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $customers->links() }}</div>
</div>
@endsection
