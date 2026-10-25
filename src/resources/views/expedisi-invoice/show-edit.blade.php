@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiExpedisiInvoiceShowEdit">
    <form id="formEditInvoice" data-debug="transaksiExpedisiInvoiceEditForm" method="POST" action="{{ route('expedisi-invoice-gen.update', $invoice->NOFAKTUR) }}">
        @csrf @method('PUT')
        <div class="row mb-3">
            <div class="col">
                <h1 class="page-title mb-1">Edit Invoice</h1>
                <p class="text-muted small mb-0">No. Invoice: <span class="font-monospace">{{ $invoice->NOFAKTUR }}</span></p>
            </div>
            <div class="col-auto">
                <button type="submit" id="btnSimpanEditInvoice" data-debug="transaksiExpedisiInvoiceEditSave" class="btn btn-success btn-sm">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('expedisi-invoice-gen.index') }}" id="btnBatalEditInvoice" data-debug="transaksiExpedisiInvoiceEditCancel" class="btn btn-outline-secondary btn-sm">
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
        @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
        @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Informasi Invoice</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">No. Invoice</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $invoice->NOFAKTUR }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tgl Invoice</label>
                        <input type="date" name="TGLFAKTUR" data-debug="transaksiExpedisiInvoiceEdit_TGLFAKTUR" class="form-control form-control-sm" value="{{ old('TGLFAKTUR', $invoice->TGLFAKTUR?->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tgl Jatuh Tempo</label>
                        <input type="date" name="TGLJT" data-debug="transaksiExpedisiInvoiceEdit_TGLJT" class="form-control form-control-sm" value="{{ old('TGLJT', $invoice->TGLJT?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <input type="text" data-debug="transaksiExpedisiInvoiceEdit_CUSTOMER" class="form-control form-control-sm" value="{{ $invoice->CUSTOMER }}" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Keterangan</label>
                        <textarea name="KETERANGAN" data-debug="transaksiExpedisiInvoiceEdit_KETERANGAN" class="form-control form-control-sm" rows="2">{{ old('KETERANGAN', $invoice->KETERANGAN) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">Detail Surat Jalan</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
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
                        @forelse($details as $d)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-monospace">{{ $d->NOSJ }}</td>
                            <td>{{ $d->TGLMUAT?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $d->CUSTOMER }}</td>
                            <td class="text-end">{{ number_format((float) $d->JUMLAH, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) $d->HARGA, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) $d->TOTAL, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) $d->GRAND, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-3 text-muted">Tidak ada detail SJ.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
