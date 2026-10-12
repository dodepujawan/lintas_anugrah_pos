@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Invoice Expedisi</h1>
            <p class="text-muted small mb-0">Daftar invoice yang sudah digenerate</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('expedisi-invoice-gen.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil-square"></i> Edit Invoice
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari No. Invoice / Customer..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('expedisi-invoice.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>No. Invoice</th>
                        <th>Tgl Invoice</th>
                        <th>Customer</th>
                        <th>Sales</th>
                        <th class="text-end">Piutang</th>
                        <th class="text-end">Bayar</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td>{{ $invoices->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $inv->NOFAKTUR }}</td>
                        <td>{{ $inv->TGLFAKTUR?->format('d/m/Y') }}</td>
                        <td>{{ $inv->CUSTOMER }}</td>
                        <td>{{ $inv->SALES }}</td>
                        <td class="text-end">{{ number_format((float) $inv->PIUTANG, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $inv->BAYAR, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $inv->SALDO, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a href="{{ route('expedisi-invoice.show', $inv->NOFAKTUR) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('expedisi-invoice.pdfGabungInvoice', $inv->NOFAKTUR) }}" class="btn btn-sm btn-outline-secondary" title="Print" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data invoice.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
