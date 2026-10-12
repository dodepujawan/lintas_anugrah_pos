@extends('layouts.app')

@section('title', 'Detail Hutang - ' . $hutang->NOFAKTUR)

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="h5 mb-1">{{ $hutang->NOFAKTUR }}</h2>
                <p class="text-muted mb-0">{{ $hutang->supplier_display }}</p>
            </div>
            <div class="d-flex gap-2">
                @if (str_starts_with((string)$hutang->SOURCE, 'SALDO_AWAL_') && (float)$hutang->BAYAR <= 0 && (float)$hutang->RETUR <= 0 && (float)$hutang->DISCOUNT <= 0)
                    <a href="{{ route('saldo-awal-hutang.edit', $hutang) }}" class="btn btn-warning btn-sm">Edit Saldo Awal</a>
                    <form method="POST" action="{{ route('saldo-awal-hutang.destroy', $hutang) }}" onsubmit="return confirm('Hapus saldo awal hutang {{ addslashes($hutang->NOFAKTUR) }}? Data tidak dapat dikembalikan.');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm">Hapus</button></form>
                @elseif ($hutang->SOURCE === 'HUTANG_BERJALAN_MANUAL' && (float)$hutang->BAYAR <= 0 && (float)$hutang->RETUR <= 0 && (float)$hutang->DISCOUNT <= 0)
                    <a href="{{ route('hutang.edit', $hutang) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                    <form method="POST" action="{{ route('hutang.destroy', $hutang) }}" onsubmit="return confirm('Hapus hutang ini beserta jurnalnya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                    </form>
                @endif
                <a href="{{ route('hutang.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4"><div class="text-muted small">Tanggal Faktur</div><div class="fw-semibold">{{ $hutang->TGLFAKTUR->format('d M Y') }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Jatuh Tempo</div><div class="fw-semibold">{{ $hutang->TGLJT ? $hutang->TGLJT->format('d M Y') : '-' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Status</div><span class="badge {{ $hutang->SALDO <= 0 ? 'bg-success' : 'bg-warning text-dark' }}">{{ $hutang->SALDO <= 0 ? 'Lunas' : 'Open' }}</span></div>
            <div class="col-md-4"><div class="text-muted small">Hutang</div><div class="fw-semibold">{{ number_format($hutang->HUTANG, 0, ',', '.') }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Bayar</div><div class="fw-semibold">{{ number_format($hutang->BAYAR, 0, ',', '.') }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Saldo</div><div class="fw-semibold">{{ number_format($hutang->SALDO, 0, ',', '.') }}</div></div>
            <div class="col-md-6">
                <div class="text-muted small">No Jurnal</div>
                <div class="fw-semibold d-flex flex-wrap align-items-center gap-2">
                    @if ($journal)
                        <a href="{{ route('jurnal.show', ['jurnal' => $journal, 'from' => 'hutang', 'hutang_id' => $hutang->id]) }}">{{ $journal->no_jurnal }}</a>
                    @else
                        <span>{{ $hutang->NOJURNAL ?? '-' }}</span>
                    @endif

                    @if ($hutang->SOURCE === 'HUTANG_BERJALAN_MANUAL')
                        @if ($journalValid)
                            <span class="badge bg-success">Jurnal Valid</span>
                        @else
                            <span class="badge bg-danger">Jurnal Perlu Cek</span>
                        @endif
                    @endif
                </div>
                @if ($hutang->SOURCE === 'HUTANG_BERJALAN_MANUAL')
                    <div class="small mt-1">
                        <span class="{{ $journalChecks['exists'] ? 'text-success' : 'text-danger' }}">Jurnal ada</span>
                        <span class="text-muted">|</span>
                        <span class="{{ $journalChecks['balanced'] ? 'text-success' : 'text-danger' }}">Debit = Kredit</span>
                        <span class="text-muted">|</span>
                        <span class="{{ $journalChecks['reference_matches'] ? 'text-success' : 'text-danger' }}">Referensi cocok</span>
                        <span class="text-muted">|</span>
                        <span class="{{ $journalChecks['payable_matches'] ? 'text-success' : 'text-danger' }}">Kredit hutang cocok</span>
                    </div>
                    @if (! $journalChecks['payable_mapping_exists'])
                        <div class="small text-warning mt-1">Mapping akun Hutang Usaha belum tersedia.</div>
                    @endif
                @endif
            </div>
            <div class="col-12"><div class="text-muted small">Keterangan</div><div>{{ $hutang->KETERANGAN ?? '-' }}</div></div>
        </div>

        <hr class="my-4">

        <h6 class="mb-3">Riwayat Pembayaran</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Bayar</th>
                        <th>Tanggal</th>
                        <th class="text-end">Nilai Bayar</th>
                        <th class="text-end">Sisa Setelah Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($hutang->details as $detail)
                        <tr>
                            <td>
                                @if ($detail->header)
                                    <a href="{{ route('pelunasan-hutang.show', $detail->header->id) }}">{{ $detail->NOBAYAR }}</a>
                                @else
                                    {{ $detail->NOBAYAR }}
                                @endif
                            </td>
                            <td>{{ $detail->header?->TGLBAYAR ? $detail->header->TGLBAYAR->format('d M Y') : '-' }}</td>
                            <td class="text-end">{{ number_format($detail->NILAI_BAYAR, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($detail->SISA_FAKTUR, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-3">Belum ada pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
