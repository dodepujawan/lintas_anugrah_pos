@extends('layouts.app')

@section('title', 'Detail Saldo Awal Akun')

@section('content')
@push('styles')
<style>
    @media (max-width: 768px) {
        .saldo-awal-detail .table {
            margin-bottom: 0;
            font-size: 0.7rem;
        }

        .saldo-awal-detail .table th,
        .saldo-awal-detail .table td {
            padding: 0.4rem 0.3rem !important;
        }

        .saldo-awal-detail .table th.text-end,
        .saldo-awal-detail .table td.text-end {
            white-space: nowrap;
            text-align: right !important;
            font-size: clamp(0.56rem, 2.3vw, 0.68rem);
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.03em;
        }

        .saldo-awal-detail .currency {
            display: none;
        }
    }
</style>
@endpush
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div>
            <h2><i class="bi bi-wallet2"></i> Detail Saldo Awal</h2>
            <p class="text-muted mb-0">Detail saldo awal yang sudah dibuat. Cetak halaman ini untuk dokumentasi.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('saldo-awal.export', ['tanggal_awal' => $saldo_awal->tanggal->format('Y-m-d'), 'tanggal_akhir' => $saldo_awal->tanggal->format('Y-m-d')]) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            @include('laporan._print-format-dropdown',[
                'pdfUrl' => route('saldo-awal.detail.pdf', ['saldo_awal' => $saldo_awal]),
                'dotMatrixUrl' => route('saldo-awal.detail.pdf', ['saldo_awal' => $saldo_awal, 'print_format' => 'dot_matrix']),
                'buttonClass' => 'btn-secondary'
            ])
            <a href="{{ route('saldo-awal.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4 saldo-awal-detail">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="fw-semibold text-secondary small">No. Saldo Awal</div>
                <div>{{ $saldo_awal->no_jurnal }}</div>
            </div>
            <div class="col-md-4">
                <div class="fw-semibold text-secondary small">Tanggal</div>
                <div>{{ $saldo_awal->tanggal->format('d/m/Y') }}</div>
            </div>
            <div class="col-md-4">
                <div class="fw-semibold text-secondary small">Status</div>
                <div>
                    @if($saldo_awal->status === App\Models\Jurnal::STATUS_POSTED)
                    <span class="badge bg-success">Posted</span>
                    @else
                    <span class="badge bg-warning text-dark">Draft</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="mb-4">
            <div class="fw-semibold text-secondary small">Keterangan</div>
            <div>{{ $saldo_awal->keterangan ?? '-' }}</div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($saldo_awal->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->coa->kode_akun }}</td>
                        <td>{{ $item->coa->nama_akun }}</td>
                        <td class="text-end">{{ number_format($item->debit, 2) }}</td>
                        <td class="text-end">{{ number_format($item->kredit, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($saldo_awal->total_debit, 2) }}</td>
                        <td class="text-end">{{ number_format($saldo_awal->total_kredit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
