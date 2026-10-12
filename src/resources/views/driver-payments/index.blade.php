@extends('layouts.app')
@section('title','Pelunasan Piutang Sopir')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
    <h1 class="h4 mb-0">Pelunasan Piutang Sopir</h1>
    <div class="d-flex gap-2 align-self-sm-start">
        <a href="{{ route('panduan.piutang_sopir') }}" class="btn btn-outline-info"><i class="bi bi-book me-1"></i>Panduan</a>
        <a href="{{ route('driver-payments.create') }}" class="btn btn-primary">+ Input Pelunasan</a>
    </div>
</div>

<form class="card border-0 shadow-sm mb-3" method="GET">
    <div class="card-body p-2">
        <div class="input-group">
            <input name="search" value="{{ $search }}" class="form-control" placeholder="Cari no bayar, piutang, sopir, metode, referensi, atau keterangan">
            <button class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari</button>
            @if($search !== '')
                <a href="{{ route('driver-payments.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No Bayar</th>
                    <th>No Jurnal</th>
                    <th>Piutang</th>
                    <th>Sopir</th>
                    <th>Metode</th>
                    <th class="text-end">Nilai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $p)
                    <tr>
                        <td class="text-nowrap">{{ $p->date->format('d/m/Y') }}</td>
                        <td class="fw-semibold text-nowrap">{{ $p->number }}</td>
                        <td class="text-nowrap">
                            @if($p->journal)
                                <a href="{{ route('jurnal.show', ['jurnal' => $p->journal, 'from' => 'driver-payment', 'driver_payment_id' => $p->id]) }}">{{ $p->journal->no_jurnal }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-nowrap">{{ $p->receivable->number }}</td>
                        <td>{{ $p->receivable->driver->name }}</td>
                        <td>{{ str_replace('_',' ',$p->method) }}</td>
                        <td class="text-end text-nowrap">{{ number_format($p->amount,2,',','.') }}</td>
                        <td><span class="badge text-bg-{{ $p->status==='POSTED'?'success':'secondary' }}">{{ $p->status }}</span></td>
                        <td class="text-nowrap">
                            <a href="{{ route('driver-payments.show',$p) }}" class="btn btn-outline-primary btn-sm">Detail</a>
                            <a href="{{ route('driver-payments.pdf',$p) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Cetak</a>
                            @if($p->status==='POSTED')
                                <a href="{{ route('driver-payments.edit',$p) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form method="POST" action="{{ route('driver-payments.void',$p) }}" class="d-inline" onsubmit="return confirm('Hapus pelunasan {{ $p->number }}? Saldo piutang sopir akan dikembalikan dan jurnal pembalik akan dibuat.')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-4">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $rows->links() }}</div>
@endsection
