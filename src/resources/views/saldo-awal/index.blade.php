@extends('layouts.app')

@section('title', 'Saldo Awal Akun')

@push('styles')
<style>
    .filter-card {
        background: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div>
            <h2><i class="bi bi-wallet2"></i> Saldo Awal Akun</h2>
            <p class="text-muted mb-0">Kelola saldo awal akun dengan draft, posting, export Excel, dan cetak tampilan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('saldo-awal.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Saldo Awal
            </a>
            <a href="{{ route('saldo-awal.export', request()->all()) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            @include('laporan._print-format-dropdown',['pdfUrl'=>route('saldo-awal.pdf',request()->except(['page','print_format'])),'dotMatrixUrl'=>route('saldo-awal.pdf',request()->except(['page','print_format'])+['print_format'=>'dot_matrix']),'buttonClass'=>'btn-outline-secondary'])
        </div>
    </div>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('saldo-awal.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" name="tanggal_awal" value="{{ request('tanggal_awal', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" name="tanggal_akhir" value="{{ request('tanggal_akhir', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>Posted</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>No. Saldo Awal</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($saldoAwals as $index => $saldoAwal)
                    <tr>
                        <td>{{ $saldoAwals->firstItem() + $index }}</td>
                        <td><strong>{{ $saldoAwal->no_jurnal }}</strong></td>
                        <td>{{ $saldoAwal->tanggal->format('d/m/Y') }}</td>
                        <td>{{ Str::limit($saldoAwal->keterangan, 60) }}</td>
                        <td class="text-end">{{ number_format($saldoAwal->total_debit, 2) }}</td>
                        <td class="text-end">{{ number_format($saldoAwal->total_kredit, 2) }}</td>
                        <td>
                            @if($saldoAwal->status === App\Models\Jurnal::STATUS_POSTED)
                            <span class="badge bg-success">Posted</span>
                            @else
                            <span class="badge bg-warning text-dark">Draft</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('saldo-awal.show', $saldoAwal->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($saldoAwal->status === App\Models\Jurnal::STATUS_POSTED && auth()->user()?->role?->name === 'Administrator')
                            <form action="{{ route('saldo-awal.reopen', $saldoAwal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Buka kembali {{ $saldoAwal->no_jurnal }} menjadi Draft? Setelah dibuka, saldo awal harus diperiksa dan diposting ulang.');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Buka Kembali menjadi Draft">
                                    <i class="bi bi-unlock"></i>
                                </button>
                            </form>
                            @endif
                            @if($saldoAwal->status === App\Models\Jurnal::STATUS_DRAFT)
                            <a href="{{ route('saldo-awal.edit', $saldoAwal->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('saldo-awal.destroy', $saldoAwal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus Draft {{ $saldoAwal->no_jurnal }}? Semua item saldo awal di dalam Draft ini akan ikut dihapus dan tindakan ini tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus Draft"><i class="bi bi-trash"></i></button>
                            </form>
                            <form action="{{ route('saldo-awal.post', $saldoAwal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Posting saldo awal ini?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Posting">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">Belum ada data Saldo Awal</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $saldoAwals->links() }}
</div>
@endsection
