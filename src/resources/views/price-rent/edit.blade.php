@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceRentEdit">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Edit Price Rent</h1>
        </div>
        <div class="col-auto">
            <button type="submit" form="priceForm" id="btnSimpanPriceRentEdit" data-debug="transaksiPriceRentEditSave" class="btn btn-success btn-sm">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('price-rent.index') }}" id="btnBatalPriceRentEdit" data-debug="transaksiPriceRentEditCancel" class="btn btn-outline-secondary btn-sm">
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

    <form id="priceForm" data-debug="transaksiPriceRentEditForm" method="POST" action="{{ route('price-rent.update', $price) }}">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">KODEDGN</label>
                        <input type="text" name="KODEDGN" data-debug="transaksiPriceRentEdit_KODEDGN" class="form-control form-control-sm" value="{{ $price->KODEDGN }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">KODE</label>
                        <input type="text" name="KODE" data-debug="transaksiPriceRentEdit_KODE" class="form-control form-control-sm" value="{{ $price->KODE }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TANGGAL</label>
                        <input type="date" name="TANGGAL" data-debug="transaksiPriceRentEdit_TANGGAL" class="form-control form-control-sm" value="{{ $price->TANGGAL?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">PERIODE</label>
                        <input type="text" name="PERIODE" data-debug="transaksiPriceRentEdit_PERIODE" class="form-control form-control-sm" value="{{ $price->PERIODE }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">PLAT</label>
                        <input type="text" name="PLAT" data-debug="transaksiPriceRentEdit_PLAT" class="form-control form-control-sm" value="{{ $price->PLAT }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">JENIS</label>
                        <input type="text" name="JENIS" data-debug="transaksiPriceRentEdit_JENIS" class="form-control form-control-sm" value="{{ $price->JENIS }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ITEM</label>
                        <input type="text" name="ITEM" data-debug="transaksiPriceRentEdit_ITEM" class="form-control form-control-sm" value="{{ $price->ITEM }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">FROM</label>
                        <input type="text" name="FROM" data-debug="transaksiPriceRentEdit_FROM" class="form-control form-control-sm" value="{{ $price->FROM }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TO</label>
                        <input type="text" name="TO" data-debug="transaksiPriceRentEdit_TO" class="form-control form-control-sm" value="{{ $price->TO }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HARGA</label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceRentEdit_HARGA" class="form-control form-control-sm" value="{{ $price->HARGA }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">KUNCI</label>
                        <input type="text" name="KUNCI" data-debug="transaksiPriceRentEdit_KUNCI" class="form-control form-control-sm" value="{{ $price->KUNCI }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
