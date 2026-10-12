@extends('layouts.app')

@section('title', 'Daftar Akun')

@section('content')
@push('styles')
<style>
    .coa-mobile-actions { display:none; }
    .coa-action-cell { white-space:nowrap; width:125px; }
    @media(max-width:767.98px){
        .coa-page-header{margin-bottom:.7rem!important}.coa-page-header h2{font-size:1.15rem}.coa-page-header p{display:none}
        .coa-toolbar{display:grid!important;grid-template-columns:1fr 1fr;width:100%;gap:.4rem!important}.coa-toolbar form{grid-column:1/-1;width:100%;display:grid!important;grid-template-columns:minmax(0,1fr) auto auto}.coa-toolbar .btn{margin:0!important;font-size:.72rem;padding:.4rem .45rem;white-space:normal}.coa-toolbar>a:last-child{grid-column:1/-1}
        .coa-table-scroll{max-height:calc(100vh - 260px)!important;overflow:auto!important}.coa-table{table-layout:fixed;min-width:0!important;width:100%!important}.coa-table th,.coa-table td{padding:.45rem .35rem;font-size:.76rem}.coa-table th:nth-child(1){width:27%}.coa-table th:nth-child(2){width:auto}.coa-table th:nth-child(7){width:42px}.coa-table th:nth-child(3),.coa-table td:nth-child(3),.coa-table th:nth-child(4),.coa-table td:nth-child(4),.coa-table th:nth-child(5),.coa-table td:nth-child(5),.coa-table th:nth-child(6),.coa-table td:nth-child(6){display:none}
        .tree-cell{padding-left:.15rem!important;display:flex;align-items:flex-start;min-width:0}.tree-cell>span:last-child{overflow-wrap:anywhere;line-height:1.25}.tree-cell .bi-folder-fill,.tree-cell .bi-file-earmark-text-fill{display:none}.tree-toggle-placeholder{width:14px;flex:0 0 14px}.tree-toggle{margin-right:.2rem!important;flex:0 0 18px}
        .coa-action-cell{width:42px!important;padding-left:.1rem!important;padding-right:.1rem!important;text-align:center}.coa-desktop-actions{display:none}.coa-mobile-actions{display:inline-block}.coa-mobile-actions .dropdown-toggle{width:34px;height:34px;padding:0}.coa-mobile-actions .dropdown-toggle::after{display:none}.coa-mobile-actions .dropdown-menu{min-width:145px;font-size:.8rem}.coa-mobile-actions .dropdown-item{padding:.5rem .7rem}.coa-mobile-actions form{margin:0}
    }
</style>
@endpush
@php
$focusedCoaId = request()->query('focus');
$currentPage = request()->query('page', 1);
$currentQuery = request()->query('q', '');
@endphp
<div class="row mb-4 g-3 align-items-end coa-page-header">
    <div class="col-12 col-lg-6">
        <h2 class="mb-1"><i class="bi bi-book"></i> Chart of Account</h2>
        <p class="text-muted mb-0">Cari akun, lalu gunakan paginasi untuk melihat data lebih rapi.</p>
    </div>
    <div class="col-12 col-lg-6 d-flex justify-content-lg-end gap-2 flex-wrap coa-toolbar">
        <form method="GET" action="{{ route('coa.index') }}" class="d-flex gap-2 flex-grow-1 flex-lg-grow-0" role="search">
            <input type="search" name="q" value="{{ old('q', $query ?? '') }}" class="form-control" placeholder="Cari kode, nama, level, tipe, kategori..." aria-label="Cari akun">
            <button class="btn btn-outline-secondary" type="submit">Cari</button>
            @if($query)
            <a href="{{ route('coa.index') }}" class="btn btn-outline-danger">Reset</a>
            @endif
        </form>
        <button type="button" class="btn btn-outline-primary" id="expandAllButton">
            <i class="bi bi-arrows-angle-expand"></i> Expand All
        </button>
        <button type="button" class="btn btn-outline-secondary" id="collapseAllButton">
            <i class="bi bi-arrows-angle-contract"></i> Collapse All
        </button>
        <a href="{{ route('coa.export') }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
        <a href="{{ route('coa.create', ['page' => $currentPage, 'q' => $currentQuery]) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Akun
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="border rounded-3 overflow-hidden">
            <div class="table-responsive coa-table-scroll" style="max-height: 70vh; overflow-y: auto;">
                <table class="table table-striped table-hover mb-0 align-middle coa-table">
                    <thead class="table-secondary">
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Tipe</th>
                            <th>Kategori</th>
                            <th class="text-end">Saldo Normal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coas as $coa)
                        <tr class="tree-row {{ (string) $focusedCoaId === (string) $coa->id ? 'table-warning' : '' }}" id="coa-row-{{ $coa->id }}" data-id="{{ $coa->id }}" data-parent-id="{{ $coa->parent_id ?: '' }}" data-depth="{{ $coa->depth }}" style="{{ (string) $focusedCoaId === (string) $coa->id ? 'background-color: #fff3cd;' : '' }}">
                            <td>{{ $coa->kode_akun }}</td>
                            <td>
                                <div class="tree-cell" style="padding-left: calc({{ $coa->depth }} * 1.5rem);">
                                    @if($coa->children->isNotEmpty())
                                    <button type="button" class="btn btn-sm btn-link tree-toggle p-0 me-2" data-id="{{ $coa->id }}" aria-label="Toggle children">
                                        <i class="bi bi-chevron-right tree-toggle-icon"></i>
                                    </button>
                                    @else
                                    <span class="tree-toggle-placeholder me-2"></span>
                                    @endif
                                    <i class="bi {{ $coa->children->isNotEmpty() ? 'bi-folder-fill text-warning' : 'bi-file-earmark-text-fill text-secondary' }} me-2"></i>
                                    <span>{{ $coa->nama_akun }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $coa->tipe == 'Aset' ? 'primary' : ($coa->tipe == 'Kewajiban' ? 'danger' : ($coa->tipe == 'Ekuitas' ? 'success' : ($coa->tipe == 'Pendapatan' ? 'info' : 'warning'))) }}">
                                    {{ $coa->tipe }}
                                </span>
                            </td>
                            <td>{{ $coa->kategori ?? '-' }}</td>
                            <td class="text-end">{{ $coa->saldo_normal_label }}</td>
                            <td>
                                @if($coa->is_active)
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="coa-action-cell">
                                <div class="coa-desktop-actions">
                                <a href="{{ route('coa.show', $coa->id) }}" class="btn btn-sm btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('coa.edit', ['coa' => $coa->id, 'page' => $currentPage, 'q' => $currentQuery]) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('coa.destroy', $coa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                </div>
                                <div class="dropdown coa-mobile-actions">
                                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" aria-label="Aksi akun"><i class="bi bi-three-dots-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li><a class="dropdown-item" href="{{ route('coa.show',$coa->id) }}"><i class="bi bi-eye me-2 text-info"></i>Detail</a></li>
                                        <li><a class="dropdown-item" href="{{ route('coa.edit',['coa'=>$coa->id,'page'=>$currentPage,'q'=>$currentQuery]) }}"><i class="bi bi-pencil me-2 text-warning"></i>Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><form action="{{ route('coa.destroy',$coa->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">@csrf @method('DELETE')<button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Hapus</button></form></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data akun</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
    <div class="text-muted small">Menampilkan {{ $coas->firstItem() ?? 0 }}-{{ $coas->lastItem() ?? 0 }} dari {{ $coas->total() }} akun</div>
    <div>{{ $coas->links() }}</div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.tree-toggle');
        const expandAllButton = document.getElementById('expandAllButton');
        const collapseAllButton = document.getElementById('collapseAllButton');

        function setVisibility(itemId, visible) {
            document.querySelectorAll(`.tree-row[data-parent-id='${itemId}']`).forEach(function(row) {
                row.style.display = visible ? '' : 'none';
                const icon = row.querySelector('.tree-toggle-icon');
                if (!visible && icon) {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                }
                const childId = row.getAttribute('data-id');
                if (!visible) {
                    setVisibility(childId, false);
                }
            });
        }

        function setAllExpanded(expanded) {
            document.querySelectorAll('.tree-row').forEach(function(row) {
                const parentId = row.getAttribute('data-parent-id');
                const icon = row.querySelector('.tree-toggle-icon');
                const hasChildren = !!icon;

                if (!parentId) {
                    row.style.display = '';
                } else {
                    row.style.display = expanded ? '' : 'none';
                }

                if (hasChildren) {
                    if (expanded) {
                        icon.classList.remove('bi-chevron-right');
                        icon.classList.add('bi-chevron-down');
                    } else {
                        icon.classList.remove('bi-chevron-down');
                        icon.classList.add('bi-chevron-right');
                    }
                }
            });
        }

        toggleButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const icon = this.querySelector('.tree-toggle-icon');
                const isExpanded = icon.classList.contains('bi-chevron-down');

                if (isExpanded) {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                    setVisibility(id, false);
                } else {
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                    setVisibility(id, true);
                }
            });
        });

        expandAllButton.addEventListener('click', function() {
            setAllExpanded(true);
        });

        collapseAllButton.addEventListener('click', function() {
            setAllExpanded(false);
        });

        setAllExpanded(true);
        const focusedCoaId = '{{ $focusedCoaId ?? '
        ' }}';
        if (focusedCoaId) {
            const focusedRow = document.querySelector(`.tree-row[data-id='${focusedCoaId}']`);
            if (focusedRow) {
                focusedRow.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                focusedRow.classList.add('table-warning');
                focusedRow.style.backgroundColor = '#fff3cd';
                setTimeout(function() {
                    focusedRow.classList.remove('table-warning');
                    focusedRow.style.backgroundColor = '';
                }, 4000);
            }
        }
    });
</script>
@endpush
@endsection
