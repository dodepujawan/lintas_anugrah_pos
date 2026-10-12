@include('jurnal._account-modal')

<style>
    .account-cell select[name="coa_id[]"] { display: none !important; }
    .account-cell .input-group { position: relative; }
    .coa-inline-results {
        display: none;
        position: absolute;
        z-index: 1060;
        top: calc(100% + 2px);
        left: 0;
        right: 0;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
    }
    .coa-inline-results.show { display: block; }
    .coa-inline-option { text-align: left; font-size: .82rem; }
</style>

<script>
(function() {
    const raw = document.getElementById('accountOptionsJson');
    if (!raw) return;

    const accounts = JSON.parse(raw.textContent || '[]').map(function(coa) {
        const label = coa.label || [coa.kode_akun, coa.nama_akun].filter(Boolean).join(' - ');
        const labelParts = label.split(' - ');
        return {
            id: String(coa.id),
            code: coa.kode_akun || labelParts.shift() || '',
            name: coa.nama_akun || labelParts.join(' - ') || '',
            type: coa.tipe || '',
            label: label
        };
    });
    let activeRow = null;
    let modal = null;

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value || '';
        return element.innerHTML;
    }

    function matches(query, limit) {
        const words = query.toLowerCase().trim().split(/\s+/).filter(Boolean);
        return accounts.filter(function(account) {
            const text = `${account.code} ${account.name} ${account.type}`.toLowerCase();
            return words.every(word => text.includes(word));
        }).slice(0, limit);
    }

    function choose(row, account) {
        const select = row.querySelector('select[name="coa_id[]"]');
        const search = row.querySelector('.account-search');
        select.innerHTML = accounts.map(item => `<option value="${item.id}">${escapeHtml(item.label)}</option>`).join('');
        select.value = account.id;
        search.value = account.label;
        row.querySelector('.coa-inline-results')?.classList.remove('show');
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function showInline(row) {
        const box = row.querySelector('.coa-inline-results');
        const found = matches(row.querySelector('.account-search').value, 12);
        box.innerHTML = found.map((account, index) => `<button type="button" class="list-group-item list-group-item-action coa-inline-option${index === 0 ? ' active' : ''}" data-coa-id="${account.id}"><strong>${escapeHtml(account.code)}</strong> - ${escapeHtml(account.name)}<small class="d-block opacity-75">${escapeHtml(account.type)}</small></button>`).join('');
        box.classList.toggle('show', found.length > 0);
    }

    function enhance(row) {
        const group = row.querySelector('.account-cell .input-group');
        if (!group || group.dataset.coaEnhanced) return;
        group.dataset.coaEnhanced = '1';
        const oldIcon = group.querySelector('.input-group-text');
        if (oldIcon) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-outline-secondary btn-coa-help';
            button.title = 'Bantuan cari COA';
            button.setAttribute('aria-label', 'Buka pencarian COA');
            button.innerHTML = '<i class="bi bi-search"></i>';
            oldIcon.replaceWith(button);
            group.appendChild(button);
        }
        const results = document.createElement('div');
        results.className = 'coa-inline-results list-group shadow';
        group.appendChild(results);
    }

    function renderModal(query) {
        const found = matches(query, 100);
        const target = document.getElementById('coaModalResults');
        target.innerHTML = found.map(account => `<tr><td class="fw-semibold text-dark">${escapeHtml(account.code)}</td><td class="text-dark">${escapeHtml(account.name)}</td><td><span class="badge bg-secondary">${escapeHtml(account.type || 'Akun Posting')}</span></td><td class="text-end"><button type="button" class="btn btn-primary btn-sm modal-coa-choice" data-coa-id="${account.id}">Pilih</button></td></tr>`).join('');
        document.getElementById('coaModalEmpty').classList.toggle('d-none', found.length > 0);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const body = document.getElementById('saldoAwalBody');
        if (!body) return;
        body.querySelectorAll('tr').forEach(enhance);
        new MutationObserver(() => body.querySelectorAll('tr').forEach(enhance)).observe(body, { childList: true });
        modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('coaSearchModal'));

        body.addEventListener('input', function(event) {
            if (event.target.classList.contains('account-search')) showInline(event.target.closest('tr'));
        });
        body.addEventListener('click', function(event) {
            const optionButton = event.target.closest('.coa-inline-option');
            if (optionButton) {
                const account = accounts.find(item => item.id === optionButton.dataset.coaId);
                if (account) choose(optionButton.closest('tr'), account);
                return;
            }
            const help = event.target.closest('.btn-coa-help');
            if (help) {
                activeRow = help.closest('tr');
                renderModal('');
                modal.show();
                setTimeout(() => document.getElementById('coaModalSearch')?.focus(), 200);
            }
        });
        body.addEventListener('keydown', function(event) {
            if (!event.target.classList.contains('account-search')) return;
            const row = event.target.closest('tr');
            const options = Array.from(row.querySelectorAll('.coa-inline-option'));
            if (event.key === 'Escape') return row.querySelector('.coa-inline-results').classList.remove('show');
            if (event.key === 'Enter' && options.length) {
                event.preventDefault(); event.stopImmediatePropagation();
                (options.find(item => item.classList.contains('active')) || options[0]).click();
                row.querySelector('input[name="deskripsi_item[]"]')?.focus();
            }
        }, true);

        document.getElementById('coaModalSearch').addEventListener('input', function() { renderModal(this.value); });
        document.getElementById('coaModalResults').addEventListener('click', function(event) {
            const button = event.target.closest('.modal-coa-choice');
            if (!button || !activeRow) return;
            const account = accounts.find(item => item.id === button.dataset.coaId);
            if (account) choose(activeRow, account);
            modal.hide();
        });
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.account-cell')) document.querySelectorAll('.coa-inline-results.show').forEach(box => box.classList.remove('show'));
        });
    });
})();
</script>
