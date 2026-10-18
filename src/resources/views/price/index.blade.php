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

    <form method="GET" id="formFilterPrice" data-debug="transaksiPriceFilter" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari KODE / NAMA ITEM / DARI / SAMPAI / RUTE..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('price.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>KODE</th>
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
                    @forelse($prices as $p)
                    <tr>
                        <td>{{ $prices->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $p->KODE ?? '-' }}</td>
                        <td>{{ $p->KETERANGAN ?? '-' }}</td>
                        <td>{{ $p->DARI ?? '-' }}</td>
                        <td>{{ $p->SAMPAI ?? '-' }}</td>
                        <td>{{ $p->RUTE ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $p->HARGA, 0, ',', '.') }}</td>
                        <td>{{ $p->JENIS == 1 ? 'Eceran' : ($p->JENIS == 2 ? 'Booking' : '-') }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('price.edit', $p) }}" id="btnEditPrice_{{ $p->id }}" data-debug="transaksiPriceEditBtn_{{ $p->id }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form class="d-inline" method="POST" action="{{ route('price.destroy', $p) }}" onsubmit="return confirm('Hapus price {{ $p->KODE ?? $p->KETERANGAN }}?')">
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
                    <tr><td colspan="9" class="text-center py-4 text-muted">
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
