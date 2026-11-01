@extends('layouts.app')

@section('title', 'Rekening')

@section('content')
@php
    $canCreateRekening = auth()->user()?->hasMenuPermission('rekening.create', 'create') ?? false;
    $canEditRekening = auth()->user()?->hasMenuPermission('rekening.edit', 'edit') ?? false;
    $canChooseRekening = auth()->user()?->hasMenuPermission('rekening.pilih.post', 'post') ?? false;
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Rekening</h1>
            <p class="text-muted small mb-0">Kelola rekening bank dan pilih rekening untuk dicantumkan pada dokumen.</p>
        </div>
        @if ($canCreateRekening)
            <a href="{{ route('rekening.create') }}" class="btn btn-primary" data-debug="extraRekeningCreateBtn">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah Rekening
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="h6 mb-0">Daftar Rekening</h2>
            <form method="GET" action="{{ route('rekening.index') }}" class="d-flex gap-2">
                <input type="search" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="Cari bank atau nomor" aria-label="Cari rekening">
                <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-debug="extraRekeningTable">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Bank</th>
                        <th>No. Rekening</th>
                        <th>Nama Pemilik</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekenings as $rekening)
                        @php($isActive = (string) $rekening->AKTIF === '1')
                        <tr>
                            <td>{{ $rekenings->firstItem() + $loop->index }}</td>
                            <td class="fw-semibold">{{ $rekening->BANK }}</td>
                            <td class="font-monospace">{{ $rekening->NOREK }}</td>
                            <td>{{ $rekening->NAMA ?: '-' }}</td>
                            <td>
                                @if ($isActive)
                                    <span class="badge text-bg-success">Aktif</span>
                                @else
                                    <span class="badge text-bg-light border text-secondary">Tidak aktif</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('rekening.show', $rekening) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                @if ($canEditRekening)
                                    <a href="{{ route('rekening.edit', $rekening) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endif
                                @if ($canChooseRekening && ! $isActive)
                                    <form method="POST" action="{{ route('rekening.pilih.post', $rekening) }}" class="d-inline" onsubmit="return confirm('Jadikan rekening ini sebagai rekening aktif?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" data-debug="extraRekeningChooseBtn">Pilih Aktif</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-bank fs-2 d-block mb-2" aria-hidden="true"></i>
                                Belum ada rekening yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $rekenings->links() }}</div>
</div>
@endsection
