@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiKendaraanCreate">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Tambah Kendaraan</h1>
        </div>
        <div class="col-auto">
            <button type="submit" form="kendaraanForm" id="btnSimpanKendaraan" data-debug="transaksiKendaraanCreateSave" class="btn btn-success btn-sm">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('kendaraan.index') }}" id="btnBatalKendaraan" data-debug="transaksiKendaraanCreateCancel" class="btn btn-outline-secondary btn-sm">
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

    <form id="kendaraanForm" data-debug="transaksiKendaraanCreateForm" method="POST" action="{{ route('kendaraan.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="KODE" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="NAMA" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Plat</label>
                        <input type="text" name="PLAT" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis</label>
                        <input type="text" name="JENIS" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK B</label>
                        <input type="text" name="FNO_PRK_B" data-debug="transaksiKendaraanCreate_FNO_PRK_B" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK P</label>
                        <input type="text" name="FNO_PRK_P" data-debug="transaksiKendaraanCreate_FNO_PRK_P" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK S</label>
                        <input type="text" name="FNO_PRK_S" data-debug="transaksiKendaraanCreate_FNO_PRK_S" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK O</label>
                        <input type="text" name="FNO_PRK_O" data-debug="transaksiKendaraanCreate_FNO_PRK_O" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK M</label>
                        <input type="text" name="FNO_PRK_M" data-debug="transaksiKendaraanCreate_FNO_PRK_M" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK T</label>
                        <input type="text" name="FNO_PRK_T" data-debug="transaksiKendaraanCreate_FNO_PRK_T" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FNO PRK</label>
                        <input type="text" name="FNO_PRK" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">NO BKK</label>
                        <input type="text" name="NO_BKK" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kendaraan Type</label>
                        <input type="text" name="KENDARAAN_TYPE" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
