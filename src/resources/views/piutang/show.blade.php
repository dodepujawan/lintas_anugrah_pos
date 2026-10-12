@extends('layouts.app')

@section('title', 'Detail Piutang')

@section('content')
@php($paymentDetails = $piutang->paymentDetails ?? collect())
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="h5 mb-1">{{ $piutang->CUSTOMER }} - {{ $piutang->CUSTOMER_NAME ?: 'Nama customer belum tersedia' }}</h2>
                        <p class="text-muted mb-0">{{ $piutang->KETERANGAN ?? 'Saldo awal piutang' }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if(str_starts_with((string)$piutang->SOURCE, 'SALDO_AWAL_') && (float)$piutang->BAYAR <= 0 && (float)$piutang->RETUR <= 0 && (float)$piutang->DISCOUNT <= 0)
                        <a href="{{ route('piutang.edit', $piutang) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form method="POST" action="{{ route('piutang.destroy', $piutang) }}" class="d-inline" onsubmit="return confirm('Yakin menghapus saldo awal piutang {{ addslashes($piutang->NOFAKTUR) }}? Data tidak dapat dikembalikan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                        @endif
                        <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm">{{ $backLabel }}</a>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Tanggal Faktur</div>
                        <div class="fw-semibold">{{ $piutang->TGLFAKTUR->format('d M Y') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Nomor Faktur</div>
                        <div class="fw-semibold">{{ $piutang->NOFAKTUR }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Tanggal Jatuh Tempo</div>
                        <div class="fw-semibold">{{ $piutang->TGLJT ? $piutang->TGLJT->format('d M Y') : '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Pelanggan</div>
                        <div class="fw-semibold">{{ $piutang->CUSTOMER }} - {{ $piutang->CUSTOMER_NAME ?: 'Nama customer belum tersedia' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Nilai Piutang</div>
                        <div class="fw-semibold">{{ number_format($piutang->PIUTANG, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Sisa Piutang</div>
                        <div class="fw-semibold">{{ number_format($piutang->SALDO, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Sudah Dibayar</div>
                        <div class="fw-semibold">{{ number_format($piutang->BAYAR, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Status</div>
                        <div>
                            @if ($piutang->SALDO <= 0)
                                <button type="button" class="badge border-0 bg-success payment-status-badge" data-bs-toggle="modal" data-bs-target="#paymentDetailModal" title="Klik untuk lihat detail pembayaran">
                                    <i class="bi bi-check-circle me-1"></i>Lunas
                                </button>
                                <span class="small text-success ms-2">Terbayar lunas</span>
                            @else
                                <button type="button" class="badge border-0 bg-warning text-dark payment-status-badge" data-bs-toggle="modal" data-bs-target="#paymentDetailModal" title="Klik untuk lihat detail pembayaran">
                                    <i class="bi bi-clock-history me-1"></i>Belum Lunas
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="text-muted small">Jurnal</div>
                        <div class="fw-semibold d-flex flex-wrap align-items-center gap-2">
                            @if ($journal)
                                <a href="{{ route('jurnal.show', ['jurnal' => $journal, 'from' => 'piutang', 'piutang_id' => $piutang->id, 'piutang_from' => $fromPiutangBerjalan ? 'piutang-berjalan' : 'piutang']) }}">{{ $journal->no_jurnal }}</a>
                            @else
                                <span>{{ $piutang->NOJURNAL ?: '-' }}</span>
                            @endif

                            @if (! str_starts_with((string) $piutang->SOURCE, 'SALDO_AWAL_'))
                                @if ($journalValid)
                                    <span class="badge bg-success">Jurnal Valid</span>
                                @else
                                    <span class="badge bg-danger">Jurnal Perlu Cek</span>
                                @endif
                            @endif
                        </div>
                        @if (! str_starts_with((string) $piutang->SOURCE, 'SALDO_AWAL_'))
                            <div class="small mt-1">
                                <span class="{{ $journalChecks['exists'] ? 'text-success' : 'text-danger' }}">Jurnal ada</span>
                                <span class="text-muted">|</span>
                                <span class="{{ $journalChecks['balanced'] ? 'text-success' : 'text-danger' }}">Debit = Kredit</span>
                                <span class="text-muted">|</span>
                                <span class="{{ $journalChecks['reference_matches'] ? 'text-success' : 'text-danger' }}">Referensi cocok</span>
                                <span class="text-muted">|</span>
                                <span class="{{ $journalChecks['receivable_matches'] ? 'text-success' : 'text-danger' }}">Debit piutang cocok</span>
                            </div>
                            @if (! $journalChecks['receivable_mapping_exists'])
                                <div class="small text-warning mt-1">Mapping akun Piutang Usaha belum tersedia.</div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <p class="text-muted small mb-2">Untuk melunasi piutang ini, gunakan menu <strong>Pelunasan Piutang</strong>.</p>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentDetailModal">
                        <i class="bi bi-receipt me-1"></i>Lihat Detail Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentDetailModal" tabindex="-1" aria-labelledby="paymentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><h5 class="modal-title" id="paymentDetailModalLabel">Detail Pembayaran {{ $piutang->NOFAKTUR }}</h5><div class="small text-muted">{{ $piutang->CUSTOMER }} - {{ $piutang->CUSTOMER_NAME ?: 'Nama customer belum tersedia' }}</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3"><div class="text-muted small">Piutang Awal</div><div class="fw-semibold">{{ number_format($piutang->PIUTANG, 0, ',', '.') }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Sudah Dibayar</div><div class="fw-semibold">{{ number_format($piutang->BAYAR, 0, ',', '.') }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Total Penyelesaian</div><div class="fw-semibold">{{ number_format($piutang->PAYMENT_SETTLED_TOTAL ?? 0, 0, ',', '.') }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Sisa</div><div class="fw-semibold {{ $piutang->SALDO <= 0 ? 'text-success' : 'text-primary' }}">{{ number_format($piutang->SALDO, 0, ',', '.') }}</div></div>
            </div>
            @if($paymentDetails->isNotEmpty())
            <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0"><thead class="table-light"><tr><th>No. Bayar</th><th>Tanggal</th><th>Metode</th><th>Akun Bayar</th><th>Referensi</th><th class="text-end">Bayar</th><th class="text-end">PPh 23</th><th class="text-end">Potongan</th><th class="text-end">Adm.</th><th class="text-end">Sisa Faktur</th><th></th></tr></thead><tbody>
                @foreach($paymentDetails as $detail)
                @php($header = $detail->header)
                <tr><td class="fw-semibold">{{ $detail->NOBAYAR }}</td><td>{{ $header?->TGLBAYAR?->format('d/m/Y') ?: '-' }}</td><td>{{ $header?->METODE_PEMBAYARAN ?: '-' }}</td><td>{{ $header?->paymentCoa ? $header->paymentCoa->kode_akun.' - '.$header->paymentCoa->nama_akun : '-' }}</td><td>{{ $header?->NO_REFERENSI ?: '-' }}</td><td class="text-end">{{ number_format($detail->NILAI_BAYAR, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->PPH23, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->DISCOUNT, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->BANK_ADMIN, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->SISA_FAKTUR, 0, ',', '.') }}</td><td class="text-end">@if($header)<a href="{{ route('pelunasan-piutang.show', $header->id) }}" class="btn btn-sm btn-outline-primary">Bukti</a>@endif</td></tr>
                @endforeach
            </tbody></table></div>
            @else
            <div class="alert alert-info mb-0">Belum ada detail pembayaran untuk faktur ini.</div>
            @endif
        </div>
    </div></div>
</div>
@endsection

@push('styles')
<style>
    .payment-status-badge { cursor: pointer; font-size: .78rem; }
</style>
@endpush
