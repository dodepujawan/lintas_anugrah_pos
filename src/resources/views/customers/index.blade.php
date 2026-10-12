@extends('layouts.app')
@section('title','Master Customer')

@section('content')
<div class="d-flex flex-column gap-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div><h1 class="h4 mb-0">Master Customer</h1><div class="text-muted small">Data pelanggan dari tabel mcustomer.</div></div>
        <div class="d-flex gap-2">
            @if(auth()->user()?->hasExplicitMenuPermission('customers', 'delete'))
                <a href="{{ route('customers.duplicates') }}" class="btn btn-outline-warning"><i class="bi bi-intersect me-1"></i> Cek Duplikat</a>
            @endif
            <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Customer</a>
        </div>
    </div>

    <form class="card border-0 shadow-sm" method="GET">
        <div class="card-body p-2">
            <div class="input-group"><input name="search" value="{{ $search }}" class="form-control" placeholder="Cari kode, nama, kota, atau telepon customer"><button class="btn btn-primary"><i class="bi bi-search me-1"></i> Cari</button>@if($search !== '')<a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Reset</a>@endif</div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive" style="max-height: calc(100vh - 285px); min-height: 240px;">
            <table class="table table-hover mb-0">
                <thead class="table-light" style="position: sticky; top: 0; z-index: 1;"><tr><th>Kode</th><th>Nama Customer</th><th>Kota</th><th>Telepon</th><th>Kontak</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($customers as $customer)
                    <tr><td class="fw-semibold text-nowrap">{{ $customer->CUSTOMER }}</td><td>{{ $customer->NAMACUST ?: '-' }}</td><td>{{ $customer->KOTA ?: '-' }}</td><td class="text-nowrap">{{ $customer->TELEPON ?: '-' }}</td><td>{{ $customer->KONTAK ?: '-' }}</td><td class="text-end text-nowrap"><a href="{{ route('customers.edit',$customer) }}" class="btn btn-warning btn-sm">Edit</a> <form class="d-inline" method="POST" action="{{ route('customers.destroy',$customer) }}" onsubmit="return confirm('Hapus master customer {{ $customer->CUSTOMER }}?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Hapus</button></form></td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data customer.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($customers->hasPages())<div>{{ $customers->links() }}</div>@endif
</div>
@endsection
