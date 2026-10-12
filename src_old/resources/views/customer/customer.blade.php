<style>
    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        padding: 20px;
    }

    .card-customer-header {
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .card-customer-header h5 {
        color: #007bff;
        margin: 0;
    }

    /* Memperkecil Table
    table#mcustomer-table.dataTable thead th,
    table#mcustomer-table.dataTable tbody td {
        padding: 6px 10px;
        font-size: 13px;
    } */

    /* Untuk mobile */
    @media (max-width: 576px) {
        .card-customer-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-customer-header > div:last-child {
            align-self: flex-end;
        }
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .btn {
        border-radius: 6px;
        font-weight: 500;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    /* ── ### Modal shell ### ── */
    .modal-content {
      border: none;
      border-radius: 12px;
      overflow: hidden;
    }
    .modal-header {
      padding: 14px 20px;
      border-bottom: 1px solid #f0f0f0;
      background: #fff;
    }
    .modal-header .modal-title {
      font-size: 15px;
      font-weight: 600;
      color: #1a1a1a;
    }

    /* ── Tab nav strip ── */
    .cust-tabs {
      display: flex;
      gap: 0;
      border-bottom: 1px solid #ebebeb;
      background: #fafafa;
      overflow-x: auto;
      scrollbar-width: none;
    }
    .cust-tabs::-webkit-scrollbar { display: none; }
    .cust-tab-btn {
      flex-shrink: 0;
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      font-weight: 500;
      color: #888;
      background: none;
      border: none;
      border-bottom: 2px solid transparent;
      padding: 10px 16px;
      cursor: pointer;
      white-space: nowrap;
      transition: color .15s, border-color .15s;
    }
    .cust-tab-btn:hover { color: #333; }
    .cust-tab-btn.active {
      color: #1a1a1a;
      border-bottom-color: #1a1a1a;
      background: #fff;
    }
    .cust-tab-btn i { font-size: 14px; }

    /* ── Tab panels ── */
    .cust-panel { display: none; padding: 20px; }
    .cust-panel.active { display: block; }

    /* ── Section label ── */
    .section-label {
      font-size: 10px;
      font-weight: 600;
      letter-spacing: .07em;
      text-transform: uppercase;
      color: #aaa;
      border-bottom: 1px solid #f0f0f0;
      padding-bottom: 8px;
      margin-bottom: 14px;
    }

    /* ── Fields ── */
    .form-label {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .04em;
      color: #888;
      margin-bottom: 5px;
    }
    .form-label .hint {
      font-size: 10px;
      font-weight: 400;
      text-transform: none;
      letter-spacing: 0;
      color: #bbb;
    }
    .form-control, .form-select {
      font-size: 13px;
      padding: 7px 10px;
      border: 1px solid #ebebeb;
      border-radius: 8px;
      background: #f8f8f8;
      color: #1a1a1a;
      transition: border-color .15s, background .15s;
    }
    .form-control:focus, .form-select:focus {
      border-color: #888;
      background: #fff;
      box-shadow: none;
    }
    .form-control[readonly] {
      color: #bbb;
      font-style: italic;
    }
    textarea.form-control { resize: vertical; min-height: 62px; }

    /* ── Footer ── */
    .modal-footer {
      padding: 12px 20px;
      border-top: 1px solid #f0f0f0;
      background: #fafafa;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .step-dots {
      display: flex;
      gap: 5px;
      align-items: center;
    }
    .step-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #ddd;
      border: none;
      padding: 0;
      cursor: pointer;
      transition: background .15s, transform .15s;
    }
    .step-dot.active {
      background: #1a1a1a;
      transform: scale(1.2);
    }
    .btn-nav {
      font-size: 13px;
      font-weight: 500;
      padding: 7px 16px;
      border-radius: 8px;
      border: 1px solid #ddd;
      background: #fff;
      color: #333;
      cursor: pointer;
      transition: background .15s;
    }
    .btn-nav:hover { background: #f5f5f5; }
    .btn-save {
      font-size: 13px;
      font-weight: 500;
      padding: 7px 20px;
      border-radius: 8px;
      border: none;
      background: #1a1a1a;
      color: #fff;
      cursor: pointer;
      transition: opacity .15s;
    }
    .btn-save:hover { opacity: .85; }
    .btn-cancel {
      font-size: 13px;
      font-weight: 500;
      padding: 7px 16px;
      border-radius: 8px;
      border: 1px solid #ddd;
      background: transparent;
      color: #888;
      cursor: pointer;
    }
    .btn-cancel:hover { background: #f5f5f5; }

    /* ── Demo trigger button ── */
    .demo-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f5f5f5;
    }
    /* ── ### End Of Modal shell ### ── */
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-customer-header">
                    <div>
                        <h5>Data Customer</h5>
                    </div>
                    <button class="btn btn-primary" id="add-btn-customer">
                        <i class="fas fa-plus"></i> Tambah Customer
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm" id="mcustomer-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>Telepon</th>
                                    <th>Email</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan di-load oleh DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     MODAL CUSTOMER
═══════════════════════════════════════════ -->
<div class="modal fade" id="customer-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
     data-bs-keyboard="false">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <!-- Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="modal-title">Tambah Customer</h5>
        {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
        <button type="button" data-bs-dismiss="modal" aria-label="Close" style="background:rgba(255,255,255,0.2);border:none;border-radius:6px; width:32px;height:32px;color:#fff;font-size:18px;line-height:1; cursor:pointer;transition:background .2s" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;
        </button>
      </div>

      <!-- Tab Nav -->
      <div class="cust-tabs" id="custTabs">
        <button class="cust-tab-btn active" data-tab="0"><i class="bi bi-person"></i> Customer</button>
        <button class="cust-tab-btn" data-tab="1"><i class="bi bi-shop"></i> Purchasing</button>
        <button class="cust-tab-btn" data-tab="2"><i class="bi bi-receipt"></i> Data Pajak</button>
        <button class="cust-tab-btn" data-tab="3"><i class="bi bi-person-badge"></i> Info Pemilik</button>
        <button class="cust-tab-btn" data-tab="4"><i class="bi bi-people"></i> Kontak &amp; Accounting</button>
      </div>

      <!-- Form -->
      <form id="customer-form" novalidate>

        <!-- ── PANEL 0: Data Customer ── -->
        <div class="cust-panel active" id="panel-0">
          <div id="form-errors" class="alert alert-danger d-none"></div>
          <p class="section-label">Data Customer</p>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="kode" class="form-label">
                KODE <span class="text-danger">*</span>
                <span class="hint">(di-generate otomatis)</span>
              </label>
              <input type="text" class="form-control enter-next" id="kode" name="kode"
                     required data-next="nama" readonly placeholder="Auto-generate…">
            </div>
            <div class="col-md-6">
              <label for="nama" class="form-label">NAMA <span class="text-danger">*</span></label>
              <input type="text" class="form-control enter-next" id="nama" name="nama"
                     required data-next="jenis_customer" placeholder="Nama lengkap customer">
            </div>

            <div class="col-md-6">
              <label for="jenis_customer" class="form-label">JENIS USAHA</label>
              <input type="text" class="form-control enter-next" id="jenis_customer" name="jenis_customer"
                     data-next="telepon" placeholder="Retail, Distributor, dll.">
            </div>
            <div class="col-md-6">
              <label for="telepon" class="form-label">TELEPON</label>
              <input type="text" class="form-control enter-next" id="telepon" name="telepon"
                     data-next="alamat" placeholder="08xx-xxxx-xxxx">
            </div>

            <div class="col-12">
              <label for="alamat" class="form-label">ALAMAT</label>
              <textarea class="form-control enter-next" id="alamat" name="alamat"
                        rows="2" data-next="desa" placeholder="Jalan, nomor, RT/RW…"></textarea>
            </div>

            <div class="col-md-3">
              <label for="desa" class="form-label">DESA</label>
              <input type="text" class="form-control enter-next" id="desa" name="desa" data-next="kecamatan">
            </div>
            <div class="col-md-3">
              <label for="kecamatan" class="form-label">KECAMATAN</label>
              <input type="text" class="form-control enter-next" id="kecamatan" name="kecamatan" data-next="kabupaten">
            </div>
            <div class="col-md-3">
              <label for="kabupaten" class="form-label">KABUPATEN</label>
              <input type="text" class="form-control enter-next" id="kabupaten" name="kabupaten" data-next="kota">
            </div>
            <div class="col-md-3">
              <label for="kota" class="form-label">KOTA</label>
              <input type="text" class="form-control enter-next" id="kota" name="kota" data-next="fax">
            </div>

            <div class="col-md-4">
              <label for="fax" class="form-label">FAX</label>
              <input type="text" class="form-control enter-next" id="fax" name="fax" data-next="kontak">
            </div>
            <div class="col-md-4">
              <label for="kontak" class="form-label">KONTAK</label>
              <input type="text" class="form-control enter-next" id="kontak" name="kontak" data-next="email">
            </div>
            <div class="col-md-4">
              <label for="email" class="form-label">EMAIL</label>
              <input type="email" class="form-control enter-next" id="email" name="email" data-next="npwp">
            </div>

            <div class="col-md-6">
              <label for="npwp" class="form-label">NPWP</label>
              <input type="text" class="form-control enter-next" id="npwp" name="npwp" data-next="top_kredit">
            </div>
            <div class="col-md-6">
              <label for="top_kredit" class="form-label">TOP KREDIT</label>
              <input type="text" class="form-control enter-next" id="top_kredit" name="top_kredit" data-next="purchasing_nama">
            </div>
          </div>
        </div>

        <!-- ── PANEL 1: Purchasing ── -->
        <div class="cust-panel" id="panel-1">
          <p class="section-label">Purchasing</p>
          <div class="row g-3">
            <div class="col-md-4">
              <label for="purchasing_nama" class="form-label">NAMA</label>
              <input type="text" class="form-control enter-next" id="purchasing_nama"
                     name="purchasing_nama" data-next="purchasing_email">
            </div>
            <div class="col-md-4">
              <label for="purchasing_email" class="form-label">EMAIL</label>
              <input type="email" class="form-control enter-next" id="purchasing_email"
                     name="purchasing_email" data-next="purchasing_extensi_hp">
            </div>
            <div class="col-md-4">
              <label for="purchasing_extensi_hp" class="form-label">EXTENSI HP</label>
              <input type="text" class="form-control enter-next" id="purchasing_extensi_hp"
                     name="purchasing_extensi_hp" data-next="data_pajak_nama">
            </div>
          </div>
        </div>

        <!-- ── PANEL 2: Data Pajak ── -->
        <div class="cust-panel" id="panel-2">
          <p class="section-label">Data Pajak</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="data_pajak_nama" class="form-label">NAMA</label>
              <input type="text" class="form-control enter-next" id="data_pajak_nama"
                     name="data_pajak_nama" data-next="data_pajak_npwp">
            </div>
            <div class="col-md-6">
              <label for="data_pajak_npwp" class="form-label">NPWP</label>
              <input type="text" class="form-control enter-next" id="data_pajak_npwp"
                     name="data_pajak_npwp" data-next="data_pajak_alamat">
            </div>
            <div class="col-12">
              <label for="data_pajak_alamat" class="form-label">ALAMAT</label>
              <textarea class="form-control enter-next" id="data_pajak_alamat"
                        name="data_pajak_alamat" rows="2" data-next="data_pajak_alamat2"></textarea>
            </div>
            <div class="col-12">
              <label for="data_pajak_alamat2" class="form-label">ALAMAT 2</label>
              <textarea class="form-control enter-next" id="data_pajak_alamat2"
                        name="data_pajak_alamat2" rows="2" data-next="pemilik_nama"></textarea>
            </div>
          </div>
        </div>

        <!-- ── PANEL 3: Info Pemilik ── -->
        <div class="cust-panel" id="panel-3">
          <p class="section-label">Info Pemilik</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="pemilik_nama" class="form-label">NAMA PEMILIK</label>
              <input type="text" class="form-control enter-next" id="pemilik_nama"
                     name="pemilik_nama" data-next="pemilik_no_ktp_sim">
            </div>
            <div class="col-md-6">
              <label for="pemilik_no_ktp_sim" class="form-label">NO. KTP / SIM</label>
              <input type="text" class="form-control enter-next" id="pemilik_no_ktp_sim"
                     name="pemilik_no_ktp_sim" data-next="pemilik_tempat_lahir">
            </div>

            <div class="col-md-6">
              <label for="pemilik_tempat_lahir" class="form-label">TEMPAT LAHIR</label>
              <input type="text" class="form-control enter-next" id="pemilik_tempat_lahir"
                     name="pemilik_tempat_lahir" data-next="pemilik_tgl_lahir">
            </div>
            <div class="col-md-6">
              <label for="pemilik_tgl_lahir" class="form-label">TANGGAL LAHIR</label>
              <input type="date" class="form-control enter-next" id="pemilik_tgl_lahir"
                     name="pemilik_tgl_lahir" data-next="pemilik_alamat_rumah">
            </div>

            <div class="col-12">
              <label for="pemilik_alamat_rumah" class="form-label">ALAMAT RUMAH</label>
              <textarea class="form-control enter-next" id="pemilik_alamat_rumah"
                        name="pemilik_alamat_rumah" rows="2" data-next="pemilik_desa"></textarea>
            </div>

            <div class="col-md-3">
              <label for="pemilik_desa" class="form-label">DESA</label>
              <input type="text" class="form-control enter-next" id="pemilik_desa"
                     name="pemilik_desa" data-next="pemilik_kecamatan">
            </div>
            <div class="col-md-3">
              <label for="pemilik_kecamatan" class="form-label">KECAMATAN</label>
              <input type="text" class="form-control enter-next" id="pemilik_kecamatan"
                     name="pemilik_kecamatan" data-next="pemilik_kabupaten">
            </div>
            <div class="col-md-3">
              <label for="pemilik_kabupaten" class="form-label">KABUPATEN</label>
              <input type="text" class="form-control enter-next" id="pemilik_kabupaten"
                     name="pemilik_kabupaten" data-next="pemilik_telepon">
            </div>
            <div class="col-md-3">
              <label for="pemilik_telepon" class="form-label">TELEPON</label>
              <input type="text" class="form-control enter-next" id="pemilik_telepon"
                     name="pemilik_telepon" data-next="pemilik_fax">
            </div>

            <div class="col-md-4">
              <label for="pemilik_fax" class="form-label">FAX</label>
              <input type="text" class="form-control enter-next" id="pemilik_fax"
                     name="pemilik_fax" data-next="pemilik_email">
            </div>
            <div class="col-md-4">
              <label for="pemilik_email" class="form-label">EMAIL</label>
              <input type="email" class="form-control enter-next" id="pemilik_email"
                     name="pemilik_email" data-next="pemilik_npwp">
            </div>
            <div class="col-md-4">
              <label for="pemilik_npwp" class="form-label">NPWP</label>
              <input type="text" class="form-control enter-next" id="pemilik_npwp"
                     name="pemilik_npwp" data-next="pemilik_agama">
            </div>

            <div class="col-md-6">
              <label for="pemilik_agama" class="form-label">AGAMA</label>
              <input type="text" class="form-control enter-next" id="pemilik_agama"
                     name="pemilik_agama" data-next="kontak_lain_nama">
            </div>
          </div>
        </div>

        <!-- ── PANEL 4: Kontak & Accounting ── -->
        <div class="cust-panel" id="panel-4">
          <p class="section-label">Kontak Selain Pemilik</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="kontak_lain_nama" class="form-label">NAMA</label>
              <input type="text" class="form-control enter-next" id="kontak_lain_nama"
                     name="kontak_lain_nama" data-next="kontak_lain_telepon">
            </div>
            <div class="col-md-6">
              <label for="kontak_lain_telepon" class="form-label">TELEPON</label>
              <input type="text" class="form-control enter-next" id="kontak_lain_telepon"
                     name="kontak_lain_telepon" data-next="accounting_nama">
            </div>
          </div>

          <p class="section-label mt-4">Accounting</p>
          <div class="row g-3">
            <div class="col-md-4">
              <label for="accounting_nama" class="form-label">NAMA</label>
              <input type="text" class="form-control enter-next" id="accounting_nama"
                     name="accounting_nama" data-next="accounting_email">
            </div>
            <div class="col-md-4">
              <label for="accounting_email" class="form-label">EMAIL</label>
              <input type="email" class="form-control enter-next" id="accounting_email"
                     name="accounting_email" data-next="accounting_extensi_hp">
            </div>
            <div class="col-md-4">
              <label for="accounting_extensi_hp" class="form-label">EXTENSI HP</label>
              <input type="text" class="form-control" id="accounting_extensi_hp" name="accounting_extensi_hp">
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <!-- Step dots -->
          <div class="step-dots" id="stepDots"></div>

          <!-- Buttons -->
          <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn-nav" id="btnPrev" style="display:none">
              <i class="bi bi-arrow-left me-1"></i>Prev
            </button>
            <button type="button" class="btn-nav" id="btnNext">
              Next <i class="bi bi-arrow-right ms-1"></i>
            </button>
            <button type="submit" class="btn-save" id="save-btn" style="display:none">
              <i class="bi bi-check-lg me-1"></i>Simpan
            </button>
          </div>
        </div>

      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /#customer-modal -->

<!-- Modal View -->
<div class="modal fade" id="view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Customer</h5>
                {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
                <button type="button" data-bs-dismiss="modal" aria-label="Close" style="background:rgba(255,255,255,0.2);border:none;border-radius:6px; width:32px;height:32px;color:#fff;font-size:18px;line-height:1; cursor:pointer;transition:background .2s" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;
                </button>
            </div>
            <div class="modal-body" id="view-content">
                <!-- Content will be loaded by AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // =============================== Initialize DataTables ====================================
    var table = $('#mcustomer-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("customer_get_data") }}',
        scrollX: true,
        scrollY: "400px",
        scrollCollapse: true,
        responsive: true,
        autoWidth: true,
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'kode_cus', name: 'kode_cus' },
            { data: 'NAMACUST', name: 'NAMACUST' },
            { data: 'ALAMAT1', name: 'ALAMAT1' },
            { data: 'TELEPON', name: 'TELEPON' },
            { data: 'EMAIL', name: 'EMAIL' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    // ============================= End Of Initialize DataTables =================================
    // ===================================== Add button click  =======================================
    $('#add-btn-customer').click(function() {
        $('#customer-form')[0].reset();
        $('#modal-title').text('Tambah Customer');
        $('#customer-form').attr('data-method', 'store');
        $('#customer-modal').modal('show');
        $('#form-errors').addClass('d-none');
        // Focus ke field pertama saat modal dibuka
        $('#customer-modal').on('shown.bs.modal', function () {
            $('#nama').focus();
        });
        // panggil fungsi load kode customer
        load_kode_customer()
        // panggil fungsi next click
        initializeEnterNext();
    });

    function load_kode_customer() {
        $.ajax({
            url: '{{ route('customer_kode') }}', // Route to load the form
            type: 'GET',
            success: function(response) {
                $('#kode').val(response.kode);
            },
            error: function() {
                $('#kode').val('<p>Error loading form.</p>');
            }
        });
    }
    // ================================= End Of Add button click  =================================
    // ===================================== Edit button click =====================================
    $(document).on('click', '.edit-btn-customer', function() {
        var id = $(this).data('id');
        $('#modal-title').text('Edit Customer');
        $('#customer-form').attr('data-method', 'update').attr('data-id', id);
        $('#form-errors').addClass('d-none');

        initializeEnterNext();
        $('#kode').focus();

        $.ajax({
            url: '{{ route("customer_show", ["id" => ":id"]) }}'.replace(':id', id),
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    let customer = response.data;

                    $.each(customer, function(key, value) {
                        let el = $('#' + key);
                        if (el.length) {
                            el.val(value ?? '');
                        }
                    });

                    $('#customer-modal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Terjadi kesalahan saat memuat data', 'error');
            }
        });
    });
    // =============================== End Of Edit button click ===================================
    // =============================== Function Next Form click ===================================
    function initializeEnterNext() {
        $('.enter-next').off('keypress').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                var nextFieldId = $(this).data('next');
                if (nextFieldId && $('#' + nextFieldId).length) {
                    $('#' + nextFieldId).focus();
                }
            }
        });
    }
    // ============================ End Of Function Next Form click ================================
    // ================================= View button click ===================================
    $(document).on('click', '.view-btn-customer', function() {
        var id = $(this).data('id');

        $.ajax({
            url: '{{ route("customer_show", ["id" => ":id"]) }}'.replace(':id', id),
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    var customer = response.data;
                    var content = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>DATA CUSTOMER</h6>
                                <table class="table table-sm">
                                    <tr><th>Kode</th><td>${customer.kode || '-'}</td></tr>
                                    <tr><th>Nama</th><td>${customer.nama || '-'}</td></tr>
                                    <tr><th>Jenis Usaha</th><td>${customer.jenis_customer || '-'}</td></tr>
                                    <tr><th>Telepon</th><td>${customer.telepon || '-'}</td></tr>
                                    <tr><th>Email</th><td>${customer.email || '-'}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>INFO PEMILIK</h6>
                                <table class="table table-sm">
                                    <tr><th>Nama Pemilik</th><td>${customer.pemilik_nama || '-'}</td></tr>
                                    <tr><th>No. KTP/SIM</th><td>${customer.pemilik_no_ktp_sim || '-'}</td></tr>
                                    <tr><th>Email</th><td>${customer.pemilik_email || '-'}</td></tr>
                                </table>
                            </div>
                        </div>
                    `;
                    $('#view-content').html(content);
                    $('#view-modal').modal('show');
                }
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat memuat data');
            }
        });
    });
    // =============================== End Of View button click =================================
    // ===================================== Form submit ======================================
    $('#customer-form').submit(function(e) {
        e.preventDefault();
        $('#loading_modal').modal('show');
        var formData = $(this).serialize();
        var method = $(this).attr('data-method');
        var id = $(this).attr('data-id');

        var url, httpMethod;

        if (method === 'update') {
            url = '{{ route("customer_update", ["id" => ":id"]) }}'.replace(':id', id);
            httpMethod = 'POST'; // Karena menggunakan POST untuk update dengan method override
        } else {
            url = '{{ route("customer_store") }}';
            httpMethod = 'POST';
        }

        setTimeout(function () {
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    $('#loading_modal').modal('hide');
                    $('#customer-modal').modal('hide');
                    table.ajax.reload();
                    Swal.fire('Sukses!', response.success, 'success');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    $('#loading_modal').modal('hide');
                    var errors = xhr.responseJSON.errors;
                    var errorHtml = '<ul>';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value[0] + '</li>';
                    });
                    errorHtml += '</ul>';
                    $('#form-errors').html(errorHtml).removeClass('d-none');
                } else {
                    $('#loading_modal').modal('hide');
                    alert('mohon lenngkapi data dengan data yang sesuai !');
                    console.log('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Server error'));
                    // alert('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Server error'));
                }
            }
        });
        }, 500);
    });
    // ================================= End Of Form submit ===================================
    // ================================= Delete button click =================================
    $(document).on('click', '.delete-btn-customer', function() {
        var id = $(this).data('id');
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loading_modal').modal('show');
                setTimeout(function () {
                $.ajax({
                    url: '{{ route("customer_destroy", ["id" => ":id"]) }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: csrfToken
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#loading_modal').modal('hide');
                            table.ajax.reload();

                            Swal.fire({
                                title: 'Terhapus!',
                                text: response.message || 'Data berhasil dihapus',
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#loading_modal').modal('hide');
                        let errorMessage = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data';

                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                });
                }, 500);
            }
        });
});
    // ============================== End Of Delete button click =================================
    // ================================= Close modal handler =================================
    $('#customer-modal').on('hidden.bs.modal', function () {
        $('#form-errors').addClass('d-none');
    });
    // ============================== End of Close modal handler ==============================
    // ================================= modal customer handler ================================
    customerModal();
    // ============================= End of modal customer handler =============================
    // =============================== Enter Next cCustomer Input ==============================
    $(document).ready(function() {
        // Daftar semua input per panel
        var panelFields = {
            0: '#panel-0 .enter-next',
            1: '#panel-1 .enter-next',
            2: '#panel-2 .enter-next',
            3: '#panel-3 .enter-next',
            4: '#panel-4 .enter-next'
        };

        // Navigasi Enter
        $('.enter-next').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                var currentPanel = $(this).closest('.cust-panel');
                var panelId = currentPanel.attr('id');
                var panelIndex = parseInt(panelId.split('-')[1]);

                var fields = currentPanel.find('.enter-next:visible');
                var idx = fields.index($(this));

                // Cek apakah ada field berikutnya di panel yang sama
                if (idx < fields.length - 1) {
                    var nextField = fields.eq(idx + 1);
                    nextField.focus();
                    nextField.select();
                } else {
                    // Pindah ke panel berikutnya
                    var nextPanelIndex = panelIndex + 1;
                    if (nextPanelIndex <= 4) {
                        // Klik tab berikutnya
                        $('.cust-tab-btn[data-tab="' + nextPanelIndex + '"]').click();

                        // Fokus ke field pertama di panel baru
                        setTimeout(function() {
                            var newPanel = $('#panel-' + nextPanelIndex);
                            var firstField = newPanel.find('.enter-next:visible').first();
                            if (firstField.length) {
                                firstField.focus();
                                firstField.select();
                            }
                        }, 300);
                    } else {
                        // Jika sudah di panel terakhir, fokus ke tombol Simpan
                        $('#save-btn').focus();
                    }
                }
            }
        });

        // Enter untuk tombol
        $('#save-btn, .btn-cancel, #btnNext, #btnPrev').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).click();
            }
        });

        // Fokus pertama saat modal ditampilkan
        $('#customer-modal').on('shown.bs.modal', function() {
            setTimeout(function() {
                // $('#panel-0 .enter-next:visible').first().focus();
                $('#nama').focus();
            }, 300);
        });
    });
    // =========================== End Of Enter Next Customer Input =============================
});
    // ################################ HELPER #######################################
    // ******* Modal Customer *********************
    function customerModal() {
        const TOTAL = 5;
        let cur = 0;

        const panels   = () => document.querySelectorAll('.cust-panel');
        const tabBtns  = () => document.querySelectorAll('.cust-tab-btn');
        const dotsWrap = document.getElementById('stepDots');
        const btnPrev  = document.getElementById('btnPrev');
        const btnNext  = document.getElementById('btnNext');
        const btnSave  = document.getElementById('save-btn');

        function buildDots() {
            dotsWrap.innerHTML = '';
            for (let i = 0; i < TOTAL; i++) {
            const d = document.createElement('button');
            d.type = 'button';
            d.className = 'step-dot' + (i === cur ? ' active' : '');
            d.title = 'Tab ' + (i + 1);
            d.addEventListener('click', () => goTo(i));
            dotsWrap.appendChild(d);
            }
        }

        function goTo(n) {
            panels()[cur].classList.remove('active');
            tabBtns()[cur].classList.remove('active');
            cur = n;
            panels()[cur].classList.add('active');
            tabBtns()[cur].classList.add('active');

            btnPrev.style.display = cur === 0         ? 'none' : '';
            btnNext.style.display = cur === TOTAL - 1 ? 'none' : '';
            btnSave.style.display = cur === TOTAL - 1 ? ''     : 'none';
            buildDots();
        }

        btnNext.addEventListener('click', () => { if (cur < TOTAL - 1) goTo(cur + 1); });
        btnPrev.addEventListener('click', () => { if (cur > 0)         goTo(cur - 1); });

        document.querySelectorAll('.cust-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => goTo(+btn.dataset.tab));
        });

        document.getElementById('customer-modal').addEventListener('show.bs.modal', () => goTo(0));

        document.getElementById('customer-form').addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            const el = e.target;
            if (el.tagName === 'TEXTAREA') return;
            e.preventDefault();
            const nextId = el.dataset.next;
            if (nextId) {
            const next = document.getElementById(nextId);
            if (next) { next.focus(); return; }
            }
            if (cur < TOTAL - 1) goTo(cur + 1);
        });

        buildDots();
        }
</script>
{{-- <div class="modal fade" id="customer-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Tambah Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="customer-form">
                <div class="modal-body">
                    <div id="form-errors" class="alert alert-danger d-none"></div>

                    <!-- DATA CUSTOMER -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">DATA CUSTOMER</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kode" class="form-label">KODE <span class="text-danger">*</span><small class="text-muted">(Di-generate otomatis oleh sistem, mohon cek kembali !)</small></label>
                                    <input type="text" class="form-control enter-next" id="kode" name="kode" required data-next="nama" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">NAMA <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control enter-next" id="nama" name="nama" required data-next="jenis_customer">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="jenis_customer" class="form-label">JENIS USAHA</label>
                                    <input type="text" class="form-control enter-next" id="jenis_customer" name="jenis_customer" data-next="telepon">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telepon" class="form-label">TELEPON</label>
                                    <input type="text" class="form-control enter-next" id="telepon" name="telepon" data-next="alamat">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="alamat" class="form-label">ALAMAT</label>
                                    <textarea class="form-control enter-next" id="alamat" name="alamat" rows="2" data-next="desa"></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="desa" class="form-label">DESA</label>
                                    <input type="text" class="form-control enter-next" id="desa" name="desa" data-next="kecamatan">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="kecamatan" class="form-label">KECAMATAN</label>
                                    <input type="text" class="form-control enter-next" id="kecamatan" name="kecamatan" data-next="kabupaten">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="kabupaten" class="form-label">KABUPATEN</label>
                                    <input type="text" class="form-control enter-next" id="kabupaten" name="kabupaten" data-next="kota">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="kota" class="form-label">KOTA</label>
                                    <input type="text" class="form-control enter-next" id="kota" name="kota" data-next="fax">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="fax" class="form-label">FAX</label>
                                    <input type="text" class="form-control enter-next" id="fax" name="fax" data-next="kontak">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="kontak" class="form-label">KONTAK</label>
                                    <input type="text" class="form-control enter-next" id="kontak" name="kontak" data-next="email">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="email" class="form-label">EMAIL</label>
                                    <input type="email" class="form-control enter-next" id="email" name="email" data-next="npwp">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="npwp" class="form-label">NPWP</label>
                                    <input type="text" class="form-control enter-next" id="npwp" name="npwp" data-next="top_kredit">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="top_kredit" class="form-label">TOP KREDIT</label>
                                    <input type="text" class="form-control enter-next" id="top_kredit" name="top_kredit" data-next="purchasing_nama">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PURCHASING -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">PURCHASING</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="purchasing_nama" class="form-label">NAMA</label>
                                    <input type="text" class="form-control enter-next" id="purchasing_nama" name="purchasing_nama" data-next="purchasing_email">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="purchasing_email" class="form-label">EMAIL</label>
                                    <input type="email" class="form-control enter-next" id="purchasing_email" name="purchasing_email" data-next="purchasing_extensi_hp">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="purchasing_extensi_hp" class="form-label">EXTENSI HP</label>
                                    <input type="text" class="form-control enter-next" id="purchasing_extensi_hp" name="purchasing_extensi_hp" data-next="data_pajak_nama">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DATA PAJAK -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">DATA PAJAK</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="data_pajak_nama" class="form-label">NAMA</label>
                                    <input type="text" class="form-control enter-next" id="data_pajak_nama" name="data_pajak_nama" data-next="data_pajak_npwp">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="data_pajak_npwp" class="form-label">NPWP</label>
                                    <input type="text" class="form-control enter-next" id="data_pajak_npwp" name="data_pajak_npwp" data-next="data_pajak_alamat">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="data_pajak_alamat" class="form-label">ALAMAT</label>
                                    <textarea class="form-control enter-next" id="data_pajak_alamat" name="data_pajak_alamat" rows="2" data-next="data_pajak_alamat2"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="data_pajak_alamat2" class="form-label">ALAMAT 2</label>
                                    <textarea class="form-control enter-next" id="data_pajak_alamat2" name="data_pajak_alamat2" rows="2" data-next="pemilik_nama"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INFO PEMILIK -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">INFO PEMILIK</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pemilik_nama" class="form-label">NAMA PEMILIK</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_nama" name="pemilik_nama" data-next="pemilik_no_ktp_sim">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pemilik_no_ktp_sim" class="form-label">NO. KTP/SIM</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_no_ktp_sim" name="pemilik_no_ktp_sim" data-next="pemilik_tempat_lahir">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pemilik_tempat_lahir" class="form-label">TEMPAT LAHIR</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_tempat_lahir" name="pemilik_tempat_lahir" data-next="pemilik_tgl_lahir">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pemilik_tgl_lahir" class="form-label">TANGGAL LAHIR</label>
                                    <input type="date" class="form-control enter-next" id="pemilik_tgl_lahir" name="pemilik_tgl_lahir" data-next="pemilik_alamat_rumah">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="pemilik_alamat_rumah" class="form-label">ALAMAT RUMAH</label>
                                    <textarea class="form-control enter-next" id="pemilik_alamat_rumah" name="pemilik_alamat_rumah" rows="2" data-next="pemilik_desa"></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="pemilik_desa" class="form-label">DESA</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_desa" name="pemilik_desa" data-next="pemilik_kecamatan">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="pemilik_kecamatan" class="form-label">KECAMATAN</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_kecamatan" name="pemilik_kecamatan" data-next="pemilik_kabupaten">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="pemilik_kabupaten" class="form-label">KABUPATEN</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_kabupaten" name="pemilik_kabupaten" data-next="pemilik_telepon">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="pemilik_telepon" class="form-label">TELEPON</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_telepon" name="pemilik_telepon" data-next="pemilik_fax">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="pemilik_fax" class="form-label">FAX</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_fax" name="pemilik_fax" data-next="pemilik_email">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="pemilik_email" class="form-label">EMAIL</label>
                                    <input type="email" class="form-control enter-next" id="pemilik_email" name="pemilik_email" data-next="pemilik_npwp">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="pemilik_npwp" class="form-label">NPWP</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_npwp" name="pemilik_npwp" data-next="pemilik_agama">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pemilik_agama" class="form-label">AGAMA</label>
                                    <input type="text" class="form-control enter-next" id="pemilik_agama" name="pemilik_agama" data-next="kontak_lain_nama">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KONTAK SELAIN PEMILIK -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">KONTAK SELAIN PEMILIK</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kontak_lain_nama" class="form-label">NAMA</label>
                                    <input type="text" class="form-control enter-next" id="kontak_lain_nama" name="kontak_lain_nama" data-next="kontak_lain_telepon">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kontak_lain_telepon" class="form-label">TELEPON</label>
                                    <input type="text" class="form-control enter-next" id="kontak_lain_telepon" name="kontak_lain_telepon" data-next="accounting_nama">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ACCOUNTING -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">ACCOUNTING</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="accounting_nama" class="form-label">NAMA</label>
                                    <input type="text" class="form-control enter-next" id="accounting_nama" name="accounting_nama" data-next="accounting_email">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="accounting_email" class="form-label">EMAIL</label>
                                    <input type="email" class="form-control enter-next" id="accounting_email" name="accounting_email" data-next="accounting_extensi_hp">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="accounting_extensi_hp" class="form-label">EXTENSI HP</label>
                                    <input type="text" class="form-control" id="accounting_extensi_hp" name="accounting_extensi_hp">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="save-btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div> --}}
