@extends('layouts.app')

@section('title', $title ?? 'Panduan Tutup Buku')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="page-title">
                <i class="bi bi-book-half me-2"></i>{{ $title ?? 'Panduan Tutup Buku' }}
            </h2>
            <p class="text-muted">Dokumen panduan langkah-langkah Tutup Buku / Tutup Tahun di aplikasi.</p>
        </div>
        <div>
            <a href="{{ route('laporan.tutup_buku') }}" class="btn btn-primary me-2">
                <i class="bi bi-arrow-right-circle me-1"></i> Buka Halaman Tutup Buku
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4 md:p-6">
        <article class="prose max-w-none">
            {!! $content !!}
        </article>
    </div>
</div>
@endsection

@push('styles')
<style>
    .prose h1:first-child,
    .prose h2:first-child { margin-top: 0 !important; }
    .prose table { border-collapse: collapse; }
    .prose pre { white-space: pre-wrap; }
    @media print {
        .btn, nav, header { display: none !important; }
        .card { box-shadow: none !important; border: none !important; }
    }
</style>
@endpush
