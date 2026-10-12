@extends('layouts.app')

@section('title', 'Pelunasan Piutang')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm payment-header-card">

        <div class="card-header d-flex justify-content-between align-items-center py-1">
            <div class="fw-semibold"><i class="bi bi-cash-coin me-1"></i>Data Pembayaran</div>
            <a href="{{ route('panduan.pelunasan_piutang') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-book"></i> Panduan
            </a>
        </div>

        <div class="card-body py-1">

            <div class="row g-1 compact-payment-form">

                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input
                        type="date"
                        id="tglbayar"
                        class="form-control"
                        value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Kode Customer</label>

                    <div class="input-group">
                        <input
                            type="text"
                            id="customer"
                            class="form-control"
                            placeholder="Ketik kode atau nama customer"
                            list="customer-list"
                            autocomplete="off">
                        <button type="button" id="btnCustomerModal" class="btn btn-outline-primary" title="Cari customer">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>

                    <datalist id="customer-list"></datalist>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Nama Customer</label>
                    <input type="text" id="customer_name" class="form-control bg-light" placeholder="Terisi setelah customer dipilih" readonly>
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button
                        type="button"
                        id="btnCari"
                        class="btn btn-primary w-100">
                        Cari Invoice
                    </button>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Jenis Bayar</label>

                    <select
                        id="jenis_bayar"
                        class="form-select">

                        <option value="CASH">Cash</option>
                        <option value="TRANSFER" selected>Transfer</option>
                        <option value="GIRO">Giro</option>
                        <option value="CEK">Cek</option>
                        <option value="QRIS">QRIS</option>

                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Akun Pembayaran</label>
                    <div class="input-group">
                        <select id="payment_coa_id" class="form-select">
                            <option value="">Default sesuai jenis bayar</option>
                            @foreach ($paymentAccounts as $account)
                                <option value="{{ $account->id }}" @selected($defaultTransferAccountId === $account->id)>
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

                <div class="col-md-5">
                    <label class="form-label">Akun PPh Pasal 23</label>
                    <div class="input-group">
                        <select id="pph23_coa_id" class="form-select">
                            <option value="">Pilih jika ada potongan PPh 23</option>
                            @foreach ($pphAccounts as $account)
                                <option value="{{ $account->id }}" @selected($defaultPph23AccountId === $account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-coa-search" data-target="pph23_coa_id" data-group="pph" data-title="Cari Akun PPh Pasal 23" title="Cari akun COA"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Akun Kerugian Pembayaran Piutang</label>
                    <div class="input-group">
                        <select id="discount_coa_id" class="form-select">
                            <option value="">Pilih jika ada potongan/kerugian pembayaran</option>
                            @foreach ($discountAccounts as $account)
                                <option value="{{ $account->id }}" @selected($defaultDiscountAccountId === $account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-coa-search" data-target="discount_coa_id" data-group="discount" data-title="Cari Akun Kerugian Pembayaran" title="Cari akun COA"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Akun Beban Administrasi Bank</label>
                    <div class="input-group">
                        <select id="bank_admin_coa_id" class="form-select">
                            <option value="">Pilih jika ada administrasi bank</option>
                            @foreach ($bankAdminAccounts as $account)
                                <option value="{{ $account->id }}" @selected($defaultBankAdminAccountId === $account->id)>{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-coa-search" data-target="bank_admin_coa_id" data-group="bankAdmin" data-title="Cari Akun Beban Administrasi Bank" title="Cari akun COA"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">No Referensi</label>

                    <input
                        type="text"
                        id="no_referensi"
                        class="form-control">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Keterangan</label>

                    <input
                        type="text"
                        id="keterangan"
                        class="form-control">
                </div>

            </div>

        </div>

    </div>

    <div class="card mt-2 shadow-sm invoice-entry-card">

        <div class="card-header py-1 fw-semibold">
            <i class="bi bi-receipt me-1"></i>Invoice Belum Lunas
        </div>

        <div class="card-body p-2">

            <div class="alert alert-info py-1 mb-1 small">
                Untuk pelunasan penuh, isi PPh 23, kerugian, dan administrasi bank terlebih dahulu, lalu klik
                <strong>Hitung Lunas</strong>. Uang diterima akan dihitung otomatis dari sisa saldo invoice.
                Pembayaran sebagian tetap dapat diisi manual.
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
                                Pilih customer terlebih dahulu
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            <div class="invoice-summary-footer mt-2 p-2">
                <div class="summary-item"><small>Uang Diterima</small><strong id="totalBayar">0.00</strong></div>
                <div class="summary-item"><small>PPh 23</small><strong id="totalPph23">0.00</strong></div>
                <div class="summary-item"><small>Kerugian</small><strong id="totalDiscount">0.00</strong></div>
                <div class="summary-item"><small>Adm. Bank</small><strong id="totalBankAdmin">0.00</strong></div>
                <div class="summary-item summary-total"><small>Total Pelunasan</small><strong id="totalPelunasan">0.00</strong></div>
                <button type="button" id="btnSimpan" class="btn btn-success text-nowrap">
                    <i class="bi bi-save me-1"></i>Simpan Pelunasan
                </button>
            </div>

        </div>

    </div>

</div>

@include('pelunasan-piutang._payment-account-modal', ['paymentAccounts' => $paymentAccounts])

<div class="modal fade" id="customerSearchModal" tabindex="-1" aria-labelledby="customerSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerSearchModalLabel"><i class="bi bi-search me-2"></i>Cari Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="customerModalSearch" class="form-control mb-3" placeholder="Ketik minimal 2 karakter kode atau nama customer" autocomplete="off">
                <div id="customerModalLoading" class="text-center text-muted py-4 d-none"><span class="spinner-border spinner-border-sm me-2"></span>Memuat customer...</div>
                <div id="customerModalError" class="alert alert-danger d-none"></div>
                <div id="customerModalEmpty" class="text-center text-muted py-4 d-none">Customer tidak ditemukan.</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Kode</th><th>Nama Customer</th><th></th></tr></thead>
                        <tbody id="customerModalResults"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const customerInput =
            document.getElementById('customer');

        const customerList =
            document.getElementById('customer-list');

        const customerNameInput =
            document.getElementById('customer_name');

        const invoiceBody =
            document.getElementById('invoiceBody');

        const totalBayar =
            document.getElementById('totalBayar');

        const totalPph23 = document.getElementById('totalPph23');
        const totalDiscount = document.getElementById('totalDiscount');
        const totalBankAdmin = document.getElementById('totalBankAdmin');
        const totalPelunasan = document.getElementById('totalPelunasan');
        const defaultTransferAccountId = @json($defaultTransferAccountId);
        const defaultCashAccountId = @json($defaultCashAccountId);
        const defaultBankAccountId = @json($defaultBankAccountId);

        const customerModalElement = document.getElementById('customerSearchModal');
        const customerModal = bootstrap.Modal.getOrCreateInstance(customerModalElement);
        const customerModalSearch = document.getElementById('customerModalSearch');
        const customerModalResults = document.getElementById('customerModalResults');
        const customerModalLoading = document.getElementById('customerModalLoading');
        const customerModalEmpty = document.getElementById('customerModalEmpty');
        const customerModalError = document.getElementById('customerModalError');

        let customerTimer = null;
        const customers = new Map();

        function showSelectedCustomer() {
            const selected = customers.get(customerInput.value.trim().toUpperCase());

            if (!selected) {
                customerNameInput.value = '';
                return;
            }

            customerNameInput.value = selected.nama || '';
        }

        function resetInvoiceRows(message = 'Klik Cari Faktur untuk memuat invoice customer.') {
            invoiceBody.innerHTML = `<tr><td colspan="10" class="text-center text-muted">${message}</td></tr>`;
            hitungTotal();
        }

        /*
        |--------------------------------------------------------------------------
        | AUTOCOMPLETE CUSTOMER
        |--------------------------------------------------------------------------
        */

        /*
|--------------------------------------------------------------------------
| AUTOCOMPLETE CUSTOMER
|--------------------------------------------------------------------------
*/

        customerInput.addEventListener('input', function() {

            const q = this.value.trim();
            showSelectedCustomer();
            resetInvoiceRows('Customer berubah. Klik Cari Faktur ulang.');

            if (q.length < 2) {
                customerList.innerHTML = '';
                return;
            }

            clearTimeout(customerTimer);

            customerTimer = setTimeout(function() {

                fetch(
                        "{{ route('pelunasan-piutang.search-customer') }}" +
                        "?q=" +
                        encodeURIComponent(q)
                    )
                    .then(response => response.json())
                    .then(data => {

                        customerList.innerHTML = '';

                        data.forEach(function(item) {

                            item.customer = String(item.customer).trim();
                            item.nama = String(item.nama || '').trim();
                            customers.set(item.customer.toUpperCase(), item);

                            const option =
                                document.createElement('option');

                            option.value =
                                item.customer;

                            option.label =
                                item.label;

                            customerList.appendChild(option);

                        });

                        showSelectedCustomer();

                    })
                    .catch(function(error) {

                        console.error(error);

                    });

            }, 300);

        });

        customerInput.addEventListener('change', function() {
            showSelectedCustomer();
            resetInvoiceRows('Customer berubah. Klik Cari Faktur ulang.');
        });

        /* Enter berfungsi seperti Tab dan tidak pernah menyimpan transaksi. */
        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Enter' || !event.target.matches(
                '#no_referensi, #keterangan, .bayar, .pph23, .discount, .bank-admin'
            )) {
                return;
            }

            event.preventDefault();

            if (event.target.classList.contains('bank-admin')) {
                const row = event.target.closest('tr');
                const saldo = parseMoney(row.querySelector('.bayar').dataset.saldo);
                const rowTotal = ['.bayar', '.pph23', '.discount', '.bank-admin']
                    .reduce((sum, selector) => sum + parseMoney(row.querySelector(selector).value), 0);

                if (rowTotal > 0 && rowTotal < saldo) {
                    const difference = saldo - rowTotal;
                    const proceed = confirm(
                        'Total pelunasan invoice ini belum sesuai dengan saldo.\n\n' +
                        'Saldo invoice : ' + formatRupiah(saldo) + '\n' +
                        'Total input    : ' + formatRupiah(rowTotal) + '\n' +
                        'Sisa piutang  : ' + formatRupiah(difference) + '\n\n' +
                        'Klik OK jika memang pembayaran sebagian.\n' +
                        'Klik Batal untuk memperbaiki nilai.'
                    );

                    if (!proceed) {
                        event.target.focus();
                        event.target.select();
                        return;
                    }
                }
            }

            const fields = Array.from(document.querySelectorAll(
                '#no_referensi, #keterangan, .bayar, .pph23, .discount, .bank-admin'
            )).filter(field => !field.disabled && field.offsetParent !== null);
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

        document.getElementById('jenis_bayar').addEventListener('change', function() {
            if (this.value === 'TRANSFER' && defaultTransferAccountId) {
                document.getElementById('payment_coa_id').value = String(defaultTransferAccountId);
            } else if (this.value === 'CASH' && defaultCashAccountId) {
                document.getElementById('payment_coa_id').value = String(defaultCashAccountId);
            } else if (['GIRO', 'CEK', 'QRIS'].includes(this.value) && defaultBankAccountId) {
                document.getElementById('payment_coa_id').value = String(defaultBankAccountId);
            }
        });

        function escapeHtml(value) {
            const element = document.createElement('div');
            element.textContent = value == null ? '' : String(value);
            return element.innerHTML;
        }

        function loadModalCustomers(query) {
            customerModalLoading.classList.remove('d-none');
            customerModalEmpty.classList.add('d-none');
            customerModalError.classList.add('d-none');

            fetch("{{ route('pelunasan-piutang.search-customer') }}?modal=1&q=" + encodeURIComponent(query), {
                headers: { 'Accept': 'application/json' }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Pencarian customer gagal.');
                    return response.json();
                })
                .then(rows => {
                    customerModalResults.innerHTML = rows.map(item => {
                        const kode = String(item.customer || '').trim();
                        const nama = String(item.nama || '').trim();
                        customers.set(kode.toUpperCase(), { customer: kode, nama: nama, label: item.label });
                        return `<tr><td class="fw-semibold">${escapeHtml(kode)}</td><td>${escapeHtml(nama || '-')}</td><td class="text-end"><button type="button" class="btn btn-primary btn-sm pilih-customer-modal" data-customer="${encodeURIComponent(kode)}">Pilih</button></td></tr>`;
                    }).join('');
                    customerModalEmpty.textContent = query === ''
                        ? 'Belum ada data customer.'
                        : 'Customer tidak ditemukan.';
                    customerModalEmpty.classList.toggle('d-none', rows.length > 0);
                })
                .catch(error => {
                    customerModalError.textContent = error.message;
                    customerModalError.classList.remove('d-none');
                })
                .finally(() => customerModalLoading.classList.add('d-none'));
        }

        let modalTimer = null;
        document.getElementById('btnCustomerModal').addEventListener('click', function() {
            customerModalSearch.value = '';
            customerModal.show();
            loadModalCustomers('');
        });
        customerModalElement.addEventListener('shown.bs.modal', () => customerModalSearch.focus());
        customerModalSearch.addEventListener('input', function() {
            clearTimeout(modalTimer);
            const query = this.value.trim();
            modalTimer = setTimeout(() => loadModalCustomers(query), 300);
        });
        customerModalResults.addEventListener('click', function(event) {
            const button = event.target.closest('.pilih-customer-modal');
            if (!button) return;
            customerInput.value = decodeURIComponent(button.dataset.customer);
            showSelectedCustomer();
            resetInvoiceRows('Customer berubah. Klik Cari Faktur ulang.');
            customerModal.hide();
        });
        /*
        |--------------------------------------------------------------------------
        | CARI INVOICE CUSTOMER
        |--------------------------------------------------------------------------
        */

        document.getElementById('btnCari')
            .addEventListener('click', function() {

                let customer =
                    customerInput.value.trim();

                if (customer === '') {
                    alert('Customer belum dipilih');
                    return;
                }

                fetch(
                        "{{ route('pelunasan-piutang.customer-invoices') }}" +
                        "?customer=" +
                        encodeURIComponent(customer)
                    )
                    .then(response => response.json())
                    .then(data => {

                        let html = '';

                        if (data.length === 0) {
                            html = `
                <tr>
                    <td colspan="10" class="text-center">
                        Tidak ada invoice yang belum lunas
                    </td>
                </tr>
                `;

                            invoiceBody.innerHTML = html;

                            return;
                        }

                        data.forEach(function(item) {

                            html += `
                <tr>

                    <td>

                        ${item.NOFAKTUR}

                        <input
                            type="hidden"
                            class="nofaktur"
                            value="${item.NOFAKTUR}">

                    </td>

                    <td class="invoice-dates">
                        <div>${item.TGLFAKTUR}</div>
                        <div class="due-date"><span>JT</span> ${item.TGLJT}</div>
                    </td>

                    <td class="text-end">

                        ${formatRupiah(item.SALDO)}

                    </td>

                    <td>

                        <input
                            type="text"
                            inputmode="decimal"
                            class="form-control bayar"
                            step="0.01"
                            min="0"
                            max="${item.SALDO}"
                            value="0,00"
                            data-saldo="${item.SALDO}"
                            data-faktur="${item.NOFAKTUR}">

                    </td>

                    <td><input type="text" inputmode="decimal" class="form-control pph23" value="0,00"></td>
                    <td><input type="text" inputmode="decimal" class="form-control discount" value="0,00"></td>
                    <td><input type="text" inputmode="decimal" class="form-control bank-admin" value="0,00"></td>
                    <td class="text-end settlement-status"><strong class="row-settlement-total">0,00</strong></td>
                    <td class="text-end settlement-status"><strong class="row-settlement-remaining settlement-remaining-balance">${formatRupiah(item.SALDO)}</strong></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-hitung-lunas text-nowrap">Hitung Lunas</button>
                    </td>

                </tr>
                `;

                        });

                        invoiceBody.innerHTML = html;

                        hitungTotal();

                    })
                    .catch(function(error) {

                        console.error(error);

                    });

            });

        /*
        |--------------------------------------------------------------------------
        | HITUNG TOTAL BAYAR
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | FUNCTION HITUNG TOTAL
        |--------------------------------------------------------------------------
        */

        function hitungTotal() {
            const sum = selector => Array.from(document.querySelectorAll(selector))
                .reduce((total, el) => total + parseMoney(el.value), 0);
            const bayar = sum('.bayar');
            const pph23 = sum('.pph23');
            const discount = sum('.discount');
            const bankAdmin = sum('.bank-admin');
            const format = value => value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            totalBayar.textContent = format(bayar);
            totalPph23.textContent = format(pph23);
            totalDiscount.textContent = format(discount);
            totalBankAdmin.textContent = format(bankAdmin);
            totalPelunasan.textContent = format(bayar + pph23 + discount + bankAdmin);
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

            totalElement.textContent = formatRupiah(total);
            remainingElement.textContent = formatRupiah(Math.abs(remaining));
            remainingElement.classList.remove('text-success', 'text-danger', 'settlement-remaining-balance');

            if (Math.abs(remaining) < 0.01) {
                remainingElement.textContent = '0,00 (Lunas)';
                remainingElement.classList.add('text-success');
            } else if (remaining > 0) {
                remainingElement.classList.add('settlement-remaining-balance');
            } else {
                remainingElement.textContent += ' (Lebih)';
                remainingElement.classList.add('text-danger');
            }
        }

        function setAutoCalculateButton(row, active) {
            const button = row.querySelector('.btn-hitung-lunas');
            if (!button) return;

            button.classList.toggle('btn-primary', active);
            button.classList.toggle('btn-outline-primary', !active);
            button.innerHTML = active
                ? '<i class="bi bi-calculator me-1"></i>Otomatis Aktif'
                : 'Hitung Lunas';
        }

        function calculateFullPayment(row) {
            const bayarInput = row.querySelector('.bayar');
            const saldo = parseMoney(bayarInput.dataset.saldo);
            const deductions = ['.pph23', '.discount', '.bank-admin']
                .reduce((sum, selector) => sum + parseMoney(row.querySelector(selector).value), 0);

            bayarInput.value = formatRupiah(Math.max(0, saldo - deductions));
            return deductions <= saldo;
        }

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.btn-hitung-lunas');
            if (!button) return;

            const row = button.closest('tr');
            if (!calculateFullPayment(row)) {
                row.dataset.autoCalculate = '0';
                setAutoCalculateButton(row, false);
                alert('Jumlah PPh 23, kerugian, dan administrasi bank melebihi saldo invoice.');
                updateRowCalculation(row);
                hitungTotal();
                return;
            }

            row.dataset.autoCalculate = '1';
            setAutoCalculateButton(row, true);
            updateRowCalculation(row);
            hitungTotal();
        });

        function formatRupiah(value) {
            return Number(value || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
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

        function formatMoneyInput(input) {
            input.value = parseMoney(input.value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        document.addEventListener('focusin', function(event) {
            if (!event.target.matches('.bayar, .pph23, .discount, .bank-admin')) return;
            event.target.value = String(parseMoney(event.target.value));
            event.target.select();
        });

        document.addEventListener('focusout', function(event) {
            if (!event.target.matches('.bayar, .pph23, .discount, .bank-admin')) return;
            formatMoneyInput(event.target);
        });

        /*
        |--------------------------------------------------------------------------
        | SIMPAN
        |--------------------------------------------------------------------------
        */

        let paymentToken = @json($paymentToken);
        let isSavingPayment = false;

        document.getElementById('btnSimpan')
            .addEventListener('click', function() {
                const saveButton = this;

                if (isSavingPayment) {
                    return;
                }

                let total = 0;

                document.querySelectorAll('.bayar')
                    .forEach(function(el) {

                        total += parseMoney(el.value);

                    });

                if (total <= 0) {
                    alert('Belum ada pembayaran');
                    return;
                }

                let details = [];

                document.querySelectorAll('#invoiceBody tr').forEach(function(row) {
                    const bayarEl = row.querySelector('.bayar');
                    if (!bayarEl) return;
                    const bayar = parseMoney(bayarEl.value);
                    const pph23 = parseMoney(row.querySelector('.pph23').value);
                    const discount = parseMoney(row.querySelector('.discount').value);
                    const bank_admin = parseMoney(row.querySelector('.bank-admin').value);
                    if (bayar + pph23 + discount + bank_admin <= 0) return;
                    details.push({ nofaktur: bayarEl.dataset.faktur, bayar, pph23, discount, bank_admin });
                });

                const pph23Total = details.reduce((sum, row) => sum + row.pph23, 0);
                const discountTotal = details.reduce((sum, row) => sum + row.discount, 0);
                const bankAdminTotal = details.reduce((sum, row) => sum + row.bank_admin, 0);
                if (pph23Total > 0 && !document.getElementById('pph23_coa_id').value) {
                    alert('Pilih akun PPh Pasal 23.');
                    return;
                }
                if (discountTotal > 0 && !document.getElementById('discount_coa_id').value) {
                    alert('Pilih akun Kerugian Pembayaran Piutang.');
                    return;
                }
                if (bankAdminTotal > 0 && !document.getElementById('bank_admin_coa_id').value) {
                    alert('Pilih akun beban administrasi bank.');
                    return;
                }

                isSavingPayment = true;
                saveButton.disabled = true;
                saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...';

                fetch(
                        "{{ route('pelunasan-piutang.store') }}", {
                            method: 'POST',
                            credentials: 'same-origin',

                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },

                            body: JSON.stringify({

                                tglbayar: document.getElementById('tglbayar').value,

                                customer: document.getElementById('customer').value,

                                jenis_bayar: document.getElementById('jenis_bayar').value,

                                payment_coa_id: document.getElementById('payment_coa_id').value,

                                pph23_coa_id: document.getElementById('pph23_coa_id').value,

                                discount_coa_id: document.getElementById('discount_coa_id').value,

                                bank_admin_coa_id: document.getElementById('bank_admin_coa_id').value,

                                no_referensi: document.getElementById('no_referensi').value,

                                keterangan: document.getElementById('keterangan').value,

                                total_bayar: total,

                                details: details,

                                _payment_token: paymentToken

                            })

                        }
                    )
                    .then(async response => {
                        const contentType = response.headers.get('content-type') || '';
                        const data = contentType.includes('application/json')
                            ? await response.json()
                            : { success: false, message: await response.text() };

                        if (data && data.success) {
                            return data;
                        }

                        if (!response.ok) {
                            throw data;
                        }

                        return data;
                    })
                    .then(data => {

                        if (data.success) {
                            paymentToken = null;
                            alert(
                                'Pelunasan berhasil disimpan\nNo Bukti : ' +
                                data.nobayar
                            );

                            window.location.href = data.receipt_url || '{{ route('pelunasan-piutang.index') }}';
                            return;
                        }

                        alert(data.message || 'Gagal menyimpan pelunasan.');
                        isSavingPayment = false;
                        saveButton.disabled = false;
                        saveButton.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Pelunasan';
                    })
                    .catch(function(error) {
                        const message = error && error.message
                            ? error.message
                            : 'Gagal menyimpan pelunasan. Periksa koneksi atau ulangi dari halaman input baru.';
                        alert(message);

                        if (error && (error.reload || error.receipt_url)) {
                            window.location.href = error.receipt_url || window.location.href;
                            return;
                        }

                        isSavingPayment = false;
                        saveButton.disabled = false;
                        saveButton.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Pelunasan';
                    });

            });

    });
</script>

@endsection

@push('styles')
<style>
    .invoice-table-scroll {
        max-height: clamp(300px, calc(100vh - 360px), 620px);
        overflow: auto;
        border: 1px solid var(--bs-border-color);
        border-radius: .375rem;
    }

    .invoice-table-scroll table {
        margin-bottom: 0;
        font-size: .82rem;
        line-height: 1.25;
        min-width: 1040px;
        table-layout: fixed;
    }

    .invoice-table-scroll .invoice-number-col { width: 135px; }
    .invoice-table-scroll .invoice-date-col { width: 100px; }
    .invoice-table-scroll .invoice-balance-col { width: 105px; }
    .invoice-table-scroll .received-col { width: 130px; }
    .invoice-table-scroll .pph-col { width: 105px; }
    .invoice-table-scroll .loss-col { width: 105px; }
    .invoice-table-scroll .admin-col { width: 105px; }
    .invoice-table-scroll .settlement-total-col { width: 105px; }
    .invoice-table-scroll .settlement-remaining-col { width: 105px; }
    .invoice-table-scroll .calculate-col { width: 75px; }

    .invoice-table-scroll th:last-child,
    .invoice-table-scroll td:last-child {
        position: sticky;
        right: 0;
        z-index: 4;
        background: var(--bs-body-bg);
        box-shadow: -2px 0 3px rgba(0, 0, 0, .06);
    }

    .invoice-table-scroll thead th:last-child {
        z-index: 7;
        background: var(--bs-light);
    }

    .invoice-table-scroll th,
    .invoice-table-scroll td {
        padding: .24rem .3rem;
        vertical-align: middle;
    }

    .invoice-table-scroll .form-control {
        min-height: 28px;
        padding: .18rem .28rem;
        font-size: .8rem;
        line-height: 1.1;
    }

    .invoice-table-scroll .btn-sm {
        min-height: 27px;
        padding: .16rem .3rem;
        font-size: .76rem;
    }

    .invoice-table-scroll .btn-hitung-lunas {
        width: 100%;
        padding: .14rem .18rem;
        font-size: .72rem;
        line-height: 1.05;
        white-space: normal !important;
    }

    .invoice-table-scroll .settlement-status {
        font-size: .78rem;
        line-height: 1.15;
    }

    .invoice-table-scroll .settlement-remaining-balance {
        color: #0f3a68;
    }

    .invoice-table-scroll table th:nth-child(n+3):nth-child(-n+9),
    .invoice-table-scroll table td:nth-child(n+3):nth-child(-n+9) {
        text-align: right;
    }

    .invoice-table-scroll .invoice-dates {
        white-space: nowrap;
        line-height: 1.2;
    }

    .invoice-table-scroll .invoice-dates .due-date {
        margin-top: .15rem;
        color: var(--bs-danger);
        font-size: .74rem;
    }

    .invoice-table-scroll .invoice-dates .due-date span {
        display: inline-block;
        min-width: 15px;
        font-weight: 700;
    }

    .invoice-table-scroll .bayar,
    .invoice-table-scroll .pph23,
    .invoice-table-scroll .discount,
    .invoice-table-scroll .bank-admin {
        text-align: right;
    }

    .invoice-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background-color: var(--bs-light);
        box-shadow: inset 0 -1px 0 var(--bs-border-color);
        white-space: nowrap;
    }

    .compact-payment-form .form-label {
        font-size: .8rem;
        font-weight: 600;
        margin-bottom: .1rem;
    }

    .compact-payment-form .form-control,
    .compact-payment-form .form-select,
    .compact-payment-form .btn {
        min-height: 30px;
        padding-top: .2rem;
        padding-bottom: .2rem;
        font-size: .82rem;
    }

    .payment-header-card .card-header,
    .invoice-entry-card .card-header {
        min-height: 32px;
        font-size: .85rem;
    }

    .compact-payment-form .card-header,
    .compact-payment-form .alert {
        font-size: .82rem;
    }

    .invoice-summary-footer {
        font-size: .8rem;
    }

    .invoice-summary-footer {
        position: sticky;
        bottom: 0;
        z-index: 6;
        display: grid;
        grid-template-columns: repeat(4, minmax(100px, 1fr)) minmax(130px, 1.2fr) auto;
        gap: .5rem;
        align-items: stretch;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: .5rem;
        box-shadow: 0 -.2rem .75rem rgba(0, 0, 0, .08);
    }

    .summary-item {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: .3rem .55rem;
        border-right: 1px solid var(--bs-border-color);
    }

    .summary-item small { color: var(--bs-secondary-color); }
    .summary-item strong { text-align: right; white-space: nowrap; }
    .summary-total { background: var(--bs-light); border-radius: .35rem; }
    .summary-total strong { color: var(--bs-primary); font-size: 1.05rem; }


    @media (max-width: 991.98px) {
        .invoice-summary-footer { grid-template-columns: repeat(2, 1fr); }
        .invoice-summary-footer .btn { grid-column: 1 / -1; }
        .summary-item { border-right: 0; border-bottom: 1px solid var(--bs-border-color); }
    }
</style>
@endpush
