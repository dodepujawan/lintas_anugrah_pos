@php
    $user = Auth::user();
@endphp
<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar"
    >
    <!-- Menu Toggle Button -->
    <div class="navbar-nav align-items-center">
        <button class="btn btn-outline-secondary me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sneatSidebar">
        <i class="bx bx-menu"></i>
        </button>
        <span class="navbar-page-title">
            <span class="navbar-page-title-main">Lintas Anugrah</span>
            <span class="navbar-page-title-sub">Portal Operasional</span>
        </span>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- Place this tag where you want the button to render. -->
        {{-- <li class="nav-item lh-1 me-3">
            <a
            class="github-button"
            href="https://github.com/themeselection/sneat-html-admin-template-free"
            data-icon="octicon-star"
            data-size="large"
            data-show-count="true"
            aria-label="Star themeselection/sneat-html-admin-template-free on GitHub"
            >Star</a
            >
        </li> --}}

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online">
                <img src="{{ asset('assets/gambar/user.png') }}" alt class="w-px-40 h-auto rounded-circle" />
            </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="#">
                <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('assets/gambar/user.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                    </div>
                    <div class="flex-grow-1">
                    <span class="fw-semibold d-block">{{ $user->name }}</span>
                    <small class="text-muted">{{ $user->role_old }}</small>
                    </div>
                </div>
                </a>
            </li>
            <li>
                <div class="dropdown-divider"></div>
            </li>
            <li>
                <a class="dropdown-item" href="#" id="navbar_edit_profile">
                <i class="bx bx-user me-2"></i>
                <span class="align-middle">Edit Profile</span>
                </a>
            </li>
            {{-- <li>
                <a class="dropdown-item" href="#">
                <i class="bx bx-cog me-2"></i>
                <span class="align-middle">Settings</span>
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#">
                <span class="d-flex align-items-center align-middle">
                    <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                    <span class="flex-grow-1 align-middle">Billing</span>
                    <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
                </span>
                </a>
            </li> --}}
            <li>
                <div class="dropdown-divider"></div>
            </li>
            <li>
                <a class="dropdown-item" href="{{route('actionlogout')}}">
                <i class="bx bx-power-off me-2"></i>
                <span class="align-middle">Log Out</span>
                </a>
            </li>
            </ul>
        </li>
        <!--/ User -->
        </ul>
    </div>
    </nav>
