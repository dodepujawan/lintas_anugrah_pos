@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Ekspedisi (EKS)</h1>
            <p class="text-muted small mb-0">Daftar surat jalan ekspedisi</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('expedisi.print.muat') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-pdf"></i> Print Muat
            </a>
            <a href="{{ route('expedisi.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah SJ
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari NOSJ / Invoice / Customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date" class="form-control form-control-sm"
                       value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="OPEN" {{ request('status') == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                    <option value="CLOSE" {{ request('status') == 'CLOSE' ? 'selected' : '' }}>CLOSE</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('expedisi.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NOSJ</th>
                        <th>Tgl Muat</th>
                        <th>Customer</th>
                        <th>Kendaraan</th>
                        <th class="text-end">Grand Total</th>
                        <th>Invoice</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expedisis as $e)
                    <tr>
                        <td>{{ $expedisis->firstItem() + $loop->index }}</td>
                        <td class="font-monospace">{{ $e->NOSJ }}</td>
                        <td>{{ $e->TGLMUAT?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $e->CUSTOMER }}</td>
                        <td>{{ $e->NAMA_KENDARAAN ?? $e->KENDARAAN }}</td>
                        <td class="text-end">{{ number_format((float) $e->GRAND, 0, ',', '.') }}</td>
                        <td class="font-monospace">{{ $e->INVOICE ?? '-' }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('expedisi.print.surjal', $e) }}" class="btn btn-outline-primary" title="Print SJ">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="{{ route('expedisi.show', $e) }}" class="btn btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('expedisi.edit', $e) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('expedisi.destroy', $e) }}" method="POST"
                                      onsubmit="return confirm('Hapus ekspedisi ini? Data yang sudah diinvoice tidak bisa dihapus.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-2">Tidak ada data ekspedisi.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $expedisis->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Search filter otomatis
        document.querySelector('input[name="search"]').addEventListener('input', function(e) {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => {
                this.closest('form').requestSubmit();
            }, 500);
        });
    });
</script>
@endpush
