<x-guest-layout>
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="auth-logo mx-auto mb-3"><i class="bi bi-envelope-lock"></i></div>
            <h1 class="auth-title mb-1">Lupa Password</h1>
            <p class="auth-subtitle">Masukkan email akun FinLedger. Tautan reset akan dikirim ke email tersebut.</p>
        </div>

        @if(session('status'))
        <div class="alert-success-auth mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="form-label"><i class="bi bi-envelope me-1"></i>Email Terdaftar</label>
                <input id="email" class="form-control auth-input @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus autocomplete="email">
                @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn-auth"><i class="bi bi-send me-2"></i>Kirim Tautan Reset</button>
        </form>

        <div class="text-center mt-4"><a href="{{ route('login') }}" class="auth-link"><i class="bi bi-arrow-left me-1"></i>Kembali ke Login</a></div>
    </div>
</x-guest-layout>
