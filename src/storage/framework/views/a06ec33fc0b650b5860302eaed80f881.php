<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('dashboard-topbar'); ?>
<div class="dashboard-topbar-content">
    <strong class="dashboard-topbar-title">Dashboard</strong>
    <div class="dashboard-topbar-group">
        <span class="dashboard-topbar-group-label">Input</span>
        <a href="<?php echo e(route('jurnal.index')); ?>" class="dashboard-topbar-link" title="Lihat dan kelola jurnal yang sudah dibuat"><i class="bi bi-journals"></i><span>Daftar Jurnal</span></a>
        <a href="<?php echo e(route('jurnal-penyesuaian.create')); ?>" class="dashboard-topbar-link"><i class="bi bi-sliders"></i><span>Penyesuaian</span></a>
        <a href="<?php echo e(route('pelunasan-piutang.create')); ?>" class="dashboard-topbar-link"><i class="bi bi-cash-coin"></i><span>Pelunasan Piutang</span></a>
        <a href="<?php echo e(route('driver-payments.create')); ?>" class="dashboard-topbar-link"><i class="bi bi-cash-stack"></i><span>Pelunasan Sopir</span></a>
        <a href="<?php echo e(route('pelunasan-hutang.create')); ?>" class="dashboard-topbar-link"><i class="bi bi-wallet2"></i><span>Pelunasan Hutang</span></a>
        <a href="<?php echo e(route('driver-receivables.create')); ?>" class="dashboard-topbar-link"><i class="bi bi-truck"></i><span>Piutang Sopir</span></a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $labaRugi = $labaBersih;
    $accountSections = [
        ['title' => 'Aset', 'accounts' => $asets, 'total' => $totalAset, 'color' => '#059669'],
        ['title' => 'Kewajiban', 'accounts' => $kewajibans, 'total' => $totalKewajiban, 'color' => '#e11d48'],
        ['title' => 'Ekuitas', 'accounts' => $ekuitas, 'total' => $totalEkuitas, 'color' => '#7c3aed'],
    ];
    $profitSections = $canViewProfitLoss ? [
        ['title' => 'Pendapatan', 'accounts' => $pendapatans, 'total' => $totalPendapatan, 'color' => '#059669'],
        ['title' => 'Beban', 'accounts' => $bebans, 'total' => $totalBeban, 'color' => '#d97706'],
    ] : [];
    $tipeColors = [
        'Aset' => ['bg' => '#eef2ff', 'text' => '#4f46e5'],
        'Kewajiban' => ['bg' => '#fff1f2', 'text' => '#e11d48'],
        'Ekuitas' => ['bg' => '#f5f3ff', 'text' => '#7c3aed'],
        'Pendapatan' => ['bg' => '#ecfdf5', 'text' => '#059669'],
        'Beban' => ['bg' => '#fffbeb', 'text' => '#d97706'],
    ];
?>

<style>
    .dashboard-topbar-content{display:flex;align-items:center;gap:1rem;min-width:0}.dashboard-topbar-title{font-size:1.48rem;font-weight:850;letter-spacing:-.035em;white-space:nowrap;background:linear-gradient(90deg,#3730a3,#7c3aed);-webkit-background-clip:text;background-clip:text;color:transparent}.dashboard-topbar-group{display:flex;align-items:center;gap:.35rem;padding-left:.8rem;border-left:1px solid #e2e8f0}.dashboard-topbar-group-label{display:none}.dashboard-topbar-link{display:flex;align-items:center;gap:.35rem;min-height:34px;padding:.34rem .52rem;border:1px solid #dce3ec;border-radius:7px;background:#f8fafc;color:#334155;text-decoration:none;font-size:.64rem;font-weight:650;white-space:nowrap;box-shadow:0 1px 2px rgba(15,23,42,.04)}.dashboard-topbar-link:hover{filter:saturate(1.08) brightness(.98);color:#334155;transform:translateY(-1px);box-shadow:0 3px 8px rgba(15,23,42,.08)}.dashboard-topbar-link i{display:inline-grid;place-items:center;width:18px;height:18px;border-radius:5px;color:#64748b;font-size:.72rem}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(1){background:#f0efff;border-color:#ddd9ff;color:#4f46a8}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(1) i{background:#e2dfff;color:#5b4fc4}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(2){background:#f4f7fb;border-color:#dfe6ef;color:#475569}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(2) i{background:#e7edf5;color:#64748b}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(3){background:#f1f8f7;border-color:#d9ebe8;color:#39736b}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(3) i{background:#dff0ed;color:#43877d}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(4){background:#fff6f1;border-color:#f3e1d7;color:#976149}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(4) i{background:#f8e8df;color:#a96b50}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(5){background:#f4f2fb;border-color:#e4dff3;color:#6e5a96}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(5) i{background:#e9e3f6;color:#7a65a5}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(6){background:#f1f7fc;border-color:#dbe9f4;color:#4f718d}.dashboard-topbar-group .dashboard-topbar-link:nth-of-type(6) i{background:#e2eef7;color:#587f9e}.dashboard-topbar-primary{box-shadow:0 1px 3px rgba(79,70,229,.08)}.dashboard-topbar-primary:hover{color:#4f46a8}.dashboard-topbar-primary i{color:#5b4fc4}
    .report-groups{display:grid;grid-template-columns:3fr 2fr;gap:.8rem;width:100%;margin:.2rem 0 1rem}.report-group{padding:.65rem .72rem;border:1px solid #d9dee6;border-radius:11px;background:linear-gradient(135deg,#eef0f3,#d7d9dd);box-shadow:0 3px 9px rgba(15,23,42,.07)}.report-group-title{display:flex;align-items:center;gap:.48rem;margin-bottom:.55rem;color:#273449;font-size:.74rem;font-weight:800;text-transform:uppercase}.report-group-title i{display:grid;place-items:center;width:27px;height:27px;border-radius:8px;background:rgba(255,255,255,.72);color:#64748b;box-shadow:0 1px 4px rgba(15,23,42,.08)}.report-shortcuts-menu{display:flex;align-items:stretch;justify-content:space-between;width:100%;gap:.5rem}.report-shortcut{display:flex;align-items:center;justify-content:center;gap:.4rem;flex:1;min-width:0;padding:.43rem .5rem;border:1px solid rgba(255,255,255,.9);border-radius:7px;background:#fff;color:#475569;text-decoration:none;text-align:center;font-size:.68rem;font-weight:680;white-space:nowrap;box-shadow:0 2px 5px rgba(15,23,42,.06)}.report-shortcut:hover{background:#eef2ff;border-color:#c7d2fe;color:#4338ca}.report-shortcut i{color:#64748b}
    .summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.7rem;margin-bottom:1rem}
    .summary-card{position:relative;width:100%;min-height:145px;padding:1rem;text-align:left;background:#fff;border:1px solid rgba(255,255,255,.7);border-radius:11px;box-shadow:0 5px 14px rgba(15,23,42,.09);transition:.18s;overflow:visible;color:inherit;text-decoration:none}
    .summary-card:hover{transform:translateY(-2px);border-color:#cbd5e1;box-shadow:0 8px 20px rgba(15,23,42,.08);color:inherit}
    .summary-icon{display:grid;place-items:center;width:38px;height:38px;margin-bottom:.7rem;border-radius:10px;font-size:1rem}
    .summary-label{color:#8491a5;font-size:.66rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase}.summary-value{margin-top:.16rem;color:#172033;font-size:.92rem;font-weight:750;line-height:1.25;overflow-wrap:anywhere;word-break:break-word;font-variant-numeric:tabular-nums}
    .summary-hint{display:flex;align-items:center;gap:.3rem;margin-top:.55rem;color:#64748b;font-size:.68rem;font-weight:650}
    .dashboard-panel{background:#fff;border:1px solid #e3e9f1;border-radius:14px;overflow:hidden}.expand-panel{display:none;margin-bottom:1rem}.expand-panel.is-open{display:block}
    .panel-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.85rem 1rem;background:#fafbfc;border-bottom:1px solid #edf1f5}.panel-head h2{margin:0;color:#243047;font-size:.88rem;font-weight:700}.panel-head a{color:#4f46e5;font-size:.76rem;font-weight:650;text-decoration:none;white-space:nowrap}
    .recap-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.7rem;padding:1rem}.recap-box{padding:.8rem;border:1px solid #e5eaf0;border-radius:10px;background:#f8fafc}.recap-label{color:#718096;font-size:.7rem;font-weight:650}.recap-value{margin-top:.2rem;color:#172033;font-size:.94rem;font-weight:750;overflow-wrap:anywhere}
    details.account-group{border-top:1px solid #edf1f5}details.account-group>summary{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:.8rem;padding:.75rem 1rem;list-style:none;cursor:pointer;color:#344258;font-size:.8rem;font-weight:680}details.account-group>summary::-webkit-details-marker{display:none}details.account-group>summary:hover{background:#f8fafc}details.account-group .arrow{transition:.18s}details.account-group[open]>summary .arrow{transform:rotate(90deg)}
    .sub-group{margin-left:.65rem;border-left:2px solid #edf1f5}.account-row{display:grid;grid-template-columns:110px minmax(0,1fr) minmax(115px,auto);gap:.7rem;padding:.52rem 1rem;border-top:1px dashed #e3e9f1;font-size:.77rem}.account-code{color:#718096;font-family:Consolas,monospace}.account-name{color:#344258}.account-balance{text-align:right;color:#172033;font-weight:680}
    .attention-panel{margin-top:.2rem}
    .attention-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.75rem;padding:1rem}
    .attention-card{display:flex;flex-direction:column;gap:.35rem;min-width:0;padding:.9rem 1rem;border:1px solid #e5eaf0;border-radius:12px;background:#f8fafc;color:inherit;text-decoration:none;transition:.15s}
    .attention-card:hover{background:#fff;border-color:#cbd5e1;box-shadow:0 4px 12px rgba(15,23,42,.06);color:inherit;transform:translateY(-1px)}
    .attention-card.is-alert{background:#fff7ed;border-color:#fdba74}
    .attention-card.is-danger{background:#fff1f2;border-color:#fda4af}
    .attention-card.is-ok{background:#ecfdf5;border-color:#86efac}
    .attention-top{display:flex;align-items:center;justify-content:space-between;gap:.5rem}
    .attention-label{color:#64748b;font-size:.68rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;line-height:1.25}
    .attention-icon{display:grid;place-items:center;flex:0 0 auto;width:28px;height:28px;border-radius:8px;background:rgba(255,255,255,.7);color:#64748b;font-size:.85rem}
    .attention-card.is-alert .attention-icon{color:#c2410c;background:#ffedd5}
    .attention-card.is-danger .attention-icon{color:#e11d48;background:#ffe4e6}
    .attention-card.is-ok .attention-icon{color:#059669;background:#d1fae5}
    .attention-value{color:#172033;font-size:1.05rem;font-weight:750;line-height:1.25;overflow-wrap:anywhere}
    .attention-hint{color:#64748b;font-size:.7rem;font-weight:650;line-height:1.35}
    @media(max-width:1100px){.attention-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:767px){.attention-grid{grid-template-columns:1fr}}
    /* Penyelarasan akhir mengikuti komposisi dashboard pilihan pengguna. */
    .dashboard-topbar-title{font-family:Georgia,'Times New Roman',serif;font-size:1.5rem;font-weight:800;letter-spacing:-.025em;background:none;-webkit-background-clip:initial;background-clip:initial;color:#374151}
    .dashboard-topbar-group .dashboard-topbar-link:nth-of-type(1){background:#7160c9;border-color:#6857bd;color:#fff;box-shadow:0 3px 8px rgba(79,70,229,.22)}
    .dashboard-topbar-group .dashboard-topbar-link:nth-of-type(1) i{background:rgba(255,255,255,.14);color:#fff}
    .dashboard-topbar-group .dashboard-topbar-link:nth-of-type(2){background:#bdd9dc;border-color:#abcdd1;color:#294e53}
    .dashboard-topbar-group .dashboard-topbar-link:nth-of-type(3){background:#bddbd7;border-color:#abd0cb;color:#305d57}
    .dashboard-topbar-group .dashboard-topbar-link:nth-of-type(4){background:#e7b9a9;border-color:#dba997;color:#714535}
    .dashboard-topbar-group .dashboard-topbar-link:nth-of-type(5){background:#d5b8d3;border-color:#c6a8c4;color:#60455e}
    .dashboard-topbar-group .dashboard-topbar-link:nth-of-type(6){background:#bec4df;border-color:#adb5d5;color:#434b70}
    .dashboard-topbar-group .dashboard-topbar-link:nth-of-type(7){background:#b8d8c0;border-color:#a7cbb1;color:#3f6048}
    .report-group{border-color:#d7dce4;background:linear-gradient(135deg,#f8f9fb,#f0f2f5);box-shadow:0 2px 7px rgba(15,23,42,.05)}
    .summary-card:nth-child(1){background:linear-gradient(135deg,#eaf3fb,#b9d4ef)}
    .summary-card:nth-child(2){background:linear-gradient(135deg,#fff4ea,#f0c79a)}
    .summary-card:nth-child(3){background:linear-gradient(135deg,#edf9f0,#bfe3c7)}
    .summary-card:nth-child(4){background:linear-gradient(135deg,#f1f2fb,#cdd0ef)}
    .summary-card:nth-child(5){background:linear-gradient(135deg,#fff0ed,#f1aaa0)}
    .summary-card:nth-child(6){background:linear-gradient(135deg,#f9efed,#e7bfbc)}
    @media(max-width:1100px){.summary-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media(max-width:1100px){.dashboard-topbar-group-label{display:none}.dashboard-topbar-link span{display:none}.dashboard-topbar-link{padding:.3rem;min-height:38px}.dashboard-topbar-link i{width:28px;height:28px}.dashboard-topbar-content{gap:.55rem}.dashboard-topbar-group{gap:.2rem;padding-left:.45rem}}
    @media(max-width:767px){.mobile-topbar-brand{display:none!important}}
    @media(max-width:767px){.topbar{height:auto!important;min-height:54px;padding:.38rem .6rem!important}.topbar>.d-flex:first-child{min-width:0;flex:1;gap:.4rem!important}.dashboard-topbar-content{min-width:0;flex:1;gap:.55rem;overflow-x:auto;padding:.12rem 0 .22rem;scrollbar-width:thin}.dashboard-topbar-title{display:inline-block!important;flex:0 0 auto;font-size:1.08rem}.dashboard-topbar-group{flex:0 0 auto;padding-left:.45rem}.dashboard-topbar-link{min-height:34px;padding:.3rem .45rem}.dashboard-topbar-link span{display:inline!important}.dashboard-topbar-link i{width:18px;height:18px}.topbar-actions{flex:0 0 auto}.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.recap-grid{grid-template-columns:1fr}.summary-card{min-height:125px}.report-shortcuts{justify-content:flex-start;padding:.65rem}.report-shortcuts-menu{justify-content:flex-start}.account-row{grid-template-columns:90px minmax(0,1fr)}.account-balance{grid-column:2;text-align:left}}
    @media(max-width:767px){.report-groups{grid-template-columns:1fr}.report-shortcuts-menu{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));width:100%}.report-group:first-child .report-shortcuts-menu .report-shortcut:last-child{grid-column:1/-1}.report-shortcut{white-space:normal}}
    @media(max-width:430px){.summary-card{padding:.8rem}.summary-value{font-size:.82rem}.report-shortcut{font-size:.65rem;padding:.38rem .5rem}}
</style>

<div class="summary-grid">
    <?php if($canViewSalesReport): ?>
        <a href="<?php echo e(route('laporan.penjualan', ['date_from' => $salesDateFrom, 'date_to' => $salesDateTo])); ?>" class="summary-card" title="Buka detail laporan penjualan">
            <span class="summary-icon" style="background:#eef6ff;color:#2563eb"><i class="bi bi-cart-check"></i></span>
            <div class="summary-label">Penjualan Bulan Ini</div>
            <div class="summary-value" style="color:#2563eb"><?php echo e(number_format($totalPenjualan, 0, ',', '.')); ?></div>
            <span class="summary-hint"><?php echo e(number_format($jumlahPenjualan, 0, ',', '.')); ?> faktur · Lihat detail <i class="bi bi-arrow-right"></i></span>
        </a>
    <?php endif; ?>
    <?php if($canViewExpenseReport): ?>
        <a href="<?php echo e(route('laporan.biaya', ['tanggal_awal' => $expenseDateFrom, 'tanggal_akhir' => $expenseDateTo])); ?>" class="summary-card" title="Buka detail laporan biaya">
            <span class="summary-icon" style="background:#fff7ed;color:#c2410c"><i class="bi bi-receipt-cutoff"></i></span>
            <div class="summary-label">Biaya Bulan Ini</div>
            <div class="summary-value" style="color:#c2410c"><?php echo e(number_format($totalBiayaBulanIni, 0, ',', '.')); ?></div>
            <span class="summary-hint"><?php echo e(number_format($jumlahAkunBiaya, 0, ',', '.')); ?> akun · Lihat detail <i class="bi bi-arrow-right"></i></span>
        </a>
    <?php endif; ?>
    <?php if($canViewProfitLoss): ?>
        <a href="<?php echo e(route('laporan.laba_rugi', [
                'mode_periode' => 'range',
                'tanggal_awal' => $profitLossDateFrom,
                'tanggal_akhir' => $profitLossDateTo,
            ])); ?>" class="summary-card" title="Buka detail laba rugi bulan ini">
            <span class="summary-icon" style="background:<?php echo e($labaRugi >= 0 ? '#ecfdf5' : '#fff1f2'); ?>;color:<?php echo e($labaRugi >= 0 ? '#059669' : '#e11d48'); ?>"><i class="bi bi-graph-up-arrow"></i></span>
            <div class="summary-label">Laba Rugi Bulan Ini</div>
            <div class="summary-value" style="color:<?php echo e($labaRugi >= 0 ? '#059669' : '#e11d48'); ?>"><?php echo e($labaRugi >= 0 ? '+' : ''); ?><?php echo e(number_format($labaRugi,0,',','.')); ?></div>
            <span class="summary-hint">Laba/rugi bersih setelah pajak · <?php echo e(\Carbon\Carbon::parse($profitLossDateFrom)->translatedFormat('d M')); ?> – <?php echo e(\Carbon\Carbon::parse($profitLossDateTo)->translatedFormat('d M Y')); ?> · Lihat detail <i class="bi bi-arrow-right"></i></span>
        </a>
    <?php endif; ?>
    <?php if($canViewReceivableReport): ?>
        <a href="<?php echo e(route('laporan.piutang')); ?>" class="summary-card" title="Buka detail laporan piutang">
            <span class="summary-icon" style="background:#eef2ff;color:#4f46e5"><i class="bi bi-people"></i></span>
            <div class="summary-label">Saldo Piutang</div>
            <div class="summary-value" style="color:#4f46e5"><?php echo e(number_format($saldoPiutang, 0, ',', '.')); ?></div>
            <span class="summary-hint"><?php echo e(number_format($jumlahPiutangOpen, 0, ',', '.')); ?> faktur open · Lihat detail <i class="bi bi-arrow-right"></i></span>
        </a>
    <?php endif; ?>
    <?php if($canViewPayableReport): ?>
        <a href="<?php echo e(route('laporan.hutang')); ?>" class="summary-card" title="Buka detail laporan hutang">
            <span class="summary-icon" style="background:#fff1f2;color:#e11d48"><i class="bi bi-journal-minus"></i></span>
            <div class="summary-label">Saldo Hutang</div>
            <div class="summary-value" style="color:#e11d48"><?php echo e(number_format($saldoHutang, 0, ',', '.')); ?></div>
            <span class="summary-hint"><?php echo e(number_format($jumlahHutangOpen, 0, ',', '.')); ?> faktur open · Lihat detail <i class="bi bi-arrow-right"></i></span>
        </a>
    <?php endif; ?>
    <?php if($canViewDriverReceivableReport): ?>
        <a href="<?php echo e(route('laporan.driver-receivables')); ?>" class="summary-card" title="Buka detail laporan piutang sopir">
            <span class="summary-icon" style="background:#fff6f1;color:#c2410c"><i class="bi bi-truck"></i></span>
            <div class="summary-label">Piutang Sopir</div>
            <div class="summary-value" style="color:#c2410c"><?php echo e(number_format($saldoPiutangSopir, 0, ',', '.')); ?></div>
            <span class="summary-hint"><?php echo e(number_format($jumlahPiutangSopirOpen, 0, ',', '.')); ?> open · Lihat detail <i class="bi bi-arrow-right"></i></span>
        </a>
    <?php endif; ?>
</div>

<div class="report-groups" aria-label="Menu cepat laporan">
    <nav class="report-group" aria-label="Laporan utama">
        <div class="report-group-title"><i class="bi bi-file-earmark-text"></i>Laporan Utama</div>
        <div class="report-shortcuts-menu">
        <a href="<?php echo e(route('laporan.neraca')); ?>" class="report-shortcut"><i class="bi bi-bank"></i>Neraca</a>
        <?php if($canViewProfitLoss): ?><a href="<?php echo e(route('laporan.laba_rugi')); ?>" class="report-shortcut"><i class="bi bi-graph-up-arrow"></i>Rugi Laba</a><?php endif; ?>
        <a href="<?php echo e(route('laporan.neraca_saldo')); ?>" class="report-shortcut"><i class="bi bi-table"></i>Neraca Saldo</a>
        </div>
    </nav>
    <nav class="report-group" aria-label="Laporan lainnya">
        <div class="report-group-title"><i class="bi bi-folder2-open"></i>Laporan Lainnya</div>
        <div class="report-shortcuts-menu">
        <a href="<?php echo e(route('laporan.buku_besar')); ?>" class="report-shortcut"><i class="bi bi-book"></i>Buku Besar</a>
        <?php if($canViewSalesReport): ?><a href="<?php echo e(route('laporan.penjualan')); ?>" class="report-shortcut"><i class="bi bi-cart-check"></i>Penjualan</a><?php endif; ?>
        <?php if($canViewExpenseReport): ?><a href="<?php echo e(route('laporan.biaya')); ?>" class="report-shortcut"><i class="bi bi-receipt-cutoff"></i>Biaya</a><?php endif; ?>
        <a href="<?php echo e(route('laporan.index')); ?>" class="report-shortcut"><i class="bi bi-grid"></i>Semua Laporan</a>
        </div>
    </nav>
</div>

<section class="dashboard-panel attention-panel">
    <header class="panel-head">
        <h2><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Perlu Perhatian</h2>
    </header>
    <div class="attention-grid">
        <a href="<?php echo e(route('jurnal.index', [
                'status' => 'draft',
                'tanggal_awal' => now()->subYear()->startOfYear()->toDateString(),
                'tanggal_akhir' => now()->toDateString(),
            ])); ?>" class="attention-card <?php echo e($jurnalDraftCount > 0 ? 'is-alert' : 'is-ok'); ?>">
            <div class="attention-top">
                <div class="attention-label">Jurnal Draft</div>
                <span class="attention-icon"><i class="bi bi-journal-text"></i></span>
            </div>
            <div class="attention-value"><?php echo e(number_format($jurnalDraftCount, 0, ',', '.')); ?></div>
            <div class="attention-hint"><?php echo e($jurnalDraftCount > 0 ? 'Belum diposting' : 'Semua aman'); ?></div>
        </a>
        <?php if($canViewReceivableReport): ?>
            <a href="<?php echo e(route('laporan.piutang', ['status' => 'jatuh_tempo'])); ?>" class="attention-card <?php echo e($piutangJatuhTempoCount > 0 ? 'is-danger' : 'is-ok'); ?>">
                <div class="attention-top">
                    <div class="attention-label">Piutang Jatuh Tempo</div>
                    <span class="attention-icon"><i class="bi bi-people"></i></span>
                </div>
                <div class="attention-value"><?php echo e(number_format($piutangJatuhTempoSaldo, 0, ',', '.')); ?></div>
                <div class="attention-hint"><?php echo e(number_format($piutangJatuhTempoCount, 0, ',', '.')); ?> faktur</div>
            </a>
        <?php endif; ?>
        <?php if($canViewPayableReport): ?>
            <a href="<?php echo e(route('laporan.hutang', ['status' => 'jatuh_tempo'])); ?>" class="attention-card <?php echo e($hutangJatuhTempoCount > 0 ? 'is-danger' : 'is-ok'); ?>">
                <div class="attention-top">
                    <div class="attention-label">Hutang Jatuh Tempo</div>
                    <span class="attention-icon"><i class="bi bi-journal-minus"></i></span>
                </div>
                <div class="attention-value"><?php echo e(number_format($hutangJatuhTempoSaldo, 0, ',', '.')); ?></div>
                <div class="attention-hint"><?php echo e(number_format($hutangJatuhTempoCount, 0, ',', '.')); ?> faktur</div>
            </a>
        <?php endif; ?>
        <?php if($canViewDriverReceivableReport): ?>
            <a href="<?php echo e(route('laporan.driver-receivables', ['status' => 'OVERDUE'])); ?>" class="attention-card <?php echo e($piutangSopirJatuhTempoCount > 0 ? 'is-alert' : 'is-ok'); ?>">
                <div class="attention-top">
                    <div class="attention-label">Piutang Sopir JT</div>
                    <span class="attention-icon"><i class="bi bi-truck"></i></span>
                </div>
                <div class="attention-value"><?php echo e(number_format($piutangSopirJatuhTempoSaldo, 0, ',', '.')); ?></div>
                <div class="attention-hint"><?php echo e(number_format($piutangSopirJatuhTempoCount, 0, ',', '.')); ?> open</div>
            </a>
        <?php endif; ?>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/src/resources/views/dashboard.blade.php ENDPATH**/ ?>