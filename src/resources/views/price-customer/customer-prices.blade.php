@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceCustomerDetail">
    <div class="row mb-3" data-debug="transaksiPriceCustomerDetailHeader">
        <div class="col">
            <h1 class="page-title mb-1">Price Customer</h1>
            <p class="text-muted small mb-0">Harga untuk customer: <strong>{{ $customer->CUSTOMER }}</strong> - {{ $customer->NAMACUST ?? '-' }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('price-customer.index') }}" id="btnKembaliPriceCus" data-debug="transaksiPriceCustomerDetailBack" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('price-customer.create', ['kodecus' => $customer->CUSTOMER]) }}" id="btnTambahPriceCus" data-debug="transaksiPriceCustomerDetailCreateBtn" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <form method="GET" id="formFilterPriceCus" data-debug="transaksiPriceCustomerDetailFilter" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari Nama Item / DARI / SAMPAI / RUTE..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('price-customer.customer-prices', $customer->CUSTOMER) }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NAMA ITEM</th>
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
                        <td>{{ $p->KETERANGAN ?? '-' }}</td>
                        <td>{{ $p->DARI ?? '-' }}</td>
                        <td>{{ $p->SAMPAI ?? '-' }}</td>
                        <td>{{ $p->RUTE ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $p->HARGA, 0, ',', '.') }}</td>
                        <td>{{ $p->JENIS == 1 ? 'Eceran' : ($p->JENIS == 2 ? 'Boking' : '-') }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('price-customer.edit', $p) }}" id="btnEditPriceCus_{{ $p->id }}" data-debug="transaksiPriceCustomerDetailEditBtn_{{ $p->id }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form class="d-inline" method="POST" action="{{ route('price-customer.destroy', $p) }}" onsubmit="return confirm('Hapus price customer {{ $p->KETERANGAN }}?')">
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
                    <tr><td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data price untuk customer ini.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $priceCuses->links() }}</div>
</div>
@endsection
