@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceRentCusIndex">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Harga Dingin Customer</h1>
            <p class="text-muted small mb-0">Pilih customer untuk melihat dan mengatur harga sewa pendingin.</p>
        </div>
    </div>

    <form method="GET" id="formFilterPriceRentCus" data-debug="transaksiPriceRentCusFilter" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control" placeholder="Cari kode, nama, jenis usaha, telepon, email..." value="{{ $search }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('price-rent-customer.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Jenis Usaha</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customers->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $customer->CUSTOMER }}</td>
                        <td>{{ $customer->NAMACUST ?? '-' }}</td>
                        <td>{{ $customer->TYPECUST ?? '-' }}</td>
                        <td>{{ $customer->TELEPON ?? '-' }}</td>
                        <td>{{ $customer->EMAIL ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('price-rent-customer.customer-prices', $customer->CUSTOMER) }}" class="btn btn-sm btn-outline-primary" data-debug="transaksiPriceRentCusView_{{ $customer->CUSTOMER }}" title="Lihat harga">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada customer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $customers->links() }}</div>
</div>
@endsection
