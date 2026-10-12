<style>
    .card-expedisi {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 15px;
        padding: 20px;
        border: 1px solid #dee2e6;
    }

    .card-expedisi-header {
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .card-expedisi-header h5 {
        color: #007bff;
        margin: 0;
        font-weight: 600;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }

    .form-control-sm {
        font-size: 0.875rem;
    }

    .table-sm th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
    }

    .btn-action {
        font-size: 0.85rem;
        padding: 6px 12px;
        margin: 2px;
    }

    .status-deteksi {
        background-color: #ffc107;
        color: #856404;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        display: inline-block;
    }

    .driver-section {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 10px;
        background-color: #f8f9fa;
    }

    .driver-header {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .col-md-2, .col-md-3, .col-md-4 {
            margin-bottom: 10px;
        }
    }
    /* sweetalert show */
    .swal2-container {
        z-index: 2000 !important;
    }
</style>
<div class="container-fluid mt-3">
    <!-- Header Form -->
    <div class="card-expedisi">
        <div class="card-expedisi-header">
            <h5><i class='bx bx-truck me-2'></i>FORM EXPEDISI</h5>
        </div>

    {{-- HEADER DOKUMEN --}}
    <div class="card-expedisi">
  <div class="card-expedisi-header d-flex justify-content-between align-items-center">
    <h5><i class='bx bx-truck me-2'></i> FORM EXPEDISI</h5>
    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#panduanSuratJalanModalExp" aria-label="Buka panduan Surat Jalan">
        <i class="bx bx-help-circle me-1"></i>Panduan SJ
      </button>
      <span class="badge bg-success">READY</span>
    </div>
  </div>

  <div class="row g-2">
    <div class="col-md-4">
      <label class="form-label">TGL SJ</label>
      <input type="date" class="form-control form-control-sm" id="tgl_sj_expedisi" name="tgl_sj_expedisi">
    </div>

    <div class="col-md-4">
      <label class="form-label">NO SJ</label>
      <div class="input-group input-group-sm">
        <input type="text" class="form-control" id="no_muat_expedisi_flag" name="no_muat_expedisi_flag" hidden>
        <input type="text" class="form-control" id="no_sj_expedisi" name="no_sj_expedisi" readonly>
        <button class="btn btn-outline-secondary" id="no_sj_expedisi_btn"><i class="bx bx-search"></i></button>
      </div>
    </div>

    <div class="col-md-4">
      <label class="form-label">WILAYAH</label>
      <select class="form-select form-select-sm" id="wilayah_expedisi" name="wilayah_expedisi">
        <option value="denpasar">DENPASAR</option>
        <option value="gianyar">GIANYAR</option>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">CUSTOMER</label>
      <div class="input-group input-group-sm">
        <input type="hidden" id="customer_expedisi_id" name="customer_expedisi_id">
        <input type="text" class="form-control" id="customer_expedisi" name="customer_expedisi" readonly>
        <button class="btn btn-outline-primary" id="customer_expedisi_btn"><i class="bx bx-search"></i></button>
      </div>
    </div>

    <div class="col-md-4">
      <label class="form-label">KODE CUSTOMER</label>
      <input type="text" class="form-control form-control-sm" id="customer_kode_expedisi" name="customer_kode_expedisi" readonly>
    </div>

    <div class="col-md-4">
      <label class="form-label">ITEM</label>
      <div class="input-group input-group-sm">
        <input type="hidden" id="item_expedisi_id" name="item_expedisi_id">
        <input type="hidden" id="item_expedisi_tipe" name="item_expedisi_tipe">
        <input type="text" class="form-control" id="item_expedisi" name="item_expedisi" readonly>
        <button class="btn btn-outline-secondary" id="item_expedisi_btn"><i class="bx bx-search"></i></button>
      </div>
    </div>

    <hr class="my-2">

    <div class="col-md-4">
      <label class="form-label">KENDARAAN</label>
      <div class="input-group input-group-sm">
        <input type="hidden" id="kendaraan_expedisi_id" name="kendaraan_expedisi_id">
        <input type="text" class="form-control" id="kendaraan_expedisi" name="kendaraan_expedisi" readonly>
        <button class="btn btn-outline-secondary" id="kendaraan_expedisi_btn" data-id="1"><i class="bx bx-search"></i></button>
      </div>
    </div>

    <div class="col-md-4">
      <label class="form-label">DRIVER I</label>
      <div class="input-group input-group-sm">
        <input type="hidden" id="driver_1_expedisi_id" name="driver_1_expedisi_id">
        <input type="text" class="form-control" id="driver_1_expedisi" name="driver_1_expedisi" readonly>
        <button class="btn btn-outline-secondary" id="driver_1_expedisi_btn" data-id="1"><i class="bx bx-search"></i></button>
      </div>
    </div>

    <div class="col-md-4">
      <label class="form-label">DRIVER II</label>
      <div class="input-group input-group-sm">
        <input type="hidden" id="driver_2_expedisi_id" name="driver_2_expedisi_id">
        <input type="text" class="form-control" id="driver_2_expedisi" name="driver_2_expedisi" readonly>
        <button class="btn btn-outline-secondary" id="driver_2_expedisi_btn" data-id="2"><i class="bx bx-search"></i></button>
      </div>
    </div>

    <div class="col-md-4">
      <label class="form-label">PENERIMA</label>
      <input type="text" class="form-control form-control-sm" id="penerima_expedisi" name="penerima_expedisi">
    </div>

    <div class="col-md-4">
      <label class="form-label">NAMA</label>
      <input type="text" class="form-control form-control-sm" id="nama_penerima_expedisi" name="nama_penerima_expedisi">
    </div>

    <div class="col-md-4">
      <label class="form-label">PHONE</label>
      <input type="text" class="form-control form-control-sm" id="phone_penerima_expedisi" name="phone_penerima_expedisi">
    </div>

    <div class="col-md-12">
      <label class="form-label">ALAMAT</label>
      <textarea class="form-control form-control-sm" rows="2" id="alamat_penerima_expedisi" name="alamat_penerima_expedisi"></textarea>
    </div>

    <hr class="my-2">

    <div class="col-md-4">
      <label class="form-label">BARANG</label>
      <input type="text" class="form-control form-control-sm" id="barang_expedisi" name="barang_expedisi">
    </div>

    <div class="col-md-4">
      <label class="form-label">PENYIMPANAN</label>
      <select class="form-select form-select-sm" id="penyimpanan_expedisi" name="penyimpanan_expedisi">
        <option value="F">F</option>
        <option value="C">C</option>
        <option value="D">D</option>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">KOLI</label>
      <input type="number" class="form-control form-control-sm" id="koli_expedisi" name="koli_expedisi">
    </div>

    <div class="col-md-12">
      <label class="form-label">CATATAN</label>
      <textarea class="form-control form-control-sm" rows="2" id="catatan_expedisi" name="catatan_expedisi"></textarea>
    </div>

    <hr class="my-2">

    <div class="col-md-3">
      <label class="form-label">RUTE</label>
      <input type="text" class="form-control form-control-sm" id="rute_expedisi" name="rute_expedisi">
    </div>

    <div class="col-md-2">
      <label class="form-label">JUMLAH</label>
      <input type="number" class="form-control form-control-sm" id="jumlah_expedisi" name="jumlah_expedisi">
    </div>

    <div class="col-md-2">
      <label class="form-label">HARGA</label>
      <input type="text" class="form-control form-control-sm" id="harga_expedisi" name="harga_expedisi">
    </div>

    <div class="col-md-2">
      <label class="form-label">DISC</label>
      <input type="number" class="form-control form-control-sm" id="disc_expedisi" name="disc_expedisi">
    </div>

    <div class="col-md-3">
      <label class="form-label">DEL CHARGE</label>
      <div class="input-group input-group-sm">
        <input type="number" class="form-control" id="del_charge_expedisi" name="del_charge_expedisi">
        <button class="btn btn-warning" id="auto_dc_expedisi">Auto DC</button>
      </div>
    </div>

    <div class="col-md-3">
      <label class="form-label">SUB TOTAL</label>
      <input type="text" class="form-control form-control-sm" id="sub_total_expedisi" readonly>
    </div>

    <div class="col-md-3">
      <label class="form-label">DPP</label>
      <input type="text" class="form-control form-control-sm" id="dpp_expedisi" readonly>
    </div>

    <div class="col-md-3">
      <label class="form-label">PPN</label>
      <input type="text" class="form-control form-control-sm" id="ppn_expedisi" readonly>
    </div>

    <div class="col-md-3">
      <label class="form-label fw-bold text-success">GRAND TOTAL</label>
      <input type="text" class="form-control form-control-sm fw-bold" id="grand_total_expedisi" readonly>
    </div>

    <div class="row g-2">
      <div class="col-md-3 col-sm-6">
        <button class="btn btn-info btn-action w-100" id="buttonSimpanExp">
          <i class='bx bx-save me-1'></i>SIMPAN
        </button>
      </div>

      <div class="col-md-3 col-sm-6">
        <button class="btn btn-danger btn-action w-100" id="buttonClearExp">
          <i class='bx bx-trash me-1'></i>CLEAR
        </button>
      </div>

      @role('admin')
      <div class="col-md-3 col-sm-6" style="display:none" id="divButtonAppendExp">
        <button class="btn btn-warning btn-action w-100" id="buttonAppendExp">
          <i class='bx bx-plus-circle me-1'></i>TAMBAH MUATAN
        </button>
      </div>

      <div class="col-md-3 col-sm-6 d-none" id="divPrintSuratJalan">
        <button class="btn btn-primary btn-action w-100" id="btnPrintSuratJalan" data-id="">
          <i class="bx bx-printer me-1"></i>PRINT SJ
        </button>
      </div>
      @endrole
    </div>
  </div>
</div>

    <hr class="my-3">
    @role('admin')
    <!-- ############# DATA MUAT ############### -->
    <div class="card-expedisi tabel-surat-jalan">
        <div class="card-expedisi-header d-flex justify-content-between align-items-center">
            <h5><i class="bx bx-package me-2"></i>FORM NO MUAT</h5>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#panduanNoMuatModalExp" aria-label="Buka panduan No Muat">
                <i class="bx bx-help-circle me-1"></i>Panduan No Muat
            </button>
        </div>
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                {{-- BARIS 1 --}}
                <div class="row align-items-end">
                    <div class="col-md-2 mb-2">
                        <label class="form-label mb-1">TGL MUAT</label>
                        <input type="date" class="form-control form-control-sm" id="tgl_muat_expedisi" name="tgl_muat_expedisi">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">NO MUAT</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="no_muat_expedisi" name="no_muat_expedisi" readonly placeholder="Auto Generate / Click Search">
                            <button class="btn btn-outline-secondary" type="button" id="muat_expedisi_btn">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">RUTE</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="rute_muat_expedisi" name="rute_muat_expedisi">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#ruteModalExp"><i class="bx bx-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">KENDARAAN</label>
                        <div class="input-group input-group-sm">
                            <input type="hidden" id="kendaraan_kode_muat_expedisi" name="kendaraan_kode_muat_expedisi">
                            <input type="text" class="form-control" id="kendaraan_muat_expedisi" name="kendaraan_muat_expedisi">
                            <button class="btn btn-outline-secondary" type="button" id="kendaraan_muat_expedisi_btn" data-id="2">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                    {{-- <div class="col-md-2 mb-2">
                        <label class="form-label mb-1">AC</label>
                        <select class="form-select form-select-sm" id="ac_kendaraan_muat_expedisi" name="ac_kendaraan_muat_expedisi">
                            <option value="">Pilih</option>
                            <option value="1">YA</option>
                            <option value="0">TIDAK</option>
                        </select>
                    </div> --}}
                </div>


                {{-- BARIS 2 --}}
                {{-- BARIS 2 --}}
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">PLAT</label>
                        <input type="text" class="form-control form-control-sm" id="plat_nomor_muat_expedisi" name="plat_nomor_muat_expedisi" readonly>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">DRIVER 1</label>
                        <div class="input-group input-group-sm">
                            <input type="hidden" id="driver1_kode_muat_expedisi" name="driver1_kode_muat_expedisi">
                            <input type="text" class="form-control" id="driver1_muat_expedisi" name="driver1_muat_expedisi">
                            <button class="btn btn-outline-secondary" type="button" id="driver1_muat_expedisi_btn" data-id="3">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">DRIVER 2</label>
                        <div class="input-group input-group-sm">
                            <input type="hidden" id="driver2_kode_muat_expedisi" name="driver2_kode_muat_expedisi">
                            <input type="text" class="form-control" id="driver2_muat_expedisi" name="driver2_muat_expedisi">
                            <button class="btn btn-outline-secondary" type="button" id="driver2_muat_expedisi_btn" data-id="4">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">KM AWAL</label>
                        <input type="number" step="0.1" class="form-control form-control-sm" id="km_awal_muat_expedisi" name="km_awal_muat_expedisi">
                    </div>
                    {{-- <div class="col-md-1 mb-2">
                        <label class="form-label mb-1">AC</label>
                        <select class="form-select form-select-sm" id="ac_kendaraan_muat_expedisi" name="ac_kendaraan_muat_expedisi">
                            <option value="">-</option>
                            <option value="1">Y</option>
                            <option value="0">T</option>
                        </select>
                    </div> --}}
                </div>


                {{-- BARIS 3 --}}
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">KM AKHIR</label>
                        <input type="number" step="0.1" class="form-control form-control-sm" id="km_akhir_muat_expedisi" name="km_akhir_muat_expedisi">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">UANG JALAN</label>
                        <input type="text" class="form-control form-control-sm rupiah text-end" id="uang_jalan_muat_expedisi" name="uang_jalan_muat_expedisi">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">UANG DRIVER + MAKAN</label>
                        <input type="text" class="form-control form-control-sm rupiah text-end" id="uang_driver_makan_muat_expedisi" name="uang_driver_makan_muat_expedisi">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">UANG LAIN-LAIN</label>
                        <input type="text" class="form-control form-control-sm rupiah text-end" id="uang_lain_lain_muat_expedisi" name="uang_lain_lain_muat_expedisi">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-expedisi-header mt-3">
            <h5><i class='bx bx-table me-2'></i>DATA SURAT JALAN</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover" id="tableProsesExpedisi">
                <thead>
                    <tr>
                        <th width="30">NO</th>
                        <th width="120">NO.SJ</th>
                        <th width="100">TGL.SJ</th>
                        <th width="100">DELCHARGE</th>
                        <th width="80">JUMLAH</th>
                        <th width="80">UNIT</th>
                        <th width="80">JENIS</th>
                        <th width="100">HARGA</th>
                        <th width="100">DISC</th>
                        <th width="100">PPN</th>
                        <th width="100">TOTAL</th>
                        <th width="100">PENGIRIM</th>
                        <th width="80">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="10" class="text-end">GRAND TOTAL</th>
                        <th class="text-end" id="grandTotal">0</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="col-md-6 col-sm-6 d-flex gap-2">
            <button class="btn btn-primary btn-action flex-fill w-100" id="simpanMuatExpBtn" style="display:none">
                <i class='bx bx-plus-circle me-1'></i>Simpan No Muat
            </button>
            <button class="btn btn-info btn-action flex-fill w-100" id="tampilkanPdfMuatExpBtn" style="display:none">
                <i class='bx bx-file me-1'></i>Tampilkan PDF
            </button>
            <button class="btn btn-danger btn-action flex-fill w-100" id="clearMuatExpBtn" style="display:none">
                <i class='bx bx-trash me-1'></i>Clear No Muat
            </button>
        </div>
    </div>
    @endrole

</div>

{{-- MODAL --}}
@include('expedisi.expedisi_modal')
<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    resetFormExpedisi();
    // ================================= driver Login =====================================
    const userRole = "{{ auth()->user()->role_old }}";
    const userId = "{{ auth()->user()->user_id }}";
    // console.log(userId);
    if (userRole === 'driver') {
        const driverDataUrlTemplate = "{{ route('driver-det.data', ['user_id' => ':user_id']) }}";
        function getMyKodeDriver(userId) {
            const url = driverDataUrlTemplate.replace(':user_id', userId);
            return $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json'
            });
        }
        getMyKodeDriver(userId)
            .done(function (res) {
                if (res.success) {
                    $('#driver_1_expedisi_id').val(res.kode);
                    $('#driver_1_expedisi').val(res.nama);
                    $('#driver_1_expedisi_btn')
                    .prop('disabled', true)
                    .addClass('disabled');

                    $('#jumlah_expedisi').prop('readonly', true);
                    $('#harga_expedisi').prop('readonly', true);
                    $('#disc_expedisi').prop('readonly', true);
                    $('#del_charge_expedisi').prop('readonly', true);
                    $('#auto_dc_expedisi').prop('disabled', true).addClass('disabled');
                    $('#jumlah_expedisi').val(1);
                    $('.tabel-surat-jalan').hide();
                }
            })
            .fail(function (xhr) {
                console.error(xhr.responseText);
                alert('Data driver tidak ditemukan');
            });
    }
    // ============================ End Of driver Login ===================================
    // ================================= Pilih No Surat Jalan =====================================
    $('#no_sj_expedisi_btn').click(function(e) {
        e.preventDefault();
        $('#surjalModalExp').modal('show');
        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalSurjalExpTable')) {
            $('#modalSurjalExpTable').DataTable().destroy();
        }
        var tableSurjal = $('#modalSurjalExpTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
            url: "{{ route('expedisi-surjal.data') }}",
                data: function(d) {
                    d.tgl_mulai = $('#filter_tgl_mulai').val();
                    d.tgl_akhir = $('#filter_tgl_akhir').val();
                    d.search_muat = $('#filter_surjal').val();
                }
            },
            // Scroll settings
            scrollX: true,
            scrollY: "400px",
            scrollCollapse: true,
            // Responsive settings
            responsive: true,
            autoWidth: true,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'NOSJ', name: 'NOSJ' },
                { data: 'tglsj', name: 'tglsj' },
                { data: 'NOMUAT', name: 'NOMUAT' },
                { data: 'CUSTOMER', name: 'CUSTOMER' },
                { data: 'rute', name: 'rute' },
                { data: 'JUMLAH', name: 'JUMLAH' },
                { data: 'harga_formatted', name: 'HARGA' },
                { data: 'DISC', name: 'DISC' },
                { data: 'dc_formatted', name: 'DC' },
                { data: 'total_formatted', name: 'GRAND' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#btn_filter_surjal').click(function () {
            tableSurjal.ajax.reload();
        });
    });
    // ============================= End Of Pilih No Surat Jalan =====================================
    // ================================ Delete No Surat Jalan ======================================
    $(document).on('click', '.deleteSurjal', function () {
        let id     = $(this).data('id');
        let nomuat = $(this).data('nomuat');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'No Muat ' + nomuat + ' akan dihapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                let url = "{{ route('expedisi.destroy', ':id') }}";
                url = url.replace(':id', id);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // reload datatable
                        $('#modalSurjalExpTable').DataTable().ajax.reload(null, false);
                    },
                    error: function () {
                        Swal.fire(
                            'Gagal!',
                            'Data tidak bisa dihapus',
                            'error'
                        );
                    }
                });
            }
        });
    });
    // ============================ End Of Delete No Surat Jalan ====================================
    // ============================= Show Detail Surjal ================================
    // Event listener untuk tombol pickSurjal
    $(document).on('click', '.pickSurjal', function() {
        const id = $(this).data('id');
        const nomuat = $(this).data('nosj');

        $('#surjalModalExp').modal('hide');
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            // Ambil data dari server berdasarkan id/nomuat
            $.ajax({
                url: '{{ route('expedisi-surjal.show') }}', // Ganti dengan endpoint yang sesuai
                type: 'GET',
                data: {
                    id: id,
                    nomuat: nomuat
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        setButtonToUpdateMode();
                         // DATA DOKUMEN
                        $('#no_muat_expedisi_flag').val(data.no_muat || '');
                        $('#wilayah_expedisi').val(data.wilayah || 'DENPASAR');

                        // Customer
                        $('#customer_expedisi_id').val(data.customer_id || '');
                        $('#customer_expedisi').val(data.customer_name || '');
                        $('#customer_kode_expedisi').val(data.customer || '');

                        // Item
                        $('#item_expedisi_tipe').val(data.jenis_item || '');
                        $('#item_expedisi').val(data.pesanan || '');

                        // KENDARAAN & DRIVER
                        $('#kendaraan_expedisi_id').val(data.kendaraan_id || '');
                        $('#kendaraan_expedisi').val(data.kendaraan_nama || '');
                        $('#tgl_sj_expedisi').val(data.tgl_sj || '');
                        $('#no_sj_expedisi').val(data.no_sj || '');

                        // Driver 1
                        $('#driver_1_expedisi_id').val(data.driver_1_id || '');
                        $('#driver_1_expedisi').val(data.driver_1_nama || '');

                        // Driver 2
                        $('#driver_2_expedisi_id').val(data.driver_2_id || '');
                        $('#driver_2_expedisi').val(data.driver_2_nama || '');

                        // DATA PENERIMA
                        $('#penerima_expedisi').val(data.penerima || '');
                        $('#nama_penerima_expedisi').val(data.nama_penerima || '');
                        $('#phone_penerima_expedisi').val(data.phone_penerima || '');
                        $('#alamat_penerima_expedisi').val(data.alamat_penerima || '');

                        // DATA BARANG
                        $('#barang_expedisi').val(data.barang || '');
                        $('#penyimpanan_expedisi').val(data.penyimpanan || '');
                        $('#koli_expedisi').val(data.koli || '');
                        $('#catatan_expedisi').val(data.catatan || '');

                        // DETAIL & PERHITUNGAN
                        $('#rute_expedisi').val(data.rute || '');
                        $('#jumlah_expedisi').val(parseFloat(data.jumlah) || 0);
                        $('#harga_expedisi').val(data.harga || 0);
                        $('#disc_expedisi').val(parseFloat(data.disc_percent) || 0);
                        $('#del_charge_expedisi').val(data.del_charge || 0);

                        // Button Print PDF
                        $('#divPrintSuratJalan').removeClass('d-none');
                        $('#btnPrintSuratJalan').attr('data-id',id);

                        // Hitung total otomatis
                        calculateTotal();

                        // // Tampilkan pesan sukses
                        // Swal.fire({
                        //     icon: 'success',
                        //     title: 'Data Dimuat',
                        //     text: 'Data muatan berhasil dimuat ke form',
                        //     timer: 2000,
                        //     showConfirmButton: false
                        // });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Gagal memuat data'
                        });
                    }
                },
                error: function(xhr) {
                    $('#loading_modal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat mengambil data'
                    });
                },
                complete: function() {
                    $('#loading_modal').modal('hide');
                }
            });
        });
    });
    // ======================== End Of Show Detail Surjal =============================
    // ================================= Pilih Customer =====================================
    $('#customer_expedisi_btn').click(function(e) {
        e.preventDefault();
        $('#customerModalExp').modal('show');
        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalCusExpTable')) {
            $('#modalCusExpTable').DataTable().destroy();
        }
        var table = $('#modalCusExpTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("expedisi-cus.data") }}',
            // Use one horizontal scroller; the paginated table does not need an inner vertical scroller.
            scrollX: true,
            pageLength: 10,
            autoWidth: false,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '56px' },
                { data: 'kode_cus', name: 'kode_cus', width: '135px' },
                { data: 'NAMACUST', name: 'NAMACUST', width: '250px' },
                { data: 'TYPECUST', name: 'TYPECUST', width: '150px' },
                { data: 'TELEPON', name: 'TELEPON', width: '150px' },
                { data: 'EMAIL', name: 'EMAIL', width: '220px' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '68px' }
            ]
        });

        $('#customerModalExp').one('shown.bs.modal', function() {
            table.columns.adjust();
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // ### Select Button
    $(document).on('click', '.view-btn-customer-expedisi', function(e) {
        e.preventDefault();
        var kodeCus = $(this).data('id');
        var namaCus = $(this).data('name');
        var CUSTOMER = $(this).data('customer');
        // Mengisi nilai ke elemen yang dituju
        $('#customer_expedisi_id').val(CUSTOMER);
        $('#customer_expedisi').val(namaCus);
        $('#customer_kode_expedisi').val(CUSTOMER);
        // Kosongkan dulu item
        $('#item_expedisi_id').val('');
        $('#item_expedisi_item').val('');
        $('#item_expedisi').val('');

        // Tutup modal
        $('#customerModalExp').modal('hide');
    });
    // ============================== End Of Pilih Customer ==================================
    // =================================== Pilih Item =====================================
    $(document).on('click', '#item_expedisi_btn', function(e) {
        var expedisiId = $('#customer_expedisi_id').val();

        if (!expedisiId || expedisiId.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Silahkan Pilih Customer!',
                confirmButtonColor: '#3085d6'
            });
            e.preventDefault();
            return false;
        }

        $('#itemModalExp').modal('show');

        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalItemExpTable')) {
            $('#modalItemExpTable').DataTable().destroy();
        }

        // rebuild datatable
        $('#modalItemExpTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('price-customer-modal.price', ':kode') }}".replace(':kode', expedisiId),
                dataSrc: function (json) {
                    // SET INFO CUSTOMER DI ATAS TABEL
                    $("#custNameExp").text(json.customer_nama);
                    $("#custKodeExp").text(json.customer_kode);
                    return json.data;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'KETERANGAN' },
                { data: 'DARI' },
                { data: 'SAMPAI' },
                { data: 'nama_rute', name: 'pc.RUTE' },
                { data: 'harga_html', orderable: false, searchable: false },
                { data: 'jenis_text' },
                { data: 'aksi', orderable: false, searchable: false }
            ]
        });
    });
    // ### Select Button
    $(document).on('click', '.pick-price-exp', function(e) {
        e.preventDefault();
        var kodeCus = $(this).data('id');
        var kodeJenis = $(this).data('jenis');
        // Ambil KETERANGAN dari kolom di baris yang sama
        var row = $(this).closest('tr');
        var keterangan = row.find('td:eq(1)').text();
        var rute = row.find('td:eq(4)').text();
        var harga = row.find('td:eq(5)').text().trim();

        // Mengisi nilai ke elemen yang dituju
        $('#jumlah_expedisi').val(1);
        $('#item_expedisi_id').val(kodeCus);
        $('#item_expedisi_tipe').val(kodeJenis);
        $('#item_expedisi').val(keterangan);
        $('#rute_expedisi').val(rute);
        $('#harga_expedisi').val(harga);
        calculateTotal();
        // Tutup modal
        $('#itemModalExp').modal('hide');
    });
    // =============================== End Of Pilih Item ==================================
    // =================================== Pilih Kendaraan =====================================
    $(document).on('click', '#kendaraan_expedisi_btn, #kendaraan_muat_expedisi_btn', function(e) {
        e.preventDefault();
        var kodeCus = $(this).data('id');
        // Simpan kodeKen ke modal sebagai data atribut
        $('#kendaraanModalExp').data('kode-cus', kodeCus);
        $('#kendaraanModalExp').modal('show');

        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalKendaraanExpTable')) {
            $('#modalKendaraanExpTable').DataTable().destroy();
        }

        // rebuild datatable
        $('#modalKendaraanExpTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('kendaraan.datamodel') }}",
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'KODE', name: 'KODE'},
                {data: 'NAMA', name: 'NAMA'},
                {data: 'PLAT', name: 'PLAT'},
                {data: 'JENIS', name: 'JENIS'},
                {data: 'FNO_PRK_B', name: 'FNO_PRK_B'},
                {data: 'FNO_PRK_P', name: 'FNO_PRK_P'},
                {data: 'FNO_PRK_S', name: 'FNO_PRK_S'},
                {data: 'FNO_PRK_O', name: 'FNO_PRK_O'},
                {data: 'FNO_PRK_M', name: 'FNO_PRK_M'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
    // ### Select Button
    $(document).on('click', '.pickKendaraanModel', function(e) {
        e.preventDefault();
        var kodeCus = $('#kendaraanModalExp').data('kode-cus');
        var kodeKendaraan = $(this).data('id');
        // Ambil KETERANGAN dari kolom di baris yang sama
        var row = $(this).closest('tr');
        var keterangan = row.find('td:eq(1)').text();
        var nama = row.find('td:eq(2)').text();
        var plat = row.find('td:eq(3)').text();

        // Cek kodeKen untuk menentukan field mana yang diisi
        if (kodeCus == 1) {
            $('#kendaraan_expedisi_id').val(keterangan);
            $('#kendaraan_expedisi').val(nama);
        } else if (kodeCus == 2) {
            $('#kendaraan_kode_muat_expedisi').val(keterangan);
            $('#kendaraan_muat_expedisi').val(nama);
            $('#plat_nomor_muat_expedisi').val(plat);
            $.ajax({
                url: "{{ route('expedisi.dataKm') }}",
                type: 'GET',
                data: {
                    kendaraan: keterangan
                },
                success: function(res) {
                    $('#km_awal_muat_expedisi').val(Math.round(Number(res.km_akhir)));
                }
            });
        }

        // Tutup modal
        $('#kendaraanModalExp').modal('hide');
    });
    // =============================== End Of Pilih Kendaraan ==================================
    // =================================== Pilih Driver =====================================
    $(document).on('click', '#driver_1_expedisi_btn, #driver_2_expedisi_btn, #driver1_muat_expedisi_btn, #driver2_muat_expedisi_btn', function(e) {
        e.preventDefault();
        var kodeKen = $(this).data('id');
        // Simpan kodeKen ke modal sebagai data atribut
        $('#driverModalExp').data('kode-ken', kodeKen);
        $('#driverModalExp').modal('show');

        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalDriverExpTable')) {
            $('#modalDriverExpTable').DataTable().destroy();
        }

        // rebuild datatable
        $('#modalDriverExpTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('driver-modal.data') }}",
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'KODE', name: 'KODE'},
                {data: 'NAMA', name: 'NAMA'},
                {data: 'ALAMAT', name: 'ALAMAT',
                    render: function(data) {
                        return data && data.length > 30 ? data.substr(0, 30) + '...' : data;
                    }
                },
                {data: 'PHONE', name: 'PHONE'},
                {data: 'MULAI', name: 'MULAI',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString('id-ID') : '-';
                    }
                },
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
        });
    });
    // ### Select Button
    $(document).on('click', '.pickDriverModal', function(e) {
        e.preventDefault();
        // Simpan kodeKen ke modal sebagai data atribut
        var kodeKen = $('#driverModalExp').data('kode-ken');
        var kodeDriver = $(this).data('id');
        // Ambil KETERANGAN dari kolom di baris yang sama
        var row = $(this).closest('tr');
        var nama = row.find('td:eq(2)').text();

        // Cek kodeKen untuk menentukan field mana yang diisi
        if (kodeKen == 1) {
            $('#driver_1_expedisi_id').val(kodeDriver);
            $('#driver_1_expedisi').val(nama);
            $('#driverModalExp').modal('hide');
        } else if (kodeKen == 2) {
            $('#driver_2_expedisi_id').val(kodeDriver);
            $('#driver_2_expedisi').val(nama);
            $('#driverModalExp').modal('hide');
            $('#penerima_expedisi').focus();
        } else if (kodeKen == 3) {
            $('#driver1_kode_muat_expedisi').val(kodeDriver);
            $('#driver1_muat_expedisi').val(nama);
            $('#driverModalExp').modal('hide');
        } else if (kodeKen == 4) {
            $('#driver2_kode_muat_expedisi').val(kodeDriver);
            $('#driver2_muat_expedisi').val(nama);
            $('#driverModalExp').modal('hide');
            $('#km_akhir_muat_expedisi').focus();
        }
    });
    // =============================== End Of Pilih Driver ==================================
    // ============================ Submit Data Form Surjal =================================
    // Hitung otomatis
    $('#jumlah_expedisi, #harga_expedisi, #disc_expedisi, #del_charge_expedisi').on('input', function() {
        calculateTotal();
    });

    // Auto Del Charge
    $('#auto_dc_expedisi').click(function(e) {
        e.preventDefault();
        const jumlah = parseNumber($('#jumlah_expedisi').val()) || 0;
        const harga = parseNumber($('#harga_expedisi').val()) || 0;
        const autoDC = (jumlah * harga) * 0.05; // 5% dari total
        $('#del_charge_expedisi').val(Math.round(autoDC));
        calculateTotal();
    });

    // Tombol Simpan
    $('#buttonSimpanExp').click(function(e) {
        e.preventDefault();

        // Validasi form
        if (!validateForm()) {
            return;
        }
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            // Jalankan setelah modal benar2 muncul
            submitForm();
        });
    });

    function validateForm() {
        let isValid = true;
        const errors = [];

        // Validasi required fields
        if (!$('#tgl_sj_expedisi').val()) {
            errors.push('Tgl surat jalan harus diisi');
            isValid = false;
        }
        if (!$('#customer_expedisi').val()) {
            errors.push('Customer harus dipilih');
            isValid = false;
        }
        if (!$('#rute_expedisi').val()) {
            errors.push('Rute harus diisi');
            isValid = false;
        }
        if (!$('#jumlah_expedisi').val() || parseNumber($('#jumlah_expedisi').val()) <= 0) {
            errors.push('Jumlah harus diisi dan lebih dari 0');
            isValid = false;
        }

        // ===============================
        // VALIDASI TABEL EXPEDISI
        // ===============================
        let tbody = $('#tableProsesExpedisi tbody');
        let rowCount = tbody.find('tr').length;

        // ### Fungsi Untuk Jenis Booking Atau Eceran Harus Sama
        // if (rowCount > 0) {
        //     // Ambil jenis dari row pertama
        //     let jenisTabel = tbody
        //         .find('tr:first td[data-jenis]')
        //         .data('jenis');

        //     let jenisInput = $('#item_expedisi_tipe').val();

        //     if (jenisInput && jenisTabel != jenisInput) {
        //         Swal.fire({
        //             icon: 'warning',
        //             title: 'Tipe Item Berbeda',
        //             html: `
        //                 Tipe item yang dipilih <b>tidak sama</b> dengan data di tabel.<br><br>
        //                 <b>Solusi:</b><br>
        //                 • Hapus data di tabel terlebih dahulu<br>
        //                 • Atau submit data yang sudah ada
        //             `,
        //             confirmButtonText: 'OK'
        //         });
        //         return false;
        //     }
        // }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: errors.join('<br>'),
                confirmButtonText: 'OK'
            });
        }

        return isValid;
    }

    function submitForm() {
        // Prepare data
        const formData = {
            // DOKUMEN
            NOMUAT: $('#no_muat_expedisi').val(),
            TGLMUAT: $('#tgl_muat_expedisi').val(),
            NOJALAN: $('#nomor_perjalanan_expedisi').val(),
            WILAYAH: $('#wilayah_expedisi').val(),
            CUSTOMER: $('#customer_expedisi').val(),
            customer_expedisi_id: $('#customer_expedisi_id').val(),
            item_expedisi_id: $('#item_expedisi_id').val(),
            item_expedisi: $('#item_expedisi').val(),

            // KENDARAAN & DRIVER
            KENDARAAN: $('#kendaraan_expedisi').val(),
            kendaraan_expedisi_id: $('#kendaraan_expedisi_id').val(),
            NAMA_KENDARAAN: $('#kendaraan_expedisi').val(),
            tglsj: $('#tgl_sj_expedisi').val(),
            NOSJ: $('#no_sj_expedisi').val(),
            DRIVER: $('#driver_1_expedisi').val(),
            driver_1_expedisi_id: $('#driver_1_expedisi_id').val(),
            NAMA_DRIVER: $('#driver_1_expedisi').val(),
            DRIVER2: $('#driver_2_expedisi').val(),
            driver_2_expedisi_id: $('#driver_2_expedisi_id').val(),
            NAMA_DRIVER2: $('#driver_2_expedisi').val(),

            // PENERIMA
            P_PENERIMA: $('#penerima_expedisi').val(),
            P_NAMA: $('#nama_penerima_expedisi').val(),
            P_PHONE: $('#phone_penerima_expedisi').val(),
            P_ALAMAT: $('#alamat_penerima_expedisi').val(),

            // BARANG
            barang: $('#barang_expedisi').val(),
            penyimpanan: $('#penyimpanan_expedisi').val(),
            koli: $('#koli_expedisi').val(),
            catatan: $('#catatan_expedisi').val(),

            // DETAIL & PERHITUNGAN
            rute: $('#rute_expedisi').val(),
            JUMLAH: parseNumber($('#jumlah_expedisi').val()) || 0,
            UNIT: 'KG', // Default KG, bisa diganti sesuai kebutuhan
            HARGA: parseNumber($('#harga_expedisi').val()) || 0,
            hargaaw: parseNumber($('#harga_expedisi').val()) || 0,
            DISC: parseFloat($('#disc_expedisi').val()) || 0,
            DC: parseNumber($('#del_charge_expedisi').val()) || 0,
            DCAW: parseNumber($('#del_charge_expedisi').val()) || 0,

            // Hitung otomatis
            NDISC: parseNumber($('#sub_total_expedisi').val()) * (parseFloat($('#disc_expedisi').val()) / 100) || 0,
            NDISCAW: parseNumber($('#sub_total_expedisi').val()) * (parseFloat($('#disc_expedisi').val()) / 100) || 0,
            TOTAL: parseNumber($('#dpp_expedisi').val()) || 0,
            PPN: parseFloat($('#ppn_expedisi').val()), // Default 11%
            GRAND: parseNumber($('#grand_total_expedisi').val()) || 0,

            // STATUS & DEFAULT VALUES
            JENISHRG: $('#item_expedisi_tipe').val(), // Default jenis harga
            JENIS: 'EKS', // Ekspedisi
            STS: 'INVOICE',
            SIMPAN: 'N',
            READY: 'Y',
            CLOSSING: 'N',
            KETERANGAN: 'EXP ' + ($('#item_expedisi').val() || '') + ' ' + ($('#customer_expedisi').val() || '') + ' KE ' + ($('#penerima_expedisi').val() || ''),

            // User info (bisa dari session)
            user: 'USER' + new Date().toLocaleDateString('id-ID'),
            // _token: $('meta[name="csrf-token"]').attr('content') // CSRF token untuk Laravel
        };

        console.log({
            barang: $('#barang_expedisi').val(),
            penyimpanan: $('#penyimpanan_expedisi').val(),
            koli: $('#koli_expedisi').val(),
            catatan: $('#catatan_expedisi').val(),
            });

        // console.log('Data yang dikirim:', formData); // Untuk debugging
        var url = $('#no_sj_expedisi').val() ? "{{ route('expedisi.update', ':nosj') }}".replace(':nosj', $('#no_sj_expedisi').val()): "{{ route('expedisi.store') }}";
        // AJAX Request
        $.ajax({
            url: url, // Ganti dengan route Laravel Anda
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#loading_modal').modal('hide');
                if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan',
                    html: `
                        <div class="text-start">
                            <p><strong>NO SJ:</strong> ${response.data.NOSJ || '-'}</p>
                            <p><strong>TOTAL:</strong> Rp ${response.data.GRAND}</p>
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    showCancelButton: false,
                    focusConfirm: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // printSuratJalan(response.data.id);
                        const id = response.data.id;
                        // let url = "{{ route('expedisi.printSuratJalan', ':id') }}";
                        // url = url.replace(':id', id);
                        // window.open(url, '_blank');
                        const noMuat = $('#no_muat_expedisi_flag').val();
                        if (userRole === 'admin' && (!noMuat || noMuat.trim() === '')) {
                            addRowExpedisi({
                                id: response.data.id,
                                NOSJ: response.data.NOSJ,
                                tglsj: $('#tgl_sj_expedisi').val(),
                                JUMLAH: formData.JUMLAH,
                                HARGA: parseNumber(formData.HARGA),
                                DC: parseNumber(formData.DC),
                                NDISC: formData.DISC,
                                PPN: formData.PPN,
                                JENISHRG: formData.JENISHRG,
                                GRAND: parseNumber(formData.GRAND),
                            });
                        }
                        resetFormExpedisi();
                    }
                });
            } else {
                    $('#loading_modal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                $('#loading_modal').modal('hide');

                let errorMessage = 'Terjadi kesalahan pada server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).join('<br>');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage,
                    confirmButtonText: 'OK'
                });
            }
        });
    }
    // ======================== End Of Submit Data Form Surjal =============================
    // ================================= Append Form to Table No Muat ====================================
    $('#buttonAppendExp').on('click', function () {
        const noMuat = $('#no_muat_expedisi_flag').val();
        if (userRole === 'admin') {
            if (!noMuat || noMuat.trim() === '') {
                addRowExpedisi({
                    NOSJ: $('#no_sj_expedisi').val(),
                    tglsj: $('#tgl_sj_expedisi').val(),
                    JUMLAH: $('#jumlah_expedisi').val(),
                    HARGA: $('#harga_expedisi').val(),
                    DC: $('#del_charge_expedisi').val(),
                    NDISC: $('#disc_expedisi').val(),
                    PPN: $('#ppn_expedisi').val(),
                    JENISHRG: $('#item_expedisi_tipe').val(),
                    GRAND: $('#grand_total_expedisi').val(),
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nomor Muat Sudah Ada',
                    text: 'Data yang sudah memiliki nomor muat tidak dapat ditambahkan ke tabel.',
                    confirmButtonText: 'OK'
                });
                return;
            }
        }
        resetFormExpedisi();
    });
    // ============================== End Of Append Form to Table No Muat ==============================
    // =========================== Print PDF ================================
    $('#btnPrintSuratJalan').on('click', function () {
        let id = $(this).attr('data-id');
        let url = "{{ route('expedisi.printSuratJalan', ':id') }}";
        url = url.replace(':id', id);
        window.open(url, '_blank');
    });

    $(document).on('click', '.btn-print-surjal', function () {
        let id = $(this).data('id');
        if (!id) return;
        let url = "{{ route('expedisi.printSuratJalan', ':id') }}";
        url = url.replace(':id', id);
        window.open(url, '_blank');
    });
    // ======================== End Of Print PDF =============================
    // ============================ Clear Form =============================
    $('#buttonClearExp').click(function() {
        resetFormExpedisi();
    });
    // ======================== End Of Clear Form =============================
    // ================================= Pilih No Muat =====================================
    $('#muat_expedisi_btn').click(function(e) {
        e.preventDefault();
        $('#muatModalExp').modal('show');
        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalMuatExpTable')) {
            $('#modalMuatExpTable').DataTable().destroy();
        }
        var tableMuat = $('#modalMuatExpTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
            url: "{{ route('expedisi.data') }}",
                data: function(d) {
                    d.tgl_mulai = $('#filter_tgl_mulai').val();
                    d.tgl_akhir = $('#filter_tgl_akhir').val();
                    d.search_muat = $('#filter_muat').val();
                }
            },
            // Scroll settings
            scrollX: true,
            scrollY: "400px",
            scrollCollapse: true,
            // Responsive settings
            responsive: true,
            autoWidth: true,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'NOMUAT', name: 'NOMUAT' },
                { data: 'TGLMUAT', name: 'TGLMUAT' },
                { data: 'CUSTOMER', name: 'CUSTOMER' },
                { data: 'rute', name: 'rute' },
                { data: 'JUMLAH', name: 'JUMLAH' },
                { data: 'harga_formatted', name: 'HARGA' },
                { data: 'DISC', name: 'DISC' },
                { data: 'dc_formatted', name: 'DC' },
                { data: 'total_formatted', name: 'GRAND' },
                { data: 'NOSJ', name: 'NOSJ' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#btn_filter_muat').click(function () {
            tableMuat.ajax.reload();
        });
    });
    // ============================= End Of Pilih No Muat =====================================
    // ============================ Rute Table Muat ===================================
     $('#ruteTableExp').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("rute-muat.data") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'RUTE', name: 'RUTE' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '20%'}
        ]
    });
    window.pickDataRuteMuat = function(id, ruteText) {
        $('#ruteModalExp').modal('hide');
        $('#rute_muat_expedisi').val(ruteText);
    }
    // ============================ End Of Rute Table Muat ===================================
    // ============================= Show Detail No Muat ================================
    // Event listener untuk tombol pickMuat
    $(document).on('click', '.pickMuat', function () {
        const nomuat = $(this).data('nomuat');

        $('#muatModalExp').modal('hide');
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            showMuatDet();
        });
        function showMuatDet(){
            $.ajax({
                url: '{{ route('expedisi.show') }}',
                type: 'GET',
                data: { nomuat: nomuat },
                success: function (res) {
                    $('#loading_modal').modal('hide');
                    if (!res.success) {
                        Swal.fire('Gagal', res.message, 'error');
                        return;
                    }
                    const tbody = $('#tableProsesExpedisi tbody');
                    tbody.empty();

                    let grandTotal = 0;

                    res.data.forEach((row, index) => {
                        // grandTotal += parseFloat(row.total || 0);

                        tbody.append(`
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${row.nosj ?? '-'}</td>
                                <td>${row.tgl_sj ?? '-'}</td>
                                <td class="text-end">${formatNumber(row.dc)}</td>
                                <td class="text-end">${formatNumber(row.jumlah)}</td>
                                <td>KG</td>
                                <td data-jenis="${row.jenishrg}">
                                    ${getJenisHargaLabel(row.jenishrg)}
                                </td>
                                <td class="text-end">${formatNumber(row.harga)}</td>
                                <td class="text-end">${row.disc ?? 0}%</td>
                                <td class="text-end">${row.ppn ?? 0 }</td>
                                <td class="text-end">${formatNumber(nvl(row.total))}</td>
                                <td class="text-end"><input type="text" class="form-control form-control-sm pengirim_muat_expedisi" data-nosj="${row.nosj}" value="${row.pengirim ?? ''}"></td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-info btn-print-surjal" data-id="${row.id ?? ''}" title="Cetak Surat Jalan">
                                            <i class="bx bx-printer"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-hapus-row">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });

                    $('#tgl_muat_expedisi').val(res.data[0].tglmuat);
                    $('#no_muat_expedisi').val(res.data[0].nomuat);
                    $('#rute_muat_expedisi').val(res.data[0].rute);
                    $('#plat_nomor_muat_expedisi').val(res.data[0].plat_nomor);
                    $('#driver1_kode_muat_expedisi').val(res.data[0].driver1_kode);
                    $('#driver1_muat_expedisi').val(res.data[0].driver1);
                    $('#driver2_kode_muat_expedisi').val(res.data[0].driver2_kode);
                    $('#driver2_muat_expedisi').val(res.data[0].driver2);
                    $('#kendaraan_kode_muat_expedisi').val(res.data[0].kendaraan_kode);
                    $('#kendaraan_muat_expedisi').val(res.data[0].kendaraan);
                    $('#km_awal_muat_expedisi').val(res.data[0].km_awal);
                    $('#km_akhir_muat_expedisi').val(res.data[0].km_akhir);
                    $('#uang_jalan_muat_expedisi').val(formatNumber(res.data[0].uang_jalan));
                    $('#uang_driver_makan_muat_expedisi').val(
                        formatNumber(res.data[0].uang_driver_makan)
                    );
                    $('#uang_lain_lain_muat_expedisi').val(
                        formatNumber(res.data[0].uang_lain_lain)
                    );
                    // $('#ac_kendaraan_muat_expedisi').val(
                    //     res.data[0].ac_kendaraan
                    // );
                    hitungGrandTotal();
                    $('#simpanMuatExpBtn').removeClass('btn-primary').addClass('btn-success').html("<i class='bx bx-plus-circle me-1'></i> Update No Muat");
                    $('#simpanMuatExpBtn').show();
                    $('#tampilkanPdfMuatExpBtn').show();
                    $('#clearMuatExpBtn').show();
                    // $('#grandTotal').text(formatNumber(grandTotal));
                }
            });
        };
    });
    // ======================== End Of Show Detail No Muat =============================
    // ================================ Delete No Muat ======================================
    $(document).on('click', '.deleteMuat', function () {
        let id     = $(this).data('id');
        let nomuat = $(this).data('nomuat');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'No Muat ' + nomuat + ' akan dihapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                let url = "{{ route('expedisi-muat.destroy', ':nomuat') }}";
                url = url.replace(':nomuat', nomuat);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // reload datatable
                        $('#modalMuatExpTable').DataTable().ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        // Ambil pesan dari controller jika ada, jika tidak ada gunakan pesan default
                        let errorMsg = 'Data tidak bisa dihapus';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMsg
                        });
                    }
                });
            }
        });
    });
    // ============================ End Of Delete No Muat ====================================
    // ============================ Simpan Submit No Muat =================================
    $('#tampilkanPdfMuatExpBtn').on('click', function () {
        let nomuat = $('#no_muat_expedisi').val();
        if (!nomuat) return;

        let urlPdf = "{{ route('expedisi.pdfMuat', ':nomuat') }}";
        urlPdf = urlPdf.replace(':nomuat', nomuat);
        window.open(urlPdf, '_blank');
    });

    $('#simpanMuatExpBtn').on('click', function () {
        // if (!validateForm()) return;
        let kmAwal  = parseFloat($('#km_awal_muat_expedisi').val()) || 0;
        let kmAkhir = parseFloat($('#km_akhir_muat_expedisi').val()) || 0;
        if(kmAkhir < kmAwal){
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'KM Akhir tidak boleh lebih kecil dari KM Awal'
            });
            return;
        }

        let nosjList = [];

        $('#tableProsesExpedisi tbody tr').each(function () {
            let nosj = $(this).find('td:eq(1)').text().trim();
            if (nosj && nosj !== '-') {
                nosjList.push(nosj);
            }
        });

        if (nosjList.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Kosong',
                text: 'Tidak ada NO SJ untuk disimpan'
            });
            return;
        }

        let pengirimData = [];
        $('.pengirim_muat_expedisi').each(function () {
            pengirimData.push({
                nosj: $(this).data('nosj'),
                pengirim: $(this).val()
            });
        });
        console.log('pengirim :' + pengirimData);

        let nomuat = $('#no_muat_expedisi').val();
        let url = nomuat ? "{{ route('expedisi-muat.update', ':nomuat') }}".replace(':nomuat', nomuat) : "{{ route('expedisi-muat.store') }}";

        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            // Jalankan setelah modal benar2 muncul
            submitFormMuat(url);
        });
        function submitFormMuat(url) {
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    nosj: nosjList,

                    tglmuat: $('#tgl_muat_expedisi').val(),
                    rute: $('#rute_muat_expedisi').val(),
                    plat_nomor: $('#plat_nomor_muat_expedisi').val(),
                    driver1_kode: $('#driver1_kode_muat_expedisi').val(),
                    driver1: $('#driver1_muat_expedisi').val(),
                    driver2_kode: $('#driver2_kode_muat_expedisi').val(),
                    driver2: $('#driver2_muat_expedisi').val(),
                    kendaraan_kode: $('#kendaraan_kode_muat_expedisi').val(),
                    kendaraan: $('#kendaraan_muat_expedisi').val(),
                    km_awal: $('#km_awal_muat_expedisi').val(),
                    km_akhir: $('#km_akhir_muat_expedisi').val(),
                    uang_jalan: parseNumber($('#uang_jalan_muat_expedisi').val()),
                    uang_driver_makan: parseNumber(
                        $('#uang_driver_makan_muat_expedisi').val()
                    ),
                    uang_lain_lain: parseNumber(
                        $('#uang_lain_lain_muat_expedisi').val()
                    ),
                    pengirim: pengirimData
                },
                success: function (res) {
                    $('#loading_modal').modal('hide');
                    // isi NOMUAT hasil store
                    if(res.nomuat){
                        $('#no_muat_expedisi').val(res.nomuat);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message
                    });
                    $('#simpanMuatExpBtn')
                        .removeClass('btn-primary')
                        .addClass('btn-success')
                        .html("<i class='bx bx-save me-1'></i> Update No Muat")
                        .show();
                    $('#tampilkanPdfMuatExpBtn').show();
                    $('#clearMuatExpBtn').show();
                },
                error: function () {
                    $('#loading_modal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan data'
                    });
                }
            });
        }
    });
    // ======================== End of Simpan Submit No Muat =============================
    // =========================== Hapus row tabel generate no muat  ===========================
    $(document).on('click', '.btn-hapus-row', function () {
        let row = $(this).closest('tr');
        row.remove();
        $('#tableProsesExpedisi tbody tr').each(function (index) {
            $(this).find('td:eq(0)').text(index + 1);
        });
        hitungGrandTotal();
        // 🔽 CEK JIKA TABEL KOSONG
        let totalRow = $('#tableProsesExpedisi tbody tr').length;

        if (totalRow === 0) {
            $('#simpanMuatExpBtn').removeClass('btn-success').addClass('btn-primary').html("<i class='bx bx-plus-circle me-1'></i> Simpan No Muat");
            $('#simpanMuatExpBtn').hide();
            $('#tampilkanPdfMuatExpBtn').hide();
            $('#clearMuatExpBtn').hide();
            $('#tgl_muat_expedisi').val('');
            $('#no_muat_expedisi').val('');
        }
    });
    // ======================= End Of Hapus row tabel generate no muat  ==========================
    // =============================== Clear Table No Muat ===================================
    $('#clearMuatExpBtn').on('click', function () {
        // Hapus semua row tabel
        deleteRowTabelMuat();
    });
    // ========================== End Of Clear Table No Muat =================================
    // =============================== Enter Next Function =====================================
    // ### FORM SURJAL
    $(document).ready(function() {
    // Daftar semua input dalam urutan
        var inputs = [
            '#tgl_sj_expedisi',
            '#no_sj_expedisi',
            '#wilayah_expedisi',
            '#customer_expedisi',
            '#customer_kode_expedisi',
            '#item_expedisi',
            '#kendaraan_expedisi',
            '#driver_1_expedisi',
            '#driver_2_expedisi',
            '#penerima_expedisi',
            '#nama_penerima_expedisi',
            '#phone_penerima_expedisi',
            '#alamat_penerima_expedisi',
            '#barang_expedisi',
            '#penyimpanan_expedisi',
            '#koli_expedisi',
            '#catatan_expedisi',
            '#rute_expedisi',
            '#jumlah_expedisi',
            '#harga_expedisi',
            '#disc_expedisi',
            '#del_charge_expedisi'
        ];

        // Event Enter untuk semua input
        $('input, select, textarea').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                var currentId = '#' + $(this).attr('id');
                var currentIndex = inputs.indexOf(currentId);

                if (currentIndex !== -1) {
                    // Cari field berikutnya yang bisa diisi
                    for (var i = currentIndex + 1; i < inputs.length; i++) {
                        var nextField = $(inputs[i]);
                        if (nextField.is(':visible') && !nextField.prop('disabled')) {
                            nextField.focus();
                            if (nextField.is('input, textarea')) {
                                nextField.select();
                            }
                            return;
                        }
                    }
                    // Jika sudah di akhir, fokus ke tombol SIMPAN
                    $('#buttonSimpanExp').focus();
                }
            }
        });
    });
    // ### FORM MUAT
    $(document).ready(function() {
        var fields = [
            '#tgl_muat_expedisi','#no_muat_expedisi','#rute_muat_expedisi',
            '#kendaraan_muat_expedisi','#plat_nomor_muat_expedisi',
            '#driver1_muat_expedisi','#driver2_muat_expedisi',
            '#km_awal_muat_expedisi','#km_akhir_muat_expedisi',
            '#uang_jalan_muat_expedisi','#uang_driver_makan_muat_expedisi',
            '#uang_lain_lain_muat_expedisi'
        ];

        $(fields.join(',')).on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var idx = fields.indexOf('#' + $(this).attr('id'));
                for (var i = idx + 1; i < fields.length; i++) {
                    var f = $(fields[i]);
                    if (f.is(':visible') && !f.prop('disabled')) {
                        f.focus();
                        f.select();
                        return;
                    }
                }
                $('#simpanMuatExpBtn').focus();
            }
        });
    });
    // ========================= End Of Enter Next Function ============================
    // ########################################################################
    // FUNCTION HELPER:
    // ########################################################################
    // Fungsi untuk format number
    function formatNumber(num) {
        num = num ?? 0;
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseNumber(str) {
        if (!str) return 0;
        return parseFloat(str.toString().replace(/\./g, '').replace(',', '.'));
    }

    // Menghitung nilai total di form expedisi
    function calculateTotal() {
        // Ambil nilai dari form
        const jumlah = parseNumber($('#jumlah_expedisi').val()) || 0;
        const harga = parseNumber($('#harga_expedisi').val()) || 0;
        const discPercent = parseFloat($('#disc_expedisi').val()) || 0;
        const delCharge = parseNumber($('#del_charge_expedisi').val()) || 0;
        const ppnPercent = parseNumber($('#ppn_expedisi').val()) || 0;

        // ===========================================
        // PERHITUNGAN YANG BENAR:
        // ===========================================

        // 1. Sub total (sebelum diskon)
        const subTotal = jumlah * harga;

        // 2. Diskon dalam rupiah
        const discAmount = subTotal * (discPercent / 100);

        // 3. Total setelah diskon (DPP)
        const dpp = subTotal - discAmount;

        // 4. PPN langsung masuk ke grand total (tanpa tampilkan nominal)
        const ppnAmount = dpp * (ppnPercent / 100);

        // 5. Grand total (DPP + PPN + Del Charge)
        const grandTotal = dpp + ppnAmount + delCharge;

        // ===========================================
        // TAMPILKAN HASIL:
        // ===========================================
        // Hanya tampilkan yang perlu
        $('#sub_total_expedisi').val(formatNumber(Math.round(subTotal)));
        $('#dpp_expedisi').val(formatNumber(Math.round(dpp)));
        $('#grand_total_expedisi').val(formatNumber(Math.round(grandTotal)));
    }

    // =================== Pajak PPN ==========================
    function loadInputPajak(){
        $.ajax({
            url: '{{ route('get_pajak') }}',
            type: 'GET',
            success: function(response) {
                let nilai_ppn = response.data.ppn;
                $('#ppn_expedisi').val(nilai_ppn);
            },
            error: function() {
                $('#ppn_expedisi').val('Error Loading');
            }
        });
    }

    function setButtonToUpdateMode() {
        $('#buttonSimpanExp')
            .removeClass('btn-info')
            .addClass('btn-success')
            .html('<i class="bx bx-edit me-1"></i>UPDATE')
            .attr('title', 'Update data ekspedisi')
            .data('mode', 'update')

        // Tambahkan badge info
        $('#buttonSimpanExp').append('<span class="badge bg-light text-dark ms-2">EDIT MODE</span>');
        // aktifkan button papend to table
        if (userRole === 'admin') {
            $('#divButtonAppendExp').show();
        }
    }

    // Fungsi untuk mengubah tombol ke mode CREATE/SIMPAN
    function setButtonToSaveMode() {
        $('#buttonSimpanExp')
            .removeClass('btn-success')
            .addClass('btn-info')
            .html('<i class="bx bx-save me-1"></i>SIMPAN')
            .attr('title', 'Simpan data ekspedisi baru')
            .data('mode', 'save')
            .data('id', '')
            .find('.badge').remove(); // Hapus badge jika ada
            $('#divPrintSuratJalan').addClass('d-none');
            // aktifkan button papend to table
            $('#divButtonAppendExp').hide();
    }

    // Fungsi Untuk Me Reset Form Expedisi
    function resetFormExpedisi() {
        // Reset semua input text, number, textarea
        $('input[type="text"], input[type="number"], textarea')
        .not(`
            #no_muat_expedisi,
            #tgl_muat_expedisi,
            #rute_muat_expedisi,
            #kendaraan_kode_muat_expedisi,
            #kendaraan_muat_expedisi,
            #plat_nomor_muat_expedisi,
            #driver1_kode_muat_expedisi,
            #driver1_muat_expedisi,
            #driver2_kode_muat_expedisi,
            #driver2_muat_expedisi,
            #km_awal_muat_expedisi,
            #km_akhir_muat_expedisi,
            #uang_jalan_muat_expedisi,
            #uang_driver_makan_muat_expedisi,
            #uang_lain_lain_muat_expedisi,
            .pengirim_muat_expedisi
        `)
        .val('');
        // Reset select
        $('select').prop('selectedIndex', 0);
        // Field khusus
        $('#sub_total_expedisi, #dpp_expedisi, #ppn_expedisi, #grand_total_expedisi').val('');
        $('#wilayah_expedisi').val('denpasar');
        $('#penyimpanan_expedisi').val('F');
        // Set default tanggal muat
        $('#tgl_sj_expedisi').val(new Date().toISOString().split('T')[0]);
        setButtonToSaveMode();
        loadInputPajak();
    }

    // fungsi merubah tabel menjadi
    function nvl(val) {
        return val ? val : 0;
    }

    // Menambah Baris Tabel No Muat
    function addRowExpedisi(data) {
        let tbody = $('#tableProsesExpedisi tbody');
        let nosjBaru = (data.NOSJ || '').toString().trim();

        // Cek duplikat NOSJ
        let sudahAda = false;
        tbody.find('tr').each(function () {
            let nosjTabel = $(this).find('td:eq(1)').text().trim(); // kolom NOSJ
            if (nosjTabel === nosjBaru && nosjBaru !== '') {
                sudahAda = true;
                return false; // break loop
            }
        });

        if (sudahAda) {
            Swal.fire({
                icon: 'warning',
                title: 'Duplikat NO SJ',
                text: `NO SJ ${nosjBaru} sudah ada di tabel`,
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        let no = tbody.find('tr').length + 1;

        let row = `
            <tr>
                <td class="text-center">${no}</td>
                <td>${nosjBaru || '-'}</td>
                <td>${data.tglsj || '-'}</td>
                <td class="text-end">${nvl(data.DC)}</td>
                <td class="text-end">${nvl(data.JUMLAH)}</td>
                <td class="text-center">KG</td>
                <td data-jenis="${data.JENISHRG}">
                    ${getJenisHargaLabel(data.JENISHRG)}
                </td>
                <td class="text-end">${nvl(data.HARGA)}</td>
                <td class="text-end">${nvl(data.NDISC)}</td>
                <td class="text-end">${nvl(data.PPN)}</td>
                <td class="text-end">${formatNumber(nvl(data.GRAND))}</td>
                <td class="text-end"><input type="text" class="form-control form-control-sm pengirim_muat_expedisi" data-nosj="${nosjBaru}" value=""></td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-info btn-sm btn-print-surjal" data-id="${data.id || ''}" title="Cetak Surat Jalan" ${data.id ? '' : 'disabled'}>
                            <i class="bx bx-printer"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-hapus-row">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;

        tbody.append(row);
        hitungGrandTotal();
        $('#simpanMuatExpBtn').show();
        $('#clearMuatExpBtn').show();
    }

    // untuk convert janis harga pada Tabel No Muat Row
    function getJenisHargaLabel(val) {
        if (val == 1) return 'Eceran';
        if (val == 2) return 'Booking';
        return '-';
    }

    // Menghitung Lumalah Tabel No Muat
    function hitungGrandTotal() {
        let total = 0;

        $('#tableProsesExpedisi tbody tr').each(function () {
            let nilai = $(this).find('td:eq(10)').text();
            total += parseNumber(nilai) || 0;
        });

        $('#grandTotal').text(total.toLocaleString('id-ID'));
    }

    // Hapus semua row tabel no muat
    function deleteRowTabelMuat() {
        // =========================
        // CLEAR TABLE
        // =========================
        $('#tableProsesExpedisi tbody').empty();
        // =========================
        // RESET HEADER FORM
        // =========================
        $('#tgl_muat_expedisi').val('');
        $('#no_muat_expedisi').val('');
        $('#rute_muat_expedisi').val('');
        $('#plat_nomor_muat_expedisi').val('');
        $('#driver1_kode_muat_expedisi').val('');
        $('#driver1_muat_expedisi').val('');
        $('#driver2_kode_muat_expedisi').val('');
        $('#driver2_muat_expedisi').val('');
        $('#kendaraan_kode_muat_expedisi').val('');
        $('#kendaraan_muat_expedisi').val('');
        $('#km_awal_muat_expedisi').val('');
        $('#km_akhir_muat_expedisi').val('');
        $('#uang_jalan_muat_expedisi').val('');
        $('#uang_driver_makan_muat_expedisi').val('');
        $('#uang_lain_lain_muat_expedisi').val('');
        $('#ac_kendaraan_muat_expedisi').val('');

        // =========================
        // HITUNG ULANG
        // =========================
        hitungGrandTotal();
        // =========================
        // RESET BUTTON
        // =========================
        $('#simpanMuatExpBtn')
            .removeClass('btn-success')
            .addClass('btn-primary')
            .html("<i class='bx bx-plus-circle me-1'></i> Simpan No Muat");
        $('#simpanMuatExpBtn').hide();
        $('#tampilkanPdfMuatExpBtn').hide();
        $('#clearMuatExpBtn').hide();
    }

    // ###Print PDF
    function printSuratJalan(id) {
        let url = "{{ route('expedisi.printSuratJalan', ':id') }}";
        url = url.replace(':id', id);
        fetch("http://localhost:3000/print", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                url: url,
                printer: "EPSONLX"
            })
        })
        .then(r => r.json())
        .then(r => console.log(r))
        .catch(err => {
            console.error(err);
            alert("Print service tidak aktif");
        });
    }
    // function hitungExpedisi() {
    //     let jumlah = parseFloat($('#jumlah_expedisi').val()) || 0;
    //     let harga  = parseFloat($('#harga_expedisi').val()) || 0;
    //     let disc   = parseFloat($('#disc_expedisi').val()) || 0;
    //     let ppnPersen = parseFloat($('#ppn_expedisi').val()) || 0; // 11 (tetap)

    //     // SUB TOTAL
    //     let subTotal = jumlah * harga;

    //     // DPP (setelah diskon)
    //     let nilaiDisc = subTotal * (disc / 100);
    //     let dpp = subTotal - nilaiDisc;

    //     // PPN (dipakai INTERNAL, tidak ditampilkan)
    //     let ppn = dpp * (ppnPersen / 100);

    //     // GRAND TOTAL
    //     let grandTotal = dpp + ppn;

    //     // TAMPILKAN (HANYA YANG PERLU)
    //     $('#sub_total_expedisi').val(subTotal.toLocaleString('id-ID'));
    //     $('#dpp_expedisi').val(dpp.toLocaleString('id-ID'));
    //     $('#grand_total_expedisi').val(grandTotal.toLocaleString('id-ID'));
    // }
    // // Trigger otomatis
    // $('#jumlah_expedisi, #harga_expedisi, #disc_expedisi').on('keyup change', hitungExpedisi);
});
</script>
