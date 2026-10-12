@extends('layouts.app')

@section('title', 'Audit Saldo Normal COA')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h2 class="page-title"><i class="bi bi-shield-check me-2"></i>Audit Saldo Normal COA</h2>
            <p class="text-muted mb-0">Periksa akun yang saldo normalnya tidak sesuai tipe akun.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('coa-saldo-normal') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-clockwise me-1"></i> Audit Ulang
            </a>
            @if($suspiciousRows->isNotEmpty())
            <form method="POST" action="{{ route('coa-saldo-normal.fix') }}" onsubmit="return confirm('Perbaiki saldo normal COA sesuai rekomendasi?');">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-tools me-1"></i> Perbaiki
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total COA</div>
                <div class="fs-3 fw-bold">{{ number_format($rows->count(), 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Perlu Dicek</div>
                <div class="fs-3 fw-bold {{ $suspiciousRows->isEmpty() ? 'text-success' : 'text-warning' }}">
                    {{ number_format($suspiciousRows->count(), 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Akses</div>
                <div class="fs-5 fw-semibold">Administrator</div>
            </div>
        </div>
    </div>
</div>

@if($suspiciousRows->isEmpty())
<div class="alert alert-success">
    Semua saldo normal COA sudah sesuai rekomendasi.
</div>
@else
<div class="alert alert-warning">
    Ada {{ $suspiciousRows->count() }} akun yang perlu dicek. Tombol perbaikan hanya mengubah kolom <code>saldo_normal</code>.
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Kode</th>
                        <th>Nama Akun</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Sekarang</th>
                        <th>Rekomendasi</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suspiciousRows as $row)
                    <tr>
                        <td class="px-3 fw-semibold">{{ $row['kode'] }}</td>
                        <td>{{ $row['nama'] }}</td>
                        <td>{{ $row['tipe'] }}</td>
                        <td>{{ $row['kategori'] }}</td>
                        <td><span class="badge bg-danger-subtle text-danger">{{ $row['sekarang'] }}</span></td>
                        <td><span class="badge bg-success-subtle text-success">{{ $row['rekomendasi'] }}</span></td>
                        <td>{{ $row['alasan'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Tidak ada akun yang perlu diperbaiki.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
