@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceCustomerEdit">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Edit Price Customer</h1>
        </div>
        <div class="col-auto">
            <button type="submit" form="priceForm" id="btnSimpanPriceCusEdit" data-debug="transaksiPriceCustomerEditSave" class="btn btn-success btn-sm">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('price-customer.index') }}" id="btnBatalPriceCusEdit" data-debug="transaksiPriceCustomerEditCancel" class="btn btn-outline-secondary btn-sm">
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

    <form id="priceForm" data-debug="transaksiPriceCustomerEditForm" method="POST" action="{{ route('price-customer.update', $priceCus) }}">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kode Customer <span class="text-danger">*</span></label>
                        <input type="text" name="KODECUS" data-debug="transaksiPriceCustomerEdit_KODECUS" class="form-control form-control-sm" value="{{ $priceCus->KODECUS }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">KODE</label>
                        <input type="text" name="KODE" data-debug="transaksiPriceCustomerEdit_KODE" class="form-control form-control-sm" value="{{ $priceCus->KODE }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama Customer</label>
                        <input type="text" name="NAMACUS" data-debug="transaksiPriceCustomerEdit_NAMACUS" class="form-control form-control-sm" value="{{ $priceCus->NAMACUS }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">TANGGAL</label>
                        <input type="date" name="TANGGAL" data-debug="transaksiPriceCustomerEdit_TANGGAL" class="form-control form-control-sm" value="{{ $priceCus->TANGGAL?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FROM</label>
                        <input type="text" name="FROM" data-debug="transaksiPriceCustomerEdit_FROM" class="form-control form-control-sm" value="{{ $priceCus->FROM }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">TO</label>
                        <input type="text" name="TO" data-debug="transaksiPriceCustomerEdit_TO" class="form-control form-control-sm" value="{{ $priceCus->TO }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">DARI</label>
                        <input type="number" name="DARI" data-debug="transaksiPriceCustomerEdit_DARI" class="form-control form-control-sm" value="{{ $priceCus->DARI }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SAMPAI</label>
                        <input type="number" name="SAMPAI" data-debug="transaksiPriceCustomerEdit_SAMPAI" class="form-control form-control-sm" value="{{ $priceCus->SAMPAI }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RUTE</label>
                        <input type="text" name="RUTE" data-debug="transaksiPriceCustomerEdit_RUTE" class="form-control form-control-sm" value="{{ $priceCus->RUTE }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">HARGA</label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceCustomerEdit_HARGA" class="form-control form-control-sm" value="{{ $priceCus->HARGA }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">KETERANGAN</label>
                        <input type="text" name="KETERANGAN" data-debug="transaksiPriceCustomerEdit_KETERANGAN" class="form-control form-control-sm" value="{{ $priceCus->KETERANGAN }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HV</label>
                        <input type="number" name="HV" data-debug="transaksiPriceCustomerEdit_HV" class="form-control form-control-sm" value="{{ $priceCus->HV }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HKG</label>
                        <input type="number" name="HKG" data-debug="transaksiPriceCustomerEdit_HKG" class="form-control form-control-sm" value="{{ $priceCus->HKG }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HBOK</label>
                        <input type="number" name="HBOK" data-debug="transaksiPriceCustomerEdit_HBOK" class="form-control form-control-sm" value="{{ $priceCus->HBOK }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">KUNCI</label>
                        <input type="text" name="KUNCI" data-debug="transaksiPriceCustomerEdit_KUNCI" class="form-control form-control-sm" value="{{ $priceCus->KUNCI }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HG</label>
                        <input type="number" name="HG" data-debug="transaksiPriceCustomerEdit_HG" class="form-control form-control-sm" value="{{ $priceCus->HG }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">JENIS</label>
                        <input type="text" name="JENIS" data-debug="transaksiPriceCustomerEdit_JENIS" class="form-control form-control-sm" maxlength="1" value="{{ $priceCus->JENIS }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
