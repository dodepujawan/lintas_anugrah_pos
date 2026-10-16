@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceEdit">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Edit Price EKS</h1>
        </div>
        <div class="col-auto">
            <button type="submit" form="priceForm" id="btnSimpanPriceEdit" data-debug="transaksiPriceEditSave" class="btn btn-success btn-sm">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('price.index') }}" id="btnBatalPriceEdit" data-debug="transaksiPriceEditCancel" class="btn btn-outline-secondary btn-sm">
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

    <form id="priceForm" data-debug="transaksiPriceEditForm" method="POST" action="{{ route('price.update', $price) }}">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">KODE</label>
                        <input type="text" name="KODE" data-debug="transaksiPriceEdit_KODE" class="form-control form-control-sm" value="{{ $price->KODE }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">FROM</label>
                        <input type="text" name="FROM" data-debug="transaksiPriceEdit_FROM" class="form-control form-control-sm" value="{{ $price->FROM }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TO</label>
                        <input type="text" name="TO" data-debug="transaksiPriceEdit_TO" class="form-control form-control-sm" value="{{ $price->TO }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HARGA</label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceEdit_HARGA" class="form-control form-control-sm" value="{{ $price->HARGA }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TANGGAL</label>
                        <input type="date" name="TANGGAL" data-debug="transaksiPriceEdit_TANGGAL" class="form-control form-control-sm" value="{{ $price->TANGGAL?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">DARI</label>
                        <input type="number" name="DARI" data-debug="transaksiPriceEdit_DARI" class="form-control form-control-sm" value="{{ $price->DARI }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SAMPAI</label>
                        <input type="number" name="SAMPAI" data-debug="transaksiPriceEdit_SAMPAI" class="form-control form-control-sm" value="{{ $price->SAMPAI }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RUTE</label>
                        <input type="text" name="RUTE" data-debug="transaksiPriceEdit_RUTE" class="form-control form-control-sm" value="{{ $price->RUTE }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">KETERANGAN</label>
                        <input type="text" name="KETERANGAN" data-debug="transaksiPriceEdit_KETERANGAN" class="form-control form-control-sm" value="{{ $price->KETERANGAN }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HV</label>
                        <input type="number" name="HV" data-debug="transaksiPriceEdit_HV" class="form-control form-control-sm" value="{{ $price->HV }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HKG</label>
                        <input type="number" name="HKG" data-debug="transaksiPriceEdit_HKG" class="form-control form-control-sm" value="{{ $price->HKG }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HBOK</label>
                        <input type="number" name="HBOK" data-debug="transaksiPriceEdit_HBOK" class="form-control form-control-sm" value="{{ $price->HBOK }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">KUNCI</label>
                        <input type="text" name="KUNCI" data-debug="transaksiPriceEdit_KUNCI" class="form-control form-control-sm" value="{{ $price->KUNCI }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HG</label>
                        <input type="number" name="HG" data-debug="transaksiPriceEdit_HG" class="form-control form-control-sm" value="{{ $price->HG }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">JENIS</label>
                        <input type="text" name="JENIS" data-debug="transaksiPriceEdit_JENIS" class="form-control form-control-sm" maxlength="1" value="{{ $price->JENIS }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
