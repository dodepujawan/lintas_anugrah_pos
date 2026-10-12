<style>
    .table-responsive {
        overflow: visible;
    }
    #userTable {
        width: 100% !important;
    }
</style>
<div class="container mt-5">
    <div id="formtable">
        <h5>User Table</h5>
        <div class="row mb-3 g-2">
            <div class="col-md-3">
                <label for="startDate" class="form-label">Start Date</label>
                <input type="date" id="startDate" class="form-control" placeholder="Start Date">
            </div>
            <div class="col-md-3">
                <label for="endDate" class="form-label">End Date</label>
                <input type="date" id="endDate" class="form-control" placeholder="End Date">
            </div>
            <div class="col-md-3">
                <label for="searchBox" class="form-label">Search</label>
                <input type="text" id="searchBox" class="form-control" placeholder="Search">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="filterBtn" class="btn btn-primary w-50">Filter</button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="userTable" class="display table table-bordered mb-2">
                <thead>
                    <tr>
                        <th>User Id</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Roles</th>
                        <th>Join</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan diisi oleh DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    {{-- ### Fungsi Edit Register --}}
    <div id="formedit" class="d-none">
        <div class="container master-edit-register"><br>
            <div class="col-md-6 offset-md-3">
                <h2 class="text-center">FORM EDIT USER</h2>
                <hr>
                {{-- @if(session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
                @endif --}}
                <h3 id="message"></h3>
                <form action="{{ route('update_list_register') }}" id="editListRegisterForm" method="post">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="id" class="form-label"><i class="fa fa-user"></i> User Id</label>
                        <input type="text" name="id" id="id" class="form-control" value="" required="" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="" required="">
                    </div>
                    <div class="form-group mb-3">
                        <label for="name" class="form-label"><i class="fa fa-user"></i> Username</label>
                        <input type="text" name="name" id="name" class="form-control" value="" required="">
                    </div>
                    <div class="form-group mb-3">
                        <label for="password" class="form-label"><i class="fa fa-key"></i> Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Kosongkan Jika Tidak Ingin Ubah Password !">
                    </div>
                    <div class="form-group mb-3">
                        <label for="roles_list_reg" class="form-label"><i class="fa fa-address-book"></i> Role</label>
                        <input type="hidden" name="roles_flag" id="roles_flag" class="form-control" value="" readonly>
                        <select name="roles_list_reg" id="roles_list_reg" class="form-control">
                            <option value="AD">Admin</option>
                            <option value="ST">Staff</option>
                            <option value="DV">Driver</option>
                            <option value="CS">Customer</option>
                       </select>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="area_name_reg_edit" id="area_name_reg_edit">
                        <label for="area_id_reg_edit" class="form-label">
                            <i class="fa fa-building me-2"></i> Area
                        </label>

                        <select name="area_id_reg_edit" id="area_id_reg_edit" class="form-select">
                            <option value="">
                                Loading Area...
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2" id="but_edit_list_register"><i class="fa fa-user"></i> Update</button>
                    <hr>
                    <p class="text-center">Kembali ke <a href="javascript:void(0);">Dashboard !</a></p>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
<script>
$(document).ready(function() {
    // ========================== menapilkan list user ===============================
    loadListRegisterForm();
    function loadListRegisterForm() {
        let table = $('#userTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("filter_register") }}',
                data: function(d) {
                    d.startDate = $('#startDate').val();
                    d.endDate = $('#endDate').val();
                    d.searchText = $('#searchBox').val();
                }
            },
            columns:[
                { data: 'user_id' },
                { data: 'email' },
                { data: 'name' },
                { data: 'role_old' },
                { data: 'created_at' },
                {
                    data: null,
                    render: function (data, type, row) {
                        return '<button class="btn btn-primary btn-sm editBtn" data-id="' + row.id + '">' + '<i class="bx bx-edit"></i>' + '</button> ' + '<button class="btn btn-danger btn-sm deleteBtn" data-id="' + row.user_id + '">' + '<i class="bx bx-trash"></i>' + '</button>';
                    }
                }
            ],
            columnDefs: [
                { targets: 4, className: "text-start" } // kolom Join Jadi Rata Kiri
            ],
            searching: false,
            paging: true,
            info: false,
            scrollY: '100vh',  // Menambahkan scrolling vertikal
            scrollCollapse: true,
            scrollX: true,
            fixedHeader: {
                header: true,
                footer: false
            }
        });
            $('#filterBtn').on('click', function() {
                table.ajax.reload();
            });
    }
    // ========================== end of menapilkan list user ===============================

     // ============================ edit list user =================================
     $(document).on('click', '.editBtn', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let url = '{{ route("edit_list_register", ":id") }}';
        url = url.replace(':id', id);
        $.ajax({
            url: url, // Route to load the form
            type: 'GET',
            success: function(data) {
                $('#id').val(data.user_id);
                $('#email').val(data.email);
                $('#name').val(data.name);
                $('#roles_flag').val(data.roles);
                calling_roles_first();
                loadAreaEdit(data.area_id);

                // Tampilkan form
                $('#formtable').hide();
                $('#formedit').removeClass('d-none');
            },
            error: function() {
                $('.master-page').html('<p>Error loading form.</p>');
            }
        });
    });

     function calling_roles_first(){
        var hidden_role = $('#roles_flag').val();
        if (hidden_role === 'admin')
        {
            $('#roles_list_reg').val("AD");
        }else if(hidden_role === 'staff')
        {
            $('#roles_list_reg').val("ST");
        }else if(hidden_role === 'customer'){
            $('#roles_list_reg').val("CS");
        }else if(hidden_role === 'driver'){
            $('#roles_list_reg').val("DV");
        }
    }

    function loadAreaEdit(selectedId = '') {
        $.ajax({
            url: '{{ route('get_area') }}',
            type: 'GET',
            success: function(response) {
                let html = `
                    <option value="">
                        -- Pilih Area --
                    </option>
                `;
                $.each(response.data, function(i, item) {
                    let selected =
                        item.id == selectedId
                        ? 'selected'
                        : '';
                        // 🔥 isi hidden area name
                        if(item.id == selectedId){
                            $('#area_name_reg_edit').val(item.area);
                        }
                    html += `
                        <option value="${item.id}"
                                data-name="${item.area}"
                                ${selected}>
                            ${item.area}
                        </option>
                    `;
                });
                $('#area_id_reg_edit').html(html);
            },
            error: function() {
                $('#area_id_reg_edit').html(`
                    <option value="">
                        Gagal Load Area
                    </option>
                `);
            }
        });
    }

    // menubah name hidden area
    $(document).on('change', '#area_id_reg_edit', function () {
        let areaName =
            $(this)
            .find(':selected')
            .data('name');
        $('#area_name_reg_edit').val(areaName ?? '');
    });
    // ========================== end of edit list user ===============================
    // ========================== update list user ===============================
    $(document).off('submit', '#editListRegisterForm');

    $(document).on('submit', '#editListRegisterForm', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Form submitted');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let formData = $(this).serialize();
        console.log('Form data:', formData);
        $.ajax({
            url: '{{ route('update_list_register') }}', // Route to handle form submission
            type: 'POST',
            data: formData,
            success: function(response) {
                // console.log('Success:', response);
                $('#formedit').addClass('d-none');
                $('#formtable').show();

                $('#userTable').DataTable().ajax.reload();
                Swal.fire({
                    title: 'Sukses!',
                    text: 'Data berhasil diupdate!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            },
            error: function(response) {
                console.error('Error:', xhr.responseText);
                $('#message_list_register').html('<p>' + response.responseJSON.pesan + '</p>');
            }
        });
    });
    // ========================== end of update list user ===============================
    // ============================= delete list user ==================================
    $(document).on('click','.deleteBtn', function(e){
        e.preventDefault();
        let row = $(this).closest('tr');
        let id = $(this).data('id');
        let url = '{{ route("delete_list_register", ":id") }}';
        url = url.replace(':id', id);

        Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#userTable').DataTable().row(row).remove().draw(false);

                        Swal.fire(
                            'Terhapus!',
                            'Data telah berhasil dihapus.',
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Terjadi kesalahan saat menghapus data.',
                            'error'
                        );
                    }
                });
            }
        });
    })
    // ========================== end of delete list user ===============================

});
</script>
