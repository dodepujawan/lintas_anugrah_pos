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
                    <div class="col-md-4">
                        <label class="form-label">Nama Item</label>
                        <input type="text" name="KETERANGAN" data-debug="transaksiPriceEdit_KETERANGAN" class="form-control form-control-sm" value="{{ $price->KETERANGAN }}" placeholder="Contoh: EC > 40 KG">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">DARI (Kg)</label>
                        <input type="number" name="DARI" data-debug="transaksiPriceEdit_DARI" class="form-control form-control-sm" value="{{ $price->DARI }}" placeholder="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SAMPAI (Kg)</label>
                        <input type="number" name="SAMPAI" data-debug="transaksiPriceEdit_SAMPAI" class="form-control form-control-sm" value="{{ $price->SAMPAI }}" placeholder="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RUTE</label>
                        <input type="text" name="RUTE" data-debug="transaksiPriceEdit_RUTE" class="form-control form-control-sm" value="{{ $price->RUTE }}" placeholder="Contoh: DIY - DPS">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">HARGA</label>
                        <input type="number" name="HARGA" data-debug="transaksiPriceEdit_HARGA" class="form-control form-control-sm" value="{{ $price->HARGA }}" placeholder="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">JENIS</label>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="JENIS" value="1" id="jenisEceran" {{ $price->JENIS == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="jenisEceran">Eceran</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="JENIS" value="2" id="jenisBoking" {{ $price->JENIS == 2 ? 'checked' : '' }}>
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
