@extends('layouts.app')

@section('title', 'Transaksi Coolroom')

@section('content')
<div class="container-fluid" data-debug="transaksiCoolroomIndex">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="status">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="small text-uppercase fw-semibold text-primary mb-1">Transaksi / Cold Storage</div>
            <h1 class="page-title mb-1">Transaksi Coolroom</h1>
            <p class="text-muted mb-0">Kelola surat jalan Coolroom yang belum dibuatkan invoice.</p>
        </div>
        <a href="{{ route('coolroom.create') }}" class="btn btn-primary" data-debug="transaksiCoolroomIndexCreate">
            <i class="bi bi-plus-lg me-1"></i>Transaksi Baru
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('coolroom.index') }}" class="row g-2 align-items-end" data-debug="transaksiCoolroomIndexSearch">
                <div class="col-md-7 col-lg-5">
                    <label for="coolroomSearch" class="form-label small fw-semibold">Cari surat jalan atau customer</label>
                    <input type="search" id="coolroomSearch" name="search" class="form-control" value="{{ $search }}" placeholder="Nomor SJ, kode, atau nama customer">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                    @if ($search !== '')
                        <a href="{{ route('coolroom.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
                <div class="col-md text-md-end small text-muted">{{ $coolrooms->total() }} surat jalan belum invoice</div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>No. SJ</th>
                        <th>Tanggal SJ</th>
                        <th>Customer</th>
                        <th class="text-end">Jumlah KG</th>
                        <th class="text-end">Harga @</th>
                        <th class="text-end">Grand</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coolrooms as $coolroom)
                        <tr>
                            <td class="ps-3">{{ $coolrooms->firstItem() + $loop->index }}</td>
                            <td><a class="font-monospace fw-semibold text-decoration-none" href="{{ route('coolroom.show', $coolroom) }}">{{ $coolroom->NOSJ }}</a></td>
                            <td>{{ $coolroom->TGLSJ?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $coolroom->CUSTOMER }}</div>
                                <div class="small text-muted">{{ $coolroom->CUSTOMER_KODE }}</div>
                            </td>
                            <td class="text-end">{{ number_format((float) $coolroom->JUMLAH, 3, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) $coolroom->HARGA, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">{{ number_format((float) $coolroom->GRAND, 0, ',', '.') }}</td>
                            <td class="text-end text-nowrap pe-3">
                                <a href="{{ route('coolroom.edit', $coolroom) }}" class="btn btn-sm btn-outline-primary" data-debug="transaksiCoolroomIndexEdit_{{ $coolroom->NOSJ }}" title="Edit transaksi">
                                    <i class="bi bi-pencil-square"></i><span class="visually-hidden">Edit</span>
                                </a>
                                <a href="{{ route('coolroom.pdf', $coolroom->NOSJ) }}" class="btn btn-sm btn-outline-secondary" data-debug="transaksiCoolroomIndexPdf_{{ $coolroom->NOSJ }}" target="_blank" rel="noopener" title="Cetak Surat Jalan">
                                    <i class="bi bi-printer"></i><span class="visually-hidden">Cetak</span>
                                </a>
                                <form method="POST" action="{{ route('coolroom.destroy', $coolroom) }}" class="d-inline" onsubmit="return confirm('Hapus transaksi Coolroom {{ $coolroom->NOSJ }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-debug="transaksiCoolroomIndexDelete_{{ $coolroom->NOSJ }}" title="Hapus transaksi">
                                        <i class="bi bi-trash"></i><span class="visually-hidden">Hapus</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-snow2 fs-2 d-block mb-2"></i>
                                @if ($search !== '')
                                    Tidak ada transaksi yang cocok dengan pencarian.
                                @else
                                    Belum ada surat jalan Coolroom yang menunggu invoice.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $coolrooms->links() }}</div>
</div>
@endsection
