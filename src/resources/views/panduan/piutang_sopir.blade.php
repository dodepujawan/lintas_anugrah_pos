@extends('layouts.app')

@section('title', $title ?? 'Panduan Piutang Sopir')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div><h2 class="page-title mb-1"><i class="bi bi-truck me-2"></i>{{ $title ?? 'Panduan Piutang Sopir' }}</h2><p class="text-muted mb-0">Panduan mencatat tanggungan, pelunasan, jurnal, edit, dan pembatalan piutang sopir.</p></div>
        <div class="d-flex gap-2 flex-wrap d-print-none">
            <a href="{{ route('driver-opening-receivables.create') }}" class="btn btn-outline-primary"><i class="bi bi-wallet2 me-1"></i>Input Saldo Awal</a>
            <a href="{{ route('driver-receivables.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Input Piutang</a>
            <a href="{{ route('driver-payments.create') }}" class="btn btn-success"><i class="bi bi-cash-coin me-1"></i>Input Pelunasan</a>
            <button type="button" onclick="window.print()" class="btn btn-secondary"><i class="bi bi-printer me-1"></i>Cetak</button>
        </div>
    </div>
</div>
<div class="card shadow-sm"><div class="card-body p-4 p-lg-5"><article class="prose max-w-none">{!! $content !!}</article></div></div>
@endsection

@push('styles')
<style>
    .prose h1:first-child,.prose h2:first-child{margin-top:0!important}.prose pre{white-space:pre-wrap}
    @media print{.d-print-none,nav,header{display:none!important}.card{box-shadow:none!important;border:none!important}}
</style>
@endpush
