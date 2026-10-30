@extends('layouts.app')

@section('title', 'Invoice Coolroom')

@section('content')
<div class="container-fluid" data-debug="transaksiCoolroomInvoiceIndex">
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-semibold text-primary mb-1">Transaksi / Coolroom</div>
            <h1 class="page-title mb-1">Invoice Coolroom</h1>
            <p class="text-muted mb-0">Daftar invoice dari surat jalan Coolroom, termasuk invoice yang sudah lunas.</p>
        </div>
        <a href="{{ route('coolroom-invoice.create', ['status_invoice' => 'belum']) }}" class="btn btn-primary" data-debug="transaksiCoolroomInvoiceIndexCreate">
            <i class="bi bi-plus-lg me-1"></i>Generate Invoice
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-muted">Jumlah Invoice</div><div class="fs-4 fw-bold">{{ number_format((int) ($stats->invoice_count ?? 0)) }}</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-muted">Grand Total</div><div class="fs-5 fw-bold">Rp {{ number_format((float) ($stats->grand_total ?? 0), 0, ',', '.') }}</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-muted">Total Dibayar</div><div class="fs-5 fw-bold text-success">Rp {{ number_format((float) ($stats->paid_total ?? 0), 0, ',', '.') }}</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-muted">Sisa Piutang</div><div class="fs-5 fw-bold text-warning-emphasis">Rp {{ number_format((float) ($stats->receivable_total ?? 0), 0, ',', '.') }}</div></div></div></div>
    </div>

    <section class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('coolroom-invoice.index') }}" class="row g-2 align-items-end" data-debug="transaksiCoolroomInvoiceIndexSearch">
                <div class="col-lg-6 col-md-8">
                    <label for="coolroomInvoiceSearch" class="form-label small fw-semibold">Cari invoice atau customer</label>
                    <input type="search" id="coolroomInvoiceSearch" name="search" class="form-control" value="{{ $search }}" placeholder="Nomor invoice, kode, atau nama customer">
                </div>
                <div class="col-auto"><button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button><a href="{{ route('coolroom-invoice.index') }}" class="btn btn-outline-secondary">Reset</a></div>
                <div class="col-md text-md-end small text-muted">{{ $invoices->total() }} invoice tercatat</div>
            </form>
        </div>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">No</th><th>No. Invoice</th><th>Tanggal</th><th>Customer</th><th class="text-end">Grand Total</th><th class="text-end">Dibayar</th><th class="text-end">Piutang</th><th>Status</th><th class="text-end pe-3">Aksi</th></tr></thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="ps-3">{{ $invoices->firstItem() + $loop->index }}</td>
                            <td class="font-monospace fw-semibold">{{ $invoice->INVOICE }}</td>
                            <td>{{ $invoice->TGLINVOICE?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $invoice->CUSTOMER }}</td>
                            <td class="text-end">Rp {{ number_format((float) $invoice->GRAND, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format((float) $invoice->BAYAR, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format((float) $invoice->PIUTANG, 0, ',', '.') }}</td>
                            <td>@if ((float) $invoice->PIUTANG <= 0)<span class="badge text-bg-success">Lunas</span>@else<span class="badge text-bg-warning">Belum lunas</span>@endif</td>
                            <td class="text-end text-nowrap pe-3">
                                <a href="{{ route('coolroom-invoice.show', $invoice->INVOICE) }}" class="btn btn-sm btn-outline-primary" data-debug="transaksiCoolroomInvoiceIndexDetail_{{ $invoice->INVOICE }}" title="Detail Invoice"><i class="bi bi-eye"></i><span class="visually-hidden">Detail</span></a>
                                <a href="{{ route('coolroom-invoice.pdf', $invoice->INVOICE) }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" data-debug="transaksiCoolroomInvoiceIndexPdf_{{ $invoice->INVOICE }}" title="Cetak PDF"><i class="bi bi-file-earmark-pdf"></i><span class="visually-hidden">PDF</span></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-receipt fs-2 d-block mb-2"></i>Belum ada invoice Coolroom yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-3">{{ $invoices->links() }}</div>
</div>
@endsection
