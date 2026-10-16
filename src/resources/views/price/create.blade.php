@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceCreate">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Tambah Price EKS</h1>
        </div>
        <div class="col-auto">
            <button type="submit" form="priceForm" id="btnSimpanPrice" data-debug="transaksiPriceCreateSave" class="btn btn-success btn-sm">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('price.index') }}" id="btnBatalPrice" data-debug="transaksiPriceCreateCancel" class="btn btn-outline-secondary btn-sm">
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

    <form id="priceForm" data-debug="transaksiPriceCreateForm" method="POST" action="{{ route('price.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">KODE</label>
                        <input type="text" name="KODE" data-debug="transaksiPriceCreate_KODE" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">FROM</label>
                        <input type="text" name="FROM" data-debug="transaksiPriceCreate_FROM" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TO</label>
                        <input type="text" name="TO" data-debug="transaksiPriceCreate_TO" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HARGA</label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceCreate_HARGA" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TANGGAL</label>
                        <input type="date" name="TANGGAL" data-debug="transaksiPriceCreate_TANGGAL" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">DARI</label>
                        <input type="number" name="DARI" data-debug="transaksiPriceCreate_DARI" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SAMPAI</label>
                        <input type="number" name="SAMPAI" data-debug="transaksiPriceCreate_SAMPAI" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RUTE</label>
                        <input type="text" name="RUTE" data-debug="transaksiPriceCreate_RUTE" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">KETERANGAN</label>
                        <input type="text" name="KETERANGAN" data-debug="transaksiPriceCreate_KETERANGAN" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HV</label>
                        <input type="number" name="HV" data-debug="transaksiPriceCreate_HV" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HKG</label>
                        <input type="number" name="HKG" data-debug="transaksiPriceCreate_HKG" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HBOK</label>
                        <input type="number" name="HBOK" data-debug="transaksiPriceCreate_HBOK" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">KUNCI</label>
                        <input type="text" name="KUNCI" data-debug="transaksiPriceCreate_KUNCI" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HG</label>
                        <input type="number" name="HG" data-debug="transaksiPriceCreate_HG" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">JENIS</label>
                        <input type="text" name="JENIS" data-debug="transaksiPriceCreate_JENIS" class="form-control form-control-sm" maxlength="1">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
