@extends('layouts.app')

@section('title', 'Pengaturan Pajak')

@section('content')
@php
    $canEditPajak = auth()->user()?->hasMenuPermission('pajak.get.update', 'edit') ?? false;
@endphp

<div class="container-fluid">
    <div class="mb-4">
        <h1 class="page-title mb-1">Pengaturan Pajak</h1>
        <p class="text-muted small mb-0">Atur persentase PPN yang digunakan pada transaksi baru.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="card shadow-sm" style="max-width: 720px">
        <div class="card-header">
            <h2 class="h6 mb-0">Tarif PPN</h2>
        </div>
        <div class="card-body">
            @if ($canEditPajak)
                <form method="POST" action="{{ route('pajak.get.update') }}" data-debug="extraPajakForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="PPN" class="form-label">PPN (%)</label>
                        <div class="input-group" style="max-width: 360px">
                            <input
                                type="number"
                                id="PPN"
                                name="PPN"
                                class="form-control @error('PPN') is-invalid @enderror"
                                min="0"
                                max="100"
                                step="0.01"
                                value="{{ old('PPN', $pajak?->PPN ?? 0) }}"
                                required
                                data-debug="extraPajakPpn"
                            >
                            <span class="input-group-text">%</span>
                            @error('PPN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Tarif ini dipakai sebagai nilai awal saat transaksi baru dibuat.</div>
                    </div>

                    <button type="submit" class="btn btn-primary" data-debug="extraPajakSaveBtn">
                        <i class="bi bi-check2 me-1" aria-hidden="true"></i> Simpan Perubahan
                    </button>
                </form>
            @else
                <div class="text-muted small mb-2">Tarif PPN saat ini</div>
                <div class="fs-4 fw-semibold">{{ number_format((float) ($pajak?->PPN ?? 0), 2, ',', '.') }}%</div>
            @endif
        </div>
    </div>
</div>
@endsection
