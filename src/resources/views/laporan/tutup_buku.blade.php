@extends('layouts.app')

@section('title', 'Tutup Buku')

@section('content')
@php
$canPostTutupBuku = auth()->user()?->hasMenuPermission('laporan.tutup_buku', 'post') ?? false;
$canDeleteTutupBuku = auth()->user()?->hasMenuPermission('laporan.tutup_buku', 'delete') ?? false;
$canExportTutupBuku = auth()->user()?->hasMenuPermission('laporan.tutup_buku', 'export') ?? false;
$canGenerateClosing = $canPostTutupBuku && $isFullYearPeriod && $hasPeriodEnded && !$isYearClosed && !$hasClosingJournal && !$hasUnpostedJournals;
$canMarkClosed = $canPostTutupBuku && $isFullYearPeriod && !$isYearClosed && !$hasUnpostedJournals;
$canArchive = $canPostTutupBuku && $isFullYearPeriod && $isYearClosed;
$canCloseYear = $canPostTutupBuku && $isFullYearPeriod && $hasPeriodEnded && !$isYearClosed && !$hasUnpostedJournals;
$canFinalizeClosing = $canCloseYear && $hasClosingJournal;
@endphp

<div class="tutup-buku-page">
<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h2 class="page-title"><i class="bi bi-box-seam me-2"></i>Tutup Buku</h2>
            <p class="text-muted mb-1">Ringkasan laba/rugi akhir periode untuk penutupan buku.</p>
            <small class="text-muted">Periode {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
            <small class="ms-2 badge bg-{{ ($periodLockStatus ?? 'TERBUKA') === 'TERKUNCI' ? 'danger' : 'success' }}">{{ $periodLockStatus ?? 'TERBUKA' }}</small>
        </div>
        <div class="card shadow-sm border-0" style="max-width: 720px;">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('account-mappings.index') }}" class="btn btn-outline-warning btn-sm"
                       onclick="return confirm('Buka halaman Account Mapping?');">
                        <i class="bi bi-hash me-1"></i> Account Mapping
                    </a>
                    @if($canExportTutupBuku)
                    <a href="{{ route('laporan.tutup_buku.excel', request()->all()) }}" class="btn btn-outline-success btn-sm"
                       onclick="return confirm('Unduh laporan tutup buku periode ini?');">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export
                    </a>
                    @endif
                    @include('laporan._print-format-dropdown', ['pdfUrl'=>route('laporan.stream_pdf',['report'=>'tutup-buku']+request()->except('print_format')),'dotMatrixUrl'=>route('laporan.stream_pdf',['report'=>'tutup-buku','print_format'=>'dot_matrix']+request()->except('print_format')),'buttonClass'=>'btn-outline-secondary','buttonSize'=>'btn-sm'])
                </div>

                <div class="small fw-semibold mb-2">Tahapan tutup tahun</div>
                <div class="d-flex flex-wrap align-items-center gap-2 small mb-3">
                    <span class="badge rounded-pill bg-success"><i class="bi bi-check-lg"></i> 1. Periksa data</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                    <span class="badge rounded-pill bg-{{ $hasClosingJournal ? 'success' : 'secondary' }}">2. Generate jurnal</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                    <span class="badge rounded-pill bg-{{ $isYearClosed ? 'success' : 'secondary' }}">3. Selesaikan</span>
                </div>

                @if($isYearClosed)
                    <div class="alert alert-success py-2 mb-3">
                        <i class="bi bi-check-circle-fill me-1"></i> Tahun {{ $year }} sudah selesai ditutup.
                    </div>
                @elseif(!$hasPeriodEnded)
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="bi bi-calendar-x me-1"></i><strong>Tahun {{ $year }} masih berjalan.</strong>
                        Generate jurnal penutup baru tersedia mulai 1 Januari {{ $year + 1 }}.
                    </div>
                @elseif($hasClosingJournal)
                    <div class="alert alert-info py-2 mb-3">
                        <strong>Jurnal penutup sudah dibuat.</strong> Periksa jurnal terlebih dahulu, lalu selesaikan tutup tahun jika sudah benar.
                    </div>
                @else
                    <p class="small text-muted mb-3">Langkah berikutnya: buat jurnal penutup untuk diperiksa sebelum tahun diselesaikan.</p>
                @endif

                <div class="d-flex flex-wrap align-items-end gap-2">
                    @if($canPostTutupBuku && !$hasClosingJournal && !$isYearClosed)
                    <form method="POST" action="{{ route('laporan.tutup_buku.generate_jurnal') }}">
                        @csrf
                        <input type="hidden" name="tanggal_awal" value="{{ $tanggalAwal }}">
                        <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                        <button type="submit" class="btn btn-primary" @disabled(!$canGenerateClosing)
                                onclick="return confirm('Generate dan posting jurnal penutup periode ini? Data jurnal akan langsung disimpan.');">
                            <i class="bi bi-journal-plus me-1"></i> Generate Jurnal Penutup
                        </button>
                    </form>
                    @endif

                    @if($hasClosingJournal)
                    <a href="{{ route('laporan.jurnal_umum', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir]) }}"
                       class="btn btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> Lihat Jurnal Penutup
                    </a>
                    @endif

                    @if($canPostTutupBuku && !$isYearClosed)
                    <form method="POST" action="{{ route('laporan.tutup_buku.close_year') }}" id="closeYearForm">
                        @csrf
                        <input type="hidden" name="tanggal_awal" value="{{ $tanggalAwal }}">
                        <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" id="confirmCloseYear" onchange="toggleCloseYearButton()" @disabled(!$canFinalizeClosing)>
                            <label class="form-check-label small" for="confirmCloseYear">Jurnal sudah saya periksa</label>
                        </div>
                        <button type="submit" class="btn btn-dark" id="closeYearButton" disabled
                                onclick="return confirm('Yakin selesaikan tutup tahun? Tahun akan ditandai tutup, jurnal diarsipkan, dan Neraca divalidasi.');">
                            <i class="bi bi-shield-check me-1"></i> Selesaikan Tutup Tahun
                        </button>
                    </form>
                    @endif
                </div>

                @if($canDeleteTutupBuku && ($hasClosingJournal || $isYearClosed))
                <div class="dropdown mt-3 pt-3 border-top">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear me-1"></i> Tindakan Lanjutan
                    </button>
                    <div class="dropdown-menu p-2 shadow">
                        @if($hasClosingJournal && !$isYearClosed)
                        <form method="POST" action="{{ route('laporan.tutup_buku.remove_closing_journal') }}">
                            @csrf
                            <input type="hidden" name="tanggal_awal" value="{{ $tanggalAwal }}">
                            <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Hapus jurnal penutup agar data dapat direvisi dan di-generate ulang?');">
                                <i class="bi bi-trash me-2"></i>Hapus Jurnal Penutup
                            </button>
                        </form>
                        @endif
                        @if($isYearClosed)
                        <form method="POST" action="{{ route('laporan.tutup_buku.reset_year_close') }}">
                            @csrf
                            <input type="hidden" name="tanggal_awal" value="{{ $tanggalAwal }}">
                            <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('PERINGATAN: Reset status tutup tahun {{ $year }}? Lakukan hanya jika benar-benar perlu revisi.');">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Tutup Tahun
                            </button>
                        </form>
                        @endif
                    </div>
                    <div class="form-text">Khusus pembatalan atau revisi oleh pengguna berwenang.</div>
                </div>
                @endif
            </div>
        </div>

        @if($canPostTutupBuku && !$isYearClosed)
        <script>
            function toggleCloseYearButton() {
                const checkbox = document.getElementById('confirmCloseYear');
                const button = document.getElementById('closeYearButton');
                const canFinalize = @json($canFinalizeClosing);
                if (button && checkbox) {
                    button.disabled = !canFinalize || !checkbox.checked;
                }
            }
        </script>
        @endif
    </div>
</div>

<div class="print-only-header">
    <div class="title-line">
        <span class="page-title">LAPORAN TUTUP BUKU</span>
    </div>
    <div class="period-line">
        <small class="text-muted">Periode: {{ date('d F Y', strtotime($tanggalAwal)) }} - {{ date('d F Y', strtotime($tanggalAkhir)) }}</small>
    </div>
    <div class="period-line">
        <small class="text-muted">Status: {{ $periodLockStatus ?? 'TERBUKA' }}</small>
    </div>
</div>

@if($isYearClosed)
<div class="alert alert-success d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <div>
        Tahun <strong>{{ $year }}</strong> sudah ditutup pada <strong>{{ $yearClosing?->closed_at?->format('d M Y H:i') }}</strong>.
    </div>
</div>
@else
<div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <div>
        @if(!$hasPeriodEnded)
            Tahun <strong>{{ $year }}</strong> masih berjalan. Halaman ini hanya menampilkan preview dan belum perlu ditutup sekarang.
        @else
            Tahun <strong>{{ $year }}</strong> belum ditutup. Anda dapat menjalankan proses tutup tahun setelah seluruh data diperiksa.
        @endif
    </div>
</div>
@endif

@unless($isFullYearPeriod)
<div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-calendar-x-fill me-2"></i>
    <div>
        Proses akhir tahun hanya boleh untuk periode penuh <strong>1 Januari sampai 31 Desember</strong> pada tahun yang sama. Periode saat ini hanya untuk preview laporan.
    </div>
</div>
@endunless

@if($hasUnpostedJournals)
<div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-journal-x me-2"></i>
    <div>
        Masih ada jurnal draft/belum posted di tahun {{ $year }}. Posting atau void dulu sebelum tutup buku.
    </div>
</div>
@endif

@if($hasClosingJournal && !$isYearClosed)
<div class="alert alert-info d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-info-circle-fill me-2"></i>
    <div>
        Jurnal penutup tahun {{ $year }} sudah ada. Gunakan tombol Selesaikan Tutup Tahun untuk menandai dan mengarsipkan jika semua sudah siap.
    </div>
</div>
@endif

<div class="card filter-card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="tanggal_awal" class="form-label">Tanggal Awal Periode Aktif</label>
                <input type="date" class="form-control" id="tanggal_awal" value="{{ $tanggalAwal }}" readonly>
            </div>
            <div class="col-md-4">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir Periode Aktif</label>
                <input type="date" class="form-control" id="tanggal_akhir" value="{{ $tanggalAkhir }}" readonly>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-text mb-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Periode ditentukan otomatis oleh sistem dan tidak dapat diubah dari halaman tutup buku.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">Pendapatan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive closing-table-scroll closing-result-scroll">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendapatanRows as $row)
                            <tr>
                                <td>{{ $row['coa']->kode_akun }}</td>
                                <td>{{ $row['coa']->nama_akun }}</td>
                                <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="closing-total-row table-success">
                    <strong>Total Pendapatan</strong>
                    <strong class="text-end text-nowrap">{{ number_format($totalPendapatan, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">Beban</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive closing-table-scroll closing-result-scroll">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bebanRows as $row)
                            <tr>
                                <td>{{ $row['coa']->kode_akun }}</td>
                                <td>{{ $row['coa']->nama_akun }}</td>
                                <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="closing-total-row table-danger">
                    <strong>Total Beban</strong>
                    <strong class="text-end text-nowrap">{{ number_format($totalBeban, 2) }}</strong>
                </div>
                @if(($taksiranPajakPenghasilan ?? 0) != 0)
                <div class="closing-total-row table-warning">
                    <strong>Taksiran Pajak Penghasilan</strong>
                    <strong class="text-end text-nowrap">{{ number_format($taksiranPajakPenghasilan, 2) }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 align-items-stretch mb-4 closing-summary-row">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">Account Mapping</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive closing-table-scroll">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mapping</th>
                                <th>Akun Dipetakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mappingAccounts as $key => $account)
                            <tr>
                                <td>{{ \App\Models\AccountMapping::mappingKeys()[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}</td>
                                <td>
                                    @if($account)
                                    {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                    @else
                                    <span class="text-muted">Belum ditetapkan</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3 closing-profit-card">
            <div class="card-body">
                <div class="text-muted small mb-1">Laba/Rugi Bersih</div>
                <div class="fs-4 fw-bold {{ $labaBersih >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($labaBersih, 2) }}</div>
            </div>
        </div>

        <div class="card closing-retained-card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Laba Ditahan</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between gap-3 mb-2"><span>Saldo Awal Laba Ditahan</span><strong class="text-nowrap">{{ number_format($saldoAwalLabaDitahan, 2) }}</strong></div>
                <div class="d-flex justify-content-between gap-3 mb-2"><span>Laba Bersih Periode</span><strong class="text-nowrap">{{ number_format($labaBersih, 2) }}</strong></div>
                <div class="d-flex justify-content-between gap-3 pt-2 border-top"><span>Proyeksi Saldo Akhir</span><strong class="text-nowrap {{ $saldoAkhirLabaDitahanProyeksi >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($saldoAkhirLabaDitahanProyeksi, 2) }}</strong></div>

                @unless($mappingAccounts['retained_earnings'])
                <div class="alert alert-warning mt-3 mb-0" role="alert">
                    Akun Laba Ditahan belum dipetakan pada Account Mapping.
                </div>
                @endunless
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@include('laporan._report-print')

@push('styles')
<style>
    @media screen {
        .tutup-buku-page { font-size: .86rem; }
        .tutup-buku-page .page-title { font-size: 1.28rem; margin-bottom: .3rem; }
        .tutup-buku-page .screen-header p,
        .tutup-buku-page .screen-header small { font-size: .72rem; }
        .tutup-buku-page .card-header { padding: .48rem .7rem; }
        .tutup-buku-page .card-title { font-size: .9rem; }
        .tutup-buku-page .card-body { padding: .78rem; }
        .tutup-buku-page .table th,
        .tutup-buku-page .table td {
            padding: .32rem .42rem;
            font-size: .76rem;
            line-height: 1.18;
            vertical-align: middle;
        }
        .tutup-buku-page .form-label,
        .tutup-buku-page .form-control,
        .tutup-buku-page .form-text { font-size: .75rem; }
        .tutup-buku-page .form-control { padding: .32rem .48rem; }
        .tutup-buku-page .btn { font-size: .75rem; padding: .32rem .55rem; }
        .tutup-buku-page .btn-sm { font-size: .7rem; padding: .25rem .45rem; }
        .tutup-buku-page .alert { font-size: .76rem; padding: .5rem .65rem; }
        .tutup-buku-page .badge { font-size: .66rem; }
        .tutup-buku-page .fs-4 { font-size: 1.05rem !important; }
        .tutup-buku-page .closing-table-scroll {
            height: 290px;
            overflow-y: auto;
            scrollbar-gutter: stable;
        }
        .tutup-buku-page .closing-table-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: var(--bs-light);
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .12);
        }
        .tutup-buku-page .closing-table-scroll tfoot th {
            position: sticky;
            bottom: 0;
            z-index: 2;
            box-shadow: inset 0 1px 0 rgba(0, 0, 0, .12);
        }
        .tutup-buku-page .closing-result-scroll { height: 255px; }
        .tutup-buku-page .closing-total-row {
            min-height: 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .38rem .48rem;
            font-size: .76rem;
            border-top: 1px solid rgba(0, 0, 0, .12);
        }
        .tutup-buku-page .closing-summary-row .closing-table-scroll { height: 260px; }
        .tutup-buku-page .closing-profit-card .card-body { padding: .72rem .85rem; }
        .tutup-buku-page .closing-retained-card .card-body { line-height: 1.25; }

        @media (max-width: 767.98px) {
            .tutup-buku-page .closing-table-scroll { height: 250px; }
            .tutup-buku-page .closing-result-scroll { height: 215px; }
            .tutup-buku-page .closing-summary-row .closing-table-scroll { height: 230px; }
            .tutup-buku-page .closing-retained-card .d-flex { align-items: flex-start; }
        }
    }

    @media print {
        .tutup-buku-page .closing-table-scroll {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }
        .tutup-buku-page .closing-table-scroll thead th,
        .tutup-buku-page .closing-table-scroll tfoot th { position: static !important; }
        .tutup-buku-page .closing-total-row { display: flex !important; }
    }
</style>
@endpush
