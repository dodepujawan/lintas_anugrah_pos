<style>
  .frm-section-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: 0.75rem;
  }
  .frm-card {
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    background: #fff;
  }
  .form-label {
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 4px;
  }
  .form-control, .form-select {
    font-size: 13px;
  }
  .input-group-text {
    font-size: 13px;
    background: #f8f9fa;
    color: #6c757d;
  }
  .total-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 0.85rem 1rem;
  }
  .total-box .lbl { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #6c757d; font-weight: 600; }
  .total-box .val { font-size: 22px; font-weight: 500; color: #212529; }
  .total-box .val small { font-size: 13px; color: #6c757d; margin-right: 3px; }
  .badge-kode {
    font-size: 11px;
    background: #f1f3f5;
    color: #6c757d;
    border-radius: 6px;
    padding: 2px 8px;
    border: 1px solid #dee2e6;
    white-space: nowrap;
  }
  /* total input transparan */
  .total-input-transparent {
    border: none;
    background: transparent;
    font-size: 22px;
    font-weight: 500;
    padding: 0;
    width: 100%;
    outline: none;
    color: #212529;
  }
  .kode-input-transparent {
    border: none;
    background: transparent;
    font-size: 12px;
    font-weight: 600;
    padding: 0;
    width: 100%;
    outline: none;
  }

    /* sweet alert didepan modal */
    .swal2-container {
        z-index: 99999 !important;
    }
</style>

<div class="container-fluid py-3">
  <div class="row justify-content-center">
    <div class="col-12" style="max-width:1200px;">

      <!-- Header -->
      <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary"
             style="width:42px;height:42px;font-size:20px;">
          <i class="bx bx-cool"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-semibold">Form Sewa Mobil Berpendingin</h5>
          <small class="text-muted">Sistem penyewaan mobil berpendingin</small>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#tutorialRentPendinginModal" aria-label="Buka panduan Rent Pendingin">
          <i class="bx bx-help-circle me-1"></i>Panduan
        </button>
      </div>

      <!-- Nomor Dokumen -->
      <div class="frm-card mb-3">
        <div class="frm-section-label"><i class="bx bx-file me-1"></i>Nomor dokumen</div>
        <div class="row g-2 align-items-end">

          @role('admin')
          <div class="col">
            <label class="form-label">No. muat</label>
            <div class="input-group input-group-sm">
              <input type="text" id="no_muat_rent_dingin" name="no_muat_rent_dingin"
                class="form-control" placeholder="Auto generate" readonly>
              <button class="btn btn-outline-secondary" id="no_muat_rent_dingin_btn" type="button">
                <i class="bx bx-search"></i>
              </button>
            </div>
          </div>
          <div class="col">
            <label class="form-label">Tanggal muat</label>
            <input type="date" id="tanggal_rent_dingin" name="tanggal_rent_dingin"
              class="form-control form-control-sm">
          </div>
          @endrole

          <div class="col">
            <label class="form-label">No. surat jalan</label>
            <div class="input-group input-group-sm">
              <input type="text" id="no_surjal_rent_dingin" name="no_surjal_rent_dingin"
                class="form-control" placeholder="Auto generate" readonly>
              <button class="btn btn-outline-secondary" id="no_surjal_rent_dingin_btn" type="button">
                <i class="bx bx-search"></i>
              </button>
            </div>
          </div>
          <div class="col">
            <label class="form-label">Tanggal surat jalan</label>
            <input type="date" id="tanggal_surjal_rent_dingin" name="tanggal_surjal_rent_dingin"
              class="form-control form-control-sm">
          </div>
          <div class="col">
            <label class="form-label">Wilayah no. sj</label>
            <select id="wilayah_nosj_rent_dingin" name="wilayah_nosj_rent_dingin"
              class="form-select form-select-sm">
              <option>DENPASAR</option>
              <option>JAKARTA</option>
              <option>SURABAYA</option>
              <option>BANDUNG</option>
            </select>
          </div>

        </div>
      </div>

      <!-- 2 Kolom: Customer | Item+Driver -->
      <div class="row g-3 mb-3">

        <!-- Kolom Kiri: Customer -->
        <div class="col-md-6">
          <div class="frm-card h-100">
            <div class="frm-section-label"><i class="bx bx-user me-1"></i>Data customer</div>
            <div class="mb-2">
              <label class="form-label">Customer</label>
              <div class="input-group input-group-sm">
                <input type="hidden" name="customer_rent_dingin_id" id="customer_rent_dingin_id">
                <input type="text" id="customer_rent_dingin" name="customer_rent_dingin"
                  class="form-control" readonly placeholder="Pilih customer...">
                <button class="btn btn-outline-secondary" id="customer_rent_dingin_btn" type="button">
                  <i class="bx bx-search"></i>
                </button>
              </div>
              <div class="d-flex align-items-center gap-2 mt-1">
                <span class="badge-kode">Kode</span>
                <input type="text" id="customer_kode_dingin" name="customer_kode_dingin"
                  class="kode-input-transparent" readonly>
              </div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <label class="form-label">Nama penerima</label>
                <input type="text" id="nama_penerima_rent_dingin" name="nama_penerima_rent_dingin"
                  class="form-control form-control-sm" placeholder="Nama penerima">
              </div>
              <div class="col-md-6">
                <label class="form-label">Telepon penerima</label>
                <input type="text" id="telpon_rent_dingin" name="telpon_rent_dingin"
                  class="form-control form-control-sm" placeholder="Nomor telepon">
              </div>
            </div>
            <div class="mb-0">
              <label class="form-label">Alamat penerima</label>
              <textarea id="alamat_rent_dingin" name="alamat_rent_dingin"
                class="form-control form-control-sm" rows="2"
                placeholder="Alamat lengkap penerima"></textarea>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Item & Driver -->
        <div class="col-md-6 d-flex flex-column gap-3">

          <!-- Item & Tarif -->
          <div class="frm-card">
            <div class="frm-section-label"><i class="bx bx-package me-1"></i>Item & tarif</div>
            <div class="mb-2">
              <label class="form-label">Item</label>
              <div class="input-group input-group-sm">
                <input type="hidden" id="item_rent_dingin_id" name="item_rent_dingin_id">
                <input type="text" id="item_rent_dingin" name="item_rent_dingin"
                  class="form-control" placeholder="Pilih item..." readonly>
                <button class="btn btn-outline-secondary" id="item_rent_dingin_btn" type="button">
                  <i class="bx bx-search"></i>
                </button>
              </div>
            </div>
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label">Jumlah hari</label>
                <input type="number" id="jml_hari_rent_dingin" name="jml_hari_rent_dingin"
                  class="form-control form-control-sm" placeholder="0" min="1">
              </div>
              <div class="col-6">
                <label class="form-label">Harga / hari</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text">Rp</span>
                  <input type="text" id="harga_rent_dingin" name="harga_rent_dingin"
                    class="form-control" placeholder="0">
                </div>
              </div>
            </div>
          </div>

          <!-- Driver & Kendaraan -->
          <div class="frm-card flex-grow-1">
            <div class="frm-section-label"><i class="bx bx-car me-1"></i>Driver & kendaraan</div>
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label">Driver</label>
                <div class="input-group input-group-sm">
                  <input type="hidden" id="driver_rent_dingin_id" name="driver_rent_dingin_id">
                  <input type="text" id="driver_rent_dingin" name="driver_rent_dingin"
                    class="form-control" readonly placeholder="Pilih driver...">
                  <button class="btn btn-outline-secondary" id="driver_rent_dingin_btn" type="button">
                    <i class="bx bx-search"></i>
                  </button>
                </div>
              </div>
              <div class="col-6">
                <label class="form-label">Kendaraan</label>
                <div class="input-group input-group-sm">
                  <input type="hidden" id="kendaraan_rent_dingin_id" name="kendaraan_rent_dingin_id">
                  <input type="text" id="kendaraan_rent_dingin" name="kendaraan_rent_dingin"
                    class="form-control" readonly placeholder="Pilih kendaraan...">
                  <button class="btn btn-outline-secondary" id="kendaraan_rent_dingin_btn" type="button">
                    <i class="bx bx-search"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Perhitungan Biaya -->
      <div class="frm-card mb-3">
        <div class="frm-section-label"><i class="bx bx-calculator me-1"></i>Perhitungan biaya</div>
        <div class="row g-2 align-items-end">
          <div class="col">
            <label class="form-label">Sub total</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text">Rp</span>
              <input type="text" id="sub_total_rent_dingin" name="sub_total_rent_dingin"
                class="form-control" value="0" readonly>
            </div>
          </div>
          <div class="col">
            <label class="form-label">Diskon</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text">%</span>
              <input type="text" id="discount_rent_dingin" name="discount_rent_dingin"
                class="form-control" value="0">
            </div>
          </div>
          <div class="col">
            <label class="form-label">DPP</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text">Rp</span>
              <input type="text" id="dpp_rent_dingin" name="dpp_rent_dingin"
                class="form-control" value="0" readonly>
            </div>
          </div>
          <div class="col">
            <label class="form-label">Pajak</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text">%</span>
              <input type="text" id="pajak_rent_dingin" name="pajak_rent_dingin"
                class="form-control" readonly>
            </div>
          </div>
          <div class="col">
            <label class="form-label">Keterangan</label>
            <textarea id="keterangan_rent_dingin" name="keterangan_rent_dingin"
              class="form-control form-control-sm" rows="1"
              placeholder="Keterangan tambahan (opsional)" style="resize:none;"></textarea>
          </div>
          <div class="col-auto">
            <div class="total-box" style="min-width:130px;">
              <div class="lbl">Total</div>
              <div class="val d-flex align-items-baseline gap-1">
                <small>Rp</small>
                <input type="text" id="total_rent_dingin" name="total_rent_dingin"
                  class="total-input-transparent" value="0" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tombol Aksi -->
      <div class="d-flex justify-content-end gap-2">
        <div id="btnSimpanRentPendinginSurjalDiv">
          <button class="btn btn-primary btn-sm px-4" id="btnSimpanRentPendinginSurjal" type="button">
            <i class="bx bx-save me-1"></i>Simpan
          </button>
        </div>
        <button class="btn btn-outline-danger btn-sm px-4" id="btnClearRentPendinginSurjal" type="button">
          <i class="bx bx-trash me-1"></i>Clear
        </button>
        @role('admin')
        <div id="btnMuatRentPendinginDiv" class="d-none">
          <button class="btn btn-outline-secondary btn-sm px-4" id="btnMuatRentPendingin" type="button">
            <i class="bx bx-car me-1"></i>Proses no muat
          </button>
        </div>
        @endrole
        <div id="divPrintSuratJalanRent" class="d-none">
          <button class="btn btn-warning btn-sm px-4" id="btnPrintSuratJalanRent" type="button" data-id="">
            <i class="bx bx-printer me-1"></i>Print surat jalan
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- MODAL --}}
@include('rentPendingin.rentPendingin_modal')
<script>
var userRole = "{{ auth()->user()->role_old }}";
var userId = "{{ auth()->user()->user_id }}";
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    console.log(userRole);
    // Clear Form
    clearRentDinginForm();
    // ================================= Pilih No Muat =====================================
    $('#no_muat_rent_dingin_btn').click(function(e) {
        e.preventDefault();
        $('#muatModalRentDgn').modal('show');
        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalMuatRentDgnTable')) {
            $('#modalMuatRentDgnTable').DataTable().destroy();
        }
        var tableMuat = $('#modalMuatRentDgnTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
            url: "{{ route('rentPendingin.data') }}",
                data: function(d) {
                    d.tgl_mulai = $('#filter_tgl_mulai_rentdgn').val();
                    d.tgl_akhir = $('#filter_tgl_akhir_rentdgn').val();
                    d.search_muat = $('#filter_rent_dgn').val();
                },
                dataSrc: function(response) {
        // Debug: lihat struktur data di console
        console.log('Response Data:', response.data);
            return response.data;
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
                { data: 'PESANAN', name: 'PESANAN' },
                { data: 'JUMLAH', name: 'JUMLAH' },
                { data: 'harga_formatted', name: 'HARGA' },
                { data: 'DISC', name: 'DISC' },
                { data: 'total_formatted', name: 'GRAND' },
                { data: 'NOSJ', name: 'NOSJ' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#btn_filter_rent_dgn').click(function () {
            tableMuat.ajax.reload();
        });
    });
    // ============================= End Of Pilih No Muat =====================================
     // =============================== Show Form Muat ==================================
    $(document).on('click', '.pickRentDgn', function (e) {
        e.preventDefault();
        const nomuat = $(this).data('nomuat');

        if (!nomuat) {
            alert('No Surat Jalan tidak ditemukan');
            return;
        }

        const surjalDataUrlRentDingin = "{{ route('rentPendinginMuat.show', ['nomuat' => ':nomuat']) }}";
        const url = surjalDataUrlRentDingin.replace(':nomuat', nomuat);
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                // optional loading
                $('#loadingModal').modal('show');
            },
            success: function (res) {
                if (!res.success) {
                    alert(res.message || 'Data tidak ditemukan');
                    return;
                }
                $('#muatModalRentDgn').modal('hide');
                const d = res.data;

                // =========================
                // ISI FORM
                // =========================
                $('#no_muat_rent_dingin').val(d.NOMUAT);
                $('#tanggal_rent_dingin').val(d.TGLMUAT);
                $('#no_surjal_rent_dingin').val(d.NOSJ);
                $('#tanggal_surjal_rent_dingin').val(d.tglsj);
                $('#wilayah_nosj_rent_dingin').val(d.WILAYAH);

                // CUSTOMER
                $('#customer_rent_dingin_id').val(d.CUSTOMER_KODE);
                $('#customer_rent_dingin').val(d.CUSTOMER);
                $('#customer_kode_dingin').val(d.CUSTOMER_KODE);
                $('#nama_penerima_rent_dingin').val(d.P_NAMA ?? '');
                $('#telpon_rent_dingin').val(d.P_PHONE ?? '');
                $('#alamat_rent_dingin').val(d.P_ALAMAT ?? '');

                // ITEM
                $('#item_rent_dingin').val(d.PESANAN);
                $('#item_rent_dingin_id').val(d.PESANAN);

                // DRIVER & KENDARAAN
                $('#driver_rent_dingin_id').val(d.DRIVER);
                $('#driver_rent_dingin').val(d.NAMA_DRIVER);
                $('#kendaraan_rent_dingin_id').val(d.KENDARAAN);
                $('#kendaraan_rent_dingin').val(d.NAMA_KENDARAAN);

                // PERHITUNGAN
                $('#jml_hari_rent_dingin').val(toFloat(d.JUMLAH));
                $('#harga_rent_dingin').val(formatRupiah(d.HARGA));
                $('#discount_rent_dingin').val(toFloat(d.DISC)); // persen
                $('#sub_total_rent_dingin').val(formatRupiah(d.JUMLAH * d.HARGA));
                $('#dpp_rent_dingin').val(formatRupiah(d.TOTAL));
                $('#pajak_rent_dingin').val(toFloat(d.PPN));
                $('#total_rent_dingin').val(formatRupiah(d.GRAND));

                $('#keterangan_rent_dingin').val(d.catatan);

                // Hitung ulang biar konsisten
                calculateTotalDgn();
                 // Hide Button
                 $('#btnMuatRentPendinginDiv').removeClass('d-none')
                 $('#btnMuatRentPendingin').removeClass('btn-primary').addClass('btn-success').html('<i class="bx bx-car me-1"></i>UPDATE NOMUAT');
                 $('#btnSimpanRentPendinginSurjalDiv').addClass('d-none');

                 // Button Print PDF
                 $('#divPrintSuratJalanRent').removeClass('d-none');
                 $('#btnPrintSuratJalanRent').attr('data-sj', d.NOSJ);

             },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Gagal mengambil data');
            },
            complete: function () {
                $('#loadingModal').modal('hide');
            }
        });
    });
    // ============================ End Of Show Form Muat ==================================
    // ================================ Delete No Rent Pendingin ======================================
    $(document).on('click', '.deleteRentDgn', function () {
        let id     = $(this).data('id');
        let nomuat = $(this).data('nomuat');

        Swal.fire({
            title: 'Cancel Data?',
            text: 'No Muat ' + nomuat + ' akan dicancel!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, cancel',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                let url = "{{ route('rentPendingin.destroy', ':id') }}";
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
                        $('#modalMuatRentDgnTable').DataTable().ajax.reload(null, false);
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
    // ========================= End Of Delete No Rent Pendingin =================================
    // ================================= Pilih Customer =====================================
    $('#customer_rent_dingin_btn').click(function(e) {
        e.preventDefault();
        $('#customerModalDgn').modal('show');
        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalCusDgnTable')) {
            $('#modalCusDgnTable').DataTable().destroy();
        }
        var table = $('#modalCusDgnTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("rentPendingin-cus.data") }}',
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

        $('#customerModalDgn').one('shown.bs.modal', function() {
            table.columns.adjust();
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // ### Select Button
    $(document).on('click', '.view-btn-customer-rent-dingin', function(e) {
        e.preventDefault();
        var kodeCus = $(this).data('id');
        var namaCus = $(this).data('name');
        var CUSTOMER = $(this).data('customer');
        var alamat = $(this).data('alamat');
        var telepon = $(this).data('telepon');
        // Mengisi nilai ke elemen yang dituju
        $('#customer_rent_dingin_id').val(kodeCus);
        $('#customer_rent_dingin').val(namaCus);
        $('#customer_kode_dingin').val(CUSTOMER);
        // Kosongkan dulu item
        $('#alamat_rent_dingin').val(alamat);
        $('#telpon_rent_dingin').val(telepon);
        $('#nama_penerima_rent_dingin').val(namaCus);

        // Tutup modal
        $('#customerModalDgn').modal('hide');
    });
    // ============================== End Of Pilih Customer ==================================
    // =================================== Pilih Item =====================================
    $(document).on('click', '#item_rent_dingin_btn', function(e) {
        var expedisiId = $('#customer_rent_dingin_id').val();

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

        $('#itemModalDgn').modal('show');

        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalItemDgnTable')) {
            $('#modalItemDgnTable').DataTable().destroy();
        }

        // rebuild datatable
        $('#modalItemDgnTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('price-rentcus-modal.price', ':kode') }}".replace(':kode', expedisiId),
                dataSrc: function (json) {
                    // SET INFO CUSTOMER DI ATAS TABEL
                    $("#custNameDgn").text(json.customer_nama);
                    $("#custKodeDgn").text(json.customer_kode);
                    return json.data;
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false },
                { data: 'nama_kendaraan' },
                { data: 'ITEM' },
                { data: 'PERIODE' },
                { data: 'PLAT' },
                { data: 'JENIS' },
                { data: 'harga_html', orderable: false, searchable: false },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    });
    // ### Select Button
    $(document).on('click', '.pick-price-dgn', function(e) {
        e.preventDefault();
        var kodeCus = $(this).data('id');
        var kodeMbl = $(this).data('kodembl');
        var kodeDgn = $(this).data('kode');
        // Ambil KETERANGAN dari kolom di baris yang sama
        var row = $(this).closest('tr');
        var kendaraan = row.find('td:eq(1)').text();
        var item = row.find('td:eq(2)').text();
        var harga = row.find('td:eq(6)').text().trim();

        // Mengisi nilai ke elemen yang dituju
        $('#jml_hari_rent_dingin').val(1);
        $('#item_rent_dingin_id').val(kodeDgn);
        $('#item_rent_dingin').val(item);
        $('#kendaraan_rent_dingin_id').val(kodeMbl);
        $('#kendaraan_rent_dingin').val(kendaraan);
        $('#harga_rent_dingin').val(harga);
        calculateTotalDgn();
        // Tutup modal
        $('#itemModalDgn').modal('hide');
    });
    // =============================== End Of Pilih Item ==================================
    // =================================== Pilih Driver =====================================
    $(document).on('click', '#driver_rent_dingin_btn', function(e) {
        e.preventDefault();
        var kodeKen = $(this).data('id');
        $('#driverModalDgn').modal('show');

        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalDriverDgnTable')) {
            $('#modalDriverDgnTable').DataTable().destroy();
        }

        // rebuild datatable
        $('#modalDriverDgnTable').DataTable({
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
        var kodeDriver = $(this).data('id');
        // Ambil KETERANGAN dari kolom di baris yang sama
        var row = $(this).closest('tr');
        var nama = row.find('td:eq(2)').text();
        $('#driver_rent_dingin_id').val(kodeDriver);
        $('#driver_rent_dingin').val(nama);
        // Tutup modal
        $('#driverModalDgn').modal('hide');
    });
    // =============================== End Of Pilih Driver ==================================
    // =================================== Pilih Kendaraan =====================================
    $(document).on('click', '#kendaraan_rent_dingin_btn', function(e) {

        $('#kendaraanModalDgn').modal('show');

        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalKendaraanDgnTable')) {
            $('#modalKendaraanDgnTable').DataTable().destroy();
        }

        // rebuild datatable
        $('#modalKendaraanDgnTable').DataTable({
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
        var kodeKendaraan = $(this).data('id');
        // Ambil KETERANGAN dari kolom di baris yang sama
        var row = $(this).closest('tr');
        var keterangan = row.find('td:eq(1)').text();
        var nama = row.find('td:eq(2)').text();

        // Mengisi nilai ke elemen yang dituju
        $('#kendaraan_rent_dingin_id').val(keterangan);
        $('#kendaraan_rent_dingin').val(nama);

        // Tutup modal
        $('#kendaraanModalDgn').modal('hide');
    });
    // =============================== End Of Pilih Kendaraan ==================================
    // =================================== Input Total ======================================
    $('#harga_rent_dingin, #jml_hari_rent_dingin, #discount_rent_dingin').on('input', function() {
        calculateTotalDgn();
    });
    // =============================== End Of Input Total ==================================
    // =============================== Submit Form Surjal ==================================
    $('#btnSimpanRentPendinginSurjal').on('click', function () {
        let formData = {
            _token: $('input[name="_token"]').val(),

            no_muat_rent_dingin: $('#no_muat_rent_dingin').val(),
            tanggal_rent_dingin: $('#tanggal_rent_dingin').val(),

            no_surjal_rent_dingin: $('#no_surjal_rent_dingin').val(),
            tanggal_surjal_rent_dingin: $('#tanggal_surjal_rent_dingin').val(),
            wilayah_nosj_rent_dingin: $('#wilayah_nosj_rent_dingin').val(),

            customer_rent_dingin_id: $('#customer_rent_dingin_id').val(),
            customer_rent_dingin: $('#customer_rent_dingin').val(),
            nama_penerima_rent_dingin: $('#nama_penerima_rent_dingin').val(),
            telpon_rent_dingin: $('#telpon_rent_dingin').val(),
            alamat_rent_dingin: $('#alamat_rent_dingin').val(),

            item_rent_dingin: $('#item_rent_dingin').val(),

            jml_hari_rent_dingin: $('#jml_hari_rent_dingin').val(),
            harga_rent_dingin: $('#harga_rent_dingin').val(),
            discount_rent_dingin: $('#discount_rent_dingin').val(),
            pajak_rent_dingin: $('#pajak_rent_dingin').val(),
            total_rent_dingin: $('#total_rent_dingin').val(),

            driver_rent_dingin_id: $('#driver_rent_dingin_id').val(),
            driver_rent_dingin: $('#driver_rent_dingin').val(),

            kendaraan_rent_dingin_id: $('#kendaraan_rent_dingin_id').val(),
            kendaraan_rent_dingin: $('#kendaraan_rent_dingin').val(),

            keterangan_rent_dingin: $('#keterangan_rent_dingin').val(),
            KETERANGAN: 'REN ' + ($('#item_rent_dingin').val() || '') + ' ' + ($('#customer_rent_dingin').val() || ''),
        };
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            // Jalankan setelah modal benar2 muncul
            submitFormSurjalRentDgn();
        });
        function submitFormSurjalRentDgn(){

            var url = $('#no_surjal_rent_dingin').val() ? "{{ route('rentPendinginSurjal.update', ':nosj') }}".replace(':nosj', $('#no_surjal_rent_dingin').val()): "{{ route('rentPendingin-surjal.store') }}";

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (res) {
                    if (res.success) {
                        $('#loading_modal').modal('hide');
                        // printSuratJalanRent(res.data.NOSJ);
                        let sj = res.data.NOSJ;
                        // let url = "{{ route('rentPendingin.printSuratJalan', ':sj') }}";
                        // url = url.replace(':sj', sj);
                        // window.open(url, '_blank');
                        if($('#no_surjal_rent_dingin').val() != ""){
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Diupdate!',
                                text: `Data berhasil diupdate dengan NOSJ: ${res.data.NOSJ}`,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#2add69'
                            });
                        }else{
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disimpan!',
                                text: `Data berhasil disimpan dengan NOSJ: ${res.data.NOSJ}`,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6'
                            });
                            $('#no_surjal_rent_dingin').val(res.data.NOSJ);
                        }
                        // Button Print PDF
                        $('#divPrintSuratJalanRent').removeClass('d-none');
                        $('#btnPrintSuratJalanRent').attr('data-sj',res.data.NOSJ);
                        setButtonToUpdateMode();
                        // clearRentDinginForm();
                        // setButtonToSaveMode();
                        // reset form kalau mau
                        // $('#formRentPendingin')[0].reset();
                    }
                },

                error: function (xhr) {
                    $('#loading_modal').modal('hide');
                    let msg = 'Terjadi kesalahan';

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        msg = Object.values(errors).map(e => e[0]).join('\n');
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }

                    alert(msg);
                },
            });
        }
    });
    // ============================ End Of Submit Form Surjal ==================================
    // =============================== Clear Form Surjal ==================================
    $('#btnClearRentPendinginSurjal').on('click', function () {
        clearRentDinginForm();
        setButtonToSaveMode();
    });
    // ============================ End Of Clear Form Surjal ================================
    // ================================= Pilih No Surat Jalan =====================================
    $('#no_surjal_rent_dingin_btn').click(function(e) {
        e.preventDefault();
        $('#surjalModalRentPendingin').modal('show');
        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#modalSurjalRentDgnTable')) {
            $('#modalSurjalRentDgnTable').DataTable().destroy();
        }
        var tableSurjalRentDgn = $('#modalSurjalRentDgnTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
            url: "{{ route('rentPendingin-surjal.data') }}",
                data: function(d) {
                    d.tgl_mulai = $('#filter_tgl_mulai').val();
                    d.tgl_akhir = $('#filter_tgl_akhir').val();
                    d.search_muat = $('#filter_surjal_rent_dgn').val();
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
                { data: 'CUSTOMER', name: 'CUSTOMER' },
                { data: 'PESANAN', name: 'PESANAN' },
                { data: 'JUMLAH', name: 'JUMLAH' },
                { data: 'harga_formatted', name: 'HARGA' },
                { data: 'DISC', name: 'DISC' },
                { data: 'total_formatted', name: 'GRAND' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#btn_filter_surjal_rent_dgn').click(function () {
            tableSurjalRentDgn.ajax.reload();
        });
    });
    // ============================= End Of Pilih No Surat Jalan =====================================
    // =============================== Show Form Surjal ==================================
    $(document).on('click', '.pickSurjalRentDgn', function (e) {
        e.preventDefault();
        const nosj = $(this).data('nosj');

        if (!nosj) {
            alert('No Surat Jalan tidak ditemukan');
            return;
        }

        const surjalDataUrlRentDingin = "{{ route('rentPendinginSurjal.show', ['nosj' => ':nosj']) }}";
        const url = surjalDataUrlRentDingin.replace(':nosj', nosj);
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                // optional loading
                $('#loadingModal').modal('show');
            },
            success: function (res) {
                if (!res.success) {
                    alert(res.message || 'Data tidak ditemukan');
                    return;
                }
                $('#surjalModalRentPendingin').modal('hide');
                const d = res.data;

                // =========================
                // ISI FORM
                // =========================
                $('#no_surjal_rent_dingin').val(d.NOSJ);
                $('#tanggal_surjal_rent_dingin').val(d.tglsj);
                $('#wilayah_nosj_rent_dingin').val(d.WILAYAH);

                // CUSTOMER
                $('#customer_rent_dingin_id').val(d.CUSTOMER_KODE);
                $('#customer_rent_dingin').val(d.CUSTOMER);
                $('#customer_kode_dingin').val(d.CUSTOMER_KODE);
                $('#nama_penerima_rent_dingin').val(d.P_NAMA ?? '');
                $('#telpon_rent_dingin').val(d.P_PHONE ?? '');
                $('#alamat_rent_dingin').val(d.P_ALAMAT ?? '');

                // ITEM
                $('#item_rent_dingin').val(d.PESANAN);
                $('#item_rent_dingin_id').val(d.PESANAN);

                // DRIVER & KENDARAAN
                $('#driver_rent_dingin_id').val(d.DRIVER);
                $('#driver_rent_dingin').val(d.NAMA_DRIVER);
                $('#kendaraan_rent_dingin_id').val(d.KENDARAAN);
                $('#kendaraan_rent_dingin').val(d.NAMA_KENDARAAN);

                // PERHITUNGAN
                $('#jml_hari_rent_dingin').val(toFloat(d.JUMLAH));
                $('#harga_rent_dingin').val(formatRupiah(d.HARGA));
                $('#discount_rent_dingin').val(toFloat(d.DISC)); // persen
                $('#sub_total_rent_dingin').val(formatRupiah(d.JUMLAH * d.HARGA));
                $('#dpp_rent_dingin').val(formatRupiah(d.TOTAL));
                $('#pajak_rent_dingin').val(toFloat(d.PPN));
                $('#total_rent_dingin').val(formatRupiah(d.GRAND));

                $('#keterangan_rent_dingin').val(d.catatan);

                // Button Print PDF
                $('#divPrintSuratJalanRent').removeClass('d-none');
                $('#btnPrintSuratJalanRent').attr('data-sj',nosj);

                // Hitung ulang biar konsisten
                calculateTotalDgn();
                setButtonToUpdateMode();
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Gagal mengambil data');
            },
            complete: function () {
                $('#loadingModal').modal('hide');
            }
        });
    });
    // ============================ End Of Show Form Surjal ==================================
    // ================================ Delete No Surat Jalan ======================================
    $(document).on('click', '.deleteSurjalRentDgn', function () {
        let id     = $(this).data('id');
        let nosj = $(this).data('nosj');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'No Muat ' + nosj + ' akan dihapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                let url = "{{ route('rentPendinginSurjal.destroy', ':id') }}";
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
                        $('#modalSurjalRentDgnTable').DataTable().ajax.reload(null, false);
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
    // =============================== Submit Form Muat ==================================
    $('#btnMuatRentPendingin').on('click', function () {
        let formData = {
            _token: $('input[name="_token"]').val(),

            no_muat_rent_dingin: $('#no_muat_rent_dingin').val(),
            tanggal_rent_dingin: $('#tanggal_rent_dingin').val(),

            no_surjal_rent_dingin: $('#no_surjal_rent_dingin').val(),
            tanggal_surjal_rent_dingin: $('#tanggal_surjal_rent_dingin').val(),
            wilayah_nosj_rent_dingin: $('#wilayah_nosj_rent_dingin').val(),

            customer_rent_dingin_id: $('#customer_rent_dingin_id').val(),
            customer_rent_dingin: $('#customer_rent_dingin').val(),
            nama_penerima_rent_dingin: $('#nama_penerima_rent_dingin').val(),
            telpon_rent_dingin: $('#telpon_rent_dingin').val(),
            alamat_rent_dingin: $('#alamat_rent_dingin').val(),

            item_rent_dingin: $('#item_rent_dingin').val(),

            jml_hari_rent_dingin: $('#jml_hari_rent_dingin').val(),
            harga_rent_dingin: $('#harga_rent_dingin').val(),
            discount_rent_dingin: $('#discount_rent_dingin').val(),
            pajak_rent_dingin: $('#pajak_rent_dingin').val(),
            total_rent_dingin: $('#total_rent_dingin').val(),

            driver_rent_dingin_id: $('#driver_rent_dingin_id').val(),
            driver_rent_dingin: $('#driver_rent_dingin').val(),

            kendaraan_rent_dingin_id: $('#kendaraan_rent_dingin_id').val(),
            kendaraan_rent_dingin: $('#kendaraan_rent_dingin').val(),

            keterangan_rent_dingin: $('#keterangan_rent_dingin').val(),
            KETERANGAN: 'REN ' + ($('#item_rent_dingin').val() || '') + ' ' + ($('#customer_rent_dingin').val() || '') + ' KE ' + ($('#nama_penerima_rent_dingin').val() || ''),
        };
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            // Jalankan setelah modal benar2 muncul
            submitFormMuatRentDgn();
        });
        function submitFormMuatRentDgn(){
            let nosj = $('#no_surjal_rent_dingin').val();
            let url = "{{ route('rentPendinginMuat.update', ':nosj') }}";
            url = url.replace(':nosj', nosj);

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (res) {
                    if (res.success) {
                        $('#loading_modal').modal('hide');
                        if($('#no_surjal_rent_dingin').val() != ""){
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Diupdate!',
                                text: `Data berhasil diupdate dengan NOSJ: ${res.data.NOSJ}`,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#2add69'
                            });
                        }else{
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disimpan!',
                                text: `Data berhasil disimpan dengan NOSJ: ${res.data.NOSJ}`,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6'
                            });
                        }

                        clearRentDinginForm();
                        setButtonToSaveMode();
                        // reset form kalau mau
                        // $('#formRentPendingin')[0].reset();
                    }
                },

                error: function (xhr) {
                    $('#loading_modal').modal('hide');
                    let msg = 'Terjadi kesalahan';

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        msg = Object.values(errors).map(e => e[0]).join('\n');
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }

                    alert(msg);
                },
            });
        }
    });
    // ============================ End Of Submit Form Muat ==================================
    // =========================== Print PDF ================================
    $('#btnPrintSuratJalanRent').on('click', function () {
        let sj = $(this).attr('data-sj');
        let url = "{{ route('rentPendingin.printSuratJalan', ':sj') }}";
        url = url.replace(':sj', sj);
        window.open(url, '_blank');
    });
    // ======================== End Of Print PDF =============================
});
    // ########################################################################
    // FUNCTION HELPER:
    // ########################################################################
    // =================== Pajak PPN ==========================
    function loadInputPajakDgn(){
        $.ajax({
            url: '{{ route('get_pajak') }}',
            type: 'GET',
            success: function(response) {
                let nilai_ppn = response.data.ppn;
                $('#pajak_rent_dingin').val(nilai_ppn);
            },
            error: function() {
                $('#ppn_expedisi').val('Error Loading');
            }
        });
    }

    // =================== Hitung Total Form ==========================
    function unformatRupiah(value) {
        if (!value) return 0;
        return parseInt(value.replace(/[^\d]/g, '')) || 0;
    }

    function formatRupiah(value) {
        return value.toLocaleString('id-ID');
    }

    function toFloat(value) {
        if (value === null || value === undefined) return 0;
        return parseFloat(value) || 0;
    }

    function calculateTotalDgn() {
        // =============================
        // AMBIL NILAI DARI FORM
        // =============================
        const harga = unformatRupiah($('#harga_rent_dingin').val()) || 0;
        const hari  = parseInt($('#jml_hari_rent_dingin').val()) || 0;

        // discount = PERSEN (misal 10)
        const discountPercent = parseFloat($('#discount_rent_dingin').val()) || 0;

        // pajak = PERSEN (misal 11)
        const pajakPercent = parseFloat($('#pajak_rent_dingin').val()) || 0;

        // =============================
        // PERHITUNGAN
        // =============================

        // 1. Sub Total
        const subTotal = harga * hari;

        // 2. Discount dalam RUPIAH
        const discountAmount = subTotal * (discountPercent / 100);

        // 3. DPP (setelah discount)
        const dpp = subTotal - discountAmount;

        // 4. Pajak
        const pajakAmount = dpp * (pajakPercent / 100);

        // 5. Total
        const total = dpp + pajakAmount;

        // =============================
        // SET KE FORM
        // =============================
        $('#sub_total_rent_dingin').val(formatRupiah(Math.round(subTotal)));
        $('#dpp_rent_dingin').val(formatRupiah(Math.round(dpp)));
        $('#total_rent_dingin').val(formatRupiah(Math.round(total)));
    }

    function clearRentDinginForm() {
        // Reset No. Muat dan Tanggal
        $('#no_muat_rent_dingin').val('');
        $('#tanggal_rent_dingin').val(new Date().toISOString().split('T')[0]);
        // Reset No. Surat Jalan dan wilayah
        $('#no_surjal_rent_dingin').val('');
        $('#tanggal_surjal_rent_dingin').val(new Date().toISOString().split('T')[0]);
        $('#wilayah_nosj_rent_dingin').val('DENPASAR'); // Reset ke default

        // Reset Customer Data
        $('#customer_rent_dingin_id').val('');
        $('#customer_rent_dingin').val('');
        $('#customer_kode_dingin').val('');
        $('#nama_penerima_rent_dingin').val('');
        $('#telpon_rent_dingin').val('');
        $('#alamat_rent_dingin').val('');

        // Reset Item
        $('#item_rent_dingin_id').val('');
        $('#item_rent_dingin').val('');
        $('#jml_hari_rent_dingin').val('');
        $('#harga_rent_dingin').val('');

        // Reset Driver dan Kendaraan
        $('#driver_rent_dingin_id').val('');
        $('#driver_rent_dingin').val('');
        $('#kendaraan_rent_dingin_id').val('');
        $('#kendaraan_rent_dingin').val('');

        // Reset Perhitungan
        $('#sub_total_rent_dingin').val('0');
        $('#discount_rent_dingin').val('0');
        $('#dpp_rent_dingin').val('0');
        loadInputPajakDgn();
        $('#keterangan_rent_dingin').val('');
        $('#total_rent_dingin').val('0');

        // Focus ke field pertama
        // $('#no_muat_rent_dingin').focus();
    }

    function setButtonToUpdateMode() {
        $('#btnSimpanRentPendinginSurjalDiv').removeClass('d-none');
        $('#btnSimpanRentPendinginSurjal')
            .removeClass('btn-info')
            .addClass('btn-success')
            .html('<i class="bx bx-edit me-1"></i>UPDATE')
            .attr('title', 'Update data ekspedisi')
            .data('mode', 'update')

        // Tambahkan badge info
        $('#btnSimpanRentPendinginSurjal').append('<span class="badge bg-light text-dark ms-2">EDIT MODE</span>');
        // aktifkan button papend to table
        if (userRole === 'admin') {
            $('#btnMuatRentPendinginDiv').removeClass('d-none');
            $('#btnMuatRentPendingin').removeClass('btn-success').addClass('btn-primary').html('<i class="bx bx-car me-1"></i>PROSES NOMUAT');
        }
    }

    // Fungsi untuk mengubah tombol ke mode CREATE/SIMPAN
    function setButtonToSaveMode() {
        $('#btnSimpanRentPendinginSurjalDiv').removeClass('d-none');
        $('#btnSimpanRentPendinginSurjal')
            .removeClass('btn-success')
            .addClass('btn-info')
            .html('<i class="bx bx-save me-1"></i>SIMPAN')
            .attr('title', 'Simpan data ekspedisi baru')
            .data('mode', 'save')
            .data('id', '')
            .find('.badge').remove(); // Hapus badge jika ada
            // $('#divPrintSuratJalan').addClass('d-none');
            // aktifkan button papend to table
            $('#btnMuatRentPendinginDiv').addClass('d-none');
            $('#divPrintSuratJalanRent').addClass('d-none');
    }

    // ###Print PDF
    function printSuratJalanRent(sj) {
        let url = "{{ route('rentPendingin.printSuratJalan', ':sj') }}";
        url = url.replace(':sj', sj);
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
</script>
