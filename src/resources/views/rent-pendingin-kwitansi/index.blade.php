@extends('layouts.app')

@section('title', 'Kwitansi Rent Pendingin')

@section('content')
<div class="container-fluid" data-debug="transaksiRentPendinginKwitansiIndex">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-semibold text-primary mb-1">Transaksi / Rent Pendingin</div>
            <h1 class="page-title mb-1">Kwitansi Rent Pendingin</h1>
            <p class="text-muted mb-0">Proses kwitansi per invoice, atau cetak kembali kwitansi yang sudah dibuat.</p>
        </div>
        <a href="{{ route('rent-pendingin-invoice.index', ['status_inv_gen' => 'sudah']) }}" class="btn btn-outline-secondary">
            <i class="bi bi-receipt-cutoff me-1"></i>Daftar Invoice
        </a>
    </div>

    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div id="rentKwitansiPrintStatus" class="small mb-3" role="status" aria-live="polite"></div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white px-3 px-lg-4 py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="h5 mb-1">Invoice siap diproses</h2>
                    <p class="small text-muted mb-0">Nomor kwitansi mengikat seluruh baris Rent Pendingin pada invoice tersebut.</p>
                </div>
                <form method="GET" action="{{ route('rent-pendingin-kwitansi.index') }}" id="rentKwitansiFilterForm">
                    <div class="btn-group" role="group" aria-label="Filter status kwitansi">
                        <button type="submit" name="status_kwt" value="belum" class="btn {{ $status === 'belum' ? 'btn-primary' : 'btn-outline-primary' }}">Belum Kwitansi</button>
                        <button type="submit" name="status_kwt" value="sudah" class="btn {{ $status === 'sudah' ? 'btn-primary' : 'btn-outline-primary' }}">Sudah Kwitansi</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-debug="transaksiRentPendinginKwitansiTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>No. Invoice</th>
                        <th>Tanggal Invoice</th>
                        <th>Customer</th>
                        <th class="text-end">Grand</th>
                        <th class="text-end">Piutang</th>
                        @if ($status === 'sudah')<th>No. Kwitansi</th>@endif
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $row)
                        <tr>
                            <td class="ps-3">{{ $invoices->firstItem() + $loop->index }}</td>
                            <td class="font-monospace fw-semibold">{{ $row->INVOICE }}</td>
                            <td>{{ $row->TGLINVOICE?->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ $row->CUSTOMER }}</td>
                            <td class="text-end text-nowrap">Rp {{ number_format((float) $row->GRAND, 0, ',', '.') }}</td>
                            <td class="text-end text-nowrap">Rp {{ number_format((float) $row->PIUTANG, 0, ',', '.') }}</td>
                            @if ($status === 'sudah')<td class="font-monospace">{{ $row->kwt }}</td>@endif
                            <td class="text-end pe-3">
                                @if ($status === 'belum')
                                    <form method="POST" action="{{ route('rent-pendingin-kwitansi.store') }}" class="d-inline"
                                          onsubmit="return confirm('Buat kwitansi untuk invoice {{ $row->INVOICE }}?')"
                                          data-debug="transaksiRentPendinginKwitansiProses_{{ $row->INVOICE }}">
                                        @csrf
                                        <input type="hidden" name="invoice" value="{{ $row->INVOICE }}">
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-check me-1"></i>Proses</button>
                                    </form>
                                @else
                                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                        <button type="button" class="btn btn-sm btn-primary btn-rent-kwitansi-print" data-kwitansi="{{ $row->kwt }}" data-debug="transaksiRentPendinginKwitansiPrint_{{ $row->kwt }}">
                                            <i class="bi bi-printer me-1"></i>Print
                                        </button>
                                        <a href="{{ route('rent-pendingin-kwitansi.pdf', $row->kwt) }}" target="_blank" rel="noopener" class="btn btn-sm btn-success" data-debug="transaksiRentPendinginKwitansiPdf_{{ $row->kwt }}">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                        </a>
                                        <form method="POST" action="{{ route('rent-pendingin-kwitansi.destroy') }}" class="d-inline"
                                              onsubmit="return confirm('Batalkan kwitansi {{ $row->kwt }}? Data pembayaran invoice tidak akan diubah.')"
                                              data-debug="transaksiRentPendinginKwitansiDelete_{{ $row->kwt }}">
                                            @csrf
                                            <input type="hidden" name="kwt" value="{{ $row->kwt }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Batal</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $status === 'sudah' ? 8 : 7 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            {{ $status === 'sudah' ? 'Belum ada kwitansi Rent Pendingin.' : 'Tidak ada invoice yang siap dibuatkan kwitansi.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const textUrl = @json(route('rent-pendingin-kwitansi.text', ['noKwt' => '__KWT__']));
    const autoPrintKwitansi = @json(session('autoPrintKwitansi'));
    const status = document.getElementById('rentKwitansiPrintStatus');

    function showStatus(message, success) {
        status.textContent = message;
        status.className = `small mb-3 ${success ? 'text-success' : 'text-danger'}`;
    }

    async function printKwitansi(noKwt, button = null) {
        if (button) button.disabled = true;
        showStatus(`Menyiapkan kwitansi ${noKwt} untuk direct print...`, true);
        try {
            const response = await fetch(textUrl.replace('__KWT__', encodeURIComponent(noKwt)), {headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error(`Data kwitansi gagal dimuat (HTTP ${response.status}).`);
            const {text} = await response.json();
            if (!text) throw new Error('Data teks kwitansi kosong.');

            const printResponse = await fetch('http://localhost:3000/print-text', {
                method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({text}),
            });
            const body = await printResponse.text();
            let result;
            try { result = body ? JSON.parse(body) : {}; } catch { result = {message: body}; }
            const message = String(result.message || result.error || 'Pekerjaan cetak dikirim ke print bridge.');
            const bridgeStatus = String(result.status ?? '').toLowerCase();
            const failed = !printResponse.ok || result.success === false || result.status === false || result.ok === false
                || result.printerFound === false || ['error', 'failed', 'fail'].includes(bridgeStatus)
                || /no printer|printer.*not found|pilih printer/i.test(message);
            if (failed) throw new Error(message);
            showStatus(message, true);
        } catch (error) {
            showStatus(`${error.message} Anda masih dapat mencetak melalui PDF.`, false);
            window.alert(`${error.message} Anda masih dapat mencetak melalui PDF.`);
        } finally {
            if (button) button.disabled = false;
        }
    }

    document.addEventListener('click', event => {
        const button = event.target.closest('.btn-rent-kwitansi-print');
        if (!button) return;
        event.preventDefault();
        printKwitansi(button.dataset.kwitansi, button);
    });

    if (autoPrintKwitansi) printKwitansi(autoPrintKwitansi);
})();
</script>
@endpush
