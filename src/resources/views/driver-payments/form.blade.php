@extends('layouts.app')
@php($edit = isset($payment))
@php($selectedReceivableId = (int) old('driver_receivable_id', $edit ? $payment->driver_receivable_id : 0))
@php($selectedReceivable = $receivables->firstWhere('id', $selectedReceivableId))
@section('title', $edit ? 'Edit Pelunasan Piutang Sopir' : 'Input Pelunasan Piutang Sopir')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        @if($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h1 class="h5 mb-1">{{ $edit ? 'Edit' : 'Input' }} Pelunasan Piutang Sopir</h1>
                    <div class="small text-muted">Enter berpindah field. Field terakhir menyimpan dan memperbarui jurnal.</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('panduan.piutang_sopir') }}" class="btn btn-sm btn-outline-info text-nowrap"><i class="bi bi-book me-1"></i>Panduan</a>
                    <a href="{{ route('driver-payments.index') }}" class="btn btn-sm btn-outline-secondary text-nowrap">Kembali</a>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <form id="driver-payment-form" method="POST" action="{{ $edit ? route('driver-payments.update', $payment) : route('driver-payments.store') }}">
                    @csrf
                    @if($edit) @method('PUT') @endif
                    @unless($edit)
                        <input type="hidden" name="_payment_token" value="{{ old('_payment_token', $paymentToken ?? '') }}">
                    @endunless
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Piutang Sopir <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    id="driverReceivableLabel"
                                    class="form-control bg-white"
                                    value="{{ $selectedReceivable ? $selectedReceivable->number.' - '.$selectedReceivable->driver->name.' - Tersedia '.number_format((float)$selectedReceivable->balance + ($edit && $payment->driver_receivable_id == $selectedReceivable->id ? (float)$payment->amount : 0), 2, ',', '.') : '' }}"
                                    placeholder="Pilih piutang sopir"
                                    readonly
                                    autofocus>
                                <input id="driverReceivableId" type="hidden" name="driver_receivable_id" value="{{ $selectedReceivableId ?: '' }}" required>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#receivableSearchModal" title="Cari piutang sopir">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="date" value="{{ old('date', $edit ? $payment->date->toDateString() : now()->toDateString()) }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Nilai Bayar <span class="text-danger">*</span></label>
                            <input type="number" inputmode="decimal" name="amount" value="{{ old('amount', $edit ? $payment->amount : '') }}" step=".01" min=".01" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Metode</label>
                            <select name="method" class="form-select">
                                @foreach(['CASH','TRANSFER','POTONG_GAJI','POTONG_UANG_JALAN'] as $m)
                                    <option value="{{ $m }}" @selected(old('method', $edit ? $payment->method : '') === $m)>{{ str_replace('_', ' ', $m) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label">Akun Pembayaran/Debit <span class="text-danger">*</span></label>
                            <select name="payment_coa_id" class="form-select" required>
                                @foreach($accounts as $a)
                                    <option value="{{ $a->id }}" @selected(old('payment_coa_id', $edit ? $payment->payment_coa_id : $cashId) == $a->id)>{{ $a->kode_akun }} - {{ $a->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Referensi</label>
                            <input name="reference" value="{{ old('reference', $edit ? $payment->reference : '') }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Keterangan</label>
                            <input name="description" value="{{ old('description', $edit ? $payment->description : '') }}" class="form-control">
                        </div>
                    </div>
                    <div class="d-grid d-sm-flex gap-2 mt-4 pt-3 border-top">
                        <button class="btn btn-primary px-4" id="driver-payment-submit">{{ $edit ? 'Update' : 'Simpan' }} & Jurnal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receivableSearchModal" tabindex="-1" aria-labelledby="receivableSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receivableSearchModalLabel">Cari Piutang Sopir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input id="receivableModalSearch" type="search" class="form-control mb-3" placeholder="Cari nomor, kode, nama sopir, kendaraan, atau referensi">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nomor</th>
                                <th>Sopir</th>
                                <th>Kendaraan / Referensi</th>
                                <th class="text-end">Tersedia</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="receivableModalRows">
                            @foreach($receivables as $r)
                                @php($available = (float)$r->balance + ($edit && $payment->driver_receivable_id == $r->id ? (float)$payment->amount : 0))
                                @php($label = $r->number.' - '.$r->driver->name.' - Tersedia '.number_format($available, 2, ',', '.'))
                                <tr data-search="{{ strtolower($r->number.' '.$r->driver->code.' '.$r->driver->name.' '.$r->vehicle_number.' '.$r->reference) }}">
                                    <td class="fw-semibold text-nowrap">{{ $r->number }}</td>
                                    <td><strong>{{ $r->driver->code }}</strong><div class="small text-muted">{{ $r->driver->name }}</div></td>
                                    <td><div>{{ $r->vehicle_number ?: '-' }}</div><div class="small text-muted">{{ $r->reference ?: '-' }}</div></td>
                                    <td class="text-end text-nowrap">{{ number_format($available, 2, ',', '.') }}</td>
                                    <td class="text-end"><button type="button" class="btn btn-sm btn-primary choose-receivable" data-id="{{ $r->id }}" data-label="{{ $label }}">Pilih</button></td>
                                </tr>
                            @endforeach
                            @if($receivables->isEmpty())
                                <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada piutang sopir yang tersedia.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div id="receivableModalEmpty" class="text-center text-muted py-3 d-none">Data tidak ditemukan.</div>
            </div>
        </div>
    </div>
</div>

<x-enter-as-tab form-id="driver-payment-form" />
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('receivableSearchModal');
    const search = document.getElementById('receivableModalSearch');
    const rows = document.getElementById('receivableModalRows');
    const empty = document.getElementById('receivableModalEmpty');
    const idInput = document.getElementById('driverReceivableId');
    const labelInput = document.getElementById('driverReceivableLabel');

    if (!modal || !search || !rows) return;

    function filterRows() {
        const query = search.value.trim().toLowerCase();
        let visible = 0;

        rows.querySelectorAll('tr[data-search]').forEach(function (row) {
            const show = row.dataset.search.includes(query);
            row.classList.toggle('d-none', !show);
            if (show) visible++;
        });

        empty.classList.toggle('d-none', visible > 0);
    }

    modal.addEventListener('shown.bs.modal', function () {
        search.value = '';
        filterRows();
        search.focus();
    });

    search.addEventListener('input', filterRows);

    rows.addEventListener('click', function (event) {
        const button = event.target.closest('.choose-receivable');
        if (!button) return;

        idInput.value = button.dataset.id;
        labelInput.value = button.dataset.label;
        bootstrap.Modal.getInstance(modal)?.hide();
    });

    document.getElementById('driver-payment-form')?.addEventListener('submit', function () {
        const submit = document.getElementById('driver-payment-submit');
        if (!submit) return;

        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...';
    });
});
</script>
@endpush
