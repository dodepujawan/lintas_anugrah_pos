@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceIndex">
    <div class="row mb-3" data-debug="transaksiPriceHeader">
        <div class="col">
            <h1 class="page-title mb-1">Price EKS</h1>
            <p class="text-muted small mb-0">Master harga ekspedisi</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('price.create') }}" id="btnTambahPrice" data-debug="transaksiPriceCreateBtn" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>FROM</th>
                        <th>TO</th>
                        <th class="text-end">HARGA</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prices as $p)
                    <tr>
                        <td>{{ $prices->firstItem() + $loop->index }}</td>
                        <td>{{ $p->FROM }}</td>
                        <td>{{ $p->TO }}</td>
                        <td class="text-end">{{ number_format((float) $p->HARGA, 0, ',', '.') }}</td>
                        <td>{{ $p->TANGGAL }}</td>
                        <td class="text-end">
                            <a href="{{ route('price.edit', $p) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i> Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data price.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $prices->links() }}</div>
</div>
@endsection
