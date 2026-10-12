@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Edit Kendaraan</h1>
        </div>
        <div class="col-auto">
            <button type="submit" form="kendaraanForm" class="btn btn-success btn-sm">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('kendaraan.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-lg"></i> Batal
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="kendaraanForm" method="POST" action="{{ route('kendaraan.update', $kendaraan) }}">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="KODE" class="form-control form-control-sm" value="{{ $kendaraan->KODE }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="NAMA" class="form-control form-control-sm" value="{{ $kendaraan->NAMA }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Plat</label>
                        <input type="text" name="PLAT" class="form-control form-control-sm" value="{{ $kendaraan->PLAT }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis</label>
                        <input type="text" name="JENIS" class="form-control form-control-sm" value="{{ $kendaraan->JENIS }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK B</label>
                        <input type="text" name="FNO_PRK_B" class="form-control form-control-sm" value="{{ $kendaraan->FNO_PRK_B }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK T</label>
                        <input type="text" name="FNO_PRK_T" class="form-control form-control-sm" value="{{ $kendaraan->FNO_PRK_T }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK</label>
                        <input type="text" name="FNO_PRK" class="form-control form-control-sm" value="{{ $kendaraan->FNO_PRK }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">NO BKK</label>
                        <input type="text" name="NO_BKK" class="form-control form-control-sm" value="{{ $kendaraan->NO_BKK }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kendaraan Type</label>
                        <input type="text" name="KENDARAAN_TYPE" class="form-control form-control-sm" value="{{ $kendaraan->KENDARAAN_TYPE }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
