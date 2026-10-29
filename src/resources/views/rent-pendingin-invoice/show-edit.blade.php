@extends('layouts.app')

@section('title', 'Edit Invoice Rent Pendingin')

@section('content')
<div class="container-fluid" data-debug="transaksiRentPendinginInvoiceShowEdit">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-semibold text-warning mb-1">Invoice Rent Pendingin</div>
            <h1 class="page-title mb-1">Edit Rincian Invoice</h1>
            <p class="text-muted mb-0">Perubahan disimpan pada data transaksi `expedisis` dan piutang terkait di `arh`.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rent-pendingin-invoice.show', $invoice->INVOICE) }}" class="btn btn-outline-primary"><i class="bi bi-eye me-1"></i>Preview</a>
            <a href="{{ route('rent-pendingin-invoice-gen.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

    <div class="row g-4">
        <div class="col-xl-8">
            <form method="POST" action="{{ route('rent-pendingin-invoice-gen.update', $invoice->INVOICE) }}" id="rentInvoiceFinancialEditForm" class="card border-0 shadow-sm">
                @csrf
                @method('PUT')
                <div class="card-header bg-white py-3"><h2 class="h5 mb-0">Rincian yang dapat dikoreksi</h2></div>
                <div class="card-body p-3 p-lg-4">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label small fw-semibold">No. Invoice</label><input class="form-control font-monospace" value="{{ $invoice->INVOICE }}" readonly></div>
                        <div class="col-md-4"><label class="form-label small fw-semibold">Tanggal Invoice</label><input class="form-control" value="{{ $invoice->TGLINVOICE?->format('d/m/Y') ?? '-' }}" readonly></div>
                        <div class="col-md-4"><label class="form-label small fw-semibold">No. Surat Jalan</label><input class="form-control font-monospace" value="{{ $invoice->NOSJ }}" readonly></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Customer</label><input class="form-control" value="{{ $invoice->CUSTOMER }}" readonly></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">No. Muat / Kendaraan</label><input class="form-control" value="{{ $invoice->NOMUAT }} / {{ $invoice->NAMA_KENDARAAN }}" readonly></div>
                        <div class="col-md-6"><label for="rentEditItem" class="form-label small fw-semibold">Pesanan</label><input id="rentEditItem" name="item" class="form-control" maxlength="100" value="{{ old('item', $invoice->PESANAN) }}"></div>
                        <div class="col-md-3"><label for="rentEditQuantity" class="form-label small fw-semibold">Jumlah</label><input id="rentEditQuantity" name="jumlah" type="number" class="form-control text-end" min="0" step="0.001" value="{{ old('jumlah', $invoice->JUMLAH) }}" required></div>
                        <div class="col-md-3"><label class="form-label small fw-semibold">Unit</label><input class="form-control" value="{{ $invoice->UNIT }}" readonly></div>
                        <div class="col-md-4"><label for="rentEditPrice" class="form-label small fw-semibold">Harga</label><input id="rentEditPrice" name="harga" type="number" class="form-control text-end" min="0" step="1" value="{{ old('harga', $invoice->HARGA) }}" required></div>
                        <div class="col-md-4"><label for="rentEditDiscount" class="form-label small fw-semibold">Diskon (%)</label><input id="rentEditDiscount" name="disc" type="number" class="form-control text-end" min="0" max="100" step="0.01" value="{{ old('disc', $invoice->DISC) }}"></div>
                        <div class="col-md-4"><label for="rentEditDelivery" class="form-label small fw-semibold">D. Charge</label><input id="rentEditDelivery" name="del_charge" type="number" class="form-control text-end" min="0" step="1" value="{{ old('del_charge', $invoice->DC) }}"></div>
                        <div class="col-md-4"><label for="rentEditPpn" class="form-label small fw-semibold">PPN (%)</label><input id="rentEditPpn" name="ppn" type="number" class="form-control text-end" min="0" max="100" step="0.01" value="{{ old('ppn', $invoice->PPN) }}"></div>
                        <div class="col-md-4"><label for="rentEditPayment" class="form-label small fw-semibold">Bayar</label><input id="rentEditPayment" name="bayar" type="number" class="form-control text-end" min="0" step="1" value="{{ old('bayar', $invoice->BAYAR) }}"></div>
                        <div class="col-md-4"><label for="rentEditDueDate" class="form-label small fw-semibold">Tanggal Jatuh Tempo</label><input id="rentEditDueDate" name="tgl_jt" type="date" class="form-control" value="{{ old('tgl_jt', ($arh?->TGLJT ?? $invoice->TGLJT)?->format('Y-m-d')) }}"></div>
                        <div class="col-12"><label for="rentEditNote" class="form-label small fw-semibold">Keterangan</label><textarea id="rentEditNote" name="keterangan" class="form-control" rows="2" maxlength="500">{{ old('keterangan', $invoice->KETERANGAN) }}</textarea></div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
                    <a href="{{ route('rent-pendingin-invoice-gen.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-success" id="rentInvoiceEditSubmit"><i class="bi bi-check2-circle me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm sticky-xl-top" style="top: 1rem">
                <div class="card-header bg-white py-3"><h2 class="h5 mb-0">Ringkasan nilai</h2></div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-7 fw-normal text-muted">Subtotal</dt><dd class="col-5 text-end" id="rentEditSubtotal">-</dd>
                        <dt class="col-7 fw-normal text-muted">Diskon</dt><dd class="col-5 text-end" id="rentEditDiscountValue">-</dd>
                        <dt class="col-7 fw-normal text-muted">Total</dt><dd class="col-5 text-end" id="rentEditTotal">-</dd>
                        <dt class="col-7 fw-normal text-muted">PPN</dt><dd class="col-5 text-end" id="rentEditPpnValue">-</dd>
                        <dt class="col-7 fw-normal text-muted">D. Charge</dt><dd class="col-5 text-end" id="rentEditDeliveryValue">-</dd>
                    </dl>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2"><span class="fw-semibold">Grand Total</span><strong class="fs-5" id="rentEditGrand">-</strong></div>
                    <div class="d-flex justify-content-between text-muted"><span>Bayar</span><span id="rentEditPaid">-</span></div>
                    <div class="d-flex justify-content-between mt-2 pt-2 border-top"><span class="fw-semibold">Sisa Piutang</span><strong id="rentEditReceivable">-</strong></div>
                    <div class="small text-danger mt-3 d-none" id="rentEditValidation" role="alert">Grand Total tidak boleh lebih kecil dari pembayaran.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const fields = ['Quantity', 'Price', 'Discount', 'Delivery', 'Ppn', 'Payment'];
    const inputs = fields.map(name => document.getElementById(`rentEdit${name}`));
    const money = value => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
    function updateSummary() {
        const [quantity, price, discount, delivery, ppn, payment] = inputs.map(input => Number(input.value || 0));
        const subtotal = Math.round(quantity * price);
        const discountValue = Math.round(subtotal * discount / 100);
        const total = Math.round(subtotal - discountValue);
        const ppnValue = Math.round(total * ppn / 100);
        const grand = Math.round(total + ppnValue + delivery);
        const paid = Math.round(payment);
        document.getElementById('rentEditSubtotal').textContent = money(subtotal);
        document.getElementById('rentEditDiscountValue').textContent = money(discountValue);
        document.getElementById('rentEditTotal').textContent = money(total);
        document.getElementById('rentEditPpnValue').textContent = money(ppnValue);
        document.getElementById('rentEditDeliveryValue').textContent = money(delivery);
        document.getElementById('rentEditGrand').textContent = money(grand);
        document.getElementById('rentEditPaid').textContent = money(paid);
        document.getElementById('rentEditReceivable').textContent = money(Math.max(0, grand - paid));
        const invalid = paid > grand;
        document.getElementById('rentEditValidation').classList.toggle('d-none', !invalid);
        document.getElementById('rentInvoiceEditSubmit').disabled = invalid;
    }
    inputs.forEach(input => input.addEventListener('input', updateSummary));
    document.getElementById('rentInvoiceFinancialEditForm').addEventListener('submit', event => {
        const button = document.getElementById('rentInvoiceEditSubmit');
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });
    updateSummary();
})();
</script>
@endpush
