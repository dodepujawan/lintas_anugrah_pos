@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiPriceRentCusDetail"
    x-data="priceRentCustomerDetail('{{ $customer->CUSTOMER }}')"
    @price-rent-saved.window="showToast($event.detail.message, $event.detail.type)">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Harga Dingin Customer</h1>
            <p class="text-muted small mb-0">{{ $customer->NAMACUST ?? '-' }} ({{ $customer->CUSTOMER }})</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('price-rent-customer.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button type="button" class="btn btn-success btn-sm" @click="updateAll()" :disabled="updating">
                <span x-show="!updating"><i class="bi bi-arrow-repeat"></i> Update Harga Sewa</span>
                <span x-show="updating"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
            </button>
        </div>
    </div>

    <div x-show="toastMessage" class="alert alert-dismissible fade show" :class="toastType === 'success' ? 'alert-success' : 'alert-danger'" style="display:none;">
        <span x-text="toastMessage"></span>
        <button type="button" class="btn-close" @click="toastMessage = ''"></button>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6"><strong>Nama Customer:</strong><br>{{ $customer->NAMACUST ?? '-' }}</div>
                <div class="col-md-6"><strong>Kode Customer:</strong><br>{{ $customer->CUSTOMER }}</div>
                <div class="col-md-6 mt-2"><strong>Jenis Usaha:</strong><br>{{ $customer->TYPECUST ?? '-' }}</div>
                <div class="col-md-6 mt-2"><strong>Pemilik:</strong><br>{{ $customer->nama_p ?? '-' }}</div>
                <div class="col-12 mt-2"><strong>Alamat:</strong><br>{{ $customer->ALAMAT1 ?? '-' }}</div>
            </div>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control" placeholder="Cari kendaraan, item, periode, plat, jenis..." value="{{ $search }}">
            <button class="btn btn-primary">Cari</button>
            <a href="{{ route('price-rent-customer.customer-prices', $customer->CUSTOMER) }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>No</th><th>Kendaraan</th><th>ITEM</th><th>PERIODE</th><th>PLAT</th><th>JENIS</th><th class="text-end">HARGA</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($prices as $price)
                    <tr x-data="priceRentCustomerRow({{ $price->id }}, {{ (float) ($price->HARGA ?? 0) }})">
                        <td>{{ $prices->firstItem() + $loop->index }}</td>
                        <td>{{ $price->kendaraan?->NAMA ?? $price->KODE ?? '-' }}</td>
                        <td>{{ $price->ITEM ?? '-' }}</td>
                        <td>{{ $price->PERIODE ?? '-' }}</td>
                        <td>{{ $price->PLAT ?? '-' }}</td>
                        <td>{{ $price->JENIS ?? '-' }}</td>
                        <td><input type="number" x-model.number="editedPrice" class="form-control form-control-sm text-end" style="background:#fff7d1; min-width:120px;" min="0"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-success" @click="save()" :disabled="saving"><i class="bi bi-save"></i></button></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada harga untuk customer ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $prices->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('priceRentCustomerDetail', (kodecus) => ({
        kodecus, updating: false, toastMessage: '', toastType: 'success',
        showToast(message, type = 'success') { this.toastMessage = message; this.toastType = type; setTimeout(() => this.toastMessage = '', 4000); },
        async updateAll() {
            if (!confirm('Update harga sewa customer ini?')) return;
            this.updating = true;
            const response = await fetch('{{ route('price-rent-customer.update-all', '__KODECUS__') }}'.replace('__KODECUS__', encodeURIComponent(this.kodecus)), { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'} });
            const data = await response.json().catch(() => ({}));
            this.showToast(data.message || 'Update selesai.', response.ok ? 'success' : 'error');
            if (response.ok) setTimeout(() => window.location.reload(), 900);
            this.updating = false;
        }
    }));
    Alpine.data('priceRentCustomerRow', (id, price) => ({
        id, originalPrice: Number(price) || 0, editedPrice: Number(price) || 0, saving: false,
        async save() {
            this.saving = true;
            const response = await fetch('{{ route('price-rent-customer.update-row') }}', { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify({id: this.id, harga: this.editedPrice}) });
            const data = await response.json().catch(() => ({}));
            if (response.ok) this.originalPrice = this.editedPrice;
            else this.editedPrice = this.originalPrice;
            this.$dispatch('price-rent-saved', {message: data.message || 'Gagal menyimpan harga.', type: response.ok ? 'success' : 'error'});
            this.saving = false;
        }
    }));
});
</script>
@endpush
