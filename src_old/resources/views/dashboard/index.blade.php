<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Lintas Anugrah Expedisi Pro</title>

    <meta name="description" content="" />

    {{-- Laravel CSRF Tken --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    {{-- <link rel="icon" type="image/x-icon" href="{{ asset('dashboard/sneat/assets/img/favicon/favicon.ico') }}" /> --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('dashboard/sneat/assets/img/favicon/cargo.svg') }}?v=2" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('dashboard/sneat/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard/sneat/assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('dashboard/sneat/assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('dashboard/sneat/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <!-- Dode CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard/sneat/assets/dode/the.css') }}?v={{ filemtime(public_path('dashboard/sneat/assets/dode/the.css')) }}" />
    <!-- Modal CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard/sneat/assets/dode/modal.css') }}?v={{ filemtime(public_path('dashboard/sneat/assets/dode/modal.css')) }}" />
    <!-- Datatables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.dataTables.min.css">
    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Helpers -->
    <script src="{{ asset('dashboard/sneat/assets/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('dashboard/sneat/assets/js/config.js') }}"></script>
    <style>
        /* Button Scrol Atass */
        #btnTop {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 55px;
            height: 55px;
            border: none;
            border-radius: 50%;
            display: none;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #btnTop i{
            font-size: 28px;
            line-height: 1;
        }

        /* MEMPERKECIL UKURAN TABBLE DATATABLES GLOBBAL */
        /* Memperkecil Table */
        /* Global - berlaku untuk semua table DataTables */
        table.dataTable thead th,
        table.dataTable tbody td {
            padding: 6px 10px;
            font-size: 13px;
        }

        table.dataTable thead th {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
    </style>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar layout-without-menu">
      <div class="layout-container">

        <!-- Offcanvas Sidebar -->
        @include('dashboard.sidebar')

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
            @include('dashboard.navbar')
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
                @yield('content')
              {{-- <div class="row mb-4">
                <div class="col-12">
                  <div class="alert alert-primary">
                    <h5 class="alert-heading">Offcanvas Sidebar dengan Scroll & Sub-menu!</h5>
                    <p class="mb-0">Sekarang sidebar bisa discroll dan memiliki sub-menu yang bisa dikembangkan.</p>
                  </div>
                </div>
              </div>

              <!-- Cards Grid -->
              <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                  <div class="card">
                    <div class="card-body">
                      <h5 class="card-title">Total Users</h5>
                      <h3 class="text-primary">12,458</h3>
                      <span class="text-success">+18% from last month</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                  <div class="card">
                    <div class="card-body">
                      <h5 class="card-title">Revenue</h5>
                      <h3 class="text-success">$48,256</h3>
                      <span class="text-success">+12% from last month</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                  <div class="card">
                    <div class="card-body">
                      <h5 class="card-title">Orders</h5>
                      <h3 class="text-warning">1,428</h3>
                      <span class="text-danger">-2% from last month</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>User</th>
                          <th>Action</th>
                          <th>Date</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>John Doe</td>
                          <td>Created new order</td>
                          <td>2024-01-15</td>
                          <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                          <td>Jane Smith</td>
                          <td>Updated profile</td>
                          <td>2024-01-15</td>
                          <td><span class="badge bg-info">Pending</span></td>
                        </tr>
                        <tr>
                          <td>Mike Johnson</td>
                          <td>Processed payment</td>
                          <td>2024-01-14</td>
                          <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div> --}}
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                <div class="mb-2 mb-md-0">
                  Copyright © 2026; Powered By DWebPro "Your Reliable IT Solutions !"
                  {{-- <script>
                    document.write(new Date().getFullYear());
                  </script> --}}
                </div>
              </div>
            </footer>
            <!-- / Footer -->
            <button id="btnTop">
                <i class='bx bx-chevron-up'></i>
            </button>
            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>
    </div>
    <!-- / Layout wrapper -->
    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('dashboard/sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('dashboard/sneat/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('dashboard/sneat/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('dashboard/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('dashboard/sneat/assets/vendor/js/menu.js') }}"></script>
    <!-- endbuild -->
    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="{{ asset('dashboard/sneat/assets/js/main.js') }}"></script>

    <!-- Page JS -->

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <!-- Dode JS -->
     <script src="{{ asset('dashboard/sneat/assets/dode/the.js') }}?v={{ filemtime(public_path('dashboard/sneat/assets/dode/the.js')) }}"></script>

    {{-- Datatables JS --}}
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>

    {{-- Sweetalert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- CART JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Untuk Btn Scroll Atas
        $(window).scroll(function () {
            if ($(this).scrollTop() > 200) {
                $('#btnTop').fadeIn();
            } else {
                $('#btnTop').fadeOut();
            }
        });
        $('#btnTop').click(function () {
            $('html, body').animate({
                scrollTop: 0
            }, 250);
        });
    </script>

    @yield('footer')
  </body>
</html>
