@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form id="formExpedisiCreate" data-debug="transaksiExpedisiCreate" method="POST" action="{{ route('expedisi.store') }}">
        @csrf
        <div class="row mb-3">
            <div class="col">
                <h1 class="page-title mb-1">Tambah Ekspedisi</h1>
                <p class="text-muted small mb-0">Input Surat Jalan (SJ)</p>
            </div>
            <div class="col-auto">
                <button type="submit" id="btnSimpanExpedisi" data-debug="transaksiExpedisiStore" class="btn btn-success btn-sm">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('expedisi.index') }}" id="btnBatalExpedisi" data-debug="transaksiExpedisiCancel" class="btn btn-outline-secondary btn-sm">
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
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="surjal-tab" data-bs-toggle="tab" data-bs-target="#surjal" type="button" role="tab">
                            Surat Jalan
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="surjal" role="tabpanel">
                        <div class="row g-3">

                            <div class="col-md-2">
                                <label class="form-label">No SJ</label>
                                <input type="text" name="NOSJ" data-debug="transaksiExpedisi_NOSJ" class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tgl Muat <span class="text-danger">*</span></label>
                                <input type="date" name="TGLMUAT" data-debug="transaksiExpedisi_TGLMUAT" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tgl PO</label>
                                <input type="date" name="TGLPO" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">No PO</label>
                                <input type="text" name="NOPO" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">No Invoice</label>
                                <input type="text" name="INVOICE" class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tgl Invoice</label>
                                <input type="date" name="TGLINVOICE" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Kode Customer <span class="text-danger">*</span></label>
                                <select name="CUSTOMER_KODE" id="selectCustomer" data-debug="transaksiExpedisi_CUSTOMER_KODE" class="form-select form-select-sm select2-customer" required>
                                    <option value="">Pilih Customer</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->CUSTOMER }}">{{ $c->CUSTOMER }} - {{ $c->NAMACUST ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Customer</label>
                                <input type="text" name="CUSTOMER" class="form-control form-control-sm" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Kendaraan</label>
                                <select name="KENDARAAN" id="selectKendaraan" data-debug="transaksiExpedisi_KENDARAAN" class="form-select form-select-sm select2-kendaraan">
                                    <option value="">Pilih Kendaraan</option>
                                    @foreach($kendaraans as $k)
                                    <option value="{{ $k->KODE }}">{{ $k->KODE }} - {{ $k->NAMA }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Driver 1</label>
                                <select name="DRIVER" id="selectDriver1" data-debug="transaksiExpedisi_DRIVER" class="form-select form-select-sm select2-driver">
                                    <option value="">Pilih Driver</option>
                                    @foreach($drivers as $d)
                                    <option value="{{ $d->NAMA ?? $d->name }}">{{ $d->getDisplayName() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">No Kendaraan</label>
                                <input type="text" name="NAMA_KENDARAAN" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nama Driver 1</label>
                                <input type="text" name="NAMA_DRIVER" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Driver 2</label>
                                <input type="text" name="DRIVER2" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nama Driver 2</label>
                                <input type="text" name="NAMA_DRIVER2" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Jumlah (KM)</label>
                                <input type="number" step="0.001" name="JUMLAH" data-debug="transaksiExpedisi_JUMLAH" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Satuan</label>
                                <input type="text" name="UNIT" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Harga</label>
                                <input type="number" step="0.01" name="HARGA" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Disc %</label>
                                <input type="number" step="0.01" name="DISC" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Denda/Auto</label>
                                <input type="number" step="0.01" name="PPN" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="KETERANGAN" class="form-control form-control-sm" rows="2"></textarea>
                            </div>

                            <input type="hidden" name="JENIS" value="EKS">
                            <input type="hidden" name="type" value="E">
                            <input type="hidden" name="USER" value="{{ auth()->user()->name }}">
                            <input type="hidden" name="USEREDIT" value="{{ auth()->user()->name }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function initSelect2(el, url) {
        if (!el) return;
        el.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const namaField = el.getAttribute('data-nama-field');
            const kodeField = el.getAttribute('data-kode-field');
            if (namaField) {
                const namaInput = document.querySelector(`[name="${namaField}"]`);
                if (namaInput) namaInput.value = selected.dataset.nama || '';
            }
        });
    }

    const customerSelect = document.querySelector('.select2-customer');
    if (customerSelect) {
        customerSelect.addEventListener('change', function() {
            const customer = this.options[this.selectedIndex];
            document.querySelector('[name="CUSTOMER"]').value = customer.value;
        });
    }

    const kendaraanSelect = document.querySelector('.select2-kendaraan');
    if (kendaraanSelect) {
        kendaraanSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            document.querySelector('[name="NAMA_KENDARAAN"]').value = selected.dataset.nama || '';
        });
    }

    const driverSelect = document.querySelector('.select2-driver');
    if (driverSelect) {
        driverSelect.addEventListener('change', function() {
            document.querySelector('[name="NAMA_DRIVER"]').value = this.value;
        });
    }
});
</script>
@endpush
