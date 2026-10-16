@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceRentCreate">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Tambah Price Rent</h1>
        </div>
        <div class="col-auto">
            <button type="submit" form="priceForm" id="btnSimpanPriceRent" data-debug="transaksiPriceRentCreateSave" class="btn btn-success btn-sm">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('price-rent.index') }}" id="btnBatalPriceRent" data-debug="transaksiPriceRentCreateCancel" class="btn btn-outline-secondary btn-sm">
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

    <form id="priceForm" data-debug="transaksiPriceRentCreateForm" method="POST" action="{{ route('price-rent.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">KODEDGN</label>
                        <input type="text" name="KODEDGN" data-debug="transaksiPriceRentCreate_KODEDGN" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">KODE</label>
                        <input type="text" name="KODE" data-debug="transaksiPriceRentCreate_KODE" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TANGGAL</label>
                        <input type="date" name="TANGGAL" data-debug="transaksiPriceRentCreate_TANGGAL" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">PERIODE</label>
                        <input type="text" name="PERIODE" data-debug="transaksiPriceRentCreate_PERIODE" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">PLAT</label>
                        <input type="text" name="PLAT" data-debug="transaksiPriceRentCreate_PLAT" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">JENIS</label>
                        <input type="text" name="JENIS" data-debug="transaksiPriceRentCreate_JENIS" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ITEM</label>
                        <input type="text" name="ITEM" data-debug="transaksiPriceRentCreate_ITEM" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">FROM</label>
                        <input type="text" name="FROM" data-debug="transaksiPriceRentCreate_FROM" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TO</label>
                        <input type="text" name="TO" data-debug="transaksiPriceRentCreate_TO" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HARGA</label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceRentCreate_HARGA" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">KUNCI</label>
                        <input type="text" name="KUNCI" data-debug="transaksiPriceRentCreate_KUNCI" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
