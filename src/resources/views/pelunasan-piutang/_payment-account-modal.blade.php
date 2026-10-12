<div class="modal fade" id="paymentAccountSearchModal" tabindex="-1" aria-labelledby="paymentAccountSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentAccountSearchModalLabel">
                    <i class="bi bi-search me-2"></i><span id="coaSearchModalTitle">Cari Akun COA</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="paymentAccountModalSearch" class="form-control mb-3"
                    placeholder="Cari kode atau nama akun COA" autocomplete="off">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Kode Akun</th><th>Nama Akun</th><th class="text-end">Aksi</th></tr>
                        </thead>
                        <tbody id="paymentAccountModalResults"></tbody>
                    </table>
                </div>
                <div id="paymentAccountModalEmpty" class="text-center text-muted py-4 d-none">Akun COA tidak ditemukan.</div>
            </div>
        </div>
    </div>
</div>

@php
    $paymentAccountOptions = $paymentAccounts
        ->map(function ($account) {
            return [
                'id' => $account->id,
                'code' => $account->kode_akun,
                'name' => $account->nama_akun,
            ];
        })
        ->values()
        ->all();
    $mapCoaOptions = function ($accounts) {
        return $accounts->map(function ($account) {
            return ['id' => $account->id, 'code' => $account->kode_akun, 'name' => $account->nama_akun];
        })->values()->all();
    };
    $coaAccountGroups = [
        'payment' => $paymentAccountOptions,
        'pph' => $mapCoaOptions($pphAccounts),
        'discount' => $mapCoaOptions($discountAccounts),
        'bankAdmin' => $mapCoaOptions($bankAdminAccounts),
    ];
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const accountGroups = @json($coaAccountGroups);
    const modalElement = document.getElementById('paymentAccountSearchModal');
    const searchInput = document.getElementById('paymentAccountModalSearch');
    const results = document.getElementById('paymentAccountModalResults');
    const empty = document.getElementById('paymentAccountModalEmpty');
    const title = document.getElementById('coaSearchModalTitle');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    let activeAccounts = [];
    let activeSelect = null;

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value == null ? '' : String(value);
        return element.innerHTML;
    }

    function renderAccounts(query = '') {
        const keyword = query.trim().toLowerCase();
        const filtered = activeAccounts.filter(account =>
            !keyword || String(account.code).toLowerCase().includes(keyword) || String(account.name).toLowerCase().includes(keyword)
        );

        results.innerHTML = filtered.map(account => `
            <tr>
                <td class="fw-semibold">${escapeHtml(account.code)}</td>
                <td>${escapeHtml(account.name)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-primary btn-sm pilih-payment-account" data-id="${account.id}">Pilih</button>
                </td>
            </tr>
        `).join('');
        empty.classList.toggle('d-none', filtered.length > 0);
    }

    document.querySelectorAll('.btn-coa-search').forEach(button => {
        button.addEventListener('click', function () {
            activeAccounts = accountGroups[this.dataset.group] || [];
            activeSelect = document.getElementById(this.dataset.target);
            title.textContent = this.dataset.title || 'Cari Akun COA';
            searchInput.value = '';
            renderAccounts();
            modal.show();
        });
    });

    modalElement.addEventListener('shown.bs.modal', () => searchInput.focus());
    searchInput.addEventListener('input', () => renderAccounts(searchInput.value));
    results.addEventListener('click', function (event) {
        const button = event.target.closest('.pilih-payment-account');
        if (!button || !activeSelect) return;
        activeSelect.value = button.dataset.id;
        activeSelect.dispatchEvent(new Event('change', { bubbles: true }));
        modal.hide();
    });
});
</script>
