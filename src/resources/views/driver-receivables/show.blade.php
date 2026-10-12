@extends('layouts.app')
@section('title','Detail Piutang Sopir - '.$row->number)
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="h5 mb-1">{{ $row->number }}</h1>
                <div class="text-muted">{{ $row->driver->code }} - {{ $row->driver->name }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('driver-receivables.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-4"><div class="text-muted small">Tanggal</div><div class="fw-semibold">{{ $row->date->format('d/m/Y') }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Jatuh Tempo</div><div class="fw-semibold">{{ $row->due_date?->format('d/m/Y') ?: '-' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Status</div><span class="badge text-bg-{{ $row->status === 'LUNAS' ? 'success' : 'warning' }}">{{ $row->status }}</span></div>
            <div class="col-md-4"><div class="text-muted small">Jenis</div><div class="fw-semibold">{{ str_replace('_',' ',$row->type) }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Nilai</div><div class="fw-semibold">{{ number_format($row->amount,2,',','.') }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Saldo</div><div class="fw-semibold">{{ number_format($row->balance,2,',','.') }}</div></div>
            <div class="col-md-6"><div class="text-muted small">Referensi</div><div class="fw-semibold">{{ $row->reference ?: '-' }}</div></div>
            <div class="col-md-6"><div class="text-muted small">Kendaraan</div><div class="fw-semibold">{{ $row->vehicle_number ?: '-' }}</div></div>
            <div class="col-12"><div class="text-muted small">Keterangan</div><div>{{ $row->description ?: '-' }}</div></div>
            <div class="col-12">
                <div class="text-muted small">Jurnal</div>
                <div class="fw-semibold d-flex flex-wrap align-items-center gap-2">
                    @if($journal)
                        <a href="{{ route('jurnal.show', ['jurnal' => $journal, 'from' => 'driver-receivable', 'driver_receivable_id' => $row->id]) }}">{{ $journal->no_jurnal }}</a>
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
                    <span class="{{ $journalChecks['amount_matches'] ? 'text-success' : 'text-danger' }}">Debit piutang sopir cocok</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
