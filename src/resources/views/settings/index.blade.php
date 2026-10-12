@extends('layouts.app')

@section('title', 'Pengaturan Role, Menu & Hak Akses')

@push('styles')
<style>
    .role-permissions-card {
        border: 1px solid #dee2e6;
        border-radius: .75rem;
        background: #fff;
        overflow: hidden;
    }

    .role-permissions-toolbar {
        padding: .85rem 1rem;
        margin: 0 !important;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        gap: .75rem;
    }

    .role-permissions-form {
        padding: 0 1rem 1rem;
    }

    .role-permissions-scroll {
        max-height: min(84vh, 900px);
        min-height: min(66vh, 680px);
        overflow: auto;
        overscroll-behavior: contain;
        border: 1px solid #dee2e6;
        border-top: 0;
        scrollbar-gutter: stable;
    }

    .role-permissions-table {
        min-width: 720px;
        margin-bottom: 0 !important;
    }

    .role-permissions-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #f1f3f5;
        box-shadow: inset 0 -1px 0 #ced4da;
        vertical-align: middle;
    }

    .role-permissions-table th:first-child,
    .role-permissions-table td:first-child {
        position: sticky;
        left: 0;
        min-width: 230px;
    }

    .role-permissions-table th:first-child {
        z-index: 4;
        background: #f1f3f5;
    }

    .role-permissions-table td:first-child {
        z-index: 2;
        background: #fff;
        box-shadow: inset -1px 0 0 #dee2e6;
    }

    .role-permissions-table tbody tr:nth-of-type(odd) td:first-child {
        background: #f8f9fa;
    }

    .role-permissions-actions {
        padding-top: 1rem;
    }

    .settings-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1rem;
    }

    .role-permission-picker {
        max-width: 420px;
    }

    .role-permission-panel[hidden] {
        display: none !important;
    }

    .settings-panel-checkbox {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .settings-panel-checkbox:not(:checked) ~ .settings-add-panel {
        display: none !important;
    }

    .settings-panel-checkbox:not(:checked) ~ .settings-section-details {
        display: none !important;
    }

    .settings-panel-checkbox:checked ~ .settings-add-panel {
        display: block !important;
    }

    .settings-panel-checkbox:checked ~ .settings-section-details {
        display: block !important;
    }

    .settings-panel-close-label {
        display: none;
    }

    .settings-panel-checkbox:checked ~ .settings-section-header .settings-panel-open-label {
        display: none;
    }

    .settings-panel-checkbox:checked ~ .settings-section-header .settings-panel-close-label {
        display: inline;
    }

    .settings-menu-scroll {
        max-height: min(55vh, 520px);
        overflow: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        border-top: 1px solid #dee2e6;
    }

    .settings-menu-table {
        min-width: 850px;
        margin-bottom: 0 !important;
    }

    .settings-menu-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #f1f3f5;
        box-shadow: inset 0 -1px 0 #ced4da;
    }

    .settings-menu-table th:first-child,
    .settings-menu-table td:first-child {
        position: sticky;
        left: 0;
        min-width: 190px;
    }

    .settings-menu-table th:last-child,
    .settings-menu-table td:last-child {
        position: sticky;
        right: 0;
        min-width: 145px;
    }

    .settings-menu-table th:first-child,
    .settings-menu-table th:last-child {
        z-index: 4;
        background: #f1f3f5;
    }

    .settings-menu-table td:first-child,
    .settings-menu-table td:last-child {
        z-index: 2;
        background: #fff;
    }

    .settings-menu-table td:first-child {
        box-shadow: inset -1px 0 0 #dee2e6;
    }

    .settings-menu-table td:last-child {
        box-shadow: inset 1px 0 0 #dee2e6;
    }

    @media (max-width: 767.98px) {
        .settings-page {
            padding: .75rem .5rem !important;
        }

        .settings-page > .row,
        .settings-page .card-body {
            margin-left: 0;
            margin-right: 0;
        }

        .role-permissions-toolbar {
            align-items: flex-start !important;
            flex-direction: column;
            padding: .75rem;
        }

        .role-permissions-toolbar > div {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: .5rem !important;
        }

        .role-permissions-toolbar .btn {
            width: 100%;
            min-height: 42px;
            margin: 0 !important;
        }

        .role-permissions-card {
            overflow: visible !important;
            padding-bottom: 0 !important;
            border-radius: .6rem;
        }

        .role-permissions-form {
            padding: .65rem;
        }

        .role-permissions-scroll {
            max-height: none;
            min-height: 0;
            overflow: visible !important;
            border: 0;
            scrollbar-gutter: auto;
        }

        .role-permissions-table {
            display: block;
            width: 100% !important;
            min-width: 0 !important;
            border: 0;
        }

        .role-permissions-table thead {
            display: none;
        }

        .role-permissions-table tbody {
            display: grid;
            gap: .7rem;
            width: 100%;
        }

        .role-permissions-table tbody tr {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            width: 100%;
            overflow: hidden;
            border: 1px solid #dbe3ec;
            border-radius: .65rem;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        }

        .role-permissions-table th,
        .role-permissions-table td {
            position: static !important;
            min-width: 0 !important;
            width: auto !important;
            white-space: normal !important;
            box-shadow: none !important;
        }

        .role-permissions-table tbody td:first-child {
            grid-column: 1 / -1;
            padding: .7rem .75rem;
            background: #f8fafc !important;
            border: 0;
            border-bottom: 1px solid #dbe3ec;
            overflow-wrap: anywhere;
        }

        .role-permissions-table tbody td:not(:first-child) {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 64px;
            padding: .4rem .15rem;
            border: 0;
            border-right: 1px solid #edf0f3;
            border-bottom: 1px solid #edf0f3;
        }

        .role-permissions-table tbody td:not(:first-child)::before {
            content: attr(data-label);
            margin-bottom: .3rem;
            color: #64748b;
            font-size: .67rem;
            font-weight: 700;
            line-height: 1;
        }

        .role-permissions-table .role-permission-checkbox {
            width: 1.45rem;
            height: 1.45rem;
            margin: 0;
            cursor: pointer;
        }

        .role-permissions-actions {
            position: sticky;
            bottom: 0;
            z-index: 5;
            padding: .65rem 0 0;
            background: linear-gradient(to bottom, rgba(255,255,255,0), #fff 22%);
        }

        .role-permissions-actions .btn {
            width: 100%;
            min-height: 46px;
        }

        .menu-create-form .row > div {
            width: 100%;
        }

        .menu-create-form .form-label,
        .menu-edit-modal .form-label {
            margin-bottom: .3rem;
            font-size: .78rem;
            font-weight: 700;
        }

        .menu-create-form .form-control,
        .menu-create-form .form-select,
        .menu-edit-modal .form-control,
        .menu-edit-modal .form-select {
            min-height: 44px;
            font-size: .9rem;
        }

        .menu-create-form .form-check {
            width: 100%;
            padding: .7rem .75rem .7rem 2.35rem;
            border: 1px solid #dbe3ec;
            border-radius: .55rem;
            background: #f8fafc;
        }

        .menu-create-form .form-check-input {
            width: 1.3rem;
            height: 1.3rem;
            margin-left: -1.7rem;
        }

        .menu-create-actions .btn {
            width: 100%;
            min-height: 46px;
        }

        .settings-menu-scroll {
            max-height: none;
            overflow: visible !important;
            border-top: 0;
        }

        .settings-menu-table {
            display: block;
            width: 100% !important;
            min-width: 0 !important;
            padding: .65rem;
        }

        .settings-menu-table thead {
            display: none;
        }

        .settings-menu-table tbody {
            display: grid;
            gap: .65rem;
        }

        .settings-menu-table tbody tr {
            display: block;
            overflow: hidden;
            border: 1px solid #dbe3ec;
            border-radius: .65rem;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        }

        .settings-menu-table tbody td {
            position: static !important;
            display: grid;
            grid-template-columns: 88px minmax(0, 1fr);
            width: auto !important;
            min-width: 0 !important;
            padding: .5rem .7rem !important;
            border: 0;
            border-bottom: 1px solid #edf0f3;
            white-space: normal !important;
            box-shadow: none !important;
            overflow-wrap: anywhere;
        }

        .settings-menu-table tbody td::before {
            content: attr(data-label);
            color: #64748b;
            font-size: .72rem;
            font-weight: 700;
        }

        .settings-menu-table tbody td:first-child {
            grid-template-columns: 1fr;
            padding: .7rem !important;
            background: #f8fafc;
            font-size: .95rem;
            font-weight: 700;
        }

        .settings-menu-table tbody td:first-child::before {
            display: none;
        }

        .settings-menu-table tbody td:last-child {
            display: flex;
            gap: .5rem;
            padding: .65rem .7rem !important;
            border-bottom: 0;
        }

        .settings-menu-table tbody td:last-child::before {
            display: none;
        }

        .settings-menu-table tbody td:last-child .btn {
            min-width: 82px;
            min-height: 40px;
        }

        .menu-edit-modal .modal-dialog {
            margin: .5rem;
        }

        .menu-edit-modal .modal-content {
            max-height: calc(100dvh - 1rem);
            border-radius: .7rem;
        }

        .menu-edit-modal .modal-body {
            overflow-y: auto;
            padding: .85rem;
        }

        .menu-edit-modal .modal-body .row > div {
            width: 100%;
        }

        .menu-edit-modal .modal-footer {
            position: sticky;
            bottom: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
            padding: .7rem .85rem;
            background: #fff;
        }

        .menu-edit-modal .modal-footer .btn {
            width: 100%;
            min-height: 44px;
            margin: 0;
        }

        .settings-section-header {
            align-items: stretch;
            flex-direction: column;
        }

        .settings-section-header .btn,
        .role-permission-picker {
            width: 100%;
            max-width: none;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 settings-page">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-1">Pengaturan Role, Menu & Hak Akses</h4>
                            <p class="text-muted mb-0">Kelola role, menu, dan hak akses.</p>
                        </div>
                    </div>

                    @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div style="width: 100%; padding-bottom: 20px;">
        <div style="width: 100%;">
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <input type="checkbox" class="settings-panel-checkbox" id="addRoleToggle">
                        <div class="card-header bg-white border-0 settings-section-header">
                            <div><h5 class="mb-1">Master Role</h5><small class="text-muted">Kelola role pengguna aplikasi.</small></div>
                            <label class="btn btn-primary btn-sm mb-0" for="addRoleToggle"><span class="settings-panel-open-label"><i class="bi bi-plus-circle me-1"></i>Tambah Role</span><span class="settings-panel-close-label"><i class="bi bi-eye-slash me-1"></i>Sembunyikan Role</span></label>
                        </div>
                        <div class="card-body border-bottom settings-add-panel" id="addRolePanel">
                            <form method="POST" action="{{ route('settings.roles.store') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Role</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Deskripsi</label>
                                        <input type="text" name="description" class="form-control">
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">Tambah Role</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-body p-0 table-responsive settings-section-details">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Nama Role</th>
                                        <th>Deskripsi</th>
                                        <th class="pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                    <tr>
                                        <td class="ps-3">{{ $role->name }}</td>
                                        <td>{{ $role->description }}</td>
                                        <td class="d-flex gap-2 pe-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">Edit</button>
                                            <form method="POST" action="{{ route('settings.roles.destroy', $role) }}" onsubmit="return confirm('Hapus role ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <input type="checkbox" class="settings-panel-checkbox" id="addMenuToggle">
                        <div class="card-header bg-white border-0 settings-section-header">
                            <div><h5 class="mb-1">Master Menu</h5><small class="text-muted">Tambah menu hanya jika diperlukan.</small></div>
                            <label class="btn btn-primary btn-sm mb-0" for="addMenuToggle"><span class="settings-panel-open-label"><i class="bi bi-plus-circle me-1"></i>Tambah Menu</span><span class="settings-panel-close-label"><i class="bi bi-eye-slash me-1"></i>Sembunyikan Menu</span></label>
                        </div>
                        <div class="card-body border-bottom settings-add-panel" id="addMenuPanel">
                            <form method="POST" action="{{ route('settings.menus.store') }}" class="menu-create-form">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Menu</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Route Name</label>
                                        <select name="route_name" class="form-select" required>
                                            <option value="">Pilih Route</option>
                                            @foreach ($routeNames as $routeName)
                                            <option value="{{ $routeName }}">{{ $routeName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Section</label>
                                        <input type="text" name="section" class="form-control" placeholder="Menu Utama / Transaksi / Laporan / Setting">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Icon</label>
                                        <input type="text" name="icon" class="form-control" placeholder="bi bi-list-columns">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control" value="0">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_sidebar" value="1" id="is_sidebar" checked>
                                            <label class="form-check-label" for="is_sidebar">Tampilkan Sidebar</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end menu-create-actions">
                                        <button type="submit" class="btn btn-primary">Tambah Menu</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-body p-0 table-responsive settings-menu-scroll settings-section-details">
                            <table class="table table-hover table-striped align-middle settings-menu-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Nama</th>
                                        <th>Route</th>
                                        <th>Section</th>
                                        <th>Sidebar</th>
                                        <th class="pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($menus as $menu)
                                    <tr>
                                        <td class="ps-3" data-label="Nama">{{ $menu->name }}</td>
                                        <td data-label="Route"><code>{{ $menu->route_name }}</code></td>
                                        <td data-label="Section">{{ $menu->section }}</td>
                                        <td data-label="Sidebar">{{ $menu->is_sidebar ? 'Ya' : 'Tidak' }}</td>
                                        <td class="d-flex gap-2 pe-3" data-label="Aksi">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editMenuModal{{ $menu->id }}">Edit</button>
                                            <form method="POST" action="{{ route('settings.menus.destroy', $menu) }}" onsubmit="return confirm('Hapus menu ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 settings-section-header">
                            <div><h5 class="mb-1">Hak Akses Role</h5><small class="text-muted">Pilih satu role untuk melihat dan mengubah hak aksesnya.</small></div>
                            <div class="role-permission-picker">
                                <label for="rolePermissionSelector" class="visually-hidden">Pilih Role</label>
                                <select id="rolePermissionSelector" class="form-select">
                                    @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach ($roles as $role)
                            <div class="mb-4 role-permissions-card role-permission-panel" data-role-id="{{ $role->id }}" @if(!$loop->first) hidden @endif>
                                <div class="d-flex align-items-center justify-content-between role-permissions-toolbar">
                                    <h6 class="mb-0 text-primary fw-bold">{{ $role->name }}</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-2 role-permissions-select-all">Pilih Semua</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary role-permissions-clear-all">Uncheck Semua</button>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('settings.roles.permissions.update', $role) }}" class="role-permissions-form">
                                    @csrf
                                    <div class="table-responsive role-permissions-scroll">
                                        <table class="table table-bordered table-striped align-middle role-permissions-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Menu</th>
                                                    <th class="text-center" style="min-width: 60px;"><small>View</small><br><input type="checkbox" class="form-check-input header-toggle mt-1" data-permission="can_view"></th>
                                                    <th class="text-center" style="min-width: 60px;"><small>Create</small><br><input type="checkbox" class="form-check-input header-toggle mt-1" data-permission="can_create"></th>
                                                    <th class="text-center" style="min-width: 60px;"><small>Edit</small><br><input type="checkbox" class="form-check-input header-toggle mt-1" data-permission="can_edit"></th>
                                                    <th class="text-center" style="min-width: 60px;"><small>Delete</small><br><input type="checkbox" class="form-check-input header-toggle mt-1" data-permission="can_delete"></th>
                                                    <th class="text-center" style="min-width: 60px;"><small>Post</small><br><input type="checkbox" class="form-check-input header-toggle mt-1" data-permission="can_post"></th>
                                                    <th class="text-center" style="min-width: 60px;"><small>Void</small><br><input type="checkbox" class="form-check-input header-toggle mt-1" data-permission="can_void"></th>
                                                    <th class="text-center" style="min-width: 60px;"><small>Export</small><br><input type="checkbox" class="form-check-input header-toggle mt-1" data-permission="can_export"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($menus as $menu)
                                                @php
                                                $pivot = $role->menus->firstWhere('id', $menu->id)?->pivot;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span class="fw-medium">{{ $menu->name }}</span><br>
                                                        <small class="text-muted">{{ $menu->route_name }}</small>
                                                        <input type="hidden" name="permissions[{{ $menu->id }}][present]" value="1">
                                                    </td>
                                                    <td class="text-center" data-label="View">
                                                        <input type="checkbox" class="form-check-input role-permission-checkbox" data-row="{{ $menu->id }}" name="permissions[{{ $menu->id }}][can_view]" value="1" {{ $pivot?->can_view ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center" data-label="Create">
                                                        <input type="checkbox" class="form-check-input role-permission-checkbox" data-row="{{ $menu->id }}" name="permissions[{{ $menu->id }}][can_create]" value="1" {{ $pivot?->can_create ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center" data-label="Edit">
                                                        <input type="checkbox" class="form-check-input role-permission-checkbox" data-row="{{ $menu->id }}" name="permissions[{{ $menu->id }}][can_edit]" value="1" {{ $pivot?->can_edit ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center" data-label="Delete">
                                                        <input type="checkbox" class="form-check-input role-permission-checkbox" data-row="{{ $menu->id }}" name="permissions[{{ $menu->id }}][can_delete]" value="1" {{ $pivot?->can_delete ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center" data-label="Post">
                                                        <input type="checkbox" class="form-check-input role-permission-checkbox" data-row="{{ $menu->id }}" name="permissions[{{ $menu->id }}][can_post]" value="1" {{ $pivot?->can_post ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center" data-label="Void">
                                                        <input type="checkbox" class="form-check-input role-permission-checkbox" data-row="{{ $menu->id }}" name="permissions[{{ $menu->id }}][can_void]" value="1" {{ $pivot?->can_void ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center" data-label="Export">
                                                        <input type="checkbox" class="form-check-input role-permission-checkbox" data-row="{{ $menu->id }}" name="permissions[{{ $menu->id }}][can_export]" value="1" {{ $pivot?->can_export ? 'checked' : '' }}>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end role-permissions-actions">
                                        <button type="submit" class="btn btn-primary">Simpan Hak Akses {{ $role->name }}</button>
                                    </div>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div> @foreach ($roles as $role)
    <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('settings.roles.update', $role) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Role</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', $role->description) }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    @foreach ($menus as $menu)
    <div class="modal fade menu-edit-modal" id="editMenuModal{{ $menu->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('settings.menus.update', $menu) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Menu</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $menu->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Route Name</label>
                                <select name="route_name" class="form-select" required>
                                    <option value="">Pilih Route</option>
                                    @foreach ($routeNames as $routeName)
                                    <option value="{{ $routeName }}" {{ old('route_name', $menu->route_name) === $routeName ? 'selected' : '' }}>{{ $routeName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Section</label>
                                <input type="text" name="section" class="form-control" value="{{ old('section', $menu->section) }}" placeholder="Menu Utama / Transaksi / Laporan / Setting">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Icon</label>
                                <input type="text" name="icon" class="form-control" value="{{ old('icon', $menu->icon) }}" placeholder="bi bi-list-columns">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $menu->sort_order) }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_sidebar" value="1" id="is_sidebar_{{ $menu->id }}" {{ old('is_sidebar', $menu->is_sidebar) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_sidebar_{{ $menu->id }}">Tampilkan Sidebar</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Deskripsi</label>
                                <input type="text" name="description" class="form-control" value="{{ old('description', $menu->description) }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelector = document.getElementById('rolePermissionSelector');
        const rolePanels = document.querySelectorAll('.role-permission-panel');

        function showSelectedRole() {
            if (!roleSelector) return;
            rolePanels.forEach(function(panel) {
                panel.hidden = panel.dataset.roleId !== roleSelector.value;
            });
            try { window.sessionStorage.setItem('settingsSelectedRole', roleSelector.value); } catch (error) {}
        }

        if (roleSelector) {
            let savedRole = null;
            try { savedRole = window.sessionStorage.getItem('settingsSelectedRole'); } catch (error) {}
            if (savedRole && roleSelector.querySelector('option[value="' + savedRole + '"]')) {
                roleSelector.value = savedRole;
            }
            roleSelector.addEventListener('change', showSelectedRole);
            showSelectedRole();
        }

        function toggleRowView(rowId) {
            const rowCheckboxes = document.querySelectorAll('input.role-permission-checkbox[data-row="' + rowId + '"]');
            const viewCheckbox = document.querySelector('input.role-permission-checkbox[name="permissions[' + rowId + '][can_view]"]');
            const anyOtherChecked = Array.from(rowCheckboxes).some(function(checkbox) {
                return checkbox.name !== 'permissions[' + rowId + '][can_view]' && checkbox.checked;
            });

            if (anyOtherChecked) {
                viewCheckbox.checked = true;
            }

            if (!viewCheckbox.checked) {
                rowCheckboxes.forEach(function(checkbox) {
                    if (checkbox.name !== 'permissions[' + rowId + '][can_view]') {
                        checkbox.checked = false;
                    }
                });
            }
        }

        function updateHeaderToggles(container) {
            container.querySelectorAll('input.header-toggle').forEach(function(headerCheckbox) {
                const permission = headerCheckbox.dataset.permission;
                const checkboxes = container.querySelectorAll('input.role-permission-checkbox[name*="[' + permission + ']"]');
                headerCheckbox.checked = Array.from(checkboxes).length > 0 && Array.from(checkboxes).every(function(checkbox) {
                    return checkbox.checked;
                });
            });
        }

        document.querySelectorAll('.role-permissions-select-all').forEach(function(button) {
            button.addEventListener('click', function() {
                const card = button.closest('.mb-4');
                card.querySelectorAll('input.role-permission-checkbox').forEach(function(checkbox) {
                    checkbox.checked = true;
                });
                updateHeaderToggles(card);
            });
        });

        document.querySelectorAll('.role-permissions-clear-all').forEach(function(button) {
            button.addEventListener('click', function() {
                const card = button.closest('.mb-4');
                card.querySelectorAll('input.role-permission-checkbox').forEach(function(checkbox) {
                    checkbox.checked = false;
                });
                updateHeaderToggles(card);
            });
        });

        document.querySelectorAll('input.header-toggle').forEach(function(headerCheckbox) {
            headerCheckbox.addEventListener('change', function() {
                const permission = headerCheckbox.dataset.permission;
                const card = headerCheckbox.closest('.role-permissions-card');
                const checkboxes = card.querySelectorAll('input.role-permission-checkbox[name*="[' + permission + ']"]');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = headerCheckbox.checked;
                });

                if (permission !== 'can_view' && headerCheckbox.checked) {
                    card.querySelectorAll('input.role-permission-checkbox[name*="[can_view]"]').forEach(function(viewCheckbox) {
                        viewCheckbox.checked = true;
                    });
                }

                updateHeaderToggles(card);
            });
        });

        document.querySelectorAll('input.role-permission-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const match = checkbox.name.match(/permissions\[(\d+)\]\[(.+)\]/);
                if (!match) {
                    return;
                }
                const rowId = match[1];
                toggleRowView(rowId);
                const card = checkbox.closest('.role-permissions-card');
                if (card) {
                    updateHeaderToggles(card);
                }
            });
        });

        document.querySelectorAll('.mb-4').forEach(function(card) {
            card.querySelectorAll('input.role-permission-checkbox').forEach(function(checkbox) {
                const match = checkbox.name.match(/permissions\[(\d+)\]\[(.+)\]/);
                if (match && match[2] !== 'can_view') {
                    toggleRowView(match[1]);
                }
            });
            updateHeaderToggles(card);
        });
    });
</script>
@endpush
