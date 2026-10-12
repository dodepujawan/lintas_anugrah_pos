@extends('layouts.app')

@section('title', 'Detail Akun')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2><i class="bi bi-eye"></i> Detail Akun</h2>
        <div>
            <a href="{{ route('coa.edit', $coa->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('coa.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Akun</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Kode Akun</th>
                        <td>{{ $coa->kode_akun }}</td>
                    </tr>
                    <tr>
                        <th>Nama Akun</th>
                        <td>{{ $coa->nama_akun }}</td>
                    </tr>
                    <tr>
                        <th>Level</th>
                        <td>
                            <span class="badge bg-{{ $coa->level_badge_class }} text-uppercase">{{ $coa->level }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Parent Akun</th>
                        <td>{{ $coa->parent?->kode_akun ? $coa->parent->kode_akun . ' - ' . $coa->parent->nama_akun : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tipe</th>
                        <td>
                            <span class="badge bg-{{ $coa->tipe == 'Aset' ? 'primary' : ($coa->tipe == 'Kewajiban' ? 'danger' : ($coa->tipe == 'Ekuitas' ? 'success' : ($coa->tipe == 'Pendapatan' ? 'info' : 'warning'))) }}">
                                {{ $coa->tipe }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td>{{ $coa->kategori ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Saldo Normal</th>
                        <td>{{ $coa->saldo_normal_label }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($coa->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Sub Akun</th>
                        <td>{{ $coa->children()->count() }}</td>
                    </tr>
                    @if($coa->deskripsi)
                    <tr>
                        <th>Deskripsi</th>
                        <td>{{ $coa->deskripsi }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Riwayat Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>No. Jurnal</th>
                                <th>Tanggal</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurnalItems as $item)
                            <tr>
                                <td><a href="{{ route('jurnal.show', $item->jurnal->id) }}">{{ $item->jurnal->no_jurnal }}</a></td>
                                <td>{{ $item->jurnal->tanggal->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($item->debit, 2) }}</td>
                                <td class="text-end">{{ number_format($item->kredit, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada transaksi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection