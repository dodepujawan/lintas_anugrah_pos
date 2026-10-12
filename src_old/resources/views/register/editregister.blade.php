<div class="container master-edit-register my-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center">FORM EDIT USER</h2>
            <hr>
            @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
            @endif
            <h3 id="message"></h3>
            <form action="#" id="editRegisterForm" method="post">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label"><i class="fa fa-user"></i> Username</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><i class="fa fa-key"></i> Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Kosongkan Jika Tidak Ingin Ubah Password !">
                </div>
                <div class="mb-3">
                    <label for="roles" class="form-label"><i class="fa fa-address-book"></i> Role</label>
                    <input type="text" name="roles" id="roles" class="form-control" value="{{ $user->role_old }}" readonly>
                </div>
                <div class="mb-3">
                    <input type="hidden" id="selected_area_id" value="{{ $user->area_id }}">
                    <label for="area_id_reg" class="form-label">
                        <i class="fa fa-building me-2"></i> Area
                    </label>

                    <select name="area_id_reg" id="area_id_reg" class="form-select">
                        <option value="">
                            Loading Area...
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3"><i class="fa fa-user"></i> Update</button>
                <hr>
                <p class="text-center">Kembali ke <a href="#">Dashboard</a></p>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Fungsi Edit
    // ### Mengambil Nilai Area Callback

function loadAreaRegister() {

    let selectedAreaId = $('#selected_area_id').val();
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
                        item.id == selectedAreaId
                        ? 'selected'
                        : '';
                    html += `
                        <option value="${item.id}"
                                data-name="${item.area}"
                                ${selected}>
                            ${item.area}
                        </option>
                    `;
                });
                $('#area_id_reg').html(html);
            },
            error: function() {
                $('#area_id_reg').html(`
                    <option value="">
                        Gagal Load Area
                    </option>
                `);
            }
        });
    }
    // AUTO LOAD
    loadAreaRegister();

    $(document).on('submit', '#editRegisterForm', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route('updateregister') }}', // Route to handle form submission
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#message').html('<p>' + response.pesan + '</p>');
                if (response.pesan === 'Update Berhasil.') {
                    loadEditForm(); // Reload form after successful update
                }
            },
            error: function(response) {
                $('#message').html('<p>' + response.responseJSON.pesan + '</p>');
            }
        });
    });
});
</script>

