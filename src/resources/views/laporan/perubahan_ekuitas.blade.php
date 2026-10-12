@extends('layouts.app')

@section('title', 'Laporan Perubahan Ekuitas')

@section('content')
<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="page-title"><i class="bi bi-diagram-3 me-2"></i>Laporan Perubahan Ekuitas</h2>
            <small class="text-muted">Periode {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
            <small class="ms-2 badge bg-{{ ($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI' ? 'danger' : 'success' }}">{{ $periodLockStatus ?? 'TERBUKA' }}</small>
        </div>
        <div>
            <a href="{{ route('laporan.perubahan_ekuitas.excel', request()->all()) }}" class="btn btn-success me-2">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            @include('laporan._print-format-dropdown', ['pdfUrl'=>route('laporan.stream_pdf',['report'=>'perubahan-ekuitas']+request()->except('print_format')),'dotMatrixUrl'=>route('laporan.stream_pdf',['report'=>'perubahan-ekuitas','print_format'=>'dot_matrix']+request()->except('print_format'))])
        </div>
    </div>
</div>

<div class="print-only-header">
    <div class="title-line">
        <span class="page-title">LAPORAN PERUBAHAN EKUITAS</span>
    </div>
    <div class="period-line">
        <small class="text-muted">Periode: {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
    </div>
    <div class="period-line">
        <small class="text-muted">Status: {{ $periodLockStatus ?? 'TERBUKA' }}</small>
    </div>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.perubahan_ekuitas') }}" class="row g-3">
            <div class="col-md-4">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
            </div>
            <div class="col-md-4">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Keterangan</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Saldo Awal Laba Ditahan</td>
                    <td class="text-end">{{ number_format($saldoAwal, 2) }}</td>
                </tr>
                <tr>
                    <td>Laba/Rugi Bersih Periode</td>
                    <td class="text-end">{{ number_format($labaBersih, 2) }}</td>
                </tr>
                <tr>
                    <td>Koreksi/Dividen</td>
                    <td class="text-end">{{ number_format($koreksi, 2) }}</td>
                </tr>
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <th>Saldo Akhir Ekuitas</th>
                    <th class="text-end">{{ number_format($saldoAkhir, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@include('laporan._report-print')
