<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Form Supplier / Leasing</h5>
    </div>
    <div class="card-body" id="table_supplier_master">
        <div class="mt-3">
            <button class="btn btn-primary" id="add_supplier">
                + Tambah Supplier
            </button>
        </div>
        <div class="table-responsive">
            <table id="table_supplier" class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Kode</th>
                        <th>Kategori</th>
                        <th>Nama</th>
                        <th>Kota</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="card-body d-none" id="form_supplier_master">
        <div class="mb-3">
            <button class="btn btn-success" id="btn_table_supplier">
                Tampilkan Table
            </button>
        </div>
        <form id="form_supplier">

            {{-- ========================= --}}
            {{-- INFORMASI UMUM --}}
            {{-- ========================= --}}
            <h6 class="border-bottom pb-2 mb-3 text-primary">
                Informasi Umum
            </h6>

            <div class="row mb-3">
                <input type="hidden" id="supplier_id" name="supplier_id">
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select name="kategori_supplier" id="kategori_supplier"
                        class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="SP">Supplier</option>
                        <option value="LS">Leasing</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Kode (Auto)</label>
                    <input type="text" id="kode_supplier"
                        class="form-control bg-light" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama_supplier"
                        id="nama_supplier"
                        class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Alamat 1</label>
                    <input type="text" name="alamat1_supplier"
                        id="alamat1_supplier"
                        class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat 2</label>
                    <input type="text" name="alamat2_supplier"
                        id="alamat2_supplier"
                        class="form-control">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">Kota</label>
                    <input type="text" name="kota_supplier"
                        id="kota_supplier"
                        class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="telepon_supplier"
                        id="telepon_supplier"
                        class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fax</label>
                    <input type="text" name="fax_supplier"
                        id="fax_supplier"
                        class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email_supplier"
                        id="email_supplier"
                        class="form-control">
                </div>
            </div>

            {{-- ========================= --}}
            {{-- INFORMASI KONTAK --}}
            {{-- ========================= --}}
            <h6 class="border-bottom pb-2 mb-3 text-primary">
                Informasi Kontak
            </h6>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Nama Kontak</label>
                    <input type="text" name="kontak_supplier"
                        id="kontak_supplier"
                        class="form-control">
                </div>
            </div>

            {{-- ========================= --}}
            {{-- INFORMASI BANK --}}
            {{-- ========================= --}}
            <h6 class="border-bottom pb-2 mb-3 text-primary">
                Informasi Bank
            </h6>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Nama Bank</label>
                    <input type="text" name="bank_supplier"
                        id="bank_supplier"
                        class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Nomor Rekening</label>
                    <input type="text" name="norek_supplier"
                        id="norek_supplier"
                        class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Atas Nama</label>
                    <input type="text" name="atasnama_supplier"
                        id="atasnama_supplier"
                        class="form-control">
                </div>
            </div>

            {{-- ========================= --}}
            {{-- FIELD KEUANGAN (HIDDEN) --}}
            {{-- ========================= --}}
            <input type="hidden" name="saldo_supplier" value="0">
            <input type="hidden" name="returan_supplier" value="0">
            <input type="hidden" name="ftop_supplier" value="0">
            <input type="hidden" name="disc_reg_supplier" value="0">

            <div class="text-end">
                {{-- <button type="reset" class="btn btn-secondary me-2">
                    Reset
                </button> --}}

                <button type="submit" id="btn_simpan_supplier"></button>
            </div>

        </form>
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
// ================================= Table Supplier =====================================
    if ($.fn.DataTable.isDataTable('#table_supplier')) {
        $('#table_supplier').DataTable().destroy();
    }
    let tableSupplier = $('#table_supplier').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('msupplier.data') }}",

        columns: [
            {data: 'SUPPLIER'},
            {data: 'kategori_label', orderable:false, searchable:false},
            {data: 'NAMA'},
            {data: 'KOTA'},
            {data: 'TELEPON'},
            {data: 'EMAIL'},
            {data: 'action', orderable:false, searchable:false}
        ]
    });
// ============================== End Of Table Supplier ==================================
// ================================= Add Supplier ====================================
    $('#add_supplier').on('click', function(e){
        $('#table_supplier_master').addClass('d-none');
        $('#form_supplier_master').removeClass('d-none');
        $('#form_supplier')[0].reset();
        $('#btn_simpan_supplier')
        .removeClass('btn btn-success')
        .addClass('btn btn-primary')
        .html('<i class="bx bx-save"></i> Simpan');
    });
// ============================== End Of Add Supplier ==================================
// ================================= Return Table Supplier ====================================
    $('#btn_table_supplier').on('click', function(e){
        $('#form_supplier_master').addClass('d-none');
        $('#table_supplier_master').removeClass('d-none');
        $('#table_supplier').DataTable().ajax.reload();
    });
// ============================== End Of Return Table Supplier =================================
// ================================= Store Supplier =====================================
    $('#form_supplier').on('submit', function(e){
        e.preventDefault();
        let formData = $(this).serialize();
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url: "{{ route('msupplier.store') }}",
                method: "POST",
                data: formData,
                success: function(res){

                    if(res.status){
                        $('#loading_modal').modal('hide');
                        $('#kode_supplier').val(res.kode);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Supplier berhasil disimpan, nomer: ' + res.kode,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $('#form_supplier')[0].reset();

                        // Kembali Ke Table
                        $('#form_supplier_master').addClass('d-none');
                        $('#table_supplier_master').removeClass('d-none');
                        $('#table_supplier').DataTable().ajax.reload();
                    } else {
                        $('#loading_modal').modal('hide');
                        alert(res.message);
                    }

                },
                error: function(){
                    $('#loading_modal').modal('hide');
                    alert('Terjadi kesalahan server');
                }
            });
        });

    });
// ============================== End Of Store Supplier ==================================
// ================================== Show Supplier =====================================
    $(document).on('click', '.btn-edit-supplier', function(){
        let id = $(this).data('id');

        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url: "{{ route('msupplier.show', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(res){
                    $('#loading_modal').modal('hide');
                    if(res.status){
                        let data = res.data;

                        // tampilkan form
                        $('#table_supplier_master').addClass('d-none');
                        $('#form_supplier_master').removeClass('d-none');

                        // isi form
                        $('#kode_supplier').val(data.SUPPLIER);
                        $('#kategori_supplier').val(data.KATEGORI);

                        $('#nama_supplier').val(data.NAMA);
                        $('#alamat1_supplier').val(data.ALAMAT1);
                        $('#alamat2_supplier').val(data.ALAMAT2);
                        $('#kota_supplier').val(data.KOTA);

                        $('#telepon_supplier').val(data.TELEPON);
                        $('#fax_supplier').val(data.FAX);
                        $('#email_supplier').val(data.EMAIL);

                        $('#kontak_supplier').val(data.KONTAK);

                        $('#bank_supplier').val(data.BANK);
                        $('#norek_supplier').val(data.NOREK);
                        $('#atasnama_supplier').val(data.ATASNAMA);

                        // simpan id untuk update
                        $('#supplier_id').val(data.id);
                        // ubah tombol
                        $('#btn_simpan_supplier')
                        .removeClass('btn btn-primary')
                        .addClass('btn btn-success')
                        .html('<i class="bx bx-edit"></i> Update');
                    }else{
                        alert(res.message);
                    }
                },
                error:function(){
                    $('#loading_modal').modal('hide');
                    alert('Gagal mengambil data');
                }
            });
        });
    });
// ============================== End Of Store Supplier ==================================
// ================================== Edit Supplier =====================================
    $(document).on('click', '.btn-delete-supplier', function(){
        let id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Supplier?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loading_modal').modal('show');
                $('#loading_modal').one('shown.bs.modal', function () {
                    $.ajax({
                        url: "{{ route('msupplier.destroy', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res){
                            $('#loading_modal').modal('hide');
                            if(res.status){
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                $('#table_supplier').DataTable().ajax.reload(null,false);
                            }else{
                                $('#loading_modal').modal('hide');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message
                                });
                            }
                        },
                        error:function(){
                            $('#loading_modal').modal('hide');
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan server'
                            });
                        }
                    });
                });
            }
        });
    });
// ============================== End Of Edit Supplier ==================================
});
</script>
