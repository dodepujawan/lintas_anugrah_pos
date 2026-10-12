@extends('layouts.app')

@section('title', 'Import Data')

@section('content')
@php
    $guides = [
        'penjualan' => [
            'label' => 'Periode berjalan',
            'color' => 'primary',
            'use' => 'Invoice penjualan sejak mulai memakai aplikasi ini.',
            'date' => 'Tanggal invoice harus sama dengan atau sesudah tanggal cutover.',
            'result' => 'Membuat master customer bila belum ada, piutang, dan jurnal penjualan.',
        ],
        'hutang' => [
            'label' => 'Periode berjalan',
            'color' => 'primary',
            'use' => 'Faktur pembelian/hutang sejak mulai memakai aplikasi ini.',
            'date' => 'Tanggal faktur harus sama dengan atau sesudah tanggal cutover.',
            'result' => 'Membuat master supplier bila belum ada, hutang, dan jurnal pembelian.',
        ],
        'saldo_piutang' => [
            'label' => 'Saldo awal',
            'color' => 'warning',
            'use' => 'Invoice lama yang masih mempunyai sisa piutang saat pindah aplikasi.',
            'date' => 'Tanggal invoice harus sebelum tanggal cutover.',
            'result' => 'Membuat master customer bila belum ada dan saldo piutang tanpa jurnal baru.',
        ],
        'saldo_hutang' => [
            'label' => 'Saldo awal',
            'color' => 'warning',
            'use' => 'Faktur lama yang masih mempunyai sisa hutang saat pindah aplikasi.',
            'date' => 'Tanggal faktur harus sebelum tanggal cutover.',
            'result' => 'Membuat master supplier bila belum ada dan saldo hutang tanpa jurnal baru.',
        ],
    ];
@endphp

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-start gap-2">
        <div>
        <h2 class="page-title mb-1"><i class="bi bi-cloud-arrow-up me-2"></i>Import Data</h2>
        <p class="text-muted mb-0">Pindahkan transaksi dan saldo yang masih berjalan dari aplikasi desktop.</p>
        </div>
        <a href="{{ route('panduan.transaksi', 'import-data') }}" class="btn btn-outline-info"><i class="bi bi-book me-1"></i>Panduan</a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-2"><i class="bi bi-x-circle me-1"></i>Import belum diproses. Perbaiki hal berikut:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('import_result'))
    @php $result = session('import_result'); @endphp
    <div class="alert alert-success shadow-sm">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <div class="fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Import berhasil</div>
                <div>{{ $result['count'] }} baris sudah disimpan. Gunakan tombol berikut untuk memeriksa hasilnya.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ $result['list_url'] }}" class="btn btn-success btn-sm">
                    <i class="bi bi-list-ul me-1"></i>{{ $result['list_label'] }}
                </a>
                <a href="{{ $result['report_url'] }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i>{{ $result['report_label'] }}
                </a>
                @if ($result['journal_url'])
                    <a href="{{ $result['journal_url'] }}" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-journal-text me-1"></i>Lihat Jurnal Otomatis
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="mb-3"><i class="bi bi-list-ol me-2 text-primary"></i>Urutan cara import</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <span class="badge rounded-pill bg-primary align-self-start">1</span>
                    <div><strong>Pilih jenis data</strong><div class="small text-muted">Bedakan transaksi berjalan dan saldo awal.</div></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <span class="badge rounded-pill bg-primary align-self-start">2</span>
                    <div><strong>Unduh template</strong><div class="small text-muted">Gunakan template pada kartu yang dipilih.</div></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <span class="badge rounded-pill bg-primary align-self-start">3</span>
                    <div><strong>Isi file Excel</strong><div class="small text-muted">Jangan mengubah judul atau urutan kolom.</div></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <span class="badge rounded-pill bg-primary align-self-start">4</span>
                    <div><strong>Unggah dan import</strong><div class="small text-muted">Pilih cutover dan file, lalu validasi.</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info d-flex align-items-start gap-2">
    <i class="bi bi-info-circle-fill mt-1"></i>
    <div>
        <strong>Apa itu tanggal cutover?</strong>
        <div>Tanggal cutover adalah hari pertama transaksi mulai dicatat di aplikasi ini. Contoh: jika mulai memakai aplikasi pada <strong>1 Juli 2026</strong>, isi cutover <strong>01-07-2026</strong> di semua jenis import.</div>
        <div class="small mt-1">Transaksi berjalan harus bertanggal 1 Juli 2026 atau sesudahnya. Saldo awal harus berasal dari faktur sebelum 1 Juli 2026.</div>
    </div>
</div>

<div class="row g-4">
    @foreach($templates as $type => $template)
        @php $guide = $guides[$type]; @endphp
        <div class="col-lg-6" id="import-{{ $type }}">
            <div class="card h-100 shadow-sm border-top border-4 border-{{ $guide['color'] }} {{ old('import_type') === $type ? 'border-primary' : '' }}">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $guide['color'] }} text-white fw-bold fs-4 flex-shrink-0"
                              style="width: 48px; height: 48px;" aria-label="Nomor {{ $loop->iteration }}">
                            {{ $loop->iteration }}
                        </span>
                        <div>
                            <div class="small text-muted fw-semibold text-uppercase">Jenis Import {{ $loop->iteration }}</div>
                            <h4 class="mb-1 fw-bold text-dark">{{ $template['title'] }}</h4>
                            <span class="badge bg-{{ $guide['color'] }}">{{ $guide['label'] }}</span>
                        </div>
                        <i class="{{ $template['icon'] }} fs-2 text-{{ $guide['color'] }} ms-auto d-none d-sm-block"></i>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row small mb-3">
                        <dt class="col-sm-3">Gunakan untuk</dt>
                        <dd class="col-sm-9">{{ $guide['use'] }}</dd>
                        <dt class="col-sm-3">Aturan tanggal</dt>
                        <dd class="col-sm-9">{{ $guide['date'] }}</dd>
                        <dt class="col-sm-3">Hasil import</dt>
                        <dd class="col-sm-9 mb-0">{{ $guide['result'] }}</dd>
                    </dl>

                    <div class="alert alert-light border small py-2">
                        <i class="bi bi-shield-check me-1 text-success"></i>
                        Nomor invoice/faktur yang sudah ada akan ditolak agar tidak terjadi data ganda. Jika satu baris gagal, seluruh file dibatalkan.
                    </div>

                    <div class="d-grid mb-3">
                        <a href="{{ route('import-data.template', $type) }}" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-excel me-1"></i> 1. Unduh Template Excel
                        </a>
                        <div class="form-text text-center">Contoh kode akun diambil dari COA aktif saat template diunduh.</div>
                    </div>

                    <form method="POST" action="{{ route('import-data.store', $type) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="import_type" value="{{ $type }}">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold" for="cutover-{{ $type }}">2. Tanggal cutover</label>
                                <input type="date" id="cutover-{{ $type }}" name="cutover_date"
                                       value="{{ old('import_type') === $type ? old('cutover_date', $defaultCutoverDate) : $defaultCutoverDate }}"
                                       class="form-control" required>
                                <div class="form-text">Gunakan tanggal yang sama untuk semua import.</div>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold" for="file-{{ $type }}">3. Pilih file yang sudah diisi</label>
                                <input type="file" id="file-{{ $type }}" name="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                                <div class="form-text">Format: XLSX, XLS, atau CSV. Maksimal 10 MB.</div>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 mt-3" type="submit">
                            <i class="bi bi-check2-circle me-1"></i> 4. Validasi dan Import {{ $guide['label'] }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-warning mt-4">
    <div class="card-body">
        <h6 class="text-warning-emphasis"><i class="bi bi-exclamation-triangle me-2"></i>Sebelum menekan tombol import</h6>
        <ul class="mb-0 small">
            <li>Jangan menghapus baris judul, mengganti nama kolom, atau mengubah urutan kolom template.</li>
            <li>Pastikan nomor invoice/faktur unik dan kode akun sesuai dengan daftar COA aktif.</li>
            <li>Kolom kode dan nama customer/supplier wajib diisi. Master yang belum ada akan dibuat otomatis.</li>
            <li>Import transaksi berjalan membuat jurnal otomatis; import saldo awal tidak membuat jurnal baru.</li>
            <li>Jangan mengunggah file yang sama dua kali.</li>
        </ul>
    </div>
</div>
@endsection
