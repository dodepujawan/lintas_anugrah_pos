@extends('layouts.app')

@section('content')

<div class="container-fluid"
    data-debug="transaksiPriceCustomerDetail"
    data-update-all-url="{{ route('price-customer.update-all', '__KODECUS__') }}"
    data-update-row-url="{{ route('price-customer.update-row') }}"
    data-success-message="{{ session('success') }}"
    x-data="priceCusPage(@js(trim($customer->CUSTOMER)))"
    @price-saved.window="showToast($event.detail.message, $event.detail.type)">
    <div class="row mb-3" data-debug="transaksiPriceCustomerDetailHeader">
        <div class="col">
            <h1 class="page-title mb-1">Price Customer</h1>
            <p class="text-muted small mb-0">Harga untuk customer: <strong>{{ $customer->CUSTOMER }}</strong> - {{ $customer->NAMACUST ?? '-' }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('price-customer.index') }}" id="btnKembaliPriceCus" data-debug="transaksiPriceCustomerDetailBack" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('price-customer.create', ['kodecus' => $customer->CUSTOMER]) }}" id="btnTambahPriceCus" data-debug="transaksiPriceCustomerDetailCreateBtn" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
            <button type="button" id="btnUpdateAllPriceCus" data-debug="transaksiPriceCustomerDetailUpdateAll"
                @click="updateAll()" class="btn btn-warning btn-sm" :disabled="updatingAll">
                <span x-show="!updatingAll"><i class="bi bi-upload"></i> Update All</span>
                <span x-show="updatingAll"><span class="spinner-border spinner-border-sm" role="status"></span> Sync...</span>
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div x-show="toastMessage" x-transition class="alert alert-dismissible fade show mb-3"
        :class="toastType === 'success' ? 'alert-success' : 'alert-danger'" role="alert"
        style="display: none;">
        <i class="bi" :class="toastType === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'"></i>
        <span x-text="toastMessage"></span>
        <button type="button" class="btn-close" @click="toastMessage = ''"></button>
    </div>

    <div class="card mb-3" data-debug="transaksiPriceCustomerInfoPanel">
        <div class="card-body">
            <h5 class="mb-3">
                <i class="bi bi-info-circle"></i> Informasi Customer
            </h5>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <strong>Nama Customer:</strong><br>
                    <span>{{ $customer->NAMACUST ?? '-' }}</span>
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Kode Customer:</strong><br>
                    <span class="font-monospace">{{ $customer->CUSTOMER }}</span>
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Jenis Usaha:</strong><br>
                    <span>{{ $customer->TYPECUST ?? '-' }}</span>
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Nama Pemilik:</strong><br>
                    <span>{{ $customer->nama_p ?? '-' }}</span>
                </div>
                <div class="col-12">
                    <strong>Alamat:</strong><br>
                    <span>{{ $customer->ALAMAT1 ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" id="formFilterPriceCus" data-debug="transaksiPriceCustomerDetailFilter" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari Nama Item / DARI / SAMPAI / RUTE..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('price-customer.customer-prices', $customer->CUSTOMER) }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NAMA_ITEM</th>
                        <th>DARI</th>
                        <th>SAMPAI</th>
                        <th>RUTE</th>
                        <th class="text-end">HARGA</th>
                        <th>JENIS</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($priceCuses as $p)
                    <tr x-data='priceRow({
                        id: @json($p->id),
                        kode: @json($p->KODE ?? null),
                        originalPrice: @json((float) ($p->HARGA ?? 0))
                    })' data-debug="transaksiPriceCustomerDetailRow_{{ $p->id }}">
                        <td>{{ $priceCuses->firstItem() + $loop->index }}</td>
                        <td>{{ $p->KETERANGAN ?? '-' }}</td>
                        <td>{{ $p->DARI ?? '-' }}</td>
                        <td>{{ $p->SAMPAI ?? '-' }}</td>
                        <td>{{ $p->RUTE ?? '-' }}</td>
                        <td class="text-end" data-debug="transaksiPriceCustomerDetailHarga_{{ $p->id }}">
                            <input type="number" x-model.number="editedPrice"
                                   class="form-control form-control-sm text-end"
                                   style="background:#fff7d1; display:inline-block; width:auto; min-width:120px;"
                                   min="0" step="1" x-init="editedPrice = Number(editedPrice) || 0">
                        </td>
                        <td>{{ $p->JENIS == 1 ? 'Eceran' : ($p->JENIS == 2 ? 'Boking' : '-') }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" id="btnSavePrice_{{ $p->id }}" data-debug="transaksiPriceCustomerDetailSaveBtn_{{ $p->id }}"
                                    @click="savePrice()" class="btn btn-sm btn-outline-success" title="Simpan Harga" :disabled="saving">
                                    <template x-if="!saving">
                                        <i class="bi bi-save"></i>
                                    </template>
                                    <template x-if="saving">
                                        <span class="spinner-border spinner-border-sm"></span>
                                    </template>
                                </button>
                                {{-- <a href="{{ route('price-customer.edit', $p) }}" id="btnEditPriceCus_{{ $p->id }}" data-debug="transaksiPriceCustomerDetailEditBtn_{{ $p->id }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a> --}}
                                <form class="d-inline" method="POST" action="{{ route('price-customer.destroy', $p) }}" onsubmit="return confirm('Hapus price customer {{ $p->KETERANGAN ?? $p->KODE }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" id="btnDeletePriceCus_{{ $p->id }}" data-debug="transaksiPriceCustomerDetailDeleteBtn_{{ $p->id }}" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data price untuk customer ini.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $priceCuses->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('priceCusPage', (kodecus) => ({
        kodecus: kodecus,
        toastMessage: '',
        toastType: 'success',
        updatingAll: false,

        init() {
            @if(session('success'))
            this.showToast(@js(session('success')), 'success');
            @endif

            this.updateAllUrl = '{{ route('price-customer.update-all', '__KODECUS__') }}';
        },

        showToast(message, type = 'success') {
            this.toastMessage = message;
            this.toastType = type;
            setTimeout(() => { this.toastMessage = ''; }, 5000);
        },

        async updateAll() {
            if (!confirm('Update semua harga customer ini? Harga baru yang belum ada akan ditambahkan.')) return;

            this.updatingAll = true;
            this.toastMessage = '';

            try {
                const url = this.updateAllUrl.replace('__KODECUS__', encodeURIComponent(this.kodecus));
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    setTimeout(() => { window.location.reload(); }, 1500);
                } else {
                    this.showToast(data.message || 'Gagal update harga.', 'error');
                }
            } catch (e) {
                this.showToast('Terjadi kesalahan: ' + e.message, 'error');
            } finally {
                this.updatingAll = false;
            }
        }
    }));

    Alpine.data('priceRow', (config) => ({
        id: config.id,
        kode: config.kode,
        originalPrice: Number(config.originalPrice) || 0,
        editedPrice: Number(config.originalPrice) || 0,
        saving: false,

        async savePrice() {
            const harga = parseFloat(this.editedPrice);

            if (isNaN(harga) || this.editedPrice === '') {
                this.$dispatch('price-saved', { message: 'Harga harus angka!', type: 'error' });
                this.editedPrice = this.originalPrice;
                return;
            }

            this.saving = true;

            try {
                const response = await fetch('{{ route('price-customer.update-row') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        id: this.id,
                        harga: harga,
                    }),
                });
                const data = await response.json();

                if (data.success) {
                    this.originalPrice = harga;
                    this.$dispatch('price-saved', { message: data.message, type: 'success' });
                } else {
                    this.editedPrice = this.originalPrice;
                    this.$dispatch('price-saved', { message: data.message || 'Gagal update harga.', type: 'error' });
                }
            } catch (e) {
                this.editedPrice = this.originalPrice;
                this.$dispatch('price-saved', { message: 'Terjadi kesalahan: ' + e.message, type: 'error' });
            } finally {
                this.saving = false;
            }
        }
    }));
});
</script>
@endpush
