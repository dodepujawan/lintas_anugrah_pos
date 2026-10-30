@extends('layouts.app')

@section('title', 'Generate Invoice Coolroom')

@section('content')
<div class="container-fluid" data-debug="transaksiCoolroomInvoiceGenerate">
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-semibold text-primary mb-1">Transaksi / Coolroom</div>
            <h1 class="page-title mb-1">Generate Invoice Coolroom</h1>
            <p class="text-muted mb-0">Pilih surat jalan untuk membuat atau memperbarui tagihan Coolroom.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('coolroom-invoice-gen.index') }}" class="btn btn-outline-warning" data-debug="transaksiCoolroomInvoiceGenerateEditBtn"><i class="bi bi-pencil-square me-1"></i>Edit Invoice</a>
            <a href="{{ route('coolroom-invoice.index') }}" class="btn btn-outline-secondary" data-debug="transaksiCoolroomInvoiceGenerateBackBtn"><i class="bi bi-receipt me-1"></i>Daftar Invoice</a>
        </div>
    </div>

    <section id="table_coolroom_inv">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Surat Jalan Coolroom</h2>
                        <p class="small text-muted mb-0">Data belum invoice dapat diproses. Data sudah invoice dapat diedit selama belum ada pembayaran.</p>
                    </div>
                    <div class="btn-group btn-group-sm align-self-start" role="group" aria-label="Status invoice">
                        <a href="{{ route('coolroom-invoice.create', ['status_invoice' => 'belum', 'search' => $search]) }}" class="btn {{ $status === 'belum' ? 'btn-primary' : 'btn-outline-primary' }}">Belum Invoice</a>
                        <a href="{{ route('coolroom-invoice.create', ['status_invoice' => 'sudah', 'search' => $search]) }}" class="btn {{ $status === 'sudah' ? 'btn-primary' : 'btn-outline-primary' }}">Sudah Invoice</a>
                    </div>
                </div>

                <form method="GET" action="{{ route('coolroom-invoice.create') }}" class="row g-2 align-items-end mb-3" data-debug="transaksiCoolroomInvoiceGenerateSearch">
                    <input type="hidden" name="status_invoice" value="{{ $status }}">
                    <div class="col-lg-6 col-md-8">
                        <label for="coolroomGenerateSearch" class="form-label small fw-semibold">Cari No. SJ / Customer</label>
                        <input type="search" id="coolroomGenerateSearch" name="search" class="form-control" value="{{ $search }}" placeholder="Nomor surat jalan, kode, atau nama customer">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari</button>
                        <a href="{{ route('coolroom-invoice.create', ['status_invoice' => $status]) }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                    <div class="col-md text-md-end small text-muted">{{ $coolrooms->total() }} surat jalan</div>
                </form>

                <form method="POST" action="{{ route('coolroom-invoice.export') }}" class="row g-2 align-items-end border-top pt-3" data-debug="transaksiCoolroomInvoiceExport">
                    @csrf
                    <input type="hidden" name="status_invoice" value="{{ $status }}">
                    <div class="col-md-3 col-sm-6">
                        <label for="coolroomExportFrom" class="form-label small fw-semibold">Tanggal dari</label>
                        <input type="date" name="date_from" id="coolroomExportFrom" class="form-control form-control-sm" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="coolroomExportTo" class="form-label small fw-semibold">Tanggal sampai</label>
                        <input type="date" name="date_to" id="coolroomExportTo" class="form-control form-control-sm" value="{{ request('date_to', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="coolroomExportCustomer" class="form-label small fw-semibold">Kode customer (opsional)</label>
                        <input type="text" name="customer" id="coolroomExportCustomer" class="form-control form-control-sm" value="{{ request('customer') }}" maxlength="20" placeholder="Semua customer">
                    </div>
                    <div class="col-md-auto col-sm-6">
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Excel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th class="ps-3">No</th><th>No. SJ</th><th>Tgl SJ</th><th>Customer</th><th class="text-end">Jumlah</th><th class="text-end">Grand</th><th>Invoice</th><th class="text-end pe-3">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($coolrooms as $row)
                            <tr>
                                <td class="ps-3">{{ $coolrooms->firstItem() + $loop->index }}</td>
                                <td class="font-monospace fw-semibold">{{ $row->NOSJ }}</td>
                                <td>{{ $row->TGLSJ?->format('d/m/Y') ?? '-' }}</td>
                                <td><div class="fw-semibold">{{ $row->CUSTOMER }}</div><div class="small text-muted">{{ $row->CUSTOMER_KODE }}</div></td>
                                <td class="text-end">{{ number_format((float) $row->JUMLAH, 3, ',', '.') }} {{ $row->UNIT ?: 'KG' }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format((float) $row->GRAND, 0, ',', '.') }}</td>
                                <td class="font-monospace">{{ $row->INVOICE ?: '-' }}</td>
                                <td class="text-end pe-3">
                                    @if ($status === 'sudah')
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-edit-invoice-coolroom" data-nosj="{{ $row->NOSJ }}" data-debug="transaksiCoolroomInvoiceGenerateEditBtn_{{ $row->NOSJ }}"><i class="bi bi-pencil-square me-1"></i>Edit Invoice</button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-success btn-proses-invoice-coolroom" data-nosj="{{ $row->NOSJ }}" data-debug="transaksiCoolroomInvoiceGenerateCreateBtn_{{ $row->NOSJ }}"><i class="bi bi-file-earmark-check me-1"></i>Buat Invoice</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Tidak ada data Coolroom yang cocok dengan filter saat ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $coolrooms->links() }}</div>
    </section>

    <section class="container-fluid px-0 mt-3 d-none" id="form_coolroom_inv" data-debug="transaksiCoolroomInvoiceForm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
            <div><div class="text-uppercase small fw-semibold text-primary mb-1">Transaksi / Coolroom</div><h2 class="page-title mb-1" id="coolroomInvoiceFormTitle">Proses Invoice</h2><p class="text-muted mb-0">Nilai invoice berasal dari surat jalan dan dihitung ulang di server.</p></div>
            <button type="button" class="btn btn-outline-secondary" id="returnCoolroomInvBtn"><i class="bi bi-arrow-left me-1"></i>Kembali ke daftar</button>
        </div>
        <div id="coolroomInvoiceAlert" class="alert alert-danger d-none" role="alert"></div>
        <input type="hidden" id="coolroom_inv_id">
        <div class="row g-3">
            <div class="col-xl-6">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3"><h3 class="h6 mb-0 fw-bold">Informasi Invoice</h3></div>
                    <div class="card-body"><div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold" for="nosj_coolroom_inv">No. SJ</label><input type="text" id="nosj_coolroom_inv" class="form-control bg-light font-monospace" readonly></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold" for="tglsj_coolroom_inv">Tanggal SJ</label><input type="date" id="tglsj_coolroom_inv" class="form-control bg-light" readonly></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold" for="invoice_coolroom_inv">No. Invoice</label><input type="text" id="invoice_coolroom_inv" class="form-control bg-light font-monospace" readonly></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold" for="tglinvoice_coolroom_inv">Tanggal Invoice</label><input type="date" id="tglinvoice_coolroom_inv" class="form-control bg-light" readonly></div>
                        <div class="col-md-8"><label class="form-label small fw-semibold" for="customer_coolroom_inv">Customer</label><input type="text" id="customer_coolroom_inv" class="form-control bg-light" readonly></div>
                        <div class="col-md-4"><label class="form-label small fw-semibold" for="customer_kode_coolroom_inv">Kode Customer</label><input type="text" id="customer_kode_coolroom_inv" class="form-control bg-light" readonly></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold" for="jumlah_coolroom_inv">Jumlah</label><div class="input-group"><input type="text" id="jumlah_coolroom_inv" class="form-control text-end bg-light" readonly><span class="input-group-text">KG</span></div></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold" for="harga_coolroom_inv">Harga</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" id="harga_coolroom_inv" class="form-control text-end bg-light" readonly></div></div>
                    </div></div>
                </section>
            </div>
            <div class="col-xl-6">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3"><h3 class="h6 mb-0 fw-bold">Rincian Tagihan</h3></div>
                    <div class="card-body"><div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold">Subtotal</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" id="subtotal_coolroom_inv" class="form-control text-end bg-light" readonly></div></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold">Diskon %</label><input type="text" id="disc_coolroom_inv" class="form-control text-end bg-light" readonly></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold">Diskon Rp</label><input type="text" id="ndisc_coolroom_inv" class="form-control text-end bg-light" readonly></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">DPP</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" id="dpp_coolroom_inv" class="form-control text-end bg-light" readonly></div></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold">PPN %</label><input type="text" id="ppn_coolroom_inv" class="form-control text-end bg-light" readonly></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold">PPN Rp</label><input type="text" id="nppn_coolroom_inv" class="form-control text-end bg-light" readonly></div>
                        <div class="col-12"><label class="form-label small fw-semibold">Grand Total</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" id="grand_coolroom_inv" class="form-control text-end fw-bold bg-light" readonly></div></div>
                    </div></div>
                </section>
            </div>
        </div>
        <section class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white py-3"><h3 class="h6 mb-0 fw-bold">Pembayaran dan Jatuh Tempo</h3></div>
            <div class="card-body"><div class="row g-3 align-items-end">
                <div class="col-lg-3 col-sm-6"><label class="form-label small fw-semibold" for="bayar_coolroom_inv">Bayar</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" id="bayar_coolroom_inv" min="0" step="1" class="form-control text-end"></div></div>
                <div class="col-lg-2 col-sm-6"><label class="form-label small fw-semibold" for="top_coolroom_inv">TOP (hari)</label><input type="number" id="top_coolroom_inv" min="0" step="1" class="form-control"></div>
                <div class="col-lg-3 col-sm-6"><label class="form-label small fw-semibold" for="tgljt_coolroom_inv">Tanggal Jatuh Tempo</label><input type="date" id="tgljt_coolroom_inv" class="form-control"></div>
                <div class="col-lg-4 col-sm-6"><label class="form-label small fw-semibold" for="piutang_coolroom_inv">Sisa Piutang</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" id="piutang_coolroom_inv" class="form-control text-end fw-bold bg-light" readonly></div></div>
            </div>
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-4">
                    <div class="small text-muted" id="coolroomInvoiceValidation" role="status"></div>
                    <button type="button" class="btn btn-success" id="proses_coolroom_inv" data-debug="transaksiCoolroomInvoiceSubmit"><i class="bi bi-check2-circle me-1"></i>Proses Invoice</button>
                </div>
            </div>
        </section>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const detailUrl = @json(route('coolroom-invoice.detail', ['nosj' => '__NOSJ__']));
    const storeUrl = @json(route('coolroom-invoice.store'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const money = value => Number(value || 0).toLocaleString('id-ID');
    const list = document.getElementById('table_coolroom_inv');
    const form = document.getElementById('form_coolroom_inv');
    const submit = document.getElementById('proses_coolroom_inv');
    const alertBox = document.getElementById('coolroomInvoiceAlert');
    let grand = 0;
    let currentNosj = '';
    let mode = 'create';

    function showError(message) {
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
    }

    function clearError() {
        alertBox.textContent = '';
        alertBox.classList.add('d-none');
    }

    function updateReceivable() {
        const payment = Number(document.getElementById('bayar_coolroom_inv').value || 0);
        const invalid = payment > grand;
        document.getElementById('piutang_coolroom_inv').value = money(Math.max(0, grand - payment));
        document.getElementById('coolroomInvoiceValidation').textContent = invalid ? 'Bayar tidak boleh melebihi Grand Total.' : '';
        document.getElementById('coolroomInvoiceValidation').classList.toggle('text-danger', invalid);
        submit.disabled = invalid;
    }

    async function openForm(nosj, edit) {
        currentNosj = nosj;
        mode = edit ? 'edit' : 'create';
        clearError();
        list.classList.add('d-none');
        form.classList.remove('d-none');
        document.getElementById('coolroomInvoiceFormTitle').textContent = edit ? 'Edit Invoice Coolroom' : 'Proses Invoice Coolroom';
        submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memuat data...';
        submit.disabled = true;

        try {
            const url = detailUrl.replace('__NOSJ__', encodeURIComponent(nosj));
            const response = await fetch(url, {headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            const payload = await response.json();
            if (!response.ok || !payload.status) throw new Error(payload.message || 'Data surat jalan gagal dimuat.');

            const data = payload.data;
            grand = Number(data.grand || 0);
            document.getElementById('coolroom_inv_id').value = data.id;
            document.getElementById('nosj_coolroom_inv').value = data.nosj || '';
            document.getElementById('tglsj_coolroom_inv').value = data.tglsj || '';
            document.getElementById('invoice_coolroom_inv').value = data.invoice || 'Dibuat saat disimpan';
            document.getElementById('tglinvoice_coolroom_inv').value = data.tgl_invoice || @json(now()->format('Y-m-d'));
            document.getElementById('customer_coolroom_inv').value = data.customer || '';
            document.getElementById('customer_kode_coolroom_inv').value = data.customer_kode || '';
            document.getElementById('jumlah_coolroom_inv').value = Number(data.jumlah || 0).toLocaleString('id-ID', {maximumFractionDigits: 3});
            document.getElementById('harga_coolroom_inv').value = money(data.harga);
            document.getElementById('subtotal_coolroom_inv').value = money(data.sub_total);
            document.getElementById('disc_coolroom_inv').value = data.disc_persen || 0;
            document.getElementById('ndisc_coolroom_inv').value = money(data.disc_rp);
            document.getElementById('dpp_coolroom_inv').value = money(data.dpp);
            document.getElementById('ppn_coolroom_inv').value = data.ppn || 0;
            document.getElementById('nppn_coolroom_inv').value = money(data.ppn_rp);
            document.getElementById('grand_coolroom_inv').value = money(data.grand);
            document.getElementById('bayar_coolroom_inv').value = Number(data.bayar || 0);
            document.getElementById('top_coolroom_inv').value = Number(data.top || 0);
            document.getElementById('tgljt_coolroom_inv').value = data.tgl_jt || '';
            submit.innerHTML = `<i class="bi bi-check2-circle me-1"></i>${edit ? 'Simpan Perubahan' : 'Proses Invoice'}`;
            updateReceivable();
        } catch (error) {
            showError(error.message);
            submit.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Proses Invoice';
            submit.disabled = true;
        }
    }

    document.addEventListener('click', event => {
        const createButton = event.target.closest('.btn-proses-invoice-coolroom');
        const editButton = event.target.closest('.btn-edit-invoice-coolroom');
        if (createButton) openForm(createButton.dataset.nosj, false);
        if (editButton) openForm(editButton.dataset.nosj, true);
    });

    document.getElementById('bayar_coolroom_inv').addEventListener('input', updateReceivable);
    document.getElementById('returnCoolroomInvBtn').addEventListener('click', () => {
        form.classList.add('d-none');
        list.classList.remove('d-none');
    });

    submit.addEventListener('click', async () => {
        if (submit.disabled || !currentNosj) return;
        const originalText = mode === 'edit' ? 'Simpan Perubahan' : 'Proses Invoice';
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        clearError();

        try {
            const body = new FormData();
            body.set('invoice', mode === 'edit' ? document.getElementById('invoice_coolroom_inv').value : '');
            body.set('nosj', currentNosj);
            body.set('bayar', document.getElementById('bayar_coolroom_inv').value || '0');
            body.set('top', document.getElementById('top_coolroom_inv').value || '0');
            body.set('tgl_jtp', document.getElementById('tgljt_coolroom_inv').value || '');
            const response = await fetch(storeUrl, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest'},
                body,
            });
            const payload = await response.json();
            if (!response.ok || !payload.status) {
                const message = payload.message || Object.values(payload.errors || {}).flat()[0] || 'Invoice gagal diproses.';
                throw new Error(message);
            }
            window.location.assign(payload.redirect_url);
        } catch (error) {
            showError(error.message);
            submit.innerHTML = `<i class="bi bi-check2-circle me-1"></i>${originalText}`;
            updateReceivable();
        }
    });
})();
</script>
@endpush
