@extends('layouts.app')

@section('title', 'Edit Invoice Coolroom')

@section('content')
<div class="container-fluid" data-debug="transaksiCoolroomInvoiceEditIndex">
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div><div class="text-uppercase small fw-semibold text-primary mb-1">Transaksi / Coolroom</div><h1 class="page-title mb-1">Edit Invoice Coolroom</h1><p class="text-muted mb-0">Invoice dengan pembayaran yang sudah tercatat tidak dapat diedit.</p></div>
        <a href="{{ route('coolroom-invoice.create', ['status_invoice' => 'sudah']) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
    <section class="card border-0 shadow-sm mb-3">
        <div class="card-body"><form method="GET" action="{{ route('coolroom-invoice-gen.index') }}" class="row g-2 align-items-end" data-debug="transaksiCoolroomInvoiceEditSearch">
            <div class="col-md-5"><label for="coolroomInvoiceEditSearch" class="form-label small fw-semibold">Cari Invoice / Customer</label><input type="search" id="coolroomInvoiceEditSearch" name="search" value="{{ $search }}" class="form-control" placeholder="Nomor invoice atau customer"></div>
            <div class="col-md-2"><label for="coolroomInvoiceEditFrom" class="form-label small fw-semibold">Dari</label><input type="date" id="coolroomInvoiceEditFrom" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control"></div>
            <div class="col-md-2"><label for="coolroomInvoiceEditTo" class="form-label small fw-semibold">Sampai</label><input type="date" id="coolroomInvoiceEditTo" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control"></div>
            <div class="col-auto"><button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari</button><a href="{{ route('coolroom-invoice-gen.index') }}" class="btn btn-outline-secondary">Reset</a></div>
        </form></div>
    </section>
    <section class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-3">No</th><th>No. Invoice</th><th>Tanggal</th><th>Customer</th><th class="text-end">Grand</th><th class="text-end">Bayar</th><th class="text-end">Piutang</th><th class="text-end pe-3">Aksi</th></tr></thead>
        <tbody>
            @forelse($invoices as $invoice)
                <tr><td class="ps-3">{{ $invoices->firstItem() + $loop->index }}</td><td class="font-monospace fw-semibold">{{ $invoice->INVOICE }}</td><td>{{ $invoice->TGLINVOICE?->format('d/m/Y') ?? '-' }}</td><td>{{ $invoice->CUSTOMER }}</td><td class="text-end">Rp {{ number_format((float) $invoice->GRAND, 0, ',', '.') }}</td><td class="text-end">Rp {{ number_format((float) $invoice->BAYAR, 0, ',', '.') }}</td><td class="text-end">Rp {{ number_format((float) $invoice->PIUTANG, 0, ',', '.') }}</td><td class="text-end pe-3"><a href="{{ route('coolroom-invoice-gen.edit', $invoice->INVOICE) }}" class="btn btn-sm btn-outline-warning" data-debug="transaksiCoolroomInvoiceEditOpen_{{ $invoice->INVOICE }}"><i class="bi bi-pencil-square me-1"></i>Edit</a></td></tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">Tidak ada invoice yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table></div></section>
    <div class="mt-3">{{ $invoices->links() }}</div>
</div>
@endsection
