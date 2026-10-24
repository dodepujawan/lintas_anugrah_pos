@extends('layouts.app')

@section('content')
<div class="container-fluid" id="invoiceGabungPage" data-debug="transaksiExpedisiInvoiceGabung">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div><h1 class="page-title mb-1">Gabung Surat Jalan Expedisi</h1><p class="text-muted small mb-0">Kelompokkan minimal dua SJ sebelum invoice dibuat.</p></div>
        <a href="{{ route('expedisi-invoice.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div id="alertBox"></div>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label small">Status</label><select id="filterStatus" class="form-select form-select-sm"><option value="belum">Belum Gabung</option><option value="sudah">Sudah Gabung</option></select></div>
                <div class="col-md-2"><label class="form-label small">Tanggal Mulai</label><input type="date" id="filterStart" class="form-control form-control-sm"></div>
                <div class="col-md-2"><label class="form-label small">Tanggal Akhir</label><input type="date" id="filterEnd" class="form-control form-control-sm"></div>
                <div class="col-md-4"><label class="form-label small">Cari No Muat / SJ / Customer / Rute</label><input type="search" id="filterSearch" class="form-control form-control-sm"></div>
                <div class="col-md-2"><button type="button" id="btnFilter" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i> Tampilkan</button></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center"><strong>Data Muat</strong><span class="small text-muted">Klik satu baris sebagai acuan customer</span></div>
        <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0" id="masterTable"><thead class="table-light"><tr><th></th><th>No</th><th>No Muat</th><th>Tgl Muat</th><th>Customer</th><th>No SJ</th><th>Pesanan</th><th>Rute</th><th class="text-end">Jumlah</th><th class="text-end">Harga</th><th class="text-end">Grand</th><th>GB</th></tr></thead><tbody></tbody></table></div>
    </div>

    <div class="card shadow-sm mb-3 d-none" id="groupPanel">
        <div class="card-header d-flex justify-content-between"><strong id="groupTitle">Detail Gabung SJ</strong><button type="button" id="btnCancel" class="btn btn-link btn-sm">Kembali ke daftar</button></div>
        <div class="card-body">
            <form id="groupForm">
                @csrf
                <input type="hidden" id="groupNo"><input type="hidden" id="customerCode">
                <div class="row g-2 mb-3">
                    <div class="col-md-3"><label class="form-label small">Nomor Gabung</label><input id="groupNumber" class="form-control form-control-sm" readonly></div>
                    <div class="col-md-5"><label class="form-label small">Customer</label><input id="customerName" class="form-control form-control-sm" readonly></div>
                    <div class="col-md-4"><label class="form-label small">Item/Pesanan Gabung</label><input id="groupItem" class="form-control form-control-sm"></div>
                </div>
                <div class="row g-3">
                    <div class="col-lg-7"><div class="border rounded"><div class="p-2 bg-light fw-semibold">SJ yang dapat ditambahkan</div><div class="table-responsive"><table class="table table-sm mb-0" id="availableTable"><thead><tr><th><input type="checkbox" id="selectAvailable"></th><th>No Muat</th><th>Tgl</th><th>No SJ</th><th>Pesanan</th><th class="text-end">Grand</th></tr></thead><tbody></tbody></table></div></div></div>
                    <div class="col-lg-5"><div class="border rounded"><div class="p-2 bg-light fw-semibold">SJ terpilih <span class="badge bg-primary" id="selectedCount">0</span></div><div class="table-responsive"><table class="table table-sm mb-0" id="selectedTable"><thead><tr><th>No SJ</th><th>Pesanan</th><th class="text-end">Jumlah</th><th></th></tr></thead><tbody></tbody></table></div></div></div>
                </div>
                <hr>
                <div class="row g-2 align-items-end">
                    <div class="col-md-2"><label class="form-label small">Harga</label><input type="number" min="0" step="1" id="amountPrice" class="form-control form-control-sm" value="0"></div>
                    <div class="col-md-2"><label class="form-label small">Diskon (%)</label><input type="number" min="0" max="100" step="0.01" id="amountDiscount" class="form-control form-control-sm" value="0"></div>
                    <div class="col-md-2"><label class="form-label small">Del Charge</label><input type="number" min="0" step="1" id="amountDc" class="form-control form-control-sm" value="0"></div>
                    <div class="col-md-2"><label class="form-label small">PPN (%)</label><input type="number" min="0" step="0.01" id="amountPpn" class="form-control form-control-sm" value="11"></div>
                    <div class="col-md-2"><label class="form-label small">DPP</label><input id="amountTotal" class="form-control form-control-sm" readonly></div>
                    <div class="col-md-2"><label class="form-label small">Grand Total</label><input id="amountGrand" class="form-control form-control-sm fw-bold" readonly></div>
                </div>
                <div class="mt-3 text-end"><button type="submit" id="btnSave" class="btn btn-success" disabled><i class="bi bi-save"></i> Simpan Gabungan</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const urls = { list: @json(route('expedisi-invoice.data.muat')), available: @json(route('expedisi-invoice.data.gabung')), existing: @json(route('expedisi-invoice.data.existing')), store: @json(route('expedisi-invoice.store')), update: @json(route('expedisi-invoice.update', ['noInvoice' => '__GB__'])) };
    let selected = [], current = null;
    const $ = id => document.getElementById(id), money = n => Number(n || 0).toLocaleString('id-ID');
    const esc = s => String(s ?? '-').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    async function get(url, params = {}) { const q = new URLSearchParams(params); const r = await fetch(url + (q.toString() ? '?' + q : ''), {headers:{'Accept':'application/json'}}); if (!r.ok) throw new Error('Gagal mengambil data.'); return r.json(); }
    function alertBox(message, type='danger') { $('alertBox').innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">${esc(message)}<button class="btn-close" data-bs-dismiss="alert"></button></div>`; }
    function rowData(r) { return `<td><button type="button" class="btn btn-link btn-sm p-0 pick-master">Pilih</button></td><td>${r.DT_RowIndex}</td><td>${esc(r.NOMUAT)}</td><td>${esc(r.TGLMUAT)}</td><td>${esc(r.CUSTOMER)}</td><td>${esc(r.NOSJ)}</td><td>${esc(r.PESANANGB || r.PESANAN)}</td><td>${esc(r.rute)}</td><td class="text-end">${esc(r.JUMLAH)}</td><td class="text-end">${esc(r.harga_formatted)}</td><td class="text-end">${esc(r.gtotal_formatted)}</td><td>${esc(r.GB)}</td>`; }
    async function loadMaster() { const data = await get(urls.list, {filter_invoice:$('filterStatus').value, tgl_mulai:$('filterStart').value, tgl_akhir:$('filterEnd').value, search_muat:$('filterSearch').value}); $('masterTable').querySelector('tbody').innerHTML = (data.data || []).map(r => `<tr data-row='${esc(JSON.stringify(r))}'>${rowData(r)}</tr>`).join('') || '<tr><td colspan="12" class="text-center text-muted py-4">Tidak ada data.</td></tr>'; }
    function renderSelected() { $('selectedCount').textContent = selected.length; $('selectedTable').querySelector('tbody').innerHTML = selected.map((r,i) => `<tr><td>${esc(r.NOSJ)}</td><td>${esc(r.PESANAN)}</td><td class="text-end">${esc(r.JUMLAH)}</td><td><button type="button" class="btn btn-sm btn-outline-danger remove-selected" data-index="${i}"><i class="bi bi-x"></i></button></td></tr>`).join(''); $('btnSave').disabled = selected.length < 2; }
    function calculate() { const price=+$('amountPrice').value||0, disc=Math.min(100, Math.max(0, +$('amountDiscount').value||0)), dc=+$('amountDc').value||0, ppn=+$('amountPpn').value||0; const total=Math.max(0, Math.round(price-price*disc/100+dc)), grand=Math.round(total+total*ppn/100); $('amountTotal').value=money(total); $('amountGrand').value=money(grand); }
    async function openGroup(row) { current=row; selected=[]; $('groupPanel').classList.remove('d-none'); $('groupTitle').textContent = row.GB ? 'Edit Gabungan SJ' : 'Buat Gabungan SJ'; $('groupNo').value=row.GB||''; $('groupNumber').value=row.GB||'(otomatis saat simpan)'; $('customerCode').value=row.CUSTOMER_KODE||''; $('customerName').value=row.CUSTOMER||''; $('groupItem').value=row.PESANANGB||row.PESANAN||''; $('amountPrice').value=row.HARGA||0; $('amountDiscount').value=row.DISC||0; $('amountDc').value=row.DC||0; $('amountPpn').value=row.PPN||11; calculate(); const data = row.GB ? await get(urls.existing,{gbExist:row.GB}) : await get(urls.available,{customer_kode:row.CUSTOMER_KODE}); selected = row.GB ? (data.data||[]) : [row]; $('availableTable').querySelector('tbody').innerHTML = (row.GB ? [] : (data.data||[])).filter(r=>!selected.some(s=>s.NOSJ===r.NOSJ)).map((r,i)=>`<tr data-row='${esc(JSON.stringify(r))}'><td><input type="checkbox" class="available-check"></td><td>${esc(r.NOMUAT)}</td><td>${esc(r.TGLMUAT)}</td><td>${esc(r.NOSJ)}</td><td>${esc(r.PESANAN)}</td><td class="text-end">${esc(r.gtotal_formatted)}</td></tr>`).join('') || '<tr><td colspan="6" class="text-center text-muted">Tidak ada SJ lain.</td></tr>'; renderSelected(); }
    $('masterTable').addEventListener('click', e => { const tr=e.target.closest('tr[data-row]'); if (!tr) return; try { openGroup(JSON.parse(tr.dataset.row)); } catch (_) { alertBox('Data tidak dapat dibaca.'); } });
    $('availableTable').addEventListener('change', e => { if (!e.target.classList.contains('available-check')) return; const tr=e.target.closest('tr'); const r=JSON.parse(tr.dataset.row); if(e.target.checked) selected.push(r); else selected=selected.filter(x=>x.NOSJ!==r.NOSJ); renderSelected(); });
    $('selectedTable').addEventListener('click', e => { const b=e.target.closest('.remove-selected'); if(b) { selected.splice(+b.dataset.index,1); renderSelected(); } });
    $('selectAvailable').addEventListener('change', e => document.querySelectorAll('.available-check').forEach(x => { x.checked=e.target.checked; x.dispatchEvent(new Event('change')); }));
    ['amountPrice','amountDiscount','amountDc','amountPpn'].forEach(id => $(id).addEventListener('input', calculate));
    $('btnFilter').addEventListener('click', () => loadMaster().catch(e=>alertBox(e.message))); $('btnCancel').addEventListener('click', () => $('groupPanel').classList.add('d-none'));
    $('filterStatus').addEventListener('change', () => loadMaster().catch(e=>alertBox(e.message)));
    $('groupForm').addEventListener('submit', async e => { e.preventDefault(); if(selected.length<2) return alertBox('Pilih minimal dua Surat Jalan.','warning'); const payload={nosj_list:selected.map(x=>x.NOSJ), no_invoice:$('groupNo').value||null, item:$('groupItem').value, harga:+$('amountPrice').value||0, diskon:+$('amountDiscount').value||0, dc:+$('amountDc').value||0, ppn:+$('amountPpn').value||0, grand_total:Number($('amountGrand').value.replace(/\./g,''))||0}; const url=payload.no_invoice ? urls.update.replace('__GB__', encodeURIComponent(payload.no_invoice)) : urls.store; try { const r=await fetch(url,{method:payload.no_invoice?'PUT':'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)}); const data=await r.json(); if(!r.ok||!data.status) throw new Error(data.message||'Gagal menyimpan gabungan.'); alertBox(data.message,'success'); $('groupPanel').classList.add('d-none'); $('filterStatus').value='belum'; await loadMaster(); } catch(err) { alertBox(err.message); } });
    loadMaster().catch(e=>alertBox(e.message));
})();
</script>
@endpush
