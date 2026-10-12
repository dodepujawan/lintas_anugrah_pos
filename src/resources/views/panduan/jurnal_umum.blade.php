@extends('layouts.app')

@section('title', $title ?? 'Panduan Input Jurnal Umum')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h2 class="page-title mb-1">
                <i class="bi bi-journal-check me-2"></i>{{ $title ?? 'Panduan Input Jurnal Umum' }}
            </h2>
            <p class="text-muted mb-0">Langkah praktis mencatat transaksi yang seimbang dan mempostingnya ke buku besar.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap d-print-none">
            <a href="{{ route('jurnal.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> Daftar Jurnal
            </a>
            <a href="{{ route('jurnal.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Mulai Input Jurnal
            </a>
            <button type="button" onclick="window.print()" class="btn btn-secondary">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4 p-lg-5">
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
        .d-print-none, nav, header { display: none !important; }
        .card { box-shadow: none !important; border: none !important; }
    }
</style>
@endpush
