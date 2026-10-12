<style>
    .card-driver {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        padding: 20px;
    }

    .card-driver-header {
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .card-driver-header h5 {
        color: #007bff;
        margin: 0;
    }

    /* Untuk mobile */
    @media (max-width: 576px) {
        .card-driver-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-driver-header > div:last-child {
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
</style>
<div class="container mt-4">
    <h2>Data Driver</h2>
    <!-- Tabel Data -->
    <div class="card-driver" id="tableDriver">
            <div class="card-driver-header">
                <div>
                    <h5>Daftar Driver</h5>
                </div>
                {{-- <div>
                    <button class="btn btn-primary" id="add_driver">+ Add Driver</button>
                </div> --}}
            </div>
        <div class="card-driver-body">
            <table class="table table-bordered" id="driverTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Phone</th>
                        <th>Mulai Kerja</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan diisi oleh DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form Input -->
    <div class="card-driver mb-4" style="display: none;" id="formDriver">
        <div class="card-driver-header">
            <h5 id="formTitle">Form Tambah Driver</h5>
        </div>
        <div>
            <button class="btn btn-primary" id="list_driver">List Driver</button>
        </div>
        <div class="card-driver-body">
            <form id="driverForm">
                @csrf
                <input type="hidden" id="id_flag" name="id_flag">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode <span class="text-danger">*</span><small class="text-muted">(Di-generate otomatis oleh sistem, mohon cek kembali !)</small></label>
                            <input type="text" class="form-control" id="kode" name="kode" required maxlength="20" readonly>
                            <div class="form-text text-muted">Kode unik driver (maksimal 20 karakter)</div>
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" required maxlength="20">
                        </div>
                        <div class="mb-3">
                            <label for="mulai_kerja" class="form-label">Mulai Kerja <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="mulai_kerja" name="mulai_kerja" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required maxlength="200"></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">Simpan</button>
                {{-- <button type="button" class="btn btn-secondary" onclick="resetForm()">Batal</button> --}}
            </form>
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

    // ============================ Initialize DataTable ============================
    var table = $('#driverTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('driver.data') }}",
        // Scroll settings
        scrollX: true,
        scrollY: "400px",
        scrollCollapse: true,
        // Responsive settings
        responsive: true,
        autoWidth: false,
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
        // language: {
        //     search: "Cari:",
        //     lengthMenu: "Tampilkan _MENU_ data per halaman",
        //     info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        //     infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
        //     infoFiltered: "(disaring dari _MAX_ total data)",
        //     zeroRecords: "Tidak ada data yang ditemukan",
        //     paginate: {
        //         first: "Pertama",
        //         last: "Terakhir",
        //         next: "Berikutnya",
        //         previous: "Sebelumnya"
        //     }
        // }
    });
    // ======================== End Of Initialize DataTable ==========================

    // ================================== Form Show / Hide ===============================
    // $("#add_driver").click(function(e) {
    //     e.preventDefault();
    //     $("#tableDriver").hide();
    //     $("#formDriver").show();
    //     $("#formTitle").text("Form Tambah Driver");
    //     $("#submitBtn").text("Simpan");
    //     resetForm();
    //     $('#nama').focus();
    //     load_kode_driver();
    //     function load_kode_driver() {
    //         $.ajax({
    //             url: '{{ route('driver_kode') }}', // Route to load the form
    //             type: 'GET',
    //             success: function(response) {
    //                 $('#kode').val(response.kode);
    //             },
    //             error: function() {
    //                 $('#kode').val('<p>Error loading form.</p>');
    //             }
    //         });
    //     }
    // });

    $("#list_driver").click(function(e) {
        e.preventDefault();
        $("#tableDriver").show();
        $("#formDriver").hide();
        table.ajax.reload();
    });
    // ============================== End Of Form Show / Hide ============================

    // ==================== Handle Enter key untuk semua input di form ===================
    $('#driverForm input, #driverForm textarea').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();

            var $inputs = $('#driverForm input, #driverForm textarea');
            var index = $inputs.index(this);
            var nextIndex = index + 1;

            // Jika bukan input terakhir, focus ke next
            if (nextIndex < $inputs.length) {
                $inputs.eq(nextIndex).focus();
            } else {
                // Jika input terakhir, submit form
                $('#driverForm').submit();
            }
        }
    });
    // ================== End Of Handle Enter key untuk semua input di form ===================

    // ============================== Submit Form ============================
    $('#driverForm').on('submit', function(e) {
        e.preventDefault();
        $('#loading_modal').modal('show');

        var formData = new FormData(this);
        var isEdit = $('#id_flag').val() !== '';
        var url = isEdit ? "{{ route('driver.update', ':id') }}".replace(':id', $('#id_flag').val()) : "{{ route('driver.store') }}";

        // Show loading state
        // $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

        setTimeout(function () {
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#loading_modal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: response.message || 'Data berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });

                resetForm();
                $("#tableDriver").show();
                $("#formDriver").hide();
                table.ajax.reload();
            },
            error: function(xhr) {
                $('#loading_modal').modal('hide');
                var errors = xhr.responseJSON.errors;
                var errorMessage = 'Terjadi kesalahan!';

                if (errors) {
                    errorMessage = '';
                    $.each(errors, function(key, value) {
                        errorMessage += value + '<br>';
                    });
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage
                });
            },
            // complete: function() {
            //     $('#submitBtn').prop('disabled', false).text('Simpan');
            // }
        });
        }, 500);
    });
    // ============================ End Of Submit Form ===============================

    // =============================== Edit Data ===============================
    $(document).on('click', '.edit-driver', function() {
        var id = $(this).data('id');
        $("#tableDriver").hide();
        $("#formDriver").show();
        $("#formTitle").text("Form Edit Driver");
        $("#submitBtn").text("Update");

        $.ajax({
            url: "{{ route('driver.edit', ':id') }}".replace(':id', id),
            type: 'GET',
            success: function(data) {
                $('#id_flag').val(data.id);
                $('#kode').val(data.KODE);
                $('#nama').val(data.NAMA);
                $('#alamat').val(data.ALAMAT);
                $('#phone').val(data.PHONE);
                // Format tanggal dari 2025-11-23T00:00:00.000000Z ke 2025-11-23
                if (data.MULAI) {
                    var tanggal = new Date(data.MULAI);
                    var formattedDate = tanggal.toISOString().split('T')[0];
                    $('#mulai_kerja').val(formattedDate);
                } else {
                    $('#mulai_kerja').val('');
                }
                // Scroll ke atas form
                $('html, body').animate({
                    scrollTop: $(".card-driver-header").offset().top
                }, 500);
            },
            error: function() {
                Swal.fire('Error!', 'Gagal memuat data driver', 'error');
            }
        });
    });
    // ============================ End Of Edit Data ============================
    // ============================== Delete Data ==============================
    $(document).on('click', '.delete-driver', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        Swal.fire({
            title: 'Hapus Driver?',
            text: "Anda akan menghapus driver '" + nama + "'. Tindakan ini tidak dapat dibatalkan!",
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
                    url: "{{ route('driver.destroy', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        $('#loading_modal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message || 'Data driver berhasil dihapus',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload();
                    },
                    error: function() {
                        $('#loading_modal').modal('hide');
                        Swal.fire('Error!', 'Gagal menghapus data driver', 'error');
                    }
                });
                }, 500);
            }
        });
    });
    // ============================ End Of Delete Data ============================
});
function resetForm() {
    $('#driverForm')[0].reset();
    $('#id_flag').val('');
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}
</script>
