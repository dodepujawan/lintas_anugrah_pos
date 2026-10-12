@extends('layouts.app')

@section('title', 'Account Mapping')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="page-title"><i class="bi bi-link-45deg me-2"></i>Account Mapping</h2>
            <p class="text-muted">Tetapkan akun default untuk transaksi otomatis, laporan keuangan, dan proses tutup buku.</p>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-1"></i>
            Mapping digunakan ketika aplikasi membuat jurnal otomatis atau ketika akun tidak dipilih langsung pada form transaksi. Periksa keterangan penggunaan sebelum memilih akun.
        </div>
        <form action="{{ route('account-mappings.store') }}" method="POST">
            @csrf

            @foreach($mappingGroups as $groupTitle => $mappingKeys)
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header {{ $loop->first ? 'bg-primary' : ($loop->iteration === 2 ? 'bg-success' : 'bg-dark') }} text-white py-3">
                    <h5 class="mb-0 fw-bold text-uppercase">
                        <i class="bi {{ $loop->first ? 'bi-cash-coin' : ($loop->iteration === 2 ? 'bi-receipt' : 'bi-bank') }} me-2"></i>{{ $groupTitle }}
                    </h5>
                    @if($loop->first)
                    <div class="small mt-1 opacity-75">Akun default khusus penerimaan dan penyelesaian piutang customer.</div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-4">
            @foreach($mappingKeys as $mappingKey)
            @php($label = $mappingLabels[$mappingKey])
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-light">
                <label for="mapping_{{ $mappingKey }}" class="form-label fw-semibold">{{ $label }}</label>
                <div class="small text-muted mb-2">
                    <i class="bi bi-arrow-return-right me-1"></i>{{ $mappingDescriptions[$mappingKey] ?? 'Digunakan sebagai akun default oleh sistem.' }}
                </div>
                <select
                    name="mappings[{{ $mappingKey }}]"
                    id="mapping_{{ $mappingKey }}"
                    class="form-select @error('mappings.' . $mappingKey) is-invalid @enderror">
                    <option value="">-- Pilih Akun --</option>
                    @foreach($accountOptions[$mappingKey] as $account)
                    <option value="{{ $account->id }}"
                        @selected(isset($currentMappings[$mappingKey]) && $currentMappings[$mappingKey]===$account->id)
                        >
                        {{ $account->kode_akun }} - {{ $account->nama_akun }}
                    </option>
                    @endforeach
                </select>
                @error('mappings.' . $mappingKey)
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    @if($mappingKey === 'retained_earnings')
                    Hanya akun Ekuitas dengan saldo normal Kredit yang ditampilkan.
                    @elseif($mappingKey === 'current_year_profit')
                    Hanya akun Ekuitas dengan saldo normal Kredit yang ditampilkan.
                    @elseif($mappingKey === 'cash_main')
                    Hanya akun Kas / Bank yang ditampilkan.
                    @elseif($mappingKey === 'accounts_payable')
                    Hanya akun Hutang / Utang yang ditampilkan.
                    @elseif($mappingKey === 'input_tax')
                    Hanya akun posting Aset untuk Pajak Masukan yang ditampilkan.
                    @elseif($mappingKey === 'output_tax')
                    Hanya akun posting Kewajiban untuk Pajak Keluaran yang ditampilkan.
                    @elseif($mappingKey === 'equity_capital')
                    Hanya akun Modal Utama yang ditampilkan.
                    @elseif($mappingKey === 'prive')
                    Hanya akun Prive / Dividen yang ditampilkan.
                    @elseif($mappingKey === 'receivable_transfer_bank')
                    Hanya akun posting Aset kelompok Bank yang ditampilkan.
                    @elseif($mappingKey === 'receivable_pph23')
                    Hanya akun posting Aset dengan nama PPh Pasal 23 yang ditampilkan.
                    @elseif($mappingKey === 'receivable_payment_loss')
                    Hanya akun Beban Kerugian/Potongan Piutang yang ditampilkan.
                    @elseif($mappingKey === 'receivable_bank_admin')
                    Hanya akun Beban Administrasi Bank yang ditampilkan.
                    @endif
                </div>
                </div>
            </div>
            @endforeach
                    </div>
                </div>
            </section>
            @endforeach

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan Mapping</button>
            </div>
        </form>
    </div>
</div>
@endsection
