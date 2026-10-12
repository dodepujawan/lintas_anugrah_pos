@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div><h2 class="page-title mb-1"><i class="bi bi-book me-2"></i>{{ $title }}</h2><p class="text-muted mb-0">Petunjuk praktis untuk input yang benar dan mudah diperiksa.</p></div>
    <div class="d-flex gap-2 d-print-none">
        <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary"><i class="bi bi-list-ul me-1"></i>Daftar</a>
        <a href="{{ route($createRoute) }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Mulai Input</a>
        <button type="button" onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Cetak</button>
    </div>
</div>
<div class="card shadow-sm"><div class="card-body p-4 p-lg-5"><article class="transaction-guide">{!! $html !!}</article></div></div>
@endsection

@push('styles')
<style>
    .transaction-guide h1:first-child, .transaction-guide h2:first-child { margin-top: 0 !important; }
    .transaction-guide table { width: 100%; border-collapse: collapse; }
    .transaction-guide pre { white-space: pre-wrap; }
    @media print { .d-print-none, nav, header { display:none !important; } .card { border:0 !important; box-shadow:none !important; } }
</style>
@endpush
