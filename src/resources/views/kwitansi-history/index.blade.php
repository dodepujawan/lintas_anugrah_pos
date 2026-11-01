@extends('layouts.app')

@section('content')
@php
    $canExportKwitansiHistory = auth()->user()?->hasMenuPermission('kwitansi-history.index', 'export') ?? false;
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Data Kwitansi</h1>
            <p class="text-muted small mb-0">
                {{ $status === 'belum' ? 'Invoice yang belum memiliki kwitansi' : 'Kwitansi yang sudah diproses' }}
            </p>
        </div>
    </div>

    @if($canExportKwitansiHistory)
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('kwitansi-history.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="status_kwt_his" class="form-label">Status Kwitansi</label>
                    <select name="status_kwt_his" id="status_kwt_his" class="form-select" data-debug="transaksiKwitansiHistoryStatus">
                        <option value="belum" @selected($status === 'belum')>Belum Kwitansi</option>
                        <option value="sudah" @selected($status === 'sudah')>Sudah Kwitansi</option>
                    </select>
                </div>
                <div class="col-12 col-md-5 col-lg-6">
                    <label for="kwitansi_history_search" class="form-label">Pencarian</label>
                    <input
                        type="search"
                        name="search"
                        id="kwitansi_history_search"
                        class="form-control"
                        placeholder="Cari nomor kwitansi, invoice, atau keterangan"
                        value="{{ $search }}"
                        data-debug="transaksiKwitansiHistorySearch"
                    >
                </div>
                <div class="col-12 col-md-3 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" data-debug="transaksiKwitansiHistoryFilterBtn">Tampilkan</button>
                    <a href="{{ route('kwitansi-history.index', ['status_kwt_his' => $status]) }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($canExportKwitansiHistory)
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('kwitansi-history.export') }}" class="row g-3 align-items-end">
                <input type="hidden" name="status_kwt_his" value="{{ $status }}">
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" data-debug="transaksiKwitansiHistoryDateFrom">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" value="{{ now()->format('Y-m-d') }}" data-debug="transaksiKwitansiHistoryDateTo">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <button type="submit" class="btn btn-success w-100" data-debug="transaksiKwitansiHistoryExportBtn">
                        <i class="bi bi-file-earmark-excel me-1" aria-hidden="true"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="h6 mb-0">{{ $status === 'belum' ? 'Daftar Belum Kwitansi' : 'Daftar Sudah Kwitansi' }}</h2>
            <span class="text-muted small">{{ number_format($kwitantis->total()) }} data</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-debug="transaksiKwitansiHistoryTable">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>No. KWT</th>
                        <th>Tanggal</th>
                        <th>Invoice</th>
                        <th>Jenis</th>
                        <th>Keterangan</th>
                        <th class="text-end">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kwitantis as $kwitansi)
                        <tr>
                            <td>{{ $kwitantis->firstItem() + $loop->index }}</td>
                            <td class="font-monospace">{{ $kwitansi->NOKWT ?: '-' }}</td>
                            <td>{{ $kwitansi->TGLINVOICE ? \Illuminate\Support\Carbon::parse($kwitansi->TGLINVOICE)->format('d-m-Y') : '-' }}</td>
                            <td class="font-monospace">{{ $kwitansi->INVOICE }}</td>
                            <td>{{ in_array($kwitansi->JENIS, ['COOL', 'COL', 'COO'], true) ? 'Coolroom' : $kwitansi->JENIS }}</td>
                            <td>{{ $kwitansi->KETERANGAN }}</td>
                            <td class="text-end">{{ number_format((float) $kwitansi->NILAI, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2" aria-hidden="true"></i>
                                Tidak ada data untuk status dan pencarian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="6" class="fw-semibold">TOTAL NILAI</td>
                        <td class="text-end fw-semibold">{{ number_format($totalNilai, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $kwitantis->links() }}</div>
</div>
@endsection
