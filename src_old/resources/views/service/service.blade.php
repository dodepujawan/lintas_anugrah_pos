<style>
.form-label {
    font-weight: 600;
}
.card-header {
    font-size: 18px;
    letter-spacing: 1px;
}
</style>
<div class="container mt-4">
<div class="card shadow">
    <div class="card-header bg-success text-white fw-bold">
        SERVICE MOBIL
    </div>

    <div class="card-body" id="tableServiceMaster">
        <div class="mt-3">
            <button class="btn btn-primary" id="add_service">
                + Tambah
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="tableService">
                <thead>
                    <tr>
                        <th>No Bukti</th>
                        <th>Supplier</th>
                        <th>Kendaraan</th>
                        <th>Nilai Service</th>
                        <th>Tanggal</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="card-body d-none" id="formServiceMaster">
        <div class="mt-3">
            <button class="btn btn-success" id="return_table_service">
            << Kembali
            </button>
        </div>
        <form id="formService">
            @csrf

            <div class="row">
                <input type="hidden" name="id_service" id="id_service">
                <!-- KIRI -->
                <div class="col-md-7 mt-3">
                    <div class="row mb-2">
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="akun_hutang" id="akun_hutang" list="list_akun_hutang" autocomplete="off" value="{{ old('akun_hutang', '20110001') }}">
                        </div>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="akun_hutang_nama" id="akun_hutang_nama" value="{{ old('akun_hutang_nama', 'HUTANG USAHA') }}" readonly>
                        </div>
                    </div>
                    <datalist id="list_akun_hutang"></datalist>

                    <div class="row mb-2 align-items-center">
                        <label class="col-sm-3 col-form-label">SUPPLIER</label>

                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="supplier" id="supplier">
                                <button class="btn btn-warning" type="button" id="supplier_service_btn">🔍</button>
                            </div>
                        </div>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" name="supplier_nama" id="supplier_nama">
                        </div>
                    </div>

                    <div class="row mb-2 align-items-center">
                        <label class="col-sm-3 col-form-label">KENDARAAN</label>

                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="kendaraan_service" id="kendaraan_service">
                                <button class="btn btn-warning" type="button" id="kendaraan_service_btn">🔍</button>
                            </div>
                        </div>

                        <div class="col-sm-5">
                            <input type="text" class="form-control" name="kendaraan_service_nama" id="kendaraan_service_nama">
                        </div>
                    </div>

                    <div class="row mb-2" hidden>
                        <label class="col-sm-3 col-form-label">AKUN BIAYA MOBIL</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="fno_prk_b_service" id="fno_prk_b_service">
                        </div>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="fno_prk_b_service_nama" id="fno_prk_b_service_nama" readonly>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-sm-3 col-form-label">NILAI SERVICE</label>
                        <div class="col-sm-3">
                            <input type="number" class="form-control text-end" name="nilai_servis" id="nilai_servis" value="0">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-sm-3 col-form-label">ITEM BARANG</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" rows="3" name="keterangan_service" id="keterangan_service"></textarea>
                        </div>
                    </div>

                </div>

                <!-- KANAN -->
                <div class="col-md-5 mt-3">

                    <div class="row mb-2">
                        <label class="col-sm-4 col-form-label">NOMOR FAKTUR</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="no_faktur_service" id="no_faktur_service" readonly>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-sm-4 col-form-label">TGL.DOCUMENT</label>
                        <div class="col-sm-7">
                            <input type="date" class="form-control" name="tgl_document_service" id="tgl_document_service">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-sm-4 col-form-label">TGL.JTH.TEMPO</label>
                        <div class="col-sm-7">
                            <input type="date" class="form-control" name="tgl_jatuh_tempo_service" id="tgl_jatuh_tempo_service">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-sm-4 col-form-label">NO.JURNAL</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="no_jurnal_service" id="no_jurnal_service">
                        </div>
                    </div>

                </div>

            </div>

            <hr>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-success" id="button_service_submit">💾 SIMPAN</button>
                {{-- <button class="btn btn-secondary">🆕 BARU</button>
                <button class="btn btn-danger">❌ HAPUS</button> --}}
            </div>

        </form>

    </div>

</div>
</div>
{{-- MODAL --}}
@include('service.service_modal')
<script>
$(document).ready(function(){
    // Set CSRF token in AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
// ================================= Table Service =====================================
    if ($.fn.DataTable.isDataTable('#tableService')) {
        $('#tableService').DataTable().destroy();
    }
    $('#tableService').DataTable({

        processing: true,
        serverSide: true,

        ajax: "{{ route('service.data') }}",

        columns: [

            { data:'NO_SERVICE', name:'NO_SERVICE' },
            { data:'KODE_SUPPLIER', name:'KODE_SUPPLIER' },
            { data:'KODE_MOBIL', name:'KODE_MOBIL' },
            { data:'NILAI_SERVIS', name:'NILAI_SERVIS' },
            { data:'TGL_SERVIS', name:'TGL_SERVIS' },
            { data:'action', name:'action', orderable:false, searchable:false }

        ]

    });
// ============================== End Of Table Service ==================================
// ================================== Perkirann FNO_PRK =====================================
    $('#akun_hutang').on('keyup', function () {
        let search = $(this).val();
        $.ajax({
            url: "{{ route('service.perkiraan') }}",
            type: "GET",
            data: {
                search: search
            },
            success: function (data) {
                let option = '';
                data.forEach(function (item) {
                    option += `
                        <option
                            value="${item.FNO_PRK}"
                            data-nama="${item.FNM_PRK}">
                            ${item.FNM_PRK}
                        </option>
                    `;
                });
                $('#list_akun_hutang').html(option);
            }
        });
    });
    $('#akun_hutang').on('change', function () {
        let kode = $(this).val();
        $.ajax({
            url: "{{ route('service.perkiraan') }}",
            type: "GET",
            data: {
                search: kode
            },
            success: function (data) {
                if (data.length > 0) {
                    $('#akun_hutang_nama').val(data[0].FNM_PRK);
                } else {
                    $('#akun_hutang_nama').val('');
                }
            }
        });
    });
// ============================== End Of Perkirann FNO_PRK ==================================
// ================================= Add Supplier ====================================
    $('#add_service').on('click', function(e){
        $('#tableServiceMaster').addClass('d-none');
        $('#formServiceMaster').removeClass('d-none');
        $('#formService')[0].reset();
        $('#button_service_submit')
        .removeClass('btn btn-success')
        .addClass('btn btn-primary')
        .html('<i class="bx bx-save"></i> SIMPAN');

        let today = new Date().toISOString().split('T')[0];
        $('#tgl_document_service').val(today);
        $('#tgl_jatuh_tempo_service').val(today);
    });
// ============================== End Of Add Supplier ==================================
// ============================== Return Table Supplier ==================================
    $('#return_table_service').on('click', function(e){
        $('#formServiceMaster').addClass('d-none');
        $('#tableServiceMaster').removeClass('d-none');
        $('#tableService').DataTable().ajax.reload();
    });
// =========================== End Of Return Table Supplier =============================
// ================================= Pilih Supplier =====================================
    $('#supplier_service_btn').click(function(e) {
        e.preventDefault();
        $('#supplierServiceModal').modal('show');
        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#supplierServiceModalTable')) {
            $('#supplierServiceModalTable').DataTable().destroy();
        }
        var table_supplier_service = $('#supplierServiceModalTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("service-supplier.data") }}',
            // Scroll settings
            scrollX: true,
            scrollY: "400px",
            scrollCollapse: true,
            // Responsive settings
            responsive: true,
            autoWidth: true,
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

        // Initialize tooltips
        // $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // ### Select Button
    $(document).on('click', '.btn-pick-supplier-service', function(e) {
        e.preventDefault();
        var supplier = $(this).data('supplier');
        var nama_supplier = $(this).data('nama');
        // Mengisi nilai ke elemen yang dituju
        $('#supplier').val(supplier);
        $('#supplier_nama').val(nama_supplier);

        // Tutup modal
        $('#supplierServiceModal').modal('hide');
    });
// ============================== End Of Pilih Supplier ==============================
// ================================= Pilih Kendaraan ====================================
    $('#kendaraan_service_btn').click(function(e) {
        e.preventDefault();
        $('#kendaraanServiceModal').modal('show');
        // hancurkan datatable jika sudah pernah dipakai
        if ($.fn.DataTable.isDataTable('#kendaraanServiceModalTable')) {
            $('#kendaraanServiceModalTable').DataTable().destroy();
        }
        var table_supplier_service = $('#kendaraanServiceModalTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("service-kendaraan.data") }}',
            // Scroll settings
            scrollX: true,
            scrollY: "400px",
            scrollCollapse: true,
            // Responsive settings
            responsive: true,
            autoWidth: true,
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

        // Initialize tooltips
        // $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // ### Select Button
    $(document).on('click', '.pickKendaraanServiceModel', function(e) {
        e.preventDefault();
        var kendaraan_id = $(this).data('kode');
        var nama_kendaraan = $(this).data('nama');
        var fno_prk_b = $(this).data('fno');
        var plat = $(this).data('plat');
        // Mengisi nilai ke elemen yang dituju
        $('#kendaraan_service').val(kendaraan_id);
        $('#kendaraan_service_nama').val(nama_kendaraan);
        $('#fno_prk_b_service').val(fno_prk_b);
        $('#fno_prk_b_service_nama').val('By keperluan service ' + plat);
        // Tutup modal
        $('#kendaraanServiceModal').modal('hide');
    });
// ============================== End Of Pilih Kendaraan ==============================
// ================================= Submit Service =====================================
    $('#formService').submit(function(e){
        e.preventDefault();
        // Simpan referensi ke form
        var form = this;
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url: "{{ route('service.store') }}",
                type: "POST",
                data: $(form).serialize(),
                success: function(response){
                    $('#loading_modal').modal('hide');
                    if(response.status){

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Service berhasil disimpan, nomer: ' + response.kode,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $('#formService')[0].reset();
                        $('#formServiceMaster').addClass('d-none');
                        $('#tableServiceMaster').removeClass('d-none');
                        $('#tableService').DataTable().ajax.reload();

                    }else{
                        alert(response.message);
                    }

                },
                error: function(xhr){
                    alert('Terjadi kesalahan server');
                }
            });
        });
    });
// ============================== End Of Submit Service =================================
// =============================== Show Detail Service =================================
    $(document).on('click','.edit-service',function(){
        let id = $(this).data('id');
        $('#loading_modal').modal('show');
        $('#loading_modal').one('shown.bs.modal', function () {
            $.ajax({
                url:"{{ route('service.show', ':id') }}".replace(':id', id),
                type:"GET",
                dataType:"json",

                success:function(data){
                    $('#loading_modal').modal('hide');
                    $('#tableServiceMaster').addClass('d-none');
                    $('#formServiceMaster').removeClass('d-none');
                    $('#id_service').val(data.id_service);

                    $('#supplier').val(data.supplier);
                    $('#supplier_nama').val(data.supplier_nama);
                    $('#kendaraan_service').val(data.kendaraan_service);
                    $('#kendaraan_service_nama').val(data.kendaraan_service_nama);

                    $('#akun_hutang').val(data.fno_prk_id);
                    $('#akun_hutang_nama').val(data.fno_prk_nama);

                    $('#fno_prk_b_service').val(data.fno_prk_b_service);
                    $('#fno_prk_b_service_nama').val("By keperluan service  " + data.kendaraan_service_plat);

                    $('#nilai_servis').val(data.nilai_servis);

                    $('#keterangan_service').val(data.keterangan);

                    $('#no_faktur_service').val(data.no_faktur);
                    console.log('wcw' + data.no_faktur);
                    $('#tgl_document_service').val(data.tgl_document);
                    console.log('wcw2' + data.tgl_document);
                    $('#tgl_jatuh_tempo_service').val(data.tgl_jatuh_tempo);
                    $('#no_jurnal_service').val(data.no_jurnal);

                    $('#button_service_submit')
                    .removeClass('btn btn-primary')
                    .addClass('btn btn-success')
                    .html('<i class="bx bx-edit"></i> Update');

                }
            });
        });
    });
// ============================== End Of Show Detail Service =============================
// ================================== Delete Service ================================
    $(document).on('click','.delete-service',function(){
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Data?',
            text: "Data service akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loading_modal').modal('show');
                $('#loading_modal').one('shown.bs.modal', function () {
                    $.ajax({
                        url:"{{ route('service.delete', ':id') }}".replace(':id', id),
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response){
                            $('#loading_modal').modal('hide');
                            if(response.status){
                                Swal.fire(
                                    'Berhasil!',
                                    response.message,
                                    'success'
                                );
                                // reload datatable kalau ada
                                $('#tableService').DataTable().ajax.reload();
                            }else{
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        }
                    });
                });
            }
        });
    });
// ============================== End Of Delete Service ==============================
});
</script>

