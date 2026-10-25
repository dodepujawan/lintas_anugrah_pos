@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiExpedisiKwitansiIndex">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Kwitansi Expedisi</h1>
            <p class="text-muted small mb-0">Proses kwitansi lunas per invoice</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif
    <div id="directPrintStatus" class="small mb-2"></div>

    <form method="GET" id="formFilterKwitansiExpedisi" data-debug="transaksiExpedisiKwitansiFilter" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <select name="status_kwt" id="filter_status_kwt" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="belum" @selected($status === 'belum')>Belum Kwitansi</option>
                    <option value="sudah" @selected($status === 'sudah')>Sudah Kwitansi</option>
                </select>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="KwitansiEksTable">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Invoice</th>
                        <th>Tgl Invoice</th>
                        <th>Customer</th>
                        <th class="text-end">Grand</th>
                        <th class="text-end">Piutang</th>
                        @if ($status === 'sudah')
                            <th>No. KWT</th>
                        @endif
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $row)
                    <tr>
                        <td>{{ $invoices->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $row->INVOICE }}</td>
                        <td>{{ $row->TGLINVOICE?->format('d-m-Y') ?? '-' }}</td>
                        <td>{{ $row->CUSTOMER }}</td>
                        <td class="text-end">{{ number_format((float) $row->GRAND, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $row->PIUTANG, 0, ',', '.') }}</td>
                        @if ($status === 'sudah')
                            <td class="font-monospace">{{ $row->kwt }}</td>
                        @endif
                        <td class="text-end">
                            @if ($status === 'belum')
                                <form method="POST" action="{{ route('expedisi-kwitansi.proses') }}" class="d-inline"
                                      onsubmit="return confirm('Kwitansi akan dibuat untuk invoice {{ $row->INVOICE }}?')"
                                      data-debug="transaksiExpedisiKwitansiProses_{{ $row->INVOICE }}">
                                    @csrf
                                    <input type="hidden" name="invoice" value="{{ $row->INVOICE }}">
                                    <button type="submit" class="btn btn-sm btn-primary">Proses</button>
                                </form>
                            @else
                                <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-primary btn-print-kwt"
                                            data-kwitansi="{{ $row->kwt }}"
                                            data-debug="transaksiExpedisiKwitansiPrint_{{ $row->kwt }}">
                                        <i class="bi bi-printer"></i> Print
                                    </button>
                                    <a href="{{ route('expedisi-kwitansi.printKwitansi', $row->kwt) }}" target="_blank"
                                       class="btn btn-sm btn-success" data-debug="transaksiExpedisiKwitansiPdf_{{ $row->kwt }}">
                                        <i class="bi bi-file-pdf"></i> PDF
                                    </a>
                                    <form method="POST" action="{{ route('expedisi-kwitansi.destroy') }}" class="d-inline"
                                          onsubmit="return confirm('Batalkan kwitansi {{ $row->kwt }}?')"
                                          data-debug="transaksiExpedisiKwitansiDelete_{{ $row->kwt }}">
                                        @csrf
                                        <input type="hidden" name="kwt" value="{{ $row->kwt }}">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Batal
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $status === 'sudah' ? 8 : 7 }}" class="text-center py-4 text-muted">
                            Tidak ada data.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const textUrlTemplate = @json(route('expedisi-kwitansi.text', ['noKwt' => '__KWT__']));
    const autoPrintKwitansi = @json(session('autoPrintKwitansi'));
    const printStatus = document.getElementById('directPrintStatus');

    function showPrintStatus(message, success) {
        printStatus.textContent = message;
        printStatus.className = `small mb-2 ${success ? 'text-success' : 'text-danger'}`;
    }

    async function printKwitansi(noKwt, button) {
        if (button) {
            button.disabled = true;
        }
        showPrintStatus(`Menyiapkan kwitansi ${noKwt} untuk direct print...`, true);

        try {
            const textUrl = textUrlTemplate.replace('__KWT__', encodeURIComponent(noKwt));
            const textResponse = await fetch(textUrl, {headers: {Accept: 'application/json'}});
            if (!textResponse.ok) throw new Error(`Data kwitansi untuk printer gagal dimuat (HTTP ${textResponse.status}).`);
            const {text} = await textResponse.json();
            if (!text) throw new Error('Format teks kwitansi dari server kosong.');

            const printResponse = await fetch('http://localhost:3000/print-text', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({text}),
            });
            const body = await printResponse.text();
            let result;
            try {
                result = body ? JSON.parse(body) : {};
            } catch {
                result = {message: body};
            }

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

            showPrintStatus(message, true);
        } catch (error) {
            showPrintStatus(`${error.message} Anda masih dapat mencetak melalui PDF.`, false);
            window.alert(`${error.message} Anda masih dapat mencetak melalui PDF.`);
        } finally {
            if (button) {
                button.disabled = false;
            }
        }
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-print-kwt');
        if (!button) return;
        event.preventDefault();
        printKwitansi(button.dataset.kwitansi, button);
    });

    if (autoPrintKwitansi) {
        printKwitansi(autoPrintKwitansi, null);
    }
})();
</script>
@endpush
