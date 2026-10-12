@extends('layouts.app')
@php($edit = isset($customer))
@section('title', $edit ? 'Edit Customer' : 'Tambah Customer')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        @if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3"><h1 class="h5 mb-1">{{ $edit ? 'Edit' : 'Tambah' }} Master Customer</h1><div class="text-muted small">Data tersimpan langsung ke tabel mcustomer.</div></div>
            <div class="card-body p-3 p-md-4">
                <form id="customer-form" method="POST" action="{{ $edit ? route('customers.update', $customer) : route('customers.store') }}">
                    @csrf @if($edit) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-12 col-md-4"><label class="form-label">Kode Customer <span class="text-danger">*</span></label><input name="CUSTOMER" value="{{ old('CUSTOMER', $edit ? $customer->CUSTOMER : '') }}" class="form-control" required autofocus></div>
                        <div class="col-12 col-md-4"><label class="form-label">Kode Cus Lama</label><input name="kode_cus" value="{{ old('kode_cus', $edit ? $customer->kode_cus : '') }}" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Nama Customer <span class="text-danger">*</span></label><input name="NAMACUST" value="{{ old('NAMACUST', $edit ? $customer->NAMACUST : '') }}" class="form-control" required></div>
                        <div class="col-12 col-md-6"><label class="form-label">Alamat 1</label><textarea name="ALAMAT1" rows="2" class="form-control">{{ old('ALAMAT1', $edit ? $customer->ALAMAT1 : '') }}</textarea></div>
                        <div class="col-12 col-md-6"><label class="form-label">Alamat 2</label><textarea name="ALAMAT2" rows="2" class="form-control">{{ old('ALAMAT2', $edit ? $customer->ALAMAT2 : '') }}</textarea></div>
                        <div class="col-12 col-md-4"><label class="form-label">Kota</label><input name="KOTA" value="{{ old('KOTA', $edit ? $customer->KOTA : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Telepon</label><input name="TELEPON" value="{{ old('TELEPON', $edit ? $customer->TELEPON : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Fax</label><input name="FAX" value="{{ old('FAX', $edit ? $customer->FAX : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Email</label><input type="email" name="EMAIL" value="{{ old('EMAIL', $edit ? $customer->EMAIL : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">Kontak</label><input name="KONTAK" value="{{ old('KONTAK', $edit ? $customer->KONTAK : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label class="form-label">NPWP</label><input name="NPWP" value="{{ old('NPWP', $edit ? $customer->NPWP : '') }}" class="form-control"></div>
                        <div class="col-6 col-md-3"><label class="form-label">Area</label><input name="AREA" value="{{ old('AREA', $edit ? $customer->AREA : '') }}" class="form-control"></div>
                    </div>
                    <div class="d-grid d-sm-flex gap-2 mt-4 pt-3 border-top"><button class="btn btn-primary px-4">Simpan</button><a href="{{ route('customers.index') }}" class="btn btn-outline-secondary px-4">Batal</a></div>
                </form>
            </div>
        </div>
    </div>
</div>
<x-enter-as-tab form-id="customer-form" />
@endsection
