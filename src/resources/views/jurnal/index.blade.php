@extends('layouts.app')

@section('title', 'Daftar Jurnal')

@push('styles')
<style>
    .filter-card {
        background: #f8f9fa;
    }

    .journal-list-table {
        table-layout: fixed;
        min-width: 900px;
        margin-bottom: 0;
    }

    .journal-list-table .journal-number-col { width: 14%; }
    .journal-list-table .journal-date-col { width: 9%; }
    .journal-list-table .journal-description-col { width: 27%; }
    .journal-list-table .journal-amount-col { width: 12%; }
    .journal-list-table .journal-status-col { width: 11%; }
    .journal-list-table .journal-action-col { width: 15%; }

    .journal-list-table th {
        padding: .55rem .6rem;
        font-size: .82rem;
        white-space: nowrap;
    }

    .journal-list-table td {
        padding: .5rem .6rem;
        font-size: .88rem;
        line-height: 1.25;
        vertical-align: middle;
    }

    .journal-list-table td:nth-child(1) {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: .84rem;
        font-weight: 400;
        white-space: nowrap;
    }

    .journal-list-table td:nth-child(2) {
        font-size: .84rem;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
        padding-left: .8rem;
    }

    .journal-list-table td:nth-child(3) {
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: normal;
    }

    .journal-list-table td:nth-child(4),
    .journal-list-table td:nth-child(5) {
        font-size: .84rem;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .journal-list-table td:nth-child(6) {
        white-space: normal;
    }

    .journal-list-table td:nth-child(6) .badge {
        display: inline-block;
        max-width: 100%;
        font-size: .72rem;
        white-space: normal;
        line-height: 1.1;
        text-align: center;
    }

    .journal-actions {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: .2rem;
        white-space: nowrap;
    }

    .journal-actions form { margin: 0; }

    .journal-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        padding: 0;
        font-size: .72rem;
    }

    .journal-list-table tr.journal-highlight-row > td {
        background: #dcfce7 !important;
        box-shadow: inset 0 1px 0 #86efac, inset 0 -1px 0 #86efac;
    }

    .journal-list-table tr.journal-highlight-row > td:first-child {
        border-left: 4px solid #16a34a;
    }

</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2><i class="bi bi-journal-text"></i> Daftar Jurnal</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('panduan.jurnal_umum') }}" class="btn btn-outline-info">
                <i class="bi bi-book"></i> Panduan Input
            </a>
            <a href="{{ route('jurnal.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Jurnal
            </a>
        </div>
    </div>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('jurnal.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ request('tanggal_awal', $tanggalAwal) }}">
            </div>
            <div class="col-md-3">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ request('tanggal_akhir', $tanggalAkhir) }}">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-select" id="status">
                    <option value="">Semua Status</option>
                    <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Void</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="search" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="No jurnal, referensi, akun, keterangan">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('jurnal.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover journal-list-table">
                <colgroup>
                    <col class="journal-number-col">
                    <col class="journal-date-col">
                    <col class="journal-description-col">
                    <col class="journal-amount-col">
                    <col class="journal-amount-col">
                    <col class="journal-status-col">
                    <col class="journal-action-col">
                </colgroup>
                <thead>
                    <tr>
                        <th>No. Jurnal</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-end">Total Debit</th>
                        <th class="text-end">Total Kredit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurnals as $jurnal)
                    @php($canEdit = $jurnal->status !== App\Models\Jurnal::STATUS_VOID && !in_array((int) $jurnal->tanggal->year, $lockedYears, true))
                    @php($isHighlighted = (string) request('highlight') === (string) $jurnal->id)
                    <tr id="jurnal-{{ $jurnal->id }}" @class(['journal-highlight-row' => $isHighlighted])>
                        <td>{{ $jurnal->no_jurnal }}</td>
                        <td>{{ $jurnal->tanggal->format('d/m/Y') }}</td>
                        <td>{{ $jurnal->keterangan }}</td>
                        <td class="text-end">{{ number_format($jurnal->total_debit, 2) }}</td>
                        <td class="text-end">{{ number_format($jurnal->total_kredit, 2) }}</td>
                        <td>
                            @if($jurnal->status === App\Models\Jurnal::STATUS_POSTED)
                            <span class="badge bg-success">Posted</span>
                            @elseif($jurnal->status === App\Models\Jurnal::STATUS_VOID)
                            <span class="badge bg-danger">Void</span>
                            @else
                            <span class="badge bg-warning">Draft</span>
                            @endif
                            @if(abs((float) $jurnal->total_debit - (float) $jurnal->total_kredit) > 0.01)
                            <div class="mt-1"><span class="badge bg-warning text-dark">Tidak Balance</span></div>
                            @endif
                        </td>
                        <td><div class="journal-actions">
                            <a href="{{ route('jurnal.show', $jurnal->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($canEdit)
                            <a href="{{ route('jurnal.edit', $jurnal->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @if(!$jurnal->is_posted)
                            <form action="{{ route('jurnal.post', $jurnal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Posting jurnal ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Posting">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <form action="{{ route('jurnal.destroy', $jurnal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus Draft {{ $jurnal->no_jurnal }}? Semua item jurnal di dalam Draft ini akan ikut dihapus dan tindakan ini tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data jurnal</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $jurnals->links() }}
        </div>
    </div>
</div>

@if(request('highlight'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    var row = document.getElementById('jurnal-{{ request('highlight') }}');
    if (row) {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    if (window.history && window.history.replaceState) {
        window.history.replaceState({}, '', '{{ route('jurnal.index') }}');
    }
});
</script>
@endif
@endsection
