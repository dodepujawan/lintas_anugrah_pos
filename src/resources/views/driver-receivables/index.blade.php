@extends('layouts.app')
@section('title','Piutang Sopir')

@push('styles')
<style>
    .driver-receivable-page { display:flex; flex-direction:column; gap:.55rem; min-height:0; font-family:Arial,sans-serif; font-size:.92rem; }
    .driver-receivable-toolbar { justify-content:flex-end; }
    .driver-receivable-page .text-muted.small,
    .driver-receivable-page .form-control,
    .driver-receivable-page .btn { font-size:.85rem; }
    .driver-receivable-page .card-body { padding:.52rem; }
    .driver-receivable-scroll { max-height:calc(100vh - 285px); min-height:240px; overflow:auto; scrollbar-gutter:stable; }
    .driver-receivable-scroll thead th { position:sticky; top:0; z-index:2; background:#f8fafc; box-shadow:inset 0 -1px #dee2e6; white-space:nowrap; }
    .driver-receivable-scroll th,
    .driver-receivable-scroll td { padding:.35rem .45rem; font-size:.85rem; vertical-align:middle; }
    .driver-receivable-scroll td.text-end,
    .driver-receivable-scroll th.text-end { font-variant-numeric:tabular-nums; }
    .driver-receivable-page .badge { min-width:42px; font-size:.74rem; padding:.2rem .35rem; }
    .driver-receivable-page .btn-sm { padding:.22rem .4rem; font-size:.74rem; line-height:1.15; }
    .driver-receivable-page table { min-width:930px; }
    .driver-receivable-actions .action-group { display:inline-flex; gap:.2rem; align-items:center; white-space:nowrap; }
    .driver-receivable-delete-btn { opacity:.72; }
    .driver-receivable-delete-btn:hover,
    .driver-receivable-delete-btn:focus { opacity:1; }
    @media (max-width:767.98px) {
        .driver-receivable-scroll { max-height:calc(100vh - 310px); min-height:280px; }
        .driver-receivable-actions { white-space:nowrap; }
        .driver-receivable-page table { min-width:980px; }
    }
</style>
@endpush

@section('content')
<div class="driver-receivable-page">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div class="flex-grow-1"></div>
        <div class="d-flex gap-2 align-self-sm-center driver-receivable-toolbar"><a href="{{ route('panduan.piutang_sopir') }}" class="btn btn-outline-info"><i class="bi bi-book me-1"></i>Panduan</a><a href="{{ route('driver-receivables.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Input Piutang</a></div>
    </div>

    <form class="card border-0 shadow-sm mb-0" method="GET">
        <div class="card-body p-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-6"><label class="form-label">Pencarian</label><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Nomor, referensi, kendaraan, atau keterangan"></div>
                <div class="col-md-3"><label class="form-label">Sopir</label><div class="input-group"><input id="driverFilterLabel" class="form-control bg-white" value="{{ ($selectedDriver=$drivers->firstWhere('id',(int)request('driver_id'))) ? $selectedDriver->code.' - '.$selectedDriver->name : '' }}" placeholder="Semua sopir" readonly><input id="driverFilter" type="hidden" name="driver_id" value="{{ request('driver_id') }}"><button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#driverFilterModal"><i class="bi bi-search"></i></button><button type="button" class="btn btn-outline-secondary" id="clearDriverFilter" title="Reset sopir"><i class="bi bi-x-lg"></i></button></div></div>
                <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Cari</button><a href="{{ route('driver-receivables.index') }}" class="btn btn-outline-secondary">Reset</a></div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm mb-0">
        <div class="table-responsive driver-receivable-scroll">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>Nomor</th><th>Sopir</th><th>Jenis</th><th class="text-end">Nilai</th><th class="text-end">Dibayar</th><th class="text-end">Saldo</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="text-nowrap">{{ $row->date->format('d/m/Y') }}</td><td class="fw-semibold text-nowrap">{{ $row->number }}</td><td class="text-nowrap">{{ $row->driver->code }} - {{ $row->driver->name }}</td><td>{{ str_replace('_',' ',$row->type) }}</td>
                        <td class="text-end text-nowrap">{{ number_format($row->amount,2,',','.') }}</td><td class="text-end text-nowrap">{{ number_format($row->paid,2,',','.') }}</td><td class="text-end fw-semibold text-nowrap">{{ number_format($row->balance,2,',','.') }}</td>
                        <td><span class="badge text-bg-{{ $row->status==='LUNAS'?'success':'warning' }}">{{ $row->status }}</span></td>
                        <td class="driver-receivable-actions"><div class="action-group"><a href="{{ route('driver-receivables.show',$row) }}" class="btn btn-outline-primary btn-sm">Detail</a>@if((float)$row->paid<=0)<a href="{{ route('driver-receivables.edit',$row) }}" class="btn btn-warning btn-sm">Edit</a><form class="d-inline" method="POST" action="{{ route('driver-receivables.destroy',$row) }}" onsubmit="return confirm('Hapus piutang {{ $row->number }} beserta jurnal?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm driver-receivable-delete-btn" title="Hapus piutang dan jurnal" aria-label="Hapus piutang {{ $row->number }}"><i class="bi bi-trash"></i></button></form>@else<span class="text-muted small">Sudah dibayar</span>@endif</div></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data piutang sopir.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($rows->hasPages())<div class="pt-1">{{ $rows->links() }}</div>@endif
</div>

<div class="modal fade" id="driverFilterModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Cari Sopir</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input id="driverModalSearch" class="form-control mb-3" placeholder="Kode atau nama sopir"><div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>Kode</th><th>Nama Sopir</th><th></th></tr></thead><tbody id="driverModalRows">@foreach($drivers as $driver)<tr data-search="{{ strtolower($driver->code.' '.$driver->name) }}"><td class="fw-semibold">{{ $driver->code }}</td><td>{{ $driver->name }}</td><td class="text-end"><button type="button" class="btn btn-sm btn-primary choose-driver" data-id="{{ $driver->id }}" data-label="{{ $driver->code }} - {{ $driver->name }}">Pilih</button></td></tr>@endforeach</tbody></table></div></div></div></div></div>
@endsection

@push('scripts')
<script>document.addEventListener('DOMContentLoaded',function(){const modal=document.getElementById('driverFilterModal'),search=document.getElementById('driverModalSearch'),rows=document.getElementById('driverModalRows'),target=document.getElementById('driverFilter'),label=document.getElementById('driverFilterLabel'),clear=document.getElementById('clearDriverFilter');if(!modal)return;modal.addEventListener('shown.bs.modal',()=>{search.value='';search.focus();rows.querySelectorAll('tr').forEach(r=>r.classList.remove('d-none'))});search.addEventListener('input',()=>{const q=search.value.toLowerCase();rows.querySelectorAll('tr').forEach(r=>r.classList.toggle('d-none',!r.dataset.search.includes(q)))});rows.addEventListener('click',e=>{const b=e.target.closest('.choose-driver');if(!b)return;target.value=b.dataset.id;label.value=b.dataset.label;bootstrap.Modal.getInstance(modal)?.hide()});clear?.addEventListener('click',()=>{target.value='';label.value='';label.focus()})});</script>
@endpush
