@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Kwitansi History</h1>
            <p class="text-muted small mb-0">Riwayat seluruh kwitansi (EKS, REN, Coolroom)</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('kwitansi-history.export') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari KWT / Invoice..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="jenis" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="Expedisi" {{ request('jenis') == 'Expedisi' ? 'selected' : '' }}>EKS</option>
                    <option value="Rent" {{ request('jenis') == 'Rent' ? 'selected' : '' }}>Rent</option>
                    <option value="Coolroom" {{ request('jenis') == 'Coolroom' ? 'selected' : '' }}>Coolroom</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>No. KWT</th>
                        <th>Tgl</th>
                        <th>Invoice</th>
                        <th>Jenis</th>
                        <th class="text-end">Bayar</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kwitantis as $k)
                    <tr>
                        <td>{{ $kwitantis->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $k->NO_KWT }}</td>
                        <td>{{ $k->TGL_KWT?->format('d/m/Y') }}</td>
                        <td class="font-monospace">{{ $k->NOFAKTUR }}</td>
                        <td>{{ $k->JENIS_KWT }}</td>
                        <td class="text-end">{{ number_format((float) $k->NILAI_BAYAR, 0, ',', '.') }}</td>
                        <td>{{ $k->KETERANGAN }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2">Tidak ada data kwitansi.</p>
                    </td></tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="fw-bold">TOTAL BAYAR:</td>
                        <td class="text-end fw-bold">{{ number_format($totalBayar, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $kwitantis->links() }}</div>
</div>
@endsection
