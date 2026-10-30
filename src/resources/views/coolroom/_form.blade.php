@php
    $coolroom = $coolroom ?? null;
    $isEditing = isset($coolroom);
    $selectedCustomerCode = old('CUSTOMER_KODE', $coolroom?->CUSTOMER_KODE ?? '');
    $selectedCustomerName = old('CUSTOMER', $coolroom?->CUSTOMER ?? '');
@endphp

<div class="container-fluid" data-debug="transaksiCoolroom{{ $isEditing ? 'Edit' : 'Create' }}">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="small text-uppercase fw-semibold text-primary mb-1">Transaksi / Coolroom</div>
            <h1 class="page-title mb-1">{{ $isEditing ? 'Edit Surat Jalan' : 'Transaksi Baru' }}</h1>
            <p class="text-muted mb-0">Nomor surat jalan dibuat otomatis setelah transaksi disimpan.</p>
        </div>
        <a href="{{ $isEditing ? route('coolroom.show', $coolroom) : route('coolroom.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-semibold mb-1">Periksa kembali data transaksi.</div>
            <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEditing ? route('coolroom.update', $coolroom) : route('coolroom.store') }}" id="coolroomForm" data-debug="transaksiCoolroom{{ $isEditing ? 'Edit' : 'Create' }}Form">
        @csrf
        @if ($isEditing) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-xl-5">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h2 class="h6 mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Informasi Surat Jalan</h2>
                    </div>
                    <div class="card-body">
                        @if ($isEditing)
                            <div class="mb-3">
                                <label class="form-label" for="NOSJ">Nomor SJ</label>
                                <input id="NOSJ" class="form-control bg-light font-monospace" value="{{ $coolroom->NOSJ }}" readonly>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="TGLSJ">Tanggal SJ <span class="text-danger">*</span></label>
                            <input type="date" name="TGLSJ" id="TGLSJ" class="form-control" value="{{ old('TGLSJ', $coolroom?->TGLSJ?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="CUSTOMER">Customer <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="hidden" name="CUSTOMER_KODE" id="CUSTOMER_KODE" value="{{ $selectedCustomerCode }}">
                                <input type="text" name="CUSTOMER" id="CUSTOMER" class="form-control bg-light" value="{{ $selectedCustomerName }}" placeholder="Pilih customer dari daftar" readonly required>
                                <button type="button" class="btn btn-outline-primary" id="coolroomCustomerOpen" data-bs-toggle="modal" data-bs-target="#coolroomCustomerModal" data-debug="transaksiCoolroom{{ $isEditing ? 'Edit' : 'Create' }}CustomerPicker">
                                    <i class="bi bi-search me-1"></i>Pilih
                                </button>
                            </div>
                            @error('CUSTOMER_KODE')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="JUMLAH">Jumlah (KG) <span class="text-danger">*</span></label>
                            <input type="number" name="JUMLAH" id="JUMLAH" class="form-control text-end" min="0" step="0.001" value="{{ old('JUMLAH', $coolroom?->JUMLAH ?? '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="HARGA">Harga per KG <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="HARGA" id="HARGA" class="form-control text-end" min="0" step="1" value="{{ old('HARGA', $coolroom?->HARGA ?? '') }}" required>
                            </div>
                        </div>

                        <div class="form-check form-switch border rounded p-3 ps-5 mb-3 bg-light">
                            <input class="form-check-input" type="checkbox" name="BOXING" id="BOXING" value="1" @checked(old('BOXING', $coolroom?->BOXING ?? false))>
                            <label class="form-check-label fw-semibold" for="BOXING">Harga Boxing</label>
                            <div class="form-text">Jika dipilih, harga dianggap total paket dan tidak dikalikan jumlah KG.</div>
                        </div>

                        <div>
                            <label class="form-label" for="KETERANGAN">Keterangan</label>
                            <textarea name="KETERANGAN" id="KETERANGAN" class="form-control" rows="3" maxlength="2000" placeholder="Catatan untuk surat jalan">{{ old('KETERANGAN', $coolroom?->KETERANGAN ?? '') }}</textarea>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-7">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h2 class="h6 mb-0 fw-bold"><i class="bi bi-calculator me-2 text-success"></i>Perhitungan Tagihan</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="coolroomSubtotal">Subtotal</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input id="coolroomSubtotal" class="form-control text-end bg-light" readonly></div>
                                <div class="form-text" id="coolroomSubtotalHint">Jumlah KG x harga per KG</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="DISC">Diskon (%)</label>
                                <input type="number" name="DISC" id="DISC" class="form-control text-end" min="0" max="100" step="0.01" value="{{ old('DISC', $coolroom?->DISC ?? 0) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="coolroomDiscount">Potongan</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input id="coolroomDiscount" class="form-control text-end bg-light" readonly></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="coolroomDpp">DPP</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input id="coolroomDpp" class="form-control text-end bg-light" readonly></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="PPN">PPN (%)</label>
                                <input type="number" name="PPN" id="PPN" class="form-control text-end" min="0" max="100" step="0.01" value="{{ old('PPN', $coolroom?->PPN ?? $ppnRate) }}" required>
                                <div class="form-text">Tarif awal dari pengaturan pajak.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="coolroomPpnAmount">Nilai PPN</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input id="coolroomPpnAmount" class="form-control text-end bg-light" readonly></div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center rounded-3 bg-primary text-white px-3 py-3">
                                    <div><div class="small text-white-50">GRAND TOTAL</div><div class="small">Periksa nilai sebelum menyimpan</div></div>
                                    <div class="fs-3 fw-bold" id="coolroomGrand">Rp 0</div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info small mt-3 mb-0" role="note">Nominal dihitung ulang oleh server saat transaksi disimpan. Perhitungan di layar merupakan pratinjau.</div>
                    </div>
                </section>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ $isEditing ? route('coolroom.show', $coolroom) : route('coolroom.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" data-debug="transaksiCoolroom{{ $isEditing ? 'Edit' : 'Create' }}Submit">
                <i class="bi bi-check2-circle me-1"></i>{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Surat Jalan' }}
            </button>
        </div>
    </form>
</div>

<div class="modal fade" id="coolroomCustomerModal" tabindex="-1" aria-labelledby="coolroomCustomerModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div><h2 class="modal-title fs-5" id="coolroomCustomerModalTitle">Pilih Customer</h2><p class="small text-muted mb-0">Cari berdasarkan kode, nama, atau telepon.</p></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="coolroomCustomerSearch" class="row g-2 mb-3">
                    <div class="col"><label class="visually-hidden" for="coolroomCustomerQuery">Cari customer</label><input type="search" id="coolroomCustomerQuery" class="form-control" placeholder="Ketik kode atau nama customer"></div>
                    <div class="col-auto"><button type="submit" class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Cari</button></div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Kode</th><th>Nama Customer</th><th>Telepon</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody id="coolroomCustomerRows"><tr><td colspan="4" class="text-center text-muted py-4">Masukkan pencarian untuk memuat customer.</td></tr></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 small">
                    <span id="coolroomCustomerPageInfo" class="text-muted"></span>
                    <div class="btn-group btn-group-sm"><button type="button" id="coolroomCustomerPrev" class="btn btn-outline-secondary" disabled>Sebelumnya</button><button type="button" id="coolroomCustomerNext" class="btn btn-outline-secondary" disabled>Berikutnya</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const customerUrl = @json(route('coolroom.customer.search'));
    const customerModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('coolroomCustomerModal'));
    let customerPage = 1;
    let customerLastPage = 1;
    const value = id => Number(document.getElementById(id).value || 0);
    const rupiah = amount => `Rp ${Math.round(amount || 0).toLocaleString('id-ID')}`;

    function calculateCoolroom() {
        const quantity = value('JUMLAH');
        const price = value('HARGA');
        const discountRate = value('DISC');
        const ppnRate = value('PPN');
        const boxing = document.getElementById('BOXING').checked;
        const subtotal = Math.round(boxing ? price : quantity * price);
        const discount = Math.round(subtotal * discountRate / 100);
        const dpp = Math.round(subtotal - discount);
        const ppnAmount = Math.round(dpp * ppnRate / 100);
        const grand = Math.round(dpp + ppnAmount);

        document.getElementById('coolroomSubtotal').value = rupiah(subtotal);
        document.getElementById('coolroomDiscount').value = rupiah(discount);
        document.getElementById('coolroomDpp').value = rupiah(dpp);
        document.getElementById('coolroomPpnAmount').value = rupiah(ppnAmount);
        document.getElementById('coolroomGrand').textContent = rupiah(grand);
        document.getElementById('coolroomSubtotalHint').textContent = boxing ? 'Harga Boxing: harga dianggap satu paket' : 'Jumlah KG x harga per KG';
    }

    async function loadCustomers(page = 1) {
        const body = document.getElementById('coolroomCustomerRows');
        body.replaceChildren();
        const loading = document.createElement('tr');
        loading.innerHTML = '<td colspan="4" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Memuat customer...</td>';
        body.append(loading);

        const formData = new FormData();
        formData.set('q', document.getElementById('coolroomCustomerQuery').value.trim());
        formData.set('page', page);
        try {
            const response = await fetch(customerUrl, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest'},
                body: formData,
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Data customer gagal dimuat.');

            body.replaceChildren();
            payload.data.forEach(customer => {
                const row = document.createElement('tr');
                const code = document.createElement('td');
                code.className = 'font-monospace';
                code.textContent = customer.CUSTOMER || '';
                const name = document.createElement('td');
                name.textContent = customer.NAMACUST || '';
                const phone = document.createElement('td');
                phone.textContent = customer.TELEPON || '-';
                const action = document.createElement('td');
                action.className = 'text-end';
                const select = document.createElement('button');
                select.type = 'button';
                select.className = 'btn btn-sm btn-primary coolroom-select-customer';
                select.textContent = 'Pilih';
                select.dataset.code = customer.CUSTOMER || '';
                select.dataset.name = customer.NAMACUST || '';
                action.append(select);
                row.append(code, name, phone, action);
                body.append(row);
            });
            if (!payload.data.length) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="4" class="text-center text-muted py-4">Customer tidak ditemukan.</td>';
                body.append(row);
            }

            customerPage = payload.current_page;
            customerLastPage = payload.last_page;
            document.getElementById('coolroomCustomerPageInfo').textContent = `Halaman ${customerPage} dari ${customerLastPage}`;
            document.getElementById('coolroomCustomerPrev').disabled = customerPage <= 1;
            document.getElementById('coolroomCustomerNext').disabled = customerPage >= customerLastPage;
        } catch (error) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 4;
            cell.className = 'text-center text-danger py-4';
            cell.textContent = error.message;
            row.append(cell);
            body.replaceChildren(row);
        }
    }

    ['JUMLAH', 'HARGA', 'DISC', 'PPN', 'BOXING'].forEach(id => {
        document.getElementById(id).addEventListener('input', calculateCoolroom);
        document.getElementById(id).addEventListener('change', calculateCoolroom);
    });
    document.getElementById('coolroomCustomerOpen').addEventListener('click', () => {
        customerModal.show();
        loadCustomers(1);
    });
    document.getElementById('coolroomCustomerSearch').addEventListener('submit', event => {
        event.preventDefault();
        loadCustomers(1);
    });
    document.getElementById('coolroomCustomerPrev').addEventListener('click', () => loadCustomers(customerPage - 1));
    document.getElementById('coolroomCustomerNext').addEventListener('click', () => loadCustomers(customerPage + 1));
    document.getElementById('coolroomCustomerRows').addEventListener('click', event => {
        const button = event.target.closest('.coolroom-select-customer');
        if (!button) return;
        document.getElementById('CUSTOMER_KODE').value = button.dataset.code;
        document.getElementById('CUSTOMER').value = button.dataset.name;
        customerModal.hide();
        document.getElementById('JUMLAH').focus();
    });

    calculateCoolroom();
})();
</script>
@endpush
