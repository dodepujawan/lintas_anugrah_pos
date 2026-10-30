@extends('layouts.app')

@section('title', 'Detail Surat Jalan Coolroom')

@section('content')
<div class="container-fluid" data-debug="transaksiCoolroomShow">
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="small text-uppercase fw-semibold text-primary mb-1">Transaksi / Coolroom</div>
            <h1 class="page-title mb-1">Surat Jalan {{ $coolroom->NOSJ }}</h1>
            <p class="text-muted mb-0">Rincian jumlah, biaya, dan perhitungan surat jalan.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('coolroom.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Daftar</a>
            @if (! trim((string) $coolroom->INVOICE))
                <a href="{{ route('coolroom.edit', $coolroom) }}" class="btn btn-outline-primary" data-debug="transaksiCoolroomShowEdit"><i class="bi bi-pencil-square me-1"></i>Edit</a>
            @endif
            <a href="{{ route('coolroom.pdf', $coolroom->NOSJ) }}" target="_blank" rel="noopener" class="btn btn-primary" data-debug="transaksiCoolroomShowPdf"><i class="bi bi-printer me-1"></i>Cetak SJ</a>
        </div>
    </div>

    @if (trim((string) $coolroom->INVOICE))
        <div class="alert alert-info" role="status">Surat jalan ini sudah ditautkan ke invoice <strong>{{ $coolroom->INVOICE }}</strong>; data tidak dapat diedit atau dihapus.</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">Informasi Surat Jalan</h2></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted fw-normal">No. SJ</dt><dd class="col-sm-7 font-monospace fw-semibold">{{ $coolroom->NOSJ }}</dd>
                        <dt class="col-sm-5 text-muted fw-normal">Tanggal SJ</dt><dd class="col-sm-7">{{ $coolroom->TGLSJ?->format('d/m/Y') ?? '-' }}</dd>
                        <dt class="col-sm-5 text-muted fw-normal">Kode Customer</dt><dd class="col-sm-7">{{ $coolroom->CUSTOMER_KODE ?: '-' }}</dd>
                        <dt class="col-sm-5 text-muted fw-normal">Customer</dt><dd class="col-sm-7">{{ $coolroom->CUSTOMER ?: '-' }}</dd>
                        <dt class="col-sm-5 text-muted fw-normal">Invoice</dt><dd class="col-sm-7">{{ $coolroom->INVOICE ?: 'Belum dibuat' }}</dd>
                        <dt class="col-sm-5 text-muted fw-normal">Keterangan</dt><dd class="col-sm-7 text-break">{{ $coolroom->KETERANGAN ?: '-' }}</dd>
                    </dl>
                </div>
            </section>
        </div>
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">Perhitungan Tagihan</h2></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-7 text-muted fw-normal">Jumlah</dt><dd class="col-5 text-end">{{ number_format((float) $coolroom->JUMLAH, 3, ',', '.') }} KG</dd>
                        <dt class="col-7 text-muted fw-normal">Harga per KG</dt><dd class="col-5 text-end">Rp {{ number_format((float) $coolroom->HARGA, 0, ',', '.') }}</dd>
                        <dt class="col-7 text-muted fw-normal">Mode harga</dt><dd class="col-5 text-end">{{ $coolroom->BOXING ? 'Boxing / paket' : 'Per KG' }}</dd>
                        <dt class="col-7 text-muted fw-normal">Subtotal</dt><dd class="col-5 text-end">Rp {{ number_format((float) $coolroom->SUBTOTAL, 0, ',', '.') }}</dd>
                        <dt class="col-7 text-muted fw-normal">Diskon ({{ number_format((float) $coolroom->DISC, 2, ',', '.') }}%)</dt><dd class="col-5 text-end">Rp {{ number_format((float) $coolroom->NDISC, 0, ',', '.') }}</dd>
                        <dt class="col-7 text-muted fw-normal">DPP</dt><dd class="col-5 text-end">Rp {{ number_format((float) $coolroom->DPP, 0, ',', '.') }}</dd>
                        <dt class="col-7 text-muted fw-normal">PPN ({{ number_format((float) $coolroom->PPN, 2, ',', '.') }}%)</dt><dd class="col-5 text-end">Rp {{ number_format((float) $coolroom->NPPN, 0, ',', '.') }}</dd>
                    </dl>
                    <div class="d-flex justify-content-between align-items-center border-top mt-3 pt-3 fs-5 fw-bold">
                        <span>Grand Total</span><span>Rp {{ number_format((float) $coolroom->GRAND, 0, ',', '.') }}</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
