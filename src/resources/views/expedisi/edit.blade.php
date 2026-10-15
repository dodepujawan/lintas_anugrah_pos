@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiExpedisiEdit">
    <form id="formExpedisiEdit" data-debug="transaksiExpedisiEditForm" method="POST" action="{{ route('expedisi.update', $expedisi) }}">
        @csrf @method('PUT')
        <div class="row mb-3">
            <div class="col">
                <h1 class="page-title mb-1">Edit Ekspedisi</h1>
                <p class="text-muted small mb-0">NOSJ: {{ $expedisi->NOSJ }}</p>
            </div>
            <div class="col-auto">
                <button type="submit" id="btnSimpanExpedisiEdit" data-debug="transaksiExpedisiEditSave" class="btn btn-success btn-sm">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('expedisi.index') }}" id="btnBatalExpedisiEdit" data-debug="transaksiExpedisiEditCancel" class="btn btn-outline-secondary btn-sm">
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

        <div class="card">
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-2">
                        <label class="form-label">No SJ</label>
                        <input type="text" name="NOSJ" class="form-control form-control-sm" value="{{ $expedisi->NOSJ }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tgl Muat <span class="text-danger">*</span></label>
                        <input type="date" name="TGLMUAT" class="form-control form-control-sm" value="{{ $expedisi->TGLMUAT?->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tgl PO</label>
                        <input type="date" name="TGLPO" class="form-control form-control-sm" value="{{ $expedisi->TGLPO?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">No PO</label>
                        <input type="text" name="NOPO" class="form-control form-control-sm" value="{{ $expedisi->NOPO }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">No Invoice</label>
                        <input type="text" name="INVOICE" class="form-control form-control-sm" value="{{ $expedisi->INVOICE }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tgl Invoice</label>
                        <input type="date" name="TGLINVOICE" class="form-control form-control-sm" value="{{ $expedisi->TGLINVOICE?->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kode Customer <span class="text-danger">*</span></label>
                        <select name="CUSTOMER_KODE" class="form-select form-select-sm select2-customer" required>
                            <option value="">Pilih Customer</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->CUSTOMER }}" {{ $expedisi->CUSTOMER_KODE == $c->CUSTOMER ? 'selected' : '' }}
                                    data-nama="{{ $c->NAMACUST ?? '' }}">
                                {{ $c->CUSTOMER }} - {{ $c->NAMACUST ?? '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Customer</label>
                        <input type="text" name="CUSTOMER" class="form-control form-control-sm" value="{{ $expedisi->CUSTOMER }}" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Kendaraan</label>
                        <select name="KENDARAAN" class="form-select form-select-sm select2-kendaraan">
                            <option value="">Pilih Kendaraan</option>
                            @foreach($kendaraans as $k)
                            <option value="{{ $k->KODE }}" {{ $expedisi->KENDARAAN == $k->KODE ? 'selected' : '' }}
                                    data-nama="{{ $k->NAMA }}">
                                {{ $k->KODE }} - {{ $k->NAMA }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Driver 1</label>
                        <select name="DRIVER" class="form-select form-select-sm select2-driver">
                            <option value="">Pilih Driver</option>
                            @foreach($drivers as $d)
                            <option value="{{ $d->NAMA ?? $d->name }}" {{ $expedisi->DRIVER == ($d->NAMA ?? $d->name) ? 'selected' : '' }}>
                                {{ $d->display_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">No Kendaraan</label>
                        <input type="text" name="NAMA_KENDARAAN" class="form-control form-control-sm" value="{{ $expedisi->NAMA_KENDARAAN }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nama Driver 1</label>
                        <input type="text" name="NAMA_DRIVER" class="form-control form-control-sm" value="{{ $expedisi->NAMA_DRIVER }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Driver 2</label>
                        <input type="text" name="DRIVER2" class="form-control form-control-sm" value="{{ $expedisi->DRIVER2 }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nama Driver 2</label>
                        <input type="text" name="NAMA_DRIVER2" class="form-control form-control-sm" value="{{ $expedisi->NAMA_DRIVER2 }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Jumlah (KM)</label>
                        <input type="number" step="0.001" name="JUMLAH" class="form-control form-control-sm" value="{{ $expedisi->JUMLAH }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="UNIT" class="form-control form-control-sm" value="{{ $expedisi->UNIT }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Harga</label>
                        <input type="number" step="0.01" name="HARGA" class="form-control form-control-sm" value="{{ $expedisi->HARGA }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Disc %</label>
                        <input type="number" step="0.01" name="DISC" class="form-control form-control-sm" value="{{ $expedisi->DISC }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">PPN</label>
                        <input type="number" step="0.01" name="PPN" class="form-control form-control-sm" value="{{ $expedisi->PPN }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Keterangan</label>
                        <textarea name="KETERANGAN" class="form-control form-control-sm" rows="2">{{ $expedisi->KETERANGAN }}</textarea>
                    </div>

                    <input type="hidden" name="USEREDIT" value="{{ auth()->user()->name }}">
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.querySelector('.select2-customer');
    if (customerSelect) {
        customerSelect.addEventListener('change', function() {
            document.querySelector('[name="CUSTOMER"]').value = this.value;
        });
    }
    const kendaraanSelect = document.querySelector('.select2-kendaraan');
    if (kendaraanSelect) {
        kendaraanSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            document.querySelector('[name="NAMA_KENDARAAN"]').value = selected.dataset.nama || '';
        });
    }
});
</script>
@endpush
