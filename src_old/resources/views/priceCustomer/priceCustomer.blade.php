<style>
    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        padding: 20px;
    }

    .card-prices-customer-header {
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .card-prices-customer-header h5 {
        color: #007bff;
        margin: 0;
    }

    /* Untuk mobile */
    @media (max-width: 576px) {
        .card-prices-customer-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-prices-customer-header > div:last-child {
            align-self: flex-end;
        }
    }

    /* Keterangan Customer */
    #customerInfo {
    background: #f1faff;
    border-left: 5px solid #0d6efd;
    border-radius: 6px;
    }
    #customerInfo span {
        font-size: 15px;
        color: #333;
    }
    #customerInfo strong {
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
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-prices-customer-header">
                    <div>
                        <h5>Harga Customer</h5>
                    </div>
                </div>
                <div class="card-body">
                    {{-- View Table Cust All --}}
                    <div class="table-responsive" id="priceCusTableMaster">
                        <table class="table table-bordered table-striped" id="priceCusTable">
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
                            <tbody>
                                <!-- Data akan di-load oleh DataTables -->
                            </tbody>
                        </table>
                    </div>

                    {{-- View Table Detail Cust --}}
                    <div id="priceCusTableDetMaster" style="display:none;">
                        <div class="col-12 d-flex justify-content-start">
                            <button class="btn btn-link text-decoration-none p-0" id="returnPrcCusBtn" style="color: #107af3;">
                                <i class='bx bx-chevron-left'></i> Kembali ke Daftar
                            </button>
                        </div>
                        <div id="customerInfo" class="card shadow-sm p-3" style="display:none;">
                            <h5 class="mb-3">
                                <i class="bx bx-user"></i> Informasi Customer
                            </h5>
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <strong>Nama Customer:</strong><br>
                                    <span id="custName"></span>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Kode Customer:</strong><br>
                                    <span id="custKode"></span>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Jenis Usaha:</strong><br>
                                    <span id="jenisUsaha"></span>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Nama Pemilik:</strong><br>
                                    <span id="pemilikNama"></span>
                                </div>
                                <div class="col-12">
                                    <strong>Alamat:</strong><br>
                                    <span id="alamat"></span>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-success" id="update_rute_customer">
                                        + Update Harga Rute
                                    </button>
                                    <button class="btn btn-primary" id="add_rute_customer" hidden disabled>
                                        + Tambah Harga Rute
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table id="priceCusTableDet" class="table table-striped table-bordered w-100">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>No</th>
                                                <th>NAMA ITEM</th>
                                                <th>DARI</th>
                                                <th>SAMPAI</th>
                                                <th>RUTE</th>
                                                <th>HARGA</th>
                                                <th>JENIS</th>
                                                <th>AKSI</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Form Add Customer Price -->
                <div id="formCusContainer" class="card bg-light mb-3" style="display: none;">
                    <div class="card-body">
                        <form id="priceFormCus">
                            @csrf
                            <input type="hidden" id="priceCusId" name="id">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="kode_val_pricecus" id="kode_val_pricecus">
                                        <input class="form-control" type="text" name="name_pricecus" id="name_pricecus" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">RUTE</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="hidden" name="rute_val_pricecus" id="rute_val_pricecus">
                                            <input class="form-control" type="text" name="rute_pricecus" id="rute_pricecus" placeholder="Tekan Pilih !!!" required readonly>
                                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ruteCusModal">
                                                <i class="fas fa-plus"></i> Pilih
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">DARI - SAMPAI (Kg)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="number" id="dari_pricecus" name="dari_pricecus"
                                                    class="form-control" placeholder="0" required>
                                            <span class="fw-semibold">-</span>
                                            <input type="number" id="sampai_pricecus" name="sampai_pricecus"
                                                    class="form-control" placeholder="0" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">JENIS</label>
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="form-check" style="margin-bottom: 0;">
                                                <input class="form-check-input" type="radio" name="JENIS" value="E" id="jenisEcerancus" required checked>
                                                <label class="form-check-label" for="jenisEcerancus">
                                                    Eceran
                                                </label>
                                            </div>
                                            <div class="form-check" style="margin-bottom: 0;">
                                                <input class="form-check-input" type="radio" name="JENIS" value="B" id="jenisBokingcus" required>
                                                <label class="form-check-label" for="jenisBokingcus">
                                                    Boking
                                                </label>
                                            </div>
                                            <div style="flex: 0 0 150px;">
                                                <input type="number" id="jenis_valcus" name="jenis_valcus" class="form-control" placeholder="" value="1" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nama Item</label>
                                        <input type="text" id="keterangan_pricecus" name="keterangan_pricecus"
                                                class="form-control" placeholder="Contoh: EC > 40 KG" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">HARGA (KG)</label>
                                        <input type="number" id="harga_pricecus" name="harga_pricecus"
                                                class="form-control" placeholder="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <button type="button" id="cancelBtnCus"
                                        class="btn btn-secondary">
                                    Batal
                                </button>
                                <button type="submit" id="submitBtnCus"
                                        class="btn btn-primary">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal Tabel Rute -->
<!-- Modal -->
<div class="modal fade" id="ruteCusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Rute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0 text-dark">Data Rute</h2>
                    <button class="btn btn-primary btn-sm" id="tambah_rute_cus">
                        <i class="fas fa-plus me-1"></i>Tambah Rute
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="ruteCusTable" class="table table-striped table-bordered table-hover w-100">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th>RUTE</th>
                                <th>Tanggal Dibuat</th>
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

<!-- Modal Tambah Rute -->
<div class="modal fade" id="addRuteCusModal" tabindex="-1" aria-labelledby="addRuteCusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRuteCusModalLabel">Tambah Rute Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="add_rute_cus_flag">
                <form id="ruteFormCus">
                    @csrf
                    <div class="mb-3">
                        <label for="newCusRute" class="form-label">Nama Rute</label>
                        <input type="text" class="form-control" id="newCusRute" name="newCusRute" placeholder="Contoh: DIY - DPS" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="ruteFormCusBtn">Simpan</button>
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
    var table = $('#priceCusTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("price-customer.data") }}',
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
    $(document).on("click", ".view-btn-customer-price", function() {

        let kodecus = $(this).data("id"); // ambil kode customer

        $("#priceCusTableMaster").hide();
        $("#priceCusTableDetMaster").show();

        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#priceCusTableDet')) {
            $('#priceCusTableDet').DataTable().destroy();
        }

        // rebuild datatable
        $('#priceCusTableDet').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('price-customer.price', ':kode') }}".replace(':kode', kodecus),
                dataSrc: function (json) {
                    // SET INFO CUSTOMER DI ATAS TABEL
                    $("#customerInfo").show();
                    $("#custName").text(json.customer_nama);
                    $("#custKode").text(json.customer_kode);
                    $("#jenisUsaha").text(json.jenis_usaha ?? '-');
                    $("#alamat").text(json.alamat ?? '-');
                    $("#pemilikNama").text(json.pemilik_nama ?? '-');
                    return json.data;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'KETERANGAN' },
                { data: 'DARI' },
                { data: 'SAMPAI' },
                { data: 'nama_rute' },
                { data: 'harga_html', orderable: false, searchable: false },
                { data: 'jenis_text' },
                { data: 'aksi', orderable: false, searchable: false }
            ]
        });

    });
    // ======================== End Of Initialize DataTables Detail Cus ==============================
    // =============================== Return Table Price Dingin Cus =====================================
    $('#returnPrcCusBtn').on('click', function () {
        $("#custName").text('');
        $("#custKode").text('');
        $("#jenisUsaha").text('');
        $("#alamat").text('');
        $("#pemilikNama").text('');
        // reload table
        $('#priceCusTableDet').DataTable().clear().draw();

        // reset form
        resetFormCus();
        $("#priceCusTableDetMaster").hide();
        $("#priceCusTableMaster").show();

        $('#priceCusTable').DataTable().ajax.reload();
    });
    // ============================ End Of Return Table Dingin Cus ==================================
    // ========================= Update All Harga Customer Click ================================
    $('#update_rute_customer').click(function () {
        let kodecus = $('#custKode').text();
        Swal.fire({
            title: 'Update Harga Customer?',
            text: 'Harga baru yang belum ada akan ditambahkan ke customer ini.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Update',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
            $('#loading_modal').modal('show');
            $('#loading_modal').one('shown.bs.modal', function () {
                $.post('{{ route("price-customer.update-all") }}', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    kodecus: kodecus
                }, function (res) {
                    $('#loading_modal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#priceCusTableDet').DataTable().ajax.reload();
                }).fail(function(xhr){
                    $('#loading_modal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.'
                    });

                });
            });
        });

    });
    // ========================= End Of Update All Harga Customer Click ================================
    // ================================ Update Row Cust ========================================
    $(document).on("click", ".save-price", function() {
        let btn = $(this);
        let kode = btn.data("kode");
        let kodecus = $("#custKode").text().trim();
        let original = btn.data("original");

        let hargaCell = btn.closest("tr").find(".editable-price");
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
                    url: "{{ route('price-customer.update-row') }}",
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
    $('#add_rute_customer').on('click', function(){
        $('#formCusContainer').show();
        resetFormCus();
        $('#priceCusTableDetMaster').hide();
        $("#name_pricecus").val($("#custName").text());
        $("#kode_val_pricecus").val($("#custKode").text());
    });
    // ================================ End Of Tambah Price Cust ========================================
    // ================================ Cancel Price Cust ========================================
    $('#cancelBtnCus').on('click', function(){
        resetFormCus();
        $('#formCusContainer').hide();
        $('#priceCusTableDetMaster').show();
        $('#priceCusTableDet').DataTable().ajax.reload();
    });
    // ================================ End Of Cancel Price Cust ========================================
    // ============================ Tambah Rute Click ===================================
    $("#tambah_rute_cus").on('click', function(){
        // Tutup modal pertama dengan method Bootstrap
        $('#ruteCusModal').modal('hide');

        // Buka modal kedua dengan method Bootstrap
        $('#addRuteCusModal').modal('show');
        $('#add_rute_cus_flag').val('');
        $('#newCusRute').val('');
    });
    // ========================= End Of Tambah Rute Click ================================
    // ============================ Rute Table ===================================
     $('#ruteCusTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("rute.data") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'RUTE', name: 'RUTE' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '20%'}
        ]
    });
    // ============================ End Of Rute Table ===================================
    // =========================== Form Prices submission ====================================
    $('#priceFormCus').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        // var url = $('#priceId').val() ? '{{ route("price-expedition.update", ["id" => ":id"]) }}'.replace(':id', $('#priceId').val()) : '{{ route("price-expedition.store") }}';

        $.ajax({
            url: '{{ route("price-customer.store") }}',
            method: 'POST',
            data: formData,
            processData: false,   // WAJIB
            contentType: false,   // WAJIB
            success: function(response) {
                if (response.success) {
                    Swal.fire('Sukses!', response.message, 'success');
                    resetFormCus();
                    $('#formCusContainer').hide();
                    $('#priceCusTableDetMaster').show();
                    $('#priceCusTableDet').DataTable().ajax.reload();
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    // Clear previous errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    // Display new errors
                    $.each(errors, function(field, messages) {
                        var input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                    });
                }
            }
        });
    });
    // ========================= End Of Form Prices submission ==================================
    // ============================ Reset form ========================================
    function resetFormCus() {
        $('#priceFormCus')[0].reset();
        $('#priceCusId').val('');
        $('#submitBtnCus').text('Simpan');
        $('#jenis_valcus').val(1);
        // Clear validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }
    // ======================== End Of Reset form ======================================
    // ======================== Submit Rute form ======================================
    $('#ruteFormCusBtn').on('click', function(e) {
        e.preventDefault();
        var newRute = $('#newCusRute').val();
        console.log('new rute: ' + newRute); // harus mengandung newRute=

        var url = $('#add_rute_cus_flag').val()
            ? '{{ route("rute.update", ["id" => ":id"]) }}'.replace(':id', $('#add_rute_cus_flag').val())
            : '{{ route("rute.store") }}';

        $.ajax({
            url: url,
            method: 'POST',
            data: {newRute: newRute},
            success: function(response) {
                $('#ruteCusTable').DataTable().ajax.reload();
                // Tutup modal
                $('#addRuteCusModal').modal('hide');
                $('#rute_val_pricecus').val(response.id);
                $('#rute_pricecus').val(response.data.RUTE);
                Swal.fire('Sukses!', response.data.RUTE, 'success');
            },
            error: function(xhr) {
                console.log(xhr.responseJSON);
            }
        });
    });
    // ======================== End Of Submit Rute form ======================================
});

function pickDataRute(id, ruteText) {
    $('#ruteCusModal').modal('hide');
    $('#rute_val_pricecus').val(id);
    $('#rute_pricecus').val(ruteText);
}

function editDataRute(id) {
    $('#ruteCusModal').modal('hide');
    $.ajax({
        url: '{{ route("rute.show", ["id" => ":id"]) }}'.replace(':id', id),
        type: 'GET',
        success: function(response) {
            $('#addRuteCusModal').modal('show');
            $('#newCusRute').val(response.data.RUTE);
            $('#add_rute_cus_flag').val(response.data.id);
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat memuat data');
        }
    });
}

</script>
