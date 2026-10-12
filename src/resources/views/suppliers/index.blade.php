@extends('layouts.app')
@section('title','Master Supplier')

@section('content')
<div class="d-flex flex-column gap-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div><h1 class="h4 mb-0">Master Supplier</h1><div class="text-muted small">Data pemasok dari tabel msupplier.</div></div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Supplier</a>
    </div>

    <form class="card border-0 shadow-sm" method="GET">
        <div class="card-body p-2">
            <div class="input-group"><input name="search" value="{{ $search }}" class="form-control" placeholder="Cari kode, nama, kota, atau telepon supplier"><button class="btn btn-primary"><i class="bi bi-search me-1"></i> Cari</button>@if($search !== '')<a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Reset</a>@endif</div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive" style="max-height: calc(100vh - 285px); min-height: 240px;">
            <table class="table table-hover mb-0">
                <thead class="table-light" style="position: sticky; top: 0; z-index: 1;"><tr><th>Kode</th><th>Nama Supplier</th><th>Kota</th><th>Telepon</th><th>Kontak</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($suppliers as $supplier)
                    <tr><td class="fw-semibold text-nowrap">{{ $supplier->SUPPLIER }}</td><td>{{ $supplier->NAMA ?: '-' }}</td><td>{{ $supplier->KOTA ?: '-' }}</td><td class="text-nowrap">{{ $supplier->TELEPON ?: '-' }}</td><td>{{ $supplier->KONTAK ?: '-' }}</td><td class="text-end text-nowrap"><a href="{{ route('suppliers.edit',$supplier) }}" class="btn btn-warning btn-sm">Edit</a> <form class="d-inline" method="POST" action="{{ route('suppliers.destroy',$supplier) }}" onsubmit="return confirm('Hapus master supplier {{ $supplier->SUPPLIER }}?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Hapus</button></form></td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data supplier.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($suppliers->hasPages())<div>{{ $suppliers->links() }}</div>@endif
</div>
@endsection
