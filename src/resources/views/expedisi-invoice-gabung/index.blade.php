@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form id="formGabungInvoice" data-debug="transaksiExpedisiInvoiceGabung" method="POST" action="{{ route('expedisi-invoice.store') }}">
        @csrf
        <div class="row mb-3">
            <div class="col">
                <h1 class="page-title mb-1">Gabung Invoice Expedisi</h1>
                <p class="text-muted small mb-0">Pilih Surat Jalan untuk digabung menjadi satu invoice</p>
            </div>
            <div class="col-auto">
                <button type="submit" id="btnGabungInvoice" data-debug="transaksiExpedisiInvoiceGabungSubmit" class="btn btn-success btn-sm" disabled>
                    <i class="bi bi-save"></i> Simpan Invoice
                </button>
                <a href="{{ route('expedisi-invoice.index') }}" id="btnKembaliInvoiceGabung" data-debug="transaksiExpedisiInvoiceGabungBack" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
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

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Form Invoice</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">No Invoice <span class="text-danger">*</span></label>
                        <input type="text" name="NOFAKTUR" data-debug="transaksiExpedisiInvoiceGabung_NOFAKTUR" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tgl Invoice <span class="text-danger">*</span></label>
                        <input type="date" name="TGLFAKTUR" data-debug="transaksiExpedisiInvoiceGabung_TGLFAKTUR" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tgl Jatuh Tempo</label>
                        <input type="date" name="TGLJT" data-debug="transaksiExpedisiInvoiceGabung_TGLJT" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <input type="text" name="CUSTOMER" data-debug="transaksiExpedisiInvoiceGabung_CUSTOMER" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="KETERANGAN" data-debug="transaksiExpedisiInvoiceGabung_KETERANGAN" class="form-control form-control-sm">
                    </div>
                    <input type="hidden" name="selected_sj" id="selectedSjInput" data-debug="transaksiExpedisiInvoiceGabung_selectedSJ">
                </div>
            </div>
        </div>
    </form>

    <form method="GET" id="formFilterGabung" data-debug="transaksiExpedisiInvoiceGabungFilter" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari NOSJ / Customer..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('expedisi-invoice.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:3%"><input type="checkbox" id="selectAllSj" data-debug="transaksiExpedisiInvoiceGabungSelectAll"></th>
                        <th>No</th>
                        <th>NOSJ</th>
                        <th>Tgl Muat</th>
                        <th>Customer</th>
                        <th class="text-end">JUMLAH</th>
                        <th class="text-end">HARGA</th>
                        <th class="text-end">TOTAL</th>
                        <th class="text-end">GRAND</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expedisis as $e)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="sj_checkbox" value="{{ $e->NOSJ }}"
                                   data-customer="{{ $e->CUSTOMER }}"
                                   class="sj-checkbox"
                                   data-debug="transaksiExpedisiInvoiceGabungCheckbox_{{ $e->NOSJ }}">
                        </td>
                        <td>{{ $expedisis->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $e->NOSJ }}</td>
                        <td>{{ $e->TGLMUAT?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $e->CUSTOMER }}</td>
                        <td class="text-end">{{ number_format((float) $e->JUMLAH, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $e->HARGA, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $e->TOTAL, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $e->GRAND, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data SJ.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $expedisis->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.sj-checkbox');
    const submitBtn = document.getElementById('btnGabungInvoice');
    const selectedInput = document.getElementById('selectedSjInput');
    const customerInput = document.querySelector('[name="CUSTOMER"]');
    const selectAll = document.getElementById('selectAllSj');

    function updateSubmitState() {
        const checked = document.querySelectorAll('.sj-checkbox:checked');
        submitBtn.disabled = checked.length === 0;
        const values = Array.from(checked).map(cb => cb.value);
        selectedInput.value = JSON.stringify(values);
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked && customerInput) {
                customerInput.value = this.dataset.customer;
            }
            updateSubmitState();
        });
    });

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSubmitState();
        });
    }

    updateSubmitState();
});
</script>
@endpush
