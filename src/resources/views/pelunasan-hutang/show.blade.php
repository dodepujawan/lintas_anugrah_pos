@extends('layouts.app')

@section('title', 'Detail Pelunasan Hutang - ' . $baph->NOBAYAR)

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="h5 mb-1">{{ $baph->NOBAYAR }}</h2>
                        <p class="text-muted mb-0">Pelunasan Hutang</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if($baph->STATUS !== 'VOID')
                            <a href="{{ route('pelunasan-hutang.edit', $baph) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" action="{{ route('pelunasan-hutang.destroy', $baph) }}" class="d-inline" onsubmit="return confirm('Hapus pelunasan {{ $baph->NOBAYAR }}? Saldo hutang akan dikembalikan dan jurnal pelunasan akan dihapus.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm payment-delete-btn">
                                    <i class="bi bi-trash me-1"></i>Hapus
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('pelunasan-hutang.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
                    </div>
                </div>
                @if($baph->STATUS === 'VOID')<div class="alert alert-danger"><strong>Pelunasan dibatalkan.</strong><br>Alasan: {{ $baph->VOID_REASON }}<br>Dibatalkan: {{ $baph->VOIDED_AT?->format('d M Y H:i') }} oleh {{ $baph->voidedBy?->name ?? 'User' }}<br>Jurnal pembalik: {{ $baph->NOJURNAL_PEMBALIK }}</div>@endif
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><div class="text-muted small">Tanggal Bayar</div><div class="fw-semibold">{{ $baph->TGLBAYAR->format('d M Y') }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Supplier</div><div class="fw-semibold">{{ $baph->supplier_display }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Metode</div><span class="badge bg-info">{{ $baph->METODE_PEMBAYARAN }}</span></div>
                    <div class="col-md-6"><div class="text-muted small">Akun Pembayaran</div><div class="fw-semibold">{{ $baph->paymentCoa ? $baph->paymentCoa->kode_akun . ' - ' . $baph->paymentCoa->nama_akun : '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">No Referensi</div><div class="fw-semibold">{{ $baph->NO_REFERENSI ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Total Bayar</div><div class="fw-semibold">{{ number_format($baph->JUMLAH_BAYAR, 0, ',', '.') }}</div></div>
                    <div class="col-12"><div class="text-muted small">Keterangan</div><div>{{ $baph->KETERANGAN ?? '-' }}</div></div>
                    <div class="col-md-6">
                        <div class="text-muted small">No Jurnal</div>
                        <div class="fw-semibold d-flex flex-wrap align-items-center gap-2">
                            @if($journal)
                                <a href="{{ route('jurnal.show', ['jurnal' => $journal, 'from' => 'pelunasan-hutang', 'pelunasan_hutang_id' => $baph->id]) }}">{{ $journal->no_jurnal }}</a>
                            @else
                                <span>{{ $baph->NOJURNAL ?? '-' }}</span>
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
                            <span class="{{ $journalChecks['amount_matches'] ? 'text-success' : 'text-danger' }}">Debit hutang cocok</span>
                        </div>
                        @if(!$journalChecks['mapping_exists'])<div class="small text-warning mt-1">Mapping akun Hutang Usaha belum tersedia.</div>@endif
                    </div>
                </div>
                <hr>
                <h6 class="mb-3">Detail Pembayaran</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No. Faktur</th>
                                <th>Tanggal Faktur</th>
                                <th class="text-end">Saldo Faktur</th>
                                <th class="text-end">Nilai Bayar</th>
                                <th class="text-end">Sisa Faktur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bapd as $detail)
                                <tr>
                                    <td>{{ $detail->NOFAKTUR }}</td>
                                    <td>{{ $detail->TGLFAKTUR->format('d/m/Y') }}</td>
                                    <td class="text-end">{{ number_format($detail->SALDO_FAKTUR, 0, ',', '.') }}</td>
                                    <td class="text-end"><strong>{{ number_format($detail->NILAI_BAYAR, 0, ',', '.') }}</strong></td>
                                    <td class="text-end">{{ number_format($detail->SISA_FAKTUR, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">Tidak ada detail pembayaran</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .payment-delete-btn {
        opacity: .78;
    }

    .payment-delete-btn:hover,
    .payment-delete-btn:focus {
        opacity: 1;
    }
</style>
@endpush
