@extends('layouts.app')

@section('title', 'Edit Invoice Coolroom ' . $invoice->INVOICE)

@section('content')
@php
    $formatNumber = static function ($value, $scale = 0) {
        $formatted = number_format((float) $value, $scale, ',', '.');

        return $scale > 0 ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
    };
@endphp
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
                    <div class="mb-3"><label class="form-label small fw-semibold">Jumlah / Unit</label><div class="input-group"><input class="form-control bg-light" value="{{ $formatNumber($invoice->JUMLAH, 3) }}" readonly><span class="input-group-text">{{ $invoice->UNIT ?: 'KG' }}</span></div></div>
                    <div><label class="form-label small fw-semibold" for="keterangan">Keterangan</label><textarea name="keterangan" id="keterangan" maxlength="500" class="form-control" rows="3">{{ old('keterangan', $invoice->KETERANGAN) }}</textarea></div>
                </div></section>
            </div>
            <div class="col-xl-7">
                <section class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0">Perhitungan Invoice</h2></div><div class="card-body"><div class="row g-3">
                    <div class="col-md-4"><label for="jumlah" class="form-label small fw-semibold">Jumlah</label><div class="input-group"><input type="text" inputmode="decimal" name="jumlah" id="jumlah" class="form-control text-end coolroom-local-number" value="{{ old('jumlah', $formatNumber($invoice->JUMLAH, 3)) }}" required><span class="input-group-text">{{ $invoice->UNIT ?: 'KG' }}</span></div></div>
                    <div class="col-md-4"><label for="harga" class="form-label small fw-semibold">Harga</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" inputmode="decimal" name="harga" id="harga" class="form-control text-end coolroom-local-number" value="{{ old('harga', $formatNumber($invoice->HARGA)) }}" required></div></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">Boxing</label><div class="form-check form-switch pt-2"><input type="checkbox" name="boxing" id="boxing" value="1" class="form-check-input" @checked(old('boxing', $invoice->BOXING))><label class="form-check-label" for="boxing">Harga paket</label></div></div>
                    <div class="col-md-4"><label for="disc" class="form-label small fw-semibold">Diskon (%)</label><div class="input-group"><input type="text" inputmode="decimal" name="disc" id="disc" class="form-control text-end coolroom-local-number" value="{{ old('disc', $formatNumber($invoice->DISC, 2)) }}"><span class="input-group-text">%</span></div></div>
                    <div class="col-md-4"><label for="ppn" class="form-label small fw-semibold">PPN (%)</label><div class="input-group"><input type="text" inputmode="decimal" name="ppn" id="ppn" class="form-control text-end coolroom-local-number" value="{{ old('ppn', $formatNumber($invoice->PPN, 2)) }}"><span class="input-group-text">%</span></div></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">Subtotal</label><div class="input-group"><span class="input-group-text">Rp</span><input id="subtotal_preview" class="form-control text-end bg-light" readonly></div></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">DPP</label><div class="input-group"><span class="input-group-text">Rp</span><input id="dpp_preview" class="form-control text-end bg-light" readonly></div></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">Nilai PPN</label><div class="input-group"><span class="input-group-text">Rp</span><input id="ppn_preview" class="form-control text-end bg-light" readonly></div></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold">Grand Total</label><div class="input-group"><span class="input-group-text">Rp</span><input id="grand_preview" class="form-control text-end fw-bold bg-light" readonly></div></div>
                    <div class="col-md-4"><label for="bayar" class="form-label small fw-semibold">Bayar</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" inputmode="decimal" name="bayar" id="bayar" class="form-control text-end coolroom-local-number" value="{{ old('bayar', $formatNumber($invoice->BAYAR)) }}"></div></div>
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
    const numberFields = [
        ['jumlah', 3, false],
        ['harga', 0, false],
        ['disc', 2, true],
        ['ppn', 2, true],
        ['bayar', 0, true],
    ];
    const factor = scale => 10n ** BigInt(scale);

    function parseLocalizedDecimal(value) {
        let input = String(value ?? '').trim().replace(/^rp\s*/i, '').replace(/[\s\u00a0]/g, '');
        if (!input) return null;

        const negative = input.startsWith('-');
        if (negative || input.startsWith('+')) input = input.slice(1);
        if (!input || /[^\d.,]/.test(input)) return null;

        const commaCount = (input.match(/,/g) || []).length;
        const dotCount = (input.match(/\./g) || []).length;
        let integer;
        let fraction = '';

        if (commaCount && dotCount) {
            const decimalSeparator = input.lastIndexOf(',') > input.lastIndexOf('.') ? ',' : '.';
            const groupingSeparator = decimalSeparator === ',' ? '.' : ',';
            const decimalIndex = input.lastIndexOf(decimalSeparator);
            const groupedInteger = input.slice(0, decimalIndex);
            const groupPattern = groupingSeparator === '.' ? /^\d{1,3}(?:\.\d{3})+$/ : /^\d{1,3}(?:,\d{3})+$/;
            if (groupedInteger.includes(decimalSeparator)) return null;
            if (groupedInteger.includes(groupingSeparator) && !groupPattern.test(groupedInteger)) return null;
            integer = groupedInteger.split(groupingSeparator).join('').split(decimalSeparator).join('');
            fraction = input.slice(decimalIndex + 1);
        } else if (commaCount) {
            if (commaCount > 1) {
                if (!/^\d{1,3}(?:,\d{3})+$/.test(input)) return null;
                integer = input.replace(/,/g, '');
            } else {
                const decimalIndex = input.lastIndexOf(',');
                integer = input.slice(0, decimalIndex);
                fraction = input.slice(decimalIndex + 1);
            }
        } else if (dotCount) {
            if (/^\d{1,3}(?:\.\d{3})+$/.test(input)) {
                integer = input.replace(/\./g, '');
            } else if (dotCount === 1) {
                const decimalIndex = input.lastIndexOf('.');
                integer = input.slice(0, decimalIndex);
                fraction = input.slice(decimalIndex + 1);
            } else {
                return null;
            }
        } else {
            integer = input;
        }

        integer ||= '0';
        if (!/^\d+$/.test(integer) || (fraction && !/^\d+$/.test(fraction))) return null;
        return `${negative ? '-' : ''}${integer}.${fraction}`;
    }

    function toScaledInteger(value, scale, emptyAsZero = false) {
        const parsed = parseLocalizedDecimal(value);
        if (parsed === null) return emptyAsZero && !String(value ?? '').trim() ? 0n : null;

        const negative = parsed.startsWith('-');
        const unsigned = negative ? parsed.slice(1) : parsed;
        const [whole, fraction = ''] = unsigned.split('.');
        const scaleFactor = factor(scale);
        const keptFraction = (fraction + '0'.repeat(scale)).slice(0, scale);
        let result = BigInt(whole || '0') * scaleFactor + BigInt(keptFraction || '0');
        if (fraction.length > scale && Number(fraction[scale]) >= 5) result++;

        return negative ? -result : result;
    }

    function formatScaled(value, scale) {
        const negative = value < 0n;
        const absolute = negative ? -value : value;
        const scaleFactor = factor(scale);
        const whole = absolute / scaleFactor;
        const remainder = absolute % scaleFactor;
        const numeric = Number(whole) + Number(remainder) / Number(scaleFactor);

        return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: scale,
            minimumFractionDigits: 0,
        }).format(negative ? -numeric : numeric);
    }

    function roundDivide(value, divisor) {
        return (value + divisor / 2n) / divisor;
    }

    function roundMultiplyDivide(value, multiplier, divisor) {
        const whole = value / divisor;
        const remainder = value % divisor;
        return whole * multiplier + (remainder * multiplier + divisor / 2n) / divisor;
    }

    const money = value => new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(Number(value));
    const form = document.getElementById('coolroomInvoiceEditForm');
    const warning = document.getElementById('coolroomInvoiceEditWarning');
    const submit = document.getElementById('coolroomInvoiceEditSubmit');

    function recalculate() {
        const jumlah = toScaledInteger(document.getElementById('jumlah').value, 3);
        const harga = toScaledInteger(document.getElementById('harga').value, 0);
        const disc = toScaledInteger(document.getElementById('disc').value, 2, true);
        const ppnRate = toScaledInteger(document.getElementById('ppn').value, 2, true);
        const bayar = toScaledInteger(document.getElementById('bayar').value, 0, true);
        const valid = [jumlah, harga, disc, ppnRate, bayar].every(value => value !== null && value >= 0n)
            && disc <= 10000n && ppnRate <= 10000n;

        if (!valid) {
            warning.textContent = 'Periksa format angka. Gunakan titik untuk ribuan dan koma untuk desimal.';
            submit.disabled = true;
            return;
        }

        const subtotalScaled = document.getElementById('boxing').checked ? harga * 1000n : jumlah * harga;
        const subtotal = roundDivide(subtotalScaled, 1000n);
        const ndisc = roundMultiplyDivide(subtotalScaled, disc, 10000000n);
        const dpp = roundDivide(subtotalScaled - ndisc * 1000n, 1000n);
        const nilaiPpn = roundMultiplyDivide(dpp, ppnRate, 10000n);
        const grand = dpp + nilaiPpn;
        const piutang = grand - bayar;
        const exceedsStorage = jumlah > 999999999999n || harga > 999999999999n || bayar > 999999999999n
            || subtotal > 999999999999n || grand > 999999999999n;
        const overpaid = bayar > grand;

        document.getElementById('subtotal_preview').value = money(subtotal);
        document.getElementById('dpp_preview').value = money(dpp);
        document.getElementById('ppn_preview').value = money(nilaiPpn);
        document.getElementById('grand_preview').value = money(grand);
        document.getElementById('piutang_preview').value = money(piutang > 0n ? piutang : 0n);
        warning.textContent = exceedsStorage
            ? 'Nilai invoice melebihi batas penyimpanan.'
            : overpaid ? 'Bayar tidak boleh lebih besar dari Grand Total.' : '';
        submit.disabled = exceedsStorage || overpaid;
    }

    numberFields.forEach(([id, scale]) => {
        const input = document.getElementById(id);
        const initial = toScaledInteger(input.value, scale, id === 'disc' || id === 'ppn' || id === 'bayar');
        if (initial !== null) input.value = formatScaled(initial, scale);
        input.addEventListener('input', recalculate);
        input.addEventListener('blur', () => {
            const value = toScaledInteger(input.value, scale, id === 'disc' || id === 'ppn' || id === 'bayar');
            if (value !== null) input.value = formatScaled(value, scale);
            recalculate();
        });
    });

    document.getElementById('boxing').addEventListener('change', recalculate);
    form.addEventListener('submit', event => {
        recalculate();
        if (submit.disabled) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });

    recalculate();
})();
</script>
@endpush
