@extends('layouts.app')
@section('title', 'Proses Penjualan Hosting')

@push('styles')
<style>
    .sales-table { min-width: 1180px; font-size:.86rem; }
    .sales-table thead th { position: sticky; top: 0; z-index: 2; background: #f8fafc; }
    .sales-table tfoot th { position: sticky; bottom: 0; z-index: 2; background: #e2e8f0; }
    .sales-table > :not(caption) > * > * { padding:.32rem .48rem; }
    .sales-table thead th, .sales-table tfoot th { padding:.42rem .48rem; white-space:nowrap; }
    .sales-table td { line-height:1.2; }
    .sales-table .customer-line, .sales-table .source-line { white-space:nowrap; }
    .mapping-chip { font-size:.75rem; border-radius:20px; padding:.3rem .65rem; }
    .hosting-compact-card { margin-bottom:.55rem !important; }
    .summary-item { display:flex; align-items:baseline; gap:.4rem; padding:.15rem .65rem; border-right:1px solid #dee2e6; }
    .summary-item:last-child { border-right:0; }
    .print-report-header { display:none; }
    .issue-list { margin:0; padding-left:1.1rem; }
    .issue-list li { margin-bottom:.15rem; }
    .posting-overlay { position:fixed; inset:0; z-index:2000; background:rgba(15,23,42,.72); display:none; align-items:center; justify-content:center; padding:1rem; }
    .posting-overlay.show { display:flex; }
    .posting-progress-card { width:min(520px,100%); background:#fff; border-radius:.75rem; box-shadow:0 1rem 3rem rgba(0,0,0,.3); padding:1.5rem; }
    .hosting-filter-form { font-size:.86rem; }
    .hosting-filter-form .form-label { font-size:.78rem; }
    .hosting-filter-form .form-control-sm,
    .hosting-filter-form .form-select-sm,
    .hosting-filter-form .btn-sm { font-size:.84rem; }
    .hosting-filter-form .date-filter-col { min-width:132px; }
    @media print {
        @page { size: landscape; margin: 10mm; }
        body * { visibility:hidden !important; }
        #problem-print-area, #problem-print-area * { visibility:visible !important; }
        #problem-print-area { position:absolute; inset:0; width:100%; margin:0; box-shadow:none !important; border:0; }
        .print-report-header { display:block; border-bottom:2px solid #334155; margin-bottom:10px; padding-bottom:7px; }
        .print-report-header h1 { font-size:16pt; margin:0 0 3px; color:#0f172a; }
        .print-report-header p { font-size:9pt; margin:0; color:#475569; }
        #problem-print-area .screen-only,
        #problem-print-area .select-column,
        #problem-print-area .status-column,
        #problem-print-area .ready-row,
        #problem-print-area tfoot { display:none !important; }
        #problem-print-area .table-responsive { max-height:none !important; overflow:visible !important; }
        #problem-print-area .sales-table { min-width:0; width:100%; font-size:8.5pt; }
        #problem-print-area .sales-table th { background:#e2e8f0 !important; color:#0f172a; white-space:nowrap; }
        #problem-print-area .sales-table th,
        #problem-print-area .sales-table td { border:1px solid #94a3b8 !important; padding:5px; vertical-align:top; }
        #problem-print-area .table-danger td { background:#fff7f7 !important; }
        #problem-print-area .badge { border:1px solid #64748b; color:#0f172a !important; background:white !important; }
        #problem-print-area tfoot th { position:static; }
        .issue-list { padding-left:14px; color:#991b1b; }
    }
</style>
@endpush

@section('content')
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle"></i> Proses belum dapat dijalankan:</div>
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="card shadow-sm hosting-compact-card"><div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
    <strong class="me-1">Kesiapan mapping:</strong>
    @foreach(['accounts_receivable' => 'Piutang Usaha', 'output_tax' => 'PPN Keluaran', 'sales_revenue_eks' => 'EKS', 'sales_revenue_ren' => 'REN', 'sales_revenue_cold_room' => 'CO'] as $key => $label)
        @php($account = $mappingStatus[$key] ?? null)
        <span class="mapping-chip {{ $account ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}" title="{{ $account ? $account->kode_akun.' - '.$account->nama_akun : 'Belum dimapping' }}">
            <i class="bi {{ $account ? 'bi-check-circle' : 'bi-exclamation-circle' }}"></i> {{ $label }}
        </span>
    @endforeach
    <a href="{{ route('account-mappings.index') }}" class="btn btn-outline-primary btn-sm ms-auto"><i class="bi bi-diagram-3"></i> Mapping Akun</a>
</div></div>

<div class="card shadow-sm hosting-compact-card"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end hosting-filter-form">
        <div class="col-md-4"><label class="form-label small mb-1">Cari invoice/customer</label><input name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Nomor invoice, kode, atau nama customer"></div>
        <div class="col-md-2"><label class="form-label small mb-1">Sumber</label><select name="sumber" class="form-select form-select-sm"><option value="">Semua</option>@foreach($sources as $code => $source)<option value="{{ $code }}" @selected(request('sumber') === $code)>{{ $code }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label small mb-1">Status jurnal</label><select name="status" class="form-select form-select-sm"><option value="pending" @selected(request('status','pending')==='pending')>Belum selesai</option><option value="ready" @selected(request('status')==='ready')>Siap diproses</option><option value="problems" @selected(request('status')==='problems')>Data bermasalah</option><option value="POSTED" @selected(request('status')==='POSTED')>Posted</option><option value="VOID" @selected(request('status')==='VOID')>Void</option><option value="GAGAL" @selected(request('status')==='GAGAL')>Gagal</option><option value="all" @selected(request('status')==='all')>Semua</option></select></div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Tampilkan</button></div>
        <div class="col-md-3"><label class="form-label small mb-1">Tanggal berdasarkan</label><select name="date_field" class="form-select form-select-sm"><option value="invoice" @selected(request('date_field','invoice')==='invoice')>Tanggal Invoice</option><option value="posted" @selected(request('date_field')==='posted')>Tanggal Posting</option></select></div>
        <div class="col-md-3"><label class="form-label small mb-1">Tanggal Awal</label><input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}" class="form-control form-control-sm"></div>
        <div class="col-md-3"><label class="form-label small mb-1">Tanggal Akhir</label><input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="form-control form-control-sm"></div>
    </form>
</div></div>

<form method="POST" action="{{ route('penjualan-hosting.post') }}" id="posting-form">
@csrf
<div class="card shadow-sm" id="problem-print-area">
    <div class="print-report-header">
        <h1>Daftar Penjualan Hosting Bermasalah</h1>
        <p>Dicetak {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; Jumlah data bermasalah: {{ number_format($problemCount, 0, ',', '.') }}</p>
    </div>
    <div class="card-body border-bottom py-1 px-2 screen-only d-flex flex-wrap align-items-center">
        <div class="summary-item"><small class="text-muted">Invoice</small><strong>{{ number_format($totals['count'], 0, ',', '.') }}</strong></div>
        <div class="summary-item"><small class="text-muted">DPP</small><strong>{{ number_format($totals['dpp'], 2, ',', '.') }}</strong></div>
        <div class="summary-item"><small class="text-muted">PPN</small><strong>{{ number_format($totals['ppn'], 2, ',', '.') }}</strong></div>
        <div class="summary-item"><small class="text-muted">Total</small><strong class="text-success">{{ number_format($totals['total'], 2, ',', '.') }}</strong></div>
    </div>
    <div class="card-header py-2 bg-white d-flex flex-wrap gap-2 justify-content-between align-items-end screen-only">
        <div><label class="form-label mb-1">Jatuh tempo</label><div class="input-group input-group-sm"><input type="number" min="0" max="365" name="due_days" value="{{ old('due_days',30) }}" class="form-control" required style="max-width:90px"><span class="input-group-text">hari dari tanggal invoice</span></div></div>
        <div class="d-flex flex-wrap gap-2">
            @if(request('status') === 'POSTED')
                <a class="btn btn-success btn-sm" href="{{ route('penjualan-hosting.export-posted', request()->query()) }}"><i class="bi bi-file-earmark-excel"></i> Export Posted</a>
            @else
            <button type="button" class="btn btn-outline-primary btn-sm" id="select-ready"><i class="bi bi-check2-square"></i> Pilih Semua Siap</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-selection"><i class="bi bi-square"></i> Batalkan Pilihan</button>
            <a class="btn btn-outline-danger btn-sm" href="{{ route('penjualan-hosting.export-problems', request()->only('search', 'sumber', 'tanggal_awal', 'tanggal_akhir', 'date_field')) }}"><i class="bi bi-file-earmark-excel"></i> Export Bermasalah</a>
            @endif
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button>
            @if(request('status') !== 'POSTED')
            <button class="btn btn-success btn-sm" id="posting-submit"><i class="bi bi-journal-check"></i> Proses Jurnal Terpilih</button>
            @endif
        </div>
    </div>
    <div class="table-responsive" style="max-height:calc(100vh - 270px); min-height:420px">
        <table class="table table-striped table-hover align-middle mb-0 sales-table">
            <thead><tr><th class="select-column"><input type="checkbox" id="select-all"></th><th>Invoice</th><th>Tanggal</th><th>Customer</th><th>Jenis</th><th class="text-end">DPP</th><th class="text-end">PPN</th><th class="text-end">Total</th><th class="status-column">Status Jurnal</th><th>Hasil Pemeriksaan</th></tr></thead>
            <tbody>
            @forelse($sales as $sale)
                @php($posted = ($sale->status_jurnal ?? null) === 'POSTED' || !empty($sale->no_jurnal))
                @php($rawJenis = $sale->jenis ?? $sale->sumber ?? '')
                @php($displaySource = strtoupper(trim((string)$rawJenis)) === 'EXSPEDISI' ? 'EKS' : strtoupper(trim((string)$rawJenis)))
                @php($validSource = isset($sources[$displaySource]))
                @php($issues = $sale->validation_issues ?? [])
                <tr class="{{ $issues ? 'table-danger issue-row' : 'ready-row' }}">
                    <td class="select-column">@if(request('status') !== 'POSTED')<input class="invoice-check" type="checkbox" name="invoices[]" value="{{ $sale->invoice }}" @disabled($posted || count($issues) > 0)>@endif</td>
                    <td class="fw-semibold">{{ $sale->invoice }}</td><td>{{ $sale->invoice_date_display }}</td>
                    <td class="customer-line"><strong>{{ $sale->customer ?: '-' }}</strong><span class="text-muted"> - {{ $sale->namacust ?: '-' }}</span></td>
                    <td class="source-line"><span class="badge {{ $validSource ? 'bg-primary' : 'bg-danger' }}">{{ $displaySource ?: '-' }}</span> <small>{{ $validSource ? $sources[$displaySource]['label'] : 'Sumber tidak dikenali' }}</small></td>
                    <td class="text-end">{{ number_format($sale->dpp,2,',','.') }}</td><td class="text-end">{{ number_format($sale->nilai_ppn,2,',','.') }}</td><td class="text-end fw-semibold">{{ number_format($sale->total,2,',','.') }}</td>
                    <td class="status-column">
                        <span class="badge {{ ($sale->status_jurnal ?? '') === 'VOID' ? 'bg-secondary' : ($posted ? 'bg-success' : (($sale->status_jurnal ?? '') === 'GAGAL' ? 'bg-danger' : 'bg-warning text-dark')) }}">{{ $sale->status_jurnal ?: 'BELUM DIPROSES' }}</span>
                    </td>
                    <td class="text-end">
                          @if(($sale->status_jurnal ?? '') === 'VOID')
                              <div class="d-flex gap-1 justify-content-end flex-nowrap">
                                  <span class="badge bg-secondary btn-sm"><i class="bi bi-x-circle"></i> Void</span>
                                  @if(auth()->user()?->hasMenuPermission('penjualan-hosting', 'void'))
                                      <button type="submit" form="revoid-{{ $loop->index }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Revoid</button>
                                  @endif
                                  @if(!empty($sale->void_journal_id))
                                      <a href="{{ route('jurnal.show', $sale->void_journal_id) }}" class="btn btn-info btn-sm"><i class="bi bi-journal-text"></i></a>
                                  @endif
                              </div>
                          @elseif($issues)<ul class="issue-list small text-danger fw-semibold">@foreach($issues as $issue)<li>{{ $issue }}</li>@endforeach</ul>
                          @elseif($sale->posting_error)<div class="small text-danger">{{ $sale->posting_error }}</div>
                          @elseif($posted)
                              <div class="d-flex gap-1 justify-content-end flex-nowrap">
                                  @if(!empty($sale->jurnal_id))
                                      <a href="{{ route('jurnal.show', $sale->jurnal_id) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-journal-text"></i></a>
                                  @endif
                                  <button type="submit" form="repost-{{ $loop->index }}" class="btn btn-outline-warning btn-sm repost-button"><i class="bi bi-arrow-repeat"></i> Rejurnal</button>
                                  @if(($sale->status_jurnal ?? '') === 'POSTED' && auth()->user()?->hasMenuPermission('penjualan-hosting', 'void'))
                                      <button type="submit" form="void-{{ $loop->index }}" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> Void</button>
                                  @endif
                              </div>
                         @else<span class="text-success small">Data siap diproses</span>@endif
                     </td>
                </tr>
            @empty<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data penjualan.</td></tr>@endforelse
            </tbody>
            <tfoot><tr><th class="select-column"></th><th colspan="4" class="text-end">TOTAL</th><th class="text-end">{{ number_format($totals['dpp'],2,',','.') }}</th><th class="text-end">{{ number_format($totals['ppn'],2,',','.') }}</th><th class="text-end">{{ number_format($totals['total'],2,',','.') }}</th><th class="status-column"></th><th></th></tr></tfoot>
        </table>
    </div>
</div>
</form>
@foreach($sales as $sale)
    @if(($sale->status_jurnal ?? '') === 'VOID')
    <form method="POST" action="{{ route('penjualan-hosting.revoid', $sale->invoice) }}" id="revoid-{{ $loop->index }}" class="d-none" onsubmit="return confirm('Revoid invoice ini? Jurnal pembalik void akan dibuat dan piutang dipulihkan. Hanya dapat dilakukan jika belum dibayar.');">@csrf</form>
    @endif
    @php($postedForRepost = ($sale->status_jurnal ?? null) === 'POSTED' || !empty($sale->no_jurnal))
    @if($postedForRepost)
    <form method="POST" action="{{ route('penjualan-hosting.repost', $sale->invoice) }}" id="repost-{{ $loop->index }}" class="d-none repost-form">
        @csrf
        <input type="hidden" name="due_days" value="30" class="repost-due-days">
    </form>
    @if(($sale->status_jurnal ?? '') === 'POSTED')
    <form method="POST" action="{{ route('penjualan-hosting.void', $sale->invoice) }}" id="void-{{ $loop->index }}" class="d-none" onsubmit="return confirm('Void invoice {{ addslashes($sale->invoice) }} dan buat jurnal pembalik?');">@csrf</form>
    @endif
    @endif
@endforeach

<div class="posting-overlay" id="posting-overlay" role="status" aria-live="polite">
    <div class="posting-progress-card">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="spinner-border text-primary" aria-hidden="true"></div>
            <div><h5 class="mb-1">Sedang Memproses Jurnal</h5><div class="text-muted" id="posting-detail">Menyiapkan data invoice...</div></div>
        </div>
        <div class="progress mb-2" style="height:12px">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted">
            <span>Jangan tutup atau muat ulang halaman.</span>
            <span id="posting-elapsed">00:00</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const maxPostingBatch = 100;
const availableInvoices = () => Array.from(document.querySelectorAll('.invoice-check:not(:disabled)'));
function selectPostingBatch() {
    const available = availableInvoices();
    available.forEach((el, index) => el.checked = index < maxPostingBatch);
    if (available.length > maxPostingBatch) {
        alert('Maksimal ' + maxPostingBatch + ' invoice per proses. Sistem memilih 100 invoice pertama. Setelah selesai, proseskan batch berikutnya.');
    }
}
document.getElementById('select-all')?.addEventListener('change', function () {
    if (this.checked) selectPostingBatch();
    else availableInvoices().forEach(el => el.checked = false);
});
document.getElementById('select-ready')?.addEventListener('click', function () {
    selectPostingBatch();
    const selectAll = document.getElementById('select-all');
    if (selectAll) selectAll.checked = true;
});
document.getElementById('clear-selection')?.addEventListener('click', function () {
    document.querySelectorAll('.invoice-check').forEach(el => el.checked = false);
    const selectAll = document.getElementById('select-all');
    if (selectAll) selectAll.checked = false;
});
document.getElementById('posting-form')?.addEventListener('submit', function (event) {
    const selected = document.querySelectorAll('.invoice-check:checked').length;
    if (selected === 0) {
        event.preventDefault();
        alert('Pilih minimal satu invoice yang siap diproses.');
        return;
    }
    if (selected > maxPostingBatch) {
        event.preventDefault();
        alert('Maksimal ' + maxPostingBatch + ' invoice dalam sekali proses. Kurangi pilihan lalu coba kembali.');
        return;
    }
    if (!confirm('Posting ' + selected + ' invoice terpilih menjadi jurnal dan AR? Data yang sudah diposting tidak dapat diproses ulang.')) {
        event.preventDefault();
        return;
    }

    const overlay = document.getElementById('posting-overlay');
    const submit = document.getElementById('posting-submit');
    document.getElementById('posting-detail').textContent = selected + ' invoice sedang divalidasi dan dibuatkan jurnal serta piutang.';
    overlay.classList.add('show');
    submit.disabled = true;
    submit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sedang Diproses...';

    const startedAt = Date.now();
    window.setInterval(function () {
        const seconds = Math.floor((Date.now() - startedAt) / 1000);
        const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
        const remainder = String(seconds % 60).padStart(2, '0');
        document.getElementById('posting-elapsed').textContent = minutes + ':' + remainder;
    }, 1000);
});
document.querySelectorAll('.repost-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        const invoice = decodeURIComponent(this.action.split('/').pop());
        if (!confirm('Rejurnal invoice ' + invoice + ' memakai data koreksi terbaru? Pembayaran sebagian tetap dipertahankan; proses ditolak jika total koreksi lebih kecil dari nilai yang sudah dibayar/dipotong atau periode terkunci.')) {
            event.preventDefault();
            return;
        }
        const dueDays = document.querySelector('#posting-form input[name="due_days"]')?.value || 30;
        this.querySelector('.repost-due-days').value = dueDays;
        document.getElementById('posting-detail').textContent = 'Invoice ' + invoice + ' sedang divalidasi dan direjurnal.';
        document.getElementById('posting-overlay').classList.add('show');
    });
});
</script>
@endpush
