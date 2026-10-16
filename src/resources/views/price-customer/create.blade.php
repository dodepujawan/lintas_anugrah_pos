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

    <form id="priceForm" data-debug="transaksiPriceCustomerCreateForm" method="POST" action="{{ route('price-customer.store') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kode Customer <span class="text-danger">*</span></label>
                        <input type="text" name="KODECUS" data-debug="transaksiPriceCustomerCreate_KODECUS" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">KODE</label>
                        <input type="text" name="KODE" data-debug="transaksiPriceCustomerCreate_KODE" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama Customer</label>
                        <input type="text" name="NAMACUS" data-debug="transaksiPriceCustomerCreate_NAMACUS" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">TANGGAL</label>
                        <input type="date" name="TANGGAL" data-debug="transaksiPriceCustomerCreate_TANGGAL" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FROM</label>
                        <input type="text" name="FROM" data-debug="transaksiPriceCustomerCreate_FROM" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">TO</label>
                        <input type="text" name="TO" data-debug="transaksiPriceCustomerCreate_TO" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">DARI</label>
                        <input type="number" name="DARI" data-debug="transaksiPriceCustomerCreate_DARI" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SAMPAI</label>
                        <input type="number" name="SAMPAI" data-debug="transaksiPriceCustomerCreate_SAMPAI" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RUTE</label>
                        <input type="text" name="RUTE" data-debug="transaksiPriceCustomerCreate_RUTE" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">HARGA</label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceCustomerCreate_HARGA" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">KETERANGAN</label>
                        <input type="text" name="KETERANGAN" data-debug="transaksiPriceCustomerCreate_KETERANGAN" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HV</label>
                        <input type="number" name="HV" data-debug="transaksiPriceCustomerCreate_HV" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HKG</label>
                        <input type="number" name="HKG" data-debug="transaksiPriceCustomerCreate_HKG" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HBOK</label>
                        <input type="number" name="HBOK" data-debug="transaksiPriceCustomerCreate_HBOK" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">KUNCI</label>
                        <input type="text" name="KUNCI" data-debug="transaksiPriceCustomerCreate_KUNCI" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">HG</label>
                        <input type="number" name="HG" data-debug="transaksiPriceCustomerCreate_HG" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">JENIS</label>
                        <input type="text" name="JENIS" data-debug="transaksiPriceCustomerCreate_JENIS" class="form-control form-control-sm" maxlength="1">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
