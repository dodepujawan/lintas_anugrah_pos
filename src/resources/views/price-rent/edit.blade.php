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

    <form id="priceForm" method="POST" action="{{ route('price-rent.update', $price) }}">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">FROM <span class="text-danger">*</span></label>
                        <input type="text" name="FROM" data-debug="transaksiPriceRentEdit_FROM" class="form-control form-control-sm" value="{{ $price->FROM }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">TO <span class="text-danger">*</span></label>
                        <input type="text" name="TO" data-debug="transaksiPriceRentEdit_TO" class="form-control form-control-sm" value="{{ $price->TO }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">HARGA <span class="text-danger">*</span></label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceRentEdit_HARGA" class="form-control form-control-sm" value="{{ $price->HARGA }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">TANGGAL</label>
                        <input type="text" name="TANGGAL" data-debug="transaksiPriceRentEdit_TANGGAL" class="form-control form-control-sm" maxlength="1" value="{{ $price->TANGGAL }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
