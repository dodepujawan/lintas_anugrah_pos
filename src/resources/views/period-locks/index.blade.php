@extends('layouts.app')

@section('title', 'Lock Periode')

@section('content')
<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="page-title"><i class="bi bi-lock-fill me-2"></i>Lock Periode</h2>
            <small class="text-muted">Kunci tahun yang sudah final agar transaksi periode lama tidak berubah.</small>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('period-locks.store') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-3">
                <label for="year" class="form-label">Tahun</label>
                <input type="number" class="form-control" id="year" name="year" min="2000" max="2100" value="{{ old('year', now()->year) }}" required>
            </div>
            <div class="col-md-6">
                <label for="notes" class="form-label">Catatan</label>
                <input type="text" class="form-control" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Opsional">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-lock me-1"></i> Kunci Tahun
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Tahun</th>
                        <th>Status</th>
                        <th>Dikunci Pada</th>
                        <th>Catatan</th>
                        <th class="text-end px-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($years as $year)
                    @php
                    $lock = $locks[(string) $year] ?? null;
                    $isLocked = (bool) ($lock?->closed_at);
                    @endphp
                    <tr>
                        <td class="px-3 fw-bold">{{ $year }}</td>
                        <td>
                            <span class="badge bg-{{ $isLocked ? 'danger' : 'success' }}">
                                {{ $isLocked ? 'Terkunci' : 'Terbuka' }}
                            </span>
                        </td>
                        <td>{{ $lock?->closed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $lock?->notes ?: '-' }}</td>
                        <td class="text-end px-3">
                            @if($isLocked)
                            <form method="POST" action="{{ route('period-locks.destroy', $year) }}" class="d-inline" onsubmit="return confirm('Buka kunci tahun {{ $year }}? Transaksi tahun ini akan bisa diubah lagi.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="bi bi-unlock me-1"></i> Buka Kunci
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('period-locks.store') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="year" value="{{ $year }}">
                                <input type="hidden" name="notes" value="Dikunci dari daftar periode.">
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-lock me-1"></i> Kunci
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
