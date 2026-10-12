@extends('layouts.app')
@section('title','Master Sopir')

@push('styles')
<style>
    .driver-page { display: flex; flex-direction: column; gap: .65rem; min-height: 0; }
    .driver-table-scroll {
        max-height: calc(100vh - 285px);
        min-height: 220px;
        overflow: auto;
        scrollbar-gutter: stable;
    }
    .driver-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8fafc;
        box-shadow: inset 0 -1px #dee2e6;
        white-space: nowrap;
    }
    .driver-table-scroll td { vertical-align: middle; }
    @media (max-width: 767.98px) {
        .driver-table-scroll { max-height: calc(100vh - 300px); min-height: 260px; }
        .driver-actions { white-space: nowrap; }
    }
</style>
@endpush

@section('content')
<div class="driver-page">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div><h1 class="h4 mb-0">Master Sopir</h1><div class="text-muted small">Data sopir untuk piutang dan pelunasan.</div></div>
        <a href="{{ route('drivers.create') }}" class="btn btn-primary align-self-sm-center"><i class="bi bi-plus-lg me-1"></i> Tambah Sopir</a>
    </div>

    <form class="card border-0 shadow-sm mb-0" method="GET">
        <div class="card-body p-2">
            <div class="input-group"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari kode, nama, atau telepon sopir"><button class="btn btn-primary"><i class="bi bi-search me-1"></i> Cari</button>@if(request('search'))<a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary">Reset</a>@endif</div>
        </div>
    </form>

    <div class="card border-0 shadow-sm mb-0">
        <div class="table-responsive driver-table-scroll">
            <table class="table table-hover mb-0">
                <thead><tr><th>Kode</th><th>Nama Sopir</th><th>Telepon</th><th>Alamat</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($drivers as $driver)
                    <tr><td class="fw-semibold text-nowrap">{{ $driver->code }}</td><td>{{ $driver->name }}</td><td class="text-nowrap">{{ $driver->phone ?: '-' }}</td><td>{{ $driver->address ?: '-' }}</td><td><span class="badge text-bg-{{ $driver->is_active ? 'success' : 'secondary' }}">{{ $driver->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="driver-actions"><a href="{{ route('drivers.edit',$driver) }}" class="btn btn-warning btn-sm">Edit</a> <form class="d-inline" method="POST" action="{{ route('drivers.destroy',$driver) }}" onsubmit="return confirm('Hapus master sopir {{ $driver->code }}?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Hapus</button></form></td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data sopir.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($drivers->hasPages())<div class="pt-1">{{ $drivers->links() }}</div>@endif
</div>
@endsection
