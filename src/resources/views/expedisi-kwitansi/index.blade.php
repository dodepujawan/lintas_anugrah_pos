@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiExpedisiKwitansiIndex">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Kwitansi Expedisi</h1>
            <p class="text-muted small mb-0">Daftar kwitansi pembayaran</p>
        </div>
        <div class="col-auto">
            <button type="button" id="btnProsesKwitansiExpedisi" data-debug="transaksiExpedisiKwitansiProsesBtn" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#prosesKwitansiModal">
                <i class="bi bi-plus-lg"></i> Proses Kwitansi
            </button>
        </div>
    </div>

    <form method="GET" id="formFilterKwitansiExpedisi" data-debug="transaksiExpedisiKwitansiFilter" class="mb-3">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Cari No. KWT / Invoice..." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
            <a href="{{ route('expedisi-kwitansi.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>No. KWT</th>
                        <th>Tgl KWT</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th class="text-end">Saldo Faktur</th>
                        <th class="text-end">Bayar</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kwitantis as $k)
                    <tr>
                        <td>{{ $kwitantis->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $k->NO_KWT }}</td>
                        <td>{{ $k->TGL_KWT?->format('d/m/Y') }}</td>
                        <td class="font-monospace">{{ $k->NOFAKTUR }}</td>
                        <td>{{ $k->CUSTOMER }}</td>
                        <td class="text-end">{{ number_format((float) $k->SALDO_FAKTUR, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $k->NILAI_BAYAR, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) ($k->SISA_FAKTUR ?? ($k->SALDO_FAKTUR - $k->NILAI_BAYAR)), 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a href="{{ route('expedisi-kwitansi.printKwitansi', $k) }}" id="btnPrintKwitansi_{{ $k->NO_KWT }}" data-debug="transaksiExpedisiKwitansiPrint_{{ $k->NO_KWT }}" class="btn btn-sm btn-outline-primary" title="Print" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                            <form action="{{ route('expedisi-kwitansi.destroy', $k) }}" method="POST"
                                  onsubmit="return confirm('Hapus kwitansi ini?')" data-debug="transaksiExpedisiKwitansiDelete_{{ $k->NO_KWT }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data kwitansi.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="prosesKwitansiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="kwitansiForm" method="POST" action="{{ route('expedisi-kwitansi.proses') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Proses Kwitansi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">No Invoice <span class="text-danger">*</span></label>
                            <input type="text" name="NOFAKTUR" class="form-control form-control-sm" required list="invoiceList">
                            <datalist id="invoiceList">
                                @foreach(\App\Models\Arh::where('STATUS','OPEN')->get() as $arh)
                                <option value="{{ $arh->NOFAKTUR }}">{{ $arh->NOFAKTUR }} — {{ $arh->CUSTOMER }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tgl KWT <span class="text-danger">*</span></label>
                            <input type="date" name="TGL_KWT" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nilai Bayar <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="NILAI_BAYAR" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <input type="text" name="METODE_PEMBAYARAN" class="form-control form-control-sm" value="TUNAI">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="KETERANGAN" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
