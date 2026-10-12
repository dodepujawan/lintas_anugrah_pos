<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Aplikasi POS</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        /* Atau jika Anda ingin menggunakan gambar */
        background: url('{{ asset('assets/gambar/toko_theme.png') }}') no-repeat center center fixed; background-size: cover;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        color: #333;
    }

    .container {
        width: 100%;
        max-width: 400px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 30px;
        transition: all 0.3s ease;
    }

    .container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .plastik-title {
        text-align: center;
        margin-bottom: 25px;
    }

    .plastik-title h2 {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .plastik-title p {
        color: #7f8c8d;
        font-size: 16px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #34495e;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #bdc3c7;
        border-radius: 5px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
    }

    .btn-primary {
        width: 100%;
        padding: 12px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.1s ease;
    }

    .btn-primary:hover {
        background-color: #2980b9;
    }

    .btn-primary:active {
        transform: scale(0.98);
    }

    .text-center {
        text-align: center;
        margin-top: 15px;
    }

    .text-center a {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .text-center a:hover {
        color: #2980b9;
        text-decoration: underline;
    }

    .alert-danger {
        background-color: #e74c3c;
        color: white;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
    }
</style>
</head>
<body>
<div class="container">
    <div class="plastik-title">
        <h2>Toko DWEBPRO</h2>
        <p>Aplikasi Online</p>
    </div>

    <!-- Error Message Block -->
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('actionlogin') }}" method="post">
        @csrf
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" id="email" placeholder="Masukkan email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" id="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit" id="submit_form" class="btn-primary">Log In</button>
    </form>

    {{-- <p class="text-center">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang!</a></p> --}}
</div>
</body>
</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#email").on( "keydown", function(event) {
        if(event.which == 13 || event.which === 9){
            event.preventDefault();
            $('#password').focus();
        }
    });
    $('#password').on("keydown", function(event) {
        if (event.which == 13 || event.which === 9) {
            event.preventDefault();
            $('#submit_form').focus();
        }
    });
    // Mencegah submit dengan Enter kecuali pada tombol submit
    $('#submit_form').on('keydown', function (event) {
        if (event.which == 13 || event.which === 9) { // Enter atau Tab
            event.preventDefault(); // Cegah submit otomatis
            $(this).click(); // Simulasikan klik tombol
        }
    });
    // Nonaktifkan tombol submit setelah klik
    $('#submit_form').click(function (e) {
        // Nonaktifkan tombol dan ubah teks
        $(this).prop('disabled', true).text('Processing...');
        // Pastikan form tetap terkirim
        $(this).closest('form').submit();
    });

});
</script>
