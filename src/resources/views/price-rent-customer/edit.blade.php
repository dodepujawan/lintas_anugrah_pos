@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceRentCusEdit">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Edit Price Rent Customer</h1>
        </div>
        <div class="col-auto">
            <button type="submit" form="priceForm" id="btnSimpanPriceRentCusEdit" data-debug="transaksiPriceRentCusEditSave" class="btn btn-success btn-sm">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('price-rent-customer.index') }}" id="btnBatalPriceRentCusEdit" data-debug="transaksiPriceRentCusEditCancel" class="btn btn-outline-secondary btn-sm">
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

    <form id="priceForm" data-debug="transaksiPriceRentCusEditForm" method="POST" action="{{ route('price-rent-customer.update', $pricedingincus) }}">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Kode Customer <span class="text-danger">*</span></label>
                        <input type="text" name="KODECUS" data-debug="transaksiPriceRentCusEdit_KODECUS" class="form-control form-control-sm" value="{{ $pricedingincus->KODECUS }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nama Customer</label>
                        <input type="text" name="NAMACUS" data-debug="transaksiPriceRentCusEdit_NAMACUS" class="form-control form-control-sm" value="{{ $pricedingincus->NAMACUS }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">KODEDGN</label>
                        <input type="text" name="KODEDGN" data-debug="transaksiPriceRentCusEdit_KODEDGN" class="form-control form-control-sm" value="{{ $pricedingincus->KODEDGN }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">KODE</label>
                        <input type="text" name="KODE" data-debug="transaksiPriceRentCusEdit_KODE" class="form-control form-control-sm" value="{{ $pricedingincus->KODE }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TANGGAL</label>
                        <input type="date" name="TANGGAL" data-debug="transaksiPriceRentCusEdit_TANGGAL" class="form-control form-control-sm" value="{{ $pricedingincus->TANGGAL?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">PERIODE</label>
                        <input type="text" name="PERIODE" data-debug="transaksiPriceRentCusEdit_PERIODE" class="form-control form-control-sm" value="{{ $pricedingincus->PERIODE }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">PLAT</label>
                        <input type="text" name="PLAT" data-debug="transaksiPriceRentCusEdit_PLAT" class="form-control form-control-sm" value="{{ $pricedingincus->PLAT }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">JENIS</label>
                        <input type="text" name="JENIS" data-debug="transaksiPriceRentCusEdit_JENIS" class="form-control form-control-sm" value="{{ $pricedingincus->JENIS }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ITEM</label>
                        <input type="text" name="ITEM" data-debug="transaksiPriceRentCusEdit_ITEM" class="form-control form-control-sm" value="{{ $pricedingincus->ITEM }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">FROM</label>
                        <input type="text" name="FROM" data-debug="transaksiPriceRentCusEdit_FROM" class="form-control form-control-sm" value="{{ $pricedingincus->FROM }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TO</label>
                        <input type="text" name="TO" data-debug="transaksiPriceRentCusEdit_TO" class="form-control form-control-sm" value="{{ $pricedingincus->TO }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HARGA</label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceRentCusEdit_HARGA" class="form-control form-control-sm" value="{{ $pricedingincus->HARGA }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">KUNCI</label>
                        <input type="text" name="KUNCI" data-debug="transaksiPriceRentCusEdit_KUNCI" class="form-control form-control-sm" value="{{ $pricedingincus->KUNCI }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
