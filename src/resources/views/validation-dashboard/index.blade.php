@extends('layouts.app')

@section('title', 'Validasi Data')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="page-title"><i class="bi bi-clipboard2-check me-2"></i>Validasi Data</h2>
        <small class="text-muted">Ringkasan pengecekan data akuntansi sebelum posting, closing, dan backup.</small>
    </div>
</div>

<div class="row">
    @foreach($summary as $item)
    <div class="col-md-3 mb-4">
        <div class="card h-100 border-{{ $item['variant'] }}">
            <div class="card-body">
                <div class="text-muted">{{ $item['label'] }}</div>
                <div class="display-6 fw-bold text-{{ $item['variant'] }}">{{ $item['count'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">Jurnal Tidak Balance</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Jurnal</th>
                            <th>Tanggal</th>
                            <th class="text-end">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unbalancedJournals as $jurnal)
                        <tr>
                            <td>{{ $jurnal->no_jurnal }}</td>
                            <td>{{ $jurnal->tanggal?->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format(abs($jurnal->total_debit - $jurnal->total_kredit), 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada masalah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">Draft Lebih 7 Hari</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Jurnal</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($oldDraftJournals as $jurnal)
                        <tr>
                            <td>{{ $jurnal->no_jurnal }}</td>
                            <td>{{ $jurnal->tanggal?->format('d/m/Y') }}</td>
                            <td>{{ $jurnal->keterangan ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada draft lama.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">COA Tanpa Saldo Normal</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Akun</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coaWithoutSaldoNormal as $coa)
                        <tr>
                            <td>{{ $coa->kode_akun }}</td>
                            <td>{{ $coa->nama_akun }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">Semua akun posting sudah memiliki saldo normal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">Kesiapan Closing dan Backup</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted">Mapping Tutup Buku Belum Lengkap</div>
                    @forelse($missingMappings as $mapping)
                    <span class="badge bg-warning text-dark me-1">{{ $mapping }}</span>
                    @empty
                    <span class="badge bg-success">Lengkap</span>
                    @endforelse
                </div>
                <div>
                    <div class="text-muted">Backup Terakhir</div>
                    @if($latestBackup)
                    <strong>{{ $latestBackup['name'] }}</strong>
                    <div class="small text-muted">{{ $latestBackup['modified_at']->format('d/m/Y H:i:s') }}</div>
                    @else
                    <span class="text-muted">Belum ada backup.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
