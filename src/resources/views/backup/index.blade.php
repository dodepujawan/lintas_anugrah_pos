@extends('layouts.app')

@section('title', 'Backup Database')

@section('content')
<div class="row mb-4">
    <div class="col-12 col-lg-8">
        <h2 class="mb-1"><i class="bi bi-hdd-stack"></i> Backup Database</h2>
        <p class="text-muted">Jalankan backup MySQL manual atau otomatis setiap Senin pukul 23:00.</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="mb-4">
            <h5 class="mb-1">Backup Manual</h5>
            <p class="text-muted mb-0">Tekan tombol di bawah untuk membuat atau mengganti backup database saat ini.</p>
        </div>

        <form method="POST" action="{{ route('backup.run') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-cloud-download"></i> Buat Backup Sekarang
            </button>
        </form>

        <div class="mt-4">
            <div class="alert alert-info">
                <strong>Catatan:</strong> Backup disimpan di server, bisa diunduh ulang, dan otomatis memakai
                collation <code>utf8mb4_unicode_ci</code> agar kompatibel saat diimpor ke database lokal.
            </div>
            <p class="mb-0">Lokasi penyimpanan: <code>storage/app/backups</code></p>
            <p class="mb-0">Backup memakai satu file bergulir. Backup sebelumnya dan file backup bertanggal lama untuk database ini akan dibersihkan saat backup baru berhasil dibuat.</p>
            <p class="mb-0">Scheduler Laravel perlu aktif agar backup otomatis mingguan berjalan.</p>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-4">
    <div class="card-body p-0">
        <div class="px-4 py-3 border-bottom bg-light">
            <h5 class="mb-0">Riwayat Backup</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama File</th>
                        <th>Ukuran</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                    <tr>
                        <td><code>{{ $file['name'] }}</code></td>
                        <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
                        <td>{{ $file['modified_at']->format('d/m/Y H:i:s') }}</td>
                        <td class="text-end">
                            <a href="{{ route('backup.download', ['file' => $file['name']]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada file backup.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
