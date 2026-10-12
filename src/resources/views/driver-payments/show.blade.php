@extends('layouts.app')
@section('title','Detail Pelunasan Piutang Sopir - '.$payment->number)
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="h5 mb-1">{{ $payment->number }}</h1>
                <div class="text-muted">{{ $payment->receivable->driver->code }} - {{ $payment->receivable->driver->name }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('driver-payments.pdf',$payment) }}" target="_blank" class="btn btn-outline-secondary btn-sm">Cetak</a>
                <a href="{{ route('driver-payments.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-4"><div class="text-muted small">Tanggal</div><div class="fw-semibold">{{ $payment->date->format('d/m/Y') }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Piutang</div><div class="fw-semibold">{{ $payment->receivable->number }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Status</div><span class="badge text-bg-{{ $payment->status === 'POSTED' ? 'success' : 'secondary' }}">{{ $payment->status }}</span></div>
            <div class="col-md-4"><div class="text-muted small">Metode</div><div class="fw-semibold">{{ str_replace('_',' ',$payment->method) }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Nilai Bayar</div><div class="fw-semibold">{{ number_format($payment->amount,2,',','.') }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Referensi</div><div class="fw-semibold">{{ $payment->reference ?: '-' }}</div></div>
            <div class="col-12"><div class="text-muted small">Keterangan</div><div>{{ $payment->description ?: '-' }}</div></div>
            <div class="col-12">
                <div class="text-muted small">Jurnal</div>
                <div class="fw-semibold d-flex flex-wrap align-items-center gap-2">
                    @if($journal)
                        <a href="{{ route('jurnal.show', ['jurnal' => $journal, 'from' => 'driver-payment', 'driver_payment_id' => $payment->id]) }}">{{ $journal->no_jurnal }}</a>
                    @else
                        <span>-</span>
                    @endif
                    @if($journalValid)<span class="badge bg-success">Jurnal Valid</span>@else<span class="badge bg-danger">Jurnal Perlu Cek</span>@endif
                </div>
                <div class="small mt-1">
                    <span class="{{ $journalChecks['exists'] ? 'text-success' : 'text-danger' }}">Jurnal ada</span>
                    <span class="text-muted">|</span>
                    <span class="{{ $journalChecks['balanced'] ? 'text-success' : 'text-danger' }}">Debit = Kredit</span>
                    <span class="text-muted">|</span>
                    <span class="{{ $journalChecks['reference_matches'] ? 'text-success' : 'text-danger' }}">Referensi cocok</span>
                    <span class="text-muted">|</span>
                    <span class="{{ $journalChecks['amount_matches'] ? 'text-success' : 'text-danger' }}">Kredit piutang sopir cocok</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
