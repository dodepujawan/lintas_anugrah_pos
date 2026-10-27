@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiExpedisiInvoiceShow">
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Detail Invoice</h1>
            <p class="text-muted small mb-0">No. Invoice: <span class="font-monospace">{{ $invoice->NOFAKTUR }}</span></p>
        </div>
        <div class="col-auto">
            <button type="button" id="btnDirectPrintInvoice" data-debug="transaksiExpedisiInvoiceDirectPrint" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Cetak Langsung
            </button>
            <a href="{{ route('expedisi-invoice.pdfGabungInvoice', $invoice->NOFAKTUR) }}" id="btnPrintInvoiceDetail" data-debug="transaksiExpedisiInvoicePrintPdf" class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="bi bi-file-pdf"></i> Cetak PDF
            </a>
            <a href="{{ route('expedisi-invoice.index') }}" id="btnKembaliInvoiceDetail" data-debug="transaksiExpedisiInvoiceBack" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div id="directPrintStatus" class="small mb-3" role="status" aria-live="polite"></div>

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
                        <tr><th>Sisa Piutang</th><td class="text-end fw-bold">{{ number_format(max(0, (float) $invoice->SALDO - (float) $invoice->BAYAR), 0, ',', '.') }}</td></tr>
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

@push('scripts')
<script>
(() => {
    const invoiceNo = @json($invoice->NOFAKTUR);
    const textUrlTemplate = @json(route('expedisi-invoice.text', ['noInvoice' => '__INVOICE__']));
    const autoPrintInvoice = @json($autoPrintInvoice);
    const printButton = document.getElementById('btnDirectPrintInvoice');
    const printStatus = document.getElementById('directPrintStatus');
    const originalButtonContent = printButton.innerHTML;

    function showPrintStatus(message, success) {
        printStatus.textContent = message;
        printStatus.className = `small mb-3 ${success ? 'text-success' : 'text-danger'}`;
    }

    async function printInvoice(noInvoice) {
        printButton.disabled = true;
        printButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim ke printer...';
        showPrintStatus(`Menyiapkan invoice ${noInvoice} untuk direct print...`, true);
        console.info('[Direct Print] Memulai cetak invoice:', noInvoice);

        try {
            const textUrl = textUrlTemplate.replace('__INVOICE__', encodeURIComponent(noInvoice));
            const textResponse = await fetch(textUrl, {headers: {Accept: 'application/json'}});
            if (!textResponse.ok) throw new Error(`Data invoice untuk printer gagal dimuat (HTTP ${textResponse.status}).`);
            const {text} = await textResponse.json();
            if (!text) throw new Error('Format teks invoice dari server kosong.');

            const printResponse = await fetch('http://localhost:3000/print-text', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({text}),
            });
            const responseBody = await printResponse.text();
            let result;
            try {
                result = responseBody ? JSON.parse(responseBody) : {};
            } catch {
                result = {message: responseBody};
            }

            console.log('[Direct Print] Respons Electron:', result);
            if (!printResponse.ok) {
                throw new Error(result.message || result.error || `Print bridge gagal (HTTP ${printResponse.status}).`);
            }

            const bridgeStatus = String(result.status ?? '').toLowerCase();
            const message = String(result.message || result.error || 'Pekerjaan cetak dikirim ke print bridge.');
            const noPrinter = /no printer|printer.*not found|no.*printer.*found|pilih printer/i.test(message);
            const failed = result.success === false || result.status === false || result.ok === false
                || result.printerFound === false || result.found === false
                || ['error', 'failed', 'fail'].includes(bridgeStatus)
                || noPrinter;

            if (failed) throw new Error(message || 'Printer tidak ditemukan atau tidak siap.');

            console.info('[Direct Print] Berhasil:', result);
            showPrintStatus(message, true);
        } catch (error) {
            console.error('[Direct Print] Gagal:', error);
            showPrintStatus(`${error.message} Anda masih dapat mencetak melalui PDF.`, false);
            window.alert(`${error.message} Anda masih dapat mencetak melalui PDF.`);
        } finally {
            printButton.disabled = false;
            printButton.innerHTML = originalButtonContent;
        }
    }

    printButton.addEventListener('click', () => printInvoice(invoiceNo));
    if (autoPrintInvoice) printInvoice(autoPrintInvoice);
})();
</script>
@endpush
