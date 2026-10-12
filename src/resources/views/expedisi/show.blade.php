@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Detail Ekspedisi</h1>
            <p class="text-muted small">NOSJ: {{ $expedisi->NOSJ }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('expedisi.edit', $expedisi) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('expedisi.print.surjal', $expedisi) }}" class="btn btn-primary btn-sm" target="_blank">
                <i class="bi bi-printer"></i> Print SJ
            </a>
            <a href="{{ route('expedisi.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Dokumen</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>NOSJ</th><td class="font-monospace">{{ $expedisi->NOSJ }}</td></tr>
                        <tr><th>Tgl Muat</th><td>{{ $expedisi->TGLMUAT?->format('d/m/Y') }}</td></tr>
                        <tr><th>Tgl PO</th><td>{{ $expedisi->TGLPO?->format('d/m/Y') }}</td></tr>
                        <tr><th>No PO</th><td>{{ $expedisi->NOPO }}</td></tr>
                        <tr><th>Invoice</th><td class="font-monospace">{{ $expedisi->INVOICE ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Customer & Kendaraan</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>Customer</th><td>{{ $expedisi->CUSTOMER }}</td></tr>
                        <tr><th>Kendaraan</th><td>{{ $expedisi->NAMA_KENDARAAN ?? $expedisi->KENDARAAN }}</td></tr>
                        <tr><th>Driver</th><td>{{ $expedisi->NAMA_DRIVER ?? $expedisi->DRIVER }}</td></tr>
                        <tr><th>Rute</th><td>{{ $expedisi->rute }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header"><h6 class="mb-0">Nilai & Total</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2"><label class="form-label">Jumlah (KM)</label><input type="text" class="form-control form-control-sm" value="{{ $expedisi->JUMLAH }}" readonly></div>
                <div class="col-md-2"><label class="form-label">Harga</label><input type="text" class="form-control form-control-sm" value="{{ $expedisi->HARGA }}" readonly></div>
                <div class="col-md-2"><label class="form-label">Disc</label><input type="text" class="form-control form-control-sm" value="{{ $expedisi->DISC }}" readonly></div>
                <div class="col-md-2"><label class="form-label">NDISC</label><input type="text" class="form-control form-control-sm" value="{{ $expedisi->NDISC }}" readonly></div>
                <div class="col-md-2"><label class="form-label">NDISCAW</label><input type="text" class="form-control form-control-sm" value="{{ $expedisi->NDISCAW }}" readonly></div>
                <div class="col-md-2"><label class="form-label">Total</label><input type="text" class="form-control form-control-sm" value="{{ $expedisi->TOTAL }}" readonly></div>
                <div class="col-md-2"><label class="form-label">PPN</label><input type="text" class="form-control form-control-sm" value="{{ $expedisi->PPN }}" readonly></div>
                <div class="col-md-2"><label class="form-label">Grand Total</label><input type="text" class="form-control form-control-sm fw-bold" value="{{ $expedisi->GRAND }}" readonly></div>
            </div>
        </div>
    </div>

    @if($expedisi->NOJURNAL)
    <div class="card mt-3">
        <div class="card-header"><h6 class="mb-0">Journal</h6></div>
        <div class="card-body">
            <p class="mb-0"><strong>NOJURNAL:</strong> <code>{{ $expedisi->NOJURNAL }}</code></p>
        </div>
    </div>
    @endif
</div>
@endsection
