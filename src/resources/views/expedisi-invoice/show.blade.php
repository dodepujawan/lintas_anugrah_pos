@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiExpedisiInvoiceShow">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Detail Invoice</h1>
            <p class="text-muted small mb-0">No. Invoice: <span class="font-monospace">{{ $invoice->NOFAKTUR }}</span></p>
        </div>
        <div class="col-auto">
            <a href="{{ route('expedisi-invoice.pdfGabungInvoice', $invoice->NOFAKTUR) }}" id="btnPrintInvoiceDetail" data-debug="transaksiExpedisiInvoicePrint" class="btn btn-primary btn-sm" target="_blank">
                <i class="bi bi-printer"></i> Print
            </a>
            <a href="{{ route('expedisi-invoice.index') }}" id="btnKembaliInvoiceDetail" data-debug="transaksiExpedisiInvoiceBack" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Invoice</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>No. Invoice</th><td class="font-monospace">{{ $invoice->NOFAKTUR }}</td></tr>
                        <tr><th>Tgl Invoice</th><td>{{ $invoice->TGLFAKTUR?->format('d/m/Y') }}</td></tr>
                        <tr><th>Tgl Jatuh Tempo</th><td>{{ $invoice->TGLJT?->format('d/m/Y') ?? '-' }}</td></tr>
                        <tr><th>Customer</th><td>{{ $invoice->CUSTOMER }}</td></tr>
                        <tr><th>Sales</th><td>{{ $invoice->SALES ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Nilai & Saldo</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>Piutang</th><td class="text-end">{{ number_format((float) $invoice->PIUTANG, 0, ',', '.') }}</td></tr>
                        <tr><th>Bayar</th><td class="text-end">{{ number_format((float) $invoice->BAYAR, 0, ',', '.') }}</td></tr>
                        <tr><th>Discount</th><td class="text-end">{{ number_format((float) $invoice->DISCOUNT, 0, ',', '.') }}</td></tr>
                        <tr><th>Saldo</th><td class="text-end fw-bold">{{ number_format((float) $invoice->SALDO, 0, ',', '.') }}</td></tr>
                        <tr><th>Status</th><td>{{ $invoice->STATUS }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
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
