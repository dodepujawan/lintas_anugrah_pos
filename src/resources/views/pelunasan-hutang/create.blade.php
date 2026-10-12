@extends('layouts.app')

@php($edit = isset($payment))
@section('title', $edit ? 'Edit Pelunasan Hutang' : 'Pelunasan Hutang')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>{{ $edit ? 'Edit Pelunasan Hutang' : 'Pelunasan Hutang' }}</h3><a href="{{ route('panduan.transaksi', 'pelunasan-hutang') }}" class="btn btn-outline-info"><i class="bi bi-book me-1"></i>Panduan</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" id="tglbayar" class="form-control" value="{{ $edit ? $payment->TGLBAYAR->format('Y-m-d') : date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kode Supplier</label>
                    <input type="text" id="supplier" class="form-control" placeholder="Ketik nama supplier" list="supplier-list" autocomplete="off" value="{{ $edit ? $payment->SUPPLIER : '' }}" @if($edit) readonly @endif>
                    <datalist id="supplier-list"></datalist>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nama Supplier</label>
                    <input type="text" id="supplier_name" class="form-control bg-light" placeholder="Terisi setelah supplier dipilih" value="{{ $edit ? $payment->supplier_display : '' }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" id="btnCari" class="btn btn-primary w-100" @if($edit) disabled @endif>Cari Faktur</button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jenis Bayar</label>
                    <select id="jenis_bayar" class="form-select">
                        <option value="CASH" @selected($edit && $payment->METODE_PEMBAYARAN === 'CASH')>Cash</option>
                        <option value="TRANSFER" @selected($edit && $payment->METODE_PEMBAYARAN === 'TRANSFER')>Transfer</option>
                        <option value="GIRO" @selected($edit && $payment->METODE_PEMBAYARAN === 'GIRO')>Giro</option>
                        <option value="CEK" @selected($edit && $payment->METODE_PEMBAYARAN === 'CEK')>Cek</option>
                        <option value="QRIS" @selected($edit && $payment->METODE_PEMBAYARAN === 'QRIS')>QRIS</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Akun Pembayaran</label>
                    <select id="payment_coa_id" class="form-select">
                        <option value="">Default sesuai jenis bayar</option>
                        @foreach ($paymentAccounts as $account)
                            <option value="{{ $account->id }}" @selected($edit && (int) $payment->PAYMENT_COA_ID === (int) $account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">No Referensi</label>
                    <input type="text" id="no_referensi" class="form-control" value="{{ $edit ? $payment->NO_REFERENSI : '' }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Keterangan</label>
                    <input type="text" id="keterangan" class="form-control" value="{{ $edit ? $payment->KETERANGAN : '' }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3 shadow-sm">
        <div class="card-header">{{ $edit ? 'Faktur Pelunasan' : 'Faktur Belum Lunas' }}</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>No Faktur</th>
                            <th>Tanggal</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-end">Saldo</th>
                            <th width="180">Bayar</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceBody">
                        <tr><td colspan="6" class="text-center">{{ $edit ? 'Memuat data pelunasan...' : 'Pilih supplier terlebih dahulu' }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-md-4 ms-auto">
                    <table class="table">
                        <tr><th>Total Bayar</th><th class="text-end"><span id="totalBayar">0.00</span></th></tr>
                    </table>
                </div>
            </div>
            <div class="text-end">
                <button type="button" id="btnSimpan" class="btn btn-success">{{ $edit ? 'Update Pelunasan' : 'Simpan Pelunasan' }}</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supplierInput = document.getElementById('supplier');
    const supplierList = document.getElementById('supplier-list');
    const supplierName = document.getElementById('supplier_name');
    const invoiceBody = document.getElementById('invoiceBody');
    const totalBayar = document.getElementById('totalBayar');
    let supplierTimer = null;
    const suppliers = new Map();
    const isEdit = @json($edit);
    const editInvoices = @json($edit ? $editInvoices : []);

    function showSupplierName() {
        const selected = suppliers.get(supplierInput.value.trim().toUpperCase());
        supplierName.value = selected ? selected.nama : '';
    }

    supplierInput.addEventListener('input', function() {
        const q = this.value.trim();
        showSupplierName();
        if (q.length < 2) {
            supplierList.innerHTML = '';
            return;
        }
        clearTimeout(supplierTimer);
        supplierTimer = setTimeout(function() {
            fetch("{{ route('pelunasan-hutang.search-supplier') }}?q=" + encodeURIComponent(q))
                .then(response => response.json())
                .then(data => {
                    supplierList.innerHTML = '';
                    data.forEach(function(item) {
                        item.supplier = String(item.supplier).trim();
                        item.nama = String(item.nama || '').trim();
                        suppliers.set(item.supplier.toUpperCase(), item);
                        const option = document.createElement('option');
                        option.value = item.supplier;
                        option.label = item.supplier + ' - ' + item.nama;
                        supplierList.appendChild(option);
                    });
                    showSupplierName();
                });
        }, 300);
    });
    supplierInput.addEventListener('change', showSupplierName);

    document.getElementById('btnCari').addEventListener('click', function() {
        const supplier = supplierInput.value.trim();
        if (!supplier) {
            alert('Supplier belum dipilih');
            return;
        }

        fetch("{{ route('pelunasan-hutang.supplier-invoices') }}?supplier=" + encodeURIComponent(supplier))
            .then(response => response.json())
            .then(function(data) {
                renderInvoices(data);
            });
    });

    document.addEventListener('input', function(e) {
        if (!e.target.classList.contains('bayar')) {
            return;
        }
        const saldo = parseFloat(e.target.dataset.saldo || 0);
        const bayar = parseFloat(e.target.value || 0);
        if (bayar > saldo) {
            e.target.value = saldo;
        }
        hitungTotal();
    });

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.bayar').forEach(function(el) {
            total += parseFloat(el.value || 0);
        });
        totalBayar.innerHTML = total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderInvoices(data) {
        if (!data.length) {
            invoiceBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada faktur yang belum lunas</td></tr>';
            hitungTotal();
            return;
        }

        invoiceBody.innerHTML = data.map(function(item) {
            const bayar = parseFloat(item.BAYAR || 0);
            return `
                <tr>
                    <td><input type="checkbox" checked></td>
                    <td>${item.NOFAKTUR}<input type="hidden" class="nofaktur" value="${item.NOFAKTUR}"></td>
                    <td>${item.TGLFAKTUR}</td>
                    <td>${item.TGLJT}</td>
                    <td class="text-end">${parseFloat(item.SALDO).toLocaleString('id-ID')}</td>
                    <td>
                        <input type="number" class="form-control bayar" step="0.01" min="0" max="${item.SALDO}" value="${bayar}" data-saldo="${item.SALDO}" data-faktur="${item.NOFAKTUR}">
                    </td>
                </tr>
            `;
        }).join('');
        hitungTotal();
    }

    if (isEdit) {
        renderInvoices(editInvoices);
    }

    document.getElementById('btnSimpan').addEventListener('click', function() {
        const saveButton = this;
        const details = [];
        let total = 0;
        document.querySelectorAll('.bayar').forEach(function(el) {
            const bayar = parseFloat(el.value || 0);
            if (bayar <= 0) {
                return;
            }
            total += bayar;
            details.push({ nofaktur: el.dataset.faktur, bayar: bayar });
        });

        if (total <= 0) {
            alert('Belum ada pembayaran');
            return;
        }

        saveButton.disabled = true;
        saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...';

        fetch(@json($edit ? route('pelunasan-hutang.update', $payment) : route('pelunasan-hutang.store')), {
            method: @json($edit ? 'PUT' : 'POST'),
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                tglbayar: document.getElementById('tglbayar').value,
                supplier: supplierInput.value,
                jenis_bayar: document.getElementById('jenis_bayar').value,
                payment_coa_id: document.getElementById('payment_coa_id').value,
                no_referensi: document.getElementById('no_referensi').value,
                keterangan: document.getElementById('keterangan').value,
                total_bayar: total,
                details: details,
                _payment_token: @json($edit ? null : $paymentToken)
            })
        })
        .then(response => response.json())
        .then(function(data) {
            if (data.success) {
                alert(data.message || 'Pelunasan hutang berhasil disimpan');
                window.location.href = @json($edit ? route('pelunasan-hutang.show', $payment) : route('pelunasan-hutang.index'));
                return;
            }
            alert(data.message || 'Gagal menyimpan pelunasan hutang');
            saveButton.disabled = false;
            saveButton.innerHTML = @json($edit ? 'Update Pelunasan' : 'Simpan Pelunasan');
        })
        .catch(function() {
            alert('Gagal menyimpan pelunasan hutang. Periksa koneksi atau ulangi dari halaman input baru.');
            saveButton.disabled = false;
            saveButton.innerHTML = @json($edit ? 'Update Pelunasan' : 'Simpan Pelunasan');
        });
    });
});
</script>
@endsection
