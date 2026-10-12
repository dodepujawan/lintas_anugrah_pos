@extends('layouts.app')

@section('title', 'Audit Trail')

@section('content')
<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="page-title"><i class="bi bi-clock-history me-2"></i>Audit Trail</h2>
            <small class="text-muted">Riwayat aktivitas penting di aplikasi.</small>
        </div>
        <div>
            <a href="{{ route('audit-logs.export', request()->query()) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label" for="module">Module</label>
                <select class="form-select" id="module" name="module">
                    <option value="">Semua</option>
                    @foreach($modules as $module)
                    <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>{{ $module }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="action">Aksi</label>
                <select class="form-select" id="action" name="action">
                    <option value="">Semua</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="user_id">User</label>
                <select class="form-select" id="user_id" name="user_id">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                        {{ $user->name }}{{ $user->username ? ' (' . $user->username . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="tanggal_awal">Tanggal Awal</label>
                <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="tanggal_akhir">Tanggal Akhir</label>
                <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Tampilkan</button>
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
                        <th class="px-3">Waktu</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>Metadata</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="px-3">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $log->user?->name ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $log->module }}</span></td>
                        <td><span class="badge bg-primary">{{ $log->action }}</span></td>
                        <td>{{ $log->description ?: '-' }}</td>
                        <td>
                            @if($log->metadata)
                            <code class="small">{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE) }}</code>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada audit trail.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
