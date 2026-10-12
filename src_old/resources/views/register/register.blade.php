<div class="container master-register my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">FORM REGISTER USER</h2>
            <hr>

            @if(session('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <h3 id="message" class="text-center"></h3>

            <form action="#" id="registerForm" method="post">
                @csrf
                <div class="mb-3">
                    <label for="id_user" class="form-label">
                        <i class="fa fa-user me-2"></i> ID User
                    </label>
                    <input type="text" name="id_user" id="id_user" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fa fa-envelope me-2"></i> Email
                    </label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">
                        <i class="fa fa-user me-2"></i> Username
                    </label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Username" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fa fa-key me-2"></i> Password
                    </label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">
                        <i class="fa fa-address-book me-2"></i> Role
                    </label>
                    <select name="role" id="role" class="form-select">
                        <option value="AD">Admin</option>
                        <option value="ST">Staff</option>
                        <option value="DV">Driver</option>
                        <option value="CS">Customer</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="area_id_reg" class="form-label">
                        <i class="fa fa-building me-2"></i> Area
                    </label>

                    <select name="area_id_reg" id="area_id_reg" class="form-select">
                        <option value="">
                            Loading Area...
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="fa fa-user me-2"></i> Register
                </button>

                <hr class="my-4">

                <p class="text-center mb-0">
                    Kembali Ke Dashboard
                    <a href="{{ route('login') }}" class="text-decoration-none">Klik Disini</a>
                </p>
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
    // ### Callbak user_id starter
    var role = "AD";
    $.ajax({
            url: '{{ route("generate_user_id") }}',
            type: 'GET',
            data: { role: role },
            success: function(response) {
                // Tampilkan user_id di input
                $('#id_user').val(response.user_id);
            },
            error: function(xhr, status, error) {
                console.error('Error: ' + error);
            }
    });

    // ### Mengambil Nilai Roles Callback
    $('#role').change(function() {
        var role = $(this).val();
        $.ajax({
            url: '{{ route("generate_user_id") }}',
            type: 'GET',
            data: { role: role },
            success: function(response) {
                // Tampilkan user_id di input
                $('#id_user').val(response.user_id);
            },
            error: function(xhr, status, error) {
                console.error('Error: ' + error);
            }
        });
    });

    // ### Mengambil Nilai Area Callback
    function loadArea() {
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
                    html += `
                        <option value="${item.id}"
                                data-name="${item.area}">
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
    loadArea();

    // ###Submit Form
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route('actionregister') }}', // Update with the actual route name
            type: 'POST',
            data: {
                name: $('#name').val(),
                email: $('#email').val(),
                password: $('#password').val(),
                role: $('#role').val(),
                area_id: $('#area_id_reg').val(),
                area_name: $('#area_id_reg').find(':selected').data('name')
            },
            success: function(response) {
                $('#message').html('<p>' + response.pesan + '</p>');
                if (response.pesan === 'Register Berhasil. Akun Anda sudah Aktif.') {
                    $('#registerForm')[0].reset();
                    var role = "AD";
                    $.ajax({
                            url: '{{ route("generate_user_id") }}',
                            type: 'GET',
                            data: { role: role },
                            success: function(response) {
                                // Tampilkan user_id di input
                                $('#id_user').val(response.user_id);
                            },
                            error: function(xhr, status, error) {
                                console.error('Error: ' + error);
                            }
                    });
                }
            },
            error: function(response) {
                console.error(response.responseJSON.pesan);
                $('#message').html('<p>' + response.responseJSON.pesan + '</p>');
            }
        });
    });
});
</script>
