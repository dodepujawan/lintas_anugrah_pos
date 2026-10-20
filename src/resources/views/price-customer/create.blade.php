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
            <a href="{{ $customer ? route('price-customer.customer-prices', $customer->CUSTOMER) : route('price-customer.index') }}" id="btnBatalPriceCus" data-debug="transaksiPriceCustomerCreateCancel" class="btn btn-outline-secondary btn-sm">
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

    <form id="priceForm" data-debug="transaksiPriceCustomerCreateForm" method="POST" action="{{ route('price-customer.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kode Customer <span class="text-danger">*</span></label>
                        <input type="text" name="KODECUS" data-debug="transaksiPriceCustomerCreate_KODECUS" class="form-control form-control-sm" value="{{ $customer->CUSTOMER ?? '' }}" {{ $customer ? 'readonly' : 'required' }}>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama Customer</label>
                        <input type="text" name="NAMACUS" data-debug="transaksiPriceCustomerCreate_NAMACUS" class="form-control form-control-sm" value="{{ $customer->NAMACUST ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">TANGGAL</label>
                        <input type="date" name="TANGGAL" data-debug="transaksiPriceCustomerCreate_TANGGAL" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama Item</label>
                        <input type="text" name="KETERANGAN" data-debug="transaksiPriceCustomerCreate_KETERANGAN" class="form-control form-control-sm" placeholder="Contoh: EC > 40 KG">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">DARI (Kg)</label>
                        <input type="number" name="DARI" data-debug="transaksiPriceCustomerCreate_DARI" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SAMPAI (Kg)</label>
                        <input type="number" name="SAMPAI" data-debug="transaksiPriceCustomerCreate_SAMPAI" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RUTE</label>
                        <input type="text" name="RUTE" data-debug="transaksiPriceCustomerCreate_RUTE" class="form-control form-control-sm" placeholder="Contoh: DIY - DPS">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HARGA</label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceCustomerCreate_HARGA" class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">JENIS</label>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="JENIS" value="1" id="jenisEceran" checked>
                                <label class="form-check-label" for="jenisEceran">Eceran</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="JENIS" value="2" id="jenisBoking">
                                <label class="form-check-label" for="jenisBoking">Boking</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
