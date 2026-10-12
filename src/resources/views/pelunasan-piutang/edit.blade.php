@extends('layouts.app')

@section('title', 'Edit Pelunasan - ' . $barh->NOBAYAR)

@section('content')

<div class="container-fluid compact-payment-edit">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="edit-payment-title">Edit Pelunasan Piutang</h3>
        <a href="{{ route('pelunasan-piutang.show', $barh->id) }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm edit-payment-header-card">

        <div class="card-body">

            <div class="row g-1">

                <div class="col-md-2">
                    <label class="form-label">No. Bukti</label>
                    <input type="text" class="form-control" value="{{ $barh->NOBAYAR }}" disabled>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" id="tglbayar" class="form-control" value="{{ $barh->TGLBAYAR->format('Y-m-d') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Kode Customer</label>
                    <input type="text" id="customer" class="form-control" placeholder="Ketik kode atau nama customer"
                        list="customer-list" autocomplete="off" value="{{ $barh->CUSTOMER }}">
                    <datalist id="customer-list"></datalist>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Nama Customer</label>
                    <input type="text" id="customer_name" class="form-control bg-light" value="{{ $customerName ?? '' }}" readonly>
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" id="btnCari" class="btn btn-primary w-100">Cari Invoice</button>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Jenis Bayar</label>
                    <select id="jenis_bayar" class="form-select">
                        <option value="CASH" {{ $barh->METODE_PEMBAYARAN === 'CASH' ? 'selected' : '' }}>Cash</option>
                        <option value="TRANSFER" {{ $barh->METODE_PEMBAYARAN === 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                        <option value="GIRO" {{ $barh->METODE_PEMBAYARAN === 'GIRO' ? 'selected' : '' }}>Giro</option>
                        <option value="CEK" {{ $barh->METODE_PEMBAYARAN === 'CEK' ? 'selected' : '' }}>Cek</option>
                        <option value="QRIS" {{ $barh->METODE_PEMBAYARAN === 'QRIS' ? 'selected' : '' }}>QRIS</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Akun Pembayaran</label>
                    <div class="input-group">
                        <select id="payment_coa_id" class="form-select">
                            <option value="">Default sesuai jenis bayar</option>
                            @foreach ($paymentAccounts as $account)
                                <option value="{{ $account->id }}" {{ (int) $barh->PAYMENT_COA_ID === (int) $account->id ? 'selected' : '' }}>
                                    {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-coa-search" data-target="payment_coa_id" data-group="payment" data-title="Cari Akun Pembayaran" title="Cari akun COA">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="form-text">Default mengikuti jenis bayar; dapat diganti dengan seluruh akun COA aktif.</div>
                </div>

                <div class="col-md-4"><label class="form-label">Akun PPh Pasal 23</label><div class="input-group"><select id="pph23_coa_id" class="form-select"><option value="">Pilih jika ada PPh 23</option>@foreach($pphAccounts as $account)<option value="{{ $account->id }}" @selected((int)$barh->PPH23_COA_ID === (int)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select><button type="button" class="btn btn-outline-primary btn-coa-search" data-target="pph23_coa_id" data-group="pph" data-title="Cari Akun PPh Pasal 23"><i class="bi bi-search"></i></button></div></div>
                <div class="col-md-4"><label class="form-label">Akun Kerugian Pembayaran</label><div class="input-group"><select id="discount_coa_id" class="form-select"><option value="">Pilih jika ada kerugian</option>@foreach($discountAccounts as $account)<option value="{{ $account->id }}" @selected((int)$barh->DISCOUNT_COA_ID === (int)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select><button type="button" class="btn btn-outline-primary btn-coa-search" data-target="discount_coa_id" data-group="discount" data-title="Cari Akun Kerugian Pembayaran"><i class="bi bi-search"></i></button></div></div>
                <div class="col-md-4"><label class="form-label">Akun Beban Administrasi Bank</label><div class="input-group"><select id="bank_admin_coa_id" class="form-select"><option value="">Pilih jika ada administrasi bank</option>@foreach($bankAdminAccounts as $account)<option value="{{ $account->id }}" @selected((int)$barh->BANK_ADMIN_COA_ID === (int)$account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>@endforeach</select><button type="button" class="btn btn-outline-primary btn-coa-search" data-target="bank_admin_coa_id" data-group="bankAdmin" data-title="Cari Akun Beban Administrasi Bank"><i class="bi bi-search"></i></button></div></div>

                <div class="col-md-4">
                    <label class="form-label">No Referensi</label>
                    <input type="text" id="no_referensi" class="form-control" value="{{ $barh->NO_REFERENSI ?? '' }}">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Keterangan</label>
                    <input type="text" id="keterangan" class="form-control" value="{{ $barh->KETERANGAN ?? '' }}">
                </div>

            </div>

        </div>

    </div>

    @include('pelunasan-piutang._payment-account-modal', ['paymentAccounts' => $paymentAccounts])

    <div class="card mt-2 shadow-sm invoice-entry-card">

        <div class="card-header">
            Invoice Belum Lunas
        </div>

        <div class="card-body">

            <div class="alert alert-info py-1 mb-1">
                Isi PPh 23, kerugian, dan administrasi bank, lalu klik <strong>Hitung Lunas</strong>.
                Uang diterima akan dihitung otomatis dan terus disesuaikan selama mode otomatis aktif.
            </div>

            <div class="table-responsive invoice-table-scroll">

                <table class="table table-bordered table-hover">

                    <colgroup>
                        <col class="invoice-number-col">
                        <col class="invoice-date-col">
                        <col class="invoice-balance-col">
                        <col class="received-col">
                        <col class="pph-col">
                        <col class="loss-col">
                        <col class="admin-col">
                        <col class="settlement-total-col">
                        <col class="settlement-remaining-col">
                        <col class="calculate-col">
                    </colgroup>

                    <thead class="table-light">
                        <tr>
                            <th>No Faktur</th>
                            <th>Tanggal / Jatuh Tempo</th>
                            <th class="text-end">Saldo</th>
                            <th>Uang Diterima</th>
                            <th>PPh 23</th>
                            <th>Kerugian</th>
                            <th>Adm. Bank</th>
                            <th>Total</th>
                            <th>Sisa</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody id="invoiceBody">
                        <tr>
                            <td colspan="10" class="text-center">
                                Click "Cari Invoice" untuk memuat data
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

            <div class="row">

                <div class="col-md-4 ms-auto">

                    <table class="table">

                        <tr><th>Uang Diterima</th><th class="text-end"><span id="totalBayar">0.00</span></th></tr>
                        <tr><th>PPh 23</th><th class="text-end"><span id="totalPph23">0.00</span></th></tr>
                        <tr><th>Kerugian</th><th class="text-end"><span id="totalDiscount">0.00</span></th></tr>
                        <tr><th>Adm. Bank</th><th class="text-end"><span id="totalBankAdmin">0.00</span></th></tr>
                        <tr class="table-light"><th>Total Pelunasan</th><th class="text-end"><span id="totalPelunasan">0.00</span></th></tr>

                    </table>

                </div>

            </div>

            <div class="text-end">

                <button type="button" id="btnUpdate" class="btn btn-warning">

                    Update Pelunasan

                </button>

            </div>

        </div>

    </div>

</div>

@php
    $existingDetails = $bard->map(function ($detail) {
        return [
            'NOFAKTUR' => $detail->NOFAKTUR,
            'TGLFAKTUR' => $detail->TGLFAKTUR ? $detail->TGLFAKTUR->format('d/m/Y') : '',
            'TGLJT' => optional($detail->invoice?->TGLJT)->format('d/m/Y') ?? '',
            'SALDO' => (float) $detail->SALDO_FAKTUR,
            'BAYAR' => (float) $detail->NILAI_BAYAR,
            'PPH23' => (float) $detail->PPH23,
            'DISCOUNT' => (float) $detail->DISCOUNT,
            'BANK_ADMIN' => (float) $detail->BANK_ADMIN,
        ];
    })->values();
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customerInput = document.getElementById('customer');
        const customerList = document.getElementById('customer-list');
        const customerNameInput = document.getElementById('customer_name');
        const invoiceBody = document.getElementById('invoiceBody');
        const totalBayar = document.getElementById('totalBayar');
        const totalPph23 = document.getElementById('totalPph23');
        const totalDiscount = document.getElementById('totalDiscount');
        const totalBankAdmin = document.getElementById('totalBankAdmin');
        const totalPelunasan = document.getElementById('totalPelunasan');
        const existingDetails = @json($existingDetails);

        /* Enter berfungsi seperti Tab dan tidak mengirim form edit. */
        document.addEventListener('keydown', function(event) {
            const selector = '#no_referensi, #keterangan, .bayar, .pph23, .discount, .bank-admin';

            if (event.key !== 'Enter' || !event.target.matches(selector)) {
                return;
            }

            event.preventDefault();

            const fields = Array.from(document.querySelectorAll(selector))
                .filter(field => !field.disabled && !field.readOnly && field.offsetParent !== null);
            const currentIndex = fields.indexOf(event.target);
            const nextIndex = event.shiftKey ? currentIndex - 1 : currentIndex + 1;

            if (fields[nextIndex]) {
                fields[nextIndex].focus();
                if (typeof fields[nextIndex].select === 'function') {
                    fields[nextIndex].select();
                }
            } else {
                event.target.blur();
            }
        });

        let customerTimer = null;
        const customers = new Map();
        @if ($customerName)
            customers.set(@json(strtoupper($barh->CUSTOMER)), { customer: @json($barh->CUSTOMER), nama: @json($customerName) });
        @endif

        function showSelectedCustomer() {
            const selected = customers.get(customerInput.value.trim().toUpperCase());
            if (!selected) {
                customerNameInput.value = ''; return;
            }
            customerNameInput.value = selected.nama || '';
        }

        function renderRows(items) {
            if (!items.length) {
                invoiceBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada invoice yang bisa ditampilkan</td>
                    </tr>
                `;
                hitungTotal();
                return;
            }

            invoiceBody.innerHTML = items.map(function(item) {
                const saldo = parseFloat(item.SALDO || 0);
                const bayar = parseFloat(item.BAYAR || 0);
                const pph23 = parseFloat(item.PPH23 || 0);
                const discount = parseFloat(item.DISCOUNT || 0);
                const bankAdmin = parseFloat(item.BANK_ADMIN || 0);

                return `
                    <tr>
                        <td>
                            ${item.NOFAKTUR}
                            <input type="hidden" class="nofaktur" value="${item.NOFAKTUR}">
                        </td>
                        <td class="invoice-dates">
                            <div>${item.TGLFAKTUR || ''}</div>
                            <div class="due-date"><span>JT</span> ${item.TGLJT || '-'}</div>
                        </td>
                        <td class="text-end">${formatMoney(saldo)}</td>
                        <td>
                            <input
                                type="text"
                                inputmode="decimal"
                                class="form-control bayar"
                                step="0.01"
                                min="0"
                                max="${saldo}"
                                value="${formatMoney(bayar)}"
                                data-saldo="${saldo}"
                                data-faktur="${item.NOFAKTUR}">
                        </td>
                        <td><input type="text" inputmode="decimal" class="form-control pph23" value="${formatMoney(pph23)}"></td>
                        <td><input type="text" inputmode="decimal" class="form-control discount" value="${formatMoney(discount)}"></td>
                        <td><input type="text" inputmode="decimal" class="form-control bank-admin" value="${formatMoney(bankAdmin)}"></td>
                        <td class="text-end settlement-status"><strong class="row-settlement-total">0,00</strong></td>
                        <td class="text-end settlement-status"><strong class="row-settlement-remaining settlement-remaining-balance">0,00</strong></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary btn-hitung-lunas text-nowrap">Hitung Lunas</button></td>
                    </tr>
                `;
            }).join('');

            document.querySelectorAll('#invoiceBody tr').forEach(updateRowCalculation);
            hitungTotal();
        }

        function hitungTotal() {
            const sum = selector => Array.from(document.querySelectorAll(selector)).reduce((total, el) => total + parseMoney(el.value), 0);
            const bayar = sum('.bayar'); const pph23 = sum('.pph23'); const discount = sum('.discount'); const bankAdmin = sum('.bank-admin');
            const format = value => value.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            totalBayar.textContent = format(bayar); totalPph23.textContent = format(pph23); totalDiscount.textContent = format(discount); totalBankAdmin.textContent = format(bankAdmin); totalPelunasan.textContent = format(bayar + pph23 + discount + bankAdmin);
        }

        function formatMoney(value) {
            return Number(value || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function parseMoney(value) {
            let text = String(value ?? '').trim().replace(/\s/g, '');
            if (text === '') return 0;

            if (text.includes(',') && text.includes('.')) {
                text = text.replace(/\./g, '').replace(',', '.');
            } else if (text.includes(',')) {
                text = text.replace(',', '.');
            } else if (/^\d{1,3}(\.\d{3})+$/.test(text)) {
                text = text.replace(/\./g, '');
            }

            text = text.replace(/[^0-9.-]/g, '');
            const number = Number.parseFloat(text);
            return Number.isFinite(number) && number >= 0 ? number : 0;
        }

        function updateRowCalculation(row) {
            const bayarInput = row.querySelector('.bayar');
            if (!bayarInput) return;

            const saldo = parseMoney(bayarInput.dataset.saldo);
            const total = ['.bayar', '.pph23', '.discount', '.bank-admin']
                .reduce((sum, selector) => sum + parseMoney(row.querySelector(selector).value), 0);
            const remaining = saldo - total;
            const totalElement = row.querySelector('.row-settlement-total');
            const remainingElement = row.querySelector('.row-settlement-remaining');

            totalElement.textContent = formatMoney(total);
            remainingElement.classList.remove('text-success', 'text-danger', 'settlement-remaining-balance');

            if (Math.abs(remaining) < 0.01) {
                remainingElement.textContent = '0,00 (Lunas)';
                remainingElement.classList.add('text-success');
            } else if (remaining > 0) {
                remainingElement.textContent = formatMoney(remaining);
                remainingElement.classList.add('settlement-remaining-balance');
            } else {
                remainingElement.textContent = formatMoney(Math.abs(remaining)) + ' (Lebih)';
                remainingElement.classList.add('text-danger');
            }
        }

        function setAutoCalculateButton(row, active) {
            const button = row.querySelector('.btn-hitung-lunas');
            if (!button) return;
            button.classList.toggle('btn-primary', active);
            button.classList.toggle('btn-outline-primary', !active);
            button.innerHTML = active ? '<i class="bi bi-calculator me-1"></i>Otomatis Aktif' : 'Hitung Lunas';
        }

        function calculateFullPayment(row) {
            const bayarInput = row.querySelector('.bayar');
            const saldo = parseMoney(bayarInput.dataset.saldo);
            const deductions = ['.pph23', '.discount', '.bank-admin']
                .reduce((sum, selector) => sum + parseMoney(row.querySelector(selector).value), 0);
            bayarInput.value = formatMoney(Math.max(0, saldo - deductions));
            return deductions <= saldo;
        }

        customerInput.addEventListener('input', function() {
            const q = this.value.trim();
            showSelectedCustomer();

            if (q.length < 2) {
                customerList.innerHTML = '';
                return;
            }

            clearTimeout(customerTimer);

            customerTimer = setTimeout(function() {
                fetch("{{ route('pelunasan-piutang.search-customer') }}?q=" + encodeURIComponent(q))
                    .then(response => response.json())
                    .then(data => {
                        customerList.innerHTML = '';

                        data.forEach(function(item) {
                            item.customer = String(item.customer).trim();
                            item.nama = String(item.nama || '').trim();
                            customers.set(item.customer.toUpperCase(), item);
                            const option = document.createElement('option');
                            option.value = item.customer;
                            option.label = item.label;
                            customerList.appendChild(option);
                        });
                        showSelectedCustomer();
                    });
            }, 300);
        });

        customerInput.addEventListener('change', showSelectedCustomer);

        document.getElementById('btnCari').addEventListener('click', function() {
            const customer = customerInput.value.trim();

            if (!customer) {
                alert('Customer belum dipilih');
                return;
            }

            fetch("{{ route('pelunasan-piutang.customer-invoices') }}?customer=" + encodeURIComponent(customer))
                .then(response => response.json())
                .then(function(data) {
                    renderRows(data.map(function(item) {
                        const existing = existingDetails.find(old => old.NOFAKTUR === item.NOFAKTUR);
                        item.BAYAR = existing ? existing.BAYAR : 0;
                        item.PPH23 = existing ? existing.PPH23 : 0;
                        item.DISCOUNT = existing ? existing.DISCOUNT : 0;
                        item.BANK_ADMIN = existing ? existing.BANK_ADMIN : 0;
                        if (existing) item.SALDO = existing.SALDO;
                        return item;
                    }).concat(existingDetails.filter(old => !data.some(item => item.NOFAKTUR === old.NOFAKTUR))));
                });
        });

        document.addEventListener('input', function(e) {
            if (!e.target.matches('.bayar, .pph23, .discount, .bank-admin')) {
                return;
            }

            const row = e.target.closest('tr');
            if (e.target.matches('.bayar')) {
                row.dataset.autoCalculate = '0';
                setAutoCalculateButton(row, false);
            } else if (row.dataset.autoCalculate === '1') {
                calculateFullPayment(row);
            }

            updateRowCalculation(row);
            hitungTotal();
        });

        document.addEventListener('focusin', function(e) {
            if (!e.target.matches('.bayar, .pph23, .discount, .bank-admin')) return;
            e.target.value = String(parseMoney(e.target.value));
            e.target.select();
        });

        document.addEventListener('blur', function(e) {
            if (!e.target.matches('.bayar, .pph23, .discount, .bank-admin')) return;
            e.target.value = formatMoney(parseMoney(e.target.value));
        }, true);

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.btn-hitung-lunas');
            if (!button) return;

            const row = button.closest('tr');
            if (!calculateFullPayment(row)) {
                row.dataset.autoCalculate = '0';
                setAutoCalculateButton(row, false);
                updateRowCalculation(row);
                hitungTotal();
                alert('Jumlah PPh 23, kerugian, dan administrasi bank melebihi saldo invoice.');
                return;
            }

            row.dataset.autoCalculate = '1';
            setAutoCalculateButton(row, true);
            updateRowCalculation(row);
            hitungTotal();
        });

        document.getElementById('btnUpdate').addEventListener('click', function() {
            const updateButton = this;
            const details = [];
            let total = 0;

            document.querySelectorAll('#invoiceBody tr').forEach(function(row) {
                const el = row.querySelector('.bayar'); if (!el) return;
                const bayar = parseMoney(el.value); const pph23 = parseMoney(row.querySelector('.pph23').value); const discount = parseMoney(row.querySelector('.discount').value); const bank_admin = parseMoney(row.querySelector('.bank-admin').value);
                if (bayar + pph23 + discount + bank_admin <= 0) return;
                total += bayar;
                details.push({ nofaktur: el.dataset.faktur, bayar, pph23, discount, bank_admin });
            });

            if (total <= 0) {
                alert('Belum ada pembayaran');
                return;
            }

            updateButton.disabled = true;
            updateButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Mengupdate...';

            fetch("{{ route('pelunasan-piutang.update', $barh->id) }}", {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        tglbayar: document.getElementById('tglbayar').value,
                        customer: customerInput.value,
                        jenis_bayar: document.getElementById('jenis_bayar').value,
                        payment_coa_id: document.getElementById('payment_coa_id').value,
                        pph23_coa_id: document.getElementById('pph23_coa_id').value,
                        discount_coa_id: document.getElementById('discount_coa_id').value,
                        bank_admin_coa_id: document.getElementById('bank_admin_coa_id').value,
                        no_referensi: document.getElementById('no_referensi').value,
                        keterangan: document.getElementById('keterangan').value,
                        total_bayar: total,
                        details: details
                    })
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type') || '';
                    const data = contentType.includes('application/json')
                        ? await response.json()
                        : { success: false, message: await response.text() };

                    if (!response.ok && data.success !== true) {
                        throw data;
                    }

                    return data;
                })
                .then(function(data) {
                    if (data.success) {
                        window.location.href = data.redirect;
                        return;
                    }

                    alert(data.message || 'Gagal update pelunasan');
                    updateButton.disabled = false;
                    updateButton.innerHTML = 'Update Pelunasan';
                })
                .catch(function(error) {
                    alert(error && error.message ? error.message : 'Gagal update pelunasan');
                    updateButton.disabled = false;
                    updateButton.innerHTML = 'Update Pelunasan';
                });
        });

        renderRows(existingDetails);
    });
</script>

@endsection

@push('styles')
<style>
    .compact-payment-edit .edit-payment-title { font-size: 1.05rem; margin-bottom: 0; }
    .compact-payment-edit .form-label { margin-bottom: .15rem; font-size: .8rem; font-weight: 600; }
    .compact-payment-edit .form-control,
    .compact-payment-edit .form-select,
    .compact-payment-edit .btn { min-height: 32px; padding: .25rem .42rem; font-size: .82rem; }
    .compact-payment-edit .card-body { padding: .5rem; }
    .compact-payment-edit .card-header,
    .compact-payment-edit .alert { padding: .4rem .55rem; font-size: .8rem; }
    .compact-payment-edit .table-responsive { overflow: auto; }
    .compact-payment-edit .invoice-table-scroll {
        max-height: clamp(300px, calc(100vh - 360px), 620px);
        border: 1px solid var(--bs-border-color);
        border-radius: .375rem;
    }
    .compact-payment-edit #invoiceBody { font-size: .8rem; line-height: 1.25; }
    .compact-payment-edit #invoiceBody .form-control { min-height: 30px; padding: .18rem .28rem; font-size: .8rem; }
    .compact-payment-edit table { font-size: .8rem; margin-bottom: .5rem; }
    .compact-payment-edit table th,
    .compact-payment-edit table td { padding: .22rem .28rem; vertical-align: middle; }
    .compact-payment-edit .invoice-table-scroll > table { min-width: 1040px; table-layout: fixed; margin-bottom: 0; }
    .compact-payment-edit .invoice-number-col { width: 135px; }
    .compact-payment-edit .invoice-date-col { width: 100px; }
    .compact-payment-edit .invoice-balance-col { width: 105px; }
    .compact-payment-edit .received-col { width: 130px; }
    .compact-payment-edit .pph-col { width: 105px; }
    .compact-payment-edit .loss-col { width: 105px; }
    .compact-payment-edit .admin-col { width: 105px; }
    .compact-payment-edit .settlement-total-col { width: 105px; }
    .compact-payment-edit .settlement-remaining-col { width: 105px; }
    .compact-payment-edit .calculate-col { width: 75px; }
    .compact-payment-edit .invoice-table-scroll th:last-child,
    .compact-payment-edit .invoice-table-scroll td:last-child {
        position: sticky;
        right: 0;
        z-index: 4;
        background: var(--bs-body-bg);
        box-shadow: -2px 0 3px rgba(0, 0, 0, .06);
    }
    .compact-payment-edit .invoice-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: var(--bs-light);
        white-space: nowrap;
    }
    .compact-payment-edit .invoice-table-scroll thead th:last-child { z-index: 7; }
    .compact-payment-edit #invoiceBody .form-control { width: 100%; min-width: 0; }
    .compact-payment-edit .settlement-status { font-size: .78rem; line-height: 1.2; }
    .compact-payment-edit .btn-hitung-lunas { width: 100%; min-height: 28px; padding: .15rem .2rem; font-size: .7rem; line-height: 1.1; white-space: normal !important; }
    .compact-payment-edit .invoice-table-scroll > table th:nth-child(n+3):nth-child(-n+9),
    .compact-payment-edit .invoice-table-scroll > table td:nth-child(n+3):nth-child(-n+9) { text-align: right; }
    .compact-payment-edit .invoice-dates { white-space: nowrap; line-height: 1.2; }
    .compact-payment-edit .invoice-dates .due-date { margin-top: .15rem; color: var(--bs-danger); font-size: .74rem; }
    .compact-payment-edit .invoice-dates .due-date span { display: inline-block; min-width: 15px; font-weight: 700; }
    .compact-payment-edit #invoiceBody .bayar,
    .compact-payment-edit #invoiceBody .pph23,
    .compact-payment-edit #invoiceBody .discount,
    .compact-payment-edit #invoiceBody .bank-admin { text-align: right; }
    .compact-payment-edit .settlement-remaining-balance { color: #0f3a68; }
</style>
@endpush
