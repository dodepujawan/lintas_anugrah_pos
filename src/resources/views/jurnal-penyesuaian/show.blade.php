@extends('layouts.app')

@section('title', 'Detail Jurnal Penyesuaian')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div>
            <h2><i class="bi bi-journal-text"></i> Detail Jurnal Penyesuaian</h2>
            <p class="text-muted mb-0">Detail Jurnal Penyesuaian yang sudah dibuat. Cetak halaman ini untuk dokumentasi.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-secondary"><i class="bi bi-printer"></i> Cetak</button>
            <a href="{{ route('jurnal-penyesuaian.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="fw-semibold text-secondary small">No. Jurnal</div>
                <div>{{ $jurnal_penyesuaian->no_jurnal }}</div>
            </div>
            <div class="col-md-4">
                <div class="fw-semibold text-secondary small">Tanggal</div>
                <div>{{ $jurnal_penyesuaian->tanggal->format('d/m/Y') }}</div>
            </div>
            <div class="col-md-4">
                <div class="fw-semibold text-secondary small">Status</div>
                <div>
                    @if($jurnal_penyesuaian->status === App\Models\Jurnal::STATUS_POSTED)
                    <span class="badge bg-success">Posted</span>
                    @else
                    <span class="badge bg-warning text-dark">Draft</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="mb-4">
            <div class="fw-semibold text-secondary small">Keterangan</div>
            <div>{{ $jurnal_penyesuaian->keterangan ?? '-' }}</div>
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
                    @foreach($jurnal_penyesuaian->items as $index => $item)
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
                        <td class="text-end">{{ number_format($jurnal_penyesuaian->total_debit, 2) }}</td>
                        <td class="text-end">{{ number_format($jurnal_penyesuaian->total_kredit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection