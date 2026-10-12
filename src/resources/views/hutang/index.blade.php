@extends('layouts.app')

@section('title', 'Hutang Berjalan')

@section('content')
<div class="running-payable-page">
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title mb-1">Hutang Berjalan</h1>
        <p class="text-muted mb-0">Faktur supplier setelah mulai menggunakan sistem.</p>
    </div>
    <a href="{{ route('hutang.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Input Hutang Berjalan</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('hutang.index') }}" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Pencarian</label>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nomor faktur atau keterangan">
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <input type="search" name="supplier" value="{{ request('supplier') }}" class="form-control" placeholder="Kode supplier">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="{{ route('hutang.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-2"><div class="text-muted small">Jumlah Faktur</div><div class="fw-bold">{{ number_format((int) $totals->count, 0, ',', '.') }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-2"><div class="text-muted small">Total Hutang</div><div class="fw-bold">{{ number_format((float) $totals->hutang, 2, ',', '.') }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-2"><div class="text-muted small">Total Bayar</div><div class="fw-bold">{{ number_format((float) $totals->bayar, 2, ',', '.') }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-2"><div class="text-muted small">Total Saldo</div><div class="fw-bold">{{ number_format((float) $totals->saldo, 2, ',', '.') }}</div></div></div></div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>No Faktur</th>
                        <th>Supplier</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Hutang</th>
                        <th class="text-end">Bayar</th>
                        <th class="text-end">Saldo</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($hutangs as $hutang)
                        <tr>
                            <td>{{ $hutang->TGLFAKTUR->format('d M Y') }}</td>
                            <td><strong>{{ $hutang->NOFAKTUR }}</strong></td>
                            <td>{{ $hutang->supplier_display }}</td>
                            <td>{{ $hutang->TGLJT ? $hutang->TGLJT->format('d M Y') : '-' }}</td>
                            <td class="text-end">{{ number_format($hutang->HUTANG, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($hutang->BAYAR, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($hutang->SALDO, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $hutang->SALDO <= 0 ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $hutang->SALDO <= 0 ? 'Lunas' : 'Open' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('hutang.show', $hutang) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                @if (str_starts_with((string)$hutang->SOURCE, 'SALDO_AWAL_') && (float)$hutang->BAYAR <= 0 && (float)$hutang->RETUR <= 0 && (float)$hutang->DISCOUNT <= 0)
                                    <a href="{{ route('saldo-awal-hutang.edit', $hutang) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form method="POST" action="{{ route('saldo-awal-hutang.destroy', $hutang) }}" class="d-inline" onsubmit="return confirm('Hapus saldo awal hutang {{ addslashes($hutang->NOFAKTUR) }}? Data tidak dapat dikembalikan dan tindakan ini tidak membuat jurnal.');">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Hapus</button></form>
                                @elseif ($hutang->SOURCE === 'HUTANG_BERJALAN_MANUAL' && (float)$hutang->BAYAR <= 0 && (float)$hutang->RETUR <= 0 && (float)$hutang->DISCOUNT <= 0)
                                    <a href="{{ route('hutang.edit', $hutang) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('hutang.destroy', $hutang) }}" class="d-inline" onsubmit="return confirm('Hapus hutang berjalan {{ addslashes($hutang->NOFAKTUR) }} beserta jurnal terkait? Data tidak dapat dikembalikan.');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4">Belum ada data hutang berjalan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $hutangs->links() }}
</div>
</div>
@endsection

@push('styles')
<style>
.running-payable-page{font-size:.95rem}.running-payable-page .page-title{font-size:1.4rem}.running-payable-page .form-label{font-size:.85rem;margin-bottom:.3rem}.running-payable-page .form-control,.running-payable-page .btn{font-size:.85rem}.running-payable-page th,.running-payable-page td{padding:.4rem .5rem;font-size:.85rem;vertical-align:middle}.running-payable-page .badge{font-size:.78rem;padding:.25rem .45rem}.running-payable-page .table strong{font-weight:600}.running-payable-page .btn-sm{padding:.22rem .45rem;font-size:.78rem}.running-payable-page .card-body{padding:.85rem}.running-payable-page .card-body.p-0{padding:0!important}.running-payable-page table{min-width:860px}.running-payable-page .table-responsive{scrollbar-gutter:stable}.running-payable-page td.text-end,.running-payable-page th.text-end{font-variant-numeric:tabular-nums}
@media(max-width:767.98px){.running-payable-page table{min-width:920px}.running-payable-page .d-flex.justify-content-between{align-items:flex-start!important;gap:.75rem;flex-wrap:wrap}}
</style>
@endpush
