@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-file-earmark-bar-graph"></i> Laporan Keuangan</h2>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-3 mb-4"><div class="card h-100"><div class="card-body text-center"><i class="bi bi-journal-minus display-3 text-danger"></i><h5 class="card-title mt-3">Laporan Hutang</h5><p class="card-text text-muted">Saldo supplier, kartu hutang, aging, pembayaran, dan rekonsiliasi.</p><a href="{{ route('laporan.hutang') }}" class="btn btn-danger"><i class="bi bi-eye"></i> Lihat Laporan</a></div></div></div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-people display-3 text-primary"></i>
                <h5 class="card-title mt-3">Laporan Piutang</h5>
                <p class="card-text text-muted">Saldo pelanggan, kartu piutang, aging, jatuh tempo, pembayaran, dan rekonsiliasi.</p>
                <a href="{{ route('laporan.piutang') }}" class="btn btn-primary">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-cart-check display-3 text-success"></i>
                <h5 class="card-title mt-3">Laporan Penjualan</h5>
                <p class="card-text text-muted">Penjualan dari penambahan piutang dengan filter tanggal, customer, dan jenis EKS/REN/CO.</p>
                <a href="{{ route('laporan.penjualan') }}" class="btn btn-success">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-text display-3 text-primary"></i>
                <h5 class="card-title mt-3">Neraca</h5>
                <p class="card-text text-muted">Laporan posisi keuangan yang menunjukkan aset, kewajiban, dan ekuitas.</p>
                <a href="{{ route('laporan.neraca') }}" class="btn btn-primary">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-graph-up-arrow display-3 text-success"></i>
                <h5 class="card-title mt-3">Laba Rugi</h5>
                <p class="card-text text-muted">Laporan yang menunjukkan pendapatan, beban, dan laba/rugi perusahaan.</p>
                <a href="{{ route('laporan.laba_rugi') }}" class="btn btn-success">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-receipt-cutoff display-3 text-danger"></i>
                <h5 class="card-title mt-3">Laporan Biaya</h5>
                <p class="card-text text-muted">Ringkasan akun beban dengan detail transaksi yang bisa diklik.</p>
                <a href="{{ route('laporan.biaya') }}" class="btn btn-danger">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-journal-richtext display-3 text-warning"></i>
                <h5 class="card-title mt-3">Buku Besar</h5>
                <p class="card-text text-muted">Ringkasan transaksi untuk setiap akun secara detail.</p>
                <a href="{{ route('laporan.buku_besar') }}" class="btn btn-warning">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-layers-fill display-3 text-secondary"></i>
                <h5 class="card-title mt-3">Neraca Saldo</h5>
                <p class="card-text text-muted">Saldo debit dan kredit akun posting dalam periode.</p>
                <a href="{{ route('laporan.neraca_saldo') }}" class="btn btn-secondary">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-spreadsheet display-3 text-dark"></i>
                <h5 class="card-title mt-3">Daftar COA</h5>
                <p class="card-text text-muted">Daftar akun COA aktif dengan filter tipe dan level.</p>
                <a href="{{ route('laporan.coa') }}" class="btn btn-dark">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-journal-text display-3 text-info"></i>
                <h5 class="card-title mt-3">Jurnal Umum</h5>
                <p class="card-text text-muted">Daftar seluruh transaksi berdasarkan urutan tanggal.</p>
                <a href="{{ route('laporan.jurnal_umum') }}" class="btn btn-info">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-currency-dollar display-3 text-success"></i>
                <h5 class="card-title mt-3">Arus Kas</h5>
                <p class="card-text text-muted">Laporan penerimaan dan pengeluaran kas dari akun aktif.</p>
                <a href="{{ route('laporan.arus_kas') }}" class="btn btn-success">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>

    @if(auth()->user()?->hasMenuPermission('laporan.perubahan_ekuitas', 'view'))
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-diagram-3 display-3 text-primary"></i>
                <h5 class="card-title mt-3">Perubahan Ekuitas</h5>
                <p class="card-text text-muted">Perubahan saldo ekuitas dari laba ditahan dan laba/rugi periode.</p>
                <a href="{{ route('laporan.perubahan_ekuitas') }}" class="btn btn-primary">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()?->hasMenuPermission('laporan.tutup_buku', 'view'))
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-box-seam display-3 text-danger"></i>
                <h5 class="card-title mt-3">Tutup Buku</h5>
                <p class="card-text text-muted">Ringkasan laba/rugi akhir periode untuk penutupan buku.</p>
                <a href="{{ route('laporan.tutup_buku') }}" class="btn btn-danger">
                    <i class="bi bi-eye"></i> Lihat Laporan
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
