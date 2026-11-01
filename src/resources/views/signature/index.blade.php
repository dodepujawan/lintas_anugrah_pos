@extends('layouts.app')

@section('title', 'Penanggung Jawab')

@section('content')
@php
    $canEditSignature = auth()->user()?->hasMenuPermission('signature.get.update', 'edit') ?? false;
@endphp

<div class="container-fluid">
    <div class="mb-4">
        <h1 class="page-title mb-1">Penanggung Jawab</h1>
        <p class="text-muted small mb-0">Nama dan jabatan ini digunakan pada dokumen transaksi.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="card shadow-sm" style="max-width: 720px">
        <div class="card-header">
            <h2 class="h6 mb-0">Data Penanggung Jawab</h2>
        </div>
        <div class="card-body">
            @if ($canEditSignature)
                <form method="POST" action="{{ route('signature.get.update') }}" data-debug="extraSignatureForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="NAMA" class="form-label">Nama</label>
                        <input
                            type="text"
                            id="NAMA"
                            name="NAMA"
                            class="form-control @error('NAMA') is-invalid @enderror"
                            maxlength="100"
                            value="{{ old('NAMA', $signature?->NAMA ?? '') }}"
                            required
                            data-debug="extraSignatureName"
                        >
                        @error('NAMA')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="JABATAN" class="form-label">Jabatan <span class="text-muted">(opsional)</span></label>
                        <input
                            type="text"
                            id="JABATAN"
                            name="JABATAN"
                            class="form-control @error('JABATAN') is-invalid @enderror"
                            maxlength="100"
                            value="{{ old('JABATAN', $signature?->JABATAN ?? '') }}"
                            data-debug="extraSignaturePosition"
                        >
                        @error('JABATAN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary" data-debug="extraSignatureSaveBtn">
                        <i class="bi bi-check2 me-1" aria-hidden="true"></i> Simpan Perubahan
                    </button>
                </form>
            @else
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Nama</dt>
                    <dd class="col-sm-9">{{ $signature?->NAMA ?? '-' }}</dd>
                    <dt class="col-sm-3 text-muted">Jabatan</dt>
                    <dd class="col-sm-9">{{ $signature?->JABATAN ?? '-' }}</dd>
                </dl>
            @endif
        </div>
    </div>
</div>
@endsection
