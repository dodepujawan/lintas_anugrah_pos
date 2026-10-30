@extends('layouts.app')

@section('title', 'Detail Invoice Coolroom')

@section('content')
<div class="container-fluid" data-debug="transaksiCoolroomInvoiceShow">
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-semibold text-primary mb-1">Preview cetak</div>
            <h1 class="page-title mb-1">Preview Invoice Coolroom</h1>
            <p class="text-muted mb-0">No. Invoice <span class="font-monospace fw-semibold">{{ $invoice->INVOICE }}</span></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if (!$arh || (float) $arh->BAYAR <= 0)
                <a href="{{ route('coolroom-invoice-gen.edit', $invoice->INVOICE) }}" class="btn btn-outline-warning" data-debug="transaksiCoolroomInvoiceShowEdit"><i class="bi bi-pencil-square me-1"></i>Edit Invoice</a>
            @endif
            <button type="button" id="coolroomInvoiceDirectPrint" class="btn btn-primary" data-debug="transaksiCoolroomInvoiceShowDirectPrint"><i class="bi bi-printer me-1"></i>Cetak Langsung</button>
            <a href="{{ route('coolroom-invoice.pdf', $invoice->INVOICE) }}" class="btn btn-outline-primary" target="_blank" rel="noopener" data-debug="transaksiCoolroomInvoiceShowPdf"><i class="bi bi-file-earmark-pdf me-1"></i>Cetak PDF</a>
            <a href="{{ route('coolroom-invoice.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
    </div>
    <div id="coolroomInvoicePrintStatus" class="small mb-3" role="status" aria-live="polite"></div>

    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h2 class="h5 mb-0">Informasi Invoice</h2></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-sm-6"><div class="small text-muted">No. Invoice</div><div class="font-monospace fw-semibold">{{ $invoice->INVOICE }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Tanggal Invoice</div><div class="fw-semibold">{{ $invoice->TGLINVOICE?->format('d/m/Y') ?? '-' }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Customer</div><div class="fw-semibold">{{ $invoice->CUSTOMER }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Kode Customer</div><div class="font-monospace">{{ $invoice->CUSTOMER_KODE ?: '-' }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Tanggal Jatuh Tempo</div><div class="fw-semibold">{{ ($arh?->TGLJT ?? $invoice->TGLJT)?->format('d/m/Y') ?? '-' }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">TOP</div><div>{{ (int) ($invoice->TOP ?? 0) }} hari</div></div>
                    <div class="col-12"><div class="small text-muted">No. Surat Jalan</div><div class="font-monospace">{{ $details->pluck('NOSJ')->filter()->implode(', ') ?: '-' }}</div></div>
                </div></div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h2 class="h5 mb-0">Ringkasan Nilai</h2></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-7 fw-normal text-muted">Subtotal</dt><dd class="col-5 text-end">Rp {{ number_format((float) $details->sum('SUBTOTAL'), 0, ',', '.') }}</dd>
                        <dt class="col-7 fw-normal text-muted">Diskon</dt><dd class="col-5 text-end">- Rp {{ number_format((float) $details->sum('NDISC'), 0, ',', '.') }}</dd>
                        <dt class="col-7 fw-normal text-muted">DPP</dt><dd class="col-5 text-end">Rp {{ number_format((float) $details->sum('DPP'), 0, ',', '.') }}</dd>
                        <dt class="col-7 fw-normal text-muted">PPN</dt><dd class="col-5 text-end">Rp {{ number_format((float) $details->sum('NPPN'), 0, ',', '.') }}</dd>
                    </dl>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center"><span class="fw-semibold">Grand Total</span><strong class="fs-5">Rp {{ number_format((float) $details->sum('GRAND'), 0, ',', '.') }}</strong></div>
                    <div class="d-flex justify-content-between mt-3 text-muted"><span>Bayar</span><span>Rp {{ number_format((float) $details->sum('BAYAR'), 0, ',', '.') }}</span></div>
                    <div class="d-flex justify-content-between border-top mt-2 pt-2"><span class="fw-semibold">Sisa Piutang</span><strong>Rp {{ number_format((float) $details->sum('PIUTANG'), 0, ',', '.') }}</strong></div>
                </div>
            </section>
        </div>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h2 class="h5 mb-0">Rincian Surat Jalan</h2></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-3">No</th><th>No. SJ</th><th>Tgl SJ</th><th>Customer</th><th class="text-end">Jumlah</th><th class="text-end">Harga</th><th class="text-end pe-3">Grand</th></tr></thead>
            <tbody>
                @foreach($details as $row)
                    <tr><td class="ps-3">{{ $loop->iteration }}</td><td class="font-monospace">{{ $row->NOSJ }}</td><td>{{ $row->TGLSJ?->format('d/m/Y') ?? '-' }}</td><td>{{ $row->CUSTOMER }}</td><td class="text-end">{{ number_format((float) $row->JUMLAH, 3, ',', '.') }} {{ $row->UNIT ?: 'KG' }}</td><td class="text-end">Rp {{ number_format((float) $row->HARGA, 0, ',', '.') }}</td><td class="text-end pe-3 fw-semibold">Rp {{ number_format((float) $row->GRAND, 0, ',', '.') }}</td></tr>
                @endforeach
            </tbody>
        </table></div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const button = document.getElementById('coolroomInvoiceDirectPrint');
    const status = document.getElementById('coolroomInvoicePrintStatus');
    const invoiceNo = @json($invoice->INVOICE);
    const textUrl = @json(route('coolroom-invoice.text', ['noInvoice' => $invoice->INVOICE]));
    const originalContent = button.innerHTML;

    function showStatus(message, success) {
        status.textContent = message;
        status.className = `small mb-3 ${success ? 'text-success' : 'text-danger'}`;
    }

    button.addEventListener('click', async () => {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim ke printer...';
        showStatus(`Menyiapkan invoice ${invoiceNo} untuk direct print...`, true);
        try {
            const dataResponse = await fetch(textUrl, {headers: {Accept: 'application/json'}});
            const payload = await dataResponse.json();
            if (!dataResponse.ok || !payload.text) throw new Error(payload.message || 'Data teks invoice gagal disiapkan.');

            const printResponse = await fetch('http://localhost:3000/print-text', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({text: payload.text}),
            });
            const responseText = await printResponse.text();
            let result;
            try { result = responseText ? JSON.parse(responseText) : {}; } catch { result = {message: responseText}; }
            const message = String(result.message || result.error || 'Pekerjaan cetak dikirim ke print bridge.');
            const bridgeStatus = String(result.status ?? '').toLowerCase();
            const failed = !printResponse.ok || result.success === false || result.status === false || result.ok === false
                || result.printerFound === false || ['error', 'failed', 'fail'].includes(bridgeStatus)
                || /no printer|printer.*not found|pilih printer/i.test(message);
            if (failed) throw new Error(message || `Print bridge gagal (HTTP ${printResponse.status}).`);
            showStatus(message, true);
        } catch (error) {
            showStatus(`${error.message} Cetak PDF tetap tersedia.`, false);
        } finally {
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    });
})();
</script>
@endpush
