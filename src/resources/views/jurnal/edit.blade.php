@extends('layouts.app')

@section('title', 'Edit Jurnal Umum')

@push('styles')
<style>
    .item-table-wrapper {
        min-height: 0;
        height: 100%;
        overflow-y: auto;
    }

    .journal-edit-card {
        position: fixed;
        top: 64px;
        right: 16px;
        bottom: 8px;
        left: 264px;
        z-index: 90;
        height: auto;
        min-height: 0;
        overflow: hidden;
    }

    .page-content.journal-editor-page {
        height: calc(100vh - 56px);
        min-height: 0;
        overflow: hidden;
    }

    .journal-edit-card > .card-body,
    .journal-edit-form {
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    .journal-edit-card > .card-body {
        padding: .75rem;
    }

    .journal-edit-form {
        display: flex;
        flex-direction: column;
    }

    .journal-edit-header {
        flex: 0 0 auto;
        background: #fff;
        position: relative;
        z-index: 10;
        padding: 0 .15rem;
    }

    .journal-edit-items {
        flex: 1 1 auto;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        margin-bottom: .5rem !important;
    }

    .journal-edit-items .card-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }

    .journal-edit-items table {
        table-layout: fixed;
        min-width: 820px;
    }

    .journal-edit-items .item-account-col { width: 33%; }
    .journal-edit-items .item-description-col { width: 31%; }
    .journal-edit-items .item-amount-col { width: 15%; }
    .journal-edit-items .item-action-col { width: 6%; }

    .journal-edit-items th,
    .journal-edit-items td {
        padding: .24rem .3rem;
        font-size: .66rem;
    }

    .journal-edit-items input.form-control,
    .journal-edit-items .input-group .btn {
        min-height: 29px;
        padding-top: .18rem;
        padding-bottom: .18rem;
        font-size: .66rem;
    }

    .journal-edit-items .btn-remove-row {
        width: 28px;
        height: 28px;
        padding: 0;
        font-size: .7rem;
    }

    .account-cell {
        min-width: 280px;
    }

    .sticky-action-bar {
        flex: 0 0 auto;
        z-index: 1020;
        background: #fff;
        border-top: 1px solid #dee2e6;
        padding: .4rem .25rem .2rem;
        --bs-gutter-y: .35rem;
    }

    .autosave-status {
        min-height: 1.4rem;
        color: #495057;
    }

    .table thead th {
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 5;
    }

    #keterangan {
        font-size: 11px;
    }

    @media (max-width: 767.98px) {
        .page-content.journal-editor-page { height: auto; overflow: visible; }
        .journal-edit-card { position: static; height: auto; min-height: 0; }
        .journal-edit-form { display: block; }
        .journal-edit-items { min-height: 420px; }
        .item-table-wrapper { max-height: 520px; }
    }

    @media (min-width: 768px) and (max-width: 992px) {
        .journal-edit-card { left: 16px; right: 16px; }
    }
</style>
@endpush

@section('content')
<div class="card shadow-sm journal-edit-card">
    <div class="card-body">
        <form action="{{ route('jurnal.update', $jurnal->id) }}" method="POST" id="formJurnal" class="journal-edit-form" data-draft-autosave="{{ $jurnal->status === App\Models\Jurnal::STATUS_DRAFT ? 'true' : 'false' }}" data-confirm-message="Simpan perubahan jurnal ini? Pastikan total Debit dan Kredit sudah seimbang." novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="draft_id" id="draft_id" value="{{ $jurnal->id }}">
            <input type="hidden" name="no_jurnal" value="{{ $jurnal->no_jurnal }}">

            @if ($errors->any())
            <div class="alert alert-danger journal-form-errors">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="journal-edit-header">
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1">No. Jurnal</label>
                    <input type="text" class="form-control form-control-sm" value="{{ $jurnal->no_jurnal }}" readonly>
                </div>
                <div class="col-md-2">
                    <label for="tanggal" class="form-label small mb-1">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" class="form-control form-control-sm @error('tanggal') is-invalid @enderror" id="tanggal" name="tanggal" value="{{ old('tanggal', $jurnal->tanggal->format('Y-m-d')) }}" required>
                    @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label for="referensi" class="form-label small mb-1">Referensi</label>
                    <input type="text" class="form-control form-control-sm @error('referensi') is-invalid @enderror" id="referensi" name="referensi" value="{{ old('referensi', $jurnal->referensi) }}" maxlength="50" placeholder="No. bukti, faktur, dll">
                    @error('referensi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="{{ $backToIndexUrl }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
            </div>

            <div class="mb-2">
                <label for="keterangan" class="form-label small mb-1">Keterangan</label>
                <textarea class="form-control form-control-sm @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="1" placeholder="Deskripsi transaksi secara umum">{{ old('keterangan', $jurnal->keterangan) }}</textarea>
                @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            </div>

            <div class="card border shadow-sm journal-edit-items">
                <div class="card-header py-2 d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
                    <div>
                        <h6 class="mb-0">Item Baris Jurnal</h6>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" onclick="tambahItem()">
                        <i class="bi bi-plus"></i> Tambah Baris
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive item-table-wrapper">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <colgroup>
                                <col class="item-account-col">
                                <col class="item-description-col">
                                <col class="item-amount-col">
                                <col class="item-amount-col">
                                <col class="item-action-col">
                            </colgroup>
                            <thead class="table-light">
                                <tr>
                                    <th class="w-30">Akun Posting</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Kredit</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="jurnalBody">
                                @php
                                $items = old('coa_id') ? old('coa_id') : $jurnal->items->pluck('coa_id')->toArray();
                                $descriptions = old('deskripsi_item') ? old('deskripsi_item') : $jurnal->items->pluck('deskripsi')->toArray();
                                $debits = old('debit') ? old('debit') : $jurnal->items->pluck('debit')->toArray();
                                $kredits = old('kredit') ? old('kredit') : $jurnal->items->pluck('kredit')->toArray();
                                @endphp
                                @foreach($items as $index => $coaId)
                                @php $selectedAccount = $coas->firstWhere('id', $coaId); @endphp
                                <tr>
                                    <td class="account-cell">
                                        <div class="input-group input-group-sm account-picker">
                                            <input type="search" class="form-control account-search @error('coa_id.' . $index) is-invalid @enderror" placeholder="Ketik kode/nama akun..." autocomplete="off" value="{{ optional($selectedAccount)->kode_akun ? optional($selectedAccount)->kode_akun . ' - ' . optional($selectedAccount)->nama_akun : '' }}">
                                            <button type="button" class="btn btn-outline-secondary btn-account-modal" title="Bantuan cari COA" aria-label="Buka pencarian COA"><i class="bi bi-search"></i></button>
                                            <input type="hidden" name="coa_id[]" class="coa-id" value="{{ $coaId }}">
                                            <div class="account-suggestions list-group shadow"></div>
                                        </div>
                                        @error('coa_id.' . $index)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="text" name="deskripsi_item[]" class="form-control form-control-sm" value="{{ $descriptions[$index] ?? '' }}" placeholder="Keterangan baris (opsional)">
                                    </td>
                                    <td>
                                        <input type="text" inputmode="decimal" name="debit[]" class="form-control form-control-sm text-end debit journal-money" value="{{ $debits[$index] ?? 0 }}">
                                    </td>
                                    <td>
                                        <input type="text" inputmode="decimal" name="kredit[]" class="form-control form-control-sm text-end kredit journal-money" value="{{ $kredits[$index] ?? 0 }}">
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" aria-label="Hapus baris">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row gy-3 align-items-center sticky-action-bar">
                <div class="col-md-7 d-flex flex-column flex-sm-row gap-4 align-items-sm-center">
                    <div>
                        <div class="text-secondary small">Status Saldo</div>
                        <div><span id="statusBalance" class="badge bg-secondary">Menunggu</span></div>
                    </div>
                    <div>
                        <div class="text-secondary small">Total Debit</div>
                        <div><strong id="totalDebit">0,00</strong></div>
                    </div>
                    <div>
                        <div class="text-secondary small">Total Kredit</div>
                        <div><strong id="totalKredit">0,00</strong></div>
                    </div>
                    <div class="autosave-status">
                        <span id="draftStatus">{{ $jurnal->status === App\Models\Jurnal::STATUS_DRAFT ? 'Draft belum disimpan.' : 'Mode edit jurnal Posted.' }}</span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    <button type="button" class="btn btn-outline-danger me-2" onclick="if (confirm('Hapus seluruh jurnal {{ $jurnal->no_jurnal }}? Semua item jurnal akan ikut dihapus dan tindakan ini tidak dapat dibatalkan.')) document.getElementById('deleteJurnalForm').submit();">
                        <i class="bi bi-trash"></i> Hapus Jurnal
                    </button>
                    <button type="button" class="btn btn-outline-secondary me-2 {{ $jurnal->status === App\Models\Jurnal::STATUS_DRAFT ? '' : 'd-none' }}" id="saveDraftButton" onclick="saveDraftViaAjax(true)">
                        <i class="bi bi-save2"></i> Simpan Draft
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitButton">
                        <i class="bi bi-save"></i> Update Jurnal
                    </button>
                </div>
            </div>
        </form>
        <form action="{{ route('jurnal.destroy', $jurnal->id) }}" method="POST" id="deleteJurnalForm" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>


<script type="application/json" id="accountOptionsJson">
    @json($coas)
</script>

@include('jurnal._account-modal')
@include('jurnal._form-scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const page = document.querySelector('.page-content');
        if (page) page.classList.add('journal-editor-page');
        window.scrollTo(0, 0);
    });
</script>

@endsection
