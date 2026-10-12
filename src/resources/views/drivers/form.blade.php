@extends('layouts.app')
@php($edit = isset($driver))
@section('title', $edit ? 'Edit Sopir' : 'Tambah Sopir')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        @if($errors->any())
            <div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h1 class="h5 mb-1">{{ $edit ? 'Edit' : 'Tambah' }} Master Sopir</h1>
                <div class="text-muted small">Enter berpindah ke field berikutnya. Enter pada field terakhir akan menyimpan.</div>
            </div>
            <div class="card-body p-3 p-md-4">
                <form id="driver-form" method="POST" action="{{ $edit ? route('drivers.update', $driver) : route('drivers.store') }}">
                    @csrf @if($edit) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-12 col-md-4"><label class="form-label">Kode Sopir <span class="text-danger">*</span></label><input name="code" value="{{ old('code', $edit ? $driver->code : '') }}" class="form-control" required autofocus></div>
                        <div class="col-12 col-md-8"><label class="form-label">Nama Sopir <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $edit ? $driver->name : '') }}" class="form-control" required></div>
                        <div class="col-12 col-md-4"><label class="form-label">Telepon</label><input name="phone" inputmode="tel" value="{{ old('phone', $edit ? $driver->phone : '') }}" class="form-control"></div>
                        <div class="col-12 col-md-8"><label class="form-label">Alamat</label><textarea name="address" rows="2" class="form-control">{{ old('address', $edit ? $driver->address : '') }}</textarea></div>
                        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="is-active" name="is_active" value="1" @checked(old('is_active', $edit ? $driver->is_active : true))><label class="form-check-label" for="is-active">Sopir aktif</label></div></div>
                    </div>
                    <div class="d-grid d-sm-flex gap-2 mt-4 pt-3 border-top"><button type="submit" class="btn btn-primary px-4">Simpan</button><a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary px-4">Batal</a></div>
                </form>
            </div>
        </div>
    </div>
</div>
<x-enter-as-tab form-id="driver-form" />
@endsection
