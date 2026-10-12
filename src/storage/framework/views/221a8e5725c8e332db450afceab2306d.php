<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', config('app.name', 'FinLedger')); ?> — <?php echo e(config('app.name', 'FinLedger')); ?></title>

    <?php
        $isPdfRender = request()->boolean('print')
            || request()->routeIs('laporan.*.pdf', 'laporan.stream_pdf');
    ?>

    <!-- Fonts: PDF menggunakan font bawaan DomPDF agar tidak bergantung pada cache Google Font. -->
    <?php if (! ($isPdfRender)): ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php endif; ?>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>

    <style>
        * {
            font-family: <?php echo e($isPdfRender ? "'DejaVu Sans', sans-serif" : "'Inter', sans-serif"); ?>;
            box-sizing: border-box;
        }

        html,
        body {
            overflow-x: hidden;
        }

        body {
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 240px;
            background: #1e293b;
            border-right: 1px solid #334155;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid #334155;
        }

        .sidebar-brand-logo {
            position: relative;
            width: 42px;
            height: 42px;
            background: linear-gradient(145deg, #fef3c7 0%, #fbbf24 48%, #d97706 100%);
            border: 1px solid rgba(254, 243, 199, .55);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #422006;
            font-size: 1.2rem;
            flex-shrink: 0;
            box-shadow: 0 7px 18px rgba(217, 119, 6, .28), inset 0 1px 0 rgba(255, 255, 255, .45);
            overflow: hidden;
        }

        .sidebar-brand-logo::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, .18), transparent 48%);
            pointer-events: none;
        }

        .sidebar-brand-logo i {
            position: relative;
            z-index: 1;
        }

        .sidebar-brand-name {
            position: relative;
            display: inline-block;
            font-size: 1.16rem;
            font-weight: 800;
            line-height: 1.15;
            color: #fde68a;
            letter-spacing: -.045em;
            text-shadow: 0 2px 12px rgba(245, 158, 11, .24);
        }

        .sidebar-brand-name .brand-fin {
            font-weight: 600;
            font-style: italic;
            color: #fde68a;
        }

        .sidebar-brand-name .brand-ledger {
            margin-left: .04em;
            color: #fbbf24;
            font-weight: 800;
        }

        .sidebar-brand-name::after {
            content: '';
            display: block;
            width: 32px;
            height: 2px;
            margin-top: 3px;
            border-radius: 999px;
            background: linear-gradient(90deg, #fde68a, #f59e0b, transparent);
        }

        .sidebar-brand-sub {
            font-size: 0.68rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0.75rem;
            overflow-y: auto;
        }

        .sidebar-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748b;
            padding: 0.5rem 0.5rem 0.25rem;
            margin-top: 0.5rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.55rem 0.75rem;
            border-radius: 8px;
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.92rem;
            font-weight: 600;
            transition: all 0.15s;
            margin-bottom: 2px;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
        }

        .sidebar-link.active {
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
        }

        .sidebar-link i {
            font-size: 1.05rem;
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-group summary {
            list-style: none;
            cursor: pointer;
            background: rgba(71, 85, 105, 0.55);
            border: 1px solid rgba(148, 163, 184, 0.18);
            color: #e2e8f0;
            font-weight: 650;
            margin-top: 0.25rem;
        }

        .sidebar-group summary:hover {
            background: rgba(99, 102, 241, 0.22);
            border-color: rgba(165, 180, 252, 0.3);
            color: #ffffff;
        }

        .sidebar-group summary.active,
        .sidebar-group[open] summary {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.38), rgba(99, 102, 241, 0.2));
            border-color: rgba(165, 180, 252, 0.42);
            color: #c7d2fe;
        }

        .sidebar-group summary::-webkit-details-marker {
            display: none;
        }

        .sidebar-group-chevron {
            margin-left: auto;
            transition: transform 0.15s ease;
        }

        .sidebar-group[open] .sidebar-group-chevron {
            transform: rotate(90deg);
        }

        .sidebar-submenu {
            margin: 0.15rem 0 0.35rem 1.05rem;
            padding-left: 0.55rem;
            border-left: 1px solid #475569;
        }

        .sidebar-submenu .sidebar-link {
            font-size: 0.86rem;
            padding: 0.48rem 0.65rem;
        }

        .sidebar-footer {
            padding: 0.75rem;
            border-top: 1px solid #334155;
        }

        .developer-contact {
            margin-top: .55rem;
            padding: .6rem .7rem;
            border: 1px solid #334155;
            border-radius: 10px;
            background: rgba(255, 255, 255, .035);
            color: #cbd5e1;
            font-size: .68rem;
            line-height: 1.35;
        }

        .developer-contact-label {
            color: #94a3b8;
            font-size: .58rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .developer-contact-name {
            margin: .1rem 0 .35rem;
            color: #b6c2d1;
            font-weight: 500;
        }

        .developer-contact-links {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
        }

        .developer-contact-link {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .2rem .4rem;
            border-radius: 6px;
            background: rgba(255, 255, 255, .06);
            color: #cbd5e1;
            text-decoration: none;
            white-space: nowrap;
        }

        .developer-contact-link:hover {
            background: rgba(255, 255, 255, .12);
            color: #fff;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.6rem 0.75rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #334155;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .user-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #e2e8f0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 0.68rem;
            color: #64748b;
        }

        /* Main content */
        .main-wrapper {
            margin-left: 240px;
            width: calc(100% - 240px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow-x: visible;
        }

        .page-content {
            flex: 1;
            width: 100%;
            padding: 1.5rem;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            overflow-x: visible;
            min-width: 0;
        }

        .page-content>.container-fluid {
            width: 100%;
            min-width: 0;
        }

        .settings-page {
            width: 100%;
            min-width: 0;
            overflow-x: auto;
        }

        .settings-scroll-row {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            align-items: flex-start;
            gap: 1rem;
            min-width: 1120px;
            padding-bottom: 0.75rem;
        }

        .settings-scroll-row>.col-lg-6 {
            min-width: 560px;
            flex: 0 0 560px;
        }

        .settings-scroll-row>.col-12 {
            min-width: 100%;
            flex: 0 0 100%;
        }

        .settings-scroll-row .card {
            min-width: 0;
        }

        .settings-page .card {
            min-width: 0;
            overflow: visible;
        }

        .settings-page .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .settings-page .table-responsive table {
            min-width: 100%;
        }

        .role-permissions-card {
            overflow-x: auto;
            padding-bottom: 1rem;
        }

        .role-permissions-card table {
            min-width: 960px;
        }

        .role-permissions-card th,
        .role-permissions-card td {
            white-space: nowrap;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 240px;
            background: #1e293b;
            border-right: 1px solid #334155;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .topbar {
            height: 56px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .topbar-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
        }

        .mobile-topbar-brand {
            display: none;
            align-items: center;
            gap: .45rem;
            text-decoration: none;
        }

        .mobile-topbar-logo {
            width: 29px;
            height: 29px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            background: linear-gradient(145deg, #fef3c7, #fbbf24 55%, #d97706);
            color: #422006;
            font-size: .82rem;
            box-shadow: 0 3px 9px rgba(217, 119, 6, .22);
        }

        .mobile-topbar-name {
            color: #b45309;
            font-size: .9rem;
            font-weight: 800;
            letter-spacing: -.04em;
            white-space: nowrap;
        }

        .mobile-topbar-name .brand-fin {
            color: #d97706;
            font-weight: 600;
            font-style: italic;
        }

        .mobile-topbar-name .brand-ledger {
            margin-left: .03em;
            color: #92400e;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .topbar-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.15s;
        }

        .topbar-btn:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .role-permissions-card {
            overflow-x: auto;
            padding-bottom: 1rem;
        }

        .role-permissions-card table {
            width: max-content;
            min-width: 100%;
        }

        .role-permissions-card th,
        .role-permissions-card td {
            white-space: nowrap;
        }

        @media (max-width: 1200px) {
            .settings-page {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        @media (max-width: 992px) {
            .main-wrapper {
                margin-left: 0;
                width: 100%;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .page-content {
                padding: 1rem;
            }
        }

        .page-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.02em;
        }

        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .mobile-topbar-brand { display: inline-flex; }

            .topbar-title { display: none; }

            .sidebar {
                transform: translateX(-100%);
                width: min(86vw, 320px);
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.28);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
                width: 100%;
                min-width: 0;
            }

            .topbar {
                height: 52px;
                padding: 0 0.9rem;
            }

            .topbar-title {
                font-size: 0.84rem;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .topbar-actions {
                gap: 0.35rem;
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .topbar-btn {
                width: 32px;
                height: 32px;
                font-size: 0.85rem;
            }

            .page-content {
                padding: 0.9rem;
                gap: 0.8rem;
            }

            .sidebar-brand {
                padding: 1rem 1rem 0.9rem;
            }

            .sidebar-nav {
                padding: 0.8rem 0.65rem;
            }

            .sidebar-link {
                padding: 0.65rem 0.7rem;
                font-size: 0.9rem;
            }

            .sidebar-footer {
                padding: 0.7rem;
            }

            .user-card {
                padding: 0.65rem;
            }

            .user-name {
                font-size: 0.76rem;
            }

            .user-role {
                font-size: 0.64rem;
            }

            .topbar-actions a {
                min-width: 40px;
            }
        }

        @media (max-width: 576px) {
            .topbar {
                padding: 0 0.75rem;
            }

            .page-content {
                padding: 0.75rem;
                gap: 0.7rem;
            }

            .sidebar-brand-name {
                font-size: 1.08rem;
            }

            .sidebar-brand-sub {
                font-size: 0.65rem;
            }

            .sidebar-link {
                padding: 0.6rem 0.65rem;
                font-size: 0.88rem;
            }
        }

        @media print {

            .sidebar,
            .topbar,
            .footer,
            .btn,
            .dropdown-menu,
            .alert,
            .toast {
                display: none !important;
            }

            .main-wrapper {
                margin-left: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .page-content {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .container,
            .container-fluid,
            .card,
            .card-body,
            .table-responsive {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .table {
                width: 100% !important;
                table-layout: fixed !important;
            }
        }

        .sidebar-logout-btn {
            background: none;
            border: none;
            color: #64748b;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
            flex-shrink: 0;
        }

        .sidebar-logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .sidebar-password-btn:hover {
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }
    </style>
    <?php echo $__env->make('laporan._dot-matrix-print', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center gap-2">
                <div class="sidebar-brand-logo" aria-hidden="true"><i class="bi bi-journal-richtext"></i></div>
                <div>
                    <div class="sidebar-brand-name" aria-label="<?php echo e(config('app.name')); ?>"><span class="brand-fin">Fin</span><span class="brand-ledger">Ledger</span></div>
                    <div class="sidebar-brand-sub"><?php echo e(config('app.tagline')); ?></div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php
                $lastSection = null;
                $openingBalanceRoutes = ['saldo-awal', 'piutang', 'saldo-awal-hutang', 'driver-opening-receivables'];
                $openingBalanceMenus = $sidebarMenus->filter(fn ($item) => in_array($item->route_name, $openingBalanceRoutes, true));
                $openingBalanceGroupRendered = false;
                $openingBalanceGroupActive = request()->routeIs('saldo-awal.*')
                    || request()->routeIs('piutang.*')
                    || request()->routeIs('saldo-awal-hutang.*')
                    || request()->routeIs('driver-opening-receivables.*');
                $journalRoutes = ['jurnal', 'jurnal-penyesuaian'];
                $journalMenus = $sidebarMenus->filter(fn ($item) => in_array($item->route_name, $journalRoutes, true));
                $journalGroupRendered = false;
                $journalGroupActive = request()->routeIs('jurnal.*') || request()->routeIs('jurnal-penyesuaian.*');
                $piutangRoutes = ['piutang-berjalan', 'pelunasan-piutang'];
                $piutangGroupedRoutes = $piutangRoutes;
                $piutangMenus = $sidebarMenus->filter(fn ($item) => in_array($item->route_name, $piutangRoutes, true));
                $piutangGroupRendered = false;
                $canViewLaporanPiutang = auth()->user()?->hasMenuPermission('laporan.piutang', 'view') ?? false;
                $piutangGroupActive = request()->routeIs('pelunasan-piutang.*')
                    || request()->routeIs('laporan.piutang*');
                $hutangRoutes = ['hutang', 'pelunasan-hutang'];
                $hutangGroupedRoutes = $hutangRoutes;
                $hutangMenus = $sidebarMenus->filter(fn ($item) => in_array($item->route_name, $hutangRoutes, true));
                $hutangGroupRendered = false;
                $canViewLaporanHutang = auth()->user()?->hasMenuPermission('laporan.hutang', 'view') ?? false;
                $hutangGroupActive = request()->routeIs('hutang.*') || request()->routeIs('pelunasan-hutang.*') || request()->routeIs('laporan.hutang*');
                $driverRoutes = ['driver-receivables', 'driver-payments'];
                $driverMenus = $sidebarMenus->filter(fn ($item) => in_array($item->route_name, $driverRoutes, true));
                $driverGroupRendered = false;
                $driverGroupActive = request()->routeIs('driver-receivables.*') || request()->routeIs('driver-payments.*');
                $importRoutes = ['penjualan-hosting', 'import-data'];
                $importMenus = $sidebarMenus->filter(fn ($item) => in_array($item->route_name, $importRoutes, true));
                $importGroupRendered = false;
                $importGroupActive = request()->routeIs('penjualan-hosting.*') || request()->routeIs('import-data.*');
                $collapsibleSections = ['Master' => 'Master Data', 'Laporan' => 'Laporan', 'Pengaturan' => 'Pengaturan'];
                $sectionGroupRendered = ['Master' => false, 'Laporan' => false, 'Pengaturan' => false];
            ?>

            <?php $__currentLoopData = $sidebarMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($menu->section !== ($lastSection ?? null)
                && !array_key_exists($menu->section, $collapsibleSections)
                && !in_array($menu->route_name, $importRoutes, true)): ?>
            <div class="sidebar-section-label"><?php echo e($menu->section ?: 'Menu'); ?></div>
            <?php endif; ?>
            <?php $lastSection = $menu->section; ?>

            <?php if(array_key_exists($menu->section, $collapsibleSections)): ?>
                <?php
                    $sectionMenus = $sidebarMenus->where('section', $menu->section);
                    $sectionActive = $sectionMenus->contains(fn ($item) => request()->routeIs($item->route_name) || request()->routeIs($item->route_name.'.*'));
                    $sectionIcon = match($menu->section) { 'Master' => 'bi bi-database-fill-gear', 'Laporan' => 'bi bi-file-earmark-bar-graph-fill', default => 'bi bi-gear-fill' };
                ?>
                <?php if(!$sectionGroupRendered[$menu->section]): ?>
                    <details class="sidebar-group" <?php if($sectionActive): ?> open <?php endif; ?>>
                        <summary class="sidebar-link <?php echo e($sectionActive ? 'active' : ''); ?>"><i class="<?php echo e($sectionIcon); ?>"></i><span><?php echo e($collapsibleSections[$menu->section]); ?></span><i class="bi bi-chevron-right sidebar-group-chevron"></i></summary>
                        <div class="sidebar-submenu">
                            <?php $__currentLoopData = $sectionMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionMenu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $sectionRoute = \Illuminate\Support\Facades\Route::has($sectionMenu->route_name) ? $sectionMenu->route_name : $sectionMenu->route_name.'.index'; ?>
                                <?php if(\Illuminate\Support\Facades\Route::has($sectionRoute)): ?>
                                <a href="<?php echo e(route($sectionRoute)); ?>" class="sidebar-link <?php echo e(request()->routeIs($sectionRoute) || request()->routeIs($sectionRoute.'.*') ? 'active' : ''); ?>"><i class="<?php echo e($sectionMenu->icon ?: 'bi bi-circle'); ?>"></i> <?php echo e($sectionMenu->name); ?></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                    <?php $sectionGroupRendered[$menu->section] = true; ?>
                <?php endif; ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if(in_array($menu->route_name, $openingBalanceRoutes, true)): ?>
                <?php if(!$openingBalanceGroupRendered): ?>
                    <details class="sidebar-group" <?php if($openingBalanceGroupActive): ?> open <?php endif; ?>>
                        <summary class="sidebar-link <?php echo e($openingBalanceGroupActive ? 'active' : ''); ?>">
                            <i class="bi bi-wallet2"></i>
                            <span>
                                <span>Saldo Awal</span>
                                <small class="d-block opacity-75" style="font-size:.68rem;line-height:1.05;">Input awal, tanpa jurnal</small>
                            </span>
                            <i class="bi bi-chevron-right sidebar-group-chevron"></i>
                        </summary>
                        <div class="sidebar-submenu">
                            <?php $__currentLoopData = $openingBalanceMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $openingMenu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $openingRoute = \Illuminate\Support\Facades\Route::has($openingMenu->route_name)
                                        ? $openingMenu->route_name
                                        : $openingMenu->route_name.'.index';
                                ?>
                                <?php if(\Illuminate\Support\Facades\Route::has($openingRoute)): ?>
                                    <a href="<?php echo e(route($openingRoute)); ?>" class="sidebar-link <?php echo e(request()->routeIs($openingRoute) || request()->routeIs($openingRoute.'.*') ? 'active' : ''); ?>" title="<?php echo e($openingMenu->description ?: 'Input saldo awal tanpa jurnal otomatis'); ?>">
                                        <i class="<?php echo e($openingMenu->icon ?: 'bi bi-circle'); ?>"></i> <?php echo e($openingMenu->name); ?>

                                    </a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                    <?php $openingBalanceGroupRendered = true; ?>
                <?php endif; ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if(in_array($menu->route_name, $journalRoutes, true)): ?>
                <?php if(!$journalGroupRendered): ?>
                    <details class="sidebar-group" <?php if($journalGroupActive): ?> open <?php endif; ?>>
                        <summary class="sidebar-link <?php echo e($journalGroupActive ? 'active' : ''); ?>">
                            <i class="bi bi-journal-bookmark-fill"></i>
                            <span>Jurnal</span>
                            <i class="bi bi-chevron-right sidebar-group-chevron"></i>
                        </summary>
                        <div class="sidebar-submenu">
                            <?php $__currentLoopData = $journalMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journalMenu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $journalRoute = \Illuminate\Support\Facades\Route::has($journalMenu->route_name) ? $journalMenu->route_name : $journalMenu->route_name.'.index'; ?>
                                <a href="<?php echo e(route($journalRoute)); ?>" class="sidebar-link <?php echo e(request()->routeIs($journalRoute) || request()->routeIs($journalRoute.'.*') ? 'active' : ''); ?>">
                                    <i class="<?php echo e($journalMenu->icon ?: 'bi bi-circle'); ?>"></i> <?php echo e($journalMenu->name); ?>

                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                    <?php $journalGroupRendered = true; ?>
                <?php endif; ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if(in_array($menu->route_name, $piutangGroupedRoutes, true)): ?>
                <?php if(!$piutangGroupRendered): ?>
                    <details class="sidebar-group" <?php if($piutangGroupActive): ?> open <?php endif; ?>>
                        <summary class="sidebar-link <?php echo e($piutangGroupActive ? 'active' : ''); ?>">
                            <i class="bi bi-cash-stack"></i>
                            <span>Piutang</span>
                            <i class="bi bi-chevron-right sidebar-group-chevron"></i>
                        </summary>
                        <div class="sidebar-submenu">
                            <?php $__currentLoopData = $piutangMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $piutangMenu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $piutangRoute = $piutangMenu->route_name;
                                    if (! \Illuminate\Support\Facades\Route::has($piutangRoute) && \Illuminate\Support\Facades\Route::has($piutangRoute.'.index')) {
                                        $piutangRoute .= '.index';
                                    }
                                ?>
                                <a href="<?php echo e(route($piutangRoute)); ?>" class="sidebar-link <?php echo e(request()->routeIs($piutangRoute.'.*') ? 'active' : ''); ?>">
                                    <i class="<?php echo e($piutangMenu->icon ?: 'bi bi-circle'); ?>"></i> <?php echo e($piutangMenu->name); ?>

                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                    <?php $piutangGroupRendered = true; ?>
                <?php endif; ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if(in_array($menu->route_name, $hutangGroupedRoutes, true)): ?>
                <?php if(!$hutangGroupRendered): ?>
                    <details class="sidebar-group" <?php if($hutangGroupActive): ?> open <?php endif; ?>>
                        <summary class="sidebar-link <?php echo e($hutangGroupActive ? 'active' : ''); ?>">
                            <i class="bi bi-journal-minus"></i>
                            <span>Hutang</span>
                            <i class="bi bi-chevron-right sidebar-group-chevron"></i>
                        </summary>
                        <div class="sidebar-submenu">
                            <?php $__currentLoopData = $hutangMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hutangMenu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $hutangRoute = \Illuminate\Support\Facades\Route::has($hutangMenu->route_name)
                                        ? $hutangMenu->route_name
                                        : $hutangMenu->route_name.'.index';
                                ?>
                                <a href="<?php echo e(route($hutangRoute)); ?>" class="sidebar-link <?php echo e(request()->routeIs($hutangRoute.'.*') ? 'active' : ''); ?>">
                                    <i class="<?php echo e($hutangMenu->icon ?: 'bi bi-circle'); ?>"></i> <?php echo e($hutangMenu->name); ?>

                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                    <?php $hutangGroupRendered = true; ?>
                <?php endif; ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if(in_array($menu->route_name, $driverRoutes, true)): ?>
                <?php if(!$driverGroupRendered): ?>
                    <details class="sidebar-group" <?php if($driverGroupActive): ?> open <?php endif; ?>>
                        <summary class="sidebar-link <?php echo e($driverGroupActive ? 'active' : ''); ?>">
                            <i class="bi bi-truck"></i><span>Piutang Sopir</span><i class="bi bi-chevron-right sidebar-group-chevron"></i>
                        </summary>
                        <div class="sidebar-submenu">
                            <?php $__currentLoopData = $driverMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driverMenu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $driverRoute = \Illuminate\Support\Facades\Route::has($driverMenu->route_name) ? $driverMenu->route_name : $driverMenu->route_name.'.index'; ?>
                                <a href="<?php echo e(route($driverRoute)); ?>" class="sidebar-link <?php echo e(request()->routeIs($driverRoute) || request()->routeIs($driverRoute.'.*') ? 'active' : ''); ?>"><i class="<?php echo e($driverMenu->icon ?: 'bi bi-circle'); ?>"></i> <?php echo e($driverMenu->name); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                    <?php $driverGroupRendered = true; ?>
                <?php endif; ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if(in_array($menu->route_name, $importRoutes, true)): ?>
                <?php if(!$importGroupRendered): ?>
                    <details class="sidebar-group" <?php if($importGroupActive): ?> open <?php endif; ?>>
                        <summary class="sidebar-link <?php echo e($importGroupActive ? 'active' : ''); ?>">
                            <i class="bi bi-cloud-arrow-down"></i><span>Impor & Sinkronisasi</span><i class="bi bi-chevron-right sidebar-group-chevron"></i>
                        </summary>
                        <div class="sidebar-submenu">
                            <?php $__currentLoopData = $importMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $importMenu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $importRoute = \Illuminate\Support\Facades\Route::has($importMenu->route_name) ? $importMenu->route_name : $importMenu->route_name.'.index'; ?>
                                <a href="<?php echo e(route($importRoute)); ?>" class="sidebar-link <?php echo e(request()->routeIs($importRoute) || request()->routeIs($importRoute.'.*') ? 'active' : ''); ?>"><i class="<?php echo e($importMenu->icon ?: 'bi bi-circle'); ?>"></i> <?php echo e($importMenu->name); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>
                    <?php $importGroupRendered = true; ?>
                <?php endif; ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php
            $sidebarRoute = $menu->route_name;
            if (! \Illuminate\Support\Facades\Route::has($sidebarRoute) && \Illuminate\Support\Facades\Route::has($sidebarRoute . '.index')) {
            $sidebarRoute = $sidebarRoute . '.index';
            }
            ?>

            <a href="<?php echo e(\Illuminate\Support\Facades\Route::has($sidebarRoute) ? route($sidebarRoute) : '#'); ?>" class="sidebar-link <?php echo e(request()->routeIs($sidebarRoute.'*') ? 'active' : ''); ?>">
                <i class="<?php echo e($menu->icon ?: 'bi bi-circle'); ?>"></i> <?php echo e($menu->name); ?>

            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        <div class="sidebar-footer">
            <?php
                $developerName = config('app.developer_name');
                $developerWhatsapp = preg_replace('/\D+/', '', (string) config('app.developer_whatsapp'));
                $developerEmail = config('app.developer_email');
            ?>
            <div class="user-card">
                <div class="user-avatar">
                    <?php echo e(strtoupper(substr(auth()->user()->name ?? 'U', 0, 1))); ?>

                </div>
                <div style="flex:1;min-width:0;">
                    <div class="user-name"><?php echo e(auth()->user()->name ?? 'User'); ?></div>
                    <div class="user-role"><?php echo e(auth()->user()->role?->name ?? 'User'); ?></div>
                </div>
                <a href="<?php echo e(route('profile.password')); ?>" class="sidebar-logout-btn sidebar-password-btn" title="Profil & Password" aria-label="Profil dan Password">
                    <i class="bi bi-person-gear"></i>
                </a>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="sidebar-logout-btn" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
            <?php if($developerName || $developerWhatsapp || $developerEmail): ?>
            <div class="developer-contact">
                <div class="developer-contact-label">Dikembangkan oleh</div>
                <?php if($developerName): ?><div class="developer-contact-name"><?php echo e($developerName); ?></div><?php endif; ?>
                <div class="developer-contact-links">
                    <?php if($developerWhatsapp): ?>
                    <a class="developer-contact-link" href="https://wa.me/<?php echo e($developerWhatsapp); ?>" target="_blank" rel="noopener noreferrer" title="Hubungi melalui WhatsApp">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                    <?php endif; ?>
                    <?php if($developerEmail): ?>
                    <a class="developer-contact-link" href="mailto:<?php echo e($developerEmail); ?>" title="Kirim email">
                        <i class="bi bi-envelope"></i> Email
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Bar -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="topbar-btn d-md-none" onclick="toggleSidebar()" style="background:none;border:none;">
                    <i class="bi bi-list"></i>
                </button>
                <a href="<?php echo e(route('dashboard')); ?>" class="mobile-topbar-brand" aria-label="<?php echo e(config('app.name')); ?>">
                    <span class="mobile-topbar-logo"><i class="bi bi-journal-richtext"></i></span>
                    <span class="mobile-topbar-name"><span class="brand-fin">Fin</span><span class="brand-ledger">Ledger</span></span>
                </a>
                <?php if (! (request()->routeIs('dashboard'))): ?>
                <span class="topbar-title"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></span>
                <?php endif; ?>
                <?php if(request()->routeIs('dashboard')): ?>
                    <?php echo $__env->yieldContent('dashboard-topbar'); ?>
                <?php endif; ?>
            </div>
            <div class="topbar-actions">
                <?php if (! (request()->routeIs('dashboard'))): ?>
                <a href="<?php echo e(route('jurnal.create')); ?>" class="topbar-btn" title="Jurnal Baru">
                    <i class="bi bi-plus-lg"></i>
                </a>
                <a href="<?php echo e(route('laporan.index')); ?>" class="topbar-btn" title="Laporan">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                </a>
                <?php endif; ?>
                <?php if(auth()->user()?->hasMenuPermission('settings', 'view')): ?>
                <a href="<?php echo e(route('settings.index')); ?>" class="topbar-btn" title="Pengaturan Role, Menu & Hak Akses">
                    <i class="bi bi-gear"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content">
            <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if(\Illuminate\Support\Facades\View::hasSection('content')): ?>
            <?php echo $__env->yieldContent('content'); ?>
            <?php elseif(isset($slot)): ?>
            <?php echo e($slot); ?>

            <?php endif; ?>

        </div>
    </div>

    <!-- Mobile overlay -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:999;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            var isOpen = sidebar.classList.toggle('open');
            overlay.style.display = isOpen ? 'block' : 'none';
            document.body.style.overflow = isOpen ? 'hidden' : '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            var sidebarNav = sidebar ? sidebar.querySelector('.sidebar-nav') : null;

            // Saat grup paling bawah dibuka, geser scroll di dalam sidebar agar
            // seluruh submenu yang memungkinkan langsung terlihat.
            if (sidebarNav) {
                sidebarNav.querySelectorAll('details.sidebar-group').forEach(function(group) {
                    group.addEventListener('toggle', function() {
                        if (!group.open) return;

                        window.requestAnimationFrame(function() {
                            var navRect = sidebarNav.getBoundingClientRect();
                            var groupRect = group.getBoundingClientRect();
                            var padding = 12;

                            if (groupRect.bottom > navRect.bottom - padding) {
                                sidebarNav.scrollBy({
                                    top: groupRect.bottom - navRect.bottom + padding,
                                    behavior: 'smooth'
                                });
                            } else if (groupRect.top < navRect.top + padding) {
                                sidebarNav.scrollBy({
                                    top: groupRect.top - navRect.top - padding,
                                    behavior: 'smooth'
                                });
                            }
                        });
                    });
                });
            }

            // Tutup sidebar HP hanya ketika user memilih link tujuan.
            // Summary grup Piutang/Hutang harus tetap terbuka agar submenu terlihat.
            document.querySelectorAll('a.sidebar-link[href]').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('open');
                        overlay.style.display = 'none';
                        document.body.style.overflow = '';
                    }
                });
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('open');
                    overlay.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH /var/www/html/src/resources/views/layouts/app.blade.php ENDPATH**/ ?>