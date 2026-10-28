@extends('layouts.app')

@section('content')
<div class="container-fluid" data-debug="transaksiRentPendinginEdit">
    <div class="row mb-3">
        <div class="col">
            <h1 class="page-title mb-1">Edit Surat Jalan Rent Pendingin</h1>
            <p class="text-muted small mb-0">{{ $rentPendingin->NOSJ }}</p>
        </div>
        <div class="col-auto">
            <button type="submit" form="rentPendinginForm" class="btn btn-success btn-sm" data-debug="transaksiRentPendinginEditSave">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="{{ route('rent-pendingin.index') }}" class="btn btn-outline-secondary btn-sm" data-debug="transaksiRentPendinginEditCancel">
                <i class="bi bi-x-lg"></i> Batal
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" data-debug="transaksiRentPendinginEditErrors">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="rentPendinginForm" method="POST" action="{{ route('rent-pendingin.update', $rentPendingin) }}" data-debug="transaksiRentPendinginEditForm">
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tgl Muat</label>
                        <input type="date" name="TGLMUAT" value="{{ old('TGLMUAT', $rentPendingin->TGLMUAT?->format('Y-m-d')) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_TGLMUAT">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">NOSJ</label>
                        <input type="text" name="NOSJ" value="{{ old('NOSJ', $rentPendingin->NOSJ) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_NOSJ">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <select name="CUSTOMER" class="form-select form-select-sm" data-debug="transaksiRentPendinginEdit_CUSTOMER">
                            <option value="">Pilih customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->CUSTOMER }}" {{ old('CUSTOMER', $rentPendingin->CUSTOMER) == $customer->CUSTOMER ? 'selected' : '' }}>
                                    {{ $customer->CUSTOMER }} - {{ $customer->NAMACUST }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">NOMUAT</label>
                        <input type="text" name="NOMUAT" value="{{ old('NOMUAT', $rentPendingin->NOMUAT) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_NOMUAT">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Kendaraan</label>
                        <select name="KENDARAAN" class="form-select form-select-sm" data-debug="transaksiRentPendinginEdit_KENDARAAN">
                            <option value="">Pilih kendaraan</option>
                            @foreach($kendaraans as $kendaraan)
                                <option value="{{ $kendaraan->KODE }}" {{ old('KENDARAAN', $rentPendingin->KENDARAAN) == $kendaraan->KODE ? 'selected' : '' }}>
                                    {{ $kendaraan->KODE }} - {{ $kendaraan->NAMA }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Driver</label>
                        <select name="DRIVER" class="form-select form-select-sm" data-debug="transaksiRentPendinginEdit_DRIVER">
                            <option value="">Pilih driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->KODE }}" {{ old('DRIVER', $rentPendingin->DRIVER) == $driver->KODE ? 'selected' : '' }}>
                                    {{ $driver->KODE }} - {{ $driver->NAMA }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" min="0" step="0.01" name="JUMLAH" value="{{ old('JUMLAH', $rentPendingin->JUMLAH) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_JUMLAH">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" name="UNIT" value="{{ old('UNIT', $rentPendingin->UNIT) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_UNIT">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Harga</label>
                        <input type="number" min="0" step="1" name="HARGA" value="{{ old('HARGA', $rentPendingin->HARGA) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_HARGA">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Disc %</label>
                        <input type="number" min="0" step="0.01" name="DISC" value="{{ old('DISC', $rentPendingin->DISC) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_DISC">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">DC</label>
                        <input type="number" min="0" step="1" name="DC" value="{{ old('DC', $rentPendingin->DC) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_DC">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grand</label>
                        <input type="number" min="0" step="1" name="GRAND" value="{{ old('GRAND', $rentPendingin->GRAND) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_GRAND">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Rute</label>
                        <input type="text" name="rute" value="{{ old('rute', $rentPendingin->rute) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_RUTE">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PESANAN</label>
                        <input type="text" name="PESANAN" value="{{ old('PESANAN', $rentPendingin->PESANAN) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_PESANAN">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">STS</label>
                        <input type="text" name="STS" value="{{ old('STS', $rentPendingin->STS) }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_STS">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">JENIS</label>
                        <input type="text" name="JENIS" value="{{ old('JENIS', $rentPendingin->JENIS ?? 'REN') }}" class="form-control form-control-sm" data-debug="transaksiRentPendinginEdit_JENIS">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
