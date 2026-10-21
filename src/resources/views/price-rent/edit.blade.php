@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceRentEdit"
    x-data="priceRentForm(@js($vehicles), @js(old('KODE', $price->KODE)))">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Edit Price Dingin</h1>
            <p class="text-muted small mb-0">{{ $price->KODEDGN }}</p>
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
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Kendaraan <span class="text-danger">*</span></label>
                        <select name="KODE" x-model="selectedCode" @change="selectVehicle()" data-debug="transaksiPriceRentEdit_KODE" class="form-select form-select-sm" required>
                            <option value="">Pilih kendaraan</option>
                            <template x-for="vehicle in vehicles" :key="vehicle.KODE">
                                <option :value="vehicle.KODE" x-text="vehicle.KODE + ' - ' + (vehicle.NAMA || vehicle.PLAT || '-')"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PLAT</label>
                        <input type="text" name="PLAT" x-model="plat" data-debug="transaksiPriceRentEdit_PLAT" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">JENIS MOBIL</label>
                        <input type="text" name="JENIS" x-model="jenis" data-debug="transaksiPriceRentEdit_JENIS" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PERIODE <span class="text-danger">*</span></label>
                        <input type="text" name="PERIODE" x-model="period" data-debug="transaksiPriceRentEdit_PERIODE" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NAMA ITEM</label>
                        <input type="text" name="ITEM" x-model="item" data-debug="transaksiPriceRentEdit_ITEM" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">HARGA <span class="text-danger">*</span></label>
                        <input type="number" name="HARGA" value="{{ old('HARGA', $price->HARGA) }}" data-debug="transaksiPriceRentEdit_HARGA" class="form-control form-control-sm" min="0" step="1" required>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('priceRentForm', (vehicles, initialCode) => ({
        vehicles: vehicles || [],
        selectedCode: initialCode || '',
        plat: @js(old('PLAT', $price->PLAT)),
        jenis: @js(old('JENIS', $price->kendaraan?->NAMA ?? $price->JENIS)),
        period: @js(old('PERIODE', $price->PERIODE)),
        item: @js(old('ITEM', $price->ITEM)),

        init() {
            this.selectVehicle();
        },

        selectVehicle() {
            const vehicle = this.vehicles.find((item) => item.KODE === this.selectedCode);
            this.plat = vehicle?.PLAT || this.plat || '';
            this.jenis = vehicle?.NAMA || this.jenis || '';
            this.updateItem();
        },

        updateItem() {
            const vehicle = this.vehicles.find((item) => item.KODE === this.selectedCode);
            this.item = [this.plat, vehicle?.JENIS].filter(Boolean).join(' ');
        }
    }));
});
</script>
@endpush
