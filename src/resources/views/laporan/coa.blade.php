@extends('layouts.app')

@section('title', 'Daftar COA')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="page-title"><i class="bi bi-diagram-3-fill me-2"></i>Daftar COA</h2>
            <p class="text-muted">Export COA aktif dengan filter tipe dan level.</p>
        </div>
        <div>
            <a href="{{ route('laporan.coa.excel', request()->all()) }}" class="btn btn-success me-2">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            @include('laporan._print-format-dropdown', ['pdfUrl'=>route('laporan.stream_pdf',['report'=>'coa']+request()->except('print_format')),'dotMatrixUrl'=>route('laporan.stream_pdf',['report'=>'coa','print_format'=>'dot_matrix']+request()->except('print_format'))])
        </div>
    </div>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.coa') }}" class="row g-3">
            <div class="col-md-4">
                <label for="tipe" class="form-label">Tipe Akun</label>
                <select name="tipe" id="tipe" class="form-select">
                    <option value="">Semua Tipe</option>
                    @foreach($tipeList as $tipe)
                    <option value="{{ $tipe }}" {{ request('tipe') == $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="level" class="form-label">Level Akun</label>
                <select name="level" id="level" class="form-select">
                    <option value="">Semua Level</option>
                    @foreach($levelList as $level)
                    <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>{{ $level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light">
            <div>
                <strong>Struktur Tree COA</strong>
                <p class="mb-0 text-muted">Gunakan kontrol untuk menampilkan atau menyembunyikan level tree.</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary btn-sm" id="treeExpandAllButton">
                    <i class="bi bi-arrows-angle-expand"></i> Expand All
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="treeCollapseAllButton">
                    <i class="bi bi-arrows-angle-contract"></i> Collapse All
                </button>
            </div>
        </div>
        <div class="table-responsive" style="max-height: 72vh; overflow-y: auto;">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Level</th>
                        <th class="text-end">Saldo Normal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coas as $coa)
                    <tr class="tree-row" data-id="{{ $coa->id }}" data-parent-id="{{ $coa->parent_id ?: '' }}" data-depth="{{ $coa->depth }}">
                        <td>{{ $coa->kode_akun }}</td>
                        <td>
                            @php
                            $coaIndentClass = match ($coa->level) {
                            'Header' => 'ps-0',
                            'Sub Header' => 'ps-4',
                            'Kelompok Akun' => 'ps-5',
                            'Akun Posting' => 'ps-6',
                            default => 'ps-0',
                            };
                            @endphp
                            <div class="d-flex align-items-center {{ $coaIndentClass }}">
                                @if($coa->children()->exists())
                                <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="{{ $coa->id }}" aria-label="Toggle children">
                                    <i class="bi bi-chevron-down tree-toggle-icon"></i>
                                </button>
                                @else
                                <span class="tree-toggle-placeholder me-2"></span>
                                @endif
                                <i class="bi {{ $coa->children()->exists() ? 'bi-folder-fill text-warning' : 'bi-file-earmark-text-fill text-secondary' }} me-2"></i>
                                <span>{{ $coa->nama_akun }}</span>
                            </div>
                        </td>
                        <td>{{ $coa->tipe }}</td>
                        <td>{{ $coa->kategori ?? '-' }}</td>
                        <td>{{ $coa->level }}</td>
                        <td class="text-end">{{ $coa->saldo_normal_label }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada akun COA yang sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@include('laporan._tree-scripts')
@include('laporan._report-print')
