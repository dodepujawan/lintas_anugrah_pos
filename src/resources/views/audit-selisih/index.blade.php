@extends('layouts.app')

@section('title', 'Audit Selisih')

@section('content')
<div class="audit-header d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h2 class="page-title mb-0"><i class="bi bi-search-heart me-2"></i>Audit Selisih</h2>
        <small class="text-muted">Mendeteksi perbedaan dan membuka sumber transaksi penyebabnya.</small>
    </div>
    <span class="badge bg-{{ $summary['issues'] > 0 ? 'danger' : 'success' }} audit-result-badge">
        {{ $summary['issues'] > 0 ? $summary['issues'].' temuan perlu diperiksa' : 'Tidak ada temuan' }}
    </span>
</div>

<div class="card audit-filter mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('audit-selisih') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" value="{{ $tanggalAwal }}" class="form-control form-control-sm" required>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir }}" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Jenis Pemeriksaan</label>
                <select name="jenis" class="form-select form-select-sm">
                    <option value="semua" @selected($jenis === 'semua')>Semua Temuan</option>
                    <option value="rekonsiliasi" @selected($jenis === 'rekonsiliasi')>Rekonsiliasi Piutang</option>
                    <option value="penjualan" @selected($jenis === 'penjualan')>Audit Penjualan</option>
                    <option value="jurnal" @selected($jenis === 'jurnal')>Konsistensi Jurnal</option>
                    <option value="sumber" @selected($jenis === 'sumber')>Sumber Transaksi</option>
                    <option value="duplikat" @selected($jenis === 'duplikat')>Referensi Ganda</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Jalankan Audit</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-2 audit-summary mb-3">
    <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Pemeriksaan</small><div class="audit-number">{{ $summary['checks'] }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card h-100 border-success"><div class="card-body"><small class="text-muted">Sesuai</small><div class="audit-number text-success">{{ $summary['ok'] }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card h-100 {{ $summary['issues'] ? 'border-danger' : 'border-success' }}"><div class="card-body"><small class="text-muted">Temuan Ditampilkan</small><div class="audit-number {{ $summary['issues'] ? 'text-danger' : 'text-success' }}">{{ $summary['issues'] }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card h-100 {{ abs($summary['difference']) > .01 ? 'border-danger' : 'border-success' }}"><div class="card-body"><small class="text-muted">Selisih Piutang</small><div class="audit-number {{ abs($summary['difference']) > .01 ? 'text-danger' : 'text-success' }}">{{ number_format($summary['difference'], 2, ',', '.') }}</div></div></div></div>
</div>

<div class="card audit-impact mb-3 {{ $summary['issues'] ? 'border-danger' : 'border-success' }}">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <small class="text-muted">Total Selisih Temuan</small>
                <div class="audit-total-selisih {{ $summary['issues'] ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format($summary['total_selisih'], 2, ',', '.') }}
                </div>
            </div>
            <span class="badge bg-{{ $summary['issues'] ? 'danger' : 'success' }}">
                {{ $summary['issues'] ? 'Perlu ditindaklanjuti' : 'Aman' }}
            </span>
        </div>
        <div class="audit-impact-warning mt-2">
            <div class="fw-semibold mb-1">
                <i class="bi bi-exclamation-triangle me-1"></i>Peringatan akibat selisih
            </div>
            <ul class="mb-0 ps-3">
                @foreach($impactWarnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header py-2 fw-semibold">Ringkasan Pemeriksaan</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 audit-table">
            <thead class="table-light"><tr><th>Pemeriksaan</th><th>Status</th><th class="text-end">Buku Pembantu</th><th class="text-end">Buku Besar</th><th class="text-end">Selisih</th><th>Kesimpulan</th></tr></thead>
            <tbody>
            @foreach($checks as $check)
                <tr>
                    <td class="fw-semibold">{{ $check['label'] }}</td>
                    <td><span class="badge bg-{{ $check['status'] === 'ok' ? 'success' : ($check['status'] === 'warning' ? 'warning text-dark' : 'danger') }}">{{ $check['status'] === 'ok' ? 'Sesuai' : 'Periksa' }}</span></td>
                    <td class="text-end">{{ $check['expected'] === null ? '-' : number_format($check['expected'], 2, ',', '.') }}</td>
                    <td class="text-end">{{ $check['actual'] === null ? '-' : number_format($check['actual'], 2, ',', '.') }}</td>
                    <td class="text-end fw-semibold {{ $check['difference'] !== null && abs($check['difference']) > .01 ? 'text-danger' : '' }}">{{ $check['difference'] === null ? '-' : number_format($check['difference'], 2, ',', '.') }}</td>
                    <td>{{ $check['message'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Daftar Temuan dan Sumber</span>
        <small class="text-muted">Klik Lacak untuk membuka data asal</small>
    </div>
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle mb-0 audit-table">
            <thead class="table-light"><tr><th>Jenis</th><th>Temuan</th><th>Penyebab</th><th>Penjelasan</th><th class="text-end">Nilai/Selisih</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($filteredIssues as $issue)
                <tr>
                    <td><span class="badge bg-{{ $issue['severity'] === 'danger' ? 'danger' : 'warning text-dark' }}">{{ ucfirst($issue['category']) }}</span></td>
                    <td class="fw-semibold">{{ $issue['title'] }}</td>
                    <td class="audit-cause">{{ $issue['cause'] ?? '-' }}</td>
                    <td>{{ $issue['description'] }}</td>
                    <td class="text-end text-nowrap">{{ $issue['amount'] === null ? '-' : number_format($issue['amount'], 2, ',', '.') }}</td>
                    <td class="text-end"><a href="{{ $issue['url'] }}" class="btn btn-sm btn-outline-primary text-nowrap"><i class="bi bi-box-arrow-up-right me-1"></i>{{ $issue['action'] }}</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-success py-4"><i class="bi bi-check-circle me-1"></i>Tidak ada temuan untuk filter ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-md-none audit-mobile-list">
        @forelse($filteredIssues as $issue)
            <div class="audit-mobile-item">
                <div class="d-flex justify-content-between gap-2"><strong>{{ $issue['title'] }}</strong><span class="badge bg-{{ $issue['severity'] === 'danger' ? 'danger' : 'warning text-dark' }}">{{ ucfirst($issue['category']) }}</span></div>
                <div class="audit-mobile-cause mt-1"><span class="fw-semibold">Penyebab:</span> {{ $issue['cause'] ?? '-' }}</div>
                <div class="text-muted mt-1">{{ $issue['description'] }}</div>
                @if($issue['amount'] !== null)<div class="fw-semibold text-danger mt-1">{{ number_format($issue['amount'], 2, ',', '.') }}</div>@endif
                <a href="{{ $issue['url'] }}" class="btn btn-sm btn-outline-primary w-100 mt-2">{{ $issue['action'] }}</a>
            </div>
        @empty
            <div class="text-center text-success py-4"><i class="bi bi-check-circle me-1"></i>Tidak ada temuan.</div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    .audit-header .page-title { font-size: 1.3rem; }
    .audit-header small { font-size: .86rem; }
    .audit-result-badge { padding: .52rem .75rem; font-size: .8rem; }
    .audit-filter .form-label, .audit-filter .form-control, .audit-filter .form-select, .audit-filter .btn { font-size: .8rem; }
    .audit-summary .card-body { padding: .55rem .7rem; }
    .audit-summary small, .audit-impact small, .card-header small { font-size: .8rem; }
    .audit-number { margin-top: .1rem; font-size: 1.12rem; line-height: 1.15; font-weight: 700; overflow-wrap: anywhere; }
    .audit-total-selisih { font-size: 1.28rem; line-height: 1.15; font-weight: 800; overflow-wrap: anywhere; }
    .audit-impact-warning { font-size: .82rem; line-height: 1.38; }
    .audit-impact-warning li + li { margin-top: .15rem; }
    .audit-table th, .audit-table td { padding: .4rem .48rem; font-size: .78rem; line-height: 1.24; }
    .audit-table .badge { font-size: .68rem; }
    .audit-table .btn-sm { font-size: .74rem; padding: .22rem .42rem; }
    .audit-cause { min-width: 180px; font-weight: 600; color: #842029; }
    .audit-mobile-item { padding: .78rem; border-bottom: 1px solid #dee2e6; font-size: .82rem; line-height: 1.35; }
    .audit-mobile-cause { color: #842029; }
    @media (max-width: 767.98px) {
        .audit-header .page-title { font-size: 1.15rem; }
        .audit-header small { font-size: .78rem; line-height: 1.3; display: block; }
        .audit-result-badge { width: 100%; text-align: center; font-size: .76rem; }
        .audit-filter .form-label, .audit-filter .form-control, .audit-filter .form-select, .audit-filter .btn { font-size: .78rem; }
        .audit-summary .card-body { padding: .52rem .6rem; }
        .audit-summary small { font-size: .72rem; }
        .audit-summary .audit-number { font-size: .96rem; }
        .audit-total-selisih { font-size: 1.08rem; }
        .audit-impact-warning { font-size: .78rem; }
        .audit-table th, .audit-table td { padding: .36rem .42rem; font-size: .74rem; }
        .audit-mobile-item { font-size: .82rem; }
        .audit-filter .btn { width: 100%; }
    }
</style>
@endpush
