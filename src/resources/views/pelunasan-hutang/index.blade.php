@extends('layouts.app')

@section('title', 'Daftar Pelunasan Hutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Daftar Pelunasan Hutang</h1>
        <p class="text-muted mb-0">Pembayaran hutang supplier.</p>
    </div>
    <a href="{{ route('pelunasan-hutang.create') }}" class="btn btn-primary">Tambah Pelunasan</a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Bukti</th>
                        <th>Tanggal</th>
                        <th>Jurnal</th>
                        <th>Supplier</th>
                        <th>Referensi</th>
                        <th>Metode</th>
                        <th class="text-end">Jumlah Bayar</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $payment)
                        <tr>
                            <td><strong>{{ $payment->NOBAYAR }}</strong></td>
                            <td>{{ $payment->TGLBAYAR->format('d M Y') }}</td>
                            <td>
                                @if($payment->journal)
                                    <a href="{{ route('jurnal.show', ['jurnal' => $payment->journal, 'from' => 'pelunasan-hutang', 'pelunasan_hutang_id' => $payment->id]) }}" class="badge text-bg-primary text-decoration-none">
                                        {{ $payment->NOJURNAL }}
                                    </a>
                                @elseif($payment->NOJURNAL)
                                    <span class="badge text-bg-warning">{{ $payment->NOJURNAL }}</span>
                                @else
                                    <span class="badge text-bg-danger">Belum Jurnal</span>
                                @endif
                            </td>
                            <td>{{ $payment->supplier_display }}</td>
                            <td>{{ $payment->NO_REFERENSI ?? '-' }}</td>
                            <td><span class="badge bg-info">{{ $payment->METODE_PEMBAYARAN }}</span></td>
                            <td class="text-end">{{ number_format($payment->JUMLAH_BAYAR, 0, ',', '.') }}</td>
                            <td><span class="badge bg-{{ $payment->STATUS === 'VOID' ? 'danger' : 'success' }}">{{ $payment->STATUS === 'VOID' ? 'Dibatalkan' : 'Aktif' }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('pelunasan-hutang.show', $payment->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                @if($payment->STATUS !== 'VOID')
                                    <a href="{{ route('pelunasan-hutang.edit', $payment->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form method="POST" action="{{ route('pelunasan-hutang.destroy', $payment->id) }}" class="d-inline" onsubmit="return confirm('Hapus pelunasan {{ $payment->NOBAYAR }}? Saldo hutang akan dikembalikan dan jurnal pelunasan akan dihapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger payment-delete-btn" title="Hapus pelunasan" aria-label="Hapus pelunasan {{ $payment->NOBAYAR }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4">Belum ada data pelunasan hutang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $data->links() }}
</div>
@endsection

@push('styles')
<style>
    .payment-delete-btn {
        --bs-btn-padding-x: .42rem;
        --bs-btn-padding-y: .24rem;
        opacity: .72;
    }

    .payment-delete-btn:hover,
    .payment-delete-btn:focus {
        opacity: 1;
    }
</style>
@endpush
