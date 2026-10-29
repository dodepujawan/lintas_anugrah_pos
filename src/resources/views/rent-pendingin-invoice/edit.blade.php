@extends('layouts.app')

@section('title', 'Edit Invoice Rent Pendingin')

@section('content')
<div class="container-fluid" data-debug="transaksiRentPendinginInvoiceEditList">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-semibold text-warning mb-1">Transaksi / Rent Pendingin</div>
            <h1 class="page-title mb-1">Edit Rincian Invoice</h1>
            <p class="text-muted mb-0">Pilih invoice yang belum memiliki pembayaran untuk mengoreksi rincian sesuai aturan legacy.</p>
        </div>
        <a href="{{ route('rent-pendingin-invoice.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Generate Invoice</a>
    </div>

    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="rentInvoiceEditSearch" class="form-label small fw-semibold">Cari Invoice / Customer</label>
                    <input type="search" id="rentInvoiceEditSearch" name="search" class="form-control" value="{{ $search }}" placeholder="Nomor invoice atau customer">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small fw-semibold" for="rentInvoiceEditFrom">Tanggal dari</label>
                    <input type="date" id="rentInvoiceEditFrom" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small fw-semibold" for="rentInvoiceEditTo">Tanggal sampai</label>
                    <input type="date" id="rentInvoiceEditTo" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                    <a href="{{ route('rent-pendingin-invoice-gen.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr>
                    <th class="ps-3">No</th><th>Tanggal Invoice</th><th>No. Invoice</th><th>No. SJ / No. Muat</th>
                    <th>Customer</th><th class="text-end">Grand</th><th class="text-end">Bayar</th><th class="text-end">Piutang</th><th class="text-end pe-3">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="ps-3">{{ $invoices->firstItem() + $loop->index }}</td>
                            <td>{{ $inv->TGLINVOICE?->format('d/m/Y') ?? '-' }}</td>
                            <td class="font-monospace fw-semibold">{{ $inv->INVOICE }}</td>
                            <td><div class="font-monospace">{{ $inv->NOSJ }}</div><div class="small text-muted">{{ $inv->NOMUAT }}</div></td>
                            <td>{{ $inv->CUSTOMER }}</td>
                            <td class="text-end">{{ number_format((float) $inv->GRAND, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) $inv->BAYAR, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) $inv->PIUTANG, 0, ',', '.') }}</td>
                            <td class="text-end pe-3"><a href="{{ route('rent-pendingin-invoice-gen.edit', $inv->INVOICE) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square me-1"></i>Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Tidak ada invoice Rent Pendingin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection
