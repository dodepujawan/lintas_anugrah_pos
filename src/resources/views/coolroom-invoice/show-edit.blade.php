@extends('layouts.app')

@section('title', 'Edit Invoice Coolroom ' . $invoice->INVOICE)

@section('content')
<div class="container-fluid" data-debug="transaksiCoolroomInvoiceEditForm">
    @if ($errors->any())<div class="alert alert-danger" role="alert"><div class="fw-semibold">Periksa kembali nilai invoice.</div><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div><div class="text-uppercase small fw-semibold text-primary mb-1">Transaksi / Coolroom</div><h1 class="page-title mb-1">Edit Invoice Coolroom</h1><p class="text-muted mb-0">Invoice <span class="font-monospace fw-semibold">{{ $invoice->INVOICE }}</span></p></div>
        <a href="{{ route('coolroom-invoice.show', $invoice->INVOICE) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali ke Detail</a>
    </div>

    <form method="POST" action="{{ route('coolroom-invoice-gen.update', $invoice->INVOICE) }}" id="coolroomInvoiceEditForm">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-xl-5">
                <section class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">Identitas Invoice</h2></div><div class="card-body">
                    <div class="mb-3"><label class="form-label small fw-semibold">No. Invoice</label><input class="form-control bg-light font-monospace" value="{{ $invoice->INVOICE }}" readonly></div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Customer</label><input class="form-control bg-light" value="{{ $invoice->CUSTOMER }}" readonly></div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Tanggal Invoice</label><input class="form-control bg-light" value="{{ $invoice->TGLINVOICE?->format('d/m/Y') ?? '-' }}" readonly></div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Jumlah / Unit</label><input class="form-control bg-light" value="{{ number_format((float) $invoice->JUMLAH, 3, ',', '.') }} {{ $invoice->UNIT ?: 'KG' }}" readonly></div>
                    <div><label class="form-label small fw-semibold" for="keterangan">Keterangan</label><textarea name="keterangan" id="keterangan" maxlength="500" class="form-control" rows="3">{{ old('keterangan', $invoice->KETERANGAN) }}</textarea></div>
                </div></section>
            </div>
            <div class="col-xl-7">
                <section class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">Perhitungan Invoice</h2></div><div class="card-body"><div class="row g-3">
                    <div class="col-md-4"><label for="jumlah" class="form-label small fw-semibold">Jumlah (KG)</label><input type="number" name="jumlah" id="jumlah" class="form-control text-end" min="0" step="0.001" value="{{ old('jumlah', $invoice->JUMLAH) }}" required></div>
                    <div class="col-md-4"><label for="harga" class="form-label small fw-semibold">Harga</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="harga" id="harga" class="form-control text-end" min="0" step="1" value="{{ old('harga', $invoice->HARGA) }}" required></div></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">Boxing</label><div class="form-check form-switch pt-2"><input type="checkbox" name="boxing" id="boxing" value="1" class="form-check-input" @checked(old('boxing', $invoice->BOXING))><label class="form-check-label" for="boxing">Harga paket</label></div></div>
                    <div class="col-md-4"><label for="disc" class="form-label small fw-semibold">Diskon (%)</label><input type="number" name="disc" id="disc" class="form-control text-end" min="0" max="100" step="0.01" value="{{ old('disc', $invoice->DISC) }}"></div>
                    <div class="col-md-4"><label for="ppn" class="form-label small fw-semibold">PPN (%)</label><input type="number" name="ppn" id="ppn" class="form-control text-end" min="0" max="100" step="0.01" value="{{ old('ppn', $invoice->PPN) }}"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">Subtotal</label><div class="input-group"><span class="input-group-text">Rp</span><input id="subtotal_preview" class="form-control text-end bg-light" readonly></div></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">DPP</label><div class="input-group"><span class="input-group-text">Rp</span><input id="dpp_preview" class="form-control text-end bg-light" readonly></div></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">Nilai PPN</label><div class="input-group"><span class="input-group-text">Rp</span><input id="ppn_preview" class="form-control text-end bg-light" readonly></div></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">Grand Total</label><div class="input-group"><span class="input-group-text">Rp</span><input id="grand_preview" class="form-control text-end fw-bold bg-light" readonly></div></div>
                    <div class="col-md-4"><label for="bayar" class="form-label small fw-semibold">Bayar</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="bayar" id="bayar" class="form-control text-end" min="0" step="1" value="{{ old('bayar', $invoice->BAYAR) }}"></div></div>
                    <div class="col-md-4"><label for="tgl_jt" class="form-label small fw-semibold">Tanggal Jatuh Tempo</label><input type="date" name="tgl_jt" id="tgl_jt" class="form-control" value="{{ old('tgl_jt', ($arh?->TGLJT ?? $invoice->TGLJT)?->format('Y-m-d')) }}"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">Sisa Piutang</label><div class="input-group"><span class="input-group-text">Rp</span><input id="piutang_preview" class="form-control text-end fw-semibold bg-light" readonly></div></div>
                </div><div id="coolroomInvoiceEditWarning" class="small text-danger mt-3" role="status"></div></div></section>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3"><a href="{{ route('coolroom-invoice.show', $invoice->INVOICE) }}" class="btn btn-outline-secondary">Batal</a><button type="submit" id="coolroomInvoiceEditSubmit" class="btn btn-primary" data-debug="transaksiCoolroomInvoiceEditSubmit"><i class="bi bi-save me-1"></i>Simpan Perubahan</button></div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const number = id => Number(document.getElementById(id).value || 0);
    const money = value => Math.round(value || 0).toLocaleString('id-ID');
    const ids = ['jumlah', 'harga', 'disc', 'ppn', 'boxing', 'bayar'];
    function recalculate() {
        const subtotal = Math.round(document.getElementById('boxing').checked ? number('harga') : number('jumlah') * number('harga'));
        const dpp = Math.round(subtotal - Math.round(subtotal * number('disc') / 100));
        const ppn = Math.round(dpp * number('ppn') / 100);
        const grand = Math.round(dpp + ppn);
        const bayar = number('bayar');
        document.getElementById('subtotal_preview').value = money(subtotal);
        document.getElementById('dpp_preview').value = money(dpp);
        document.getElementById('ppn_preview').value = money(ppn);
        document.getElementById('grand_preview').value = money(grand);
        document.getElementById('piutang_preview').value = money(Math.max(0, grand - bayar));
        document.getElementById('coolroomInvoiceEditWarning').textContent = bayar > grand ? 'Bayar tidak boleh lebih besar dari Grand Total.' : '';
        document.getElementById('coolroomInvoiceEditSubmit').disabled = bayar > grand;
    }
    ids.forEach(id => {
        document.getElementById(id).addEventListener('input', recalculate);
        document.getElementById(id).addEventListener('change', recalculate);
    });
    recalculate();
})();
</script>
@endpush
