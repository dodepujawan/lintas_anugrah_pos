<style>
    /* Styling Header */
    .card-price {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        padding: 20px;
    }

    .card-price-header {
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .card-price-header h5 {
        color: #007bff;
        margin: 0;
    }

    /* Untuk mobile */
    @media (max-width: 576px) {
        .card-price-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-price-header > div:last-child {
            align-self: flex-end;
        }
    }
    /* untuk table delete rute agar sweet alert diatas modal rute */
    .high-z-index {
        z-index: 99999 !important;
    }
</style>
<div class="container-fluid py-4">
    <h2>PRICE EXPEDISI</h2>

    <!-- Tabel Data -->
    <div class="card shadow-sm card-price" id="master_table_price">
        <div class="card-header card-price-header bg-white">
            <div>
                <h2 class="h5 mb-0 text-dark">Data Price Expedition</h2>
            </div>
            <div>
                <button id="toggleFormBtn" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Tambah Data Baru
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="priceTable" class="table table-striped table-bordered table-hover w-100">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th>NAMA ITEM</th>
                            <th>DARI</th>
                            <th>SAMPAI</th>
                            <th>RUTE</th>
                            <th>HARGA</th>
                            <th>JENIS</th>
                            <th width="15%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data akan di-load oleh DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

     <!-- Form Input -->
    <div id="formContainer" class="card border-0 bg-light mb-3" style="display: none;">
        <div class="card-body">
            <form id="priceForm">
                @csrf
                <input type="hidden" id="priceId" name="id">

                <div class="row g-4">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">RUTE</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="hidden" name="rute_val_price" id="rute_val_price">
                                <input class="form-control" type="text" name="rute_price" id="rute_price" placeholder="Tekan Pilih !!!" required readonly>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ruteModal">
                                    <i class="fas fa-plus"></i> Pilih
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">DARI - SAMPAI (Kg)</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="number" id="dari_price" name="dari_price"
                                        class="form-control" placeholder="0" required>
                                <span class="fw-semibold">-</span>
                                <input type="number" id="sampai_price" name="sampai_price"
                                        class="form-control" placeholder="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">JENIS</label>
                            <div class="d-flex gap-4 align-items-center">
                                <div class="form-check" style="margin-bottom: 0;">
                                    <input class="form-check-input" type="radio" name="JENIS" value="E" id="jenisEceran" required checked>
                                    <label class="form-check-label" for="jenisEceran">
                                        Eceran
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 0;">
                                    <input class="form-check-input" type="radio" name="JENIS" value="B" id="jenisBoking" required>
                                    <label class="form-check-label" for="jenisBoking">
                                        Boking
                                    </label>
                                </div>
                                <div style="flex: 0 0 150px;">
                                    <input type="number" id="jenis_val" name="jenis_val" class="form-control" placeholder="" value="1" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Item</label>
                            <input type="text" id="keterangan_price" name="keterangan_price"
                                    class="form-control" placeholder="Contoh: EC > 40 KG" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">HARGA (KG)</label>
                            <input type="number" id="harga_price" name="harga_price"
                                    class="form-control" placeholder="0" required>
                        </div>

                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" id="cancelBtn"
                            class="btn btn-secondary">
                        Batal
                    </button>
                    <button type="submit" id="submitBtn"
                            class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Modal Tabel Rute -->
<!-- Modal -->
<div class="modal fade" id="ruteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Rute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0 text-dark">Data Rute</h2>
                    <button class="btn btn-primary btn-sm" id="tambah_rute">
                        <i class="fas fa-plus me-1"></i>Tambah Rute
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="ruteTable" class="table table-striped table-bordered table-hover w-100">
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
<div class="modal fade" id="addRuteModal" tabindex="-1" aria-labelledby="addRuteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRuteModalLabel">Tambah Rute Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="add_rute_flag">
                <form id="ruteForm">
                    @csrf
                    <div class="mb-3">
                        <label for="newRute" class="form-label">Nama Rute</label>
                        <input type="text" class="form-control" id="newRute" name="newRute" placeholder="Contoh: DIY - DPS" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveRute()">Simpan</button>
            </div>
        </div>
    </div>
</div>

 <script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ========================== Initialize DataTable ================================
    var table = $('#priceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('price-expedition.data') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },

            { data: 'KETERANGAN', name: 'prices.KETERANGAN' },

            { data: 'DARI', name: 'prices.DARI' },
            { data: 'SAMPAI', name: 'prices.SAMPAI' },

            // ini ambil alias "RUTE" dari SELECT
            { data: 'RUTE', name: 'rute.RUTE' },

            { data: 'HARGA', name: 'prices.HARGA' },

            // JENIS: tetap pakai nama asli tabel
            { data: 'JENIS', name: 'prices.JENIS' },

            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],

        language: {
            search: "Cari:",
            lengthMenu: "Tampil _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });
    // ====================== End Of Initialize DataTable ==============================
    // ========================== Toggle form show/hide ===============================
    $('#toggleFormBtn').click(function() {
        $('#formContainer').toggle();
        resetForm();
        $('#master_table_price').hide();
    });
    $('#cancelBtn').click(function() {
        $('#formContainer').hide();
        resetForm();
        $('#master_table_price').show();
        $('#priceTable').DataTable().ajax.reload();
    });
    // ===================== End Of Toggle form show/hide ===============================
    // ============================ Reset form ========================================
    function resetForm() {
        $('#priceForm')[0].reset();
        $('#priceId').val('');
        $('#submitBtn').text('Simpan');
        $('#jenis_val').val(1);
        // Clear validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }
    // ======================== End Of Reset form ======================================
    // ======================= Toggle fields berdasarkan jenis==========================
    $('input[name="JENIS"]').change(function() {
        const jenis = $(this).val();
        toggleJenisFields(jenis);
    });

    function toggleJenisFields(jenis) {
        if (jenis === 'E') {
            $('#jenis_val').val(1);
        } else if (jenis === 'B') {
            $('#jenis_val').val(2);
        }
    }
    // ============================ End of Reset form ====================================
    // =========================== Form Prices submission ====================================
    $('#priceForm').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        var url = $('#priceId').val() ? '{{ route("price-expedition.update", ["id" => ":id"]) }}'.replace(':id', $('#priceId').val()) : '{{ route("price-expedition.store") }}';
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,   // WAJIB
                contentType: false,   // WAJIB
                success: function(response) {
                    if (response.success) {
                        $('#loading_modal').modal('hide');
                        Swal.fire('Sukses!', response.message, 'success');
                        $('#formContainer').hide();
                        resetForm();
                        $('#master_table_price').show();
                        $('#priceTable').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $('#loading_modal').modal('hide');
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
    });
    // ========================= End Of Form Prices submission ==================================
    // ============================ Rute Table ===================================
     $('#ruteTable').DataTable({
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
    // ============================ Tambah Rute Click ===================================
    $("#tambah_rute").on('click', function(){
        // Tutup modal pertama dengan method Bootstrap
        $('#ruteModal').modal('hide');

        // Buka modal kedua dengan method Bootstrap
        $('#addRuteModal').modal('show');
        $('#add_rute_flag').val('');
        $('#newRute').val('');
    });
    // ========================= End Of Tambah Rute Click ================================
});

     // ============================== Edit data Price ============================================
    function editData(id) {
        $.ajax({
            url: '{{ route("price-expedition.show", ["id" => ":id"]) }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                $('#master_table_price').hide();
                $('#formContainer').show();

                $('#priceId').val(response.id);
                $('#keterangan_price').val(response.keterangan);
                $('#dari_price').val(response.dari);
                $('#sampai_price').val(response.sampai);

                // rute id (angka)
                $('#rute_val_price').val(response.rute_id);

                // nama rute (teks)
                $('#rute_price').val(response.rute_id);

                $('#harga_price').val(response.harga);

                // radio JENIS (1 = eceran, 2 = booking)
                $('input[name="JENIS"][value="' + response.jenis + '"]').prop('checked', true);
                toggleJenisFields(response.jenis);

                $('#submitBtn').text('Update');
            }
        });
    }
    // ========================= End Of Edit data Price =======================================
    // ================================ Delete data Price =====================================
    function deleteData(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("price-expedition.destroy", ["id" => ":id"]) }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire('Terhapus!', response.message, 'success');
                        $('#priceTable').DataTable().ajax.reload();
                    }
                });
            }
        });
    }
    // ============================ End Of Delete data Price ===================================

   function saveRute() {
        var formData = $('#ruteForm').serialize();
        console.log(formData); // harus mengandung newRute=

        var url = $('#add_rute_flag').val()
            ? '{{ route("rute.update", ["id" => ":id"]) }}'.replace(':id', $('#add_rute_flag').val())
            : '{{ route("rute.store") }}';

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#ruteTable').DataTable().ajax.reload();
                // Tutup modal
                $('#addRuteModal').modal('hide');
                $('#rute_val_price').val(response.id);
                $('#rute_price').val(response.data.RUTE);
                Swal.fire('Sukses!', response.data.RUTE, 'success');
            },
            error: function(xhr) {
                console.log(xhr.responseJSON);
            }
        });
    }


    function pickDataRute(id, ruteText) {
        $('#ruteModal').modal('hide');
        $('#rute_val_price').val(id);
        $('#rute_price').val(ruteText);
    }

    function editDataRute(id) {
        $('#ruteModal').modal('hide');
        $.ajax({
            url: '{{ route("rute.show", ["id" => ":id"]) }}'.replace(':id', id),
            type: 'GET',
            success: function(response) {
                $('#addRuteModal').modal('show');
                $('#newRute').val(response.data.RUTE);
                $('#add_rute_flag').val(response.data.id);
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat memuat data');
            }
        });
    }

    function deleteDataRute(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                container: 'high-z-index'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("rute.destroy", ["id" => ":id"]) }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Terhapus!',
                            text: response.success,
                            icon: 'success',
                            customClass: {
                                container: 'high-z-index'  // atau class custom Anda
                            }
                        });
                         $('#ruteTable').DataTable().ajax.reload();
                    }
                });
            }
        });
    }


</script>
