@extends('layouts.app')
@section('title','Duplikat Customer')
@push('styles')
<style>
    .customer-duplicate-table {
        font-size: .78rem;
    }
    .customer-duplicate-table th,
    .customer-duplicate-table td {
        padding: .36rem .45rem;
        vertical-align: middle;
    }
    .customer-duplicate-table .code-col {
        width: 15%;
        min-width: 170px;
    }
    .customer-duplicate-table .name-col {
        width: 18%;
        min-width: 190px;
    }
    .customer-duplicate-table .address-col {
        width: 28%;
        min-width: 280px;
    }
    .customer-duplicate-table .city-col {
        width: 8%;
        min-width: 90px;
    }
    .customer-duplicate-table .count-col {
        width: 5%;
        min-width: 68px;
    }
    .customer-duplicate-table .summary-col {
        width: 16%;
        min-width: 190px;
    }
    .customer-duplicate-table .choice-col {
        width: 4%;
        min-width: 54px;
    }
    .customer-duplicate-table .badge {
        font-size: .62rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column gap-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h1 class="h4 mb-0">Duplikat Customer</h1>
            <div class="text-muted small">Default menampilkan nama sama dengan kode customer kepotong/mirip awalan.</div>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Master Customer</a>
    </div>

    @if(session('success'))<div class="alert alert-success py-2 mb-0">{{ session('success') }}</div>@endif
    @if(session('merge_report'))
        @php($report = session('merge_report'))
        <div class="card border-success shadow-sm">
            <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center gap-2">
                <span class="fw-semibold">Report Penggabungan Customer</span>
                <button type="button" class="btn btn-sm btn-light" id="copyMergeReport"><i class="bi bi-clipboard me-1"></i> Copy Report</button>
            </div>
            <div class="card-body py-2 small">
                <pre id="mergeReportText" class="d-none">REPORT PENGGABUNGAN CUSTOMER
Tanggal: {{ $report['deleted_at'] }}
User: {{ $report['user'] }}

Dihapus: {{ $report['from'] }} - {{ $report['from_name'] ?: '-' }}
Dipakai: {{ $report['to'] }} - {{ $report['to_name'] ?: '-' }}

Transaksi dipindah:
- Piutang: {{ number_format($report['counts']['arh'] ?? 0, 0, ',', '.') }}
- Pelunasan: {{ number_format($report['counts']['barh'] ?? 0, 0, ',', '.') }}
- Hosting: {{ number_format($report['counts']['t_penjualan_hosting'] ?? 0, 0, ',', '.') }}

Alamat: {{ $report['copy_address'] ? 'disalin dari customer yang dihapus' : 'disalin hanya jika alamat tujuan kosong' }}</pre>
                <div class="row g-2">
                    <div class="col-12 col-md-4"><span class="text-muted">Dihapus:</span> <span class="fw-semibold">{{ $report['from'] }}</span> - {{ $report['from_name'] ?: '-' }}</div>
                    <div class="col-12 col-md-4"><span class="text-muted">Dipakai:</span> <span class="fw-semibold">{{ $report['to'] }}</span> - {{ $report['to_name'] ?: '-' }}</div>
                    <div class="col-12 col-md-4"><span class="text-muted">Oleh:</span> {{ $report['user'] }} · {{ $report['deleted_at'] }}</div>
                    <div class="col-12">
                        <span class="text-muted">Transaksi dipindah:</span>
                        Piutang {{ number_format($report['counts']['arh'] ?? 0, 0, ',', '.') }},
                        Pelunasan {{ number_format($report['counts']['barh'] ?? 0, 0, ',', '.') }},
                        Hosting {{ number_format($report['counts']['t_penjualan_hosting'] ?? 0, 0, ',', '.') }}.
                        <span class="text-muted">Alamat:</span> {{ $report['copy_address'] ? 'disalin dari customer yang dihapus' : 'disalin hanya jika alamat tujuan kosong' }}.
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if(session('error'))<div class="alert alert-danger py-2 mb-0">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger py-2 mb-0"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form class="card border-0 shadow-sm" method="GET">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg-3">
                    <label class="form-label small text-muted">Mode Deteksi</label>
                    <select name="mode" class="form-select">
                        <option value="safe" @selected($filters['mode'] === 'safe')>Aman: nama sama + kode kepotong</option>
                        <option value="same_name" @selected($filters['mode'] === 'same_name')>Nama sama saja</option>
                        <option value="address_code" @selected($filters['mode'] === 'address_code')>Alamat sama + kode mirip</option>
                        <option value="name_code" @selected($filters['mode'] === 'name_code')>Nama mirip + kode mirip</option>
                        <option value="all_same_name" @selected($filters['mode'] === 'all_same_name')>Semua nama sama</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label small text-muted">Kemiripan Kode</label>
                    <select name="code_similarity" class="form-select">
                        <option value="prefix" @selected($filters['code_similarity'] === 'prefix')>Kepotong saja</option>
                        <option value="strict" @selected($filters['code_similarity'] === 'strict')>Mirip ketat</option>
                        <option value="normal" @selected($filters['code_similarity'] === 'normal')>Mirip normal</option>
                        <option value="loose" @selected($filters['code_similarity'] === 'loose')>Mirip longgar</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label small text-muted">Transaksi</label>
                    <select name="transaction_status" class="form-select">
                        <option value="all" @selected($filters['transaction_status'] === 'all')>Semua</option>
                        <option value="with" @selected($filters['transaction_status'] === 'with')>Ada transaksi</option>
                        <option value="without" @selected($filters['transaction_status'] === 'without')>Tidak ada transaksi</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Cari Nama</label>
                    <div class="input-group">
                        <input id="filterName" name="name" value="{{ $filters['name'] }}" class="form-control" placeholder="GLOBAL SEAFOOD">
                        <button class="btn btn-outline-secondary open-customer-lookup" type="button" data-target="name"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Cari Customer</label>
                    <div class="input-group">
                        <input id="filterCustomer" name="customer" value="{{ $filters['customer'] }}" class="form-control" placeholder="DPS-GSB">
                        <button class="btn btn-outline-secondary open-customer-lookup" type="button" data-target="customer"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-1 d-grid">
                    <button class="btn btn-primary"><i class="bi bi-search me-1"></i> Filter</button>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label small text-muted">Cari Alamat</label>
                    <input name="address" value="{{ $filters['address'] }}" class="form-control" placeholder="DENPASAR">
                </div>
                <div class="col-12 col-lg-2 d-grid">
                    <a href="{{ route('customers.duplicates') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
                <div class="col-12 col-lg-2 d-grid">
                    <a href="{{ route('customers.duplicates.export', request()->query()) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i> Export Excel</a>
                </div>
            </div>
        </div>
    </form>

    @forelse($groups as $groupIndex => $group)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex flex-column flex-lg-row justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">{{ $group['name'] ?: 'Tanpa Nama' }}</div>
                    <div class="text-muted small">{{ $group['customers']->count() }} kode ditemukan</div>
                </div>
                    <div class="text-muted small">Nama sama, kode customer mirip. Pilih kode yang benar untuk dipertahankan.</div>
            </div>
            <form id="merge-form-{{ $groupIndex }}" method="POST" action="{{ route('customers.merge') }}">
                @csrf
                @foreach($filters as $filterName => $filterValue)
                    <input type="hidden" name="filters[{{ $filterName }}]" value="{{ $filterValue }}">
                @endforeach
                <input type="hidden" name="confirm_merge" value="">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 customer-duplicate-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center choice-col">Pakai</th>
                                    <th class="text-center choice-col">Hapus</th>
                                    <th class="code-col">Kode</th>
                                    <th class="name-col">Nama Customer</th>
                                    <th class="address-col">Alamat</th>
                                    <th class="city-col">Kota</th>
                                    <th class="text-end count-col">Piutang</th>
                                    <th class="text-end count-col">Pelunasan</th>
                                    <th class="text-end count-col">Hosting</th>
                                    <th class="summary-col">Keterangan Transaksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($group['customers'] as $row)
                                <tr>
                                    <td class="text-center"><input class="form-check-input keep-choice" type="radio" name="keep_customer" value="{{ $row['code'] }}" @checked($loop->first)></td>
                                    <td class="text-center"><input class="form-check-input delete-choice" type="radio" name="delete_customer" value="{{ $row['code'] }}" @checked(! $loop->first)></td>
                                    <td class="fw-semibold text-nowrap">{{ $row['code'] }}</td>
                                    <td>{{ $row['name'] ?: '-' }}</td>
                                    <td>{{ $row['address'] ?: '-' }} @if($row['address'] !== '')<span class="badge text-bg-success ms-1">alamat ada</span>@endif</td>
                                    <td>{{ $row['city'] ?: '-' }}</td>
                                    <td class="text-end">{{ number_format($row['counts']['arh'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($row['counts']['barh'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($row['counts']['t_penjualan_hosting'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="small">
                                        @forelse($row['transaction_summary'] as $summary)
                                            <div><span class="fw-semibold">{{ $summary['label'] }}</span> {{ number_format($summary['count'], 0, ',', '.') }}{{ $summary['samples'] ? ': '.$summary['samples'] : '' }}</div>
                                        @empty
                                            <span class="text-muted">Belum ada transaksi</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex flex-column flex-lg-row justify-content-between gap-2">
                    <div>
                        <div class="text-muted small merge-preview">Pilih kode yang dipakai dan kode yang dihapus.</div>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="copy_address" value="1" id="copy-address-{{ $groupIndex }}">
                            <label class="form-check-label small" for="copy-address-{{ $groupIndex }}">Salin alamat dari kode yang dihapus ke kode yang dipakai</label>
                        </div>
                    </div>
                    <div class="d-flex align-items-end">
                        <button class="btn btn-danger"><i class="bi bi-shuffle me-1"></i> Proses Penggabungan</button>
                    </div>
                </div>
            </form>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">Tidak ada kandidat customer dengan nama sama dan kode customer mirip.</div>
        </div>
    @endforelse
</div>

<div class="modal fade" id="mergeConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Penggabungan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 small">Pastikan pilihan sudah benar. Setelah diproses, transaksi akan dipindahkan dan customer yang dihapus tidak muncul lagi.</div>
                <div class="small d-flex flex-column gap-1">
                    <div><span class="text-muted">Pakai:</span> <span id="confirmKeep" class="fw-semibold"></span></div>
                    <div><span class="text-muted">Hapus:</span> <span id="confirmRemove" class="fw-semibold text-danger"></span></div>
                    <div><span class="text-muted">Transaksi terdampak:</span> <span id="confirmCounts"></span></div>
                    <div><span class="text-muted">Alamat:</span> <span id="confirmAddress"></span></div>
                </div>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmMergeCheck">
                    <label class="form-check-label" for="confirmMergeCheck">Saya sudah memeriksa dan yakin ingin menggabungkan customer ini.</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="confirmMergeSubmit" class="btn btn-danger" disabled><i class="bi bi-shuffle me-1"></i> Ya, Gabungkan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="customerLookupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cari Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input id="customerLookupSearch" class="form-control" placeholder="Cari kode, nama, atau alamat">
                    <button id="customerLookupButton" type="button" class="btn btn-primary"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
                <div id="customerLookupLoading" class="text-muted small d-none">Memuat data...</div>
                <div id="customerLookupEmpty" class="text-muted small d-none">Data tidak ditemukan.</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Alamat</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody id="customerLookupRows"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let lookupTarget = 'customer';
let lookupTimer;
let pendingMergeForm = null;
const lookupModalEl = document.getElementById('customerLookupModal');
const lookupModal = lookupModalEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(lookupModalEl) : null;
const mergeConfirmEl = document.getElementById('mergeConfirmModal');
const mergeConfirmModal = mergeConfirmEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(mergeConfirmEl) : null;
const confirmMergeCheck = document.getElementById('confirmMergeCheck');
const confirmMergeSubmit = document.getElementById('confirmMergeSubmit');
const lookupSearch = document.getElementById('customerLookupSearch');
const lookupRows = document.getElementById('customerLookupRows');
const lookupLoading = document.getElementById('customerLookupLoading');
const lookupEmpty = document.getElementById('customerLookupEmpty');
const filterName = document.getElementById('filterName');
const filterCustomer = document.getElementById('filterCustomer');

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));

document.getElementById('copyMergeReport')?.addEventListener('click', () => {
    const text = document.getElementById('mergeReportText')?.textContent?.trim();
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const button = document.getElementById('copyMergeReport');
        button.innerHTML = '<i class="bi bi-check-lg me-1"></i> Tersalin';
        setTimeout(() => button.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Report', 1800);
    });
});

const loadLookupRows = () => {
    const query = lookupSearch.value.trim();
    lookupLoading?.classList.remove('d-none');
    lookupEmpty?.classList.add('d-none');
    if (lookupRows) lookupRows.innerHTML = '';

    fetch(`{{ route('customers.lookup') }}?q=${encodeURIComponent(query)}`, {headers: {'Accept': 'application/json'}})
        .then((response) => response.json())
        .then((rows) => {
            if (!rows.length) {
                lookupEmpty?.classList.remove('d-none');
                return;
            }
            lookupRows.innerHTML = rows.map((row) => `
                <tr>
                    <td class="fw-semibold text-nowrap">${escapeHtml(row.customer)}</td>
                    <td>${escapeHtml(row.name || '-')}</td>
                    <td>${escapeHtml(row.address || '-')}</td>
                    <td class="text-end"><button type="button" class="btn btn-sm btn-primary select-lookup-row" data-customer="${encodeURIComponent(row.customer)}" data-name="${encodeURIComponent(row.name || '')}">Pilih</button></td>
                </tr>
            `).join('');
        })
        .finally(() => lookupLoading?.classList.add('d-none'));
};

document.querySelectorAll('.open-customer-lookup').forEach((button) => {
    button.addEventListener('click', () => {
        lookupTarget = button.dataset.target || 'customer';
        lookupSearch.value = lookupTarget === 'name' ? filterName.value : filterCustomer.value;
        lookupModal?.show();
        setTimeout(() => lookupSearch.focus(), 200);
        loadLookupRows();
    });
});

document.getElementById('customerLookupButton')?.addEventListener('click', loadLookupRows);
lookupSearch?.addEventListener('input', () => {
    clearTimeout(lookupTimer);
    lookupTimer = setTimeout(loadLookupRows, 250);
});
lookupRows?.addEventListener('click', (event) => {
    const button = event.target.closest('.select-lookup-row');
    if (!button) return;
    if (lookupTarget === 'name') {
        filterName.value = decodeURIComponent(button.dataset.name || '');
    } else {
        filterCustomer.value = decodeURIComponent(button.dataset.customer || '');
    }
    lookupModal?.hide();
});

document.querySelectorAll('form[id^="merge-form-"]').forEach((form) => {
    const preview = form.querySelector('.merge-preview');
    const updatePreview = () => {
        const keep = form.querySelector('input[name="keep_customer"]:checked')?.value;
        const remove = form.querySelector('input[name="delete_customer"]:checked')?.value;
        const removeRadio = remove ? Array.from(form.querySelectorAll('input[name="delete_customer"]')).find((radio) => radio.value === remove) : null;
        const removeRow = removeRadio ? removeRadio.closest('tr') : null;
        const counts = removeRow ? Array.from(removeRow.querySelectorAll('td')).slice(6, 9).map((cell) => cell.textContent.trim()).join(' / ') : '0 / 0 / 0';
        if (preview && keep && remove && keep !== remove) {
            preview.textContent = `Akan menggabungkan ${remove} ke ${keep}. Transaksi terdampak Piutang / Pelunasan / Hosting: ${counts}.`;
        } else if (preview) {
            preview.textContent = 'Pilih kode yang dipakai dan kode yang dihapus.';
        }
    };

    form.querySelectorAll('input[type="radio"]').forEach((radio) => radio.addEventListener('change', updatePreview));
    updatePreview();

    form.addEventListener('submit', (event) => {
        const keep = form.querySelector('input[name="keep_customer"]:checked')?.value;
        const remove = form.querySelector('input[name="delete_customer"]:checked')?.value;
        if (!keep || !remove || keep === remove) {
            event.preventDefault();
            alert('Kode yang dipakai dan dihapus harus dipilih dan tidak boleh sama.');
            return;
        }

        if (form.querySelector('input[name="confirm_merge"]').value === '1') {
            return;
        }

        event.preventDefault();
        pendingMergeForm = form;
        const removeRadio = Array.from(form.querySelectorAll('input[name="delete_customer"]')).find((radio) => radio.value === remove);
        const removeRow = removeRadio ? removeRadio.closest('tr') : null;
        const counts = removeRow ? Array.from(removeRow.querySelectorAll('td')).slice(6, 9).map((cell) => cell.textContent.trim()).join(' / ') : '0 / 0 / 0';
        const copyAddress = form.querySelector('input[name="copy_address"]')?.checked;
        document.getElementById('confirmKeep').textContent = keep;
        document.getElementById('confirmRemove').textContent = remove;
        document.getElementById('confirmCounts').textContent = `Piutang / Pelunasan / Hosting: ${counts}`;
        document.getElementById('confirmAddress').textContent = copyAddress ? 'Alamat akan disalin dari customer yang dihapus.' : 'Alamat hanya dilengkapi jika tujuan masih kosong.';
        confirmMergeCheck.checked = false;
        confirmMergeSubmit.disabled = true;
        mergeConfirmModal?.show();
    });
});

confirmMergeCheck?.addEventListener('change', () => {
    confirmMergeSubmit.disabled = !confirmMergeCheck.checked;
});

confirmMergeSubmit?.addEventListener('click', () => {
    if (!pendingMergeForm || !confirmMergeCheck.checked) return;
    pendingMergeForm.querySelector('input[name="confirm_merge"]').value = '1';
    mergeConfirmModal?.hide();
    pendingMergeForm.submit();
});
</script>
@endpush
@endsection
