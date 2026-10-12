@extends('layouts.app')

@section('title', 'Laporan Piutang')

@php
    $piutangPdfVersion = max(
        filemtime(resource_path('views/laporan/piutang_pdf.blade.php')) ?: 0,
        filemtime(resource_path('views/laporan/piutang_kartu_pdf.blade.php')) ?: 0
    ) ?: time();
@endphp

@section('content')
<div class="piutang-screen-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h2 class="page-title mb-0"><i class="bi bi-people me-2"></i>Laporan Piutang Keseluruhan</h2>
        <small class="text-muted">Gabungan saldo awal, piutang berjalan, import, dan sinkronisasi. Posisi {{ date('d F Y', strtotime($tanggalPosisi)) }}</small>
    </div>
    <div class="d-flex gap-2">
        <a id="piutangExportButton" href="{{ route('laporan.piutang.export', array_merge(request()->except(['page', 'print']), ['section' => $reportSection])) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Excel
        </a>
        @include('laporan._print-format-dropdown', [
            'pdfUrl' => route('laporan.piutang.pdf', array_merge(request()->except(['page', 'print', 'print_format', 'pdf_v']), ['section' => $reportSection, 'pdf_v' => $piutangPdfVersion])),
            'dotMatrixUrl' => route('laporan.piutang.pdf', array_merge(request()->except(['page', 'print', 'print_format', 'pdf_v']), ['section' => $reportSection, 'print_format' => 'dot_matrix', 'pdf_v' => $piutangPdfVersion])),
            'buttonClass' => 'btn-outline-secondary',
            'linkClass' => 'piutang-print-button',
        ])
    </div>
</div>

<div class="card mb-3 piutang-filter">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('laporan.piutang') }}" class="row g-2 align-items-end">
            <input type="hidden" name="section" id="piutangReportSection" value="{{ $reportSection }}">
            <div class="col-md-3 {{ in_array($reportSection, ['mutasi', 'penambahan', 'pembayaran'], true) ? 'd-none' : '' }}" id="positionDateField">
                <label class="form-label small mb-1">Tanggal Posisi Saldo</label>
                <input type="date" name="tanggal_posisi" class="form-control form-control-sm" value="{{ $tanggalPosisi }}" required>
                <div class="form-text">Saldo dihitung sampai tanggal ini.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Pelanggan</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="reportCustomerFilter" name="customer" class="form-control" value="{{ $customer }}" placeholder="Semua pelanggan" readonly>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportCustomerModal"><i class="bi bi-search"></i> Cari</button>
                    @if($customer !== '')<button type="button" id="clearReportCustomer" class="btn btn-outline-secondary" title="Hapus pelanggan"><i class="bi bi-x-lg"></i></button>@endif
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">No. Faktur</label>
                <input type="text" name="nofaktur" value="{{ $nofaktur ?? '' }}" class="form-control form-control-sm" placeholder="Contoh: FJ20260003788" autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="semua" @selected($status === 'semua')>Semua</option>
                    <option value="open" @selected($status === 'open')>Belum lunas</option>
                    <option value="jatuh_tempo" @selected($status === 'jatuh_tempo')>Sudah jatuh tempo</option>
                    <option value="lunas" @selected($status === 'lunas')>Lunas</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </div>
            <div id="transactionPeriodFields" class="col-12 {{ in_array($reportSection, ['mutasi', 'penambahan', 'pembayaran'], true) ? '' : 'd-none' }}">
                <div class="border-top mt-1 pt-2">
                    <div class="small fw-semibold text-primary mb-1"><i class="bi bi-calendar-range me-1"></i>Periode Transaksi</div>
                    <div class="small text-muted mb-2">Digunakan khusus untuk laporan Mutasi, Penambahan Piutang, dan Pembayaran.</div>
                    <div class="row g-2">
                        <div class="col-md-3"><label class="form-label small mb-1">Dari Tanggal</label><input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ $tanggalAwal }}"></div>
                        <div class="col-md-3"><label class="form-label small mb-1">Sampai Tanggal</label><input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ $tanggalAkhir }}"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="reportCustomerModal" tabindex="-1" aria-labelledby="reportCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="reportCustomerModalLabel"><i class="bi bi-search me-2"></i>Cari Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <input type="search" id="reportCustomerSearch" class="form-control mb-3" placeholder="Ketik kode atau nama customer..." autocomplete="off">
            <div id="reportCustomerLoading" class="text-center text-muted py-3 d-none">Memuat customer...</div>
            <div id="reportCustomerEmpty" class="text-center text-muted py-3 d-none">Customer tidak ditemukan.</div>
            <div id="reportCustomerError" class="alert alert-danger py-2 d-none">Data customer gagal dimuat.</div>
            <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0"><thead class="table-light"><tr><th>Kode</th><th>Nama Customer</th><th class="text-end">Aksi</th></tr></thead><tbody id="reportCustomerResults"></tbody></table></div>
        </div>
    </div></div>
</div>

<div class="row g-2 mb-3 piutang-summary">
    <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Saldo Buku Pembantu Piutang</div><div class="fs-4 fw-bold">{{ number_format($subledgerBalance, 2, ',', '.') }}</div><div class="small text-muted">Sisa invoice pelanggan yang belum lunas</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Saldo Akun Piutang (Buku Besar)</div><div class="fs-4 fw-bold">{{ $ledgerBalance === null ? '-' : number_format($ledgerBalance, 2, ',', '.') }}</div><div class="small text-muted">Saldo dari jurnal akuntansi yang posted</div>@if($customer !== '')<div class="small text-muted">Rekonsiliasi hanya untuk semua pelanggan</div>@endif</div></div></div>
    <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Selisih Rekonsiliasi</div>@php
        $difference = $ledgerBalance === null ? null : $subledgerBalance - $ledgerBalance;
    @endphp<div class="fs-4 fw-bold {{ $difference !== null && abs($difference) > 0.01 ? 'text-danger' : 'text-success' }}">{{ $difference === null ? '-' : number_format($difference, 2, ',', '.') }}</div><div class="small text-muted">Buku pembantu dikurangi buku besar</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Invoice Ditampilkan</div><div class="fs-4 fw-bold">{{ $invoiceTotals['count'] }}</div></div></div></div>
</div>

@if($customer !== '')
<div class="alert alert-info">Kartu piutang pelanggan <strong>{{ $customer }}</strong> sedang ditampilkan. Kosongkan filter pelanggan untuk rekonsiliasi dengan saldo buku besar.</div>
@elseif(!$receivableCoaId)
<div class="alert alert-warning">Akun <strong>Piutang Usaha</strong> belum dipetakan. Rekonsiliasi buku besar belum dapat dihitung.</div>
@elseif($difference !== null && abs($difference) > 0.01)
<div class="alert alert-danger d-flex justify-content-between align-items-center gap-2">
    <div>
        <div>Saldo buku pembantu berbeda dengan akun Piutang Usaha sebesar <strong>{{ number_format(abs($difference), 2, ',', '.') }}</strong>.</div>
        <small>{{ $difference < 0 ? 'Buku besar lebih tinggi. Cari debit Piutang berlebih atau kredit Piutang yang kurang.' : 'Buku pembantu lebih tinggi. Cari debit Piutang yang kurang atau kredit Piutang berlebih.' }}</small>
    </div>
    <a href="{{ route('laporan.buku_besar', ['coa_id' => $receivableCoaId, 'tanggal_awal' => date('Y-m-01', strtotime($tanggalPosisi)), 'tanggal_akhir' => $tanggalPosisi]) }}" class="btn btn-sm btn-outline-danger text-nowrap"><i class="bi bi-search me-1"></i>Cek Buku Besar Piutang</a>
</div>
@else
<div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> Saldo rincian piutang sesuai dengan buku besar.</div>
@endif

@if($agingFilter !== '')
<div class="alert alert-info py-2 d-flex justify-content-between align-items-center gap-2">
    <span>Detail umur piutang: <strong>{{ match($agingFilter) { 'belum_jatuh_tempo' => 'Belum Jatuh Tempo', '1_30' => '1–30 Hari', '31_60' => '31–60 Hari', '61_90' => '61–90 Hari', 'lebih_90' => '> 90 Hari' } }}</strong></span>
    <a href="{{ route('laporan.piutang', array_merge(request()->except(['aging', 'page', 'print', 'section']), ['section' => 'aging'])) }}" class="btn btn-sm btn-outline-primary">Tutup Detail</a>
</div>
@endif

<ul class="nav nav-tabs mb-3 piutang-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link {{ $reportSection === 'kartu' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#kartu" data-report-section="kartu" type="button">Kartu Faktur</button></li>
    <li class="nav-item"><button class="nav-link {{ $reportSection === 'invoice' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#invoice" data-report-section="invoice" type="button">Invoice & Jatuh Tempo</button></li>
    <li class="nav-item"><button class="nav-link {{ $reportSection === 'pelanggan' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#pelanggan" data-report-section="pelanggan" type="button">Daftar Pelanggan</button></li>
    <li class="nav-item"><button class="nav-link {{ $reportSection === 'aging' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#aging" data-report-section="aging" type="button">Umur Piutang</button></li>
    <li class="nav-item"><button class="nav-link {{ $reportSection === 'mutasi' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#mutasi" data-report-section="mutasi" type="button">Mutasi</button></li>
    <li class="nav-item"><button class="nav-link {{ $reportSection === 'penambahan' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#penambahan" data-report-section="penambahan" type="button">Penambahan Piutang</button></li>
    <li class="nav-item"><button class="nav-link {{ $reportSection === 'pembayaran' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#pembayaran" data-report-section="pembayaran" type="button">Pembayaran</button></li>
</ul>

@php
    $agingLabels = ['belum_jatuh_tempo' => 'Belum Jatuh Tempo', '1_30' => '1–30 Hari', '31_60' => '31–60 Hari', '61_90' => '61–90 Hari', 'lebih_90' => '> 90 Hari'];
    $printTitle = match($reportSection) {
        'kartu' => 'KARTU PIUTANG PER FAKTUR',
        'pelanggan' => 'DAFTAR PELANGGAN PIUTANG',
        'aging' => $agingDetail === 'all' ? 'DETAIL PIUTANG BERDASARKAN UMUR' : ($agingFilter !== '' ? 'LAPORAN PIUTANG UMUR '.$agingLabels[$agingFilter] : 'REKAP UMUR PIUTANG'),
        'mutasi' => 'MUTASI PIUTANG',
        'penambahan' => 'DAFTAR PENAMBAHAN PIUTANG',
        'pembayaran' => 'LAPORAN PEMBAYARAN PIUTANG',
        default => 'LAPORAN PIUTANG PER FAKTUR',
    };
@endphp
<div class="piutang-print-header">
    <div class="fw-bold">{{ $printTitle }}</div>
    <div>Posisi {{ date('d/m/Y', strtotime($tanggalPosisi)) }}</div>
    @if($customer !== '')<div>Pelanggan: {{ $customer }}</div>@endif
    @if(($nofaktur ?? '') !== '')<div>No. Faktur: {{ $nofaktur }}</div>@endif
    <div class="small">Status: {{ $status === 'semua' ? 'Semua' : ($status === 'open' ? 'Belum Lunas' : ($status === 'jatuh_tempo' ? 'Jatuh Tempo' : 'Lunas')) }}</div>
</div>

<div class="tab-content">
    <div class="tab-pane fade {{ $reportSection === 'kartu' ? 'show active' : '' }}" id="kartu">
        @if(!empty($kartuNeedsFilter))
            <div class="alert alert-info mb-3">
                Isi <strong>No. Faktur</strong> (mis. FJ20260003788) dan/atau pilih <strong>Pelanggan</strong>, lalu klik Tampilkan untuk membuka kartu piutang per faktur.
            </div>
        @elseif(($invoiceCards ?? collect())->isEmpty())
            <div class="alert alert-warning mb-0">Tidak ada faktur yang cocok dengan filter.</div>
        @else
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <small class="text-muted">Menampilkan {{ $invoiceCards->firstItem() }}–{{ $invoiceCards->lastItem() }} dari {{ $invoiceCards->total() }} faktur</small>
                {{ $invoiceCards->links() }}
            </div>
            @foreach($invoiceCards as $card)
                <div class="card mb-3 invoice-card-track">
                    <div class="card-header py-2 d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">{{ $card['invoice']->NOFAKTUR }}</div>
                            <div class="small text-muted">
                                {{ $card['invoice']->CUSTOMER }}
                                @if($card['customer_name']) — {{ $card['customer_name'] }}@endif
                                · Tgl {{ $card['invoice']->TGLFAKTUR?->format('d/m/Y') }}
                                · JTP {{ $card['invoice']->TGLJT?->format('d/m/Y') ?? '-' }}
                                · {{ $card['status'] }}
                            </div>
                            @if($card['journal_number'])
                                <div class="small">
                                    Jurnal:
                                    @if($card['journal_url'])
                                        <a href="{{ $card['journal_url'] }}">{{ $card['journal_number'] }}</a>
                                    @else
                                        {{ $card['journal_number'] }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="text-end small">
                            <div>Nilai: <strong>{{ number_format($card['invoice_amount'], 2, ',', '.') }}</strong></div>
                            <div>Terbayar: {{ number_format($card['paid'], 2, ',', '.') }}</div>
                            <div>Sisa: <strong class="{{ $card['balance'] > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($card['balance'], 2, ',', '.') }}</strong></div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Referensi</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Kredit</th>
                                    <th class="text-end">Saldo</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($card['lines'] as $line)
                                    <tr>
                                        <td>{{ $line['date']?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $line['type'] === 'invoice' ? 'Invoice' : 'Pelunasan' }}</td>
                                        <td class="fw-semibold">{{ $line['reference'] }}</td>
                                        <td>{{ $line['description'] }}</td>
                                        <td class="text-end">{{ $line['debit'] > 0 ? number_format($line['debit'], 2, ',', '.') : '-' }}</td>
                                        <td class="text-end">{{ $line['credit'] > 0 ? number_format($line['credit'], 2, ',', '.') : '-' }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($line['balance'], 2, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if($line['url'])
                                                <a href="{{ $line['url'] }}" class="btn btn-sm btn-outline-primary">Edit Pelunasan</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
            @if($invoiceCards->hasPages())
                <div class="d-flex justify-content-end">{{ $invoiceCards->links() }}</div>
            @endif
        @endif
    </div>

    <div class="tab-pane fade {{ $reportSection === 'invoice' ? 'show active' : '' }}" id="invoice">
        <div class="card"><div class="table-responsive"><table class="table table-hover mb-0 invoice-report-table">
            <colgroup><col class="invoice-col"><col class="date-col"><col class="date-col"><col class="customer-code-col"><col class="customer-name-col"><col class="age-col"><col class="amount-col"><col class="amount-col"><col class="amount-col"><col class="status-col"></colgroup>
            <thead class="table-light"><tr><th>Invoice</th><th>Tanggal</th><th>JTP</th><th>Customer</th><th>Nama Customer</th><th>Umur</th><th class="text-end">Nilai</th><th class="text-end">Terbayar</th><th class="text-end">Sisa</th><th>Status</th></tr></thead>
            <tbody>@forelse($invoices as $row)<tr>
                @php
                    $paymentDetails = $row['invoice']->paymentDetails ?? collect();
                @endphp
                <td><a href="{{ route('laporan.piutang', array_merge(request()->except(['page', 'print', 'section', 'kartu_page']), ['section' => 'kartu', 'nofaktur' => $row['invoice']->NOFAKTUR])) }}" class="text-decoration-none" title="Buka kartu faktur">{{ $row['invoice']->NOFAKTUR }}</a></td><td>{{ $row['invoice']->TGLFAKTUR?->format('d/m/Y') }}</td><td>{{ $row['invoice']->TGLJT?->format('d/m/Y') ?? '-' }}</td><td>{{ $row['invoice']->CUSTOMER }}</td><td>{{ $row['customer_name'] ?: '-' }}</td><td>{{ $row['age_label'] }}</td>
                <td class="text-end">{{ number_format($row['invoice_amount'], 2, ',', '.') }}</td><td class="text-end">{{ number_format($row['paid'], 2, ',', '.') }}</td><td class="text-end fw-semibold">{{ number_format($row['balance'], 2, ',', '.') }}</td>
                <td>
                    @if((float) $row['balance'] <= 0)
                        <button type="button" class="badge border-0 bg-success report-payment-badge" data-bs-toggle="modal" data-bs-target="#reportPaymentDetailModal{{ $row['invoice']->id }}" title="Klik untuk lihat detail pembayaran"><i class="bi bi-check-circle me-1"></i>Lunas</button>
                        <div class="small text-success mt-1">Terbayar</div>
                    @else
                        <button type="button" class="badge border-0 bg-warning text-dark report-payment-badge" data-bs-toggle="modal" data-bs-target="#reportPaymentDetailModal{{ $row['invoice']->id }}" title="Klik untuk lihat detail pembayaran"><i class="bi bi-clock-history me-1"></i>Belum Lunas</button>
                        @if($paymentDetails->isNotEmpty())<div class="small text-muted mt-1">{{ $paymentDetails->count() }} pembayaran</div>@endif
                    @endif
                </td>
            </tr>@empty<tr><td colspan="10" class="text-center text-muted py-4">Tidak ada invoice sesuai filter.</td></tr>@endforelse</tbody>
            <tfoot class="table-light"><tr><th colspan="6">Total Seluruh Data</th><th class="text-end">{{ number_format($invoiceTotals['invoice_amount'], 2, ',', '.') }}</th><th class="text-end">{{ number_format($invoiceTotals['paid'], 2, ',', '.') }}</th><th class="text-end">{{ number_format($invoiceTotals['balance'], 2, ',', '.') }}</th><th></th></tr></tfoot>
        </table></div></div>
        @foreach($invoices as $row)
        @php
            $invoice = $row['invoice'];
            $paymentDetails = $invoice->paymentDetails ?? collect();
        @endphp
        <div class="modal fade" id="reportPaymentDetailModal{{ $invoice->id }}" tabindex="-1" aria-labelledby="reportPaymentDetailModalLabel{{ $invoice->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
                <div class="modal-header"><div><h5 class="modal-title" id="reportPaymentDetailModalLabel{{ $invoice->id }}">Detail Pembayaran {{ $invoice->NOFAKTUR }}</h5><div class="small text-muted">{{ $invoice->CUSTOMER }} - {{ $row['customer_name'] ?: 'Nama customer belum tersedia' }}</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3"><div class="text-muted small">Nilai Piutang</div><div class="fw-semibold">{{ number_format($row['invoice_amount'], 0, ',', '.') }}</div></div>
                        <div class="col-6 col-md-3"><div class="text-muted small">Terbayar</div><div class="fw-semibold">{{ number_format($row['paid'], 0, ',', '.') }}</div></div>
                        <div class="col-6 col-md-3"><div class="text-muted small">Total Penyelesaian</div><div class="fw-semibold">{{ number_format($invoice->PAYMENT_SETTLED_TOTAL ?? 0, 0, ',', '.') }}</div></div>
                        <div class="col-6 col-md-3"><div class="text-muted small">Sisa</div><div class="fw-semibold {{ $row['balance'] <= 0 ? 'text-success' : 'text-primary' }}">{{ number_format($row['balance'], 0, ',', '.') }}</div></div>
                    </div>
                    @if($paymentDetails->isNotEmpty())
                    <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0"><thead class="table-light"><tr><th>No. Bayar</th><th>Tanggal</th><th>Metode</th><th>Akun Bayar</th><th>Referensi</th><th class="text-end">Bayar</th><th class="text-end">PPh 23</th><th class="text-end">Potongan</th><th class="text-end">Adm.</th><th class="text-end">Sisa Faktur</th><th></th></tr></thead><tbody>
                        @foreach($paymentDetails as $detail)
                        @php
                            $header = $detail->header;
                        @endphp
                        <tr><td class="fw-semibold">{{ $detail->NOBAYAR }}</td><td>{{ $header?->TGLBAYAR?->format('d/m/Y') ?: '-' }}</td><td>{{ $header?->METODE_PEMBAYARAN ?: '-' }}</td><td>{{ $header?->paymentCoa ? $header->paymentCoa->kode_akun.' - '.$header->paymentCoa->nama_akun : '-' }}</td><td>{{ $header?->NO_REFERENSI ?: '-' }}</td><td class="text-end">{{ number_format($detail->NILAI_BAYAR, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->PPH23, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->DISCOUNT, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->BANK_ADMIN, 0, ',', '.') }}</td><td class="text-end">{{ number_format($detail->SISA_FAKTUR, 0, ',', '.') }}</td><td class="text-end">@if($header)<a href="{{ route('pelunasan-piutang.show', $header->id) }}" class="btn btn-sm btn-outline-primary">Bukti</a>@endif</td></tr>
                        @endforeach
                    </tbody></table></div>
                    @else
                    <div class="alert alert-info mb-0">Belum ada detail pembayaran untuk invoice ini.</div>
                    @endif
                </div>
            </div></div>
        </div>
        @endforeach
        @if($invoices->hasPages())
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted">Menampilkan {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} dari {{ $invoices->total() }} invoice</small>
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
    <div class="tab-pane fade {{ $reportSection === 'pelanggan' ? 'show active' : '' }}" id="pelanggan">
        <div class="card"><div class="table-responsive"><table class="table table-hover mb-0 customer-report-table"><colgroup><col class="customer-code-col"><col class="customer-name-col"><col class="invoice-count-col"><col class="amount-col"><col class="amount-col"><col class="amount-col"><col class="action-col"></colgroup><thead class="table-light"><tr><th>Customer</th><th>Nama Customer</th><th class="text-center">Invoice</th><th class="text-end">Nilai</th><th class="text-end">Terbayar</th><th class="text-end">Saldo</th><th></th></tr></thead><tbody>
        @forelse($customers as $row)<tr><td>{{ $row['customer'] }}</td><td>{{ $row['customer_name'] ?: '-' }}</td><td class="text-end">{{ $row['invoice_count'] }}</td><td class="text-end">{{ number_format($row['invoice_amount'], 2, ',', '.') }}</td><td class="text-end">{{ number_format($row['paid'], 2, ',', '.') }}</td><td class="text-end fw-semibold">{{ number_format($row['balance'], 2, ',', '.') }}</td><td><a class="btn btn-sm btn-outline-primary text-nowrap" title="Lihat Kartu Piutang" href="{{ route('laporan.piutang', array_merge(request()->except(['customer', 'page', 'print', 'section', 'nofaktur', 'kartu_page']), ['customer' => $row['customer'], 'section' => 'kartu'])) }}">Kartu</a></td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data.</td></tr>@endforelse
        </tbody></table></div></div>
        @if($customers->hasPages())<div class="mt-3">{{ $customers->links() }}</div>@endif
    </div>
    <div class="tab-pane fade {{ $reportSection === 'aging' ? 'show active' : '' }}" id="aging">
        <div class="d-flex justify-content-between align-items-center mb-2 aging-controls">
            <small class="text-muted">{{ $agingDetail === 'all' ? 'Rincian seluruh invoice dikelompokkan berdasarkan umur piutang.' : 'Klik kelompok umur untuk melihat rincian invoice.' }}</small>
            <div class="d-flex gap-2">
                @if($agingDetail === 'all')
                    <a href="{{ route('laporan.piutang', array_merge(request()->except(['aging', 'aging_detail', 'page', 'print', 'section']), ['section' => 'aging'])) }}" class="btn btn-sm btn-outline-secondary">Kembali ke Rekap</a>
                @else
                    <a href="{{ route('laporan.piutang', array_merge(request()->except(['aging_detail', 'page', 'print', 'section']), ['section' => 'aging', 'aging_detail' => 'all'])) }}" class="btn btn-sm btn-outline-primary">Tampilkan Semua Umur</a>
                @endif
            </div>
        </div>
        <div class="row g-2 aging-cards {{ $agingDetail === 'all' ? 'd-none' : '' }}">
            @foreach($agingLabels as $key => $label)
            <div class="col-6 col-md">
                <a class="text-decoration-none" href="{{ route('laporan.piutang', array_merge(request()->except(['aging', 'page', 'print', 'section']), ['aging' => $key, 'section' => 'aging'])) }}">
                    <div class="card h-100 aging-card {{ $agingFilter === $key ? 'border-primary shadow-sm' : '' }}">
                        <div class="card-body py-2 px-3">
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="aging-value fw-semibold {{ $key === 'lebih_90' ? 'text-danger' : 'text-dark' }}">{{ number_format($aging[$key], 2, ',', '.') }}</div>
                            <small class="text-primary">Lihat detail</small>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @if($agingFilter !== '')
        <div class="card mt-3 aging-detail-table">
            <div class="card-header py-2 fw-semibold">Rincian Invoice {{ $agingLabels[$agingFilter] }}</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Invoice</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Customer</th><th>Nama Customer</th><th>Umur</th><th class="text-end">Nilai</th><th class="text-end">Terbayar</th><th class="text-end">Sisa</th></tr></thead>
                    <tbody>
                    @forelse($invoices as $row)
                        <tr>
                            <td>{{ $row['invoice']->NOFAKTUR }}</td>
                            <td>{{ $row['invoice']->TGLFAKTUR?->format('d/m/Y') }}</td>
                            <td>{{ $row['invoice']->TGLJT?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $row['invoice']->CUSTOMER }}</td>
                            <td>{{ $row['customer_name'] ?: '-' }}</td>
                            <td>{{ $row['age_label'] }}</td>
                            <td class="text-end">{{ number_format($row['invoice_amount'], 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row['paid'], 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row['balance'], 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada invoice pada kelompok umur ini.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot class="table-light"><tr><th colspan="6">Total</th><th class="text-end">{{ number_format($invoiceTotals['invoice_amount'], 2, ',', '.') }}</th><th class="text-end">{{ number_format($invoiceTotals['paid'], 2, ',', '.') }}</th><th class="text-end">{{ number_format($invoiceTotals['balance'], 2, ',', '.') }}</th></tr></tfoot>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted">Menampilkan {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} dari {{ $invoices->total() }} invoice</small>
                {{ $invoices->links() }}
            </div>
        @endif
        @endif

        @if($agingDetail === 'all')
        <div class="aging-all-detail {{ $agingDetail === 'all' ? 'd-block' : '' }}">
            <div class="card mb-3 aging-subtotal-summary">
                <div class="card-header py-2 fw-semibold">Subtotal Per Kategori Umur</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light"><tr><th>Kategori Umur</th><th class="text-end">Invoice</th><th class="text-end">Nilai</th><th class="text-end">Terbayar</th><th class="text-end">Sisa</th></tr></thead>
                        <tbody>
                        @foreach($agingLabels as $key => $label)
                            <tr>
                                <td class="fw-semibold">{{ $label }}</td>
                                <td class="text-end">{{ number_format($agingGroupTotals->get($key)['count'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($agingGroupTotals->get($key)['invoice_amount'] ?? 0, 2, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($agingGroupTotals->get($key)['paid'] ?? 0, 2, ',', '.') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($agingGroupTotals->get($key)['balance'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="table-light"><tr><th>Total Semua Umur</th><th class="text-end">{{ number_format($agingGroupTotals->sum('count'), 0, ',', '.') }}</th><th class="text-end">{{ number_format($agingGroupTotals->sum('invoice_amount'), 2, ',', '.') }}</th><th class="text-end">{{ number_format($agingGroupTotals->sum('paid'), 2, ',', '.') }}</th><th class="text-end">{{ number_format($agingGroupTotals->sum('balance'), 2, ',', '.') }}</th></tr></tfoot>
                    </table>
                </div>
            </div>
            @foreach($agingLabels as $key => $label)
                @php
                    $groupRows = $agingGroups->get($key, collect());
                    $subtotal = $agingGroupTotals->get($key, ['count' => 0, 'invoice_amount' => 0, 'paid' => 0, 'balance' => 0]);
                @endphp
                <section class="aging-print-group">
                    <h3>{{ $label }}</h3>
                    <table class="table table-sm">
                        <thead><tr><th>Invoice</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Customer</th><th>Nama Customer</th><th>Umur</th><th class="text-end">Nilai</th><th class="text-end">Terbayar</th><th class="text-end">Sisa</th></tr></thead>
                        <tbody>
                        @forelse($groupRows as $row)
                            <tr><td>{{ $row['invoice']->NOFAKTUR }}</td><td>{{ $row['invoice']->TGLFAKTUR?->format('d/m/Y') }}</td><td>{{ $row['invoice']->TGLJT?->format('d/m/Y') ?? '-' }}</td><td>{{ $row['invoice']->CUSTOMER }}</td><td>{{ $row['customer_name'] ?: '-' }}</td><td>{{ $row['age_label'] }}</td><td class="text-end">{{ number_format($row['invoice_amount'], 2, ',', '.') }}</td><td class="text-end">{{ number_format($row['paid'], 2, ',', '.') }}</td><td class="text-end">{{ number_format($row['balance'], 2, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="9" class="text-center">Tidak ada invoice.</td></tr>
                        @endforelse
                        </tbody>
                        <tfoot><tr><th colspan="6">Subtotal {{ $label }} ({{ $subtotal['count'] ?? 0 }} invoice)</th><th class="text-end">{{ number_format($subtotal['invoice_amount'] ?? 0, 2, ',', '.') }}</th><th class="text-end">{{ number_format($subtotal['paid'] ?? 0, 2, ',', '.') }}</th><th class="text-end">{{ number_format($subtotal['balance'] ?? 0, 2, ',', '.') }}</th></tr></tfoot>
                    </table>
                </section>
            @endforeach
        </div>
        @if($agingDetailPaginator?->hasPages())
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted">Menampilkan {{ $agingDetailPaginator->firstItem() }}–{{ $agingDetailPaginator->lastItem() }} dari {{ $agingDetailPaginator->total() }} invoice detail umur</small>
                {{ $agingDetailPaginator->links() }}
            </div>
        @endif
        @endif
    </div>
    <div class="tab-pane fade {{ $reportSection === 'mutasi' ? 'show active' : '' }}" id="mutasi">
        <div class="card"><div class="card-header">Mutasi {{ date('d/m/Y', strtotime($tanggalAwal)) }}–{{ date('d/m/Y', strtotime($tanggalAkhir)) }}</div><div class="card-body"><table class="table mb-0"><tr><td>Saldo awal</td><td class="text-end">{{ number_format($movement['opening'], 2, ',', '.') }}</td></tr><tr><td>Penambahan invoice</td><td class="text-end">{{ number_format($movement['invoices'], 2, ',', '.') }}</td></tr><tr><td>Pembayaran</td><td class="text-end">({{ number_format($movement['payments'], 2, ',', '.') }})</td></tr><tr><td>Diskon dan retur</td><td class="text-end">({{ number_format($movement['adjustments'], 2, ',', '.') }})</td></tr><tr class="fw-bold"><td>Saldo akhir mutasi</td><td class="text-end">{{ number_format($movement['ending'], 2, ',', '.') }}</td></tr></table></div></div>
    </div>
    <div class="tab-pane fade {{ $reportSection === 'penambahan' ? 'show active' : '' }}" id="penambahan">
        <div class="card">
            <div class="card-header py-2">Penambahan Piutang {{ date('d/m/Y', strtotime($tanggalAwal)) }}–{{ date('d/m/Y', strtotime($tanggalAkhir)) }}</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 addition-report-table">
                    <colgroup><col class="addition-number-col"><col class="addition-date-col"><col class="addition-due-col"><col class="addition-customer-col"><col class="addition-customer-name-col"><col class="addition-amount-col"></colgroup>
                    <thead class="table-light"><tr><th>Invoice</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Customer</th><th>Nama Customer</th><th class="text-end">Nilai Piutang</th></tr></thead>
                    <tbody>
                    @forelse($periodInvoices as $row)
                        <tr>
                            <td>{{ $row['invoice']->NOFAKTUR }}</td>
                            <td>{{ $row['invoice']->TGLFAKTUR?->format('d/m/Y') }}</td>
                            <td>{{ $row['invoice']->TGLJT?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $row['invoice']->CUSTOMER }}</td>
                            <td>{{ $row['customer_name'] ?: '-' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row['invoice_amount'], 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada penambahan piutang pada periode ini.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot class="table-light"><tr><th colspan="5">Total Penambahan ({{ $periodInvoiceTotals['count'] }} invoice)</th><th class="text-end">{{ number_format($periodInvoiceTotals['invoice_amount'], 2, ',', '.') }}</th></tr></tfoot>
                </table>
            </div>
        </div>
        @if($periodInvoices->hasPages())<div class="mt-3">{{ $periodInvoices->links() }}</div>@endif
    </div>
    <div class="tab-pane fade {{ $reportSection === 'pembayaran' ? 'show active' : '' }}" id="pembayaran">
        <div class="card"><div class="table-responsive"><table class="table table-hover mb-0 payment-report-table"><colgroup><col class="payment-number-col"><col class="payment-date-col"><col class="payment-customer-col"><col class="payment-customer-name-col"><col class="payment-method-col"><col class="payment-invoice-col"><col class="payment-amount-col"></colgroup><thead class="table-light"><tr><th>No. Bayar</th><th>Tanggal</th><th>Customer</th><th>Nama Customer</th><th>Metode</th><th>Invoice</th><th class="text-end">Jumlah</th></tr></thead><tbody>
        @forelse($payments as $payment)<tr><td>{{ $payment->NOBAYAR }}</td><td>{{ $payment->TGLBAYAR?->format('d/m/Y') }}</td><td>{{ $payment->CUSTOMER }}</td><td>{{ $payment->customer_name ?: '-' }}</td><td>{{ $payment->METODE_PEMBAYARAN ?? '-' }}</td><td>{{ $payment->details->pluck('NOFAKTUR')->join(', ') }}</td><td class="text-end">{{ number_format($payment->details->sum('NILAI_BAYAR'), 2, ',', '.') }}</td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pembayaran pada periode ini.</td></tr>@endforelse
        </tbody><tfoot class="table-light"><tr><th colspan="6">Total Pembayaran ({{ $paymentTotals['count'] }} transaksi)</th><th class="text-end">{{ number_format($paymentTotals['amount'], 2, ',', '.') }}</th></tr></tfoot></table></div></div>
        @if($payments->hasPages())<div class="mt-3">{{ $payments->links() }}</div>@endif
    </div>
</div>

@endsection

@include('laporan._report-print')

@push('styles')
<style>
    .piutang-print-header { display: none; }
    .piutang-screen-header .page-title { font-size: 1.25rem; }
    .piutang-screen-header small { font-size: .82rem; }
    .piutang-filter .form-label,
    .piutang-filter summary { font-size: .84rem !important; }
    .piutang-filter .form-control,
    .piutang-filter .form-select,
    .piutang-filter .btn { font-size: .86rem; }
    .piutang-filter .form-text { font-size: .76rem; margin-top: .2rem; }
    .piutang-summary .card-body { padding: .58rem .7rem; }
    .piutang-summary .fs-4 { font-size: 1.05rem !important; line-height: 1.15; }
    .piutang-summary .small { font-size: .78rem; line-height: 1.2; }
    .piutang-tabs .nav-link { padding: .38rem .52rem; font-size: .84rem; }
    .report-payment-badge { cursor: pointer; font-size: .72rem; }
    .invoice-report-table .status-col { width: 92px; }
    .tab-content .card-header { padding: .4rem .52rem; font-size: .88rem; }
    .piutang-screen-header,
    .piutang-filter,
    .piutang-summary,
    .piutang-tabs,
    .tab-content,
    .alert {
        font-family: var(--bs-body-font-family);
    }
    .tab-content .table th,
    .tab-content .table td {
        padding: .28rem .38rem;
        font-size: .86rem;
        line-height: 1.15;
        vertical-align: middle;
    }
    .tab-content .table th { font-size: .88rem; }
    .tab-content .table thead th,
    .tab-content .table tfoot th,
    .tab-content .table tfoot td {
        background: #fff !important;
        --bs-table-bg: #fff;
        --bs-table-color: #212529;
    }
    .tab-content .badge { padding: .17rem .28rem; font-size: .6rem; }
    .tab-content .btn-sm { padding: .18rem .35rem; font-size: .65rem; }
    .alert { padding: .5rem .65rem; font-size: .72rem; line-height: 1.25; }
    .alert small { font-size: .66rem; }
    .aging-value { font-size: .74rem; line-height: 1.2; }
    .aging-card { transition: border-color .15s ease, box-shadow .15s ease; }
    .aging-card:hover { border-color: var(--bs-primary); box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075); }
    .aging-detail-table .card-header { font-size: .85rem; }
    .aging-detail-table .table th,
    .aging-detail-table .table td {
        padding: .28rem .4rem;
        font-size: .82rem;
        line-height: 1.15;
        vertical-align: middle;
    }
    .aging-detail-table .table th:nth-child(4),
    .aging-detail-table .table td:nth-child(4),
    .aging-detail-table .table th:nth-child(5),
    .aging-detail-table .table td:nth-child(5) {
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .aging-detail-table .table th:nth-child(7),
    .aging-detail-table .table td:nth-child(7),
    .aging-detail-table .table th:nth-child(8),
    .aging-detail-table .table td:nth-child(8),
    .aging-detail-table .table th:nth-child(9),
    .aging-detail-table .table td:nth-child(9) {
        text-align: right;
        white-space: nowrap;
        overflow-wrap: normal;
        word-break: normal;
    }
    .aging-detail-table .badge { font-size: .59rem; padding: .17rem .28rem; }
    .aging-all-detail { display: none; }
    .invoice-report-table { width: 100%; table-layout: fixed; }
    .invoice-report-table .invoice-col { width: 12%; }
    .invoice-report-table .date-col { width: 9%; }
    .invoice-report-table .customer-code-col { width: 11%; }
    .invoice-report-table .customer-name-col { width: 21%; }
    .invoice-report-table .age-col { width: 7%; }
    .invoice-report-table .amount-col { width: 10%; }
    .invoice-report-table th,
    .invoice-report-table td {
        border-right: 1px solid #dee2e6;
        overflow: hidden;
        overflow-wrap: anywhere;
    }
    .invoice-report-table th:last-child,
    .invoice-report-table td:last-child { border-right: 0; }
    .invoice-report-table th:nth-child(1),
    .invoice-report-table td:nth-child(1),
    .invoice-report-table th:nth-child(2),
    .invoice-report-table td:nth-child(2),
    .invoice-report-table th:nth-child(3),
    .invoice-report-table td:nth-child(3) { white-space: nowrap; overflow-wrap: normal; word-break: normal; }
    .invoice-report-table th:nth-child(7),
    .invoice-report-table td:nth-child(7),
    .invoice-report-table th:nth-child(8),
    .invoice-report-table td:nth-child(8),
    .invoice-report-table th:nth-child(9),
    .invoice-report-table td:nth-child(9) { text-align: right; white-space: nowrap; overflow-wrap: normal; word-break: normal; }
    .invoice-report-table th:nth-child(4),
    .invoice-report-table td:nth-child(4) {
        line-height: 1.05;
        padding-top: .18rem;
        padding-bottom: .18rem;
        white-space: normal;
        overflow: hidden;
        overflow-wrap: anywhere;
        word-break: break-all;
        vertical-align: top;
    }
    .invoice-report-table th:nth-child(5),
    .invoice-report-table td:nth-child(5) {
        line-height: 1.05;
        padding-top: .18rem;
        padding-bottom: .18rem;
        white-space: normal;
        overflow: hidden;
        overflow-wrap: break-word;
        word-break: break-word;
        vertical-align: top;
    }
    .customer-report-table { table-layout: fixed; }
    .customer-report-table .customer-code-col { width: 12%; }
    .customer-report-table .customer-name-col { width: 36%; }
    .customer-report-table .invoice-count-col { width: 8%; }
    .customer-report-table .amount-col { width: 12%; }
    .customer-report-table .action-col { width: 8%; }
    .customer-report-table th,
    .customer-report-table td { padding: .45rem .55rem; vertical-align: middle; }
    .customer-report-table th:first-child,
    .customer-report-table td:first-child { white-space: normal; overflow-wrap: anywhere; }
    .customer-report-table th:nth-child(2),
    .customer-report-table td:nth-child(2) { white-space: normal; overflow-wrap: anywhere; word-break: break-word; }
    .customer-report-table th:nth-child(3),
    .customer-report-table td:nth-child(3) { text-align: center !important; white-space: nowrap; }
    .customer-report-table th:nth-child(n+4):nth-child(-n+6),
    .customer-report-table td:nth-child(n+4):nth-child(-n+6) { white-space: nowrap; }
    .payment-report-table { table-layout: fixed; }
    .payment-report-table .payment-number-col { width: 13%; }
    .payment-report-table .payment-date-col { width: 9%; }
    .payment-report-table .payment-customer-col { width: 12%; }
    .payment-report-table .payment-customer-name-col { width: 22%; }
    .payment-report-table .payment-method-col { width: 11%; }
    .payment-report-table .payment-invoice-col { width: 20%; }
    .payment-report-table .payment-amount-col { width: 13%; }
    .payment-report-table th,
    .payment-report-table td { padding: .26rem .36rem; vertical-align: middle; font-size: .82rem; line-height: 1.15; }
    .payment-report-table th { font-size: .84rem; }
    .payment-report-table th:nth-child(2),
    .payment-report-table td:nth-child(2),
    .payment-report-table th:nth-child(5),
    .payment-report-table td:nth-child(5) { white-space: nowrap; }
    .payment-report-table th:nth-child(3),
    .payment-report-table td:nth-child(3),
    .payment-report-table th:nth-child(4),
    .payment-report-table td:nth-child(4),
    .payment-report-table th:nth-child(6),
    .payment-report-table td:nth-child(6) { white-space: normal; overflow-wrap: anywhere; }
    .addition-report-table { table-layout: fixed; }
    .addition-report-table .addition-number-col { width: 14%; }
    .addition-report-table .addition-date-col,
    .addition-report-table .addition-due-col { width: 10%; }
    .addition-report-table .addition-customer-col { width: 13%; }
    .addition-report-table .addition-customer-name-col { width: 35%; }
    .addition-report-table .addition-amount-col { width: 18%; }
    .addition-report-table th,
    .addition-report-table td { padding: .28rem .38rem; font-size: .82rem; vertical-align: middle; }
    .addition-report-table td:nth-child(1),
    .addition-report-table td:nth-child(2),
    .addition-report-table td:nth-child(3),
    .addition-report-table td:nth-child(6) { white-space: nowrap; }
    .addition-report-table th:nth-child(4),
    .addition-report-table td:nth-child(4),
    .addition-report-table th:nth-child(5),
    .addition-report-table td:nth-child(5) {
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    @media (max-width: 767.98px) {
        .piutang-screen-header { align-items: flex-start !important; }
        .piutang-screen-header > div:last-child { width: 100%; }
        .piutang-screen-header > div:last-child .btn { flex: 1 1 auto; }
        .piutang-screen-header .page-title { font-size: 1.05rem; }

        .piutang-summary .card-body { padding: .6rem .7rem; }
        .piutang-summary .fs-4 {
            font-size: .82rem !important;
            overflow-wrap: anywhere;
        }
        .piutang-summary .text-muted.small { font-size: .6rem; }

        .piutang-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            padding-bottom: 2px;
        }
        .piutang-tabs .nav-link { padding: .36rem .5rem; font-size: .67rem; }

        #aging .aging-controls {
            align-items: stretch !important;
            flex-direction: column;
        }
        #aging .aging-controls > div { flex-wrap: wrap; }
        #aging .aging-controls .btn { flex: 1 1 100%; }
        .aging-card .card-body { padding: .55rem .65rem !important; }
        .aging-value { font-size: .75rem; overflow-wrap: anywhere; }

        .tab-content .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        #invoice table { min-width: 1120px; }
        .aging-detail-table table,
        .aging-all-detail table { min-width: 980px; }
        .customer-report-table { min-width: 980px; }
        .invoice-report-table th,
        .invoice-report-table td,
        .aging-detail-table .table th,
        .aging-detail-table .table td,
        .aging-all-detail .table th,
        .aging-all-detail .table td,
        .customer-report-table th,
        .customer-report-table td,
        .payment-report-table th,
        .payment-report-table td,
        .addition-report-table th,
        .addition-report-table td {
            font-size: .82rem;
            padding: .34rem .42rem;
        }
        .payment-report-table { min-width: 980px; }
        .addition-report-table { min-width: 980px; }
        #mutasi table,
        #aging > .card table { min-width: 520px; }

        .pagination { flex-wrap: wrap; justify-content: center; }
        .alert.d-flex { align-items: flex-start !important; flex-direction: column; }
    }

    @media print {
        @page { size: 8.5in 11in; margin: 0.28in; }

        html, body {
            font-size: 9px !important;
            line-height: 1.15 !important;
        }

        .piutang-screen-header,
        .piutang-filter,
        .piutang-summary,
        .piutang-tabs {
            display: none !important;
        }

        .tab-pane { display: none !important; }
        #{{ $reportSection }} {
            display: block !important;
            opacity: 1 !important;
        }

        .piutang-print-header {
            display: block !important;
            text-align: center;
            margin: 0 0 6px !important;
            font-size: 9pt;
            line-height: 1.2;
        }

        .piutang-print-header .fw-bold {
            font-size: 13pt;
            letter-spacing: .4px;
        }

        .tab-content,
        #{{ $reportSection }},
        #{{ $reportSection }} .card,
        #{{ $reportSection }} .table-responsive {
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #invoice table {
            table-layout: fixed !important;
            margin: 0 !important;
        }

        #invoice .table th,
        #invoice .table td {
            width: auto !important;
            max-width: none !important;
            padding: 2px 3px !important;
            font-size: 9px !important;
            line-height: 1.18 !important;
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        #invoice .table th:nth-child(4),
        #invoice .table td:nth-child(4),
        #invoice .table th:nth-child(5),
        #invoice .table td:nth-child(5) {
            white-space: normal !important;
            overflow-wrap: anywhere !important;
            word-break: break-word !important;
            text-align: left !important;
        }

        #invoice .table th:nth-child(1), #invoice .table td:nth-child(1) { width: 12% !important; }
        #invoice .table th:nth-child(2), #invoice .table td:nth-child(2),
        #invoice .table th:nth-child(3), #invoice .table td:nth-child(3) { width: 9% !important; }
        #invoice .table th:nth-child(4), #invoice .table td:nth-child(4) { width: 11% !important; }
        #invoice .table th:nth-child(5), #invoice .table td:nth-child(5) { width: 21% !important; }
        #invoice .table th:nth-child(6), #invoice .table td:nth-child(6) { width: 7% !important; }
        #invoice .table th:nth-child(7), #invoice .table td:nth-child(7) { width: 10% !important; }
        #invoice .table th:nth-child(8), #invoice .table td:nth-child(8),
        #invoice .table th:nth-child(9), #invoice .table td:nth-child(9) { width: 10% !important; }

        #invoice .table thead th {
            font-weight: 700 !important;
            background: #fff !important;
            --bs-table-bg: #fff !important;
        }

        #invoice tfoot {
            display: table-row-group !important;
        }

        @if($reportSection === 'pelanggan')
        #pelanggan .customer-report-table { table-layout: fixed !important; }
        #pelanggan .customer-report-table th,
        #pelanggan .customer-report-table td {
            width: auto !important;
            padding: 2px 4px !important;
            font-size: 9px !important;
        }
        #pelanggan .customer-report-table th:first-child,
        #pelanggan .customer-report-table td:first-child { width: 12% !important; white-space: normal !important; text-align: left !important; }
        #pelanggan .customer-report-table th:nth-child(2),
        #pelanggan .customer-report-table td:nth-child(2) { width: 36% !important; text-align: left !important; white-space: normal !important; }
        #pelanggan .customer-report-table th:nth-child(3),
        #pelanggan .customer-report-table td:nth-child(3) { width: 8% !important; text-align: center !important; }
        #pelanggan .customer-report-table th:nth-child(4),
        #pelanggan .customer-report-table td:nth-child(4),
        #pelanggan .customer-report-table th:nth-child(5),
        #pelanggan .customer-report-table td:nth-child(5),
        #pelanggan .customer-report-table th:nth-child(6),
        #pelanggan .customer-report-table td:nth-child(6) { width: 12% !important; text-align: right !important; white-space: nowrap !important; }
        #pelanggan .customer-report-table th:last-child,
        #pelanggan .customer-report-table td:last-child,
        #pelanggan .customer-report-table col.action-col { display: none !important; }
        @endif

        @if($reportSection === 'pembayaran')
        #pembayaran .payment-report-table { table-layout: fixed !important; }
        #pembayaran .payment-report-table th,
        #pembayaran .payment-report-table td {
            width: auto !important;
            padding: 2px 3px !important;
            font-size: 9px !important;
            line-height: 1.18 !important;
        }
        #pembayaran .payment-report-table th:nth-child(1),
        #pembayaran .payment-report-table td:nth-child(1) { width: 13% !important; text-align: left !important; }
        #pembayaran .payment-report-table th:nth-child(2),
        #pembayaran .payment-report-table td:nth-child(2) { width: 9% !important; text-align: center !important; white-space: nowrap !important; }
        #pembayaran .payment-report-table th:nth-child(3),
        #pembayaran .payment-report-table td:nth-child(3) { width: 12% !important; text-align: left !important; white-space: normal !important; }
        #pembayaran .payment-report-table th:nth-child(4),
        #pembayaran .payment-report-table td:nth-child(4) { width: 22% !important; text-align: left !important; white-space: normal !important; }
        #pembayaran .payment-report-table th:nth-child(5),
        #pembayaran .payment-report-table td:nth-child(5) { width: 11% !important; text-align: center !important; white-space: nowrap !important; }
        #pembayaran .payment-report-table th:nth-child(6),
        #pembayaran .payment-report-table td:nth-child(6) { width: 20% !important; text-align: left !important; white-space: normal !important; }
        #pembayaran .payment-report-table th:nth-child(7),
        #pembayaran .payment-report-table td:nth-child(7) { width: 13% !important; text-align: right !important; white-space: nowrap !important; }
        @endif

        @if($reportSection === 'aging')
        #aging .aging-controls { display: none !important; }

        @if($agingDetail === 'all')
        #aging .aging-cards,
        #aging .aging-detail-table { display: none !important; }
        #aging .aging-all-detail { display: block !important; }
        @elseif($agingFilter !== '')
        #aging .aging-cards,
        #aging .aging-all-detail { display: none !important; }
        #aging .aging-detail-table { display: block !important; margin-top: 0 !important; }
        #aging .aging-detail-table .card-header { display: none !important; }
        #aging .aging-detail-table th:nth-child(6), #aging .aging-detail-table td:nth-child(6),
        #aging .aging-detail-table th:nth-child(7), #aging .aging-detail-table td:nth-child(7),
        #aging .aging-detail-table th:nth-child(8), #aging .aging-detail-table td:nth-child(8) { display: none !important; }
        @else
        #aging .aging-detail-table,
        #aging .aging-all-detail { display: none !important; }
        #aging .aging-cards { display: flex !important; }
        @endif

        #aging .aging-detail-table table,
        #aging .aging-all-detail table {
            width: 100% !important;
            table-layout: auto !important;
            margin: 0 0 7px !important;
        }

        #aging .aging-detail-table th,
        #aging .aging-detail-table td,
        #aging .aging-all-detail th,
        #aging .aging-all-detail td {
            width: auto !important;
            padding: 2px 3px !important;
            font-size: 9px !important;
            line-height: 1.18 !important;
            white-space: nowrap !important;
        }

        #aging .aging-detail-table th:nth-child(4),
        #aging .aging-detail-table td:nth-child(4),
        #aging .aging-all-detail th:nth-child(4),
        #aging .aging-all-detail td:nth-child(4) {
            white-space: normal !important;
            text-align: left !important;
        }

        #aging .aging-print-group h3 {
            font-size: 9pt !important;
            font-weight: 700 !important;
            margin: 6px 0 2px !important;
            padding: 2px 3px !important;
            border: 1px solid #000;
            background: #e9ecef !important;
        }
        @endif
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const printButtons = document.querySelectorAll('.piutang-print-button');
        const exportButton = document.getElementById('piutangExportButton');
        const reportSectionInput = document.getElementById('piutangReportSection');
        const positionDateField = document.getElementById('positionDateField');
        const transactionPeriodFields = document.getElementById('transactionPeriodFields');
        const customerModal = document.getElementById('reportCustomerModal');
        const customerSearch = document.getElementById('reportCustomerSearch');
        const customerResults = document.getElementById('reportCustomerResults');
        const customerLoading = document.getElementById('reportCustomerLoading');
        const customerEmpty = document.getElementById('reportCustomerEmpty');
        const customerError = document.getElementById('reportCustomerError');
        const customerFilter = document.getElementById('reportCustomerFilter');
        let customerSearchTimer;

        const escapeCustomerHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));

        function loadReportCustomers(query = '') {
            customerLoading.classList.remove('d-none');
            customerEmpty.classList.add('d-none');
            customerError.classList.add('d-none');
            customerResults.innerHTML = '';

            fetch("{{ route('pelunasan-piutang.search-customer') }}?modal=1&q=" + encodeURIComponent(query), {headers: {'Accept': 'application/json'}})
                .then(response => { if (!response.ok) throw new Error(); return response.json(); })
                .then(rows => {
                    if (!rows.length) { customerEmpty.classList.remove('d-none'); return; }
                    customerResults.innerHTML = rows.map(row => `<tr><td class="fw-semibold">${escapeCustomerHtml(row.customer)}</td><td>${escapeCustomerHtml(row.nama || '-')}</td><td class="text-end"><button type="button" class="btn btn-primary btn-sm select-report-customer" data-customer="${encodeURIComponent(row.customer)}">Pilih</button></td></tr>`).join('');
                })
                .catch(() => customerError.classList.remove('d-none'))
                .finally(() => customerLoading.classList.add('d-none'));
        }

        customerModal?.addEventListener('shown.bs.modal', function () {
            customerSearch.value = customerFilter.value;
            customerSearch.focus();
            loadReportCustomers(customerSearch.value);
        });
        customerSearch?.addEventListener('input', function () {
            clearTimeout(customerSearchTimer);
            customerSearchTimer = setTimeout(() => loadReportCustomers(customerSearch.value.trim()), 250);
        });
        customerResults?.addEventListener('click', function (event) {
            const button = event.target.closest('.select-report-customer');
            if (!button) return;
            customerFilter.value = decodeURIComponent(button.dataset.customer);
            bootstrap.Modal.getInstance(customerModal).hide();
        });
        document.getElementById('clearReportCustomer')?.addEventListener('click', () => { customerFilter.value = ''; });

        document.querySelectorAll('.piutang-tabs [data-report-section]').forEach(function (tabButton) {
            tabButton.addEventListener('shown.bs.tab', function () {
                if (reportSectionInput) {
                    reportSectionInput.value = tabButton.dataset.reportSection;
                }

                if (transactionPeriodFields) {
                    const usesTransactionPeriod = ['mutasi', 'penambahan', 'pembayaran'].includes(tabButton.dataset.reportSection);
                    transactionPeriodFields.classList.toggle('d-none', !usesTransactionPeriod);
                    positionDateField?.classList.toggle('d-none', usesTransactionPeriod);
                }

                printButtons.forEach(function (printButton) {
                    const printUrl = new URL(printButton.href, window.location.origin);
                    printUrl.searchParams.set('section', tabButton.dataset.reportSection);
                    printUrl.searchParams.delete('page');
                    printButton.href = printUrl.toString();
                });

                if (exportButton) {
                    const exportUrl = new URL(exportButton.href, window.location.origin);
                    exportUrl.searchParams.set('section', tabButton.dataset.reportSection);
                    exportUrl.searchParams.delete('page');
                    exportUrl.searchParams.delete('print');
                    exportButton.href = exportUrl.toString();
                }
            });
        });
    });
</script>
@endpush

@if($printMode ?? false)
@push('scripts')
<script>
    window.addEventListener('load', function () {
        const cleanUrl = new URL(window.location.href);
        cleanUrl.searchParams.delete('print');
        cleanUrl.searchParams.delete('page');
        window.history.replaceState({}, document.title, cleanUrl.toString());

        window.setTimeout(function () {
            window.print();
        }, 100);
    });
</script>
@endpush
@endif
