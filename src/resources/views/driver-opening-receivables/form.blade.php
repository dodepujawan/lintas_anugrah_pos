@extends('layouts.app')
@php($edit=isset($row))
@section('title',$edit?'Edit Saldo Awal Piutang Sopir':'Input Saldo Awal Piutang Sopir')
@section('content')
<div class="row justify-content-center"><div class="col-12 col-xl-10">
@if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card border-0 shadow-sm"><div class="card-header bg-white py-3 d-flex justify-content-between align-items-start gap-2"><div><h1 class="h5 mb-1">{{ $edit?'Edit':'Input' }} Saldo Awal Piutang Sopir</h1><div class="small text-muted">Khusus saldo sebelum aplikasi mulai dipakai. Tidak membuat jurnal dan bukan untuk input piutang berjalan.</div></div><a href="{{ route('panduan.piutang_sopir') }}" class="btn btn-sm btn-outline-info text-nowrap"><i class="bi bi-book me-1"></i>Panduan</a></div><div class="card-body p-3 p-md-4">
<div class="alert alert-warning d-flex gap-2 align-items-start" role="alert"><i class="bi bi-exclamation-triangle-fill mt-1"></i><div><strong>Perhatian:</strong> menu ini hanya untuk memasukkan saldo piutang sopir awal saat pindah ke sistem. Transaksi baru/berjalan harus diinput dari menu <strong>Piutang Sopir</strong> karena saldo awal tidak membuat jurnal.</div></div>
<form id="driver-opening-form" method="POST" action="{{ $edit?route('driver-opening-receivables.update',$row):route('driver-opening-receivables.store') }}">@csrf @if($edit)@method('PUT')@endif
<div class="row g-3">
<div class="col-12 col-md-4"><label class="form-label">Nomor Saldo Awal <span class="text-danger">*</span></label><input name="number" value="{{ old('number',$edit?$row->number:'SAPS-'.now()->format('YmdHis')) }}" class="form-control bg-light" required readonly autofocus><div class="form-text">Nomor dibuat otomatis dan tidak dapat diedit.</div></div>
<div class="col-12 col-sm-6 col-md-4"><label class="form-label">Tanggal Saldo <span class="text-danger">*</span></label><input type="date" name="date" value="{{ old('date',$edit?$row->date->toDateString():now()->toDateString()) }}" class="form-control" required></div>
<div class="col-12 col-sm-6 col-md-4"><label class="form-label">Jatuh Tempo</label><input type="date" name="due_date" value="{{ old('due_date',$edit?$row->due_date?->toDateString():'') }}" class="form-control"></div>
<div class="col-12 col-md-8"><label class="form-label">Sopir <span class="text-danger">*</span></label><select name="driver_id" class="form-select" required><option value="">Pilih sopir</option>@foreach($drivers as $driver)<option value="{{ $driver->id }}" @selected(old('driver_id',$edit?$row->driver_id:'')==$driver->id)>{{ $driver->code }} - {{ $driver->name }}</option>@endforeach</select></div>
<div class="col-12 col-md-4"><label class="form-label">Nomor Kendaraan</label><input name="vehicle_number" value="{{ old('vehicle_number',$edit?$row->vehicle_number:'') }}" class="form-control"></div>
<div class="col-12 col-md-6"><label class="form-label">Referensi</label><input name="reference" value="{{ old('reference',$edit?$row->reference:'') }}" class="form-control"></div>
<div class="col-12 col-md-6"><label class="form-label">Saldo Awal <span class="text-danger">*</span></label><input type="number" inputmode="decimal" name="amount" step=".01" min=".01" value="{{ old('amount',$edit?$row->amount:'') }}" class="form-control" required></div>
<div class="col-12"><label class="form-label">Keterangan <span class="text-danger">*</span></label><textarea name="description" rows="3" class="form-control" placeholder="Contoh: Sisa piutang sopir dari sistem lama per tanggal cutover" required>{{ old('description',$edit?$row->description:'Saldo awal piutang sopir') }}</textarea><div class="form-text">Catatan ini hanya untuk asal saldo awal. Simpan saldo awal piutang sopir tidak membuat jurnal baru.</div></div>
</div><div class="d-grid d-sm-flex gap-2 mt-4 pt-3 border-top"><button class="btn btn-primary px-4">Simpan Saldo Awal</button><a href="{{ route('driver-opening-receivables.index') }}" class="btn btn-outline-secondary px-4">Batal</a></div></form>
</div></div></div></div>
<x-enter-as-tab form-id="driver-opening-form" />
@endsection
