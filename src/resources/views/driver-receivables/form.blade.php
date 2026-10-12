@extends('layouts.app')
@php($edit = isset($row))
@section('title', $edit ? 'Edit Piutang Sopir' : 'Input Piutang Sopir')
@section('content')
<div class="row justify-content-center"><div class="col-12 col-xxl-11">
    @if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-start gap-2"><div class="small text-muted">Transaksi piutang sopir berjalan. Saat disimpan, sistem membuat jurnal otomatis sesuai akun yang dipilih.</div><div class="d-flex gap-2"><a href="{{ route('panduan.piutang_sopir') }}" class="btn btn-sm btn-outline-info text-nowrap"><i class="bi bi-book me-1"></i>Panduan</a><a href="{{ route('driver-receivables.index') }}" class="btn btn-sm btn-outline-secondary text-nowrap">Kembali</a></div></div>
        <div class="card-body p-3 p-md-4">
            <form id="driver-receivable-form" method="POST" action="{{ $edit ? route('driver-receivables.update', $row) : route('driver-receivables.store') }}">
                @csrf @if($edit) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-12 col-md-4"><label class="form-label">Nomor <span class="text-danger">*</span></label><input name="number" value="{{ old('number', $edit ? $row->number : 'PS-'.now()->format('YmdHis')) }}" class="form-control" required autofocus></div>
                    <div class="col-12 col-sm-6 col-md-4"><label class="form-label">Tanggal <span class="text-danger">*</span></label><input type="date" name="date" value="{{ old('date', $edit ? $row->date->toDateString() : now()->toDateString()) }}" class="form-control" required></div>
                    <div class="col-12 col-sm-6 col-md-4"><label class="form-label">Jatuh Tempo</label><input type="date" name="due_date" value="{{ old('due_date', $edit ? $row->due_date?->toDateString() : '') }}" class="form-control"></div>
                    @php($selectedDriverId = old('driver_id', $edit ? $row->driver_id : ''))
                    @php($selectedDriver = $drivers->firstWhere('id', (int)$selectedDriverId))
                    <div class="col-12 col-lg-6"><label class="form-label">Sopir <span class="text-danger">*</span></label><div class="input-group"><input type="hidden" id="driverId" name="driver_id" value="{{ $selectedDriverId }}" required><input id="driverLabel" class="form-control bg-white" value="{{ $selectedDriver ? $selectedDriver->code.' - '.$selectedDriver->name : '' }}" placeholder="Pilih sopir" readonly required><button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#driverSearchModal"><i class="bi bi-search me-1"></i>Cari</button></div></div>
                    <div class="col-12 col-lg-6"><label class="form-label">Jenis Tanggungan <span class="text-danger">*</span></label><select name="type" class="form-select" required>@foreach(['KERUSAKAN_KENDARAAN','KAS_BON','KEHILANGAN_BARANG','TILANG','SELISIH_SETORAN','LAINNYA'] as $t)<option value="{{ $t }}" @selected(old('type', $edit ? $row->type : '') === $t)>{{ str_replace('_', ' ', $t) }}</option>@endforeach</select></div>
                    <div class="col-12 col-md-4"><label class="form-label">Nomor Kendaraan</label><input name="vehicle_number" value="{{ old('vehicle_number', $edit ? $row->vehicle_number : '') }}" class="form-control"></div>
                    <div class="col-12 col-md-4"><label class="form-label">Referensi/BA</label><input name="reference" value="{{ old('reference', $edit ? $row->reference : '') }}" class="form-control"></div>
                    <div class="col-12 col-md-4"><label class="form-label">Nilai Tanggungan <span class="text-danger">*</span></label><input type="number" inputmode="decimal" step=".01" min=".01" name="amount" value="{{ old('amount', $edit ? $row->amount : '') }}" class="form-control" required></div>
                    @php($selectedReceivableId = old('receivable_coa_id', $edit ? $row->receivable_coa_id : $receivableId))
                    @php($selectedCounterId = old('counter_coa_id', $edit ? $row->counter_coa_id : ''))
                    @php($selectedReceivable = $accounts->firstWhere('id', (int)$selectedReceivableId))
                    @php($selectedCounter = $accounts->firstWhere('id', (int)$selectedCounterId))
                    <div class="col-12 col-lg-6"><label class="form-label">Akun Piutang Sopir <span class="text-danger">*</span></label><div class="input-group"><input type="hidden" id="receivableCoaId" name="receivable_coa_id" value="{{ $selectedReceivableId }}" required><input id="receivableCoaLabel" class="form-control bg-white" value="{{ $selectedReceivable ? $selectedReceivable->kode_akun.' - '.$selectedReceivable->nama_akun : '' }}" placeholder="Pilih akun Piutang Sopir" readonly><button type="button" class="btn btn-outline-primary open-coa-modal" data-target-id="receivableCoaId" data-target-label="receivableCoaLabel" data-modal-title="Cari Akun Piutang Sopir"><i class="bi bi-search me-1"></i>Cari</button></div></div>
                    <div class="col-12 col-lg-6"><label class="form-label">Akun Lawan/Kredit <span class="text-danger">*</span></label><div class="input-group"><input type="hidden" id="counterCoaId" name="counter_coa_id" value="{{ $selectedCounterId }}" required><input id="counterCoaLabel" class="form-control bg-white" value="{{ $selectedCounter ? $selectedCounter->kode_akun.' - '.$selectedCounter->nama_akun : '' }}" placeholder="Pilih akun lawan kredit" readonly><button type="button" class="btn btn-outline-primary open-coa-modal" data-target-id="counterCoaId" data-target-label="counterCoaLabel" data-modal-title="Cari Akun Lawan/Kredit"><i class="bi bi-search me-1"></i>Cari</button></div></div>
                    <div class="col-12"><label class="form-label">Keterangan <span class="text-danger">*</span></label><textarea name="description" rows="3" class="form-control" placeholder="Contoh: Kas bon sopir, kerusakan kendaraan, tilang, atau selisih setoran" required>{{ old('description', $edit ? $row->description : '') }}</textarea><div class="form-text">Catatan ini untuk piutang sopir berjalan dan akan tersimpan bersama jurnal otomatis.</div></div>
                </div>
                <div class="d-grid d-sm-flex gap-2 mt-4 pt-3 border-top"><button type="submit" class="btn btn-primary px-4" id="driver-receivable-submit">Simpan & Jurnal</button></div>
            </form>
        </div>
    </div>
</div></div>

<div class="modal fade" id="coaSearchModal" tabindex="-1" aria-labelledby="coaSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="coaSearchModalLabel"><i class="bi bi-search me-2"></i>Cari Akun</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <input type="search" id="coaModalSearch" class="form-control mb-3" placeholder="Ketik kode atau nama akun..." autocomplete="off">
            <div class="table-responsive coa-modal-table"><table class="table table-sm table-hover align-middle mb-0"><thead class="table-light sticky-top"><tr><th>Kode Akun</th><th>Nama Akun</th><th>Tipe</th><th></th></tr></thead><tbody id="coaModalRows">
                @foreach($accounts as $account)<tr data-search="{{ strtolower($account->kode_akun.' '.$account->nama_akun.' '.$account->tipe) }}"><td class="fw-semibold text-nowrap">{{ $account->kode_akun }}</td><td>{{ $account->nama_akun }}</td><td>{{ $account->tipe }}</td><td class="text-end"><button type="button" class="btn btn-primary btn-sm choose-coa" data-id="{{ $account->id }}" data-label="{{ $account->kode_akun }} - {{ $account->nama_akun }}">Pilih</button></td></tr>@endforeach
            </tbody></table></div><div id="coaModalEmpty" class="text-center text-muted py-4 d-none">Akun tidak ditemukan.</div>
        </div>
    </div></div>
</div>
<div class="modal fade" id="driverSearchModal" tabindex="-1" aria-labelledby="driverSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="driverSearchModalLabel"><i class="bi bi-search me-2"></i>Cari Sopir</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <input type="search" id="driverModalSearch" class="form-control mb-3" placeholder="Ketik kode atau nama sopir..." autocomplete="off">
            <div class="table-responsive driver-modal-table"><table class="table table-sm table-hover align-middle mb-0"><thead class="table-light sticky-top"><tr><th>Kode</th><th>Nama Sopir</th><th></th></tr></thead><tbody id="driverModalRows">
                @foreach($drivers as $driver)<tr data-search="{{ strtolower($driver->code.' '.$driver->name) }}"><td class="fw-semibold text-nowrap">{{ $driver->code }}</td><td>{{ $driver->name }}</td><td class="text-end"><button type="button" class="btn btn-primary btn-sm choose-driver" data-id="{{ $driver->id }}" data-label="{{ $driver->code }} - {{ $driver->name }}">Pilih</button></td></tr>@endforeach
            </tbody></table></div><div id="driverModalEmpty" class="text-center text-muted py-4 d-none">Sopir tidak ditemukan.</div>
        </div>
    </div></div>
</div>
<x-enter-as-tab form-id="driver-receivable-form" />
@endsection

@push('styles')
<style>.coa-modal-table,.driver-modal-table{max-height:55vh;overflow:auto}.coa-modal-table thead th,.driver-modal-table thead th{z-index:2}.open-coa-modal{white-space:nowrap}</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('coaSearchModal');
    const modalTitle = document.getElementById('coaSearchModalLabel');
    const search = document.getElementById('coaModalSearch');
    const rows = document.getElementById('coaModalRows');
    const empty = document.getElementById('coaModalEmpty');
    if (!modalElement || !window.bootstrap) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    let targetId = null, targetLabel = null;

    function filterRows() {
        const query = search.value.trim().toLowerCase(); let visible = 0;
        rows.querySelectorAll('tr').forEach(function (row) { const show = row.dataset.search.includes(query); row.classList.toggle('d-none', !show); if (show) visible++; });
        empty.classList.toggle('d-none', visible > 0);
    }

    document.querySelectorAll('.open-coa-modal').forEach(function (button) {
        button.addEventListener('click', function () {
            targetId = document.getElementById(button.dataset.targetId);
            targetLabel = document.getElementById(button.dataset.targetLabel);
            modalTitle.innerHTML = '<i class="bi bi-search me-2"></i>' + button.dataset.modalTitle;
            search.value = ''; filterRows(); modal.show();
        });
    });
    modalElement.addEventListener('shown.bs.modal', function () { search.focus(); });
    search.addEventListener('input', filterRows);
    rows.addEventListener('click', function (event) {
        const button = event.target.closest('.choose-coa'); if (!button || !targetId || !targetLabel) return;
        const otherId = targetId.id === 'receivableCoaId' ? document.getElementById('counterCoaId') : document.getElementById('receivableCoaId');
        if (otherId && otherId.value === button.dataset.id) { alert('Akun Piutang Sopir dan Akun Lawan/Kredit harus berbeda.'); return; }
        targetId.value = button.dataset.id; targetLabel.value = button.dataset.label; modal.hide();
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('driverSearchModal');
    const search = document.getElementById('driverModalSearch');
    const rows = document.getElementById('driverModalRows');
    const empty = document.getElementById('driverModalEmpty');
    const driverId = document.getElementById('driverId');
    const driverLabel = document.getElementById('driverLabel');
    if (!modalElement || !window.bootstrap) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    function filterRows() {
        const query = search.value.trim().toLowerCase(); let visible = 0;
        rows.querySelectorAll('tr').forEach(function (row) { const show = row.dataset.search.includes(query); row.classList.toggle('d-none', !show); if (show) visible++; });
        empty.classList.toggle('d-none', visible > 0);
    }

    modalElement.addEventListener('shown.bs.modal', function () { search.value = ''; filterRows(); search.focus(); });
    search.addEventListener('input', filterRows);
    rows.addEventListener('click', function (event) {
        const button = event.target.closest('.choose-driver'); if (!button) return;
        driverId.value = button.dataset.id; driverLabel.value = button.dataset.label; modal.hide();
    });

    document.getElementById('driver-receivable-form')?.addEventListener('submit', function () {
        const submit = document.getElementById('driver-receivable-submit');
        if (!submit) return;

        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...';
    });
});
</script>
@endpush
