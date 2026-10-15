@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceCustomerCreate">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Tambah Price Customer</h1>
        </div>
        <div class="col-auto">
            <button type="submit" form="priceForm" id="btnSimpanPriceCus" data-debug="transaksiPriceCustomerCreateSave" class="btn btn-success btn-sm">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('price-customer.index') }}" id="btnBatalPriceCus" data-debug="transaksiPriceCustomerCreateCancel" class="btn btn-outline-secondary btn-sm">
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

    <form id="priceForm" method="POST" action="{{ route('price-customer.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Customer <span class="text-danger">*</span></label>
                        <input type="text" name="KODECUS" data-debug="transaksiPriceCustomerCreate_KODECUS" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Customer</label>
                        <input type="text" name="NAMACUS" data-debug="transaksiPriceCustomerCreate_NAMACUS" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">FROM <span class="text-danger">*</span></label>
                        <input type="text" name="FROM" data-debug="transaksiPriceCustomerCreate_FROM" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TO <span class="text-danger">*</span></label>
                        <input type="text" name="TO" data-debug="transaksiPriceCustomerCreate_TO" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HARGA <span class="text-danger">*</span></label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceCustomerCreate_HARGA" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">TANGGAL</label>
                        <input type="text" name="TANGGAL" data-debug="transaksiPriceCustomerCreate_TANGGAL" class="form-control form-control-sm" maxlength="1">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
