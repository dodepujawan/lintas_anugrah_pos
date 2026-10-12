@extends('layouts.app')

@section('title', 'Edit Hutang')

@push('styles')
<style>
.hutang-berjalan-form .form-control,
.hutang-berjalan-form .form-select,
.hutang-berjalan-form .form-label,
.hutang-berjalan-form .input-group .btn { font-size:1.05rem; }
.hutang-berjalan-form .form-label { margin-bottom:.4rem; }
.hutang-berjalan-form .form-text { font-size:.9rem; }
.hutang-berjalan-form .btn-primary#saveHutangButton { font-size:1.05rem; padding:.45rem 1.1rem; }
.hutang-berjalan-form .fs-4 { font-size:1.6rem; }
.hutang-berjalan-form .alert { font-size:1rem; }
</style>
@endpush

@section('content')
<div class="card shadow-sm border-0 hutang-berjalan-form">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="h5 mb-1">Edit Hutang Supplier</h2>
                <p class="text-muted mb-0">{{ $hutang->NOFAKTUR }} - {{ $hutang->supplier_display }}</p>
            </div>
            <a href="{{ route('hutang.show', $hutang) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ((float) $hutang->BAYAR > 0)
            <div class="alert alert-warning">
                Hutang ini sudah memiliki pembayaran, sehingga tidak dapat diubah.
            </div>
        @endif

        <form method="POST" action="{{ route('hutang.update', $hutang) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">No. Faktur</label>
                    <input type="text" name="nofaktur" class="form-control @error('nofaktur') is-invalid @enderror" value="{{ old('nofaktur', $hutang->NOFAKTUR) }}" required @disabled((float) $hutang->BAYAR > 0)>
                    @error('nofaktur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Faktur</label>
                    <input type="date" name="tglfaktur" class="form-control @error('tglfaktur') is-invalid @enderror" value="{{ old('tglfaktur', $hutang->TGLFAKTUR->format('Y-m-d')) }}" required @disabled((float) $hutang->BAYAR > 0)>
                    @error('tglfaktur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Jatuh Tempo</label>
                    <input type="date" name="tgljt" class="form-control @error('tgljt') is-invalid @enderror" value="{{ old('tgljt', $hutang->TGLJT ? $hutang->TGLJT->format('Y-m-d') : '') }}" @disabled((float) $hutang->BAYAR > 0)>
                    @error('tgljt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kode Supplier</label>
                    <input type="text" name="supplier" id="supplier" class="form-control @error('supplier') is-invalid @enderror" value="{{ old('supplier', $hutang->SUPPLIER) }}" list="supplier-list" autocomplete="off" required @disabled((float) $hutang->BAYAR > 0)>
                    <datalist id="supplier-list"></datalist>
                    @error('supplier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nama Supplier</label>
                    <input type="text" id="supplier_name" class="form-control bg-light" value="{{ trim((string) optional($hutang->supplierMaster)->NAMA) }}" readonly>
                </div>
                <div class="col-md-6"><label class="form-label">Nilai DPP/Pembelian</label><input type="number" name="nilai_dpp" class="form-control" value="{{ old('nilai_dpp',$dppValue) }}" min="0.01" step="0.01" required></div>
                <div class="col-md-6">
                    <label class="form-label">Akun Hutang Usaha *</label>
                    <select name="coa_hutang_id" class="form-select" required @disabled((float) $hutang->BAYAR > 0)>
                        <option value="">Pilih akun</option>
                        @foreach($accounts as $account)<option value="{{ $account->id }}" @selected((string)old('coa_hutang_id', $payableAccountId)===(string)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Akun Pajak Masukan</label><select name="coa_pajak_id" class="form-select"><option value="">Tanpa pajak</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected((string)old('coa_pajak_id',$inputTaxAccountId)===(string)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Nilai Pajak</label><input type="number" name="nilai_pajak" class="form-control" value="{{ old('nilai_pajak',$taxValue) }}" min="0" step="0.01"></div>
                <div class="col-md-6">
                    <label class="form-label">Akun Beban/Biaya *</label>
                    <select name="coa_beban_id" class="form-select" required @disabled((float) $hutang->BAYAR > 0)>
                        <option value="">Pilih sesuai jenis pembelian</option>
                        @foreach($expenseAccounts as $account)<option value="{{ $account->id }}" @selected((string)old('coa_beban_id', $expenseAccountId)===(string)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach
                    </select>
                    <div class="form-text">Pilih akun sesuai penggunaan barang atau biaya yang dibeli.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan</label>
                    <textarea name="deskripsi" class="form-control" rows="3" @disabled((float) $hutang->BAYAR > 0)>{{ old('deskripsi', $hutang->KETERANGAN) }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary" @disabled((float) $hutang->BAYAR > 0)>Simpan Perubahan</button>
                <a href="{{ route('hutang.show', $hutang) }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supplierInput = document.getElementById('supplier');
    const supplierList = document.getElementById('supplier-list');
    const supplierName = document.getElementById('supplier_name');
    const suppliers = new Map();
    @if ($hutang->supplierMaster)
        suppliers.set(@json(strtoupper(trim((string) $hutang->SUPPLIER))), {
            supplier: @json(trim((string) $hutang->SUPPLIER)),
            nama: @json(trim((string) $hutang->supplierMaster->NAMA))
        });
    @endif

    function showSupplierName() {
        const selected = suppliers.get(supplierInput.value.trim().toUpperCase());
        supplierName.value = selected ? selected.nama : '';
    }

    if (!supplierInput || supplierInput.disabled) {
        return;
    }

    let supplierTimer = null;

    supplierInput.addEventListener('input', function() {
        const q = this.value.trim();
        showSupplierName();
        if (q.length < 2) {
            supplierList.innerHTML = '';
            return;
        }

        clearTimeout(supplierTimer);
        supplierTimer = setTimeout(function() {
            fetch("{{ route('hutang.search-supplier') }}?q=" + encodeURIComponent(q))
                .then(response => response.json())
                .then(function(data) {
                    supplierList.innerHTML = '';
                    data.forEach(function(item) {
                        item.supplier = String(item.supplier || item.value).trim();
                        item.nama = String(item.nama || '').trim();
                        suppliers.set(item.supplier.toUpperCase(), item);
                        const option = document.createElement('option');
                        option.value = item.supplier;
                        option.label = item.supplier + ' - ' + item.nama;
                        supplierList.appendChild(option);
                    });
                    showSupplierName();
                });
        }, 250);
    });
    supplierInput.addEventListener('change', showSupplierName);
});
</script>
@endsection
