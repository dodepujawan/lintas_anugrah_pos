@extends('layouts.app')

@section('title', 'Saldo Awal Hutang')

@section('content')
<div class="opening-payable-page">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 opening-payable-header">
        <div><h1 class="page-title mb-1">Saldo Awal Hutang</h1><p class="text-muted mb-0">Daftar faktur supplier sebelum penggunaan sistem.</p></div>
        <div class="d-flex gap-2 opening-payable-toolbar">
            <a href="{{ route('saldo-awal-hutang.export', request()->except(['page','print'])) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
            @include('laporan._print-format-dropdown',['pdfUrl'=>route('saldo-awal-hutang.pdf',request()->except(['page','print','print_format'])),'dotMatrixUrl'=>route('saldo-awal-hutang.pdf',request()->except(['page','print','print_format'])+['print_format'=>'dot_matrix']),'buttonClass'=>'btn-outline-secondary'])
            <a href="{{ route('saldo-awal-hutang.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
        </div>
    </div>

    <div class="opening-payable-print-header"><div class="fw-bold">SALDO AWAL HUTANG</div><div>Dicetak {{ now()->format('d/m/Y H:i') }}</div>@if(request('supplier'))<div>Supplier: {{ request('supplier') }}</div>@endif @if(request('search'))<div>Filter: {{ request('search') }}</div>@endif</div>

    <div class="card shadow-sm border-0 mb-3 opening-payable-filter"><div class="card-body"><form method="GET" action="{{ route('saldo-awal-hutang.index') }}" class="row g-2 align-items-end">
        <div class="col-md-6"><label class="form-label">Pencarian</label><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nomor faktur, supplier, atau keterangan"></div>
        <div class="col-md-3"><label class="form-label">Supplier</label><div class="input-group"><input id="openingSupplierFilter" name="supplier" value="{{ request('supplier') }}" class="form-control bg-white" placeholder="Semua supplier" readonly><button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#openingSupplierModal"><i class="bi bi-search"></i></button></div></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Cari</button><a href="{{ route('saldo-awal-hutang.index') }}" class="btn btn-outline-secondary">Reset</a></div>
    </form></div></div>

    <div class="row g-2 mb-3 opening-payable-summary">
        <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Jumlah Faktur</div><div class="fw-bold summary-value">{{ number_format($totals->count,0,',','.') }}</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Hutang Awal</div><div class="fw-bold summary-value">{{ number_format($totals->payable,2,',','.') }}</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Pembayaran</div><div class="fw-bold summary-value">{{ number_format($totals->paid,2,',','.') }}</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total Sisa</div><div class="fw-bold summary-value text-primary">{{ number_format($totals->balance,2,',','.') }}</div></div></div></div>
    </div>

    <div class="card shadow-sm border-0"><div class="card-body p-0"><div class="table-responsive opening-payable-scroll"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th class="date-col">Tanggal</th><th class="invoice-col">No. Faktur</th><th>Supplier</th><th class="text-end">Hutang Awal</th><th class="text-end">Pembayaran</th><th class="text-end">Sisa</th><th>Status</th><th></th></tr></thead>
        <tbody>@forelse($hutangs as $hutang)<tr>
            <td class="date-cell"><div>{{ $hutang->TGLFAKTUR?->format('d/m/Y') }}</div><div class="due-date"><span>JTP</span> {{ $hutang->TGLJT?->format('d/m/Y') ?? '-' }}</div></td>
            <td class="invoice-cell fw-semibold">{{ $hutang->NOFAKTUR }}</td><td>{{ $hutang->supplier_display }}</td>
            <td class="text-end amount-cell">{{ number_format($hutang->HUTANG,0,',','.') }}</td><td class="text-end amount-cell">{{ number_format($hutang->BAYAR,0,',','.') }}</td><td class="text-end amount-cell fw-semibold">{{ number_format($hutang->SALDO,0,',','.') }}</td>
            <td><span class="badge {{ $hutang->SALDO<=0?'bg-success':'bg-warning text-dark' }}">{{ $hutang->SALDO<=0?'Lunas':'Belum Lunas' }}</span></td>
            <td class="actions"><div class="d-inline-flex gap-1"><a href="{{ route('hutang.show',$hutang) }}" class="btn btn-sm btn-outline-primary">Detail</a>@if((float)$hutang->BAYAR<=0&&(float)$hutang->RETUR<=0&&(float)$hutang->DISCOUNT<=0)<a href="{{ route('saldo-awal-hutang.edit',$hutang) }}" class="btn btn-sm btn-warning">Edit</a><form method="POST" action="{{ route('saldo-awal-hutang.destroy',$hutang) }}" onsubmit="return confirm('Hapus saldo awal hutang {{ addslashes($hutang->NOFAKTUR) }}?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Hapus</button></form>@endif</div></td>
        </tr>@empty<tr><td colspan="8" class="text-center py-4">Belum ada saldo awal hutang.</td></tr>@endforelse</tbody>
        <tfoot class="table-light"><tr class="fw-bold"><td colspan="3">Total Seluruh Data ({{ number_format($totals->count,0,',','.') }} faktur)</td><td class="text-end">{{ number_format($totals->payable,2,',','.') }}</td><td class="text-end">{{ number_format($totals->paid,2,',','.') }}</td><td class="text-end">{{ number_format($totals->balance,2,',','.') }}</td><td colspan="2"></td></tr></tfoot>
    </table></div></div></div>
    @if(!$printMode && $hutangs->hasPages())<div class="mt-2 payable-pagination">{{ $hutangs->links() }}</div>@endif
</div>

<div class="modal fade" id="openingSupplierModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Cari Supplier</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input id="openingSupplierSearch" type="search" class="form-control mb-3" placeholder="Kode atau nama supplier"><div id="openingSupplierLoading" class="text-center py-4 d-none">Memuat...</div><div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>Kode</th><th>Nama</th><th></th></tr></thead><tbody id="openingSupplierResults"></tbody></table></div></div></div></div></div>
@endsection

@push('styles')
<style>
.opening-payable-page{font-size:.82rem}.opening-payable-page .page-title{font-size:1.2rem}.opening-payable-page .form-label{font-size:.72rem;margin-bottom:.25rem}.opening-payable-page .btn,.opening-payable-page .form-control{font-size:.74rem}.opening-payable-summary .card-body{padding:.55rem .65rem}.summary-value{font-size:.82rem;white-space:nowrap}.opening-payable-scroll{max-height:58vh;min-height:240px;overflow:auto;scrollbar-gutter:stable}.opening-payable-scroll thead th{position:sticky;top:0;z-index:3;background:var(--bs-light)}.opening-payable-page th,.opening-payable-page td{padding:.32rem .4rem;font-size:.72rem;vertical-align:middle}.date-col,.date-cell{width:92px;min-width:92px;white-space:nowrap}.invoice-col,.invoice-cell{width:112px;min-width:112px;padding-left:.65rem!important;padding-right:.65rem!important;white-space:nowrap}.due-date{color:#dc3545;font-size:.62rem;margin-top:.18rem}.due-date span{font-weight:700}.amount-cell{white-space:nowrap;font-variant-numeric:tabular-nums}.actions{width:1%;white-space:nowrap}.actions .btn{padding:.18rem .35rem;font-size:.66rem}.opening-payable-print-header{display:none}
@media(max-width:767.98px){.opening-payable-header{align-items:flex-start!important}.opening-payable-toolbar{flex-wrap:wrap;justify-content:flex-end}.opening-payable-page table{min-width:850px}.opening-payable-scroll{max-height:55vh}}
@media print{@page{size:A4 landscape;margin:9mm}.opening-payable-header,.opening-payable-filter,.opening-payable-summary,.payable-pagination,.actions{display:none!important}.opening-payable-print-header{display:block;text-align:center;margin-bottom:7px;font-size:8pt}.opening-payable-print-header .fw-bold{font-size:13pt}.opening-payable-page .card{border:0!important;box-shadow:none!important}.opening-payable-scroll{max-height:none!important;min-height:0!important;overflow:visible!important}.opening-payable-scroll thead th{position:static}.opening-payable-page table{min-width:0!important;width:100%!important}.opening-payable-page th,.opening-payable-page td{padding:2px 3px!important;font-size:7pt!important;color:#000!important}.opening-payable-page th:last-child,.opening-payable-page td:last-child{display:none!important}.opening-payable-page .badge{padding:0;color:#000!important;background:transparent!important}}
</style>
@endpush

@push('scripts')
<script>document.addEventListener('DOMContentLoaded',function(){const modal=document.getElementById('openingSupplierModal'),input=document.getElementById('openingSupplierSearch'),target=document.getElementById('openingSupplierFilter'),results=document.getElementById('openingSupplierResults'),loading=document.getElementById('openingSupplierLoading');if(!modal)return;let timer;const esc=v=>{const e=document.createElement('div');e.textContent=v||'';return e.innerHTML};function load(q=''){loading.classList.remove('d-none');fetch("{{ route('hutang.search-supplier') }}?modal=1&q="+encodeURIComponent(q),{headers:{Accept:'application/json'}}).then(r=>r.json()).then(rows=>{results.innerHTML=rows.map(row=>`<tr><td class="fw-semibold">${esc(row.supplier||row.value)}</td><td>${esc(row.nama||row.label||'-')}</td><td class="text-end"><button type="button" class="btn btn-sm btn-primary choose-supplier" data-code="${esc(row.supplier||row.value)}">Pilih</button></td></tr>`).join('')}).finally(()=>loading.classList.add('d-none'))}modal.addEventListener('shown.bs.modal',()=>{input.value='';input.focus();load()});input.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>load(input.value.trim()),250)});results.addEventListener('click',e=>{const b=e.target.closest('.choose-supplier');if(!b)return;target.value=b.dataset.code;bootstrap.Modal.getInstance(modal)?.hide()})});</script>
@endpush

@if($printMode)@push('scripts')<script>window.addEventListener('load',()=>setTimeout(()=>window.print(),100));</script>@endpush @endif
