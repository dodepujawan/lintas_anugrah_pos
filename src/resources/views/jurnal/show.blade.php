@extends('layouts.app')

@section('title', 'Detail Jurnal')

@section('content')
<div class="journal-screen-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <div><h1 class="journal-screen-title mb-0">Bukti Jurnal Umum</h1><small class="text-muted">{{ $jurnal->no_jurnal }}</small></div>
        <div class="d-flex gap-1 flex-wrap">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-file-earmark-pdf"></i> Cetak PDF</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a target="_blank" class="dropdown-item" href="{{ route('jurnal.pdf', [$jurnal->id, 'paper' => 'half']) }}">Setengah Letter (8,5 × 5,5)</a></li>
                    <li><a target="_blank" class="dropdown-item" href="{{ route('jurnal.pdf', [$jurnal->id, 'paper' => 'full']) }}">Full Letter Portrait</a></li>
                </ul>
            </div>
            @if($canEdit)
            <a href="{{ route('jurnal.edit', $jurnal->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <form action="{{ route('jurnal.destroy', $jurnal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus seluruh jurnal {{ $jurnal->no_jurnal }}? Semua item jurnal akan ikut dihapus dan tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Hapus Jurnal
                </button>
            </form>
            @endif
            @if(! $jurnal->is_posted && ! ($viewOnly ?? false))
            <form action="{{ route('jurnal.post', $jurnal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Posting jurnal ini?')">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg"></i> Posting
                </button>
            </form>
            @endif
            <a href="{{ $backUrl }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> {{ $backLabel }}
            </a>
        </div>
</div>

<div class="journal-print-header">
    <div class="journal-print-title">BUKTI JURNAL UMUM</div>
    <div class="journal-print-status {{ $jurnal->is_posted ? 'is-posted' : 'is-draft' }}">
        {{ $jurnal->is_posted ? 'POSTED' : 'DRAFT — BELUM DIPOSTING' }}
    </div>
</div>

<div class="row mb-2 journal-info-section">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Jurnal</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="25%">No. Jurnal</th>
                        <td><strong>{{ $jurnal->no_jurnal }}</strong></td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>{{ $jurnal->tanggal->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <td>{{ $jurnal->keterangan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Referensi</th>
                        <td>{{ $jurnal->referensi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($jurnal->is_posted)
                            <span class="badge bg-success">Posted{{ $jurnal->posted_at ? ' ('.$jurnal->posted_at->format('d/m/Y H:i').')' : '' }}</span>
                            @else
                            <span class="badge bg-warning">Draft</span>
                            @endif
                            @if(abs((float) $jurnal->total_debit - (float) $jurnal->total_kredit) > 0.01)
                            <span class="badge bg-warning text-dark ms-1">Tidak Balance: Selisih {{ number_format(abs((float) $jurnal->total_debit - (float) $jurnal->total_kredit), 2) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat Oleh</th>
                        <td>{{ $jurnal->createdBy->name ?? 'System' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Item Jurnal</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Deskripsi</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurnal->items as $item)
                    <tr>
                        <td>{{ $item->coa?->kode_akun ?? 'Unknown' }}</td>
                        <td>{{ $item->coa?->nama_akun ?? 'Unknown' }}</td>
                        <td>{{ $item->deskripsi ?? '-' }}</td>
                        <td class="text-end">{{ number_format($item->debit, 2) }}</td>
                        <td class="text-end">{{ number_format($item->kredit, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="3">Total</th>
                        <th class="text-end">{{ number_format($jurnal->total_debit, 2) }}</th>
                        <th class="text-end">{{ number_format($jurnal->total_kredit, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="journal-approval">
    <div>
        <div>Dibuat oleh,</div>
        <div class="journal-sign-space"></div>
        <strong>{{ $jurnal->createdBy->name ?? '________________' }}</strong>
    </div>
    <div>
        <div>Diperiksa oleh,</div>
        <div class="journal-sign-space"></div>
        <strong>________________</strong>
    </div>
    <div>
        <div>Disetujui oleh,</div>
        <div class="journal-sign-space"></div>
        <strong>________________</strong>
    </div>
</div>
@endsection

@push('styles')
<style>
    .journal-screen-title { font-size: 1.12rem; font-weight: 700; }
    .journal-screen-header small { font-size: .86rem; }
    .journal-screen-header .btn { padding: .28rem .52rem; font-size: .78rem; }
    .journal-info-section .card-header { padding: .35rem .55rem; }
    .journal-info-section .card-title { font-size: .98rem; }
    .journal-info-section .card-body { padding: .48rem .65rem; }
    .journal-info-section .table { margin-bottom: 0; font-size: .84rem; }
    .journal-info-section .table th,
    .journal-info-section .table td { padding: .32rem .42rem; }
    .journal-info-section .badge { font-size: .78rem; padding: .32rem .48rem; white-space: normal; text-align: left; line-height: 1.25; }
    .journal-info-section td .badge + .badge { margin-top: .15rem; }
    .card > .card-header .card-title { font-size: 1rem; }
    .card > .card-body > .table-responsive .table { font-size: .84rem; }
    .card > .card-body > .table-responsive .table th,
    .card > .card-body > .table-responsive .table td { padding: .45rem .55rem; line-height: 1.28; }

    @media (max-width: 767.98px) {
        .journal-screen-title { font-size: 1.05rem; }
        .journal-screen-header small { font-size: .8rem; }
        .journal-screen-header .btn { font-size: .76rem; }
        .journal-info-section .table { font-size: .82rem; }
        .journal-info-section .badge { font-size: .76rem; }
        .card > .card-body > .table-responsive .table { font-size: .8rem; }
    }

    .journal-print-header,
    .journal-approval { display: none; }

    @media print {
        @page { size: A4 portrait; margin: 12mm; }

        .journal-screen-header { display: none !important; }

        .journal-print-header {
            display: block !important;
            position: relative;
            text-align: center;
            margin-bottom: 7px;
            padding-bottom: 5px;
            border-bottom: 1px solid #111;
        }

        .journal-print-title { font-size: 12pt; font-weight: 800; letter-spacing: .4px; }
        .journal-print-status {
            position: absolute;
            top: 1px;
            right: 0;
            padding: 3px 7px;
            border: 1px solid #111;
            font-size: 7pt;
            font-weight: 800;
        }

        .journal-print-status.is-draft { border-style: dashed; }

        .row.mb-4 {
            display: flex !important;
            gap: 10px;
            margin-bottom: 10px !important;
        }

        .row.mb-4 > .col-12 { width: 100% !important; }

        .card { border: 1px solid #555 !important; border-radius: 0 !important; }
        .card-header { padding: 5px 7px !important; background: #eee !important; border-bottom: 1px solid #555 !important; }
        .card-title { font-size: 9pt !important; font-weight: 700 !important; }
        .card-body { padding: 6px 7px !important; }

        .table { margin: 0 !important; font-size: 8pt !important; border-collapse: collapse !important; }
        .table th,
        .table td { padding: 4px 5px !important; border: 1px solid #555 !important; }
        .table thead th,
        .table tfoot th { background: #eee !important; color: #000 !important; }
        .badge { color: #000 !important; background: transparent !important; border: 1px solid #555; }

        .journal-approval {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 22px;
            text-align: center;
            font-size: 8pt;
            break-inside: avoid;
        }

        .journal-sign-space { height: 52px; }
    }
</style>
@endpush

@if($printMode)
@push('scripts')
<script>
    window.addEventListener('load', function () {
        window.setTimeout(function () { window.print(); }, 100);
    });
</script>
@endpush
@endif
