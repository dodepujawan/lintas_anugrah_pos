@extends('layouts.app')
@php($edit = isset($supplier))
@section('title', $edit ? 'Edit Supplier' : 'Tambah Supplier')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        @if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3"><h1 class="h5 mb-1">{{ $edit ? 'Edit' : 'Tambah' }} Master Supplier</h1><div class="text-muted small">Data tersimpan langsung ke tabel msupplier.</div></div>
            <div class="card-body p-3 p-md-4">
                <form id="supplier-form" method="POST" action="{{ $edit ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
                    @csrf @if($edit) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-12 col-md-4"><label class="form-label">Kode Supplier <span class="text-danger">*</span></label><input name="SUPPLIER" value="{{ old('SUPPLIER', $edit ? $supplier->SUPPLIER : '') }}" class="form-control" required autofocus></div>
                        <div class="col-12 col-md-8"><label class="form-label">Nama Supplier <span class="text-danger">*</span></label><input name="NAMA" value="{{ old('NAMA', $edit ? $supplier->NAMA : '') }}" class="form-control" required></div>
                        <div class="col-12 col-md-6"><label class="form-label">Alamat 1</label><textarea name="ALAMAT1" rows="2" class="form-control">{{ old('ALAMAT1', $edit ? $supplier->ALAMAT1 : '') }}</textarea></div>
                        <div class="col-12 col-md-6"><label class="form-label">Alamat 2</label><textarea name="ALAMAT2" rows="2" class="form-control">{{ old('ALAMAT2', $edit ? $supplier->ALAMAT2 : '') }}</textarea></div>
                        <div class="col-12 col-md-4"><label class="form-label">Kota</label><input name="KOTA" value="{{ old('KOTA', $edit ? $supplier->KOTA : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Telepon</label><input name="TELEPON" value="{{ old('TELEPON', $edit ? $supplier->TELEPON : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Fax</label><input name="FAX" value="{{ old('FAX', $edit ? $supplier->FAX : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Email</label><input type="email" name="EMAIL" value="{{ old('EMAIL', $edit ? $supplier->EMAIL : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Kontak</label><input name="KONTAK" value="{{ old('KONTAK', $edit ? $supplier->KONTAK : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">TOP Hari</label><input type="number" step="1" min="0" name="FTOP" value="{{ old('FTOP', $edit ? $supplier->FTOP : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Bank</label><input name="BANK" value="{{ old('BANK', $edit ? $supplier->BANK : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">No. Rekening</label><input name="NOREK" value="{{ old('NOREK', $edit ? $supplier->NOREK : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Atas Nama</label><input name="ATASNAMA" value="{{ old('ATASNAMA', $edit ? $supplier->ATASNAMA : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Disc Reg</label><input type="number" step="0.01" min="0" name="DISC_REG" value="{{ old('DISC_REG', $edit ? $supplier->DISC_REG : '') }}" class="form-control"></div>
                    </div>
                    <div class="d-grid d-sm-flex gap-2 mt-4 pt-3 border-top"><button class="btn btn-primary px-4">Simpan</button><a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary px-4">Batal</a></div>
                </form>
            </div>
        </div>
    </div>
</div>
<x-enter-as-tab form-id="supplier-form" />
@endsection
