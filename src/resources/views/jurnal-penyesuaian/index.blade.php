@extends('layouts.app')

@section('title', 'Jurnal Penyesuaian')

@push('styles')
<style>
    .filter-card {
        background: #f8f9fa;
    }
    .journal-index-actions .btn { white-space: nowrap; }
    .journal-print-header { display: none; }
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        .journal-screen-header, .filter-card, .journal-pagination, .journal-action-column { display: none !important; }
        .journal-print-header { display: block !important; text-align: center; margin-bottom: 10px; }
        .journal-print-header h2 { font-size: 14pt; margin: 0; }
        .journal-print-header div { font-size: 9pt; }
        .table th, .table td { padding: 3px 5px !important; font-size: 8pt !important; }
    }
</style>
@endpush

@section('content')
<div class="row mb-3 journal-screen-header">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div>
            <h2><i class="bi bi-journal-text"></i> Jurnal Penyesuaian</h2>
            <p class="text-muted mb-0">Kelola Jurnal Penyesuaian dengan draft, posting, export Excel, dan cetak tampilan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 journal-index-actions">
            <a href="{{ route('jurnal-penyesuaian.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Jurnal Penyesuaian
            </a>
            <a href="{{ route('panduan.jurnal_penyesuaian') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-question-circle"></i> Panduan
            </a>
            <a href="{{ route('jurnal-penyesuaian.export', request()->all()) }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            @include('laporan._print-format-dropdown',['pdfUrl'=>route('jurnal-penyesuaian.pdf',request()->except(['page','print','print_format'])),'dotMatrixUrl'=>route('jurnal-penyesuaian.pdf',request()->except(['page','print','print_format'])+['print_format'=>'dot_matrix']),'buttonClass'=>'btn-outline-secondary btn-sm'])
        </div>
    </div>
</div>

<div class="journal-print-header">
    <h2>DAFTAR JURNAL PENYESUAIAN</h2>
    <div>Periode {{ date('d/m/Y', strtotime(request('tanggal_awal', now()->startOfMonth()->format('Y-m-d')))) }} s.d. {{ date('d/m/Y', strtotime(request('tanggal_akhir', now()->format('Y-m-d')))) }}</div>
    <div>Status: {{ request('status') === 'posted' ? 'Posted' : (request('status') === 'draft' ? 'Draft' : 'Semua') }}</div>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('jurnal-penyesuaian.index') }}" class="row g-3 align-items-end">
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
                        <th>No. Jurnal</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th>Status</th>
                        <th class="journal-action-column">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurnals as $index => $jurnal)
                    <tr>
                        <td>{{ $jurnals->firstItem() + $index }}</td>
                        <td><strong>{{ $jurnal->no_jurnal }}</strong></td>
                        <td>{{ $jurnal->tanggal->format('d/m/Y') }}</td>
                        <td>{{ Str::limit($jurnal->keterangan, 60) }}</td>
                        <td class="text-end">{{ number_format($jurnal->total_debit, 2) }}</td>
                        <td class="text-end">{{ number_format($jurnal->total_kredit, 2) }}</td>
                        <td>
                            @if($jurnal->status === App\Models\Jurnal::STATUS_POSTED)
                            <span class="badge bg-success">Posted</span>
                            @else
                            <span class="badge bg-warning text-dark">Draft</span>
                            @endif
                        </td>
                        <td class="text-nowrap journal-action-column">
                            @php($isLocked = in_array((int) $jurnal->tanggal->year, $lockedYears ?? [], true))
                            <a href="{{ route('jurnal-penyesuaian.show', $jurnal->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(!$isLocked && (int) $jurnal->archived_items_count === 0 && in_array($jurnal->status, [App\Models\Jurnal::STATUS_DRAFT, App\Models\Jurnal::STATUS_POSTED], true))
                            <a href="{{ route('jurnal-penyesuaian.edit', $jurnal->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @if(!$isLocked && $jurnal->status === App\Models\Jurnal::STATUS_DRAFT)
                            <form action="{{ route('jurnal-penyesuaian.destroy', $jurnal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus Draft {{ $jurnal->no_jurnal }}? Semua item jurnal penyesuaian di dalam Draft ini akan ikut dihapus dan tindakan ini tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus Draft"><i class="bi bi-trash"></i></button>
                            </form>
                            <form action="{{ route('jurnal-penyesuaian.post', $jurnal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Posting jurnal penyesuaian ini?');">
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
                        <td colspan="8" class="text-center py-4">Belum ada data Jurnal Penyesuaian</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-3 journal-pagination">
    {{ $jurnals->links() }}
</div>
@endsection

@if(request()->boolean('print'))
@push('scripts')
<script>
    window.addEventListener('load', function () {
        window.setTimeout(function () { window.print(); }, 100);
    });
</script>
@endpush
@endif
