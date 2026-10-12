<style>
    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        padding: 20px;
    }

    .card-prices-customer-dingin-header {
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .card-prices-customer-dingin-header h5 {
        color: #007bff;
        margin: 0;
    }

    /* Untuk mobile */
    @media (max-width: 576px) {
        .card-prices-customer-dingin-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-prices-customer-dingin-header > div:last-child {
            align-self: flex-end;
        }
    }

    /* Keterangan Customer */
    #customerDinginInfo {
    background: #f1faff;
    border-left: 5px solid #0d6efd;
    border-radius: 6px;
    }
    #customerDinginInfo span {
        font-size: 15px;
        color: #333;
    }
    #customerDinginInfo strong {
        color: #0d6efd;
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
</style>
<div class="card">

    <!-- HEADER -->
    <div class="card-prices-customer-dingin-header">
        <h5 class="mb-0">Harga Mobil Dingin Customer</h5>
    </div>

    <!-- BODY -->
    <div class="card-body">

        <!-- TABLE MASTER -->
        <div class="table-responsive" id="priceCusDinginTableMaster">
            <table class="table table-bordered table-striped w-100" id="priceCusDinginTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Jenis Usaha</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- DETAIL -->
        <div id="priceCusDinginTableDetMaster" style="display:none;">
            <div class="col-12 d-flex justify-content-start">
                <button class="btn btn-link text-decoration-none p-0" id="returnPrcDgnCusBtn" style="color: #107af3;">
                    <i class='bx bx-chevron-left'></i> Kembali ke Daftar
                </button>
            </div>
            <div id="customerDinginInfo" class="card shadow-sm p-3 mb-3" style="display:none;">
                <h5 class="mb-3">
                    <i class="bx bx-user"></i> Informasi Customer
                </h5>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Nama Customer:</strong><br>
                        <span id="custDinginName"></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Kode Customer:</strong><br>
                        <span id="custDinginKode"></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Jenis Usaha:</strong><br>
                        <span id="jenisDinginUsaha"></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Nama Pemilik:</strong><br>
                        <span id="pemilikDinginNama"></span>
                    </div>
                    <div class="col-12">
                        <strong>Alamat:</strong><br>
                        <span id="alamatDingin"></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-success" id="update_dingin_customer">
                            + Update Harga Sewa
                        </button>
                        <button class="btn btn-primary" id="add_dingin_customer" hidden disabled>
                            + Tambah Harga Sewa
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="priceCusDinginTableDet"
                               class="table table-striped table-bordered w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Kendaraan</th>
                                    <th>ITEM</th>
                                    <th>PERIODE</th>
                                    <th>PLAT</th>
                                    <th>JENIS</th>
                                    <th>HARGA</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <!-- Form Input -->
        <div id="formCustomerDinginContainer" class="form-section" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Form Data Sewa Mobil Dingin Customer</h4>
            </div>

            <form id="pricedinginCustomerForm">
                @csrf
                <input type="hidden" id="pricePendinginCustomerId" name="pricePendinginCustomerId">

                <div class="row g-4">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                            <input type="text" name="kodecus_pricedingin_cus" id="kodecus_pricedingin_cus" hidden readonly>
                            <input type="text" id="nama_pricedingin_cus" name="nama_pricedingin_cus"
                                class="form-control" required readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kendaraan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="kode_pricedingin_cus" name="kode_pricedingin_cus"
                                    class="form-control" placeholder="Silahkan Tekan Pilih" readonly required>
                                <button type="button" class="btn btn-outline-primary" id="btnPilihJenisDingin" data-bs-toggle="modal" data-bs-target="#kendaraanDinginCustomerModal">
                                    <i class="bi bi-search me-1"></i> Pilih
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">PLAT <span class="text-danger">*</span></label>
                            <input type="text" id="plat_pricedingin_cus" name="plat_pricedingin_cus"
                                class="form-control" required readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">JENIS MOBIL<span class="text-danger">*</span></label>
                            <input type="text" id="jenis_pricedingin_cus" name="jenis_pricedingin_cus"
                                class="form-control" required readonly>
                        </div>

                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">PERIODE <span class="text-danger">*</span></label>
                            <input type="text" id="periode_pricedingin_cus" name="periode_pricedingin_cus"
                                class="form-control" placeholder="Contoh: PERIODE-2024" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">NAMA ITEM <span class="text-danger">*</span></label>
                            <input type="text" id="item_pricedingin_cus" name="item_pricedingin_cus"
                                class="form-control" required  readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">HARGA <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="harga_pricedingin_cus" name="harga_pricedingin_cus"
                                    class="form-control" placeholder="0"
                                    min="0" step="1" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" id="cancelDinginCusBtn" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" id="submitDinginCusBtn" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- Modal Tabel Rute -->
    <div class="modal fade" id="kendaraanDinginCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Data Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0 text-dark">Data Kendaraan</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="kendaraanDinginCustomerTable" class="table table-striped table-bordered table-hover w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama</th>
                                    <th>Plat</th>
                                    <th>Jenis</th>
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

<script>
$(document).ready(function() {
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // =============================== Initialize DataTables ====================================
    var table = $('#priceCusDinginTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("price-rentcus.data") }}',
        // Scroll settings
        scrollX: true,
        scrollY: "400px",
        scrollCollapse: true,
        // Responsive settings
        responsive: true,
        autoWidth: true,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'CUSTOMER', name: 'CUSTOMER' },
            { data: 'NAMACUST', name: 'NAMACUST' },
            { data: 'TYPECUST', name: 'TYPECUST' },
            { data: 'TELEPON', name: 'TELEPON' },
            { data: 'EMAIL', name: 'EMAIL' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    // ============================= End Of Initialize DataTables =================================
    // =========================== Initialize DataTables Detail Cus ===============================
    $(document).on("click", ".view-btn-customer-price-dingin", function (e) {
        e.preventDefault();

        let kodecus = $(this).data("id");

        $("#priceCusDinginTableMaster").hide();
        $("#priceCusDinginTableDetMaster").show();

        if ($.fn.DataTable.isDataTable('#priceCusDinginTableDet')) {
            $('#priceCusDinginTableDet').DataTable().destroy();
        }

        $('#priceCusDinginTableDet').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('price-rentcus.price', ':kode') }}".replace(':kode', kodecus),
                dataSrc: function (json) {
                    $("#customerDinginInfo").show();
                    $("#custDinginName").text(json.customer_nama);
                    $("#custDinginKode").text(json.customer_kode);
                    $("#jenisDinginUsaha").text(json.jenis_usaha ?? '-');
                    $("#alamatDingin").text(json.alamat ?? '-');
                    $("#pemilikDinginNama").text(json.pemilik_nama ?? '-');
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
    // ======================== End Of Initialize DataTables Detail Cus ==============================
    // =============================== Return Table Price Dingin Cus =====================================
    $('#returnPrcDgnCusBtn').on('click', function () {
        // reload table
        $('#priceCusDinginTableDet').DataTable().clear().draw();

        // reset form
        resetFormDinginCus();
        $("#priceCusDinginTableDetMaster").hide();
        $("#priceCusDinginTableMaster").show();

        $('#priceCusDinginTable').DataTable().ajax.reload();
    });
    // ============================ End Of Return Table Dingin Cus ==================================
    // ================================ Update All Price Cust =====================================
    $('#update_dingin_customer').click(function () {
    let kodecus = $('#custDinginKode').text();
    Swal.fire({
        title: 'Update Harga Dingin Customer?',
        text: 'Harga dingin baru yang belum ada akan ditambahkan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Update',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.post('{{ route("price-rentcus.update-all") }}', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                kodecus: kodecus
            }, function (res) {
                $('#loading_modal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message
                });
                $('#priceDinginTableDet').DataTable().ajax.reload();
            });
        });
    });

});
    // ================================ End Of Update All Price Cust =====================================
    // ================================ Update Row Cust ========================================
    $(document).on("click", ".save-price-dingin", function() {
        let btn = $(this);
        let kode = btn.data("kode");
        let kodecus = $("#custDinginKode").text().trim();
        let original = btn.data("original");

        let hargaCell = btn.closest("tr").find(".editable-price-dingin");
        let hargaBaru = hargaCell.text().trim();

        if (isNaN(hargaBaru) || hargaBaru === "") {
            Swal.fire("Error", "Harga harus angka!", "error");
            hargaCell.text(original);
            return;
        }

        Swal.fire({
            title: "Simpan Perubahan?",
            text: "Harga akan disimpan ke pricecus.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, simpan",
            cancelButtonText: "Batal"
        }).then((result) => {

            if (!result.isConfirmed) {
                hargaCell.text(original);
                return;
            }
            $('#loading_modal').modal('show');
            $('#loading_modal').one('shown.bs.modal', function () {
                $.ajax({
                    url: "{{ route('price-rentcus.update-row') }}",
                    method: "POST",
                    data: {
                        kode: kode,
                        kodecus: kodecus,
                        harga: hargaBaru,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        $('#loading_modal').modal('hide');
                        Swal.fire("Berhasil", res.message, "success");
                        btn.data("original", hargaBaru);
                        hargaCell.attr("data-original", hargaBaru);
                    },
                    error: function() {
                        $('#loading_modal').modal('hide');
                        Swal.fire("Error", "Gagal update harga!", "error");
                        hargaCell.text(original);
                    }
                });
            });
        });
    });
    // ========================= End Of Update Row Cust =====================================
    // ================================ Tambah Price Cust ========================================
    $('#add_dingin_customer').on('click', function(){
        $('#formCustomerDinginContainer').show();
        resetFormDinginCus();
        $('#priceCusDinginTableDetMaster').hide();
        $("#nama_pricedingin_cus").val($("#custDinginName").text());
        $("#kodecus_pricedingin_cus").val($("#custDinginKode").text());
    });
    // ================================ End Of Tambah Price Cust ========================================
    // ================================ Cancel Price Cust ========================================
    $('#cancelBtnCus').on('click', function(){
        resetFormDinginCus();
        $('#formCustomerDinginContainer').hide();
        $('#priceCusDinginTableDetMaster').show();
        $('#priceCusDinginTableDet').DataTable().ajax.reload();
    });
    // ================================ End Of Cancel Price Cust ========================================
    // ============================ Reset form ========================================
    function resetFormDinginCus() {
        $('#pricedinginCustomerForm')[0].reset();
        $('#pricePendinginCustomerId').val('');
        $('#submitDinginCusBtn').text('Simpan Data');
        // Clear validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }
    // ======================== End Of Reset form ======================================
     // ============================ Kendaraan Table ===================================
     $('#kendaraanDinginCustomerTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("kendaraan.datamodel") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'NAMA', name: 'NAMA' },
            { data: 'PLAT', name: 'PLAT' },
            { data: 'JENIS', name: 'JENIS' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '20%'}
        ]
    });
    // ============================ End Of Kendaraan Table ===================================
    // =============================== Pick Kendaraan =======================================
    $(document).on('click', '#pickKendaraanDingin', function() {
        var id = $(this).data('id');
        $('#kendaraanDinginCustomerModal').modal('hide');

        $.get("{{ route('kendaraan.edit', ':id') }}".replace(':id', id), function(data) {
            // console.log("aba" + data.id + data.kode + data.nama);
            // $('#kendaraanDinginCustomerModal').modal('hide');
            $('#kode_pricedingin_cus').val(data.KODE);
            $('#jenis_pricedingin_cus').val(data.NAMA);
            $('#plat_pricedingin_cus').val(data.PLAT);
            $('#item_pricedingin_cus').val(data.PLAT +' '+ data.JENIS);
        });
    });
    // ============================ End Of Pick Kendaraan ===================================
    // =================== Event listener field periode_pricedingin ==============================
    $('#periode_pricedingin_cus').on('input', function () {
        let periode = $(this).val() || '';
        let item = $('#item_pricedingin_cus').val() || '';

        item = item.replace(/\s\d+$/, ''); // hapus periode lama
        $('#item_pricedingin_cus').val(item + ' ' + periode);
    });

    // =============== End Of Event listener field periode_pricedingin ===========================
    // ============================ Submit form pricedingin ======================================
    $('#pricedinginCustomerForm').on('submit', function(e) {
        e.preventDefault();

        // Menyiapkan data form
        var formData = {
            'KODECUS': $('#kodecus_pricedingin_cus').val(),
            'KODE': $('#kode_pricedingin_cus').val(),
            'PERIODE': $('#periode_pricedingin_cus').val(),
            'PLAT': $('#plat_pricedingin_cus').val(),
            'ITEM': $('#item_pricedingin_cus').val(),
            'HARGA': $('#harga_pricedingin_cus').val(),
            'USER': '{{ Auth::user()->user_id ?? "SYSTEM" }}', // Ganti sesuai kebutuhan
            // USEREDIT dan KUNCI akan diisi nanti saat edit
        };

        var url ='{{ route("price-rentcus.store") }}';

        // AJAX request
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                // Tampilkan notifikasi sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Kode ID :' + response.data.KODEDGN,
                    timer: 2000,
                    showConfirmButton: false
                });
                resetFormDinginCus();
                $('#formCustomerDinginContainer').hide();
                $('#priceCusDinginTableDetMaster').show();
                $('#priceCusDinginTableDet').DataTable().ajax.reload();
            },
            error: function(xhr) {

                // Tampilkan error
                var errors = xhr.responseJSON?.errors;
                var errorMessage = 'Terjadi kesalahan saat menyimpan data.';

                if (errors) {
                    errorMessage = '';
                    $.each(errors, function(key, value) {
                        errorMessage += value[0] + '\n';
                    });
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
            }
        });
    });
    // ======================= End Of Submit form pricedingin ===================================
});
</script>
