@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceRentCusIndex">
    <div class="row mb-3" data-debug="transaksiPriceRentCusHeader">
        <div class="col">
            <h1 class="page-title mb-1">Price Rent Customer</h1>
            <p class="text-muted small mb-0">Master harga sewa customer</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('price-rent-customer.create') }}" id="btnTambahPriceRentCus" data-debug="transaksiPriceRentCusCreateBtn" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <form method="GET" id="formFilterPriceRentCus" data-debug="transaksiPriceRentCusFilter" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari KODECUS / KODE / KODEDGN..." value="{{ request('search') }}">
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
                        <th>KODECUS</th>
                        <th>Jenis Customer Name</th>
                        <th>KODEDGN</th>
                        <th>KODE</th>
                        <th>PERIODE</th>
                        <th>PLAT</th>
                        <th>JENIS</th>
                        <th>ITEM</th>
                        <th>FROM</th>
                        <th>TO</th>
                        <th class="text-end">HARGA</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($prices as $p)
                    <tr>
                        <td>{{ $prices->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $p->KODECUS ?? '-' }}</td>
                        <td>{{ $p->NAMACUS ?? '-' }}</td>
                        <td>{{ $p->KODEDGN ?? '-' }}</td>
                        <td>{{ $p->KODE ?? '-' }}</td>
                        <td>{{ $p->PERIODE ?? '-' }}</td>
                        <td class="font-monospace">{{ $p->PLAT ?? '-' }}</td>
                        <td>{{ $p->JENIS ?? '-' }}</td>
                        <td>{{ $p->ITEM ?? '-' }}</td>
                        <td>{{ $p->FROM ?? '-' }}</td>
                        <td>{{ $p->TO ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $p->HARGA, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a href="{{ route('price-rent-customer.edit', $p) }}" id="btnEditPriceRentCus_{{ $p->id }}" data-debug="transaksiPriceRentCusEditBtn_{{ $p->id }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i> Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="13" class="text-center py-4 text-muted">
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
