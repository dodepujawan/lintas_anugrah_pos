@extends('layouts.app')

@section('title', 'Detail Rekening')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Detail Rekening</h1>
            <p class="text-muted small mb-0">Informasi rekening bank.</p>
        </div>
        <a href="{{ route('rekening.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm" style="max-width: 720px">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Bank</dt><dd class="col-sm-8">{{ $rekening->BANK }}</dd>
                <dt class="col-sm-4 text-muted">Nomor Rekening</dt><dd class="col-sm-8 font-monospace">{{ $rekening->NOREK }}</dd>
                <dt class="col-sm-4 text-muted">Nama Pemilik</dt><dd class="col-sm-8">{{ $rekening->NAMA ?: '-' }}</dd>
                <dt class="col-sm-4 text-muted">Status</dt><dd class="col-sm-8">{{ (string) $rekening->AKTIF === '1' ? 'Aktif' : 'Tidak aktif' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
